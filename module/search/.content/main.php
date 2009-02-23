<?php
if(!isset($res)) // if calling from within search itself don't include structure
{
  $Base = new Base;
  $Base->type(SEARCH);
  $Base->title("Search");
  $Base->header();
}
$Form = new Form;
$Form->ajax(false);
if(isset($_SESSION['search'])) $Form->values($_SESSION['search']);
$Form->header("/search/","post",FORM_SALT);
$Form->fieldset_open("Search Information");
$Form->add_text("search","Search For:",300);
$Form->add_select("_type","Within:","Choose",array("thread"=>"Threads","thread_post"=>"Thread Posts","message"=>"Messages","message_post"=>"Message Posts"));
$Form->fieldset_close();
$Form->fieldset_open("Optional Fields");
print "<li>will return in a bit</li>\n";
/*
$Form->add_text("member","By Member:");
$Form->labels(false);
print "<li>\n";
print "  <label>Date Range:</label>\n";
$Form->add_date("start",false);
$Form->add_date("end",false);
print "</li>\n";
$Form->labels(true);
$Form->add_select("quickdate","Quick Ranges:","Choose",array("thisweek"=>"This Week","thismonth"=>"This Month","lastweek"=>"Last Week","lastmonth"=>"Last Month"),"onchange=\"quickrange($(this).val())\">");
*/

$Form->fieldset_close();

$Form->add_submit("Search");
$Form->footer();

$Form->header_validate();
$Form->add_notnull("search","Please enter a search term.");
$Form->add_notnull("_type","Please choose what to search.");
$Form->footer_validate();

if(!isset($res)) $Base->footer();
?>
<script type="text/javascript">
function quickrange(what)
{
  switch(what)
  {

    default:
      $('#start').val('no');
      $('#end').val('no');
      alert(what);
      break;
  }
}
</script>
