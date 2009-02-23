<?php
/*
* list_query constants
**/
define("LIST_ID",0);
define("LIST_DATE_LAST_POST",1);
define("LIST_CREATOR_ID",2);
define("LIST_CREATOR_NAME",3);
define("LIST_LAST_POSTER_ID",4);
define("LIST_LAST_POSTER_NAME",5);
define("LIST_SUBJECT",6);
define("LIST_POSTS",7);
define("LIST_VIEWS",8);
define("LIST_FIRSTPOST_BODY",9);
define("LIST_LAST_VIEW_POSTS",10);
define("LIST_DOTFLAG",11);
define("LIST_STICKY",12);
define("LIST_LOCKED",13);
define("LIST_LEGENDARY",14);

/*
* view_query constants
**/
define("VIEW_ID",0);
define("VIEW_DATE_POSTED",1);
define("VIEW_CREATOR_ID",2);
define("VIEW_CREATOR_NAME",3);
define("VIEW_BODY",4);
define("VIEW_CREATOR_IP",5);
define("VIEW_SUBJECT",6);
define("VIEW_THREAD_ID",7);
define("VIEW_CREATOR_IS_ADMIN",8);

/**
* Builds SQL queries for board usage
*
* @classDescription	This class builds SQL queries.
* @return {SQL}
* @type {Object}
* @constructor
*/
class BoardQuery
{
  /**
  * build thread listing query
  */
  function list_thread($sticky=false,$offset,$limit,$threads=false,$cond=false)
  {
    global $Core;
    
    // set query conditionals
    $where = "WHERE t.sticky IS false";
    $order = "ORDER BY t.date_last_posted DESC";
    $offset = $this->list_offset($offset);
    $limit = $this->list_limit($limit);
    $ignore = "";

    if(session('id'))
    if($list = array_keys($Core->list_ignored(session('id'))))
    {
      $list = implode(",",$list);
      $ignore = "AND m.id NOT IN ($list)";
    }

    // set query conditionals for stickies only
    if($sticky)
    {
      $where = "WHERE t.sticky IS true";
      $offset = $limit = "";
    }

    // set query conditionals if an array of threads are defined
    if($threads)
    {
      $threads = implode(",",$threads);
      $where = "WHERE t.id in ($threads)";
      $order = "ORDER BY indexOf(t.id,ARRAY[$threads])";
    }
    
    if($cond) $where = $cond;
    
    return "SELECT
              t.id as thread,
              extract(epoch from t.date_last_posted) as date_last_posted,
              m.id,
              m.name,
              l.id as lastid,
              l.name as lastname,
              t.subject,
              t.posts,
              t.views,
              tp.body,
              (CASE WHEN tm.last_view_posts IS null THEN 0 ELSE tm.last_view_posts END) as last_view_posts,
              (CASE WHEN tm.date_posted IS NOT null AND tm.member_id IS NOT null THEN 't' ELSE 'f' END) as dot,
              t.sticky,
              t.locked,
              t.legendary
            FROM
              thread t
            LEFT JOIN
              member m
            ON
              m.id=t.member_id
            LEFT JOIN
              member l
            ON
              l.id=t.last_member_id
            LEFT JOIN
              thread_post tp
            ON
              tp.id=t.first_post_id
            LEFT OUTER JOIN
              thread_member tm
            ON
              (tm.member_id=".(session('id')?session('id'):0)." AND tm.thread_id=t.id)
            $where
            $ignore
            $order
            $limit
            $offset";
  }
  function list_thread_bymember($member_id,$offset,$limit)
  {
    $cond = "WHERE t.member_id=$member_id";
    return $this->list_thread(false,$offset,$limit,array(),$cond);
  }

  /**
  * build thread view query
  */
  function view_thread($thread=false,$offset,$limit,$posts=false,$cond=false)
  {
    global $Core;
    
    // set query conditionals
    $where = "WHERE tp.thread_id=$thread";
    $order = "ORDER BY tp.date_posted ASC";
    $ignore = "";

    if(session('id'))
    if($list = array_keys($Core->list_ignored(session('id'))))
    {
      $list = implode(",",$list);
      $ignore = "AND m.id NOT IN ($list)";
    }

    // sort of hack clean this up (support posting histories)
    if($cond)
    {
      $offset = "OFFSET ".($offset?$offset:0)*LIST_DEFAULT_LIMIT;
      $limit = "LIMIT ".LIST_DEFAULT_LIMIT;
    }
    else
    {
      $offset = $this->view_offset($offset);
      $limit = $this->view_limit($limit);
    }

    // set query conditionals if an array of posts are defined
    if($posts)
    {
      $posts = implode(",",$posts);
      $where = "WHERE tp.id in ($posts)";
      $order = "ORDER BY indexOf(tp.id,ARRAY[$posts])";
    }
    if($cond)
    {
      $where = $cond;
      $order = "ORDER BY tp.date_posted DESC";
    }

    return "SELECT
              tp.id,
              extract(epoch from tp.date_posted) as date_posted,
              m.id as member_id,
              m.name,
              tp.body,
              tp.member_ip,
              t.subject,
              t.id as thread_id,
              m.is_admin
            FROM
              thread_post tp
            LEFT JOIN
              member m
            ON
              m.id=tp.member_id
            LEFT JOIN
              thread t
            ON
              t.id = tp.thread_id
            $where
            $ignore
            $order
            $limit
            $offset";
  }
  function view_thread_bymember($member_id,$offset,$limit)
  {
    $cond = "WHERE tp.member_id=$member_id";
    return $this->view_thread(false,$offset,$limit,false,$cond);
  }

  /**
  * build message list query
  */
  function list_message($offset,$limit,$messages=false)
  {
    global $Core;
  
    // set query conditionals
    $where = "WHERE";
    $order = "ORDER BY m.date_last_posted DESC";
    $offset = $this->list_offset($offset);
    $limit = $this->list_limit($limit);
    $ignore = "";

/*
    if(session('id'))
    if($list = array_keys($Core->list_ignored(session('id'))))
    {
      $list = implode(",",$list);
      $ignore = "AND mem.id NOT IN ($list)";
    }
*/

    // set query conditionals if an array of messages are defined
    if($messages)
    {
      $threads = implode(",",$threads);
      $where = "WHERE m.id in ($threads) AND";
      $order = "ORDER BY indexOf(t.id,ARRAY[$threads])";
    }

    return "SELECT
              m.id as message,
              extract(epoch from m.date_last_posted) as date_last_posted,
              mem.id,
              mem.name,
              l.id as lastid,
              l.name as lastname,
              m.subject,
              m.posts,
              m.views,
              mp.body,
              (CASE WHEN mm.last_view_posts IS null THEN 0 ELSE mm.last_view_posts END) as readbars,
              (CASE WHEN mm.date_posted IS NOT null AND mm.member_id IS NOT null THEN 't' ELSE 'f' END) as dot
            FROM
              message_member mm
            LEFT JOIN
              message m
            ON
              m.id = mm.message_id
            LEFT JOIN
              member mem
            ON
              mem.id=m.member_id
            LEFT JOIN
              member l
            ON
              l.id=m.last_member_id
            LEFT JOIN
              message_post mp
            ON
              mp.id=m.first_post_id
            $where
              mm.member_id = ".session('id')."
            AND
              mm.deleted IS false
            $ignore
            $order
            $limit
            $offset";
  }
  
  /**
  * build thread view query
  */
  function view_message($message=false,$offset=false,$limit=false,$posts=false)
  {
    global $Core;

    // set query conditionals
    $where = "WHERE mp.message_id=$message";
    $order = "ORDER BY mp.date_posted ASC";
    $offset = $this->view_offset($offset);
    $limit = $this->view_limit($limit);
    $ignore = "";

/*
    if(session('id'))
    if($list = array_keys($Core->list_ignored(session('id'))))
    {
      $list = implode(",",$list);
      $ignore = "AND mem.id NOT IN ($list)";
    }
*/

    // set query conditionals if an array of posts are defined
    if($posts)
    {
      $posts = implode(",",$posts);
      $where = "WHERE mp.id in ($posts)";
      $order = "ORDER BY indexOf(mp.id,ARRAY[$posts])";
    }

    return "SELECT
              mp.id,
              extract(epoch from mp.date_posted) as date_posted,
              mem.id as member_id,
              mem.name,
              mp.body,
              mp.member_ip,
              m.subject,
              m.id as message_id,
              mem.is_admin
            FROM
              message_post mp
            LEFT JOIN
              member mem
            ON
              mem.id=mp.member_id
            LEFT JOIN
              message m
            ON
              m.id = mp.message_id
            $where
            AND
              ".session('id')."
            IN
              (SELECT mm.member_id FROM message_member mm WHERE mm.message_id = mp.message_id)
            $ignore
            $order
            $limit
            $offset";
  }

  /**
  * helper functions for offsetting / limiting
  **/
  function list_limit($limit)
  {
    if($limit) return "LIMIT $limit";
    else
    return "LIMIT ".LIST_DEFAULT_LIMIT;
  }

  function list_offset($offset)
  {
    if($offset) return "OFFSET ".($offset*LIST_DEFAULT_LIMIT);
    else
    return "";
  }

  function view_limit($limit)
  {
    if($limit) return "LIMIT $limit";
    else
    return "";
  }

  function view_offset($offset)
  {
    if($offset) return "OFFSET $offset";
    else
    return "";
  }
}
