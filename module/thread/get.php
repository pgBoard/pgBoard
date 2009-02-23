<?php
function main_get() { list_get(); }

function list_get()
{
  global $DB,$Core,$_title_;
  
  $Query = new BoardQuery;
  $List = new BoardList;
  $List->type(LIST_THREAD);

  $_title_ = TITLE_BOARD;

  if(FUNDRAISER_ID != -1)
  {
    $goal = $Core->fundraiser_goal();
    $total = $Core->fundraiser_total();
    $remaining = number_format(str_replace(array("$",","),"",$goal)-str_replace(array("$",","),"",$total),2);
    $_title_ .=  " <span class=\"smaller\">&raquo; $$remaining left to raise!</span>";
  }
  $List->title($_title_);
  $List->header();

  // stickies
  $DB->query($Query->list_thread(true,false,false));
  $List->data($DB->load_all());
  $List->thread(true);
  
  // the rest
  $DB->query($Query->list_thread(false,cmd(2,true),cmd(3,true)));
  $List->data($DB->load_all());
  $List->thread();

  $List->footer();
}

function view_get()
{
  global $DB,$Core;
  
  if(!id(true)) return to_index();

  $Query = new BoardQuery;
  $View = new BoardView;
  $View->type(VIEW_THREAD);
  $View->increment_views();

  $subtitle = "";
  if(session('hidemedia'))
  {
    if(get('media')) $subtitle .= "<a href=\"".url()."\">hide images</a>";
    if(!get('media')) $subtitle .= "<a href=\"".url()."&media=true\">show images</a>";
  }
  if(!session('hidemedia'))
  {
    if(!get('media')) $subtitle .= "<a href=\"".url()."&media=true\">hide images</a>";
    if(get('media')) $subtitle .= "<a href=\"".url()."\">show images</a>";
  }

  if(session('id'))
  {
    if(!$Core->check_favorite(id())) $subtitle .= SPACE.ARROW_RIGHT.SPACE."<a href=\"javascript:;\" onclick=\"toggle_favorite(".id().");\"><span id=\"fcmd\">add</span> favorite</a>\n";
    else
    $subtitle .= SPACE.ARROW_RIGHT.SPACE."<a href=\"javascript:;\" onclick=\"toggle_favorite(".id().");\"><span id=\"fcmd\">remove</span> favorite</a>\n";
  }

  if(session('admin'))
  {
    $Admin = new BoardAdmin;
    $sticky = $Admin->check_flag("sticky",id());
    $locked = $Admin->check_flag("locked",id());
    $subtitle .= SPACE.ARROW_RIGHT.SPACE."<a href=\"/admin/togglesticky/".id()."\">".($sticky ? "unsticky" :"sticky")."</a>";
    $subtitle .= SPACE.ARROW_RIGHT.SPACE."<a href=\"/admin/togglelocked/".id()."\">".($locked ? "unlock" :"lock")."</a>";
  }
  $View->title($View->subject(id()));
  $View->subtitle($subtitle);

  $View->header();

  $DB->query($Query->view_thread(id(true),cmd(3,true),cmd(4,true)));
  $View->data($DB->load_all());
  $View->thread();

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

?>

