<?php
define("FORM_HIDDEN",100);
define("FORM_TEXT",200);
define("FORM_PASSWORD",300);
define("FORM_TEXTAREA",400);
define("FORM_SELECT",500);
define("FORM_CHECKBOX",600);
define("FORM_RADIO",700);
define("FORM_BUTTON",800);
define("FORM_SUBMIT",900);
define("FORM_DATE",1000);

class Form
{
  public $name = "form";
  public $ajax = true;
  public $values = array();
  public $labels = true;

  function name($name) { $this->name = $name; }
  function ajax($ajax) { $this->ajax = $ajax; }
  function values($values) { $this->values = $values; }
  function labels($labels) { $this->labels = $labels; }
  
  function header($action,$method,$__fac__)
  {
    print "<div id=\"response_{$this->name}\"></div>\n";
    print "<form method=\"$method\" name=\"{$this->name}\" id=\"{$this->name}\" class=\"coreform\" action=\"$action\" onsubmit=\"return capture_submit(this,".($this->ajax?'true':'false').");\">\n";
    print "<input type=\"hidden\" name=\"_fac\" id=\"_fac\" value=\"$__fac__\"/>\n";
  }
  function footer() { print "</form>\n"; }
    
  function fieldset_open($title)
  {
    print "<fieldset>\n";
    print "  <legend>$title</legend>\n";
    print "  <ol>\n";
  }
  
  function fieldset_close()
  {
    print "  </ol>\n";
    print "</fieldset>\n";
  }

  function add_hidden($name)
  {
    $curr = new stdClass();
    $curr->type  = FORM_HIDDEN;
    $curr->name  = $name;
    return $this->build_element($curr);
  }

  // fix this stupid noclose hack
  function add_text($name,$title,$width=150,$max=false,$extra="/>")
  {
    $curr = new stdClass();
    $curr->type  = FORM_TEXT;
    $curr->name  = $name;
    $curr->title = $title;
    $curr->width = $width;
    $curr->extra = $extra;
    if($max) $curr->max = $max;
    return $this->build_element($curr);
  }
  
  function add_date($name,$title,$width=100)
  {
    $init = "/><script type=\"text/javascript\">$('#{$name}').datePicker({startDate:'2005-01-01'});</script>\n";
    return $this->add_text($name,$title,$width,$max=false,$init);
  }

  function add_password($name,$title,$width=150,$extra="/>")
  {
    $curr = new stdClass();
    $curr->type  = FORM_PASSWORD;
    $curr->name  = $name;
    $curr->title = $title;
    $curr->width = $width;
    $curr->extra = $extra;
    return $this->build_element($curr);
  }

  function add_textarea($name,$title,$height=100,$width=600,$extra=">")
  {
    $curr = new stdClass();
    $curr->type   = FORM_TEXTAREA;
    $curr->name   = $name;
    $curr->title = $title;
    $curr->height = $height;
    $curr->width  = $width;
    $curr->extra = $extra;
    return $this->build_element($curr);
  }
  
  function add_select($name,$title,$initial,$data=false,$extra=">")
  {
    $curr = new stdClass();
    $curr->type    = FORM_SELECT;
    $curr->name    = $name;
    $curr->title   = $title;
    $curr->initial = $initial;
    $curr->data    = $data;
    $curr->extra = $extra;
    return $this->build_element($curr);
  }

  function add_checkbox($name,$title,$extra="/>")
  {
    $curr = new stdClass();
    $curr->type  = FORM_CHECKBOX;
    $curr->name  = $name;
    $curr->title = $title;
    $curr->extra = $extra;

    return $this->build_element($curr);
  }

  function add_radio($name,$title,$val,$id,$extra="/>")
  {
    $curr = new stdClass();
    $curr->type  = FORM_RADIO;
    $curr->name  = $name;
    $curr->title = $title;
    $curr->val   = $val;
    $curr->id    = $id;
    $curr->extra = $extra;

    return $this->build_element($curr);
  }
  
  function add_button($name,$value,$func=false,$extra="/>")
  {
    $curr = new stdClass();
    $curr->type  = FORM_BUTTON;
    $curr->name  = $name;
    $curr->value = $value;
    $curr->func  = $func;
    $curr->extra = $extra;
    return $this->build_element($curr);
  }

  function add_submit($value,$extra="/>")
  {
    $curr = new stdClass();
    $curr->type  = FORM_SUBMIT;
    $curr->value = $value;
    $curr->extra = $extra;
    return $this->build_element($curr);
  }
  
  function add_data($title,$text)
  {
    $output = "";
    $output .= "  <li>\n";
    $output .= "    <label>$title</label>\n";
    $output .= "    <p style=\"line-height:21px;float:left\">$text</p>\n";
    $output .= "    <div class=\"clear\"></div>\n";
    $output .= "  </li>\n";
    print $output;
  }

  function build_hidden($ob)
  {
    return "<input type=\"hidden\" name=\"$ob->name\" id=\"$ob->name\" value=\"$ob->value\"/>\n";
  }

  function build_text($ob)
  {
    $max = isset($ob->max) ? " maxlength=\"$ob->max\"" : "";
    return "<input type=\"text\" name=\"$ob->name\" id=\"$ob->name\" value=\"$ob->value\" style=\"width:{$ob->width}px;\"$max$ob->extra";
  }

  function build_password($ob)
  {
    return "<input type=\"password\" name=\"$ob->name\" id=\"$ob->name\" value=\"$ob->value\" style=\"width:{$ob->width}px;\"$ob->extra";
  }
  
  function build_textarea($ob)
  {
    $output = "<textarea name=\"$ob->name\" id=\"$ob->name\" style=\"float:left;height:{$ob->height}px;width:{$ob->width}px;\"{$ob->extra}$ob->value</textarea>";
    return $output;
  }

  function build_select($ob)
  {
    $output = "";
    $output .= "<select name=\"$ob->name\" id=\"$ob->name\"$ob->extra\n";
    if($ob->initial) $output .= "  <option value=\"\">$ob->initial</option>\n";

    if(!isset($this->values[$ob->name])) $this->values[$ob->name] = "";
    foreach($ob->data as $val => $key)
    {
      $key = strip_tags($key);
      if($val == $this->values[$ob->name]) $output .= "  <option value=\"$val\" SELECTED>$key</option>\n";
      else
      $output .= "  <option value=\"$val\">$key</option>\n";
    }
    $output .= "</select>\n";
    return $output;
  }

  function build_checkbox($ob)
  {
    $checked = ($ob->value == 't' || $ob->value == 'true') ? " checked=\"checked\" " : " ";
    return "<input type=\"hidden\" name=\"$ob->name\" value=\"false\"/>\n<input type=\"checkbox\" value=\"true\" name=\"$ob->name\" id=\"$ob->name\"{$checked}{$ob->extra}";
  }

  function build_radio($ob)
  {
    $checked = $ob->value == $ob->val ? " checked=\"checked\" " : " ";
    return "<input type=\"radio\" value=\"$ob->val\" name=\"$ob->name\" id=\"$ob->id\"{$checked}{$ob->extra}";
  }

  function build_button($ob)
  {
    return "<input type=\"button\" name=\"$ob->name\" id=\"$ob->name\" value=\"$ob->value\" onclick=\"$ob->func\"$ob->extra";
  }
  
  function build_submit($ob)
  {
    return "<input type=\"submit\" class=\"submit\" value=\"$ob->value\"$ob->extra\n";
  }

  function build_element($ob)
  {
    if($ob->type != FORM_SUBMIT && $ob->type != FORM_BUTTON)
    {
      if(isset($this->values[$ob->name]))
      {
        if($ob->type != FORM_TEXTAREA && !is_array($this->values[$ob->name]))
        {
          $ob->value=htmlentities($this->values[$ob->name]);
        }
        else
        $ob->value=$this->values[$ob->name];
      }
      else
      $ob->value = "";
    }

    $ob->extra = isset($ob->extra) ? " $ob->extra" : "";
    
    switch($ob->type)
    {
      case FORM_HIDDEN:      $buff = $this->build_hidden($ob);      break;
      case FORM_TEXT:        $buff = $this->build_text($ob);        break;
      case FORM_PASSWORD:    $buff = $this->build_password($ob);    break;
      case FORM_TEXTAREA:    $buff = $this->build_textarea($ob);    break;
      case FORM_SELECT:      $buff = $this->build_select($ob);      break;
      case FORM_CHECKBOX:    $buff = $this->build_checkbox($ob);    break;
      case FORM_RADIO:       $buff = $this->build_radio($ob);       break;
      case FORM_BUTTON:      $buff = $this->build_button($ob);      break;
      case FORM_SUBMIT:      $buff = $this->build_submit($ob);      break;
    }
    $output = "";
    if(isset($ob->title) && $this->labels)
    {
      $output .= "    <li>\n";
      $output .= "      <label id=\"label_{$ob->name}\" for=\"{$ob->name}\">{$ob->title}</label>\n";
      $output .= "      {$buff}\n";
      $output .= "      <div class=\"clear\"></div>\n";
      $output .= "    </li>\n";
    }
    else
    $output = $buff;
      
    print $output;
  }
  
  // clean up completed function branch
  function header_validate()
  {
    print "<script type=\"text/javascript\">\n";
    print "$(document).ready(function()\n";
    print "{\n";
  }

  function add_notnull($id,$msg)
  {
    $msg = addslashes($msg);
    print "  $('#$id').attr('notnull','$msg').addClass('validate_{$this->name}');\n";
  }

  function footer_validate()
  {
    print "});\n";
    print "</script>\n";
  }

  function account_login()
  {
    $this->fieldset_open("Account");
    if(session('id'))
    {
      print "<li id=\"loggedin\">\n";
      print "<h4 style=\"display:inline\">".session('name')."</h4>&nbsp;\n";
      print "(<a href=\"javascript:;\" onclick=\"$('#loggedin').hide();$('#account').show();\">post as someone else</a>)\n";
      print "</li>\n";
      print "<span id=\"account\" style=\"display:none\">\n";
    }
    $this->add_text("name","Name:");
    $this->add_password("pass","Password:");
    if(session('id')) print "</span>\n";
    $this->fieldset_close();
  }
}
?>
