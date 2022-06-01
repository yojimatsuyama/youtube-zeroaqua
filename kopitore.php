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

if(!(file_exists('./log/kopitore_'.(new DateTime())->format('Y-m-d').'.log'))){
  $log_file = fopen('./log/kopitore_'.(new DateTime())->format('Y-m-d').'.log', "w") or die("can't open file");
  fclose($log_file);
}
Analog::handler(File::init('./log/kopitore_'.(new DateTime())->format('Y-m-d').'.log'));

$router = new AltoRouter();

$router->setBasePath('/kopitore');

$router->map( 'GET', '/myfxbook/my_accounts', function() {
  Analog::log('GET /myfxbook/my_accounts');
  
  //login
  /*$curl = curl_init('https://www.myfxbook.com/api/login.json?email=myfxbook-thb@kopitore.com&password=kQ6X3CDC');
  curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
  $response = curl_exec($curl);
  curl_close($curl);
  Analog::log('login_res.'.var_export($response, true));
  $response_obj = json_decode($response);
  if(!property_exists($response_obj, 'session')){//login failed
    echo [];
  }

  $session_id = $response_obj->session;

  $curl = curl_init('https://www.myfxbook.com/api/get-my-accounts.json?session='.$session_id);
  curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
  $my_accounts = curl_exec($curl);
  curl_close($curl);
  Analog::log('get_my_accounts_res.'.var_export($my_accounts, true));


  //logout
  $curl = curl_init('https://www.myfxbook.com/api/logout.json?session='.$session_id);
  curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
  $response = curl_exec($curl);
  curl_close($curl);
  Analog::log('logout_res.'.var_export($response, true));

  header("Access-Control-Allow-Origin: *");
  echo $my_accounts;*/

  $mysqli = new mysqli('localhost', 'grape', 'f|yE4g|eSf|y', 'kopitore');
  
  if($mysqli->connect_errno){
    header('HTTP/1.1 500 Internal Server Error');
    die(json_encode(array('message' => 'Failed to connect to MySQL: ' . $mysqli->connect_error, 'code' => 500)));
  }

  //update deleted
  if($result = $mysqli->query('SELECT * FROM myfxbook_accounts')){
    $myArray = [];
    while($row = $result->fetch_array(MYSQLI_ASSOC)) {
      $myArray[] = $row;
    }
    header("Access-Control-Allow-Origin: *");
    echo json_encode(array('accounts' => $myArray));
  }else{
    header('HTTP/1.1 500 Internal Server Error');
    die(json_encode(array('message' => 'Error updating record: ' . $mysqli->error, 'code' => 500)));
  }

  //$result->free_result();
  $mysqli->close();
});

// match current request url
$match = $router->match();

// call closure or throw 404 status
if( is_array($match) && is_callable( $match['target'] ) ) {
  call_user_func_array( $match['target'], $match['params'] ); 
} else {
  // no route was matched
  header( $_SERVER["SERVER_PROTOCOL"] . ' 404 Not Found');
}
//CREATE TABLE `kopitore`.`myfxbook_accounts` ( `id` BIGINT NOT NULL AUTO_INCREMENT , `name` VARCHAR(40) NOT NULL , `profit` DECIMAL(12,2), PRIMARY KEY (`id`));
?>