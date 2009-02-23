<?php
function pref_exists($pref_id,$member_id)
{
  global $DB;
  return $DB->check("SELECT true FROM member_pref WHERE pref_id=$1 AND member_id=$2",array($pref_id,$member_id));
}

function authorize_post()
{
  if(post('password') != REGISTRATION_PASSWORD)
  {
    print "<br/><h4>Authorization failed.</h4>";
    print "<script type=\"text/javascript\">$('input[type=submit]').attr('disabled',false);</script>\n";
  }
  else
  {
    $_SESSION['authorized'] = true;
    print "<script type=\"text/javascript\">location.reload();</script>\n";
  }
  exit_clean();
}

function create_post()
{
  global $DB;
  if(session('id') || !REGISTRATION_OPEN || !session('authorized')) return to_index();

  $output = "";
  if($DB->check("SELECT true FROM member WHERE LOWER(name)=$1",array(strtolower(post('account'))))) $output .= ERROR_MEMBER_NAME_INUSE."<br/>";

  if(!eregi(MEMBER_REGEXP,post('account'))) $output .= ERROR_MEMBER_NAME."<br/>";

  if(eregi("^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$",post('email_signup')))
  {
    if(post('email_signup') != post('email_confirm')) $output .= ERROR_MEMBER_EMAIL_NOMATCH."<br/>\n";
  }
  else
  $output .= ERROR_MEMBER_EMAIL."<br/>\n";

  if($DB->check("SELECT true FROM member WHERE email_signup=$1",array(post('email_signup')))) $output .= ERROR_MEMBER_EMAIL_INUSE."<br/>";

  if(!is_numeric(post('postalcode'))) $output .= ERROR_MEMBER_POSTALCODE."<br/>\n";
  if($output != "")
  {
    print "<br/><div class=\"hr\"><hr/></div><br/>\n";
    print "<strong>$output</strong>\n";
    print "<script type=\"text/javascript\">$('input[type=submit]').attr('disabled',false);</script>\n";
  }
  else
  {
    $insert = array();
    $insert['name'] = post('account');
    $insert['email_signup'] = post('email_signup');
    $pass= rand(1000000,99999999);
    $insert['pass'] = md5($pass);
    $insert['postalcode'] = post('postalcode');
    $insert['secret'] = md5(post('secret'));
    $insert['ip'] = $_SERVER['REMOTE_ADDR'];
    $DB->insert("member",$insert);
    print "<br/><br/>";
    print ACCOUNT_CREATED;
    send_email(post('email_signup'),SIGNUP_EMAIL_SUBJECT,str_replace(array("%NAME%","%PASS%"),array(post('account'),$pass),SIGNUP_EMAIL_BODY),ADMIN_EMAIL);
    print "<script type=\"text/javascript\">$('form').remove();</script>\n";
  }
  exit_clean();
}

function edit_post()
{
  global $DB,$Security;
  if(!session('id')) return to_index();

  if(strtolower(session('name')) != strtolower(post('name')))
  {
    print "You may only change the case of your name.";
    exit_clean();
  }
  else
  {
    $update = array();
    $update['name'] = post('name');
    $update['postalcode'] = post('postalcode');
    $DB->update("member","id",session('id'),$update);
    unset($_POST['name'],$_POST['postalcode']);
  }
  if(post("_current") && post("_pass") && post("_pass_confirm"))
  {
    if(strlen(post("_pass")) < 4)
    {
      print "Your password must be at least 4 characters.";
      exit_clean();
    }
    if(post("_pass") != post("_pass_confirm"))
    {
      print "Your new passwords did not match.";
      exit_clean();
    }
    if(!$Security->auth(session('name'),post('_current')))
    {
      print "Your current password did not match our records.";
      exit_clean();
    }
    $DB->query("UPDATE member SET pass=$1 WHERE id=$2",array(md5(post('_pass')),session('id')));
    $Security->update_session(session('id'));
    $Security->setcookie();
  }
  
  foreach($_POST as $key => $value)
  {
    if(substr($key,0,1) == "_") continue;
    if($pref_id = $DB->value("SELECT id FROM pref WHERE name=$1",array($key)))
    {
      if(!pref_exists($pref_id,session('id')))
      {
        if($value == "") continue;
        $insert = array();
        $insert['pref_id'] = $pref_id;
        $insert['member_id'] = session('id');
        $insert['value'] = $value;
        $DB->insert("member_pref",$insert);
      }
      else
      {
        if($value == "") $DB->query("DELETE FROM member_pref WHERE member_id=$1 AND pref_id=$2",array(session('id'),$pref_id));
        else
        $DB->query("UPDATE
                      member_pref
                    SET
                      value=$1
                    WHERE
                      member_id=$2
                    AND
                      pref_id=$3",array($value,session('id'),$pref_id));
      }
    }
  }
  $Security->update_session(session('id'));
  exit_clean();
}

function editcolors_post()
{
  global $DB,$Core;
  $theme = array();
  foreach($_POST as $key => $val)
  {
    if(substr($key,0,1) == "_" || $key == "theme") continue;
    switch($key)
    {
      case "font":
      case "fontsize":
        break;

      case "body":
      case "even":
      case "odd":
      case "me":
      case "readbar":
        $val = "#".substr($val,0,6);
        break;
      case "hover":
        if($val == "none") $val = "transparent";
        else
        $val = "#".substr($val,0,6);
        break;
      default:
        continue;
        break;
    }
    $theme[$key] = strip_tags($val);
  }
  $save = serialize($theme);
  
  if($Core->member_pref(session('id'),"theme"))
  {
    $DB->query("UPDATE member_pref SET value=$1 WHERE member_id=$2 AND pref_id=15",array($save,session('id')));
  }
  else
  {
    $insert = array();
    $insert['member_id'] = session('id');
    $insert['pref_id'] = 15;
    $insert['value'] = $save;
    $DB->insert("member_pref",$insert);
  }
  return to_index("/");
  exit_clean();
}
?>
