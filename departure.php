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

if(!(file_exists('./log/departure_'.(new DateTime())->format('Y-m-d').'.log'))){
  $log_file = fopen('./log/departure_'.(new DateTime())->format('Y-m-d').'.log', "w") or die("can't open file");
  fclose($log_file);
}
Analog::handler(File::init('./log/departure_'.(new DateTime())->format('Y-m-d').'.log'));

$router = new AltoRouter();

$router->setBasePath('/departure');

$router->map( 'GET', '/', function() {
  require __DIR__ . '/departure.html';
});

$router->map( 'GET', '/list', function() {
  $mysqli = new mysqli('localhost', 'us3ot98vy5jtz', 'zkftfpjhs9qw', 'dbb3tqaycgagbq');
  
  if($mysqli->connect_errno){
    header('HTTP/1.1 500 Internal Server Error');
    die(json_encode(array('message' => 'Failed to connect to MySQL: ' . $mysqli->connect_error, 'code' => 500)));
  }

  if($result = $mysqli->query('UPDATE departures SET deleted = true WHERE cutoff_date < "' . date("Y-m-d H:i:S") . '"')){

  }else{
    header('HTTP/1.1 500 Internal Server Error');
    die(json_encode(array('message' => 'Error updating record: ' . $mysqli->error, 'code' => 500)));
  }

  if($result = $mysqli->query('SELECT * FROM departures WHERE deleted = false')){
    $myArray = [];
    while($row = $result->fetch_array(MYSQLI_ASSOC)) {
      $myArray[] = $row;
    }
    echo json_encode($myArray);
  }else{
    header('HTTP/1.1 500 Internal Server Error');
    die(json_encode(array('message' => 'Error getting record: ' . $mysqli->error, 'code' => 500)));
  }

  $result->free_result();
  $mysqli->close();
});

$router->map( 'POST', '/add', function() {
  if(isset($_POST['destination'])){
    $destination = $_POST['destination'];
  }else{
    header('HTTP/1.1 500 Internal Server Error');
    die(json_encode(array('message' => 'destination is not set', 'code' => 500)));
  }

  if(isset($_POST['departing_date'])){
    $departing_date = $_POST['departing_date'];
  }else{
    header('HTTP/1.1 500 Internal Server Error');
    die(json_encode(array('message' => 'departing_date is not set', 'code' => 500)));
  }

  if(isset($_POST['cutoff_date'])){
    $cutoff_date = $_POST['cutoff_date'];
  }else{
    header('HTTP/1.1 500 Internal Server Error');
    die(json_encode(array('message' => 'cutoff_date is not set', 'code' => 500)));
  }

  $mysqli = new mysqli('localhost', 'us3ot98vy5jtz', 'zkftfpjhs9qw', 'dbb3tqaycgagbq');
  
  if($mysqli->connect_errno){
    header('HTTP/1.1 500 Internal Server Error');
    die(json_encode(array('message' => 'Failed to connect to MySQL: ' . $mysqli->connect_error, 'code' => 500)));
  }

  if($result = $mysqli->query('INSERT INTO departures (destination,departing_date,cutoff_date) VALUE ("'.$destination.'","'.$departing_date.'","'.$cutoff_date.'")')){
    echo json_encode(array('message' => 'success'));
  }else{
    header('HTTP/1.1 500 Internal Server Error');
    die(json_encode(array('message' => 'Error updating record: ' . $mysqli->error, 'code' => 500)));
  }

  $mysqli->close();
});

$router->map( 'POST', '/delete', function() {
  if(isset($_POST['id'])){
    $id = $_POST['id'];
  }else{
    header('HTTP/1.1 500 Internal Server Error');
    die(json_encode(array('message' => 'id is not set', 'code' => 500)));
  }

  $mysqli = new mysqli('localhost', 'us3ot98vy5jtz', 'zkftfpjhs9qw', 'dbb3tqaycgagbq');
  
  if($mysqli->connect_errno){
    header('HTTP/1.1 500 Internal Server Error');
    die(json_encode(array('message' => 'Failed to connect to MySQL: ' . $mysqli->connect_error, 'code' => 500)));
  }

  if($result = $mysqli->query('UPDATE departures SET deleted = true WHERE id = ' . $id)){
    echo json_encode(array('message' => 'success'));
  }else{
    header('HTTP/1.1 500 Internal Server Error');
    die(json_encode(array('message' => 'Error updating record: ' . $mysqli->error, 'code' => 500)));
  }

  $result->free_result();
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

//CREATE TABLE `zeroaqua`.`departures` ( `id` BIGINT NOT NULL AUTO_INCREMENT , `destination` VARCHAR(50) NOT NULL , `daparting_date` DATE NOT NULL , `cutoff_date` DATETIME NOT NULL , `deleted` BOOLEAN NOT NULL DEFAULT FALSE , PRIMARY KEY (`id`));
?>