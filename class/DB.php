<?php
class DB
{
  public $db;        // database connection
  public $res;       // current query resultset
  public $sql;       // hold current query sql
  public $params;    // hold current query params
  public $limit;     // limit on query
  public $offset;    // offset on query
  public $debug;     // debugging on?
  public $showsql;   // show sql?

  // establish connection with database
  function __construct($connect,$debug=false,$showsql=false)
  {
    $this->debug = $debug;
    $this->showsql = $showsql;
    
    // check to see if pg_* functions are available
    if(!function_exists('pg_connect'))
    {
      if($this->debug) trigger_error("pg_* functions not found.\nPHP may not be compiled with pgsql support or its extension has been disabled in php.ini.");
      return false;
    }
    else
    {
      // connect to DB with appropriate verbosity
      if($this->debug) $this->db = pg_connect($connect);
      else
      $this->db = @pg_connect($connect);
      
      if(!$this->db)
      {
        if($this->debug) trigger_error("Possible malformed connection string, unable to connect to database.\n\n<strong>CONNECTION STRING:</strong>\n$connect");
        return false;
      }
      $this->query("set transform_null_equals to on");
    }
  }

  // close connection at end of script
  function __destruct() { /* if(isset($this->db)) pg_close($this->db);*/ }

  // free database resultset
  function free()
  {
    if($this->res)
    {
      if($this->debug) return pg_free_result($this->res);
      else
      return @pg_free_result($this->res);
    }
  }

  // escape string to prevent sql injection attack
  function escape($str)
  {
    if($this->debug > 1) return pg_escape_string($str);
    else
    return @pg_escape_string($str);
  }
  
  // define sql to run
  function set_query($sql,$params=array(),$offset=0,$limit=0)
  {
    // clear any prior resultset from memory
    $this->free();
    
    $this->sql = $sql;
    $this->params = $params;
    $this->limit = intval($limit);
    $this->offset = intval($offset);
    
    // add limit/offset if applicable
    if($this->limit > 0 || $this->offset > 0)
    {
      $this->sql .= "\nLIMIT $this->limit\n";
      $this->sql .= "\nOFFSET $this->offset\n";
    }
  }
  
  // return current sql to set to run
  function get_query()
  {
    $query = "<strong>QUERY</strong>:\n".htmlspecialchars($this->sql)."\n\n";
    $query .= "<strong>PARAMS</strong>:\n";
    $i = 1;
    foreach($this->params as $val)
    {
      $query .= "$$i = $val\n";
      $i++;
    }
    return $query;
  }
  
  // execute currently defined sql
  function run_query()
  {
    // if no sql defined die gracefully
    if(!$this->sql)
    {
      if($this->debug) trigger_error("No query to run.");
      return false;
    }
    else
    {
      if($this->showsql) print "<pre>$this->sql</pre>\n";
      // run query with appropriate verbosity
      if($this->debug) $this->res = pg_query_params($this->db,$this->sql,$this->params);
      else
      $this->res = @pg_query_params($this->db,$this->sql,$this->params);
    }

    // if query failed die gracefully
    if(!$this->res)
    {
      if($this->debug) trigger_error("<strong>FAILED</strong> ".$this->get_query()."\n".pg_last_error());
      return false;
    }
    else
    return $this->res;
  }
  
  // define sql and execute in single call
  function query($sql,$params=array(),$offset=0,$limit=0)
  {
    $this->set_query($sql,$params,$offset,$limit);
    return $this->run_query();
  }
  
  // commit,begin,rollback
  function begin() { return $this->query("BEGIN"); }
  function commit() { return $this->query("COMMIT"); }
  function rollback() { return $this->query("ROLLBACK"); }

  // load the first result from the first row of executed sql
  function load_result()
  {
    $res = false;
    
    // if no sql defined die gracefully
    if(!$this->res)
    {
      if($this->debug) trigger_error("No query to load result from.");
      return false;
    }
    else
    {
      if($this->debug) $res = pg_fetch_result($this->res,0);
      else
      $res = @pg_fetch_result($this->res,0);
    }
    return $res;
  }

  // load next tuple as a numeric array
  function load_row()
  {
    // if no sql defined die gracefully
    if(!$this->res)
    {
      if($this->debug) trigger_error("No query to load result from.");
      return false;
    }
    else
    {
      if($this->debug) return pg_fetch_row($this->res);
      else
      return @pg_fetch_row($this->res);
    }
  }
  
  // load next tuple as an associative asrray
  function load_array()
  {
    // if no sql defined die gracefully
    if(!$this->res)
    {
      if($this->debug) trigger_error("No query to load result from.");
      return false;
    }
    else
    {
      if($this->debug) return pg_fetch_array($this->res,NULL,PGSQL_ASSOC);
      else
      return @pg_fetch_array($this->res,NULL,PGSQL_ASSOC);
    }
  }
  
  // load next tuple as an object
  function load_object()
  {
    // if no sql defined die gracefully
    if(!$this->res)
    {
      if($this->debug) trigger_error("No query to load result from.");
      return false;
    }
    else
    {
      if($this->debug > 1) return pg_fetch_object($this->res);
      else
      return @pg_fetch_object($this->res);
    }
  }
  
  // load the entire resultset, confine to one key if defined
  function load_all($key=false)
  {
    $data = array();

    // if no sql defined die gracefully
    if(!$this->res)
    {
      if($this->debug) trigger_error("No query to load result from.");
      return false;
    }
    else
    {
      if($this->debug) $rows = pg_fetch_all($this->res);
      else
      $rows = @pg_fetch_all($this->res);
      
      if($key && $rows)
      {
        foreach($rows as $arr) array_push($data,$arr[$key]);
        return $data;
      }
      else
      return $rows;
    }
  }

  // load the entire resultset using defined column as the key
  function load_all_key($key="id",$value="name")
  {
    $data = array();
    // if no sql defined die gracefully
    if(!$this->res)
    {
      if($this->debug) trigger_error("No query to load result from.");
      return false;
    }
    else
    {
      if($this->debug) $rows = pg_fetch_all($this->res);
      else
      $rows = @pg_fetch_all($this->res);
      if(is_array($rows)) foreach($rows as $arr) $data[$arr[$key]] = $arr[$value];
      return $data;
    }
  }

  // output results in XML format -- INCOMPLETE
  function get_xml_results()
  {
    // if no sql defined die gracefully
    if(!$this->res)
    {
      if($this->debug) trigger_error("No query to load results from.");
      return false;
    }
    else
    {
      while($ob = $this->load_object())
      {
        foreach($ob as $key => $value)
        {
          print $value;
        }
      }
    }
  }

  // automated insert function
  function insert($table,$data=false,$keys=false)
  {
    $i = 1;
    $params = array();
    $titles = $values = "";

    if(!$data) $data = $_POST;
    if(!$keys) $keys = array_keys($data);
    foreach($keys as $key)
    {
      if(substr($key,0,1) == "_") continue;

      $titles .= "$key,";
      $val = isset($data[$key]) ? $data[$key] : "";

      if($val === false) $values .= "false,";
      else
      if($val === true) $values .= "true,";
      else
      if($val == "") $values .= "NULL,";
      else
      {
        $values .= "$$i,";
        $params[] = $val;
        $i++;
      }
    }
    $titles = substr($titles,0,-1);
    $values = substr($values,0,-1);
    return $this->query("INSERT INTO\n  $table\n    ($titles)\n  VALUES\n    ($values)",$params);
  }

  // automated update function
  function update($table,$cond,$condid,$data=false,$keys=false)
  {
    $i = 1;
    $params = array();
    $update = "";

    if(!$data) $data = $_POST;
    if(!$keys) $keys = array_keys($data);
    foreach($keys as $key)
    {
      if(substr($key,0,1) == "_") continue;
      $val = isset($data[$key]) ? $data[$key] : "";
    
      if($val == "") $update .= "  $key = NULL,\n";
      else
      {
        $update .= "  $key = $$i,\n";
        $params[] = $val;
        $i++;
      }
    }
    $update = substr($update,0,-2);
    return $this->query("UPDATE\n  $table\nSET\n$update\nWHERE\n  $cond = '".$this->escape($condid)."'",$params);
  }

  // automated delete function
  function delete($table,$cond,$condid)
  {
    return $this->query("DELETE FROM\n  $table\nWHERE\n  $cond = '".$this->escape($condid)."'");
  }

  // simple true false database check (does this row exist etc)
  function check($query,$params=array())
  {
    $check = $this->query($query,$params);
    return $this->load_result() ? true : false;    
  }
  
  function value($query,$params=array())
  {
    $check = $this->query($query,$params);
    return $this->load_result();
  }
  
  // get the last id from a defined sequence
  function get_last_id($seq)
  {
    $seq = $this->escape($seq);
    if(!$this->query("SELECT currval('$seq')"))
    {
      if($this->debug) trigger_error("Invalid query requesting currval of sequence.");
      return false;
    }
    else
    return $this->load_result();  
  }

  // output 'explain analyze' of currently defined sql
  function analyze_query()
  {
    if(!$this->sql)
    {
      if($this->debug) trigger_error("No query to analyze.");
      return false;
    }
    else
    {
      $temp = $this->sql;
      $this->sql = "EXPLAIN ANALYZE\n$this->sql";
      $this->run_query();
      
      print "<pre>\n";
      print $this->get_query()."\n";
      print "<strong>QUERY PLAN</strong>:\n";
      while($row = $this->load_row()) print $row[0]."\n";
      print "</pre>\n";

      $this->sql = $temp;
      $this->free();
    }
  }
  
  // get fieldname for defined column
  function get_field_name($num)
  {
    if($this->debug) return pg_field_name($this->res,intval($num));
    else
    return @pg_field_name($this->res,intval($num));
  }
  
  // get field type for defined column
  function get_field_type($num)
  {
    if($this->debug) return pg_field_type($this->res,intval($num));
    else
    return @pg_field_type($this->res,intval($num));
  }
  
  // get number of fields in resultset
  function get_num_fields()
  {
    if($this->debug) return pg_num_fields($this->res);
    else
    return @pg_num_fields($this->res);
  }
  
  // get number of rows in resultset
  function get_num_rows()
  {
    if($this->debug) return pg_num_rows($this->res);
    else
    return @pg_num_rows($this->res);
  }
}

?>
