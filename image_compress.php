<?php
require_once 'google-api-php-client/vendor/autoload.php';

require 'analog/lib/Analog.php';

require_once 'vendor/autoload.php';

use Automattic\WooCommerce\Client;
use Analog\Handler\File;
use Enqueue\SimpleClient\SimpleClient;
use Interop\Queue\Message;
use Interop\Queue\Processor;

include __DIR__.'/vendor/autoload.php';

if(!(file_exists('/var/www/home/post/log/image_compress_'.(new DateTime())->format('Y-m-d').'.log'))){
  $log_file = fopen('/var/www/home/post/log/image_compress_'.(new DateTime())->format('Y-m-d').'.log', "w") or die("can't open file");
  fclose($log_file);
}
Analog::handler(File::init('./log/image_compress_'.(new DateTime())->format('Y-m-d').'.log'));
Analog::log('image_compress');

$path = 'files/';
foreach(glob($path.'*.jpg') as $file){
  $filesize = filesize($file);
  $filename = str_replace($path, '', $file);
  if(strpos($filename, 'merged') != false){
    if($filesize > 5000000){
      Analog::log($file.'|'.$filesize);
      $image = imagecreatefromjpeg($file);
      imagejpeg($image, $file, 90);
    }
  }else{
    if($filesize > 1000000){
      Analog::log($file.'|'.$filesize);
      $image = imagecreatefromjpeg($file);
      imagejpeg($image, $file, 90);
    }
  }
}
?>