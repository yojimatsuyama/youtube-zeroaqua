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

if(!(file_exists('/var/www/home/post/log/delete_images_'.(new DateTime())->format('Y-m-d').'.log'))){
  $log_file = fopen('/var/www/home/post/log/delete_images_'.(new DateTime())->format('Y-m-d').'.log', "w") or die("can't open file");
  fclose($log_file);
}
Analog::handler(File::init('./log/delete_images_'.(new DateTime())->format('Y-m-d').'.log'));
Analog::log('delete_images');

$path = 'files/';
foreach(glob($path.'*.jpg') as $file){
  $filename = str_replace($path, '', $file);

  if(strpos($filename, 'merged') == false && strpos($filename, 'thumbnail') == false && strpos($filename, '-1.jpg') == false){
    Analog::log('filename.'.var_export($filename, true));
    unlink($file);
  }
}
?>