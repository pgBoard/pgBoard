CREATE LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION indexOf(anyelement,anyarray) RETURNS integer AS $$
DECLARE
  search ALIAS FOR $1;
  arr ALIAS FOR $2;
BEGIN
  FOR i IN COALESCE(array_lower(arr,1),0)..COALESCE(array_upper(arr,1),-1) LOOP
    IF arr[i] = search THEN
      RETURN i;
    END IF;
  END LOOP;
  RETURN NULL;
END;
$$ LANGUAGE plpgsql;


CREATE OR REPLACE FUNCTION member_sync() RETURNS trigger AS $$
BEGIN
  IF TG_OP = 'DELETE' THEN
    UPDATE board_data SET value=(value::integer)-1 WHERE name='total_members';
    RETURN OLD;
  ELSEIF TG_OP = 'INSERT' THEN
    UPDATE board_data SET value=(value::integer)+1 WHERE name='total_members';
    RETURN NEW;
  END IF;
  RETURN NULL;
END;
$$ LANGUAGE plpgsql;


CREATE OR REPLACE FUNCTION thread_sync() RETURNS trigger AS $$
BEGIN
  IF TG_OP = 'DELETE' THEN
    UPDATE member SET total_threads=total_threads-1 WHERE id=OLD.member_id;
    UPDATE board_data SET value=(value::integer)-1 WHERE name='total_threads';
    RETURN OLD;
  ELSEIF TG_OP = 'INSERT' THEN
    UPDATE member SET total_threads=total_threads+1 WHERE id=NEW.member_id;
    UPDATE board_data SET value=(value::integer)+1 WHERE name='total_threads';
    RETURN NEW;
  END IF;
  RETURN NULL;
END;
$$ LANGUAGE plpgsql;


CREATE OR REPLACE FUNCTION thread_post_sync() RETURNS trigger AS $$
BEGIN
  IF TG_OP = 'DELETE' THEN
    UPDATE member SET total_thread_posts=total_thread_posts-1, last_post=now() WHERE id=OLD.member_id;
    UPDATE board_data SET value=(value::integer)-1 WHERE name='total_thread_posts';
    IF (SELECT count(*) FROM thread_post WHERE thread_id=OLD.thread_id) > 1 THEN
      UPDATE
        thread
      SET
        posts=posts-1,
        first_post_id=(SELECT id FROM thread_post WHERE thread_id=OLD.thread_id ORDER BY date_posted ASC LIMIT 1),
        last_member_id=(SELECT member_id FROM thread_post WHERE thread_id=OLD.thread_id ORDER BY date_posted DESC LIMIT 1),
        date_last_posted=(SELECT date_posted FROM thread_post WHERE thread_id=OLD.thread_id ORDER BY date_posted DESC LIMIT 1)
      WHERE
        id=OLD.thread_id;
    ELSEIF (SELECT posts FROM thread WHERE id=OLD.thread_id) = 1 THEN
      DELETE FROM thread_member WHERE thread_id=OLD.thread_id;
      DELETE FROM favorite WHERE thread_id=OLD.thread_id;
      DELETE FROM thread WHERE id=OLD.thread_id;
    END IF;
    IF (SELECT count(*) FROM thread_post WHERE member_id=OLD.member_id AND thread_id=OLD.thread_id) = 0 THEN
      DELETE FROM thread_member WHERE member_id=OLD.member_id AND thread_id=OLD.thread_id;
    END IF;
    RETURN OLD;
  ELSEIF TG_OP = 'INSERT' THEN
    UPDATE member SET last_post=now() WHERE id=NEW.member_id;
    UPDATE member SET total_thread_posts=total_thread_posts+1 WHERE id=NEW.member_id;
    UPDATE board_data SET value=(value::integer)+1 WHERE name='total_thread_posts';
    UPDATE
      thread
    SET
      posts=posts+1,
      first_post_id=(SELECT id FROM thread_post WHERE thread_id=NEW.thread_id ORDER BY date_posted ASC LIMIT 1),
      last_member_id=(SELECT member_id FROM thread_post WHERE thread_id=NEW.thread_id ORDER BY date_posted DESC LIMIT 1),
      date_last_posted=now()
    WHERE
      id=NEW.thread_id;
    IF NOT EXISTS (SELECT 1 FROM thread_member WHERE member_id=NEW.member_id AND thread_id=NEW.thread_id) THEN
      INSERT INTO
        thread_member (member_id,thread_id,date_posted,last_view_posts)
      VALUES
        (NEW.member_id,NEW.thread_id,now(),(SELECT posts FROM thread WHERE id=NEW.thread_id));
    ELSE
      UPDATE
        thread_member
      SET
        date_posted=now(),
        last_view_posts=(SELECT posts FROM thread WHERE id=NEW.thread_id)
      WHERE
        member_id=NEW.member_id
      AND
        thread_id=NEW.thread_id;
    END IF;
    RETURN NEW;
  END IF;
  RETURN NULL;
END;
$$ LANGUAGE plpgsql;


CREATE OR REPLACE FUNCTION message_post_sync() RETURNS trigger AS $$
BEGIN
  IF TG_OP = 'DELETE' THEN
    IF (SELECT count(*) FROM message_post WHERE message_id=OLD.message_id) > 1 THEN
      UPDATE
        message
      SET
        posts=posts-1,
        first_post_id=(SELECT id FROM message_post WHERE message_id=OLD.message_id ORDER BY date_posted ASC LIMIT 1),
        last_member_id=(SELECT member_id FROM message_post WHERE message_id=OLD.message_id ORDER BY date_posted DESC LIMIT 1),
        date_last_posted=(SELECT date_posted FROM message_post WHERE message_id=OLD. message_id ORDER BY date_posted DESC LIMIT 1)
      WHERE
        id=OLD.message_id;
    ELSEIF (SELECT posts FROM message WHERE id=OLD.message_id) = 1 THEN
      DELETE FROM message_member WHERE message_id=OLD.message_id;
      DELETE FROM message WHERE id=OLD.message_id;
    END IF;
    IF (SELECT count(*) FROM message_post WHERE member_id=OLD.member_id AND message_id=OLD.message_id) = 0 THEN
      DELETE FROM message_member WHERE member_id=OLD.member_id AND message_id=OLD.message_id;
    END IF;
    RETURN OLD;
  ELSEIF TG_OP = 'INSERT' THEN
    UPDATE
      message
    SET
      posts=posts+1,
      first_post_id=(SELECT id FROM message_post WHERE message_id=NEW.message_id ORDER BY date_posted ASC LIMIT 1),
      last_member_id=(SELECT member_id FROM message_post WHERE message_id=NEW.message_id ORDER BY date_posted DESC LIMIT 1),
      date_last_posted=now()
    WHERE
      id=NEW.message_id;
    IF NOT EXISTS (SELECT 1 FROM message_member WHERE member_id=NEW.member_id AND message_id=NEW.message_id) THEN
      INSERT INTO
        message_member (member_id,message_id,date_posted,last_view_posts)
      VALUES
        (NEW.member_id,NEW.message_id,now(),(SELECT posts FROM message WHERE id=NEW.message_id));
    ELSE
      UPDATE
        message_member
      SET
        date_posted=now(),
        last_view_posts=(SELECT posts FROM message WHERE id=NEW.message_id)
      WHERE
        member_id=NEW.member_id
      AND
        message_id=NEW.message_id;
    END IF;
    RETURN NEW;
  END IF;
  RETURN NULL;
END;
$$ LANGUAGE plpgsql;


CREATE OR REPLACE FUNCTION message_member_sync() RETURNS trigger AS $$
BEGIN
  IF TG_OP = 'UPDATE' THEN
    IF NEW.deleted IS TRUE THEN
      IF (SELECT count(*) FROM message_member WHERE message_id=OLD.message_id AND deleted IS false) < 1 THEN
        DELETE FROM message_member WHERE message_id=OLD.message_id;
        DELETE FROM message_post WHERE id=OLD.message_id;
      END IF;
    END IF;
  END IF;
  RETURN NULL;
END;
$$ LANGUAGE plpgsql;


CREATE OR REPLACE FUNCTION join(varchar,anyarray) RETURNS varchar AS $$
DECLARE
  sep ALIAS FOR $1;
  arr ALIAS FOR $2;
  buf varchar;
BEGIN
  buf := '';

  FOR i IN COALESCE(array_lower(arr,1),0)..COALESCE(array_upper(arr,1),-1) LOOP
    buf := buf || arr[i];
    IF i < array_upper(arr, 1) THEN
      buf := buf || sep;
    END IF;
  END LOOP;

  RETURN buf;
END;
$$ LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION indexOf(anyelement,anyarray) RETURNS integer AS $$
DECLARE
  search ALIAS FOR $1;
  arr ALIAS FOR $2;
BEGIN
  FOR i IN COALESCE(array_lower(arr,1),0)..COALESCE(array_upper(arr,1),-1) LOOP
    IF arr[i] = search THEN
      RETURN i;
    END IF;
  END LOOP;
  RETURN NULL;
END;
$$ LANGUAGE plpgsql;
