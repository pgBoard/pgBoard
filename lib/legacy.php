<?php
function parse_urls($str)
{
  $link = str_replace("\r","",$str[2]);
  $url = preg_replace("/.*href\s*=\s*[\"']?\s*(\S+?)(?:[\\1\s>]|$).*/i","\\1",$str[1]);
  $part = preg_match("/(?:https?|ftp):\/\/(.+?)(?:\/|$).*/i",$url,$matches);
  if(!$part) return str_replace("\r","",$str[0]);
  // What a hack
  if(substr($matches[1],-1) == "\"") $matches[1] = substr($matches[1],0,-1);
  return "<a href=\"$url\">$link</a> [$matches[1]]";
}

function parse_pre($str)
{
  $pre = str_replace("<br />","",substr(substr($str[0],5),0,-6));
  $pre = html_entity_decode($pre);
  $pre = htmlentities($pre);
  return "<pre>$pre</pre>";
}

function parse_images($str)
{
  $link = isset($str[2]) ? $str[2] : "";
  if(strpos($str[1],"document.") !== false) return ""; // bugfix for javascript bs

  $url = preg_replace("/.*src\s*=\s*[\"']?\s*(\S+?)(?:[\\1\s>]|$).*/i","\\1",$str[1]);
  $part = preg_match("/(?:https?|ftp):\/\/(.+?)(?:\/|$).*/i",$url,$matches);

  $extra = " ondblclick=\"if(this.parentNode.tagName != 'A') window.open(this.alt)\"";
  if(!$part) return $str[0];
  
  $hide = session('hidemedia') ? true : false;
  if(get('media') && session('hidemedia')) $hide=false;
  if(get('media') && !session('hidemedia')) $hide=true;

  if($hide) return "<a href=\"$url\">IMAGE REMOVED CLICK TO VIEW</a> [$matches[1]]";
  else
  return "<img src=\"$url\" alt=\"$url\"$extra/>";
}

function board_clean($input,$admin='f')
{
  $board_tags = "<strike><strong><b><i><u><a><img><pre><br><br/><em><ul><li><p>";
  $board_replace = array("<3","<>","<-");
  $board_replace_result = array("&lt;3","&lt;&gt;","&gt;&lt;","&lt;-");

  $input = trim($input);
  $input = str_replace($board_replace,$board_replace_result,$input);
  if($admin == 'f') $input = strip_tags($input,$board_tags);
  $input = preg_replace_callback("/<a\s+([^>]*?)>(.*?)<\/[aib]>/i","parse_urls",$input);
  $input = preg_replace_callback("/<img\s+([^>]*?)>/i","parse_images",$input);
  $input = nl2br($input);
  $input = preg_replace_callback("/<pre([^>]*?>)(.*?)<\/pre>/i","parse_pre",$input);
  return $input;
}
?>
