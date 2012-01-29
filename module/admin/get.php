<?php
function togglesticky_get()
{
  global $Security;

  if(id() && $Security->is_admin(session('id')) && md5(session_id()) == cmd(3))
  {
    $Admin = new BoardAdmin;
    $Admin->toggle_flag("thread","sticky",id());
  }
  return to_index();
}

function togglelocked_get()
{
  global $Security;
  if(id() && $Security->is_admin(session('id')) && md5(session_id()) == cmd(3))
  {
    $Admin = new BoardAdmin;
    $Admin->toggle_flag("thread","locked",id());
  }
  return to_index();
}

function togglelegendary_get()
{
  global $Security;
  if(id() && $Security->is_admin(session('id')) && md5(session_id()) == cmd(3))
  {
    $Admin = new BoardAdmin;
    $Admin->toggle_flag("thread","legendary",id());
  }
  return to_index();
}

function togglebanned_get()
{
  global $Security,$DB;
  if(id() && $Security->is_admin(session('id')) && md5(session_id()) == cmd(3))
  {
    $Admin = new BoardAdmin;
    $Admin->toggle_flag("member","banned",id());
    $DB->update("member","id",id(),array("cookie"=>""));
  }
  return to_index($_SERVER['HTTP_REFERER']);
}

function resetlink_get()
{
  global $Security,$DB;
  if(id() && $Security->is_admin(session('id')) && md5(session_id()) == cmd(3))
  {
    $DB->query("SELECT email_signup||pass FROM member WHERE id=$1",array(id()));
    $hash = md5($DB->load_result().time());
    $DB->update("member","id",id(),array("reset"=>$hash));
    print "http://$_SERVER[HTTP_HOST]/member/reset/$hash/";
  }
  exit_clean();
}
?>
