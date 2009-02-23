<?php
function history_get()
{
  global $DB,$Parse,$Core;
  $last = MD5($DB->value("SELECT extract(epoch from stamp) FROM chat ORDER BY stamp DESC LIMIT 1"));

  if($last == id())
  {
    print $last;
    exit_clean();
  }

  if(session('id')) $DB->query("UPDATE member SET last_chat=now() WHERE id=$1",array(session('id')));

  $DB->query("SELECT
                extract(epoch from c.stamp) as stamp,
                c.member_id as member_id,
                m.name as name,
                c.chat as chat
              FROM
                chat c
              LEFT JOIN
                member m
              ON
                m.id=c.member_id
              ORDER BY c.stamp DESC LIMIT 100");
  $chats = array_reverse($DB->load_all());

  $output = $last;
  foreach($chats as $chat)
  {
    $output .= date("h:i:s A",$chat['stamp'])."&nbsp; | ";
    $output .= "<strong>".$Core->member_link($chat['name'])."</strong>: ";
    $output .= "<span>".$Parse->run($chat['chat'])."</span><br/>\n";
  }
  print str_replace(": <span>/me ","<span> ",$output);
  exit_clean();
}
?>
