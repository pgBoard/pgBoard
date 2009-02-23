<?php

class Data
{
  public $insert = array();
  function set_value($key,$val) { $this->insert[$key] = $val; }
  function clear_data() { $this->insert = array(); }

  function thread_insert($data)
  {
    global $DB,$Security;
    $this->clear_data();
    $this->set_value("subject",$data['subject']);

    if(!isset($data['name'])) $data['name'] = "";
    if(!isset($data['pass'])) $data['pass'] = "";

    if($member_id = $Security->form_login($data['name'],$data['pass']))
    {
      $this->set_value("member_id",$member_id);
      $this->set_value("last_member_id",$member_id);

      $DB->begin();
      if($DB->insert("thread",$this->insert,array_keys($this->insert)))
      {
        $post = array();
        $post['thread_id'] = $DB->value("SELECT currval('thread_id_seq')");
        $post['body'] = $data['body'];

        $Search = new Search("thread");
        if($Search->thread_insert($this->insert,$post['thread_id']))
        {
          if($this->thread_post_insert($post,$member_id))
          {
            $DB->commit();
            return true;
          }
          else
          $DB->rollback();
        }
        else
        $DB->rollback();
      }
      else
      $DB->rollback();
    }
    else
    {
      $DB->rollback();
      return false;
    }
  }

  function thread_post_insert($data,$member_id=false)
  {
    global $DB,$Security;
    
    if($DB->value("SELECT locked FROM thread WHERE id=$1",array($data['thread_id'])) == 't') return false;
    
    $this->clear_data();
    $this->set_value("thread_id",$data['thread_id']);
    $this->set_value("body",$data['body']);
    $this->set_value("member_ip",$_SERVER['REMOTE_ADDR']);

    // if no member id defined auth from form
    if(!$member_id)
    if(!$member_id = $Security->form_login($data['name'],$data['pass'])) return false;

    $this->set_value("member_id",$member_id);
      
    $DB->begin();
    if($DB->insert("thread_post",$this->insert,array_keys($this->insert)))
    {
      $Search = new Search;
      if($Search->thread_post_insert($this->insert,$DB->value("SELECT currval('thread_post_id_seq')")))
      {
        $DB->commit();
        return true;
      }
      else
      {
        $DB->rollback();
        return false;
      }
    }
    else
    {
      $DB->rollback();
      return false;
    }
  }
  
  function thread_post_update($data,$id)
  {
    global $DB;
    $this->clear_data();
    $this->set_value("body",$data['body']);

    $DB->begin();
    if($DB->update("thread_post","id",$id,$data))
    {
      $Search = new Search;
      if($Search->thread_post_update($id))
      {
        $DB->commit();
        return true;
      }
      else
      {
        $DB->rollback();
        return false;
      }
    }
    else
    {
      $DB->rollback();
      return false;
    }
  }
  
  function message_insert($data)
  {
    global $DB,$Security;
    $this->clear_data();
    $this->set_value("subject",$data['subject']);

    if(!isset($data['name'])) $data['name'] = "";
    if(!isset($data['pass'])) $data['pass'] = "";
    
    if($member_id = $Security->form_login($data['name'],$data['pass']))
    {
      $this->set_value("member_id",$member_id);
      $this->set_value("last_member_id",$member_id);

      $DB->begin();
      if($DB->insert("message",$this->insert,array_keys($this->insert)))
      {
        $post = array();
        $post['message_id'] = $DB->value("SELECT currval('message_id_seq')");
        $post['body'] = $data['body'];

        $members = explode(",",$data['message_members']);
        $members[] = session('id');
        foreach($members as $member_id)
        {
          $mm = array();
          $mm['message_id'] = $post['message_id'];
          $mm['member_id'] = $member_id;
          $DB->insert("message_member",$mm);
        }

        if($this->message_post_insert($post,$member_id))
        {
          $DB->commit();
          return true;
        }
        else
        $DB->rollback();
      }
    }
    else
    {
      $DB->rollback();
      return false;
    }
  }
  
  function message_post_insert($data,$member_id=false)
  {
    global $DB,$Security;
    $this->clear_data();
    $this->set_value("message_id",$data['message_id']);
    $this->set_value("body",$data['body']);
    $this->set_value("member_ip",$_SERVER['REMOTE_ADDR']);

    // if no member id defined auth from form
    if(!$member_id)
    if(!$member_id = $Security->form_login($data['name'],$data['pass'])) return false;

    $this->set_value("member_id",$member_id);

    if(!$DB->check("SELECT true FROM message_member WHERE message_id=$1 AND member_id=$2",array($data['message_id'],$member_id)))
    {
      print "You are not a member of this message.<br/>";
      return false;
    }

    $DB->begin();
    if($DB->insert("message_post",$this->insert,array_keys($this->insert)))
    {
      $DB->commit();
      return true;
    }
    else
    {
      $DB->rollback();
      return false;
    }
  }
}
?>
