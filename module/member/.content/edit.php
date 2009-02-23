<?php
if(!session('id')) return to_index();

// prep data for form
$DB->query("SELECT * FROM member m WHERE id=$1",array(session('id')));
$member = $DB->load_array();

$DB->query("SELECT
              p.name as id,
              mp.value as name
            FROM
              member_pref mp
            LEFT JOIN
              pref p
            ON
              p.id = mp.pref_id
            WHERE
                mp.member_id=$1",array(session('id')));
$prefs = $DB->load_all_key();
if(!isset($prefs['mincollapse'])) $prefs['mincollapse'] = COLLAPSE_DEFAULT;
if(!is_numeric($prefs['mincollapse'])) $prefs['mincollapse'] = COLLAPSE_DEFAULT;
if(!isset($prefs['collapseopen'])) $prefs['collapseopen'] = COLLAPSE_DEFAULT;
if(!is_numeric($prefs['collapseopen'])) $prefs['collapseopen'] = COLLAPSE_OPEN_DEFAULT;
if($prefs['collapseopen'] < 1) $prefs['collapseopen'] = 1;

$Base = new Base;
$Base->type(EDIT);
$Base->title("Account Management: $member[name]");
$Base->header();

print "<div class=\"box clear\">\n";
  
$Form = new Form;
$Form->values(array_merge($member,$prefs));
$Form->header(url(),"post",FORM_SALT);

$Form->fieldset_open("Account Management");
$Form->add_text("name","Name:");
$Form->add_text("postalcode","Postal Code:");
$Form->fieldset_close();

$Form->fieldset_open("Password Management");
print "<div id=\"password\" style=\"display:none\">\n";
$Form->add_password("_current","Current:");
$Form->add_password("_pass","New:");
$Form->add_password("_pass_confirm","Confirm:");
print "</div>\n";
$Form->add_button("_change","Change Password","change_password()","style=\"margin:5px\"");

$Form->fieldset_close();
$Form->fieldset_open("Details");

$DB->query("SELECT
              p.display,
              p.name as field,
              pt.name as type,
              p.width
            FROM
              pref p
            LEFT JOIN
              pref_type pt
            ON
              pt.id = p.pref_type_id
            WHERE
              p.editable IS true
            ORDER BY
              p.ordering");
              
while($pref = $DB->load_array())
{
  switch($pref['type'])
  {
    case "input":
      $Form->add_text($pref['field'],"$pref[display]:",$pref['width']);
      break;
    case "textarea":
      $Form->add_textarea($pref['field'],"$pref[display]:");
      break;
    case "checkbox":
      $Form->add_checkbox($pref['field'],"$pref[display]:");
      break;
  }
}
$Form->add_submit("Save Changes");
$Form->fieldset_close();
$Form->footer();
$Form->header_validate();
$Form->add_notnull("postalcode","Please enter a postal code.");
$Form->footer_validate();

$Base->footer();

print "</div>";
?>
<script type="text/javascript">
function completed() { $('.submit').attr('disabled',false); }
function change_password()
{
  if($('#password').css('display') != "block")
  {
    $('#_change').val('Cancel');
  }
  else
  {
    $('#_change').val('Change Password');
    $('#_current,#_pass,#_pass_confirm').val('');
  }
  $('#password').toggle();
};
</script>
