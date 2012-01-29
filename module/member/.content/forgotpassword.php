<?php
if(session('id')) return to_index();

$Base = new Base;
$Base->title("Forgotten Password");
$Base->type(CREATE);
$Base->header();

print "<div class=\"box clear\">\n";
print FORGOT_PASSWORD;

$Form = new Form;
$Form->ajax(false);
$Form->header(url(),"post",FORM_SALT);
  
$Form->fieldset_open("Reset Password");
$Form->add_text("email_signup","Email Signup:");
$Form->fieldset_close();
$Form->add_submit("Send Reset Email");
$Form->footer();

$Form->header_validate();
$Form->add_notnull("email_signup","Please enter an email address.");
$Form->footer_validate();
print "</div>";

$Base->footer();

?>
