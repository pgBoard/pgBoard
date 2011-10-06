<?php
if($DB->value("SELECT locked FROM thread WHERE id=$1",array(id())) == 't') return;

$Form = new Form;
$Form->header(url(0,1)."reply","post",FORM_SALT);
$Form->values(array("thread_id"=>id(true)));
$Form->add_hidden("thread_id");
$Form->account_login();

$Form->fieldset_open("Reply");
$Form->add_textarea("body","Body:");
$Form->fieldset_close();
$Form->add_submit(SAY_BUTTON,"id=\"submit\"/>");
$Form->add_button("preview",PREVIEW_BUTTON,"preview_post('{$Form->name}','thread',".id().");");
if(id() == 362137)
{
  print "&nbsp;";
  $Form->add_button("_load","load bradyism","$(this).val('loading...');$.post('/thread/view/bradyism/',{},function(data){ $('#body').val($.trim(data));$('#_load').val('load bradyism')});");
}
print "&nbsp;<sup><a href=\"javascript:;\" onclick=\"$('#bbcode').slideToggle()\">[help]</a></sup>\n";
$Form->footer();

$Form->header_validate();
$Form->add_notnull("body","Please enter a post body.");
$Form->footer_validate();

print BBCODE_GUIDE;
?>
<script type="text/javascript">
function completed(data)
{
  if(jQuery.trim(data) == "")
  {
    loadposts('thread',false);
    e('<?php print $Form->name;?>').reset();
  }
  $('.submit').attr('disabled',false);
}
</script>
