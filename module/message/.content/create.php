<?php
$Base = new Base;
$Base->type(CREATE);
$Base->title("Create New Message");
$Base->header();

$Form = new Form;
$Form->header(url(),"post",FORM_SALT);
$Form->account_login();
$Form->fieldset_open("Message Details");

$Form->labels(false);
print "<li>\n";
print "  <label>Recipients:</label>\n";
print "  <div id=\"m\" style=\"width:500px;float:left;line-height:1.8em;\"><span id=\"notice\" class=\"small\">(invalid names will be discarded)</span></div>\n";
print "</li>\n";

print "<li>\n";
print "  <label for=\"recipients\">Add Members:</label>\n";
$Form->add_hidden("message_members");
$Form->add_text("_recipients",false,200,false,"onkeydown=\"return catch_enter(event)\"/>");
$Form->add_button("add","Add","check_member();","tabindex=\"10\"/>");
print " <sup id=\"names\">add multiples with: name, name, name</sup>";
print "</li>\n";
$Form->labels(true);

$Form->add_text("subject","Subject:",400,200);
$Form->add_textarea("body","Body:");
$Form->fieldset_close();
$Form->add_submit(SAY_BUTTON,"class=\"nodisable\"/>");
$Form->add_button("preview",PREVIEW_BUTTON,"preview_post('{$Form->name}','message',99999999);");
print "&nbsp;<sup><a href=\"javascript:;\" onclick=\"$('#bbcode').slideToggle()\">[help]</a></sup>\n";
$Form->footer();

$Form->header_validate();
$Form->add_notnull("message_members","Please enter at least one recipient.");
$Form->add_notnull("subject","Please enter a subject.");
$Form->add_notnull("body","Please enter a post body.");
$Form->footer_validate();

$Base->footer();

print BBCODE_GUIDE;
?>
<script type="text/javascript">
function completed(data)
{
  if(jQuery.trim(data) == "") window.location = '/message/';
  $('.submit').attr('disabled',false);
}
<?php
if(id())
{
  print "$(document).ready(function() {\n";
  print "  $('#_recipients').val('".htmlentities(id())."');";
  print "});\n";
}
?>
</script>
