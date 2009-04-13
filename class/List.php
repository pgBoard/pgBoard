<?php
class BoardList extends Base
{
  public $data;
  public $favorites;
  function data($data) { $this->data = $data; }

  function prep_favorites()
  {
    global $DB;
    $DB->query("SELECT thread_id FROM favorite WHERE member_id=$1",array(session('id')));
    $favs= $DB->load_all("thread_id");
    $this->favorites = $favs ? $favs : array();
  }

  function prep_data($row)
  {
    global $Parse;
  
    $data = array_values($row);
    
    // start shared parsing
    $data['date'] = date(LIST_DATE_FORMAT,$data[LIST_DATE_LAST_POST]);
    $data['subject'] = htmlentities($data[LIST_SUBJECT]);

    $data['dot'] = $data['fav'] = $data['read'] = $data['me'] = "";
    $data['body'] = $Parse->run($data[LIST_FIRSTPOST_BODY]);

    if(session('id'))
    {
      if($data[LIST_CREATOR_ID] == session('id')) $data['me'] = SPACE.CSS_ME;
      if(in_array($data[LIST_ID],$this->favorites)) $data['fav'] = LIST_FAV.NON_BREAKING_SPACE;
    }
    // end shared parsing

    // start list specific parsing
    switch($this->type)
    {
      case LIST_THREAD:
      case LIST_THREAD_HISTORY:
      case LIST_THREAD_SEARCH:
        if($data[LIST_STICKY] == "t") $data['subject'] = STICKY_TEXT.NON_BREAKING_SPACE.$data['subject'];
        if(session('id'))
        {
          if($data[LIST_LAST_POSTER_ID] != session('id') && $data[LIST_DOTFLAG] == "t") $data['dot'] = LIST_DOT;
          if($data[LIST_POSTS] != $data[LIST_LAST_VIEW_POSTS]) $data['read'] = SPACE.CSS_READ;
        }
        break;
      case LIST_MESSAGE:
        if(session('id'))
        {
          if($data[LIST_LAST_POSTER_ID] != session('id') && $data[LIST_DOTFLAG] == "t") $data['dot'] = LIST_DOT;
          if($data[LIST_POSTS] != $data[LIST_LAST_VIEW_POSTS])
          {
            $data['read'] = SPACE.CSS_READ;
            $data['subject'] = "<strong>$data[subject]</strong>";
          }
        }
        break;
    }
    // end list specific parsing
    
    // Start Parsing Override
    $Plugin = new BoardPlugin;
    $data = $Plugin->list_prep_data($data,$row);
    // End Parsing Override

    return $data;
  }


  function thread($stickies=false) // this stickies flag is crap, find a better way
  {
    global $Core;
    if(!isset($this->data))
    {
      print "No data to display specified.";
      return;
    }
    if(!$this->data) $this->data = array();

    $id = $this->name;
    if($stickies) $id = $this->name."_stickies";

    if(session('id')) $this->prep_favorites();

    $class = CSS_ODD;
    if(!$this->ajax) print "<div id=\"list_{$id}\" class=\"data\">\n";
    foreach($this->data as $row)
    {
      $field = $this->prep_data($row);
      $class = ($class==CSS_ODD?CSS_EVEN:CSS_ODD);
      $firstpost = "<a href=\"javascript:;\" onclick=\"firstpost('{$this->table}',{$field[LIST_ID]},this);return false;\">".ARROW_RIGHT."</a>&nbsp;";
      if(session('nofirstpost')) $firstpost = "";

      print "<div class=\"{$class}$field[me]\" id=\"{$this->table}_{$field[LIST_ID]}\">\n";
      print "<ul class=\"list$field[read]\" ondblclick=\"location.href='/{$this->table}/view/{$field[LIST_ID]}/&r={$field[LIST_POSTS]}'\">\n";
      print "  <li class=\"readbar\">&nbsp;</li>\n";
      print "  <li class=\"member\"><span>Thread By: </span>".$Core->member_link($field[LIST_CREATOR_NAME])."</li>\n";
      print "  <li class=\"subject\">\n";
      print "    <div class=\"extra\">\n";
      print "      $field[dot]&nbsp;$field[fav]{$firstpost}\n";
      print "    </div>\n";
      print "    <span>Subject: </span>\n";
      print "    <a href=\"/{$this->table}/view/{$field[LIST_ID]}/&p={$field[LIST_POSTS]}\">$field[subject]</a>\n";
      print "  </li>\n";
      print "  <li class=\"posts\"><span>Posts: </span>{$field[LIST_POSTS]}</li>\n";
      print "  <li class=\"lastpost\"><span>Last Post By:</span>".$Core->member_link($field[LIST_LAST_POSTER_NAME])." on $field[date]</li>\n";
      print "  <li class=\"readbar\" style=\"float:right\">&nbsp;</li>\n";
      print "</ul>\n";
      print "<div id=\"fp_{$field[LIST_ID]}\" class=\"firstpost\"></div>\n";
      print "</div>\n";
    }
    if(!$this->ajax)
    {
      $display = "none";
      if(!count($this->data)) $display = "block";
      if(!$stickies) print "<ul class=\"{$class}\" id=\"noresults\" style=\"display:$display\">".NO_RESULTS."</ul>\n";
      print "</div>\n";
    }
  }
  
  function message() { $this->thread(); }
}
