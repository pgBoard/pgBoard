<?php
$Base = new Base;
$Base->type(MISC);
$Base->title(TITLE_CHAT);
$Base->header();
?>
<div id="data" class="box clear" style="overflow:scroll;overflow-x:hidden;height:375px;">
Loading chat history...
</div>
<?php
$Form = new Form;
$Form->labels(false);
$Form->header("/chat/speak/","post",FORM_SALT);
$Form->fieldset_open("Chat Panel");
$Form->add_text("chat",false,400,false,"autocomplete=\"off\" onkeydown=\"return catch_enter(event)\"/>");
$Form->add_button("add","say that shit","speak();");
print "<br/>\n";
$Form->add_checkbox("stop","pause scroll","onclick=\"pause=pause?false:true\"/> pause scrolling");
//$Form->add_checkbox("disablemedia","disable media",(session('hidemedia')?' checked="yes" ':'') . "onclick=\"media=!media;lasthash='';update(false);\"/> disable media");
$Form->add_checkbox("enablemedia","enable media","onclick=\"media=!media;lasthash='';update(false);\"/> enable media");
$Form->fieldset_close();
$Form->footer();
$Form->header_validate();
$Form->set_focus('chat');
$Form->footer_validate();
$Base->footer();
?>
<script type="text/javascript">
var lasthash = '';
var pause = false;
var media = <?php print /*session('hidemedia') ? */ 'false' /*: 'true' */ ;?>;
function speak()
{
  var text = $('#chat').val();
  $('#chat').val('');
  $('#add')[0].disabled = true;
  $.post("/chat/speak/",{msg:text},function(data)
  {
    $('#add')[0].disabled = false;
    e('data').scrollTop = e('data').scrollHeight;
    update(false);
  });
}

var combiner_stripper = /([^\u0300-\u036F\u1DC0-\u1DFF\u20D0-\u20FF\uFE20-\uFE2F][\u0300-\u036F\u1DC0-\u1DFF\u20D0-\u20FF\uFE20-\uFE2F])[\u0300-\u036F\u1DC0-\u1DFF\u20D0-\u20FF\uFE20-\uFE2F]*/g;
function strip_combiners (s)
{
  // Application of many Unicode combiners render annoyingly (incorrectly?) in
  // common browsers and OSes.  The following should limit each normal
  // character to a single combining mark and alleviate the problem without
  // stopping many legitimate uses of combining marks.
  // Doing it on the client for now so that the server isn't burdened with it.
  return s.replace(combiner_stripper, "\$1");
}

function update(auto)
{
  if(pause && auto) return;
  $.get("/chat/history/"+lasthash+"/&media="+(media?'enabled':'disabled'),{},function(data)
  {
    data = jQuery.trim(data);
    if(data != lasthash)
    {
      lasthash = data.substring(0,32);
      $('#data').html(strip_combiners(data.substring(32)));
      $('#data')[0].scrollTop = $('#data')[0].scrollHeight;
    }
  });
}

function catch_enter(e)
{
  var code = e ? e.keyCode : event.keyCod;
  if(code == 13)
  {
    speak();
    return false;
  }
  else
  return true;
}
setInterval(function(){update(true)},2000);
</script>
