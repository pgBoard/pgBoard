<?php
class Search
{
  function query($query,$index,$offset=0)
  {
    require_once(DIR."lib/sphinx/sphinxapi.php");
    $sphinx = new SphinxClient();
    $sphinx->setServer(SPHINX_HOST,SPHINX_PORT);
    $sphinx->SetLimits($offset,100,10000000);
    $sphinx->SetMatchMode(SPH_MATCH_EXTENDED);
    $sphinx->SetSortMode(SPH_SORT_ATTR_DESC,'date_posted');
    $res = $sphinx->Query($query,$index);
    return $res;
  }
  function insert($type,$doc) { return true; }
  function delete($type,$id) { return true; }
  function thread_insert($data,$id) { return true; }
  function thread_post_insert($data,$id) { return true; }
  function message_insert($data,$id) { return true; }
  function message_post_insert($data,$id) { return true; }
  function thread_update($data) { return true; }
  function thread_post_update($id) { return true; }
  function message_update($data) { return true; }
  function message_post_update($data) { return true; }
  function thread_delete($id) { return true; }
  function thread_post_delete($id) { return true; }
  function message_delete($id) { return true; }
  function message_post_delete($id) { return true; }
}
