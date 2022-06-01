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

if(!(file_exists('/var/www/home/post/log/batch_delete_video_'.(new DateTime())->format('Y-m-d').'.log'))){
  $log_file = fopen('/var/www/home/post/log/batch_delete_video_'.(new DateTime())->format('Y-m-d').'.log', "w") or die("can't open file");
  fclose($log_file);
}
Analog::handler(File::init('./log/batch_delete_video_'.(new DateTime())->format('Y-m-d').'.log'));
Analog::log('batch_delete_video');

$woocommerce = new Client(
  'https://zeroaqua.com',
  'ck_1991e463567a15c79c59ad9539880b27538e3d58',
  'cs_7ea706d48b56b33bd146edda1aa2ff2226cfac37',
  [
    'version' => 'wc/v3',
    'verify_ssl' => false,
    'timeout' => 120
  ]
);

$path = 'files/';
foreach(glob($path.'*.*') as $file){
  $filename = str_replace($path, '', $file);

  if(preg_match('/^([a-zA-Z0-9\s_\\.\-:])+(.mp4)$/', strtolower($filename))){
    Analog::log('file.'.var_export($filename, true));
    $sku = basename($filename, '.mp4');
    Analog::log('sku.'.var_export($sku, true));

    $data = [
      'sku' => $sku
    ];
    $woocommerce_products = $woocommerce->get('products', $data);
    Analog::log('woocommerce_products. count=' . count($woocommerce_products));
    if(count($woocommerce_products) > 0){
      foreach($woocommerce_products as $woocommerce_product){
        if($woocommerce_product->sku == $sku){
          Analog::log('woocommerce_product. sku=' . $woocommerce_product->sku . ' stock=' . $woocommerce_product->stock_quantity);
          if($woocommerce_product->stock_quantity == 0){
            //delete
            Analog::log('delete');
            unlink($file);
          }
        }
      }
    }else{
      Analog::log('product not found');
    }
  }
}
?>
