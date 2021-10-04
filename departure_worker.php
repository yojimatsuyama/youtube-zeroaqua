<?php
require 'analog/lib/Analog.php';

use Analog\Handler\File;

if(!(file_exists('./log/departure_worker_'.(new DateTime())->format('Y-m-d').'.log'))){
  $log_file = fopen('./log/departure_worker_'.(new DateTime())->format('Y-m-d').'.log', "w") or die("can't open file");
  fclose($log_file);
}
Analog::handler(File::init('./log/departure_worker_'.(new DateTime())->format('Y-m-d').'.log'));

Analog::log('departure_worker');

$destinations = ['United States', 'United Kingdom', 'European Union', 'Japan', 'Singapore'];

$mysqli = new mysqli('localhost', 'us3ot98vy5jtz', 'zkftfpjhs9qw', 'dbb3tqaycgagbq');

if($mysqli->connect_errno){
  Analog::log('Failed to connect to MySQL: ' . $mysqli->connect_error);
  die(json_encode(array('message' => 'Failed to connect to MySQL: ' . $mysqli->connect_error, 'code' => 500)));
}

if($result = $mysqli->query('UPDATE departures SET deleted = true WHERE cutoff_date < "' . date("Y-m-d H:i:S") . '"')){

}else{
  Analog::log('Error updating record: ' . $mysqli->error);
  die(json_encode(array('message' => 'Error updating record: ' . $mysqli->error, 'code' => 500)));
}

$mail_str = 'Departure no next schedule for:';

foreach ($destinations as $destination) {
  if($result = $mysqli->query('SELECT * FROM departures WHERE cutoff_date > "'.date("Y-m-d H:i:s").'" AND deleted = false AND destination = "'.$destination.'"')){
    Analog::log($destination.'.'.$result->num_rows);
    if($result->num_rows < 1){
      $mail_str = $mail_str . '\r\n' . $destination;
    }
    $result->free_result();
  }else{
    Analog::log('Error getting record: ' . $mysqli->error);
    die(json_encode(array('message' => 'Error getting record: ' . $mysqli->error, 'code' => 500)));
  }
}

Analog::log('mail_str.'.$mail_str);
if($mail_str != 'Departure no next schedule for:'){
  Analog::log('sending_mail.');
  mail('nattapol.phakhakit@hotmail.com', "Departure destination notify", $mail_str, 'From: nattapol.phakhakit@hotmail.com');
}
?>