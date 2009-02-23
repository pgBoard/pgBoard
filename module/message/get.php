<?php
function main_get() { list_get(); }

function list_get()
{
  global $DB;

  $Query = new BoardQuery;
  $List = new BoardList;
  $List->type(LIST_MESSAGE);

  $List->title(TITLE_BOARD);
  $List->header();

  $DB->query($Query->list_message(cmd(2,true),cmd(3,true)));
  $List->data($DB->load_all());
  $List->message();

  $List->footer();
}

function view_get()
{
  global $DB,$Core;

  if(!id()) return to_index();

  if(!$DB->check("SELECT true FROM message_member WHERE message_id=$1 AND member_id=$2 AND deleted IS false",array(id(),session('id'))))
  {
    return to_index("/message/");
  }

  $Query = new BoardQuery;
  $View = new BoardView;
  $View->type(VIEW_MESSAGE);
  $View->member_update();
  $View->increment_views();
  
  $subject = $View->subject(id());
  $subject .= "<span class=\"smaller\">";
  if(session('hidemedia'))
  {
    if(get('media')) $subject .= SPACE.ARROW_RIGHT.SPACE."<a href=\"".url()."\">hide images</a>";
    if(!get('media')) $subject .= SPACE.ARROW_RIGHT.SPACE."<a href=\"".url()."&media=true\">show images</a>";
  }
  if(!session('hidemedia'))
  {
    if(!get('media')) $subject .= SPACE.ARROW_RIGHT.SPACE."<a href=\"".url()."&media=true\">hide images</a>";
    if(get('media')) $subject .= SPACE.ARROW_RIGHT.SPACE."<a href=\"".url()."\">show images</a>";
  }
  $subject .= SPACE.ARROW_RIGHT.SPACE."<a href=\"/message/delete/".id()."/".md5(session_id())."/\">delete</a>";
  $subject .= "</span>";
  
  $View->title($subject);

  $DB->query("SELECT m.name,mm.deleted FROM message_member mm LEFT JOIN member m ON m.id=mm.member_id WHERE mm.message_id=$1",array(id()));
  $subtitle = "<strong>Participating:</strong> ";
  while($name = $DB->load_array())
  {
    if($name['deleted'] == 't') $subtitle .= "<strike>".$Core->member_link($name['name'])."</strike>, ";
    else
    $subtitle .= $Core->member_link($name['name']).", ";
  }
  $View->subtitle(substr($subtitle,0,-2));

  $View->header();

  $DB->query($Query->view_message(id(true),cmd(3,true),cmd(4,true)));
  $View->data($DB->load_all());
  $View->message();

  $View->footer();
}

function firstpost_get()
{
  global $DB,$Parse;
  if(!id() || !session('id')) return to_index();
  $body = $DB->value("SELECT
                        mp.body
                      FROM
                        message_member mm
                      LEFT JOIN
                        message_post mp
                      ON
                        mp.message_id=mm.message_id
                      WHERE
                        mm.message_id=$1
                      AND
                        mm.member_id=$2
                      ORDER BY
                        mp.date_posted
                      LIMIT 1",array(id(),session('id')));
  print $Parse->run($body);
}

function viewpost_get()
{
  global $DB,$Parse;
  if(!id() || !session('id')) return;
  $body = $DB->value("SELECT
                        mp.body
                      FROM
                        message_member mm
                      LEFT JOIN
                        message_post mp
                      ON
                        mp.message_id=mm.message_id
                      WHERE
                        mp.id=$1
                      AND
                        mm.member_id=$2
                      AND
                        mm.deleted IS NOT true",array(id(),session('id')));
  print htmlentities($body);
}

function delete_get()
{
  global $DB;
  if(!id() || !session('id') || cmd(3) != MD5(session_id())) return to_index();
  if($DB->query("UPDATE message_member SET deleted=true WHERE member_id=$1 AND message_id=$2",array(session('id'),id())))
  {
    return to_index("/message/");
  }
}
?>
