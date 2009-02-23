<?php
class BoardCore
{
  public $data;
  
  function command_parse()
  {
    global $DB,$Core,$Parse,$Security,$Base,$Style;
    if(!$Security->allowed()) return;
    
    $include = implode("/",module());
    if(file_exists("module/{$include}/main.php"))
    {
      $dir = "";
      foreach(module() as $module)
      {
        $dir .= "$module/";
        $shared = "module/{$dir}shared.php";
        if(file_exists($shared)) include($shared);
      }
      require_once("module/{$include}/main.php");
      if(function_exists(command())) eval(command()."();");
      if(file_exists("module/{$include}/.content/".func().".php"))
      {
        if(!get('ajax')) require_once("module/{$include}/.content/".func().".php");
      }
    }
    else
    {
      $Base = new Base;
      $Base->title("Invalid Module");
      $Base->Header();
      $Base->Footer();
    }
  }

  function namefromid($id)
  {
    global $DB;
    if(!is_numeric($id)) return false;
    return $DB->value("SELECT name FROM member WHERE id=$1",array($id));
  }
  
  function idfromname($name)
  {
    global $DB;
    $name = str_replace(SPACE,"",strtolower($name));
    return $DB->value("SELECT id FROM member WHERE LOWER(REPLACE(name,' ',''))=$1",array($name));
  }
  
  function member_link($id)
  {
    if(is_int($id))
    {
      if(!$id = $this->namefromid($id)) return "";
    }
    /*
    $output = "<span class=\"dropmenu\">";
    $output .= "<a href=\"/member/view/$id/\" class=\"domenu memberlink\">".str_replace(SPACE,"&nbsp;",$id)."</a>";
    $output .= "<span class=\"control\" onclick=\"menuhandle(this)\">".ARROW_RIGHT."</span>";
    $output .= "</span>";
    */
    $output = "<a href=\"/member/view/$id/\" class=\"memberlink\">".str_replace(SPACE,"&nbsp;",$id)."</a>";
    return $output;
  }
  
  function member_pref($member_id,$name)
  {
    global $DB;
    return $DB->value("SELECT
                         mp.value
                       FROM
                         member_pref mp
                       LEFT JOIN
                         pref p
                       ON
                         p.id = mp.pref_id
                       WHERE
                         mp.member_id=$1
                       AND
                         LOWER(p.name)=LOWER($2)",array($member_id,$name));
  }
  
  function is_ignoring($member_id,$ignored)
  {
    global $DB;
    if(!is_numeric($ignored)) $ignored = $this->idfromname($ignored);
    if(!$ignored) return false;
    
    return $DB->check("SELECT true FROM member_ignore WHERE member_id=$1 AND ignore_member_id=$2",array($member_id,$ignored));
  }
  
  function list_ignored($member_id)
  {
    global $DB;
    $DB->query("SELECT
                  m.id,
                  m.name
                FROM
                  member_ignore mi
                LEFT JOIN
                  member m
                ON
                  m.id = mi.ignore_member_id
                WHERE
                  mi.member_id=$1
                ORDER BY
                  m.name",array($member_id));
    return $DB->load_all_key();
  }
  
  function list_ignoredby($member_id)
  {
    global $DB;
    $DB->query("SELECT
                  m.id,
                  m.name
                FROM
                  member_ignore mi
                LEFT JOIN
                  member m
                ON
                  m.id = mi.member_id
                WHERE
                  mi.ignore_member_id=$1
                ORDER BY
                  m.name",array($member_id));
    return $DB->load_all_key();
  }
  
  function message_unread_count($member_id)
  {
    global $DB;
    return $DB->value("SELECT
                         count(*)
                       FROM
                         message_member mm
                       WHERE
                         mm.member_id=$1
                       AND
                         mm.deleted IS false
                       AND
                         mm.last_view_posts=0",array($member_id));
  }
  
  /* there is a bug here, figure out how posts and last_view_posts get out of sync */
  function message_unread_post_count($member_id)
  {
    global $DB;
    return $DB->value("SELECT
                         sum(m.posts-mm.last_view_posts)
                       FROM
                         message_member mm
                       LEFT JOIN
                         message m
                       ON
                         m.id=mm.message_id
                       WHERE
                         mm.member_id=$1
                       AND
                         mm.deleted IS false",array($member_id));
  }
  
  function check_favorite($thread_id)
  {
    global $DB;
    if(!session('id')) return false;
    else
    return $DB->check("SELECT true FROM favorite WHERE thread_id=$1 AND member_id=$2",array($thread_id,session('id')));
  }
  
  function thread_count()
  {
    global $DB;
    return $DB->value("SELECT value FROM board_data where name='total_threads'");
  }
  
  function thread_post_count()
  {
    global $DB;
    return $DB->value("SELECT value FROM board_data where name='total_thread_posts'");
  }

  function member_count()
  {
    global $DB;
    return $DB->value("SELECT value FROM board_data where name='total_members'");
  }

  function active_member_count()
  {
    global $DB;
    return $DB->value("SELECT count(id) FROM member WHERE last_view BETWEEN now() - INTERVAL '5 minutes' AND now()");
  }

  function posting_member_count()
  {
    global $DB;
    return $DB->value("SELECT count(id) FROM member WHERE last_post BETWEEN now() - INTERVAL '5 minutes' AND now()");
  }

  function chatting_member_count()
  {
    global $DB;
    return $DB->value("SELECT count(id) FROM member WHERE last_chat BETWEEN now() - INTERVAL '5 minutes' AND now()");
  }

  function lurking_member_count()
  {
    global $DB;
    return $DB->value("SELECT count(id) FROM member WHERE last_post < now()-INTERVAL '3 day' AND last_view BETWEEN now() - INTERVAL '5 minutes' AND now()");
  }

  function active_members()
  {
    global $DB;
    $DB->query("SELECT id,name FROM member WHERE last_view BETWEEN now() - INTERVAL '5 minutes' AND now() ORDER BY name");
    return $DB->load_all_key();
  }
  
  function posting_members()
  {
    global $DB;
    $DB->query("SELECT id,name FROM member WHERE last_post BETWEEN now() - INTERVAL '5 minutes' AND now() ORDER BY name");
    return $DB->load_all_key();
  }

  function chatting_members()
  {
    global $DB;
    $DB->query("SELECT id,name FROM member WHERE last_chat BETWEEN now() - INTERVAL '5 minutes' AND now() ORDER BY name");
    return $DB->load_all_key();
  }
  
  function lurking_members()
  {
    global $DB;
    $DB->query("SELECT id,name FROM member WHERE last_post < now()-INTERVAL '3 day' AND last_view BETWEEN now() - INTERVAL '5 minutes' AND now() ORDER BY name");
    return $DB->load_all_key();
  }

  function fundraiser_name()
  {
    global $DB;
    return $DB->value("SELECT name FROM fundraiser WHERE id=$1",array(FUNDRAISER_ID));
  }

  function fundraiser_goal()
  {
    global $DB;
    return $DB->value("SELECT goal FROM fundraiser WHERE id=$1",array(FUNDRAISER_ID));
  }
  
  function fundraiser_total()
  {
    global $DB;
    return $DB->value("SELECT
                         COALESCE(sum(payment_gross-payment_fee),'$0')
                       FROM
                         donation
                       WHERE
                         fundraiser_id=$1
                       AND
                         payment_status  = 'Completed'",array(FUNDRAISER_ID));
  }
}
