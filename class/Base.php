<?php
/*
* List Functions
**/
define("LIST_THREAD",100);
define("LIST_THREAD_HISTORY",200);
define("LIST_THREAD_SEARCH",300);
define("LIST_MESSAGE",400);
define("LIST_MESSAGE_HISTORY",500);
define("LIST_MESSAGE_SEARCH",600);
define("LIST_MEMBER",700);

/*
* View Functions
**/
define("VIEW_THREAD",800);
define("VIEW_THREAD_HISTORY",900);
define("VIEW_THREAD_SEARCH",1000);
define("VIEW_THREAD_PREVIEW",1100);
define("VIEW_MESSAGE",1200);
define("VIEW_MESSAGE_HISTORY",1300);
define("VIEW_MESSAGE_SEARCH",1400);
define("VIEW_MESSAGE_PREVIEW",1100);
define("VIEW_MEMBER",1500);
/*
* Various Functions
**/
define("CREATE",1600);
define("EDIT",1700);
define("SEARCH",1800);
define("ERROR",1900);
define("MISC",2000);

/**
* Base Display Class
*/
class Base
{
  public $ajax = false;     // data only flag
  public $name;             // unique name
  public $title;            // title to display
  public $type;             // type of call
  public $table;            // table split for thread/message
  public $blocked = false;  // blocked members
  public $subtitle = "";
  

  function __construct()
  {
    global $DB;
    $this->name = "l".substr(md5(time()),0,5);
    if(session('id'))
    {
      $DB->query("UPDATE member SET last_view=now() WHERE id=$1",array(session('id')));
    }
    if(session('blocked')) $this->blocked(session('blocked'));
    if(get('ajax')) $this->ajax = true;
  }

  function title($title)
  {
    global $_title_;
    $_title_ = strip_tags($title);
    $Plugin = new BoardPlugin;
    $title = $Plugin->base_title($title);
    $this->title = $title;
  }
  function subtitle($subtitle) { $this->subtitle = $subtitle; }
  function blocked($blocked) { $this->blocked = $blocked; }

  function type($type)
  {
    $this->type = $type;
    switch($this->type)
    {
      case LIST_THREAD:
      case VIEW_THREAD:
      case LIST_THREAD_HISTORY:
      case LIST_THREAD_SEARCH:
      case VIEW_THREAD_HISTORY:
      case VIEW_THREAD_SEARCH:
        $this->table = "thread";
        break;
      case LIST_MESSAGE:
      case VIEW_MESSAGE:
      case LIST_MESSAGE_HISTORY:
      case LIST_MESSAGE_SEARCH:
      case VIEW_MESSAGE_HISTORY:
      case VIEW_MESSAGE_SEARCH:
        $this->table = "message";
        break;
    }
  }

  function header($loadmenu=true)
  {
    global $Core,$Security;
    if($this->ajax) return;

    print "<div id=\"wrap_{$this->name}\" class=\"clear\">\n";
    print "  <h3 class=\"title\">$this->title</h3>\n";
    $Security->auth_control();
    if(!$this->subtitle && $this->type != ERROR)
    {
      $subtitle = "<a href=\"/\">".number_format($Core->thread_count())." threads</a> ".ARROW_RIGHT.SPACE;
      $subtitle .= "<a href=\"/main/status/\">".number_format($Core->active_member_count())." active members, ";
      $subtitle .= number_format($Core->posting_member_count())." of which are posting, ";
      $subtitle .= number_format($Core->lurking_member_count())." of which are lurking, ";
      $subtitle .= number_format($Core->chatting_member_count())." of which are chatting</a>";
      $this->subtitle($subtitle);
    }
    print "  <div class=\"subtitle\">\n";
    print $this->subtitle;
    print "  </div>\n";
    print "  <div class=\"clear\"></div>\n";
    if($loadmenu) $this->header_menu();
  }
  
  function header_menu()
  {
    global $Core,$_menu_;
    
    if(!isset($this->type) || $this->ajax) return;

    // quicksearch
    switch($this->type)
    {
      case LIST_THREAD:
      case LIST_THREAD_HISTORY:
      case LIST_THREAD_SEARCH:
      case LIST_MESSAGE:
      case LIST_MESSAGE_HISTORY:
      case LIST_MESSAGE_SEARCH:
        print "<div id=\"quicksearch\"><div class=\"setdown searchwrap\">\n";
        print "<input type=\"text\" class=\"searchtext\" id=\"filter_{$this->name}\"/>\n";
        print "<input type=\"button\" class=\"clearbutton\" value=\"clear\" onclick=\"clear_search('{$this->name}')\"/>\n";
        print "</div></div>\n";
        break;
    }

    $messages = $chatters = "";
    if(session('id'))
    {
      $m = $Core->message_unread_count(session('id'));
      $p = $Core->message_unread_post_count(session('id'));
      if($m != 0 || $p != 0) $messages = " <strong class=\"blink\">({$m}/{$p})</strong>";
    }
    
    $c = $Core->chatting_member_count();
    if($c != 0) $chatters = " ({$c})";

    print "  <ul class=\"nav top\"><div class=\"setdown\">\n";
    foreach($_menu_ as $menu => $data)
    {
      if(!$data['show']) continue;
      if(!session('id') && $data['auth']) continue;
      if($data['link'] == "/member/create/" && session('id')) continue;
      $menu = str_replace(array("%MESSAGES%","%CHATTERS%"),array($messages,$chatters),$menu);
      print "<li><a href=\"$data[link]\" title=\"$data[title]\">$menu</a></li>\n";
    }
    print "  </div></ul>\n";

    // line under nav (not showing standard board display)
    switch($this->type)
    {
      case CREATE:
      case EDIT:
      case SEARCH:
      case ERROR:
        print "<div class=\"hr\"><hr/></div>\n";
        break;

    }
  }

  function footer_menu()
  {
    global $DB,$Security,$Core;
    
    // setup message/thread split and next/prev offsets from url
    switch($this->type)
    {
      case LIST_THREAD:
      case VIEW_THREAD:
      case LIST_MESSAGE:
      case VIEW_MESSAGE:
        $end = 2;
        break;
        
      case LIST_THREAD_HISTORY:
      case LIST_THREAD_SEARCH:
      case VIEW_THREAD_HISTORY:
      case VIEW_THREAD_SEARCH:
      case LIST_MESSAGE_HISTORY:
      case LIST_MESSAGE_SEARCH:
      case VIEW_MESSAGE_HISTORY:
      case VIEW_MESSAGE_SEARCH:
        $end = 3;
        break;
    }

    switch($this->type)
    {
      case LIST_THREAD:
      case LIST_THREAD_HISTORY:
      case LIST_THREAD_SEARCH:
      case LIST_MESSAGE:
      case LIST_MESSAGE_HISTORY:
      case LIST_MESSAGE_SEARCH:
      case VIEW_THREAD_SEARCH:
      case VIEW_THREAD_HISTORY:
      case VIEW_MESSAGE_SEARCH:
      case VIEW_MESSAGE_HISTORY:
        $next = (cmd($end,true) ? cmd($end,true)+1 : 1);
        $prev = ($next-2 > 0 ? ($next-2)."/" : "");
        print "<ul class=\"nav bottom clear\">\n";
        print "  <li><a href=\"/{$this->table}/list/\">".HOME_BUTTON."</a></li>\n";
        if(cmd($end,true) > 0) print "  <li><a href=\"".url(0,$end)."$prev\">&laquo; prev</a></li>\n";
        print "  <li><a href=\"".url(0,$end)."$next/\">next ".ARROW_RIGHT."</a></li>\n";
        print "</ul>\n";
        print "<div class=\"clear\"></div>\n";
        break;

      case VIEW_THREAD:
      case VIEW_MESSAGE:
        print "<ul class=\"nav bottom clear shiftup\">\n";
        print "  <li><a href=\"/{$this->table}/list/\">".HOME_BUTTON."</a></li>\n";
        print "  <li><a href=\"javascript:;\" onclick=\"loadposts('{$this->table}',this)\">load new posts</a></li>\n";
        print "</ul>\n";
        print "<div class=\"clear\"></div>\n";
        break;


    case VIEW_MEMBER:
        if(!is_numeric(id())) $idnum = $Core->idfromname(id());
        else
        $idnum = $DB->value("SELECT id FROM member WHERE name=$1",array(id()));

        $ignorelisten = $Core->is_ignoring(session('id'),$idnum) ? "listen" : "ignore";
        print "<ul class=\"nav bottom clear\">\n";
        if(session('id') != $idnum) print "  <li><a href=\"/message/create/".id()."/\">send message</a></li>\n";
        if(!$Security->is_admin($idnum) && session('id') != $idnum && IGNORE_ENABLED)
        {
          print "  <li><a href=\"/member/$ignorelisten/".id()."/".MD5(session_id())."/\">$ignorelisten</a></li>\n";
        }
        print "  <li><a href=\"/thread/listbymemberposted/".id()."/\">threads participated</a></li>\n";
        print "  <li><a href=\"/thread/listbymember/".id()."/\">threads created</a></li>\n";
        print "  <li><a href=\"/thread/viewbymember/".id()."/\">posts created</a></li>\n";
        print "  <li><a href=\"/thread/listfavoritesbymember/".id()."/\">favorites</a></li>\n";
        print "  <li><a href=\"/member/editcolors/".id()."/\">color scheme</a></li>\n";
        if(session('id') == $idnum) print "  <li><a href=\"/member/edit/\">edit account</a></li>\n";
        print "</ul>\n";
        print "<div class=\"clear\"></div>\n";
        break;
    }

  }

  function footer($loadmenu=true)
  {
    if($this->ajax) return;
    print "</div>\n";
    if($loadmenu) $this->footer_menu();
    switch($this->type)
    {
      case LIST_THREAD:
      case LIST_THREAD_HISTORY:
      case LIST_THREAD_SEARCH:
      case LIST_MESSAGE:
      case LIST_MESSAGE_HISTORY:
      case LIST_MESSAGE_SEARCH:
        print "<script type=\"text/javascript\">\n";
        print "setTimeout(function(){init_search('{$this->name}','list');},50);\n";
        print "</script>\n";
    } 
  }
}
