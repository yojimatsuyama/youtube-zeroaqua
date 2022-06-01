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

$log_path = './log/aquashop_posted_';//testing
//$log_path = '/var/www/home/post/log/aquashop_posted_';

if(!(file_exists($log_path.(new DateTime())->format('Y-m-d').'.log'))){
  $log_file = fopen($log_path.(new DateTime())->format('Y-m-d').'.log', "w") or die("can't open file");
  fclose($log_file);
}
Analog::handler(File::init($log_path.(new DateTime())->format('Y-m-d').'.log'));
Analog::log('aquashop_posted');

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

posted($woocommerce);

function posted($woocommerce){
  $page_number = 1;
  $max_page = 40;
  $item_found = false;

  $p = 0;
  $n = 0;
  for($page_number; $page_number <= $max_page; $page_number++){
    $data = [
      'page' => $page_number
    ];
    //$data = ['sku' => '0008'];//test
    $woocommerce_products = $woocommerce->get('products', $data);
    foreach($woocommerce_products as $woocommerce_product){
      /*Analog::log('woocommerce_product.'.var_export($woocommerce_product, true));
      $data = [
        'meta_data' => [[
          'key' => 'post_to_aquashop',
          'value' => 'posted'
        ]]
      ];
      $res = $woocommerce->put('products/'.$woocommerce_product->id, $data);
      Analog::log('res.'.var_export($res, true));*/

      $meta_datas = $woocommerce_product->meta_data;
      $post_to_aquashop = '';
      foreach($meta_datas as $meta_data){
        if($meta_data->key == 'post_to_aquashop'){
          $post_to_aquashop = $meta_data->value;
        }
      }
      
      if($post_to_aquashop == ''){
        $n = $n + 1;
      }else{
        $p = $p + 1;
      }
    }
  }
  Analog::log('p.'.var_export($p, true));
  Analog::log('n.'.var_export($n, true));
}
?>