<?php
function logout_get()
{
  global $Security;
  $Security->logout();
}

function changelog_get()
{
  $Base = new Base;
  $Base->type(MISC);
  $Base->title("pgBoard v".VERSION.SPACE.ARROW_RIGHT." Change Log");
  $Base->header();
  print "<br/>\n";
  print "<div style=\"font: 12px courier new\">";
  print str_replace("»","&raquo;",nl2br(file_get_contents(DIR."/CHANGELOG")));
  print "</pre>";
  print "<br/>\n";
  $Base->footer();
}

function status_get()
{
  global $DB,$Core,$Parse;

  if(!session('id')) return;

  $active = $Core->active_members();
  $posting = array_keys($Core->posting_members());
  $lurking = array_keys($Core->lurking_members());
  $chatting = array_keys($Core->chatting_members());

  $Base = new Base;
  $Base->type(MISC);
  $Base->title("Board Status");
  $Base->header();
  $output = "";
  $output .= "key:<br/>\n";
  $output .= "&nbsp;normal - viewing<br />";
  $output .= "&nbsp;<strong>bold</strong> - posting<br/>";
  $output .= "&nbsp;<u>underline</u> - lurking<br/>";
  $output .= "&nbsp;<strike>strikethrough</strike> - chatting<br/><br/>";
  print "<div class=\"box clear\">\n";

  $name_output = "";
  foreach($active as $id => $name)
  {
    $name = $Core->member_link($name);
    if(in_array($id,$posting)) $name = "<strong>$name</strong>";
    if(in_array($id,$lurking)) $name = "<span class=\"lurker\">$name</span>";
    if(in_array($id,$chatting)) $name = "<strike>$name</strike>";
    $name_output .= "$name, ";
  }
  print $output;
  print substr($name_output,0,-2);


  if(IGNORE_ENABLED && IGNORE_PUBLIC)
  {
    $DB->query("SELECT
                  count(*) as num,
                  m.name
                FROM
                  member_ignore mi
                LEFT JOIN
                  member m
                ON
                  m.id = mi.ignore_member_id
                GROUP BY
                  m.name
                ORDER BY num DESC
                LIMIT 25");
    print "<br/><br/><strong>top 25 ignored posters:</strong><br/><br/>";
    print "<ol style=\"padding-left:30px\">\n";
    while($row = $DB->load_array()) print "  <li>".$Core->member_link($row['name'])." ($row[num])</li>\n";
    print "</ol>\n";
  
    $DB->query("SELECT
                  count(*) as num,
                  m.name
                FROM
                  member_ignore mi
                LEFT JOIN
                  member m
                ON
                  m.id = mi.member_id
                GROUP BY
                  m.name
                ORDER BY num DESC
                LIMIT 25");
    print "<br/><br/><strong>top 25 posters using ignore:</strong><br/><br/>";
    print "<ol style=\"padding-left:30px\">\n";
    while($row = $DB->load_array()) print "  <li>".$Core->member_link($row['name'])." ($row[num])</li>\n";
    print "</ol>\n";
  }
  
  $DB->query("SELECT
                count(*) as num,
                (SELECT subject FROM thread WHERE id=f.thread_id) as subject,
                f.thread_id as id
              FROM
                favorite f
              GROUP BY
                f.thread_id
              ORDER BY num DESC
              LIMIT 25");
  
  print "<br/><br/><strong>top 25 favorited threads:</strong><br/><br/>";
  print "<ol style=\"padding-left:30px\">\n";
  while($row = $DB->load_array()) print "  <li><a href=\"/thread/view/$row[id]/\">".strip_tags($row['subject'])."</a> ($row[num])</li>\n";
  print "</ol>\n";
  
  print "</div>";
  $Base->footer();
}
?>
