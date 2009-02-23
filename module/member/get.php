<?php
function view_get()
{
  global $DB,$Core,$Parse;
  if(!id() && !session('id')) return to_index();
  if(!id()) set_cmd(2,session('name'));

  // if member name is a number, overrride lookups
  if(is_numeric(id()))
  if($search = $Core->idfromname(id())) $field = "m.id";

  if(!isset($field))
  {
    if($search = id(true)) $field = "m.id";
    else
    {
      $field = "LOWER(m.name)";
      $search = strtolower(id());
    }
  }

  $DB->query("SELECT
                *,
                extract(epoch from date_joined) as date_joined,
                extract(epoch from last_view) as last_view,
                extract(epoch from last_post) as last_post
              FROM
                member m
              WHERE
                $field=$1",array($search));
  $member = $DB->load_array();

  $DB->query("SELECT
                p.display as id,
                mp.value as name
              FROM
                member_pref mp
              LEFT JOIN
                pref p
              ON
                p.id = mp.pref_id
              WHERE
                mp.member_id=$1
              AND
                p.profile IS true
              ORDER BY
                ordering",array($member['id']));

  $pref = $DB->load_all_key();

  $Base = new Base;
  $Base->title($member['name']);
  $Base->type(VIEW_MEMBER);

  $Base->header();

  // show profile photo (if any)
  print "<div class=\"box clear\">\n";
  if($photo = $DB->value("SELECT value FROM member_pref WHERE pref_id=1 AND member_id=$1",array($member['id'])))
  {
    print $Parse->run("[img]{$photo}[/img]");
  }
  else
  print "<div class=\"nophoto\"></div>";

  // admin controllable member data
  print "<ul class=\"memberinfo\">\n";
  foreach($pref as $key => $value)
  {
    if($key == "email" && $Core->member_pref($member['id'],"show_email") != 'true') continue;
    $value = $Parse->run($value);
    print "  <li><div class=\"pref\">$key:</div> <div class=\"prefdata\">$value</div></li>\n";
  }

  print "<li style=\"padding-top:15px\">\n";
  print "  <div class=\"pref\">date joined:</div>\n";
  print "  <div class=\"prefdata\">".date(VIEW_DATE_FORMAT,$member['date_joined'])."</div>\n";
  print "</li>\n";

  print "<li>\n";
  print "  <div class=\"pref\">last posted:</div>\n";
  print "  <div class=\"prefdata\">".date(VIEW_DATE_FORMAT,$member['last_post'])."</div>\n";
  print "</li>\n";

  print "<li>\n";
  print "  <div class=\"pref\">last seen:</div>\n";
  print "  <div class=\"prefdata\">".date(VIEW_DATE_FORMAT,$member['last_view'])."</div>\n";
  print "</li>\n";

  print "<li>\n";
  print "  <div class=\"pref\">member:</div>\n";
  print "  <div class=\"prefdata\">$member[id]</div>\n";
  print "</li>\n";
  
  // total threads
  $threads_percent = 0;
  $total_threads = $Core->thread_count();
  if($member['total_threads']) $threads_percent = round(($member['total_threads']/$total_threads*100),3);
  print "<li style=\"padding-top:15px\">\n";
  print "  <div class=\"pref\">total threads:</div>\n";
  print "  <div class=\"prefdata\"><strong>".number_format($member['total_threads'])."</strong>, $threads_percent% of ".number_format($total_threads)."</div>\n";
  print "</li>\n";

  // total thread posts
  $thread_posts_percent= 0;
  $total_thread_posts = $Core->thread_post_count();
  if($member['total_thread_posts']) $thread_posts_percent = round(($member['total_thread_posts']/$total_thread_posts*100),3);
  print "<li>\n";
  print "  <div class=\"pref\">total posts:</div>\n";
  print "  <div class=\"prefdata\"><strong>".number_format($member['total_thread_posts'])."</strong>, $thread_posts_percent% of ".number_format($total_thread_posts)."</div>\n";
  print "</li>\n";

  if(IGNORE_ENABLED)
  {
    if(IGNORE_PUBLIC || $member['id'] == session('id'))
    {
      // ignoring
      if($member['id'] == session('id')) $listen = " <sup><a href=\"/member/listen/%name%/".MD5(session_id())."/\">x</a></sup>";
      else
      $listen = "";

      $ignoring = "";
      foreach($Core->list_ignored($member['id']) as $id => $name)
      {
        $l = str_replace("%name%",$name,$listen);
        $ignoring .= $l.$Core->member_link($name).", ";
      }
      if($ignoring == "") $ignoring = "-";
      else
      $ignoring = substr($ignoring,0,-2);
      print "<li style=\"padding-top:15px\">\n";
      print "  <div class=\"pref\">ignoring:</div>\n";
      print "  <div class=\"prefdata\">$ignoring</div>\n";
      print "</li>\n";
    }
    
    if(IGNORE_PUBLIC)
    {
      // ignored by
      $ignoredby = "";
      foreach($Core->list_ignoredby($member['id']) as $id => $name) $ignoredby .= $Core->member_link($name).", ";
      if($ignoredby == "") $ignoredby = "-";
      else
      $ignoredby = substr($ignoredby,0,-2);
      print "<li>\n";
      print "  <div class=\"pref\">ignored by:</div>\n";
      print "  <div class=\"prefdata\">$ignoredby</div>\n";
      print "</li>\n";
    }
  }

  print "</ul>\n";
  print "<div class=\"clear\"></div>\n";
  print "</div>\n";
  $Base->footer();
}

function ignore_get()
{
  global $Security,$Core,$DB;

  if(cmd(3) != MD5(session_id()) || !IGNORE_ENABLED) return to_index();
  if($Core->idfromname(id()) == session('id')) return to_index();
  
  if(!$listen = $Core->idfromname(id()))
  {
    $Base = new Base;
    $Base->type(ERROR);
    $Base->title(ERROR_MEMBER_NOTFOUND);
    $Base->header();
    $Base->footer();
    return;
  }

  if($Security->is_admin($listen) || !session('id')) return to_index();

  $insert = array();
  $insert['member_id'] = session('id');
  $insert['ignore_member_id'] = $listen;
  if($DB->insert("member_ignore",$insert)) return to_index();
  else
  print "<h3>Something got fucked.</h3>\n";
}

function listen_get()
{
  global $Security,$Core,$DB;

  if(cmd(3) != MD5(session_id())) return to_index();
  
  if(!$listen = $Core->idfromname(id()))
  {
    $Base = new Base;
    $Base->type(ERROR);
    $Base->title(ERROR_MEMBER_NOTFOUND);
    $Base->header();
    $Base->footer();
    return;
  }

  if($Security->is_admin($listen)  || !session('id')) return to_index();

  if($DB->query("DELETE FROM member_ignore WHERE member_id=$1 AND ignore_member_id=$2",array(session('id'),$listen)))
  {
    return to_index();
  }
  else
  print "<h3>Something got fucked.</h3>\n";
}
?>
