<?php
class BoardStyle
{
  public $theme = false;

  function set_theme($theme) { $this->theme = $theme; }
  function get_theme() { return $this->theme; }
  
  function __construct()
  {
    global $DB,$Core;
    if(!$this->theme)
    {
      if(session('id')) $this->set_theme($Core->member_pref(session('id'),"theme"));
      if(!$this->theme) $this->set_theme($DB->value("SELECT value FROM theme WHERE main IS true"));
    }
  }
  
  function calc_color($hex)
  {
    global $colors;
    $hex = substr($hex,1);
    $rgb = explode(':',wordwrap($hex,2,':',2));
    $rgb[0] = (isset($rgb[0]) ? hexdec($rgb[0]) : 0);
    $rgb[1] = (isset($rgb[1]) ? hexdec($rgb[1]) : 0);
    $rgb[2] = (isset($rgb[2]) ? hexdec($rgb[2]) : 0);

    $m = min($rgb[0], $rgb[1], $rgb[2]);
    $n = max($rgb[0], $rgb[1], $rgb[2]);
    $lum = (double)(($m+$n)/510.0);

    if($lum < 0.45) return "#ffffff";
    else
    return "#000000";
  }
  
  function display($theme)
  {
    global $Plugin;
    $style = STYLESHEET;
    $theme = unserialize($theme);

    foreach($theme as $type => $val)
    {
      if(substr($val,0,1) != "#") continue;
      $theme[$type.'_font'] = $this->calc_color($val);
    }
    foreach($theme as $type => $val)
    {
      $style = str_replace("%".strtoupper($type)."%",$val,$style);
    }
    if(session('italicread'))
    {
      $style .= ".subject a { font-style: italic; }";
      $style .= ".read .subject a { font-style: normal; }";
    }
    if(session('notabs'))
    {
      $style .= ".nav li, .nav li:hover, .nav li a, .nav li a:hover\n";
      $style .= "{\n";
      $style .= "  background-color: transparent;\n";
      $style .= "  border: none;\n";
      $style .= "  color: $theme[body_font];\n";
      $style .= "}\n";
      $style .= ".top li { padding-bottom: 5px; }\n";
    }

    // Start Style Override
    $Plugin = new BoardPlugin;
    $data = $Plugin->style_display($style);
    // End Style Override
    
    print $style;
  }
  
  function external_css($css)
  {
    $css = htmlentities(strip_tags($css));
    if(!preg_match('|^http(s)?://[a-z0-9-]+(.[a-z0-9-]+)*(:[0-9]+)?(/.*)?$|i',$css)) return false;
    else
    {
      print "@import \"$css\";\n";
      return true;
    }
  }
}
