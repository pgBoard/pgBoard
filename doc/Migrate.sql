-- Start Exporting
pg_dump -a -O -t member DB > member.sql
pg_dump -a -O -t pref_old DB > pref_old.sql
pg_dump -a -O -t member_pref_old DB > member_pref_old.sql
pg_dump -a -O -t thread DB > thread.sql
pg_dump -a -O -t thread_post DB > thread_post.sql
pg_dump -a -O -t thread_member DB > thread_member.sql
pg_dump -a -O -t message DB > message.sql
pg_dump -a -O -t message_post DB > message_post.sql
pg_dump -a -O -t message_member DB > message_member.sql
pg_dump -a -O -t favorite DB > favorite.sql
pg_dump -a -O -t chat DB > chat.sql
tar cvzf board.tar.gz *.sql
-- End Exporting

-- Start Importing
tar xvzf board.tar.gz
psql DB < 1-Schema.sql
psql DB < member.sql
psql DB < member_pref.sql
psql DB < thread.sql
psql DB < thread_post.sql
psql DB < message.sql
psql DB < message_post.sql
psql DB < favorite.sql
psql DB < chat.sql

psql DB < 2-Functions.sql
psql DB < 3-Indexes-FKeys-Triggers.sql
-- End Importing

-- merge users together
-- UPDATE thread_post SET member_id=EXISTING WHERE member_id=OLD;
-- UPDATE thread SET member_id=EXISTING WHERE member_id=OLD;
-- DELETE from member_pref WHERE member_id=OLD;
-- DELETE FROM thread_member WHERE member_id=OLD OR member_id=EXISTING;
-- INSERT INTO thread_member (member_id,thread_id,date_posted,last_view_posts) SELECT DISTINCT ON (tp.member_id,tp.thread_id) tp.member_id,tp.thread_id,tp.date_posted,t.post FROM thread_post tp LEFT JOIN thread t ON t.id=tp.thread_id WHERE tp.member_id=EXISTING;
-- end merge users together

-- Start Cleanup
UPDATE board_data set value=(SELECT count(*) FROM member) WHERE name='total_members';
UPDATE board_data set value=(SELECT count(*) FROM thread) WHERE name='total_threads';
UPDATE board_data set value=(SELECT count(*) FROM thread_post) WHERE name='total_thread_posts';

UPDATE member set total_thread_posts=(SELECT count(*) FROM thread_post WHERE member_id=member.id);
UPDATE member set total_threads=(SELECT count(*) FROM thread WHERE member_id=member.id);
UPDATE member set last_post=(SELECT date_posted FROM thread_post WHERE member_id=member.id ORDER BY date_posted DESC LIMIT 1);

UPDATE thread set posts=(SELECT count(*) FROM thread_post WHERE thread_id=thread.id);
UPDATE thread SET first_post_id = (SELECT min(id) FROM thread_post WHERE thread_id=thread.id GROUP BY thread_id);
INSERT INTO thread_member (member_id,thread_id,date_posted,last_view_posts) SELECT DISTINCT ON (tp.member_id,tp.thread_id) tp.member_id,tp.thread_id,tp.date_posted,t.posts FROM thread_post tp LEFT JOIN thread t ON t.id=tp.thread_id;
-- End Cleanup
