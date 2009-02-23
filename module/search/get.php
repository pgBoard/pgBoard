<?php
function thread_get()
{
  global $DB;

  $Search = new Search;

  $offset = cmd(3,true)?cmd(3,true)*100:0;
  $res = $Search->query(cmd(2),"thread",$offset);
  $ids = array_keys($res['matches']);
  $page = cmd(3,true)+1;

  $Query = new BoardQuery;
  $List = new BoardList;
  $List->type(LIST_THREAD_SEARCH);
  
  $List->title("Search Threads: ".htmlentities($query));
  $List->subtitle(number_format($res['total'])." results found showing ".($offset?$offset:1)."-".($offset+100).SPACE.ARROW_RIGHT.SPACE."page: $page");
  $List->header(false);
  require_once(DIR."module/search/.content/main.php");
  $List->header_menu();

  if($res['total'] == 0 || $offset > $res['total']) $ids = array(0);
  $DB->query($Query->list_thread(false,false,false,$ids));
  $List->data($DB->load_all());
  $List->thread();

  $List->footer();
}

function thread_post_get()
{
  global $DB;

  $Search = new Search;

  $offset = cmd(3,true)?cmd(3,true)*100:0;
  $res = $Search->query(cmd(2),"thread_post",$offset);
  $ids = array_keys($res['matches']);
  $page = cmd(3,true)+1;

  $Query = new BoardQuery;
  $View = new BoardView;
  $View->type(VIEW_THREAD_SEARCH);
  
  $View->title("Search Thread Posts: ".htmlentities(cmd(2)));
  $View->subtitle(number_format($res['total'])." results found showing ".($offset?$offset:1)."-".($offset+100).SPACE.ARROW_RIGHT.SPACE."page: $page");
  $View->header(false);
  require_once(DIR."module/search/.content/main.php");
  $View->header_menu();

  if($res['total'] == 0) $ids = array(0);
  $DB->query($Query->view_thread(false,cmd(3,true),cmd(4,true),$ids));
  $View->data($DB->load_all());
  $View->thread();

  $View->footer();
}
?>
