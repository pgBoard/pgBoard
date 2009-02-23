<?php
function number($str) { return str_replace(array("$",","),"",$str); }

$Base = new Base;
$Base->type(MISC);
$Base->title("Donate");
$Base->header();
print "<div class=\"box clear\">\n";
if(FUNDRAISER_ID != -1)
{
  $goal = $Core->fundraiser_goal();
  $total = $Core->fundraiser_total();
  if($total != "$0.00") $percent = round((number($total)*100/number($goal)),2);
  else
  $percent = "0";
  print "<h4>".$Core->fundraiser_name()." Status:</h4><br/>\n";
  print "<h4>$total <span class=\"smaller\">($percent%) of $goal raised.</span></h4><br/>\n";
}
print DONATION_TEXT;
print DONATION_BUTTON;
print "</div>\n";
$Base->footer();
?>
