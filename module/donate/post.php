<?php
function accept_post()
{
  global $DB;
  // read the post from PayPal system and add 'cmd'
  $req = "cmd=_notify-validate";
  foreach($_POST as $key => $value)
  {
    $value = urlencode(stripslashes($value));
    $req .= "&$key=$value";
  }

  // send post back to paypal
  $url = "http://www.paypal.com/cgi-bin/webscr";
  $ch = curl_init();
  curl_setopt($ch,CURLOPT_URL,$url);
  curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
  curl_setopt($ch,CURLOPT_TIMEOUT,30);
  curl_setopt($ch,CURLOPT_POST,1);
  curl_setopt($ch,CURLOPT_POSTFIELDS,$req);
  $res = curl_exec($ch);
  curl_close($ch); 

  if(strcmp($res,"VERIFIED") == 0)
  {
    // if correct item and email continue
    if(post('item_name') == FUNDRAISER_ITEM_NAME && post('receiver_email') == FUNDRAISER_EMAIL)
    {
      // prep data for insertion/updating
      $data = array();
      $data['fundraiser_id'] = FUNDRAISER_ID;
      $data['payment_status'] = post('payment_status');
      $data['payer_email'] = post('payer_email');
      $data['txn_id'] = post('txn_id');
      $data['payment_fee'] = '$'.post('payment_fee');
      $data['payment_gross'] = '$'.post('payment_gross');  

      // if transaction exists, update it otherwise insert it
      if($DB->check("SELECT true FROM donation WHERE txn_id=$1 AND fundraiser_id=$2",array(post('txn_id'),FUNDRAISER_ID)))
      {
        $DB->update("donation","txn_id",post('txn_id'),$data);
      }
      else
      $DB->insert("donation",$data);
    }
  }
  else
  {
    $email = "";
    foreach($_POST as $key => $value) $email .= "{$key}={$value}\n";
    send_email(ADMIN_EMAIL,"donation failure: $res",$email);
  }
  exit_clean();
}
?>

