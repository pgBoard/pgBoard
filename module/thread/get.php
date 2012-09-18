<?php
function main_get() { list_get(); }

function list_get()
{
  global $DB,$Core,$_title_;

  // Container for XML data
  $xmldata = '';
  
  $Query = new BoardQuery;
  $List = new BoardList;
  $List->type(LIST_THREAD);

  $_title_ = TITLE_BOARD;

  if(FUNDRAISER_ID != -1)
  {
    $goal = $Core->fundraiser_goal();
    $total = $Core->fundraiser_total();
    $remaining = number_format(str_replace(array("$",","),"",$goal)-str_replace(array("$",","),"",$total),2);
    $days = round((strtotime("2011-02-01")-time())/86400)+round((substr($total,1)/6.53));
//    $_title_ .=  " <span class=\"smaller\">&raquo; $$remaining left to raise ($days days until bco shuts down)</span>";
    $_title_ .=  " <span class=\"smaller\">&raquo; $$remaining left to raise</span>";
  }
  $List->title($_title_);
  $List->header();



  // stickies
  $DB->query($Query->list_thread(true,false,false));
  $List->data($DB->load_all());
  if (get('xml')) {
    $xmldata .= $List->thread_xml(true);
  } else {
    $List->thread(true);
  }
  
  // the rest
  $DB->query($Query->list_thread(false,cmd(2,true),cmd(3,true)));
  $List->data($DB->load_all());
  if (get('xml')) {
    $xmldata .= $List->thread_xml();
  } else {
    $List->thread();
  }

  // Build XML output
  if (get('xml')) {
    header("Content-type: text/xml");
    print "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>\n";
    print "<threads>\n";
    print $xmldata;
    print "</threads>";
  }

  $List->footer();
}

function view_get()
{
  global $DB,$Core;

  $xmldata = '';
 
  if(!id(true)) return to_index();

  $Query = new BoardQuery;
  $View = new BoardView;
  $View->type(VIEW_THREAD);
  $View->increment_views();

  $subtitle="";

  // set flags for media link
  $flags="";
  if(!get('media')) $flags .= "&media=true";
  if(get('uncollapse')) $flags .= "&uncollapse=true";
  if(session('hidemedia'))
  {
    if(get('media')) $subtitle .= "<a href=\"".url()."$flags\">hide images</a>";
    if(!get('media')) $subtitle .= "<a href=\"".url()."$flags\">show images</a>";
  }
  if(!session('hidemedia'))
  {
    if(!get('media')) $subtitle .= "<a href=\"".url()."$flags\">hide images</a>";
    if(get('media'))  $subtitle .= "<a href=\"".url()."$flags\">show images</a>";
  }

  // set flags for collase link
  $flags="";
  if(!get('uncollapse')) $flags .= "&uncollapse=true";
  if(get('media')) $flags .= "&media=true";
  if(!session('nocollapse'))
  {
    if(!get('uncollapse')) $subtitle .= SPACE.ARROW_RIGHT.SPACE."<a href=\"".url()."$flags\">uncollapse</a>";
    if(get('uncollapse')) $subtitle .= SPACE.ARROW_RIGHT.SPACE."<a href=\"".url()."$flags\">collapse</a>";
  }

  if(session('id'))
  {
    if(!$Core->check_favorite(id())) $subtitle .= SPACE.ARROW_RIGHT.SPACE."<a href=\"javascript:;\" onclick=\"toggle_favorite(".id().");\"><span id=\"fcmd\">add</span> favorite</a>\n";
    else
    $subtitle .= SPACE.ARROW_RIGHT.SPACE."<a href=\"javascript:;\" onclick=\"toggle_favorite(".id().");\"><span id=\"fcmd\">remove</span> favorite</a>\n";

    if(!$Core->check_ignored_thread(id()))
      $subtitle .= SPACE.ARROW_RIGHT.SPACE."<a href=\"javascript:;\" onclick=\"toggle_ignore_thread(".id().");\"><span id=\"ignorecmd\"></span>ignore</a>\n";
    else
      $subtitle .= SPACE.ARROW_RIGHT.SPACE."<a href=\"javascript:;\" onclick=\"toggle_ignore_thread(".id().");\"><span id=\"ignorecmd\">un</span>ignore</a>\n";

    // undot
    if($Core->check_dotted(id())) $subtitle .= SPACE.ARROW_RIGHT.SPACE."<a href=\"javascript:;\" onclick=\"undot(".id().");\" id=\"undot\">undot</a>\n";
  }

  if(session('admin'))
  {
    $Admin = new BoardAdmin;
    $sticky = $Admin->check_flag("thread","sticky",id());
    $locked = $Admin->check_flag("thread","locked",id());
    $subtitle .= SPACE.ARROW_RIGHT.SPACE."<a href=\"/admin/togglesticky/".id()."/".md5(session_id())."/\">".($sticky ? "unsticky" :"sticky")."</a>";
    $subtitle .= SPACE.ARROW_RIGHT.SPACE."<a href=\"/admin/togglelocked/".id()."/".md5(session_id())."/\">".($locked ? "unlock" :"lock")."</a>";
  }
  $View->title($View->subject(id()));
  $View->subtitle($subtitle);

  $View->header();

  $DB->query($Query->view_thread(id(true),cmd(3,true),cmd(4,true)));
  $View->data($DB->load_all());
  if(get('xml'))
  {
    header("Content-type: text/xml");
    print "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>\n";
    print "<posts>\n";
    $View->thread_xml();
    print "</posts>";
  }
  else
  {
    if(get('uncollapse') && !session('nocollapse')) $_SESSION['nocollapse']=true;
    $View->thread();
    if(get('uncollapse')) unset($_SESSION['nocollapse']);
  }

  $View->footer();
  $View->member_update();
}

function firstpost_get()
{
  global $DB,$Parse;
  if(!id()) return;
  $body = $DB->value("SELECT body FROM thread_post WHERE thread_id=$1 ORDER BY date_posted LIMIT 1",array(id()));
  print $Parse->run($body);
}

function viewpost_get()
{
  global $DB;
  if(!id()) return;
  $body = $DB->value("SELECT body FROM thread_post WHERE id=$1",array(id()));
  print htmlentities($body);
}

function listbymember_get()
{
  global $DB,$Core;

  // get info
  $id = $Core->idfromname(id());
  $name = $Core->namefromid($id);
  $page = cmd(3,true)+1;
  
  if(!$id || !$name) return to_index();
  
  $Query = new BoardQuery;
  $List = new BoardList;
  $List->type(LIST_THREAD_HISTORY);

  $List->title("Threads Created: $name");
  $List->subtitle("page: $page");
  $List->header();

  $DB->query($Query->list_thread_bymember($id,cmd(3,true),cmd(4,true)));
  $List->data($DB->load_all());
  $List->thread();

  $List->footer();
}

function listbymemberposted_get()
{
  global $DB,$Core;

  // get info
  $id = $Core->idfromname(id());
  $name = $Core->namefromid($id);
  $page = cmd(3,true)+1;

  // get threads participiated in
  $DB->query("SELECT
                tm.thread_id
              FROM
                thread_member tm
              LEFT JOIN
                thread t
              ON
                t.id = tm.thread_id
              WHERE
                tm.member_id=$1
              AND
                tm.date_posted IS NOT null
              ORDER BY
                t.date_last_posted DESC",array($id));
  $threads = $DB->load_all('thread_id');

  if(!$id || !$name) return to_index();

  $Query = new BoardQuery;
  $List = new BoardList;
  $List->type(LIST_THREAD_HISTORY);

  $List->title("Threads Participated: $name");
  $List->subtitle("page: $page");
  $List->header();

  $DB->query($Query->list_thread(false,cmd(3,true),cmd(4,true),$threads));
  $List->data($DB->load_all());
  $List->thread();

  $List->footer();
}

function listfavoritesbymember_get()
{
  global $DB,$Core;

  // get info
  $id = $Core->idfromname(id());
  $name = $Core->namefromid($id);
  $page = cmd(3,true)+1;

  // get threads participiated in
  $DB->query("SELECT
                f.thread_id
              FROM
                favorite f
              LEFT JOIN
                thread t
              ON
                t.id = f.thread_id
              WHERE
                f.member_id=$1
              ORDER BY
                t.date_last_posted DESC",array($id));
  $threads = $DB->load_all('thread_id');

  if(!$id || !$name) return to_index();

  $Query = new BoardQuery;
  $List = new BoardList;
  $List->type(LIST_THREAD_HISTORY);

  $List->title("Favorites: $name");
  $List->subtitle("page: $page");
  $List->header();

  $DB->query($Query->list_thread(false,cmd(3,true),cmd(4,true),$threads));
  $List->data($DB->load_all());
  $List->thread();

  $List->footer();
}

function listignoredthreadsbymember_get()
{
  global $DB,$Core;

  // get info
  $id = $Core->idfromname(id());
  $name = $Core->namefromid($id);
  $page = cmd(3,true)+1;

  // get threads participiated in
  $DB->query("SELECT
                tm.thread_id
              FROM
                thread_member tm
              LEFT JOIN
                thread t
              ON
                t.id = tm.thread_id
              WHERE
                tm.member_id=$1 AND tm.ignore=true
              ORDER BY
                t.date_last_posted DESC",array($id));
  $threads = $DB->load_all('thread_id');
  if (!$threads) $threads = array(0);
  if(!$id || !$name) return to_index();

  $Query = new BoardQuery;
  $List = new BoardList;
  $List->type(LIST_THREAD_HISTORY);

  $List->title("Ignored threads: $name");
  $List->subtitle("page: $page");
  $List->header();

  $DB->query($Query->list_thread(false,cmd(3,true),cmd(4,true),$threads, false, false));
  $List->data($DB->load_all());
  $List->thread();

  $List->footer();
}

function viewbymember_get()
{
  global $DB,$Core;

  // get info
  $id = $Core->idfromname(id());
  $name = $Core->namefromid($id);
  $page = cmd(3,true)+1;

  if(!$id || !$name) return to_index();

  $Query = new BoardQuery;
  $View = new BoardView;
  $View->type(VIEW_THREAD_HISTORY);

  $View->title("Posts Created: $name");
  $View->subtitle("page $page");
  $View->header();
  $DB->query($Query->view_thread_bymember($id,cmd(3,true),cmd(4,true)));
  $View->data($DB->load_all());
  $View->thread();

  $View->footer();
}

function toggleignore_get()
{
  global $DB,$Core;

  if(!session('id'))
  {
    print "failed to change";
    exit_clean();
  }
  if($Core->check_ignored_thread(id()))
  {
    // It's ignored -- unignore it
    $DB->query("UPDATE thread_member SET ignore=false WHERE thread_id=$1 AND member_id=$2",array(id(),session('id')));
    print "";
    exit_clean();
  }
  else
  {
    $DB->query("UPDATE thread_member SET ignore=true WHERE thread_id=$1 AND member_id=$2",array(id(),session('id')));
    print "un";
    exit_clean();
  }
}

function togglefavorite_get()
{
  global $DB,$Core;
  if(!session('id'))
  {
    print "failed to change";
    exit_clean();
  }
  if($Core->check_favorite(id()))
  {
    $DB->query("DELETE FROM favorite WHERE thread_id=$1 AND member_id=$2",array(id(),session('id')));
    print "add";
    exit_clean();
  }
  else
  {
    $insert = array();
    $insert['thread_id'] = id();
    $insert['member_id'] = session('id');
    $DB->insert("favorite",$insert);
    print "remove";
    exit_clean();
  }
}

function undot_get()
{
  global $DB,$Core;
  if(!session('id'))
  {
    print "undot failed.";
    exit_clean();
  }
  $DB->query("UPDATE thread_member SET undot=true WHERE thread_id=$1 AND member_id=$2",array(id(),session('id')));
  print "undotted";
  exit_clean();
}

?>

