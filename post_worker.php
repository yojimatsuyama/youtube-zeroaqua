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

//$client = new SimpleClient('file:///home/u892-pnwjixzcfqyf/www/zeroaqua.com/public_html/post/foo/bar');
$client = new SimpleClient('file:///var/www/home/post/foo/bar');

$client->bindTopic('post', function(Message $message) {
  sleep(1);
  Analog::handler(File::init('/var/www/home/post/log/post_worker_'.(new DateTime())->format('Y-m-d').'.log'));
  Analog::log('test');
  $google_client = new Google_Client();
  $google_client->setAuthConfig('/var/www/home/post/client_secret.json');
  $google_client->setAccessType('offline');
  $google_client->setApprovalPrompt('force');
  $google_client->setAccessToken(file_get_contents('/var/www/home/post/youtube_accesstoken.txt'));

  // Define service object for making API requests.
  $service = new Google_Service_YouTube($google_client);

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

  $data = json_decode($message->getBody());
  Analog::log('data.'.var_export($data, true));
  $videos = $data->videos;
  $youtube_description_names = $data->youtube_description_names;
  $youtube_title_names = $data->youtube_title_names;
  $post_to_youtube = $data->post_to_youtube;
  $products = $data->products;
  $sku = $products[0]->sku;

  foreach($products as $product)
  {
    $sku = $product->sku;
    $description = '';
    $vid_count = 0;

    $sku_bool = false;
    $sku_count = 1;
    $sku_str = $sku;
    while(!$sku_bool){
      $data = [
        'sku' => $sku_str
      ];

      $res = $woocommerce->get('products', $data);
      //echo $sku_str . "\n";
      //echo count($res) . "\n";

      if(count($res) == 0){
        $sku_bool = true;
        $sku = $sku_str;
      }else{
        $sku_count = $sku_count + 1;
        $sku_str = $sku . $sku_count;
      }
    }

    $product->sku = $sku;

    $tmp_slug = $product->slug;
    $product->slug = "";
    $tmp_description = $product->description;
    $product->description = "";

    $res = $woocommerce->post('products' ,$product);
    Analog::log('woocommerce_res.'.var_export($res, true));

    if(isset($res->id)){
      $woocommerce_id = $res->id;
      $tmp_slug = $tmp_slug.'-'.$woocommerce_id;
      $tmp_slug = str_replace(' ', '-', $tmp_slug);
      $slug = $tmp_slug;

      $update_data = [
        'slug' => $tmp_slug
      ];
      $res = $woocommerce->put('products/'.$woocommerce_id, $update_data);
      Analog::log('woocommerce_update_res.'.var_export($res, true));

      /*$product->slug = $product->slug.'-'.$sku;
      $product->slug = str_replace(' ', '-', $product->slug);
      $slug = $product->slug;*/

      foreach($videos as $video_url)
      {
        if($post_to_youtube == 1) { //schedule
          $mysqli = new mysqli('localhost', 'zeroaqua-post', '%)?t`x6>5L4Vk5sJ', 'zeroaqua');
  
          if($mysqli->connect_errno){
            die(json_encode(array('message' => 'Failed to connect to MySQL: ' . $mysqli->connect_error, 'code' => 500)));
          }

          if($result = $mysqli->query('INSERT INTO youtube_queues (sku,title,description_name,woocommerce_id) VALUE ("'.$sku.'","'.$youtube_title_names[$vid_count].'","'.$youtube_description_names[$vid_count].'","'.$woocommerce_id.'")')){
            
          }else{
            die(json_encode(array('message' => 'Error updating record: ' . $mysqli->error, 'code' => 500)));
          }

          //$result->free_result();
          $mysqli->close();
        }
        elseif($post_to_youtube == 2){ //post now
          //queue
          $mysqli = new mysqli('localhost', 'zeroaqua-post', '%)?t`x6>5L4Vk5sJ', 'zeroaqua');
  
          if($mysqli->connect_errno){
            die(json_encode(array('message' => 'Failed to connect to MySQL: ' . $mysqli->connect_error, 'code' => 500)));
          }

          if($result = $mysqli->query('INSERT INTO youtube_queues (sku,title,description_name,woocommerce_id) VALUE ("'.$sku.'","'.$youtube_title_names[$vid_count].'","'.$youtube_description_names[$vid_count].'","'.$woocommerce_id.'")')){
            
          }else{
            die(json_encode(array('message' => 'Error updating record: ' . $mysqli->error, 'code' => 500)));
          }

          //$result->free_result();
          $mysqli->close();

          Analog::log('description_name.'.$youtube_description_names[$vid_count]);
          Analog::log('title_name.'.$youtube_title_names[$vid_count].' No:'.$sku);

          if($google_client->isAccessTokenExpired()) {
            Analog::log('refresh_google_api_access_token');
            $token = json_decode(file_get_contents('/var/www/home/post/youtube_accesstoken.txt'));
            $google_client->refreshToken($token->refresh_token);
            $token = $google_client->getAccessToken();
            file_put_contents('/var/www/home/post/youtube_accesstoken.txt', json_encode($token));
          }

          // Define the $video object, which will be uploaded as the request body.
          $video = new Google_Service_YouTube_Video();

          // Add 'snippet' object to the $video object.
          $videoSnippet = new Google_Service_YouTube_VideoSnippet();
          $videoSnippet->setCategoryId('15');
          //$videoSnippet->setTitle('#shorts Betta Aquarium ベタ 熱帯魚 ' . $sku);
          //$videoSnippet->setTitle('Buy Betta Online - 100% DOA Money Back Guarantee #ベタ通販');
          //$videoSnippet->setTitle('☆SALE☆ Betta Fish #shorts #aquarium #ベタ No:'.$sku);
          $videoSnippet->setTitle($youtube_title_names[$vid_count].' No:'.$sku);

          /*$color_str_youtube = '';
          if($color_str != ''){
            $color_str_youtube = $color_str.' Color';
          }*/

          $videoSnippet->setDescription('【キャンペーン実施中 | 全国どこでも送料無料】

全個体☆保証有り

複数媒体で同時に出品中、
ご購入は早い者勝ちです！

-----

Responsibility:
This video is posted by Aquashop(aquashop.jp)

Registered Business in Thailand
Shipping Betta Fish to US, UK, EU, SG, JP

*Request us to add shipping to your region.
(Business Discount Available)

WhatsApp: 0979989788
Email: info@aquashop.com

Terms:
https://aquashop.jp/Terms-of-Use/

Privacy Policy:
https://aquashop.jp/privacy-policy/');
          $video->setSnippet($videoSnippet);

          // Add 'status' object to the $video object.
          $videoStatus = new Google_Service_YouTube_VideoStatus();
          $videoStatus->setPrivacyStatus('public');
          $video->setStatus($videoStatus);

          $response = $service->videos->insert(
            'snippet,status',
            $video,
            array(
              'data' => file_get_contents($video_url),
              'mimeType' => 'application/octet-stream',
              'uploadType' => 'multipart'
            )
          );

          Analog::log('google_api_res.'.var_export($response, true));

          if(array_key_exists('id', $response)){
            if($response['id'] != NULL) {
              //update queue
              $mysqli = new mysqli('localhost', 'zeroaqua-post', '%)?t`x6>5L4Vk5sJ', 'zeroaqua');
      
              if($mysqli->connect_errno){
                die(json_encode(array('message' => 'Failed to connect to MySQL: ' . $mysqli->connect_error, 'code' => 500)));
              }

              if($result = $mysqli->query('UPDATE youtube_queues SET posted = true WHERE sku = "'.$sku.'" AND title = "'.$youtube_title_names[$vid_count].'" AND description_name = "'.$youtube_description_names[$vid_count].'" AND woocommerce_id = "'.$woocommerce_id.'"')){
          
              }else{
                die(json_encode(array('message' => 'Error updating record: ' . $mysqli->error, 'code' => 500)));
              }

              //$result->free_result();
              $mysqli->close();

              $description = $description . '<div class="video-holder">[iframe src="http://www.youtube.com/embed/'.$response['id'].'" width="560" height="315" frameborder="0" allowfullscreen="allowfullscreen"]</div>';
              $vid_count = $vid_count + 1;
            }
          }
        }
      }

      if($vid_count > 0){
        $description = $description . '<span ="text-secondary">*No color enhancement filters applied to the footage, recorded under white led lights.</span>';
      }

      //$product->description = $description . $product->description;
      //$res = $woocommerce->post('products' ,$product);
      //Analog::log('woocommerce_res.'.var_export($res, true));

      $tmp_description = $description . $tmp_description;
      $update_data = [
        'description' => $tmp_description
      ];
      $res = $woocommerce->put('products/'.$woocommerce_id, $update_data);
      Analog::log('woocommerce_update_res.'.var_export($res, true));
    }
  }

  return Processor::ACK;
});

// this call is optional but it worth to mention it.
// it configures a broker, for example it can create queues and excanges on RabbitMQ side.
$client->setupBroker();

$client->consume();
?>
