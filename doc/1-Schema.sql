CREATE TABLE board_data
(
  id      serial PRIMARY KEY,
  name    text NOT NULL CHECK(name <> ''),  -- name of variable
  value   text NOT NULL CHECK(value <> ''), -- preference value
  UNIQUE(name)
);
INSERT INTO board_data (name,value) VALUES ('total_members',0);
INSERT INTO board_data (name,value) VALUES ('total_threads',0);
INSERT INTO board_data (name,value) VALUES ('total_thread_posts',0);


CREATE TABLE member
(
  id                   serial PRIMARY KEY,
  name                 varchar(25) NOT NULL CHECK(name <> ''),     -- login name
  pass                 char(32) NOT NULL CHECK(pass <> ''),        -- member password md5 hashed
  secret               char(32) NOT NULL CHECK(secret <> ''),      -- secret word for password recovery md5 hashed
  ip                   cidr NOT NULL,                              -- ip of member at last login
  date_joined          timestamp DEFAULT now(),                    -- date of signup
  email_signup         text NOT NULL CHECK(email_signup <> ''),    -- email used to sign up
  postalcode           text NOT NULL CHECK(postalcode <> ''),      -- member's postalcode
  total_threads        int DEFAULT 0,                              -- member's total threads created
  total_thread_posts   int DEFAULT 0,                              -- member's total posts
  last_view            timestamp,                                  -- last view of board
  last_post            timestamp,                                  -- last post to board
  last_chat            timestamp,                                  -- last time user chatted
  last_search          timestamp,                                  -- last time user searched
  banned               bool DEFAULT false,                         -- banned user?
  is_admin             bool DEFAULT false,                         -- is admin?
  cookie               char(32),
  session              char(32)
);

CREATE TABLE member_ignore
(
  member_id         int,
  ignore_member_id  int
);

CREATE TABLE pref_type
(
  id      serial PRIMARY KEY,
  name    text NOT NULL CHECK(name <> ''),
  UNIQUE(name)
);
INSERT INTO pref_type (name) VALUES ('input');
INSERT INTO pref_type (name) VALUES ('checkbox');
INSERT INTO pref_type (name) VALUES ('textarea');

CREATE TABLE pref
(
  id            serial PRIMARY KEY,
  pref_type_id  int NOT NULL REFERENCES pref_type(id),
  name          text NOT NULL CHECK(name <> ''),
  display       text NOT NULL CHECK(display <> ''),
  profile       bool NOT NULL DEFAULT false,
  session       bool NOT NULL DEFAULT false,
  editable      bool NOT NULL DEFAULT true,
  width         int,
  ordering      int,
  UNIQUE(name)
);
INSERT INTO pref VALUES (1,1,'photo','photo url',false,false,true,300,1);
INSERT INTO pref VALUES (2,1,'location','location',true,false,true,200,2);
INSERT INTO pref VALUES (3,1,'email','email',true,false,true,200,3);
INSERT INTO pref VALUES (4,1,'website','website',true,false,true,200,4);
INSERT INTO pref VALUES (5,1,'aim','aim',true,false,true,NULL,5);
INSERT INTO pref VALUES (6,1,'msn','msn',true,false,true,NULL,6);
INSERT INTO pref VALUES (7,1,'yahoo','yahoo',true,false,true,NULL,7);
INSERT INTO pref VALUES (8,1,'gtalk','gtalk',true,false,true,NULL,8);
INSERT INTO pref VALUES (9,1,'jabber','jabber',true,false,true,NULL,9);
INSERT INTO pref VALUES (10,3,'info','info',true,false,true,NULL,10);
INSERT INTO pref VALUES (11,2,'show_email','show email',false,false,true,NULL,12);
INSERT INTO pref VALUES (12,2,'hidemedia','hide media',false,true,true, NULL,13);
INSERT INTO pref VALUES (13,2,'ignore','soft ignore',false,true,true,NULL,14);
INSERT INTO pref VALUES (14,2,'nocollapse','disable collapsing',false,true,true,NULL,18);
INSERT INTO pref VALUES (15,3,'theme','theme',false,true,false,NULL,22);
INSERT INTO pref VALUES (17,2,'nofirstpost','hide firstpost arrow',false,true,true,NULL,15);
INSERT INTO pref VALUES (18,2,'italicread','italicize read posts',false,true,true,NULL,19);
INSERT INTO pref VALUES (19,2,'nopostnumber','hide posts #',false,true,true,NULL,20);
INSERT INTO pref VALUES (20,2,'notabs','hide nav tabs',false,true,true,NULL,21);
INSERT INTO pref VALUES (21,1,'mincollapse','<span class=''small''>min post # to collapse</span>',false,true,true,50,16);
INSERT INTO pref VALUES (22,1,'collapseopen','<span class=''small''># open after collapse (min 1)</span>',false,true,true,50,17);
INSERT INTO pref VALUES (23,1,'externalcss','external css<br/><span class=''small''>(may break color schemes)</span>',false,true,true,300,11);


CREATE TABLE member_pref
(
  id             serial PRIMARY KEY,
  pref_id        int NOT NULL,
  member_id      int NOT NULL,
  value          text NOT NULL CHECK(value <> '')
);

CREATE TABLE thread
(
  id                 serial PRIMARY KEY,
  member_id          int NOT NULL,                               -- id of member who created thread
  subject            varchar(200) NOT NULL CHECK(subject <> ''), -- subject of thread
  date_posted        timestamp not NULL DEFAULT now(),           -- date thread was created
  first_post_id      int,                                        -- first post id
  posts              int DEFAULT 0,                              -- total posts in a thread
  views              int DEFAULT 0,                              -- total views to thread
  sticky             bool DEFAULT false,                         -- thread sticky flag
  locked             bool DEFAULT false,                         -- thread locked flag
  last_member_id     int NOT NULL,                               -- last member who posted to thread
  date_last_posted   timestamp NOT NULL DEFAULT now(),           -- time last post was entered
  indexed            bool NOT NULL DEFAULT false,                -- has been indexed: for search indexer
  edited             bool NOT NULL DEFAULT false,                -- has been edited: for search indexer
  deleted            bool NOT NULL DEFAULT false,                -- flagged for deletion: for search indexer
  legendary          bool NOT NULL DEFAULT false
);

CREATE TABLE thread_post
(
  id            serial PRIMARY KEY,
  thread_id     int NOT NULL,                  -- thread post belongs to
  date_posted   timestamp DEFAULT now(),       -- time this post was created
  member_id     int NOT NULL,                  -- id of member who created post
  member_ip     cidr NOT NULL,                 -- current ip address of member who created post
  indexed       bool NOT NULL DEFAULT false,   -- has been indexed by search indexer
  edited        bool NOT NULL DEFAULT false,   -- has been edited: for search indexer
  deleted       bool NOT NULL DEFAULT false,   -- flagged for deletion: for search indexer
  body          text                           -- body text of post
);

CREATE TABLE thread_member
(
  member_id	        int NOT NULL,
  thread_id	        int NOT NULL,
  date_posted       timestamp,
  last_view_posts   int NOT NULL DEFAULT 0
);


CREATE TABLE message
(
  id                 serial PRIMARY KEY,
  member_id          int NOT NULL,                               -- id of member who created message
  subject            varchar(200) NOT NULL CHECK(subject <> ''), -- subject of message
  first_post_id      int,                                        -- first post id for message
  date_posted        timestamp NOT NULL DEFAULT now(),           -- date message was created
  posts              int DEFAULT 0,                              -- total posts in a message
  views              int DEFAULT 0,                              -- total views to message
  last_member_id     int NOT NULL,                               -- last member to reply
  date_last_posted   timestamp NOT NULL DEFAULT now()
);

CREATE TABLE message_post
(
  id            serial PRIMARY KEY,
  message_id    int NOT NULL,            -- message post belongs to
  date_posted   timestamp DEFAULT now(), -- time message post was created
  member_id     int NOT NULL,            -- id of member who created message post
  member_ip     cidr NOT NULL,           -- current ip address of member who created message post
  body          text                     -- body text of message post
);

CREATE TABLE message_member
(
  member_id	        int NOT NULL,
  message_id	      int NOT NULL,
  date_posted       timestamp,
  last_view_posts   int NOT NULL DEFAULT 0,
  deleted           bool NOT NULL DEFAULT false
);

CREATE TABLE favorite
(
  id          serial PRIMARY KEY,
  member_id   int NOT NULL,       -- member who this watched thread belongs to
  thread_id   int NOT NULL        -- thread member is watching
);

CREATE TABLE chat
(
  id         serial PRIMARY KEY,
  member_id  int NOT NULL,
  stamp      timestamp DEFAULT now(),
  chat       text
);

CREATE TABLE theme
(
  id      serial PRIMARY KEY,
  name    text NOT NULL CHECK(name <> ''),
  value   text,
  main    bool NOT NULL DEFAULT false,
  UNIQUE(name)
);
INSERT INTO theme (main,name,value) VALUES (true,'blue','a:9:{s:4:"font";s:37:"verdana, helvetica, arial, sans-serif";s:8:"fontsize";s:3:"1.1";s:4:"body";s:7:"#333333";s:4:"even";s:7:"#c3dae4";s:3:"odd";s:7:"#acccdb";s:2:"me";s:7:"#82b3c9";s:5:"hover";s:7:"#82b3c9";s:7:"readbar";s:7:"#3488ab";s:4:"menu";s:7:"#555555";}');
INSERT INTO theme (name,value) VALUES ('simple','a:9:{s:4:"font";s:37:"verdana, helvetica, arial, sans-serif";s:8:"fontsize";s:3:"1.1";s:4:"body";s:7:"#ffffff";s:4:"even";s:7:"#cccccc";s:3:"odd";s:7:"#eeeeee";s:2:"me";s:7:"#82b3c9";s:5:"hover";s:7:"#82b3c9";s:7:"readbar";s:7:"#82b3c9";s:4:"menu";s:7:"#555555";}');
INSERT INTO theme (name,value) VALUES ('gray','a:9:{s:4:"font";s:37:"verdana, helvetica, arial, sans-serif";s:8:"fontsize";s:3:"1.1";s:4:"body";s:7:"#555555";s:4:"even";s:7:"#d7d7d7";s:3:"odd";s:7:"#c9c9c9";s:2:"me";s:7:"#adadad";s:5:"hover";s:7:"#adadad";s:7:"readbar";s:7:"#333333";s:4:"menu";s:7:"#555555";}');
INSERT INTO theme (name,value) VALUES ('white','a:9:{s:4:"font";s:37:"verdana, helvetica, arial, sans-serif";s:8:"fontsize";s:3:"1.1";s:4:"body";s:7:"#ffffff";s:4:"even";s:7:"#cccccc";s:3:"odd";s:7:"#eeeeee";s:2:"me";s:7:"#999999";s:5:"hover";s:7:"#999999";s:7:"readbar";s:7:"#666666";s:4:"menu";s:7:"#555555";}');
INSERT INTO theme (name,value) VALUES ('black','a:9:{s:4:"font";s:37:"verdana, helvetica, arial, sans-serif";s:8:"fontsize";s:3:"1.1";s:4:"body";s:7:"#000000";s:4:"even";s:7:"#bbbbbb";s:3:"odd";s:7:"#dddddd";s:2:"me";s:7:"#666666";s:5:"hover";s:7:"#666666";s:7:"readbar";s:7:"#555555";s:4:"menu";s:7:"#000000";}');
INSERT INTO theme (name,value) VALUES ('purple','a:9:{s:4:"font";s:37:"verdana, helvetica, arial, sans-serif";s:8:"fontsize";s:3:"1.1";s:4:"body";s:7:"#333333";s:4:"even";s:7:"#bebde9";s:3:"odd";s:7:"#a6a5e1";s:2:"me";s:7:"#7978d2";s:5:"hover";s:7:"#7978d2";s:7:"readbar";s:7:"#5553ae";s:4:"menu";s:7:"#555555";}');
INSERT INTO theme (name,value) VALUES ('green','a:9:{s:4:"font";s:37:"verdana, helvetica, arial, sans-serif";s:8:"fontsize";s:3:"1.1";s:4:"body";s:7:"#333333";s:4:"even";s:7:"#d4f0be";s:3:"odd";s:7:"#c5eba7";s:2:"me";s:7:"#a8e07a";s:5:"hover";s:7:"#a8e07a";s:7:"readbar";s:7:"#3e8c00";s:4:"menu";s:7:"#555555";}');
INSERT INTO theme (name,value) VALUES ('orange','a:9:{s:4:"font";s:37:"verdana, helvetica, arial, sans-serif";s:8:"fontsize";s:3:"1.1";s:4:"body";s:7:"#333333";s:4:"even";s:7:"#e0c18b";s:3:"odd";s:7:"#dbb878";s:2:"me";s:7:"#d1a453";s:5:"hover";s:7:"#d1a453";s:7:"readbar";s:7:"#a36d00";s:4:"menu";s:7:"#555555";}');
INSERT INTO theme (name,value) VALUES ('red','a:9:{s:4:"font";s:37:"verdana, helvetica, arial, sans-serif";s:8:"fontsize";s:3:"1.1";s:4:"body";s:7:"#333333";s:4:"even";s:7:"#a22626";s:3:"odd";s:7:"#ae2929";s:2:"me";s:7:"#7e0101";s:5:"hover";s:7:"#7e0101";s:7:"readbar";s:7:"#111111";s:4:"menu";s:7:"#555555";}');
INSERT INTO theme (name,value) VALUES ('halloween','a:9:{s:4:"font";s:37:"verdana, helvetica, arial, sans-serif";s:8:"fontsize";s:3:"1.1";s:4:"body";s:7:"#333333";s:4:"even";s:7:"#eaa61e";s:3:"odd";s:7:"#f4b028";s:2:"me";s:7:"#000000";s:5:"hover";s:7:"#000000";s:7:"readbar";s:7:"#a36d00";s:4:"menu";s:7:"#555555";}');

CREATE TABLE fundraiser
(
  id     serial PRIMARY KEY,
  name   text,
  goal   money
);

CREATE TABLE donation
(
  id              serial PRIMARY KEY,
  fundraiser_id   int NOT NULL REFERENCES fundraiser(id),
  payment_date    date NOT NULL DEFAULT now(),
  payment_status  text,
  payer_email     text,
  txn_id          text,
  payment_fee     money,
  payment_gross   money
);
