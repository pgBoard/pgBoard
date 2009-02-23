<?php
if(session('id') || !REGISTRATION_OPEN) return to_index();

$Base = new Base;
$Base->type(CREATE);
$Base->title("Create Account");
$Base->header();

print "<div class=\"box clear\">\n";
print LEGAL;

if(!REGISTRATION_PASSWORD && !session('authorized')) $_SESSION['authorized'] = true;


if(REGISTRATION_PASSWORD && !session('authorized'))
{
  $Form = new Form;
  $Form->header(url(0,-2)."authorize/","post",FORM_SALT);
  
  $Form->fieldset_open("Registration Authorization");
  $Form->add_password("password","Password:");
  $Form->fieldset_close();
  $Form->add_submit("Authorize Me");
  $Form->footer();
}

if(session('authorized'))
{
  $Form = new Form;
  $Form->header(url(),"post",FORM_SALT);

  $Form->fieldset_open("Create Account");
  $Form->add_text("account","Name:",150);
  $Form->add_text("secret","Secret Word:",150,false,"/><span class=\"small\">(to recover forgotten password)</span>");
  $Form->add_text("email_signup","Email:",200);
  $Form->add_text("email_confirm","Email (confirm):",200);
  $Form->add_text("postalcode","Postal Code:",75);
  $Form->fieldset_close();
  $Form->add_submit("Create Account");
  $Form->footer();

  $Form->header_validate();
  $Form->add_notnull("account","Please enter an account name.");
  $Form->add_notnull("secret","Please enter a secret word for password recovery.");
  $Form->add_notnull("email_signup","Please enter an email address.");
  $Form->add_notnull("email_confirm","Please confirm your email address.");
  $Form->add_notnull("postalcode","Please enter a postal code.");
  $Form->footer_validate();
}

$Base->footer();

print "</div>";
?>
