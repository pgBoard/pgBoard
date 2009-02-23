<?php
class BoardSecurity
{
  function allowed()
  {
    global $_allowed_;
    if(!session('id'))
    {
      if(!in_array(implode("-",module()).func(),$_allowed_))
      {
        $Base = new Base;
        $Base->type(ERROR);
        $Base->title(ERROR_MUST_LOGIN);
        $Base->header();
        $Base->footer();
        return false;
      }
      else
      return true;
    }
    else
    return true;
  }

  function auth_control()
  {
    if(!session('id'))
    {
      print "  <form id=\"auth\" method=\"post\" action=\"/main/login/\">\n";
      print "  <fieldset style=\"border:none\">\n";
      print "    <ol>\n";
      print "      <li>\n";
      print "        <label for=\"name\">name:</label>\n";
      print "        <input type=\"text\" name=\"name\" id=\"name\"/>\n";
      print "      </li>\n";
      print "      <li>\n";
      print "        <label for=\"pass\">pass:</label>\n";
      print "        <input type=\"password\" name=\"pass\" id=\"pass\"/>\n";
      print "      </li>\n";
      print "      <li>\n";
      print "        <label for=\"login\">&nbsp;</label>\n";
      print "        <input type=\"submit\" class=\"loginbutton nodisable\" id=\"login\" name=\"login\" value=\"login\"/>\n";
      print "      </li>\n";
      print "    </ol>\n";
      print "    </fieldset>\n";
      print "  </form>\n";
      print "  <script type=\"text/javascript\">e('name').focus();</script>\n";
    }
    else
    {
      print "  <div id=\"auth\">\n";
      print "  logged in as <h4 style=\"display:inline\"><a href=\"/member/edit/\">".session('name')."</a></h4><br/>\n";
      print "  <a href=\"/main/logout/".MD5(session_id())."/\">logout</a>\n";
      print "  </div>\n";
    }
    print "  <div class=\"clear\"></div>\n";
  }

  function auth($name,$pass,$field="pass",$prehash=false)
  {
    global $DB;
    if(!$prehash)
    {
      //$pass = MD5($__salt__.$pass.strtolower($login));
      $pass = MD5($pass);
    }
    return $DB->value("SELECT id FROM member WHERE LOWER(name)=LOWER($1) AND $field=$2",array($name,$pass));
  }
  
  function setcookie()
  {
    global $DB;
    $year = 31536000;
    $month = $year/12;
    $day = $year/365;
    switch(session('duration'))
    {
      case "day":
        $duration = time()+$day;
        break;
      case "month":
        $duration = time()+$month;
        break;
      case "year":
        $duration = time()+$year;
        break;
      default:
        $duration = 0;
        break;
    }
    setcookie("board",base64_encode(session('name')."|".session('cookie')."|".session('duration')),$duration,"/",$_SERVER['SERVER_NAME']);
    $DB->query("UPDATE member SET cookie=$1 WHERE id=$2",array(session('cookie'),session('id')));
  }

  function update_session($member_id)
  {
    global $DB;
    $_SESSION = array();
    $DB->query("SELECT * FROM member WHERE id=$1",array($member_id));
    $data = $DB->load_array();
    $_SESSION['id'] = $data['id'];
    $_SESSION['name'] = $data['name'];
    $_SESSION['admin'] = $data['is_admin'] == 't' ? true : false;
    $_SESSION['cookie'] = md5($data['email_signup'].$data['pass']);
    $_SESSION['duration'] = "year";

    $DB->query("SELECT
                  p.name,
                  mp.value
                FROM
                  member_pref mp
                LEFT JOIN
                  pref p
                ON
                  p.id=mp.pref_id
                WHERE
                  mp.member_id=$1
                AND
                  p.session IS true",array($member_id));
    while($pref = $DB->load_array())
    {
      if($pref['value'] == "t" || $pref['value'] == "true") $_SESSION[$pref['name']] = true;
      else
      if($pref['value'] == "f" || $pref['value'] == "false") $_SESSION[$pref['name']] = false;
      else
      $_SESSION[$pref['name']] = $pref['value'];
    }
  }
  
  function login($name,$pass,$field="pass",$prehash=false)
  {
    global $DB;
    if($login = $this->auth($name,$pass,$field,$prehash))
    {
      $this->update_session($login);
      $this->setcookie();
      return true;
    }
    else
    return false;
  }
  
  function login_cookie()
  {
    if(cookie('board'))
    {
      $data = explode("|",base64_decode(cookie('board')));
      $this->login($data[0],$data[1],"cookie",true);
    }
  }

  function form_login($name=false,$pass=false)
  {
    // if logged in and we didn't send a different username flag member_id as session_id
    if(session('id') && ($name == "" && $pass == "")) return session('id');
    else
    if($login = $this->auth($name,$pass)) return $login;
    else
    {
      print ERROR_AUTH;
      return false;
    }
  }
  
  function is_admin($id)
  {
    global $DB;
    if(!is_numeric($id)) return false;
    else
    return $DB->check("SELECT true FROM member WHERE is_admin IS true AND id=$1",array($id));
  }
  
  function logout()
  {
    if(id() != MD5(session_id())) return;
    session_destroy();
    setcookie("board","",0,"/",$_SERVER['SERVER_NAME']);
    unset($_COOKIE);
    
    if(get('login')) return to_index("/main/login/");
    else
    return to_index('/');
  }
}
