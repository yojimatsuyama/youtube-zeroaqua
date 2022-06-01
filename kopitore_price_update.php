<?php
require 'AltoRouter.php';

require 'cloudinary/Cloudinary.php';
require 'cloudinary/Uploader.php';
require 'cloudinary/Api.php';
require 'cloudinary/Error.php';

require_once 'google-api-php-client/vendor/autoload.php';

require 'analog/lib/Analog.php';

require_once 'vendor/autoload.php';

use Automattic\WooCommerce\Client;
use Analog\Handler\File;
use Enqueue\SimpleClient\SimpleClient;
use Interop\Queue\Message;
use Interop\Queue\Processor;

set_time_limit(500);

if(!(file_exists('/var/www/home/post/log/kopitore_price_update_'.(new DateTime())->format('Y-m-d').'.log'))){
  $log_file = fopen('/var/www/home/post/log/kopitore_price_update_'.(new DateTime())->format('Y-m-d').'.log', "w") or die("can't open file");
  fclose($log_file);
}
Analog::handler(File::init('/var/www/home/post/log/kopitore_price_update_'.(new DateTime())->format('Y-m-d').'.log'));

Analog::log('kopitore_price_update');

$woocommerce = new Client(
  'https://store.kopitore.com',
  'ck_d814420e7cff092848b5e7427aebe04c75882abc',
  'cs_d1c66a25c48b1bfaf97e084337a4e21dd6667ac7',
  [
    'version' => 'wc/v3',
    'verify_ssl' => false,
    'timeout' => 120
  ]
);

$woocommerce_ids = [
  'Gaia' => [123,269],
  'Kratos' => [121,268],
  'Moai' => [214,270]
];

$monthly_ids = [
  'Gaia' => [501],
  'Kratos' => [500],
  'Moai' => [502]
];

//login
$curl = curl_init('https://www.myfxbook.com/api/login.json?email=myfxbook-thb@kopitore.com&password=kQ6X3CDC');
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($curl);
curl_close($curl);
Analog::log('login_res.'.var_export($response, true));
$response_obj = json_decode($response);
if(!property_exists($response_obj, 'session')){//login failed
  return;
}

$session_id = $response_obj->session;

$curl = curl_init('https://www.myfxbook.com/api/get-my-accounts.json?session='.$session_id);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
$my_accounts_res = curl_exec($curl);
curl_close($curl);
Analog::log('get_my_accounts_res.'.var_export($my_accounts_res, true));
$my_accounts_obj = json_decode($my_accounts_res);
if(!property_exists($my_accounts_obj, 'accounts')){//failed
  return;
}

$accounts = $my_accounts_obj->accounts;


//logout
$curl = curl_init('https://www.myfxbook.com/api/logout.json?session='.$session_id);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($curl);
curl_close($curl);
Analog::log('logout_res.'.var_export($response, true));

foreach($accounts as $account){
  $mysqli = new mysqli('localhost', 'grape', 'f|yE4g|eSf|y', 'kopitore');
  
  if($mysqli->connect_errno){
    Analog::log('Failed to connect to MySQL: ' . $mysqli->connect_error);
  }else{
    //update deleted
    if($result = $mysqli->query('INSERT INTO myfxbook_accounts (name, profit) VALUES("'.$account->name.'", '.$account->profit.') ON DUPLICATE KEY UPDATE profit='.$account->profit)){
    }else{
      Analog::log('Error updating record: ' . $mysqli->error);
    }

    //$result->free_result();
    $mysqli->close();
  }

  

  if($account->profit > 2){
    $price = intval($account->profit / 2);
  }else{
    $price = 1;
  }
  $ids = $woocommerce_ids[$account->name];
  foreach($ids as $id){
    $data = [
      'regular_price' => (string)$price
    ];
    $res = $woocommerce->put('products/'.$id, $data);
    Analog::log('woocommerce_res.'.var_export($res, true));
  }

  $ids = $monthly_ids[$account->name];
  if($price > 10){
    $monthly_price = intval($price / 10);
  }else{
    $monthly_price = 1;
  }
  foreach($ids as $id){
    $data = [
      'regular_price' => (string)$monthly_price
    ];
    $res = $woocommerce->put('products/'.$id, $data);
    Analog::log('woocommerce_res.'.var_export($res, true));
  }
}
?>