<?php

function main_post()
{
  $_SESSION['search'] = $_POST;

  $terms = 0;
  $url = "";
  foreach($_POST as $key => $value)
  {
    if(substr($key,0,1) == "_" || $value == "") continue;
    $url .= "$key,$value,";
    $terms++;
  }
  if($terms == 1) $url = post('search');
  else
  $url = substr($url,0,-1);
  
  header("Location: /search/".post('_type')."/$url/");
  exit();
}

?>
