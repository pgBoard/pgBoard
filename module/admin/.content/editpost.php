<?php
if(!id() || !session('admin')) return to_index();

$Base = new Base;
$Base->type(EDIT);
$Base->title("Edit Post");
$Base->header();

$DB->query("SELECT thread_id,body FROM thread_post WHERE id=$1",array(id()));
$data = $DB->load_array();
$Form = new Form;
$Form->values($data);
$Form->header(url(),"post",FORM_SALT);
$Form->fieldset_open("Edit");
$Form->add_textarea("body","Body:");
$Form->fieldset_close();
$Form->add_submit(SAY_BUTTON,"id=\"submit\"/>");
$Form->footer();

$Form->header_validate();
$Form->add_notnull("body","Please enter a post body.");
$Form->footer_validate();

$Base->footer();
?>
<script type="text/javascript">
function completed(data)
{
  if(jQuery.trim(data) == "") location.href='/thread/view/<?php print $data['thread_id'];?>/';
  $('.submit').attr('disabled',false);
}
</script>
