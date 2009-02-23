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
$Form->add_text("chat",false,400,false,"onkeydown=\"return catch_enter(event)\"/>");
$Form->add_button("add","say that shit","speak();");
print "<br/>\n";
$Form->add_checkbox("stop","pause scroll","onclick=\"pause=pause?false:true\"/> pause scrolling");
$Form->fieldset_close();
$Form->footer();
$Base->footer();
?>
<script type="text/javascript">
var lasthash;
var pause = false;
function speak()
{
  var text = $('#chat').val();
  $('#add')[0].disabled = true;
  $.post("/chat/speak/",{msg:text},function(data)
  {
    $('#add')[0].disabled = false;
    $('#chat').val('');
    e('data').scrollTop = e('data').scrollHeight;
    update(false);
  });
}

function update(auto)
{
  if(pause && auto) return;
  $.get("/chat/history/"+lasthash,{},function(data)
  {
    data = jQuery.trim(data);
    if(data != lasthash)
    {
      lasthash = data.substring(0,32);
      $('#data').html(data.substring(32));
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
