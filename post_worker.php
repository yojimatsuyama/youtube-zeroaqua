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
    'http://zeroaqua.com',
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

      /*$product->slug = $product->slug.'-'.$sku;
      $product->slug = str_replace(' ', '-', $product->slug);
      $slug = $product->slug;*/

      foreach($videos as $video_url)
      {
        Analog::log('name.'.$youtube_description_names[$vid_count]);

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
        $videoSnippet->setTitle('☆SALE☆ Betta Fish #shorts #aquarium #ベタ No:'.$sku);
        $color_str_youtube = '';
        if($color_str != ''){
          $color_str_youtube = $color_str.' Color';
        }
        $videoSnippet->setDescription($youtube_description_names[$vid_count].'

100% D.O.A. Money Back Guarantee

Online Store:
https://zeroaqua.com/product/'.$slug.'


-----

Responsibility:
This video is posted by ZEROAQUA (zeroaqua.com)

Registered Business in Thailand
Shipping Betta Fish to US, UK, EU, SG, JP

*Request us to add shipping to your region.
(Business Discount Available)

WhatsApp: 0979989788
Email: info@zeroaqua.com

Terms:
https://zeroaqua.com/Terms-of-Use/

Privacy Policy:
https://zeroaqua.com/privacy-policy/');
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
        $description = $description . '<div class="video-holder">[iframe src="http://www.youtube.com/embed/'.$response['id'].'" width="560" height="315" frameborder="0" allowfullscreen="allowfullscreen"]</div>';
        $vid_count = $vid_count + 1;
        Analog::log('google_api_res.'.var_export($response, true));
      }

      if($vid_count > 0){
        $description = $description . '<span ="text-secondary">*No color enhancement filters applied to the footage, recorded under white led lights.</span>';
      }

      /*$product->description = $description . $product->description;
      $res = $woocommerce->post('products' ,$product);
      Analog::log('woocommerce_res.'.var_export($res, true));*/

      $tmp_description = $description . $tmp_description;
      $update_data = [
        'description' => $tmp_description,
        'slug' => $tmp_slug
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