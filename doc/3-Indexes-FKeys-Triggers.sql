-- start member
CREATE UNIQUE INDEX member_name_lower_index ON member(REPLACE(LOWER(name),' ',''));
CREATE UNIQUE INDEX member_email_signup_lower_index ON member(LOWER(email_signup));
CREATE INDEX member_last_post_index ON member(last_post);
CREATE INDEX member_last_view_index ON member(last_view);

CREATE TRIGGER member_sync AFTER INSERT OR DELETE ON member
  FOR EACH ROW EXECUTE PROCEDURE member_sync();
-- end member


-- start member_pref
ALTER TABLE member_pref ADD FOREIGN KEY (pref_id) REFERENCES pref(id);
ALTER TABLE member_pref ADD FOREIGN KEY (member_id) REFERENCES member(id);
CREATE UNIQUE INDEX mp_mi_pi_index ON member_pref(member_id,pref_id);
-- end member_pref


--start member_ignore
CREATE UNIQUE INDEX mi_mi_imi_index ON member_ignore(member_id,ignore_member_id);
ALTER TABLE member_ignore ADD FOREIGN KEY (member_id) REFERENCES member(id);
ALTER TABLE member_ignore ADD FOREIGN KEY (ignore_member_id) REFERENCES member(id);
-- end member_ignore


-- start thread
CREATE INDEX thread_member_id_index ON thread(member_id);
CREATE INDEX thread_sticky_index ON thread(sticky);
CREATE INDEX thread_date_last_posted_index ON thread(date_last_posted);
CREATE INDEX thread_indexed_index ON thread(indexed);
CREATE INDEX thread_edited_index ON thread(edited);
CREATE INDEX thread_deleted_index ON thread(deleted);
CLUSTER thread_date_last_posted_index ON thread;

ALTER TABLE thread ADD FOREIGN KEY (member_id) REFERENCES member(id);
ALTER TABLE thread ADD FOREIGN KEY (last_member_id) REFERENCES member(id);

CREATE TRIGGER thread_sync AFTER INSERT OR DELETE ON thread
  FOR EACH ROW EXECUTE PROCEDURE thread_sync();
-- end thread


-- start thread_post
CREATE INDEX thread_post_member_id_index ON thread_post(member_id);
CREATE INDEX thread_post_thread_id_index ON thread_post(thread_id);
CREATE INDEX thread_post_date_posted_index ON thread_post(date_posted);
CREATE INDEX thread_post_member_ip ON thread_post(member_ip);
CREATE INDEX thread_post_indexed_index ON thread_post(indexed);
CREATE INDEX thread_post_edited_index ON thread_post(edited);
CREATE INDEX thread_post_deleted_index ON thread_post(deleted);

ALTER TABLE thread_post ADD FOREIGN KEY (member_id) REFERENCES member(id);
ALTER TABLE thread_post ADD FOREIGN KEY (thread_id) REFERENCES thread(id);

CREATE TRIGGER thread_post_sync AFTER INSERT OR DELETE ON thread_post
  FOR EACH ROW EXECUTE PROCEDURE thread_post_sync();
-- end thread_post


-- start thread_member
CREATE UNIQUE INDEX thread_member_member_id_thread_id ON thread_member(member_id,thread_id);
CREATE UNIQUE INDEX tm_mi_mi_lvr ON thread_member(member_id,thread_id,last_view_posts);
CREATE INDEX thread_member_member_id_date_posted ON thread_member(member_id,date_posted);

ALTER TABLE thread_member ADD FOREIGN KEY (member_id) REFERENCES member(id);
ALTER TABLE thread_member ADD FOREIGN KEY (thread_id) REFERENCES thread(id);
-- end thread_member


-- start message
CREATE INDEX message_member_id_index ON message(member_id);
CREATE INDEX message_date_last_posted_index ON message(date_last_posted);
CLUSTER message_date_last_posted_index ON message;

ALTER TABLE message ADD FOREIGN KEY (member_id) REFERENCES member(id);
ALTER TABLE message ADD FOREIGN KEY (last_member_id) REFERENCES member(id);
-- end message


-- start message_member
CREATE UNIQUE INDEX message_member_member_id_message_id ON message_member(member_id,message_id);
CREATE UNIQUE INDEX mm_mi_mi_lvr ON message_member(member_id,message_id,last_view_posts);

ALTER TABLE message_member ADD FOREIGN KEY (member_id) REFERENCES member(id);
ALTER TABLE message_member ADD FOREIGN KEY (message_id) REFERENCES message(id);

CREATE TRIGGER message_post_sync AFTER INSERT OR DELETE OR UPDATE ON message_member
  FOR EACH ROW EXECUTE PROCEDURE message_member_sync();
-- end message_member


-- start message_post
CREATE INDEX message_post_member_id_index ON message_post(member_id);
CREATE INDEX message_post_message_id_index ON message_post(message_id);
CREATE INDEX message_post_date_posted_index ON message_post(date_posted);
CREATE INDEX message_post_member_ip ON message_post(member_ip);

ALTER TABLE message_post ADD FOREIGN KEY (member_id) REFERENCES member(id);
ALTER TABLE message_post ADD FOREIGN KEY (message_id) REFERENCES message(id);

CREATE TRIGGER message_post_sync AFTER INSERT OR DELETE ON message_post
  FOR EACH ROW EXECUTE PROCEDURE message_post_sync();
-- end message_post
  

-- start favorites
CREATE INDEX favorite_member_id_thread_id_index ON favorite(member_id,thread_id);

ALTER TABLE favorite ADD FOREIGN KEY (member_id) REFERENCES member(id);
ALTER TABLE favorite ADD FOREIGN KEY (thread_id) REFERENCES thread(id);
-- end favorites


-- start chat
CREATE INDEX chat_stamp_index ON CHAT(stamp);

ALTER TABLE chat ADD FOREIGN KEY (member_id) REFERENCES member(id);
-- end chat


CREATE AGGREGATE array_accum
(
  sfunc = array_append,
  basetype = anyelement,
  stype = anyarray,
  initcond = '{}'
);
