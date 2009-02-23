<?php
function togglesticky_get()
{
  global $Security;

  if(id() && $Security->is_admin(session('id')))
  {
    $Admin = new BoardAdmin;
    $Admin->toggle_flag("sticky",id());
  }
  return to_index();
}

function togglelocked_get()
{
  global $Security;
  if(id() && $Security->is_admin(session('id')))
  {
    $Admin = new BoardAdmin;
    $Admin->toggle_flag("locked",id());
  }
  return to_index();
}

function togglelegendary_get()
{
  global $Security;
  if(id() && $Security->is_admin(session('id')))
  {
    $Admin = new BoardAdmin;
    $Admin->toggle_flag("legendary",id());
  }
  return to_index();
}
?>
