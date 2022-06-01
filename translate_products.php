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

$log_path = '/var/www/home/post/log/translate_products_';//testing
$root_path = '/var/www/home/post/';

if(!(file_exists($log_path.(new DateTime())->format('Y-m-d').'.log'))){
  $log_file = fopen($log_path.(new DateTime())->format('Y-m-d').'.log', "w") or die("can't open file");
  fclose($log_file);
}
Analog::handler(File::init($log_path.(new DateTime())->format('Y-m-d').'.log'));
Analog::log('translate_products');

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

$woocommerce_jp = new Client(
  'https://xn--xckya1d0c8233adqyab74d.com',
  'ck_d7eb29878587f3b2e6343e65794b8f08b6e8b7b3',
  'cs_44e3c9e9a595fd848ffe73457b7fa0a381352bb7',
  [
    'version' => 'wc/v3',
    'verify_ssl' => false,
    'timeout' => 120
  ]
);

//$mysqli = new mysqli('localhost', 'zeroaqua-post', '%)?t`x6>5L4Vk5sJ', 'zeroaqua');
/*$mysqli = new mysqli('156.67.219.175', 'grape', 'f|yE4g|eSf|y', 'zeroaqua');
  
if($mysqli->connect_errno){
  die(json_encode(array('message' => 'Failed to connect to MySQL: ' . $mysqli->connect_error, 'code' => 500)));
}

//expired entry
if($result = $mysqli->query('UPDATE departures SET deleted = true WHERE cutoff_date < "' . date("Y-m-d H:i:s", strtotime('+7 hours')) . '"')){

}else{
  die(json_encode(array('message' => 'Error updating record: ' . $mysqli->error, 'code' => 500)));
}

//query
if($result = $mysqli->query('SELECT * FROM departures WHERE destination = "Japan" AND deleted = false ORDER BY cutoff_date ASC LIMIT 1')){
  $myArray = [];
  while($row = $result->fetch_array(MYSQLI_ASSOC)) {
    $myArray[] = $row;
    $date = DateTime::createFromFormat('Y-m-d', $row['departing_date']);
  }
}else{
  die(json_encode(array('message' => 'Error getting record: ' . $mysqli->error, 'code' => 500)));
}

$result->free_result();
$mysqli->close();*/

$date = DateTime::createFromFormat('Y-m-d', '2022-06-03');

$updating = false;

/*if(date("w") == 4 &&
  (int)date("G") == 23 &&
  (int)date("i") >= 49
){
  $updating = true;
}*/
//$updating = true;//test

translate_products($woocommerce, $woocommerce_jp, $date, $updating, $root_path);

function translate_products($woocommerce, $woocommerce_jp, $date, $updating, $root_path){
  $page_number = 1;
  $max_page = 40;
  $item_found = false;

  $counter = 0;
  for($page_number; $page_number <= $max_page; $page_number++){
    $data = [
      'page' => $page_number
    ];
    //$data = ['sku' => '2C57.'];//test
    $woocommerce_products = $woocommerce->get('products', $data);
    foreach($woocommerce_products as $woocommerce_product){
      $counter++;
      //Analog::log('woocommerce_product.'.var_export($woocommerce_product, true));

      $meta_datas = $woocommerce_product->meta_data;
      $post_to_aquashop = '';
      foreach($meta_datas as $meta_data){
        if($meta_data->key == 'post_to_aquashop'){
          $post_to_aquashop = $meta_data->value;
        }
      }

      if($post_to_aquashop == ''){
        $search_data = [
          'sku' => $woocommerce_product->sku
        ];
        $woocommerce_jp_products = $woocommerce_jp->get('products', $search_data);
        //Analog::log('woocommerce_jp_products.'.var_export($woocommerce_jp_products, true));

        if(count($woocommerce_jp_products) != 0)continue;

        $meta_datas = $woocommerce_product->meta_data;
        $type = '';
        $nickname = '';
        $gender = '';
        $color = '';
        $regular_price = '';
        $sale_price = '';
        foreach($meta_datas as $meta_data){
          if($meta_data->key == 'type')$type = $meta_data->value;
          if($meta_data->key == 'nickname')$nickname = $meta_data->value;
          if($meta_data->key == 'gender')$gender = $meta_data->value;
          if($meta_data->key == 'color')$color = $meta_data->value;
          if($meta_data->key == 'yahoo_auction_buy_price')$regular_price = $meta_data->value;
          if($meta_data->key == 'yahoo_auction_start_price')$sale_price = $meta_data->value;
        }

        $categories = [];
        $tags = [];
        $meta_datas = [];

        //type
        $type_id = 0;
        switch($type){
          case 'Plakat':
            $type_id = 29;
            $type = 'プラカット';
            break;
          case 'Half Moon':
            $type_id = 25;
            $type = 'ハーフムーン';
            break;
          case 'Crowntail':
            $type_id = 26;
            $type = 'クラウンテール';
            break;
          case 'Dumbo':
            $type_id = 30;
            $type = 'ダンボ';
            break;
          case 'Wild Alien':
            $type_id = 35;
            $type = 'ワイルド';
            break;
          case 'Dumbo Half Moon':
            $type_id = 31;
            $type = 'ダンボハーフムーン';
            break;
          case 'Double Tail':
            $type_id = 41;
            $type = 'ダブルテール';
            break;
          case 'Isan':
            $type_id = 43;
            $type = 'イサーン';
            break;
          default:
            $type = '';
            break;
        }
        if($type_id > 0){
          array_push($categories, ['id' => (int)$type_id]);
        }
        array_push($meta_datas,[
          'key' => 'type',
          'value' => $type
        ]);

        //nickname
        $nickname_id = 0;
        switch($nickname){
          case 'Nemo':
            $nickname_id = 42;
            $nickname = 'ニモ';
            break;
          case 'Nemo Galaxy':
            $nickname_id = 37;
            $nickname = 'ギャラクシー';
            break;
          case 'Dragon':
            $nickname_id = 34;
            $nickname = 'ドラゴン';
            break;
          case 'Black Samurai':
            //removed
            break;
          case 'Avatar':
            $nickname_id = 32;
            $nickname = 'アバター';
            break;
          case 'Alien':
            $nickname_id = 28;
            $nickname = 'エイリアン';
            break;
          default:
            $nickname = '';
            break;
        }
        if($nickname_id > 0){
          array_push($categories, ['id' => (int)$nickname_id]);
        }
        array_push($meta_datas,[
          'key' => 'nickname',
          'value' => $nickname
        ]);

        //gender
        $gender_id = 0;
        $gender_sym = '';
        switch($gender){
          case 'Male':
            $gender = 'オス';
            $gender_sym = '♂';
            break;
          case 'Female':
            $gender = 'メス';
            $gender_sym = '♀';
            break;
          case 'Pair':
            $gender_id = 33;
            $gender = '繁殖ペア';
            $gender_sym = '⚤';
            break;
          default:
            $gender = '';
            break;
        }
        if($gender_id > 0){
          array_push($categories, ['id' => (int)$gender_id]);
        }
        array_push($meta_datas,[
          'key' => 'gender',
          'value' => $gender
        ]);

        //color
        if($color != ''){
          $color = str_replace('Black', 'ブラック', $color);
          $color = str_replace('White', 'ホワイト', $color);
          $color = str_replace('Silver', 'シルバー', $color);
          $color = str_replace('Gold', 'ゴールド', $color);
          $color = str_replace('Copper', 'カッパー', $color);
          $color = str_replace('Yellow', 'イエロー', $color);
          $color = str_replace('Orange', 'オレンジ', $color);
          $color = str_replace('Red', 'レッド', $color);
          $color = str_replace('Purple', 'パープル', $color);
          $color = str_replace('Blue', 'ブルー', $color);
          $color = str_replace('Green', 'グリーン', $color);
          $color = str_replace('Turquoise', 'ターコイズ', $color);
        }
        array_push($meta_datas,[
          'key' => 'color',
          'value' => $color
        ]);

        if($type != '')$type = ' '.$type;
        if($nickname != '')$nickname = ' '.$nickname;
        if($gender != '')$gender = ' '.$gender;
        if($color != '')$color = ' '.$color;
        if($color != '')$color = str_replace(',', ' ', $color);
        $name = '【動画】ベタ 熱帯魚 淡水魚 ペット 送料無料 死着保証 本場タイ産 ('.$woocommerce_product->sku.')'.$type.$nickname.$gender;
        $color_strings = explode(' ', $color);
        foreach($color_strings as $color_string){
          Analog::log('color_string.'.var_export($color_string, true));
          Analog::log('mb_strlen($name, "UTF-8") + mb_strlen($color_string, "UTF-8") + 1.'.var_export(mb_strlen($name, 'UTF-8') + mb_strlen($color_string, 'UTF-8') + 1, true));

          if(mb_strlen($name, 'UTF-8') + mb_strlen($color_string, 'UTF-8') + 1 <= 65 && $color_string != ''){
            $name = $name.' '.$color_string;
          }
        }
        //Analog::log('name.'.var_export($name, true));

        $images = [];
        $i = 0;
        foreach($woocommerce_product->images as $image){
          $i = $i + 1;
          $png = imagecreatefrompng('https://zeroaqua.com/asset/tmp/item-overlay-min.png');
          $jpeg = imagecreatefromjpeg($image->src);
          list($width, $height) = getimagesize($image->src);
          list($newwidth, $newheight) = getimagesize('https://zeroaqua.com/asset/tmp/item-overlay-min.png');
          $out = imagecreatetruecolor(600, 600);
          imagecopyresampled($out, $jpeg, 0, 0, 0, 0, 600, 600, $width, $height);
          imagecopyresampled($out, $png, 0, 0, 0, 0, 600, 600, $newwidth, $newheight);
          imagejpeg($out, $root_path.'files/'.$woocommerce_product->sku.'-jp-'.$i.'.jpg', 90);

          array_push($images, ['src' => 'https://cms.zeroaqua.com/post/files/'.$woocommerce_product->sku.'-jp-'.$i.'.jpg']);
        }

        $short_description = ['元気な', '綺麗な', '人気の', '一押しの'];

        $prev_date = DateTime::createFromFormat('Y-m-d', $date->format('Y-m-d'));
        $prev_date->modify('-1 day');
        $next_date = DateTime::createFromFormat('Y-m-d', $date->format('Y-m-d'));
        $next_date->modify('+1 day');
        $dow = ['日', '月', '火', '水', '木', '金', '土'];
        $youtube_embed_url = explode('"',$woocommerce_product->description)[3];
        $youtube_short_url = '';
        $description_youtube = '';

        if(str_contains($youtube_embed_url, 'http://www.youtube.com/embed/')){
          $png = imagecreatefrompng('https://zeroaqua.com/asset/tmp/overlay-min.png');
          $jpeg = imagecreatefromjpeg('https://img.youtube.com/vi/'.str_replace('http://www.youtube.com/embed/','',$youtube_embed_url).'/0.jpg');
          list($width, $height) = getimagesize('https://img.youtube.com/vi/'.str_replace('http://www.youtube.com/embed/','',$youtube_embed_url).'/0.jpg');
          list($newwidth, $newheight) = getimagesize('https://zeroaqua.com/asset/tmp/overlay-min.png');
          $out = imagecreatetruecolor($newwidth/2, $newheight/2);
          imagecopyresampled($out, $jpeg, 0, 0, 0, 0, $newwidth/2, $newheight/2, $width, $height);
          imagecopyresampled($out, $png, 0, 0, 0, 0, $newwidth/2, $newheight/2, $newwidth, $newheight);
          imagejpeg($out, $root_path.'files/'.$woocommerce_product->sku.'-youtube-thumbnail.jpg', 100);
          
          $youtube_short_url = 'https://youtu.be/'.str_replace('http://www.youtube.com/embed/','',$youtube_embed_url);
          $description_youtube = '      <tr>
          <td>
            <div>Youtube動画はこちら:</div>
            <a href="'.$youtube_short_url.'"><img class="content-outer-box" width="100%" src="https://cms.zeroaqua.com/post/files/'.$woocommerce_product->sku.'-youtube-thumbnail.jpg"></a>
            <div><a href="'.$youtube_short_url.'"><br>'.$youtube_short_url.'<br></a></div>
          </td>
        </tr>';
        }
        $description_top = '<div class="content-outer-box">

    <div align="center">

      <table border="0" cellspacing="0" cellpadding="0">
        <tr>
          <td>
            <div>次回: '.$date->format('n').'月'.$date->format('j').'日('.$dow[$date->format('w')].') 千葉県より発送、<br>'.$prev_date->format('j').'日('.$dow[$prev_date->format('w')].')正午までにお支払いください。<br>(*最短'.$dow[$next_date->format('w')].'曜日午前着)</div>
            <img class="content-outer-box" width="100%"
              src="https://xn--xckya1d0c8233adqyab74d.com/wp-content/uploads/2022/05/hd-content.png">
          </td>
        </tr>
        '.$description_youtube;
        $description_bottom = '      <tr>
          <td height="60px">
            <font color="#ff0000" size="5"><br><br><br><b>送料無料キャンペーン実施中！</b></font>
          </td>
        </tr>
        <tr>
          <td>
            <br>全国一律送料無料！<br>北海道/沖縄もOK！<br><br>
          </td>
        </tr>
        <tr>
          <td height="60px">
            <font size="5" color="#555"><b>安心の「☆」到着保証</b></font>
          </td>
        </tr>
        <tr>
          <td>
            到着したベタちゃんが動かない、<br>
            回復不能なレベルで弱っていた。<br><br>
            ご安心ください、<br><br>
            熱帯魚ショップ.comでは、<br>
            死着保証をご提供しています。<br><br>
          </td>
        </tr>
        <tr>
          <td height="60px">
            <font size="5" color="#555"><b>バンコク直通最短お届け</b></font>
          </td>
        </tr>
        <tr>
          <td>
            →木曜深夜バンコク発<br>
            →金曜早朝日本着<br>
            →税関受取<br>
            →当日千葉県より発送<br>
            →土曜日or日曜日到着予定<br><br>

            発送連絡はバンコク発送後に行います。<br>

            最短ルートでお届けします、<br>
            到着予定は金曜発送後に通知可能です。<br><br>

            *最短お届けより遅い日程のご指定は、<br>
            死着保証適応外とさせていただきます。<br><br>

            【お支払いステータスについて】<br>
            当日発送は木曜日正午までOK!<br><br>
          </td>
        </tr>
        <tr>
          <td height="60px">
            <font size="5" color="#555"><b>同時販売について</b></font>
          </td>
        </tr>
        <tr>
          <td>
            多数媒体で販売中です！<br><br>
            ほぼ同時に購入された場合、<br>
            先に入金確認できた方が優先になります。<br>
            お支払いはお早めにお願いします!
          </td>
        </tr>
        <tr>
          <td>
            商品コード: '.$woocommerce_product->sku.'<br><br>
          </td>
        </tr>
        <tr>
          <td>
            <img class="content-outer-box" width="100%"
              src="https://xn--xckya1d0c8233adqyab74d.com/wp-content/uploads/2022/05/content-footer-yahoo-592022-min.png">
          </td>
        </tr>
        <tr>
          <td>
            <img class="content-outer-box" width="100%"
              src="https://xn--xckya1d0c8233adqyab74d.com/wp-content/uploads/2022/05/content-footer-yahoo-5820222-min.png">
          </td>
        </tr>
      </table>

    </div>


  </div>';

        $data = [
          'name' => $name,
          'sku' => $woocommerce_product->sku,
          'slug' => str_replace(' ', '-', $name),
          'type' => 'simple',
          'description' => $description_top.$description_bottom,
          //'short_description' => '<div class="za-short-description"><i class="fas fa-heart fa-lg text-heart"></i> 100% No-Risk Money Back Guarantee! You are fully protected by our D.O.A. 100% Money Back Guarantee.</div>',
          'regular_price' => (string)$regular_price,
          'categories' => $categories,
          'tags' => $tags,
          'images' => $images,
          'manage_stock' => true,
          'stock_quantity' => $woocommerce_product->stock_quantity,
          'backorders' => 'no',
          'sold_individually' => true,
          'weight' => '0.2kg',
          'dimensions' => [
            'width' => '10cm',
            'length' => '20cm',
            'height' => '6cm'
          ],
          'meta_data' => $meta_datas
        ];
        if($sale_price != ''){
          $data['sale_price'] = (string)$sale_price;
        }
        Analog::log('data.'.var_export($data, true));

        //Analog::log('add item');
        $res = $woocommerce_jp->post('products', $data);
        Analog::log('res.'.var_export($res, true));

        if(isset($res->id)){
          $zeroaqua_data = [
            'meta_data' => [[
              'key' => 'post_to_aquashop',
              'value' => 'posted'
            ]]
          ];
          $zeroaqua_res = $woocommerce->put('products/'.$woocommerce_product->id, $zeroaqua_data);

          $update_data = [
            'slug' => $res->slug.'-'.$res->id
          ];
          $i = 0;
          if(isset($res->images)){
            $description_images = '';
            foreach($res->images as $image){
              if($i == 0){
                $top_file = $image->src;

                $top = imagecreatefromjpeg($top_file);

                list($top_width, $top_height) = getimagesize($top_file);

                $new = imagecreatetruecolor($top_width, $top_height);
                imagefilledrectangle($new, 0, 0, $top_width, $top_height, imagecolorallocate($new, 255, 255, 255));
                imagecopyresampled($new, $top, 0, 0, 0, 0, $top_width, $top_height, $top_width, $top_height);

                imagejpeg($new, $root_path.'files/'.$res->sku.'-jp-merged.jpg', 100);
              }else{
                $top_file = 'http://156.67.219.175/post/files/'.$res->sku.'-jp-merged.jpg';
                $bottom_file = $image->src;

                $top = imagecreatefromjpeg($top_file);
                $bottom = imagecreatefromjpeg($bottom_file);

                // get current width/height
                list($top_width, $top_height) = getimagesize($top_file);
                list($bottom_width, $bottom_height) = getimagesize($bottom_file);

                // compute new width/height
                $new_width = ($top_width > $bottom_width) ? $top_width : $bottom_width;
                $new_height = $top_height + $bottom_height + 20;

                // create new image and merge
                $new = imagecreatetruecolor($new_width, $new_height);
                imagefilledrectangle($new, 0, 0, $top_width, $new_height, imagecolorallocate($new, 255, 255, 255));
                imagecopyresampled($new, $top, 0, 0, 0, 0, $top_width, $top_height, $top_width, $top_height);
                imagecopyresampled($new, $bottom, 0, $top_height+21, 0, 0, $bottom_width, $bottom_height, $bottom_width, $bottom_height);

                // save to file
                imagejpeg($new, $root_path.'files/'.$res->sku.'-jp-merged.jpg', 100);
              }

              $i = $i + 1;
            }
            if(count($res->images) > 0){
              $description_images = $description_images.'     <tr>
        <td style="text-align:center;">
          <img class="content-image" width="100%"
            src="https://cms.zeroaqua.com/post/files/'.$res->sku.'-jp-merged.jpg">
        </td>
      </tr>';
            }
            $update_data['description'] = $description_top.$description_images.$description_bottom;
          }
          $update_res = $woocommerce_jp->put('products/'.$res->id, $update_data);
          Analog::log('woocommerce_update_res.'.var_export($update_res, true));

          $delete_res = $woocommerce_jp->delete('products/'.$res->id, ['force' => false]);
          Analog::log('woocommerce_delete_res.'.var_export($delete_res, true));
        }
      }
    }
  }
  Analog::log($counter);
}
?>