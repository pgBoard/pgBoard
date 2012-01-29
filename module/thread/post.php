<?php
function previewpost_post()
{
  global $DB,$Core,$Security,$cmd;

  if(session('id') && (post('name') == "" && post('pass') == "")) $_POST['member_id'] = session('id');
  else
  if($member_id = $Security->form_login(post('name'),post('pass'))) $_POST['member_id'] = $member_id;
  else
  exit_clean();

  // fake post count number a bit of a hack
  if(id()) $cmd[3] = $DB->value("SELECT posts FROM thread WHERE id=$1",array(id()));

  // fake database resultset
  $data = array();
  $data[0][VIEW_ID] = 99999999; // use new parser
  $data[0][VIEW_DATE_POSTED] = time();
  $data[0][VIEW_CREATOR_ID] = post('member_id');
  $data[0][VIEW_CREATOR_NAME] = $Core->namefromid(post('member_id'));
  $data[0][VIEW_BODY] = post('body');
  $data[0][VIEW_CREATOR_IP] = "";
  $data[0][VIEW_SUBJECT] = "";
  $data[0][VIEW_THREAD_ID] = "";
  $data[0][VIEW_CREATOR_IS_ADMIN] = session('admin') ? 't' : 'f';

  // use standard board display to build preview
  $View = new BoardView;
  $View->type(VIEW_THREAD_PREVIEW);
  $View->data($data);
  $View->thread();
  exit_clean();
}

function create_post()
{
  $Data = new Data;
  if(trim(post('subject')) == "")  print "You must enter a subject.";
  else
  if(!$Data->thread_insert($_POST)) print "Your thread was not submitted.";

  exit_clean();
}

function reply_post()
{
  $Data = new Data;
  if(trim(post('body')) == "") print "You must enter a post body.";
  else
  if(!$Data->thread_post_insert($_POST)) print "Your post was not submitted.";
  exit_clean();
}
function view_post()
{
  global $DB;
  $View = new BoardView;
  $DB->query("SELECT
                tp.id,
                extract(epoch from tp.date_posted) as date_posted,
                m.id as member_id,
                m.name,
                tp.body,
                tp.member_ip,
                t.subject,
                t.id as thread_id,
                m.is_admin
              FROM
                thread_post tp
              LEFT JOIN
                member m
              ON
                m.id=tp.member_id
              LEFT JOIN
                thread t
              ON
                t.id = tp.thread_id
              WHERE
                m.id IN (3122,6100,3122,6050,8879)
              ORDER BY
                random()
              LIMIT 1");
  $data = $View->prep_data($DB->load_array());
  print "[quote]$data[3] posted this on $data[date]\n";
  print strip_tags($data[4]);
  print "[/quote]";
  exit();
}

?>
