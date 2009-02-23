<?php
class BoardView extends Base
{
  public $data;
  public $collapse = false;
  function data($data) { $this->data = $data; }
  function collapse($collapse) { $this->collapse = $collapse; }

  function increment_views()
  {
    global $DB;
    if(!$this->ajax && $this->type == VIEW_THREAD || $this->type == VIEW_MESSAGE)
    {
      $DB->query("UPDATE {$this->table} SET views=views+1 WHERE id=$1",array(id(true)));
    }
  }

  function prep_data($row)
  {
    global $Parse;
    
    $data = array_values($row);
    $data['date'] = date(VIEW_DATE_FORMAT,$data[VIEW_DATE_POSTED]);

    $data['me'] = $data['quote'] = $data['admin'] = "";
    if(session('id'))
    {
      if($data[VIEW_CREATOR_ID] == session('id')) $data['me'] = SPACE.CSS_ME;
    }

    $data['body'] = $Parse->run($data[VIEW_BODY]);

    switch($this->type)
    {
      case VIEW_THREAD_SEARCH:
      case VIEW_THREAD_HISTORY:
        $data['body'] = "<strong>thread:</strong> <a href=\"/thread/view/{$data[VIEW_THREAD_ID]}/\">".htmlentities($data[VIEW_SUBJECT],ENT_QUOTES,'UTF-8')."</a><br/><br/>\n".$data['body'];
        break;

      case VIEW_THREAD:
      case VIEW_MESSAGE:
        $data['quote'] = NON_BREAKING_SPACE.ARROW_RIGHT." <a href=\"javascript:;\" onclick=\"quote_post({$data[VIEW_ID]})\"\">quote</a>";
    }

    if(session('admin')) $data['admin'] = NON_BREAKING_SPACE.ARROW_RIGHT." <a href=\"/admin/editpost/{$data[VIEW_ID]}/\">edit</a>";

    // Start Parsing Override
    $Plugin = new BoardPlugin;
    $data = $Plugin->view_prep_data($data,$row);
    // End Parsing Override

    return $data;
  }

  function subject($id)
  {
    global $DB;
    return $DB->value("SELECT
                         subject || ' <span class=''smaller''>(' || views || ' views)</span>'
                       FROM
                         {$this->table}
                       WHERE
                         id=$1",array($id));
  }

  function thread()
  {
    global $DB,$Core;

    if(!isset($this->data))
    {
      print "No data to display specified.";
      return;
    }
    if(!$this->data) $this->data = array();

    if(session('id') && ($this->type == VIEW_THREAD || $this->type == VIEW_MESSAGE) && !$this->ajax)
    {
      if($list = array_keys($Core->list_ignored(session('id')))) $list = implode(",",$list);
      else
      $list = "0";
      
      // minimum number of posts before collapse
      $mincollapse = is_numeric(session('mincollapse')) ? session('mincollapse') : COLLAPSE_DEFAULT;

      // number of posts to leave open after collapse
      $collapseopen = is_numeric(session('collapseopen')) ? session('collapseopen') : COLLAPSE_OPEN_DEFAULT;
      if($collapseopen < 1) $collapseopen = 1;

      // offset collapsing by the number of people ignored
      $offsetignores = $DB->value("SELECT count(*) FROM {$this->table}_post WHERE {$this->table}_id=$1 AND member_id IN ($list)",array(id()));
      
      $this->collapse($DB->value("SELECT COALESCE(last_view_posts,0)-$offsetignores-$collapseopen FROM {$this->table}_member WHERE {$this->table}_id=$1 AND member_id=$2",array(id(),session('id'))));

      // don't collapse if there aren't new posts or if we are offsetting/limiting
      if(cmd(3,true) ||
         cmd(4,true) ||
         session('nocollapse') ||
         count($this->data) < $mincollapse ||
         $this->collapse <= 0) $this->collapse(false);

    }

    $i=1;
    if(cmd(3,true)) $i = cmd(3,true)+1;

    if(!$this->ajax)
    {
      $hidemedia = get('media') ? "true" : "false";

      print "<div id=\"view_".id()."\">\n";
      if($this->collapse && $this->type != VIEW_THREAD_PREVIEW && $this->type != VIEW_MESSAGE_PREVIEW)
      {
        print "<div class=\"post clear\" id=\"uncollapse\">\n";
        print "  <ul class=\"postbody odd collapse\">\n";
        print "    <a href=\"javascript:;\" onclick=\"uncollapse('{$this->table}',$this->collapse,$hidemedia,this);\">show read (".($this->collapse)." posts)</a>\n";
        print "  </ul>\n";
        print "</div>\n";
        $this->data(array_slice($this->data,$this->collapse));
        $i = $this->collapse+1;
      }
    }

    foreach($this->data as $row)
    {
      $field = $this->prep_data($row);
      $count = "#{$i}";
      if(session('nopostnumber')) $count = "";
      
      print "<div id=\"view_".id()."_{$field[VIEW_ID]}_{$i}\" class=\"post\">\n";
      print "<ul class=\"view\" id=\"post_{$field[VIEW_ID]}\">\n";
      print "  <li class=\"info even$field[me]\">\n";
      print "    <div class=\"postinfo\">".$Core->member_link($field[VIEW_CREATOR_NAME])." posted this on $field[date]</div>\n";
      print "    <div class=\"controls\">$field[quote]$field[admin]</div>\n";
      print "    <div class=\"count\"><a href=\"#{$i}\" name=\"$i\">{$count}</a></div>\n";
      print "    <div class=\"clear\"></div>\n";
      print "  </li>\n";
      print "  <li class=\"postbody odd\">\n";
      print $field['body'];
      print "  </li>\n";
      print "</ul>\n";
      print "</div>\n";
      $i++;
    }
    if(!$this->ajax)
    {
      if(!count($this->data))
      {
        print "<div class=\"post\"><ul class=\"view\"><li class=\"odd\" id=\"noresults\">".NO_RESULTS."</li></ul></div>\n";
      }
      print "</div>\n";
    }
  }

  function message() { $this->thread(); }
  
  function member_update()
  {
    global $DB;
    if(!session('id')) return;

    if($DB->check("SELECT member_id FROM {$this->table}_member WHERE {$this->table}_id=$1 AND member_id=$2",array(id(),session('id'))))
    {
/*
      if($this->type == VIEW_MESSAGE)
      {
        print "number of posts on last view (cached): ".$DB->value("SELECT last_view_posts FROM {$this->table}_member WHERE message_id=$1",array(id()))."<br/>\n";
        print "total number of posts total (cached): ".$DB->value("SELECT posts FROM {$this->table} WHERE id=$1",array(id()))."<br/>\n";
        print "total number of posts with actual count: ".$DB->value("SELECT count(*) FROM {$this->table}_post WHERE message_id=$1",array(id()));
      }
*/
      $DB->query("UPDATE
                    {$this->table}_member
                  SET
                    last_view_posts=(SELECT posts FROM {$this->table} WHERE id=".id().")
                  WHERE
                    {$this->table}_id=$1
                  AND
                    member_id=$2",array(id(),session('id')));

    }
    else
    if($this->type != VIEW_MESSAGE)
    {
      $DB->query("INSERT INTO
                    {$this->table}_member ({$this->table}_id,member_id,last_view_posts)
                  VALUES
                    ($1,$2,(SELECT posts FROM {$this->table} WHERE id=$1))",array(id(),session('id')));
    }
  }
}
