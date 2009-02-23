<?php
define("LANG","en");

define("ADMIN_EMAIL","admin@domain.com");
define("DB","dbname=board user=board password=board");
define("DIR","/path/to/board/www/");
define("SPHINX_HOST","localhost");
define("SPHINX_PORT",3312);

define("REGISTRATION_OPEN",true);
define("REGISTRATION_PASSWORD","membersonly"); // set to false to disable this feature
define("MEMBER_REGEXP","^[a-z0-9_-]{3,15}$"); // regexp to define valid member name

define("IGNORE_ENABLED",true); // if you disable this be sure to DELETE * FROM member_ignore
define("IGNORE_PUBLIC",true); // set to false to make ignoring private

define("LIST_DEFAULT_LIMIT",100); // number of threads per page
define("COLLAPSE_DEFAULT",25); // default value to collapse at
define("COLLAPSE_OPEN_DEFAULT",5); // default number of posts to leave open after collapse

define("FUNDRAISER_ID",-1); // id of fundraiser record in database
define("FUNDRAISER_ITEM_NAME","Board Hosting"); // item name for paypal ipn to recognize payment
define("FUNDRAISER_EMAIL","adminpaypal@domain.com"); // email address for paypal payments

define("VIEW_DATE_FORMAT","F jS, Y @ g:i:s a");
define("LIST_DATE_FORMAT","D\&\\n\b\s\p\;M\&\\n\b\s\p\;d\&\\n\b\s\p;Y&\\n\b\s\p\;h:i\&\\n\b\s\p\;a");

define("FORM_SALT","aksjdsa9*^&*@&(@*22@*1");

// functions allowed no matter what your login state
$_allowed_ = array("threadmain","threadlist","threadview","threadviewpost",
                   "threadfirstpost","threadreply","threadpreviewpost",
                   "threadtogglefavorite","mainlogin","donatemain","donateaccept",
                   "membercreate","memberauthorize");

// menu display
$_menu_ = array("create account"     => array("link" => "/member/create/",
                                              "title" => "create an account",
                                              "show" => REGISTRATION_OPEN,
                                              "auth" => false),
                "threads"            => array("title" => "back to the home page",
                                              "link" => "/",
                                              "show" => true,
                                              "auth" => false),
                "messages%MESSAGES%" => array("link" => "/message/list/",
                                              "title" => "view your messages",
                                              "show" => true,
                                              "auth" => true),
                "new thread"         => array("link" => "/thread/create/",
                                              "title" => "create a new thread",
                                              "show" => true,
                                              "auth" => true),
                "new message"        => array("link" => "/message/create/",
                                              "title" => "send a message to another member",
                                              "show" => true,
                                              "auth" => true),
                "search"             => array("link" => "/search/",
                                              "title"=> "search the board",
                                              "show" => true,
                                              "auth" => true),
                "chat%CHATTERS%"     => array("link" => "/chat/",
                                              "title" => "chat in realtime",
                                              "show" => true,
                                              "auth" => true),
                "profile"            => array("link" => "/member/view/",
                                              "title" => "view my profile",
                                              "show" => true,
                                              "auth" => true),
                "donate"             => array("link" => "/donate/",
                                              "title" => "donate!",
                                              "show" => true,
                                              "auth" => false));

// parser find
$_bbc_ = array("","[u]","[/u]",
               "[i]","[/i]",
               "[em]","[/em]",
               "[quote]","[/quote]",
               "[b]","[/b]",
               "[strong]","[/strong]",
               "[strike]","[/strike]",
               "[code]","[/code]",
               "[sub]","[/sub]",
               "[sup]","[/sup]",
               "[spoiler]","[/spoiler]");

// parser replace
$_rep_ = array("","<span style=\"text-decoration:underline;\">","</span>",
               "<em>","</em>",
               "<em>","</em>",
               "<blockquote>","</blockquote>",
               "<strong>","</strong>",
               "<strong>","</strong>",
               "<strike>","</strike>",
               "<pre>","</pre><div class=clear></div>",
               "<sub>","</sub>",
               "<sup>","</sup>",
               "<span class=\"spoiler\" onclick=\"$(this).next().show();$(this).remove()\">show spoiler</span><span style=\"display:none\">","</span>");

/*
* Do not edit below this line unless you know what you're doing!
**/
define("VERSION","2.9.5");

require_once("core.php"); // framework
ini_set("magic_gpc_quotes",false);

if(module(0) == "main" && !cmd(1))
{
  set_cmd(0,"thread");
  set_cmd(1,"list");
}
if(!isset($commandline))
{
  $cache = array(); // no caching for the moment array("threadview","messageview");
  if(!in_array(module(0).func(),$cache))
  {
    header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
    header("Cache-Control: no-store, no-cache, must-revalidate");
    header("Cache-Control: post-check=0, pre-check=0", false);
    header("Pragma: no-cache");
  }
  else
  session_cache_limiter("private");

  session_start();
}

require_once("error.php");          // error handler
require_once("lang/".LANG.".php");  // language file
require_once("class/Security.php"); // security
require_once("class/Core.php");     // common commands
require_once("class/DB.php");       // database
require_once("class/Query.php");    // query creation
require_once("class/Style.php");    // color themes, dynamic styling
require_once("class/Base.php");     // base layout
require_once("class/List.php");     // display for lists
require_once("class/View.php");     // display for views
require_once("class/Parse.php");    // bbcode parser
require_once("class/Form.php");     // forms
require_once("class/Data.php");     // data management
require_once("class/Search.php");   // search management
require_once("class/Admin.php");    // search management
require_once("class/Plugin.php");    // plugins

$Security = new BoardSecurity;
$Core = new BoardCore;
$DB = new DB(DB,true);
$Parse = new BoardParse($_bbc_,$_rep_);
if(!session('id') && cookie('board')) $Security->login_cookie();
$Style = new BoardStyle(session('id'));

if(!isset($commandline))
{
  ob_start();
  if(!$DB->db)
  {
    $Base = new Base;
    $Base->title("Dead database!");
    $Base->header();
    $Base->footer();
  }
  else
  {
    $Core->command_parse();
    if(get('ajax'))
    {
      $buffer = ob_get_contents();
      ob_end_clean();
      print $buffer;
      exit_clean();
    }
    $buffer = ob_get_contents();
    ob_end_clean();
  }
}
