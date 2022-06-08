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

$log_path = '/var/www/home/post/log/aquashop_description_update_';
$log_path = './log/aquashop_description_update_';//test

if(!(file_exists($log_path.(new DateTime())->format('Y-m-d').'.log'))){
  $log_file = fopen($log_path.(new DateTime())->format('Y-m-d').'.log', "w") or die("can't open file");
  fclose($log_file);
}
Analog::handler(File::init($log_path.(new DateTime())->format('Y-m-d').'.log'));
Analog::log('aquashop_description_update');

$aquashop = new Client(
  'https://xn--xckya1d0c8233adqyab74d.com',
  'ck_d7eb29878587f3b2e6343e65794b8f08b6e8b7b3',
  'cs_44e3c9e9a595fd848ffe73457b7fa0a381352bb7',
  [
    'version' => 'wc/v3',
    'verify_ssl' => false,
    'timeout' => 120
  ]
);

$date = DateTime::createFromFormat('Y-m-d', SHIPPING_DATE);

update_description($aquashop, $date);

function update_description($aquashop, $date){
  $page_number = 1;
  $max_page = 40;
  //$max_page = 1;//test
  $item_found = false;

  $counter = 0;
  for($page_number; $page_number <= $max_page; $page_number++){
    $data = [
      'page' => $page_number
    ];
    //$data = ['sku' => '2C57.'];//test
    $products = $aquashop->get('products', $data);
    foreach($products as $product){
      $counter++;
      //Analog::log('product.'.var_export($product, true));

      $description = $product->description;

      $prev_date = DateTime::createFromFormat('Y-m-d', $date->format('Y-m-d'));
      $prev_date->modify('-1 day');
      $next_date = DateTime::createFromFormat('Y-m-d', $date->format('Y-m-d'));
      $next_date->modify('+1 day');
      $dow = ['日', '月', '火', '水', '木', '金', '土'];

      $old_prev_date = DateTime::createFromFormat('Y-m-d', $date->format('Y-m-d'));
      $old_prev_date->modify('-8 day');
      $old_date = DateTime::createFromFormat('Y-m-d', $date->format('Y-m-d'));
      $old_date->modify('-7 day');
      $old_next_date = DateTime::createFromFormat('Y-m-d', $date->format('Y-m-d'));
      $old_next_date->modify('-6 day');
      $dow = ['日', '月', '火', '水', '木', '金', '土'];

      $old_str = '<div>次回: '.$old_date->format('n').'月'.$old_date->format('j').'日('.$dow[$old_date->format('w')].') 千葉県より発送、<br />'.$old_prev_date->format('j').'日('.$dow[$old_prev_date->format('w')].')正午までにお支払いください。<br />(*最短'.$dow[$old_next_date->format('w')].'曜日午前着)</div>';
      $new_str = '<div>次回: '.$date->format('n').'月'.$date->format('j').'日('.$dow[$date->format('w')].') 千葉県より発送、<br />'.$prev_date->format('j').'日('.$dow[$prev_date->format('w')].')正午までにお支払いください。<br />(*最短'.$dow[$next_date->format('w')].'曜日午前着)</div>';
      $description = str_replace($old_str, $new_str, $description);
      //Analog::log('old_str.'.var_export($old_str, true));
      //Analog::log('new_str.'.var_export($new_str, true));

      $update_data = [
        'description' => $description
      ];
      Analog::log('update_data.'.var_export($update_data, true));
      $res = $aquashop->put('products/'.$product->id, $update_data);
      Analog::log('woocommerce_update_res.'.var_export($res, true));
    }
  }
  Analog::log($counter);
}
?>