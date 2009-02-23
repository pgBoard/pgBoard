<?php
function speak_post()
{
  global $DB;
  if(!session('id') || !post('msg')) return;

  if(post('msg') != "")
  {
    $insert = array();
    $insert['member_id'] = session('id');
    $insert['chat'] = post('msg');
    $DB->insert("chat",$insert);
  }
  exit_clean();
}
?>
