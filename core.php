<?php
$cmd = array();
if(get('cmd'))
{
  if(substr(get('cmd'),-1) == "/") $cmd = substr(get('cmd'),0,-1); // removing trailing slashes
  $cmd = explode("/",(get('cmd')?get('cmd'):"")); // explode by slash
}
function set_post($i,$value) { $_POST[$i] = $value; }
function post($i,$numeric=false)
{
  if(isset($_POST[$i]))
  {
    if($numeric) return is_numeric($_POST[$i]) ? $_POST[$i] : false;
    else
    return $_POST[$i];
  }
  else
  return false;
}

function set_get($i,$value) { $_GET[$i] = $value; }
function get($i,$numeric=false)
{
  if(isset($_GET[$i]))
  {
    if($numeric) return is_numeric($_GET[$i]) ? $_GET[$i] : false;
    else
    return $_GET[$i];
  }
  else
  return false;
}

function set_session($i,$value) { $_SESSION[$i] = $value; }
function session($i,$numeric=false)
{
  if(isset($_SESSION[$i]))
  {
    if($numeric) return is_numeric($_SESSION[$i]) ? $_SESSION[$i] : false;
    else
    return $_SESSION[$i];
  }
  else
  return false;
}

function set_cookie($i,$value) { $_COOKIE[$i] = $value; }
function cookie($i,$numeric=false)
{
  if(isset($_COOKIE[$i]))
  {
    if($numeric) return is_numeric($_COOKIE[$i]) ? $_COOKIE[$i] : false;
    else
    return $_COOKIE[$i];
  }
  else
  return false;
}

function set_cmd($i,$value) { global $cmd; $cmd[$i] = $value; }
function cmd($i,$numeric=false)
{
  global $cmd;
  if(isset($cmd[$i]))
  {
   if($numeric) return is_numeric($cmd[$i]) ? $cmd[$i] : false;
   else
   return $cmd[$i];
  }
  else
  return false;
}

function method() { return strtolower($_SERVER['REQUEST_METHOD']); }

function set_module($i,$value)
{
  $module = module();
  $module[$i] = value;
  set_cmd(0,implode("-",$module));
}
function module($i=false)
{
  $cmd = explode("-",(cmd(0) ? cmd(0) : "main"));
  if($i !== false) return isset($cmd[$i]) ? $cmd[$i] : false;
  else
  return $cmd;
}
function func() { return cmd(1) ? cmd(1) : "main"; }
function id($numeric=false) { return cmd(2,$numeric); }
function command() { return func()."_".method(); }
function fac() { return session('id') ? md5(session('id').DIST_FAC) : md5(DIST_FAC); }

function page()
{
  global $cmd,$page;
  if(isset($page)) return $page;
  $page = implode("_",count($cmd)?$cmd:array("home"));
  if(substr($page,-1) == "_") $page = substr($page,0,-1);
  return $page;
}

function url($s=false,$e=false)
{
  global $cmd;
  $url = "/";
  if(!$cmd) return $url;
  $segs = array_slice($cmd,$s?$s:0,$e?$e:count($cmd));
  foreach($segs as $seg)
  {
    if($seg == "") continue;
    $url .= $seg."/";
  }
  return $url;
}

function to_index($url=false)
{
  if(!$url)
  {
    $url = $_SERVER['HTTP_REFERER'];
    if($url == "") $url = "/";
  }
  header("Location: $url");
  exit();
}

function send_email($email,$subject,$body,$from="donotreply@crewcial.org")
{
  if(substr($email,0,1) == ",") $email = substr($email,1);
  $headers = "From: $from\n";
  $headers .= "Content-Type: text/html; charset=iso-8859-1";
  return mail($email,$subject,$body,$headers);
}
