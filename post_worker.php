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

$client = new SimpleClient('file:///home/u892-pnwjixzcfqyf/www/zeroaqua.com/public_html/post/foo/bar');

$client->bindTopic('post', function(Message $message) {
  Analog::handler(File::init('/home/u892-pnwjixzcfqyf/www/zeroaqua.com/public_html/post/log/post_worker_'.(new DateTime())->format('Y-m-d').'.log'));

  $client = new Google_Client();
  $client->setAuthConfig('/home/u892-pnwjixzcfqyf/www/zeroaqua.com/public_html/post/client_secret.json');
  $client->setAccessType('offline');
  $client->setApprovalPrompt('force');
  $client->setAccessToken(file_get_contents('/home/u892-pnwjixzcfqyf/www/zeroaqua.com/public_html/post/youtube_accesstoken.txt'));

  if($client->isAccessTokenExpired()) {
    Analog::log('refresh_google_api_access_token');
    $token = json_decode(file_get_contents('/home/u892-pnwjixzcfqyf/www/zeroaqua.com/public_html/post/youtube_accesstoken.txt'));
    $client->refreshToken($token->refresh_token);
    $token = $client->getAccessToken();
    file_put_contents('/home/u892-pnwjixzcfqyf/www/zeroaqua.com/public_html/post/youtube_accesstoken.txt', json_encode($token));
  }

  // Define service object for making API requests.
  $service = new Google_Service_YouTube($client);

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
  $products = $data->products;
  $sku = $products[0]->sku;
  $description = '';

  $vid_count = 0;
  foreach($videos as $video_url)
  {
    /*// Define the $video object, which will be uploaded as the request body.
    $video = new Google_Service_YouTube_Video();

    // Add 'snippet' object to the $video object.
    $videoSnippet = new Google_Service_YouTube_VideoSnippet();
    $videoSnippet->setCategoryId('15');
    //$videoSnippet->setTitle('#shorts Betta Aquarium ベタ 熱帯魚 ' . $sku);
    $videoSnippet->setTitle('Buy Betta Online - 100% DOA Money Back Guarantee #ベタ通販');
    $video->setSnippet($videoSnippet);

    // Add 'status' object to the $video object.
    $videoStatus = new Google_Service_YouTube_VideoStatus();
    $videoStatus->setPrivacyStatus('unlisted');
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
    Analog::log('google_api_res.'.var_export($response, true));*/
  }

  if($vid_count > 0){
    $description = $description . '<span ="text-secondary">*No color enhancement filters applied to the footage, recorded under white led lights.</span>';
  }

  foreach($products as $product)
  {
    $product->description = $description . $product->description;
    $res = $woocommerce->post('products' ,$product);
    Analog::log('woocommerce_res.'.var_export($res, true));
  }

  return Processor::ACK;
});

// this call is optional but it worth to mention it.
// it configures a broker, for example it can create queues and excanges on RabbitMQ side. 
$client->setupBroker();

$client->consume();
?>