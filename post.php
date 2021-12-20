<?php
require 'AltoRouter.php';

require 'cloudinary/Cloudinary.php';
require 'cloudinary/Uploader.php';
require 'cloudinary/Api.php';
require 'cloudinary/Error.php';

require_once 'google-api-php-client/vendor/autoload.php';

require 'analog/lib/Analog.php';

require_once 'vendor/autoload.php';

use Automattic\WooCommerce\Client;
use Analog\Handler\File;
use Enqueue\SimpleClient\SimpleClient;
use Interop\Queue\Message;
use Interop\Queue\Processor;

set_time_limit(500);

if(!(file_exists('./log/post_'.(new DateTime())->format('Y-m-d').'.log'))){
  $log_file = fopen('./log/post_'.(new DateTime())->format('Y-m-d').'.log', "w") or die("can't open file");
  fclose($log_file);
}
Analog::handler(File::init('./log/post_'.(new DateTime())->format('Y-m-d').'.log'));
if(!(file_exists('./log/post_worker_'.(new DateTime())->format('Y-m-d').'.log'))){
  $log_file = fopen('./log/post_worker_'.(new DateTime())->format('Y-m-d').'.log', "w") or die("can't open file");
  fclose($log_file);
}

$router = new AltoRouter();

$router->setBasePath('/post');

$router->map( "GET", "/", function() {
  require __DIR__ . '/post.html';
});

$router->map( "GET", "/youtube/auth", function() {
  $client = new Google_Client();
  $client->setAuthConfig('client_secret.json');
  $client->addScope(Google_Service_YouTube::YOUTUBE_FORCE_SSL);
  $client->setRedirectUri('https://zeroaqua.com/post/youtube/auth/callback');
  // offline access will give you both an access and refresh token so that
  // your app can refresh the access token without user interaction.
  $client->setAccessType('offline');
  // Using "consent" ensures that your application always receives a refresh token.
  // If you are not using offline access, you can omit this.
  //$client->setApprovalPrompt('consent');
  $client->setApprovalPrompt('force');
  $client->setIncludeGrantedScopes(true);   // incremental auth
  $auth_url = $client->createAuthUrl();
  header('Location: ' . filter_var($auth_url, FILTER_SANITIZE_URL));
});

$router->map( "GET", "/youtube/auth/", function() {
  $client = new Google_Client();
  $client->setAuthConfig('client_secret.json');
  $client->addScope(Google_Service_YouTube::YOUTUBE_FORCE_SSL);
  $client->setRedirectUri('https://zeroaqua.com/post/youtube/auth/callback');
  // offline access will give you both an access and refresh token so that
  // your app can refresh the access token without user interaction.
  $client->setAccessType('offline');
  // Using "consent" ensures that your application always receives a refresh token.
  // If you are not using offline access, you can omit this.
  //$client->setApprovalPrompt("consent");
  $client->setApprovalPrompt('force');
  $client->setIncludeGrantedScopes(true);   // incremental auth
  $auth_url = $client->createAuthUrl();
  header('Location: ' . filter_var($auth_url, FILTER_SANITIZE_URL));
});

$router->map( "GET", "/youtube/auth/callback", function() {
  $client = new Google_Client();
  $client->setAuthConfig('client_secret.json');
  $client->addScope(Google_Service_YouTube::YOUTUBE_FORCE_SSL);
  $client->setRedirectUri('https://zeroaqua.com/post/youtube/auth/callback');
  // offline access will give you both an access and refresh token so that
  // your app can refresh the access token without user interaction.
  $client->setAccessType('offline');
  // Using "consent" ensures that your application always receives a refresh token.
  // If you are not using offline access, you can omit this.
  //$client->setApprovalPrompt("consent");
  $client->setApprovalPrompt('force');
  $client->setIncludeGrantedScopes(true);   // incremental auth

  if (isset($_GET['code'])) {
    $client->authenticate($_GET['code']);
    $access_token = $client->getAccessToken();
    $file = fopen("youtube_accesstoken.txt", "w+");
    fwrite($file, json_encode($access_token));
    echo var_dump($access_token);
    fclose($file);
  }
});

$router->map( "GET", "/youtube/auth/callback/", function() {
  $client = new Google_Client();
  $client->setAuthConfig('client_secret.json');
  $client->addScope(Google_Service_YouTube::YOUTUBE_FORCE_SSL);
  $client->setRedirectUri('https://zeroaqua.com/post/youtube/auth/callback');
  // offline access will give you both an access and refresh token so that
  // your app can refresh the access token without user interaction.
  $client->setAccessType('offline');
  // Using "consent" ensures that your application always receives a refresh token.
  // If you are not using offline access, you can omit this.
  //$client->setApprovalPrompt("consent");
  $client->setApprovalPrompt('force');
  $client->setIncludeGrantedScopes(true);   // incremental auth

  if (isset($_GET['code'])) {
    $client->authenticate($_GET['code']);
    $access_token = $client->getAccessToken();
    $file = fopen("youtube_accesstoken.txt", "w+");
    fwrite($file, json_encode($access_token));
    echo var_dump($access_token);
    fclose($file);
  }
});

/*$router->map( "GET", "/test", function() {
  $google_client = new Google_Client();
  $google_client->setAuthConfig('client_secret.json');
  $google_client->setAccessType('offline');
  $google_client->setApprovalPrompt('force');
  $google_client->setAccessToken(file_get_contents('youtube_accesstoken.txt'));
  $token = json_decode(file_get_contents('youtube_accesstoken.txt'));
  $google_client->refreshToken($token->refresh_token);
  $token = $google_client->getAccessToken();
  file_put_contents('youtube_accesstoken.txt', json_encode($token));
  echo var_dump($token);
});*/

$router->map( "POST", "/post", function() {
  if(!(file_exists('./log/post_worker_'.(new DateTime())->format('Y-m-d').'.log'))){
    $log_file = fopen('./log/post_worker_'.(new DateTime())->format('Y-m-d').'.log', "w") or die("can't open file");
    fclose($log_file);
  }

  Analog::log('/post');
  Analog::log('post_params.'.var_export($_POST, true));
  Analog::log('post_files.'.var_export($_FILES, true));

  \Cloudinary::config([
    "cloud_name" => "domr7bm4t",
    "api_key" => "393941319363972",
    "api_secret" => "lGJYpU83kOW6Eg3X8_L6h3E77gM",
  ]);

  /*$google_client = new Google_Client();
  $google_client->setAuthConfig('client_secret.json');
  $google_client->setAccessType('offline');
  $google_client->setApprovalPrompt('force');
  $google_client->setAccessToken(file_get_contents('youtube_accesstoken.txt'));

  if($google_client->isAccessTokenExpired() || true) {
    Analog::log('refresh_google_api_access_token');
    $token = json_decode(file_get_contents('youtube_accesstoken.txt'));
    $google_client->refreshToken($token->refresh_token);
    $token = $google_client->getAccessToken();
    file_put_contents('youtube_accesstoken.txt', json_encode($token));
  }

  // Define service object for making API requests.
  $service = new Google_Service_YouTube($google_client);*/

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

  $skus = [];
  $meta_datas = [];
  $tags = [];
  $premium = false;
  $penny = false;
  $type = 0;
  $nickname = 0;
  $gender = 0;
  $age = 0;
  $color_black = false;
  $color_white = false;
  $color_silver = false;
  $color_gold = false;
  $color_copper = false;
  $color_yellow = false;
  $color_orange = false;
  $color_red = false;
  $color_purple = false;
  $color_blue = false;
  $color_green = false;
  $color_turquoise = false;

  $color_str = '';
  $color_str_comma = '';

  foreach($_POST as $key=>$value)
  {
    //echo "$key=$value\n";
    switch($key){
      case 'sku':
        $skus = explode(',', str_replace(' ', '', $_POST['sku']));
        break;
      case 'premium':
        if($value = 'on'){
          $premium = true;
        }
        break;
      case 'penny':
        if($value = 'on'){
          $penny = true;
        }
        break;
      case 'type':
        $type = $value;
        break;
      case 'nickname':
        $nickname = $value;
        break;
      case 'gender':
        $gender = $value;
        break;
      case 'age':
        $age = $value;
        if($age > 0){
          $sub_days = $age * 30;
          $birthday = date_sub(new DateTime(), date_interval_create_from_date_string($sub_days.' days'));
          array_push($meta_datas,[
            'key' => 'age',
            'value' => $age
          ]);
          array_push($meta_datas,[
            'key' => 'birthday',
            'value' => $birthday->format('Y-m-d')
          ]);
        }
        break;
      case 'color-black':
        if($value = 'on'){
          $color_black = true;
          $color_str = $color_str . ' Black';
          $color_str_comma = $color_str_comma . 'Black,';
          array_push($tags, ['id' => 41]);
        }
        break;
      case 'color-white':
        if($value = 'on'){
          $color_white = true;
          $color_str = $color_str . ' White';
          $color_str_comma = $color_str_comma . 'White,';
          array_push($tags, ['id' => 39]);
        }
        break;
      case 'color-silver':
        if($value = 'on'){
          $color_silver = true;
          $color_str = $color_str . ' Silver';
          $color_str_comma = $color_str_comma . 'Silver,';
          array_push($tags, ['id' => 60]);
        }
        break;
      case 'color-gold':
        if($value = 'on'){
          $color_gold = true;
          $color_str = $color_str . ' Gold';
          $color_str_comma = $color_str_comma . 'Gold,';
          array_push($tags, ['id' => 61]);
        }
        break;
      case 'color-copper':
        if($value = 'on'){
          $color_copper = true;
          $color_str = $color_str . ' Copper';
          $color_str_comma = $color_str_comma . 'Copper,';
          array_push($tags, ['id' => 62]);
        }
        break;
      case 'color-yellow':
        if($value = 'on'){
          $color_yellow = true;
          $color_str = $color_str . ' Yellow';
          $color_str_comma = $color_str_comma . 'Yellow,';
          array_push($tags, ['id' => 63]);
        }
        break;
      case 'color-orange':
        if($value = 'on'){
          $color_orange = true;
          $color_str = $color_str . ' Orange';
          $color_str_comma = $color_str_comma . 'Orange,';
          array_push($tags, ['id' => 40]);
        }
        break;
      case 'color-red':
        if($value = 'on'){
          $color_red = true;
          $color_str = $color_str . ' Red';
          $color_str_comma = $color_str_comma . 'Red,';
          array_push($tags, ['id' => 38]);
        }
        break;
      case 'color-purple':
        if($value = 'on'){
          $color_purple = true;
          $color_str = $color_str . ' Purple';
          $color_str_comma = $color_str_comma . 'Purple,';
          array_push($tags, ['id' => 64]);
        }
        break;
      case 'color-blue':
        if($value = 'on'){
          $color_blue = true;
          $color_str = $color_str . ' Blue';
          $color_str_comma = $color_str_comma . 'Blue,';
          array_push($tags, ['id' => 65]);
        }
        break;
      case 'color-green':
        if($value = 'on'){
          $color_green = true;
          $color_str = $color_str . ' Green';
          $color_str_comma = $color_str_comma . 'Green,';
          array_push($tags, ['id' => 66]);
        }
        break;
      case 'color-turquoise':
        if($value = 'on'){
          $color_turquoise = true;
          $color_str = $color_str . ' Turquoise';
          $color_str_comma = $color_str_comma . 'Turquoise,';
          array_push($tags, ['id' => 67]);
        }
        break;
    }
  }

  $color_str_comma = rtrim($color_str_comma, ',');
  array_push($meta_datas,[
    'key' => 'color',
    'value' => $color_str_comma
  ]);

  //echo var_dump($skus);
  //echo var_dump($premium);
  //echo var_dump($penny);
  //echo var_dump($type);
  //echo var_dump($nickname);
  //echo var_dump($gender);
  //echo var_dump($age);
  //echo var_dump($color_black);
  //echo var_dump($color_white);
  //echo var_dump($color_silver);
  //echo var_dump($color_gold);
  //echo var_dump($color_copper);
  //echo var_dump($color_yellow);
  //echo var_dump($color_orange);
  //echo var_dump($color_red);
  //echo var_dump($color_purple);
  //echo var_dump($color_blue);
  //echo var_dump($color_green);
  //echo var_dump($color_turquoise);

  $regular_price = 0;
  $sale_price = 0;
  $regular_price_jpy = 0;
  $sale_price_jpy = 0;
  $regular_price_thb = 0;
  $ebay_us_start_price = 0;
  $ebay_us_buy_price = 0;
  $yahoo_auction_start_price = 0;
  $yahoo_auction_buy_price = 0;
  $yahoo_shopping_price = 0;

  if($penny){
    if(!$premium){ //Premium off + Penny Start
      $regular_price = 25;
      $sale_price = 19.95;
      $regular_price_jpy = 2500;
      $sale_price_jpy = 1980;
      $regular_price_thb = 490;
      $ebay_us_start_price = 0.01;
      $yahoo_auction_start_price = 1;
    }else if(!$premium){ //Premium on + Penny Start
      $regular_price = 350;
      $regular_price_jpy = 35000;
      $regular_price_thb = 12000;
      $ebay_us_start_price = 0.01;
      $yahoo_auction_start_price = 1;
    }
  }else{
    if(!$premium && ($gender == 1 || $gender == 2)){ //Premium off (male or female)
      $regular_price = 25;
      $sale_price = 19.95;
      $regular_price_jpy = 2500;
      $sale_price_jpy = 1980;
      $regular_price_thb = 490;
      $ebay_us_start_price = 9.95;
      $ebay_us_buy_price = 35;
      $yahoo_auction_start_price = 980;
      $yahoo_auction_buy_price = 3500;
      $yahoo_shopping_price = 2980;
    }else if($premium && ($gender == 1 || $gender == 2)){ //Premium on (male or female)
      $regular_price = 55;
      $sale_price = 45.95;
      $regular_price_jpy = 5500;
      $sale_price_jpy = 4980;
      $regular_price_thb = 950;
      $ebay_us_start_price = 19.95;
      $ebay_us_buy_price = 55;
      $yahoo_auction_start_price = 1980;
      $yahoo_auction_buy_price = 5500;
      $yahoo_shopping_price = 4980;
    }else if(!$premium && $gender == 3){ //Premium off (pair)
      $regular_price = 110;
      $sale_price = 99.95;
      $regular_price_jpy = 11000;
      $sale_price_jpy = 9980;
      $regular_price_thb = 1000;
      $ebay_us_start_price = 29.95;
      $ebay_us_buy_price = 99.95;
      $yahoo_auction_start_price = 2980;
      $yahoo_auction_buy_price = 11000;
      $yahoo_shopping_price = 9980;
    }else if($premium && $gender == 3){ //Premium on (pair)
      $regular_price = 350;
      $sale_price = 299.95;
      $regular_price_jpy = 35000;
      $sale_price_jpy = 29800;
      $regular_price_thb = 9500;
      $ebay_us_start_price = 299.95;
      $ebay_us_buy_price = 299.95;
      $yahoo_auction_start_price = 29800;
      $yahoo_auction_buy_price = 29800;
      $yahoo_shopping_price = 29800;
    }
  }

  $price_method_jpy = 'manual';
  $sale_price_jpy = $sale_price_jpy > 0 ? $sale_price_jpy : '';
  $price_jpy = $sale_price_jpy > 0 ? $sale_price_jpy : $regular_price_jpy;
  $sale_price_dates_jpy = 'manual';
  $sale_price_dates_from_jpy = '';
  $sale_price_dates_to_jpy = '';

  $price_method_thb = 'manual';
  $sale_price_thb = $sale_price_thb > 0 ? $sale_price_thb : '';
  $price_thb = $sale_price_thb > 0 ? $sale_price_thb : $regular_price_thb;
  $sale_price_dates_thb = 'manual';
  $sale_price_dates_from_thb = '';
  $sale_price_dates_to_thb = '';

  array_push($meta_datas,[
    'key' => '_jpy_price_method',
    'value' => (string)$price_method_jpy
  ]);
  array_push($meta_datas,[
    'key' => '_jpy_regular_price',
    'value' => (string)$regular_price_jpy
  ]);
  array_push($meta_datas,[
    'key' => '_jpy_sale_price',
    'value' => (string)$sale_price_jpy
  ]);
  array_push($meta_datas,[
    'key' => '_jpy_price',
    'value' => (string)$price_jpy
  ]);
  array_push($meta_datas,[
    'key' => '_jpy_sale_price_dates',
    'value' => (string)$sale_price_dates_jpy
  ]);
  array_push($meta_datas,[
    'key' => '_jpy_sale_price_dates_from',
    'value' => (string)$sale_price_dates_from_jpy
  ]);
  array_push($meta_datas,[
    'key' => '_jpy_sale_price_dates_to',
    'value' => (string)$sale_price_dates_to_jpy
  ]);

  array_push($meta_datas,[
    'key' => '_thb_price_method',
    'value' => (string)$price_method_thb
  ]);
  array_push($meta_datas,[
    'key' => '_thb_regular_price',
    'value' => (string)$regular_price_thb
  ]);
  array_push($meta_datas,[
    'key' => '_thb_sale_price',
    'value' => (string)$sale_price_thb
  ]);
  array_push($meta_datas,[
    'key' => '_thb_price',
    'value' => (string)$price_thb
  ]);
  array_push($meta_datas,[
    'key' => '_thb_sale_price_dates',
    'value' => (string)$sale_price_dates_thb
  ]);
  array_push($meta_datas,[
    'key' => '_thb_sale_price_dates_from',
    'value' => (string)$sale_price_dates_from_thb
  ]);
  array_push($meta_datas,[
    'key' => '_thb_sale_price_dates_to',
    'value' => (string)$sale_price_dates_to_thb
  ]);

  if($ebay_us_start_price > 0){
    array_push($meta_datas,[
      'key' => 'ebay_us_start_price',
      'value' => $ebay_us_start_price
    ]);
  }
  if($ebay_us_buy_price > 0){
    array_push($meta_datas,[
      'key' => 'ebay_us_buy_price',
      'value' => $ebay_us_buy_price
    ]);
  }
  if($yahoo_auction_start_price > 0){
    array_push($meta_datas,[
      'key' => 'yahoo_auction_start_price',
      'value' => $yahoo_auction_start_price
    ]);
  }
  if($yahoo_auction_buy_price > 0){
    array_push($meta_datas,[
      'key' => 'yahoo_auction_buy_price',
      'value' => $yahoo_auction_buy_price
    ]);
  }
  if($yahoo_shopping_price > 0){
    array_push($meta_datas,[
      'key' => 'yahoo_shopping_price',
      'value' => $yahoo_shopping_price
    ]);
  }
  
  $type_str = '';
  switch($type){
    case 15:
      $type_str = 'Prakat';
      break;
    case 49:
      $type_str = 'Half Moon';
      break;
    case 50:
      $type_str = 'Crowntail';
      break;
    case 51:
      $type_str = 'Dumbo';
      break;
    case 52:
      $type_str = 'Wild';
      break;
  }
  array_push($meta_datas,[
    'key' => 'type',
    'value' => $type_str
  ]);

  $nickname_str = '';
  switch($nickname){
    case 54:
      $nickname_str = ' Nemo';
      break;
    case 55:
      $nickname_str = ' Nemo Galaxy';
      break;
    case 56:
      $nickname_str = ' Black Samurai';
      break;
    case 57:
      $nickname_str = ' Avatar';
      break;
    case 58:
      $nickname_str = ' Alien';
      break;
  }
  array_push($meta_datas,[
    'key' => 'nickname',
    'value' => ltrim($nickname_str, ' ')
  ]);

  $gender_str = '';
  switch($gender){
    case 1:
      $gender_str = ' Male';
      break;
    case 2:
      $gender_str = ' Female';
      break;
    case 3:
      $gender_str = ' Pair';
      break;
  }
  array_push($meta_datas,[
    'key' => 'gender',
    'value' => ltrim($gender_str, ' ')
  ]);

  $categories = [];
  if($type > 0){
    array_push($categories, ['id' => (int)$type]);
  }
  if($nickname > 0){
    array_push($categories, ['id' => (int)$nickname]);
  }
  if($gender == 3){
    array_push($categories, ['id' => 53]);
  }
  if($premium){
    array_push($categories, ['id' => 25]);
  }

  $images = [];
  $videos = [];
  $img_count = 0;
  $vid_count = 0;
  $description = '';

  $sku_bool = false;
  $sku_count = 1;
  $sku_str = $skus[0];
  while(!$sku_bool){
    $data = [
      'sku' => $sku_str
    ];
  
    $res = $woocommerce->get('products', $data);
    //echo $sku_str . "\n";
    //echo count($res) . "\n";

    if(count($res) == 0){
      $sku_bool = true;
      $skus[0] = $sku_str;
    }else{
      $sku_count = $sku_count + 1;
      $sku_str = $skus[0] . $sku_count;
    }
  }

  $sku_main = $skus[0];

  foreach($_FILES as $file)
  {
    if(preg_match('/^([a-zA-Z0-9\s_\\.\-:])+(.mp4|.mov)$/', strtolower($file['name']))){
      Analog::log('upload_video.'.var_export($file, true));

      $res = \Cloudinary\Uploader::upload_large($file['tmp_name'], ['resource_type' => 'video', 'public_id' => $sku_main]);
      Analog::log('cloudinary_video_res.'.var_export($res, true));

      array_push($videos, $res['url']);

      /*// Define the $video object, which will be uploaded as the request body.
      $video = new Google_Service_YouTube_Video();

      // Add 'snippet' object to the $video object.
      $videoSnippet = new Google_Service_YouTube_VideoSnippet();
      $videoSnippet->setCategoryId('15');
      $videoSnippet->setTitle('#shorts Betta Aquarium ベタ 熱帯魚 ' . $sku_main);
      $video->setSnippet($videoSnippet);

      // Add 'status' object to the $video object.
      $videoStatus = new Google_Service_YouTube_VideoStatus();
      $videoStatus->setPrivacyStatus('private');
      $video->setStatus($videoStatus);

      $response = $service->videos->insert(
        'snippet,status',
        $video,
        array(
          'data' => file_get_contents($file['tmp_name']),
          'mimeType' => 'application/octet-stream',
          'uploadType' => 'multipart'
        )
      );
      $description = $description . '<div class="video-holder">[iframe src="http://www.youtube.com/embed/'.$response['id'].'" width="560" height="315" frameborder="0" allowfullscreen="allowfullscreen"]</div>';
      $vid_count = $vid_count + 1;
      Analog::log('google_api_res.'.var_export($response, true));*/
    }
    if(preg_match('/^([a-zA-Z0-9\s_\\.\-:])+(.jpg|.jpeg|.gif|.png|.bmp)$/', strtolower($file['name']))){
      Analog::log('upload_img.'.var_export($file, true));

      $img_count = $img_count + 1;
        
      $res = \Cloudinary\Uploader::upload_large($file['tmp_name'], ['resource_type' => 'image', 'public_id' => $sku_main.'-'.$img_count, 'width' => 490, 'height' => 490, 'crop' => 'fit']);
      Analog::log('cloudinary_img_res.'.var_export($res, true));
      array_push($images, ['src' => $res['url']]);
    }
  }

  /*if($vid_count > 0){
    $description = $description . '*No color enhancement filters applied to the footage, recorded under white led lights.';
  }*/
  $description = $description . '<div class="ms--167">
<div class="row top-md center-md center-sm center-xs">
    <div class="col-md-2 col-sm-12 col-xs-12 mobile-w50p">
        <img class="aligncenter floatright-pc w50p-pc" alt="zeroaqua moneyback guarantee badge" src="https://zeroaqua.com/wp-content/uploads/2021/09/doa-money-back-guarantee-badge-1.png">
    </div>
    <div class="col-md-6 col-sm-12 col-xs-12 text-left">
        <h3 class="mt-21 font-14em">100% D.O.A.  Money Back Guarantee</h3>

    <p>Approximately 0.9% of those who buy betta online experience D.O.A (death on arrival) We do our best to reduce D.O.A as much as possible since it’s a tragic event for all of us.</p>

    <p>However, if you unfortunately experience this unlikely & unwanted event, we provide “100% D.O.A. Money Back Guarantee” No questions asked!</p>
<p>We’d like you to have peace of mind when shopping with us.</p>
    </div>
</div>


<div class="row center-md center-sm center-xs mt-20">
    <div class="col-md-2 col-sm-12 col-xs-12 mobile-w50p">
        <img class="aligncenter floatright-pc w50p-pc" alt="zeroaqua managed shipping from Thailand" src="https://zeroaqua.com/wp-content/uploads/2021/09/managed-shipping-badge-1.png">
    </div>
    <div class="col-md-6 col-sm-12 col-xs-12 text-left">
        <h3 class="mt-21 font-14em">Hassle Free: Fully Managed Shipping</h3>

<img src="https://zeroaqua.com/wp-content/uploads/2021/09/fully-managed-international-shipping.png" alt="Fully Managed International Shipping" width="485" height="238" class="alignnone size-full wp-image-9475" />

    <p>Let us do it for you! Buying live fish from overseas can get challenging, after we know your delivery address, we will take care of all delivery processes for you (inc. custom clearance), you can say good bye to paper works!</p>
    </div>
</div>

<div class="row center-md center-sm center-xs mt-20">
    <div class="col-md-2 col-sm-12 col-xs-12 mobile-w50p">
        <img class="aligncenter floatright-pc w50p-pc" alt="zeroaqua betta youtube video" src="https://zeroaqua.com/wp-content/uploads/2021/09/video-badge-1.png">
    </div>
    <div class="col-md-6 col-sm-12 col-xs-12 text-left">
        <h3 class="mt-21 font-14em">Photos & Videos Ready for All Bettas</h3>

    <p>How disappointed can we get when you buy a betta online and get shipped a totally different looking betta. Unfortunately there are online stores which don\'t mind putting best looking photos and ship out it’s child bettas.</p>

                 <p><b>ZEROAQUA is Different</b></p>

                 <p>Because all bettas are different, You can find photos and videos for each bettas. You can buy betta online with ZEROAQUA, just as easily as shopping at a local store.</p>
    </div>
</div>
</div>


  <img src="https://zeroaqua.com/wp-content/uploads/2021/09/mello-zeroaqua.jpg" alt="Mello-Zeroaqua" width="1000" height="563" class="alignnone size-full wp-image-600 mt-38" />

  <div class="za-product-content">
  <h3>Note to next owner</h3>
  Hi! I\'m Mello, looking after Bettas at Zeroaqua!

  Betta is fed with protein rich pallets 2 times/day, 5 times/week (eats well!) and bloodworms 1 time/week as snack. No food on Sunday to improve intestinal environment. (No chemicals used to boost the growth)

  Water is changed daily, saline concentration is kept to 0.2% ~ 0.4% and organic almond (umbrella) leaf is added to help betta stay as comfortable as possible.

  We open up the blinds once a day for about 10~15 min to let Bettas flare up and exercise.

  *We raise betta with lots of care and love, Please do not purchase if you cant be responsible for animal lives.

  </div>

<div class="za-product-content">
  <h3 class="mt-20">Next Shipping Date</h3>
</div>
<div class="row center-md center-sm center-xs mt-40">
    <div class="col-md-2 col-sm-6 col-xs-6">
        <img class="center" alt="Buy Betta Online from United states" src="https://zeroaqua.com/wp-content/uploads/2021/09/united-states-flag.png">
        <h3 class="font-1em mt-10 mb-0">United States</h3>
        <p>Next Shipping:<br><span id="shipping-us">29/September/2021</span> (GMT+7)</p>
    </div>
    <div class="col-md-2 col-sm-6 col-xs-6">
        <img class="center" alt="Buy Betta Online from United Kingdom" src="https://zeroaqua.com/wp-content/uploads/2021/09/united-kingdom-flag.png">
        <h3 class="font-1em mt-10 mb-0">United Kingdom</h3>
        <p>Next Shipping:<br><span id="shipping-gb">29/September/2021</span> (GMT+7)</p>
    </div>
    <div class="col-md-2 col-sm-6 col-xs-6">
        <img class="center" alt="Buy Betta Online from European Union" src="https://zeroaqua.com/wp-content/uploads/2021/09/european-union-flag.png">
        <h3 class="font-1em mt-10 mb-0">European Union</h3>
        <p>Next Shipping:<br><span id="shipping-eu">29/September/2021</span> (GMT+7)</p>
    </div>
    <div class="col-md-2 col-sm-6 col-xs-6">
        <img class="center" alt="Buy Betta Online from Japan" src="https://zeroaqua.com/wp-content/uploads/2021/09/japan-flag.png">
        <h3 class="font-1em mt-10 mb-0">Japan</h3>
        <p>Next Shipping:<br><span id="shipping-jp">29/September/2021</span> (GMT+7)</p>
    </div>
    <div class="col-md-2 col-sm-6 col-xs-6">
        <img class="center" alt="Buy Betta Online from Singapore" src="https://zeroaqua.com/wp-content/uploads/2021/09/singapore-flag.png">
        <h3 class="font-1em mt-10 mb-0">Singapore</h3>
        <p>Next Shipping:<br><span id="shipping-sg">29/September/2021</span> (GMT+7)</p>
    </div>
    <div class="col-md-2 col-sm-6 col-xs-6">
        <img class="center" alt="Buy Betta Online from Thailand" src="https://zeroaqua.com/wp-content/uploads/2021/09/thailand-flag.png">
        <h3 class="font-1em mt-10 mb-0">Thailand</h3>
        <p>Next Day Shipping</p>
    </div>
</div>

<p class="text-center"><a href="#">Request shipping to your country</a></p>';

  $products = [];
  foreach($skus as $sku){
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

    $data = [
      'name' => 'Betta ' . $type_str . $nickname_str . $gender_str . $color_str . ' Aquarium Live Fish',
      'sku' => $sku,
      'slug' => 'Betta'.'-'.$type_str.'-'.$nickname_str.'-'.$gender_str.'-'.$color_str.'-'.'Aquarium-Live-Fish'.'-'.$sku,
      'type' => 'simple',
      'description' => $description,
      'short_description' => '<div class="za-short-description"><i class="fas fa-heart fa-lg text-heart"></i> 100% No-Risk Money Back Guarantee! You are fully protected by our D.O.A. 100% Money Back Guarantee.</div>',
      'regular_price' => (string)$regular_price,
      'sale_price' => (string)$sale_price,
      'categories' => $categories,
      'tags' => $tags,
      'images' => $images,
      'manage_stock' => true,
      'stock_quantity' => 1,
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

    array_push($products, $data);
    Analog::log('woocommerce_params.'.var_export($data, true));

    /*$res = $woocommerce->post('products' ,$data);
    Analog::log('woocommerce_res.'.var_export($res, true));*/
  }

  $queue_data = [
    'videos' => $videos,
    'products' => $products
  ];

  $client = new SimpleClient('file:///home/u892-pnwjixzcfqyf/www/zeroaqua.com/public_html/post/foo/bar');
  $client->sendEvent('post', $queue_data);

  echo 'success';
});

// match current request url
$match = $router->match();

// call closure or throw 404 status
if( is_array($match) && is_callable( $match['target'] ) ) {
  call_user_func_array( $match['target'], $match['params'] ); 
} else {
  // no route was matched
  header( $_SERVER["SERVER_PROTOCOL"] . ' 404 Not Found');
}
?>