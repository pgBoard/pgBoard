<?php
$Base = new Base;
$Base->type(CREATE);
$Base->title("Create New Thread");
$Base->header();

$Form = new Form;
$Form->header(url(),"post",FORM_SALT);
$Form->account_login();
$Form->fieldset_open("Thread Details");
$Form->add_text("subject","Subject:",400,200);
$Form->add_textarea("body","Body:");
$Form->fieldset_close();
$Form->add_submit(SAY_BUTTON,"class=\"nodisable\"/>");
$Form->add_button("preview",PREVIEW_BUTTON,"preview_post('{$Form->name}','thread',99999999);");
print "&nbsp;<sup><a href=\"javascript:;\" onclick=\"$('#bbcode').slideToggle()\">[help]</a></sup>\n";
$Form->footer();

$Form->header_validate();
$Form->add_notnull("subject","Please enter a subject.");
$Form->add_notnull("body","Please enter a post body.");
$Form->footer_validate();

$Base->footer();

print BBCODE_GUIDE;
?>
<script type="text/javascript">
function completed(data)
{
  if(jQuery.trim(data) == "") window.location = '/';
  $('.submit').attr('disabled',false);
}
</script>
