<?php
class BoardParse
{
  private $bbc;
  private $rep;
  private $imgsuffix = array("jpg","gif","png");
  private $hidemedia = false;

  function __construct($bbc,$rep)
  {
    $this->bbc = $bbc;
    $this->rep = $rep;
    $this->hidemedia = session('hidemedia');
    if(get('media') && session('hidemedia')) $this->hidemedia=false;
    if(get('media') && !session('hidemedia')) $this->hidemedia=true;
  }

  // prepare urls (so hack)
  function prep_url_linktext($href) { return $this->prep_url(array($href[1]),htmlentities($href[2])); }
  function prep_url($href,$link=false)
  {
    $clean = str_replace(array("[url]","[/url]"),"",$href[0]);
    if(substr($clean,0,3) == "www") $clean = "http://$clean";
    if(!$link) $link = htmlentities($clean);
    $clean = trim($clean);

    if(!preg_match('|^http(s)?://[a-z0-9-]+(.[a-z0-9-]+)*(:[0-9]+)?(/.*)?$|i',$clean)) return $href[0];
    else
    {
      $host = parse_url($clean);
      $host = $host['host'];
      $link = htmlspecialchars_decode($link);
      return "<a href=\"$clean\" class=\"link\" onclick=\"window.open(this.href); return false;\" title=\"$link\">$link</a> [$host] ".ARROW_RIGHT.SPACE;
    }
  }

  function youtube($href)
  {
    $host = parse_url($href[1]);
    $host = isset($host['host']) ? $host['host'] : "";
    if($host != "youtube.com" && $host != "www.youtube.com") return $href[1];
    else
    {
      $href = str_replace("watch?v=","/v/",$href[1]);
      return "<object width=\"425\" height=\"355\"><param name=\"movie\" value=\"$href\"></param><param name=\"wmode\" value=\"transparent\"></param><embed src=\"$href\" type=\"application/x-shockwave-flash\" wmode=\"transparent\" width=\"425\" height=\"355\"></embed></object>";
    }
  }

  function run($string)
  {
    if(!$s = $string) return "";

    // remove the garbage
    $s = htmlentities($s,ENT_QUOTES,'UTF-8');

    // basic parse
    for($b=1;$b<count($this->bbc);$b++)
    {
      $bbcn = '#'.preg_quote($this->bbc[$b],'#')."(.*)".preg_quote($this->bbc[$b+1],'#').'#Uis'; // needle
      $bbcr = $this->rep[$b]."$1".$this->rep[++$b]; // replacement
      $s = preg_replace($bbcn,$bbcr,$s);
    }

    // do links
    $s = preg_replace_callback("#\[url\=(.*)\](.*)\[\/url\]#Ui",array(&$this,'prep_url_linktext'),$s);
    $s = preg_replace_callback("#\[url\](.*)\[\/url\]#Ui",array(&$this,'prep_url'),$s);
    $s = preg_replace_callback("#(^|\s|>)((http|https)://\w+[^\s\[\]\<]+)#i",array(&$this,'prep_url'),$s);

    // do media
    if($this->hidemedia)
    {
      $s = preg_replace("#\[img\](.*)\[\/img\]#Ui","<a href=\"$1\" class=\"link\" onclick=\"$(this).after('<img src=\\''+this.href+'\\' ondblclick=\\'window.open(this.src);return false\\'/>');$(this).remove();return false;\">IMAGE REMOVED CLICK TO VIEW</a>",$s);
      $s = preg_replace("#\[youtube\](.*)\[\/youtube\]#Ui","<a href=\"$1\" onclick=\"window.open(this.href); return false;\">YOUTUBE REMOVED CLICK TO VIEW</a>",$s);
    }
    else
    {
      $s = preg_replace("#\[img\](.*)\[\/img\]#Ui","<img src=\"$1\" ondblclick=\"window.open(this.src);\"/>",$s);
      $s = preg_replace_callback("#\[youtube\](.*)\[\/youtube\]#Ui",array(&$this,'youtube'),$s);
    }

    // start line break stuff
    $s = str_replace('<br />',NULL,$s);
    $s = nl2br(chop($s));

    // remove line breaks inside these tags
    $lbr = array(array("<pre>","</pre>"));

    foreach($lbr as $lb)
    {
      $lb1 = $lb[0];
      $lb2 = $lb[1];
      $lb1q = preg_quote($lb1,'#');
      $lb2q = preg_quote($lb2,'#');
      $lbn = "#".$lb1q."(.+?)".$lb2q."#sie";
      $s = preg_replace($lbn,"'".$lb1."'.str_replace('<br />','',str_replace('\\\"','\"','$1')).'".$lb2."'",$s);
      $s = preg_replace("#\<br \/\>(\r\n)".$lb1q."#i","\n".$lb1,$s);
      $s = preg_replace("#".$lb2q."\<br \/\>#i",$lb2,$s);
      $s = preg_replace("#".$lb2q."(\r\n)\<br \/\>#i",$lb2,$s);
    }
    // end line break stuff

    return $s;
  }
}
