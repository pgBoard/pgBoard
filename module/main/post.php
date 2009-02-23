<?php
function login_post()
{
  global $DB,$Security;

  if(!$Security->login(post('name'),post('pass')))
  {
    $Base = new Base;
    $Base->type(ERROR);
    $Base->title(ERROR_AUTH);
    $Base->header();
    $Base->footer();
    return;
  }
  else
  {
    $to = false;
    if(isset($_SERVER['HTTP_REFERER'])) $to = $_SERVER['HTTP_REFERER'];
    if(substr($to,-12) == "/main/login/") $to = "/";
    return to_index($to);
  }
}
?>
