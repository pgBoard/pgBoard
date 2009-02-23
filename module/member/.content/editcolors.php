<?php
global $Style;

if(get('theme'))
if($theme = $DB->value("SELECT value FROM theme WHERE name=$1",array(get('theme'))))
{
  $Style->set_theme($theme);
}
$theme = unserialize($Style->theme);
foreach($theme as $type => $val) if(substr($val,0,1) == "#") $theme[$type] = substr($val,1);

$fontsizes = array("1"=>"10pt",
                   "1.1"=>"11pt",
                   "1.2"=>"12pt",
                   "1.3"=>"13pt",
                   "1.4"=>"14pt");
?>
<!--
Copyright (c) 2007 John Dyer (http://johndyer.name)

Permission is hereby granted, free of charge, to any person
obtaining a copy of this software and associated documentation
files (the "Software"), to deal in the Software without
restriction, including without limitation the rights to use,
copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the
Software is furnished to do so, subject to the following
conditions:

The above copyright notice and this permission notice shall be
included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES
OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR
OTHER DEALINGS IN THE SOFTWARE.
-->
<style type="text/css">
.picker
{
  float: left;
  margin-right: 10px;
}
.picker ul li
{
  list-style: none;
  margin-top: 8px;
}
</style>
<script type="text/javascript" src="/lib/colorpicker/prototype.js"></script>
<script type="text/javascript" src="/lib/colorpicker/colormethods.js"></script>
<script type="text/javascript" src="/lib/colorpicker/colorvaluepicker.js"></script>
<script type="text/javascript" src="/lib/colorpicker/slider.js"></script>
<script type="text/javascript" src="/lib/colorpicker/colorpicker.js"></script>
<?php
$Base = new Base;
$Base->type(EDIT);
$Base->title("Color Theme Styler");
$Base->header();
?>
<div style="padding-top:25px;width:50%;float:left">
<div class="picker"><div id="cp1_ColorMap"></div></div>
<div class="picker"><div id="cp1_ColorBar"></div></div>
<div class="picker">
<div id="cp1_Preview" style="background-color:#fff;width:60px;height:60px;padding:0;margin:0;border:solid 1px #000;"><br/></div>
<ul>
  <li>
    <input type="radio" id="cp1_HueRadio" name="cp1_Mode" value="0" />
    <label for="cp1_HueRadio">H:</label>
    <input type="text" id="cp1_Hue" value="0" style="width: 40px;" /> &deg;
  </li>
  <li>
    <input type="radio" id="cp1_SaturationRadio" name="cp1_Mode" value="1" />
    <label for="cp1_SaturationRadio">S:</label>
    <input type="text" id="cp1_Saturation" value="100" style="width: 40px;" /> %
  </li>
  <li>
    <input type="radio" id="cp1_BrightnessRadio" name="cp1_Mode" value="2" />
    <label for="cp1_BrightnessRadio">B:</label>
    <input type="text" id="cp1_Brightness" value="100" style="width: 40px;" /> %
  </li>
  <li>
    <input type="radio" id="cp1_RedRadio" name="cp1_Mode" value="r" />
    <label for="cp1_RedRadio">R:</label>
    <input type="text" id="cp1_Red" value="255" style="width: 40px;" />
  </li>
  <li>
    <input type="radio" id="cp1_GreenRadio" name="cp1_Mode" value="g" />
    <label for="cp1_GreenRadio">G:</label>
    <input type="text" id="cp1_Green" value="0" style="width: 40px;" />
  </li>
  <li>
    <input type="radio" id="cp1_BlueRadio" name="cp1_Mode" value="b" />
    <label for="cp1_BlueRadio">B:</label>
    <input type="text" id="cp1_Blue" value="0" style="width: 40px;" />
  </li>
  <li>
    #: <input type="text" id="cp1_Hex" value="FF0000" style="width: 60px;"/>
  </li>
</ul>
</div>
<div style="display:none;">
  <img src="/lib/colorpicker/rangearrows.gif" />
  <img src="/lib/colorpicker/mappoint.gif" />
  <img src="/lib/colorpicker/bar-saturation.png" />
  <img src="/lib/colorpicker/bar-brightness.png" />
  <img src="/lib/colorpicker/bar-blue-tl.png" />
  <img src="/lib/colorpicker/bar-blue-tr.png" />
  <img src="/lib/colorpicker/bar-blue-bl.png" />
  <img src="/lib/colorpicker/bar-blue-br.png" />
  <img src="/lib/colorpicker/bar-red-tl.png" />
  <img src="/lib/colorpicker/bar-red-tr.png" />
  <img src="/lib/colorpicker/bar-red-bl.png" />
  <img src="/lib/colorpicker/bar-red-br.png" />
  <img src="/lib/colorpicker/bar-green-tl.png" />
  <img src="/lib/colorpicker/bar-green-tr.png" />
  <img src="/lib/colorpicker/bar-green-bl.png" />
  <img src="/lib/colorpicker/bar-green-br.png" />
  <img src="/lib/colorpicker/map-red-max.png" />
  <img src="/lib/colorpicker/map-red-min.png" />
  <img src="/lib/colorpicker/map-green-max.png" />
  <img src="/lib/colorpicker/map-green-min.png" />
  <img src="/lib/colorpicker/map-blue-max.png" />
  <img src="/lib/colorpicker/map-blue-min.png" />
  <img src="/lib/colorpicker/map-saturation.png" />
  <img src="/lib/colorpicker/map-saturation-overlay.png" />
  <img src="/lib/colorpicker/map-brightness.png" />
  <img src="/lib/colorpicker/map-hue.png" />
</div>
<script type="text/javascript">
Event.observe(window,'load',function() { cp1 = new Refresh.Web.ColorPicker('cp1',{startHex: '<?php print $theme['body']; ?>', startMode:'s'});});
</script>
</div>
<script type="text/javascript">
var last;
function update()
{
  var type = jQuery('[name=type]').fieldValue();
  var val = jQuery('#cp1_Hex').val();
  setTimeout("update();",100);
  if(last != val)
  {
    jQuery('#'+type).val(val);
    preview_theme();
  }
  last = val;
}
setTimeout("update();",100);

function preview_theme()
{
  jQuery('body').css('font-family',jQuery('#font').val());
  jQuery('html').css('font-size',jQuery('#fontsize').val()+'em');
  jQuery('body').css('background-color','#'+jQuery('#body').val());
  jQuery('.even').css('background-color','#'+jQuery('#even').val());
  jQuery('.odd').css('background-color','#'+jQuery('#odd').val());
  jQuery('.me').css('background-color','#'+jQuery('#me').val());
  jQuery('.read').css('border-left','3px solid #'+jQuery('#readbar').val());
  jQuery('.read').css('border-right','3px solid #'+jQuery('#readbar').val());
  if(jQuery('#hover').val() == 'none')
  {
    jQuery('body').append("<style type=text/css>.list:hover { background-color: transparent !important; }</style>");
  }
  else
  jQuery('body').append("<style type=text/css>.list:hover { background-color: #"+jQuery('#hover').val()+"; }</style>");
}
</script>
<div style="float:left;width:50%">
<?php
$Form = new Form;
if($theme['hover'] == "transparent") $theme['hover'] = "none";
$Form->values(array_merge($theme,get('theme')?array("theme"=>get('theme')):array()));
$Form->header(url(),"post",FORM_SALT);

$Form->fieldset_open("Theme Options");
$Form->add_text("font","Font Family:",250);
$Form->add_select("fontsize","Font Size:","Select Size",$fontsizes);
$Form->add_text("body","Background #:",50,6,"/> <input type=\"radio\" id=\"type\" name=\"type\" value=\"body\" checked=\"true\"/>");
$Form->add_text("even","Even #:",50,6,"/> <input type=\"radio\" id=\"type\" name=\"type\" value=\"even\"/>");
$Form->add_text("odd","Odd #:",50,6,"/> <input type=\"radio\" id=\"type\" name=\"type\" value=\"odd\"/>");
$Form->add_text("me","My Posts #:",50,6,"/> <input type=\"radio\" id=\"type\" name=\"type\" value=\"me\"/>");
$Form->add_text("hover","Hover Bar #:<br/><span class=\"small\">(or 'none')</span>",50,6,"/> <input type=\"radio\" id=\"type\" name=\"type\" value=\"hover\"/>");
$Form->add_text("readbar","Read Bars #:",50,6,"/> <input type=\"radio\" id=\"type\" name=\"type\" value=\"readbar\"/>");
$Form->fieldset_close();

$Form->add_submit("Save");
$Form->add_button("preview","Preview","preview_theme()");

$Form->labels(false);
$Form->fieldset_open("Preset Themes");
$DB->query("SELECT name as id,name FROM theme");
$Form->add_select("theme","Themes:","Select Theme",$DB->load_all_key());
$Form->add_button("preview","Preview","if(jQuery('#theme')[0].selectedIndex) location.href='/member/editcolors/&theme='+jQuery('#theme')[0][jQuery('#theme')[0].selectedIndex].value;");
$Form->fieldset_close();

$Form->footer();
?>
</div>
<div class="clear"><br/></div>
<div class="hr"><hr/></div><br/>
<?php
$list = array();
$list[0][LIST_ID] = 0;
$list[0][LIST_DATE_LAST_POST] = time();
$list[0][LIST_CREATOR_ID] = 0;
$list[0][LIST_CREATOR_NAME] = "color scheme";
$list[0][LIST_LAST_POSTER_ID] = 0;
$list[0][LIST_LAST_POSTER_NAME] = "color scheme";
$list[0][LIST_SUBJECT] = "color schemer";
$list[0][LIST_POSTS] = "5";
$list[0][LIST_VIEWS] = "5";
$list[0][LIST_FIRSTPOST_BODY] = "test";
$list[0][LIST_LAST_VIEW_POSTS] = "";
$list[0][LIST_DOTFLAG] = "";
$list[0][LIST_STICKY] = "";
$list[0][LIST_LOCKED] = "";
$list[0][LIST_LEGENDARY] = "";
$list[1] = $list[0];
$list[2] = $list[0];
$list[2][LIST_CREATOR_ID] = session('id');
$list[2][LIST_CREATOR_NAME] = $Core->namefromid(session('id'));
$list[3] = $list[0];
$List = new BoardList;
$List->type(LIST_THREAD);
$List->data($list);
$List->thread();

print "<br/>";

$view = array();
$view[0][VIEW_ID] = "";
$view[0][VIEW_DATE_POSTED] = time();
$view[0][VIEW_CREATOR_ID] = 0;
$view[0][VIEW_CREATOR_NAME] = "color scheme";
$view[0][VIEW_BODY] = "color scheme post text";
$view[0][VIEW_CREATOR_IP] = "";
$view[0][VIEW_SUBJECT] = "";
$view[0][VIEW_THREAD_ID] = "";
$view[0][VIEW_CREATOR_IS_ADMIN] = 'f';

$view[1] = $view[0];
$view[2] = $view[0];
$view[2][VIEW_CREATOR_ID] = session('id');
$view[2][VIEW_CREATOR_NAME] = $Core->namefromid(session('id'));
$view[3] = $view[0];

// use standard board display to build preview
$View = new BoardView;
$View->type(VIEW_THREAD_PREVIEW);
$View->data($view);
$View->thread();

$Base->footer();
?>
