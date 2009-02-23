<?php
class BoardAdmin
{
  function toggle_flag($flag,$id)
  {
    global $DB;
    $DB->query("UPDATE
                  thread
                SET
                  {$flag}=(CASE WHEN {$flag} IS true THEN false ELSE true END)
                WHERE
                  id=$1",array($id));
  }
  
  function check_flag($flag,$id)
  {
    global $DB;
    return $DB->value("SELECT {$flag} FROM thread WHERE id=$1",array($id)) == "t" ? true : false;
  }
}
