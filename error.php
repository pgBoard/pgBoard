<?php
$error = "";

// list how the command interpreter is handling calls
function debug()
{
  print "<strong>query string:</strong> $_SERVER[QUERY_STRING]\n\n";
  print "<strong>module:</strong> ".implode("-",module())."\n";
  print "<strong>function:</strong> ".func()."\n";
  print "<strong>method:</strong> ".method()."\n\n";
  print "<strong>include:</strong> /module/".implode("/",module())."/main.php\n\n";
  print "<strong>run:</strong> ".command()."\n\n";
}

function error_handler($errno, $errstr, $errfile, $errline)
{
  global $error;

  $errno = $errno & error_reporting();
  if($errno == 0) return;

  $error .= "<pre>\n";
  switch($errno)
  {
      case E_ERROR:             $type = "ERROR";                  break;
      case E_WARNING:           $type = "WARNING";                break;
      case E_PARSE:             $type = "PARSE ERROR";            break;
      case E_NOTICE:            $type = "NOTICE";                 break;
      case E_CORE_ERROR:        $type = "CORE ERROR";             break;
      case E_CORE_WARNING:      $type = "CORE WARNING";           break;
      case E_COMPILE_ERROR:     $type = "COMPILE ERROR";          break;
      case E_COMPILE_WARNING:   $type = "COMPLE WARNING";         break;
      case E_USER_ERROR:        $type = "USER ERROR";             break;
      case E_USER_WARNING:      $type = "USER WARNING";           break;
      case E_USER_NOTICE:       $type = "USER NOTICE";            break;
      case E_STRICT:            $type = "STRICT NOTICE";          break;
      case E_RECOVERABLE_ERROR: $type = "RECOVERABLE ERROR";      break;
      default:                  $type = "UNKNOWN ERROR [$errno]"; break;
  }
  $error .= "<strong>$type</strong>:\n$errstr\n";
  if(function_exists('debug_backtrace'))
  {
    $error .= "\n<strong>BACKTRACE:</strong>\n";
    $backtrace = debug_backtrace();
    array_shift($backtrace);
    foreach($backtrace as $i=>$l)
    {
      $c = $t = $f = "";
      if(isset($l['class'])) $c = $l['class'];
      if(isset($l['type'])) $t = $l['type'];
      if(isset($l['function'])) $f = $l['function'];
      
      $error .= "Function <strong>{$c}{$t}{$f}</strong>";
      if(isset($l['file'])) $error .= " in <strong>{$l['file']}</strong>";
      if(isset($l['line'])) $error .= " on line <strong>{$l['line']}</strong>";
      $error .= ".\n";
    }
  }
  $error .= "</pre><hr/>\n";
  return true;
}

function error_display()
{
  global $error;
  if($error != "")
  {
    print "<div id=\"error\">\n";
    print "<pre>Errors:</pre><hr/>\n";
    print $error;
    print "<pre>\n";
    print "<strong>DEBUG</strong>:\n";
    debug();
    print "<hr/>";
    print "<strong>SERVER</strong>:\n";
    unset($_SERVER['HTTP_COOKIE']);
    print_r($_SERVER);
    print "</pre>\n</div>\n";
    print "<div id=\"errorbar\"><a href=\"javascript:;\" onclick=\"$('#error').slideToggle('slow');\">toggle errors</a> &raquo; <a href=\"javascript:;\" onclick=\"$('input[type=submit]').attr('disabled',false);$('#error').remove();$('#errorbar').remove();\">clear</a>&nbsp;</div>\n";
  }
}
$handler = set_error_handler("error_handler");


// exit after showing errors
function exit_clean()
{
  error_display();
  exit();
}
