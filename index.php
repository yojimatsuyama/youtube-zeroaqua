<?php
require 'AltoRouter.php';

require 'cloudinary/Cloudinary.php';
require 'cloudinary/Uploader.php';
require 'cloudinary/Api.php';
require 'cloudinary/Error.php';

require_once 'google-api-php-client/vendor/autoload.php';

require 'analog/lib/Analog.php';

require_once 'vendor/autoload.php';

require_once "config.php";

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
  Analog::log('GET /');
  check_session();
  require __DIR__ . '/post.html';
});

//to do
/*$router->map( "POST", "/", function() {
  Analog::log('POST /');
  require __DIR__ . '/post.php';
});*/

$router->map( "GET", "/youtube/auth", function() {
  $client = new Google_Client();
  $client->setAuthConfig('client_secret.json');
  $client->addScope(Google_Service_YouTube::YOUTUBE_FORCE_SSL);
  $client->setRedirectUri('https://cms.zeroaqua.com/post/youtube/auth/callback');
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
  $client->setRedirectUri('https://cms.zeroaqua.com/post/youtube/auth/callback');
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
  $client->setRedirectUri('https://cms.zeroaqua.com/post/youtube/auth/callback');
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
  $client->setRedirectUri('https://cms.zeroaqua.com/post/youtube/auth/callback');
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

$router->map( "GET", "/test", function() {
  $file = fopen("wc.csv","r");
  $line_count = 0;
  $product_count = 0;
  while(!feof($file)){
    $line = fgetcsv($file);
    if($line_count != 0){ // skip first line(header)
      if(is_array($line)){
        $product_count++;
      }
    }
    $line_count++;
  }
  /*print_r('line_count');
  print_r($line_count);
  print_r('product_count');
  print_r($product_count);*/
  fclose($file);
  $csv_header = [
    '*Action(SiteID=US|Country=US|Currency=USD|Version=1193)',
    'Custom label (SKU)',
    'Category ID',
    'Title',
    'Relationship',
    'Relationship details',
    'P:UPC',
    'P:ISBN',
    'P:EAN',
    'P:EPID',
    'Start price',
    'Quantity',
    'Item photo URL',
    'Condition ID',
    'Description',
    'Format',
    'Duration',
    'Buy It Now price',
    'Paypal accepted',
    'Paypal email address',
    'Immediate pay required',
    'Payment instructions',
    'Location',
    'Shipping service 1 option',
    'Shipping service 1 cost',
    'Shipping service 1 priority',
    'Shipping service 2 option',
    'Shipping service 2 cost',
    'Shipping service 2 priority',
    'Max dispatch time',
    'Returns accepted option',
    'Returns within option',
    'Refund option',
    'Return shipping cost paid by',
    'Shipping profile name',
    'Return profile name',
    'Payment profile name',
    'TakeBackPolicyID',
    'ProductCompliancePolicyID',
    'C:Species',
    'C:Gender',
    'C:Water Type',
    'C:Water Temperature'
  ];

  header('Content-Type: application/csv');
  header('Content-Disposition: attachment; filename="test.csv";');

  // open the "output" stream
  // see http://www.php.net/manual/en/wrappers.php.php#refsect2-wrappers.php-unknown-unknown-unknown-descriptioq
  $f = fopen('php://output', 'w');

  fputcsv($f, $csv_header, ',');
});

$router->map( "GET", "/ebay", function() {
  Analog::log('GET /ebay');
  require __DIR__ . '/ebay.html';
});

$router->map( "GET", "/ebay/", function() {
  Analog::log('GET /ebay/');
  require __DIR__ . '/ebay.html';
});

$router->map( "POST", "/ebay/", function() {
  $departure_date = '';
  $mysqli = new mysqli('localhost', 'grape', 'f|yE4g|eSf|y', 'zeroaqua');
  
  if($mysqli->connect_errno){
    header('HTTP/1.1 500 Internal Server Error');
    die(json_encode(array('message' => 'Failed to connect to MySQL: ' . $mysqli->connect_error, 'code' => 500)));
  }

  //expired entry
  if($result = $mysqli->query('UPDATE departures SET deleted = true WHERE cutoff_date < "' . date("Y-m-d H:i:s", strtotime('+7 hours')) . '"')){

  }else{
    header('HTTP/1.1 500 Internal Server Error');
    die(json_encode(array('message' => 'Error updating record: ' . $mysqli->error, 'code' => 500)));
  }

  //query
  if($result = $mysqli->query('(SELECT * FROM departures WHERE destination = "United States" AND deleted = false ORDER BY cutoff_date ASC LIMIT 1)')){
    $myArray = [];
    while($row = $result->fetch_array(MYSQLI_ASSOC)) {
      $myArray[] = $row;
    }
    if(count($myArray) == 1){
      $departure_date = date('j/F/Y', strtotime($myArray[0]['departing_date']));
    }
  }else{
    header('HTTP/1.1 500 Internal Server Error');
    die(json_encode(array('message' => 'Error getting record: ' . $mysqli->error, 'code' => 500)));
  }

  //query
  if($result = $mysqli->query('SELECT * FROM ebay_recommends WHERE deleted = false')){
    $recommends = [];
    while($row = $result->fetch_array(MYSQLI_ASSOC)) {
      $recommends[] = $row;
    }
    $recommend_html = "";
    if(count($myArray) > 0){
      $recommend_html = "<!-- Recommend -->
<div class='container'>
<div class='row'>
<h1>More Beautiful Bettas</h1>
</div>
<div class='recoomend-outer'>
<div class='row'>";
      foreach($recommends as $recommend){
        if($recommend['sku'] == '2D15'){
          $recommend_html = $recommend_html . "<div class='col-6 col-md-4 recoomend'><a target='_blank' href='".$recommend['url']."'><img alt='' class='img-responsive' width='100%'
src='https://zeroaqua.com/wp-content/uploads/2022/02/2D16-1.jpg' /></a></div>";
        }else{
          $recommend_html = $recommend_html . "<div class='col-6 col-md-4 recoomend'><a target='_blank' href='".$recommend['url']."'><img alt='' class='img-responsive' width='100%'
src='https://cms.zeroaqua.com/post/files/".$recommend['sku']."-1.jpg' /></a></div>";
        }

      }
      $recommend_html = $recommend_html . "</div>
</div>
</div>";
    }else{
      $recommend_html = "";
    }
  }else{
    header('HTTP/1.1 500 Internal Server Error');
    die(json_encode(array('message' => 'Error getting record: ' . $mysqli->error, 'code' => 500)));
  }

  $result->free_result();
  $mysqli->close();

  Analog::log('departing_date.'.var_export($departure_date, true));

  $output_headers = [
    '*Action(SiteID=US|Country=US|Currency=USD|Version=1193)',
    'Custom label (SKU)',
    'Category ID',
    'Title',
    'Relationship',
    'Relationship details',
    'P:UPC',
    'P:ISBN',
    'P:EAN',
    'P:EPID',
    'Start price',
    'Quantity',
    'Item photo URL',
    'Condition ID',
    'Description',
    'Format',
    'Duration',
    'Buy It Now price',
    'Paypal accepted',
    'Paypal email address',
    'Immediate pay required',
    'Payment instructions',
    'Location',
    'Shipping service 1 option',
    'Shipping service 1 cost',
    'Shipping service 1 priority',
    'Shipping service 2 option',
    'Shipping service 2 cost',
    'Shipping service 2 priority',
    'Max dispatch time',
    'Returns accepted option',
    'Returns within option',
    'Refund option',
    'Return shipping cost paid by',
    'Shipping profile name',
    'Return profile name',
    'Payment profile name',
    'TakeBackPolicyID',
    'ProductCompliancePolicyID',
    'C:Species',
    'C:Gender',
    'C:Water Type',
    'C:Water Temperature',
  ];

  if(is_array($_FILES)){
    Analog::log('files.'.var_export($_FILES, true));
    Analog::log('files_length.'.var_export(count($_FILES), true));

    $input_headers = [
      'SKU',
      'Name',
      'Meta: ebay_us_buy_price',
      'Stock',
      'Description',
      'Images'
    ];

    $output_pos = [
      1,
      3,
      10,
      11,
      14,
      12
    ];

    $header_pos = array_fill(0, count($input_headers), -1);

    $description_headers = [
      'SKU',
      'Name',
      'Images',
      'Meta: color',
      'Meta: ebay_us_start_price',
      'Meta: ebay_us_buy_price',
      'Meta: type',
      'Meta: gender',
      'Meta: age'
    ];

    $description_pos = array_fill(0, count($description_headers), -1);

    foreach($_FILES as $file){
      Analog::log('file.'.var_export($file['name'], true));

      $reader = fopen($file['tmp_name'], "r");

      $line_count = 0;
      $product_count = 0;
      $output_product_datas = [];
      while(!feof($reader)){
        $line = fgetcsv($reader);
        if($line_count == 0){ //first line(header)
          for($i = 0; $i < count($line); $i++){
            for($j = 0; $j < count($input_headers); $j++){
              if($line[$i] == $input_headers[$j]){
                $header_pos[$j] = $i;
              }
            }

            for($j = 0; $j < count($description_headers); $j++){
              if($line[$i] == $description_headers[$j]){
                $description_pos[$j] = $i;
              }
            }
          }
          Analog::log('header_pos.'.var_export($header_pos, true));
        }else{
          if(is_array($line)){
            if(count($line) > max($header_pos)){ //each product
              $product_data = array_fill(0, count($output_headers), '');
              $description_data = array_fill(0, count($description_headers), '');

              for($i = 0; $i < count($header_pos); $i++){
                if($header_pos[$i] != -1){
                  $product_data[$output_pos[$i]] = $line[$header_pos[$i]];
                  if($input_headers[$i] == 'Images'){
                    $product_data[$output_pos[$i]] = str_replace(', ', '|',$product_data[$output_pos[$i]]);
                  }
                }
              }

              for($i = 0; $i < count($description_pos); $i++){
                if($description_pos[$i] != -1){
                  if($description_headers[$i] == 'Images'){
                    $images = explode(', ', $line[$description_pos[$i]]);
                    foreach($images as $image){
                      $description_data[$i] = $description_data[$i]."<img alt='' class='img-responsive' width='100%' src='".$image."' />";
                    }
                  }elseif($description_headers[$i] == 'Meta: color'){
                    $colors = explode(',', $line[$description_pos[$i]]);
                    foreach($colors as $color){
                      $description_data[$i] = $description_data[$i]."<input type='radio' name='variants' id='".$color."'><label for='".$color."'><span class='var'>".$color."</span></label>";
                    }
                  }else{
                    $description_data[$i] = $line[$description_pos[$i]];
                  }
                }
              }

              //default
              $product_data[0] = 'Add';
              $product_data[2] = '168141';
              $product_data[13] = '1000';
              $product_data[15] = 'FixedPrice';
              $product_data[16] = '10';
              $product_data[22] = 'Bangkok, Thailand';
              $product_data[34] = 'Flat:UPS Ground($39.00),1 business day';
              $product_data[35] = '100% DOA Money Back Guarantee';
              $product_data[36] = 'PayPal:Immediate pay';

              //description
              $description = "
<link rel='stylesheet' href='https://maxcdn.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css'>
<link rel='stylesheet' href='https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css'>
<link rel='stylesheet' href='https://cms.zeroaqua.com/post/ebay_data/ebay.css'>

<!-- Banner -->
<div class='container-fluid slider-bg hidden-xs'>
<div class='container'>
<div class='row'>
<div class='slider'>
<img alt='' class='img-responsive' src='https://cms.zeroaqua.com/post/ebay_data/img/ebay-top-banner.jpg' />
</div>
</div>
</div>
</div>
<!-- Banner -->

<!-- Content -->
<div class='container-fluid'>
<div class='container'>

<!-- Article -->
<div class='row article'>
<h1>".$description_data[1]."</h1>
<!-- Artikelbilder -->
<div class='col-md-6 artpic'>

<div class='articlepics galerie'>
<video controls onloadstart='this.volume=0.2' width='100%'>
<source src='https://cms.zeroaqua.com/post/files/".$description_data[0].".mp4' type='video/mp4'>
Sorry, your browser does not support embedded videos.
</video>
</div>
</div>
<!-- Artikelbilder -->

<!-- Artikelbeschreibung -->
<div class='col-md-6 desc'>
<!-- Kaufen-Box -->
<div class='row buynowbox'>
<!-- Preis -->
<div class='col-6 price'>
<h3>$".$description_data[4]."</h3>
<!--<small>Buy Now Price: $".$description_data[5]."</small>-->
<!-- Preis -->
</div>
<!-- Buttons -->
<div class='col-6'>
<!-- SOFORT KAUFEN -->
<p class='buy-now-price'><small></small></p>
<!-- BEOBACHTEN -->
<!-- Buy now price -->

</div>
<!-- Buttons -->
</div>
<!-- Kaufen-Box -->

<ul class='row'>
<li class='col-md-6'>
<strong>Type:</strong>
".$description_data[6]."
</li>
<li class='col-md-6'>
<strong>Gender:</strong>
".$description_data[7]."
</li>
<li class='col-md-6'>
<strong>Origin:</strong>
Thailand
</li>
<li class='col-md-6'>
<strong>Age:</strong>
".$description_data[8]." months
</li>
<li class='col-md-6'>
<strong>SKU:</strong>
".$description_data[0]."
</li>
</ul>

<!-- Variants -->
<div class='variants'>
<h3>Color</h3>
".$description_data[3]."
</div>
</div>
<!-- Artikelbeschreibung -->
</div>
<!-- Article -->
</div>
</div>
<!-- Content -->


<!-- Content -->
<div class='container-fluid'>
<div class='container'>
<!-- Article -->
<div class='row '>
<div class='col-md-6 guarantee'>
<p class='guarantee-heart'>🧡</p>
<p class='guarantee-text'>100% D.O.A. Money Back Guarantee!<br>Get Your Money Back if Betta Arrives Dead / Bad
Conditions</p>
</div>
</div>
</div>
</div>



<div class='container-fluid'>
<div class='container'>
<!-- Article -->
<div class='row'>
<div class='col-md-6 conetnt-photos'>
".$description_data[2]."
<img alt='' class='img-responsive mt-40' width='100%' src='https://cms.zeroaqua.com/post/ebay_data/img/fully-managed-shipping.jpg' />
</div>
</div>
</div>
</div>

<!-- Content -->
<div class='container-fluid'>
<div class='container'>
<!-- Artikelbeschreibung -->
<div class='col-md-6 desc center'>
<!-- Kaufen-Box -->
<div class='row buynowbox'>
<!-- Preis -->
<div class='col-6 price'>
<h3>$".$description_data[4]."</h3>
<small>Buy Now Price: $".$description_data[5]."</small>
<!-- Preis -->
</div>
<!-- Buttons -->
<div class='col-6'>
<!-- SOFORT KAUFEN -->
<p class='buy-now-price'><div style='height:1px'></div><small></small></p>
<!-- BEOBACHTEN -->
<!-- Buy now price -->

</div>
<!-- Buttons -->
</div>
<!-- Kaufen-Box -->
</div>
</div>
</div>

<!-- Content -->
<div class='container-fluid'>
<div class='container'>
<!-- Article -->
<div class='row '>
<div class='col-md-6 mt-40 guarantee'>
<p class='guarantee-heart'>✈️</p>
<p class='guarantee-text'>Next Shipping Date: <span id='shipping-us'>".$departure_date."</span> (GMT+7)</p>
</div>
</div>
</div>
</div>


<div class='container-fluid'>
<div class='container'>
<!-- Article -->
<div class='row'>
<div class='col-md-8 center'>
<img alt='' class='img-responsive mt-40' width='100%' src='https://cms.zeroaqua.com/post/ebay_data/img/mello-zeroaqua.jpg' />
</div>
</div>
<div class='row'>
<div class='col-md-6 center'>
<h2 class='mt-40 mb-20'>Note to next owner</h2>
<p>Hi! I’m Mello, looking after Bettas at Zeroaqua!</p>
<p>Betta is fed with protein rich pallets 2 times/day, 5 times/week (eats well!) and bloodworms 1 time/week as
snack. No food on Sunday to improve intestinal environment. (No chemicals used to boost the growth)</p>
<p>Water is changed daily, saline concentration is kept to 0.1% ~ 0.2% and organic almond (umbrella) leaf is
added to help betta stay as comfortable as possible.</p>
<p>We open up the blinds once a day for about 10~15 min to let Bettas flare up and exercise.</p>
<p>Please feel free to contact us if you have any pre-purcahse questions!</p>
<p>*10% of all proceeds will be donated to Wildlife Trafficking Alliance, We appreciate your support.</p>
</div>
</div>
</div>
</div>
".$recommend_html;
              $description = str_replace(array("\r", "\n"), '', $description);
              $product_data[14] = $description;

              Analog::log('product_data.'.var_export($product_data, true));
              array_push($output_product_datas, $product_data);
            }
            $product_count++;
          }
        }
        $line_count++;
      }
      Analog::log('line_count.'.var_export($line_count, true));
      Analog::log('product_count.'.var_export($product_count, true));
      fclose($reader);
    }
  }

  header('Content-Type: application/csv');
  header('Content-Disposition: attachment; filename="ebay.csv";');

  // open the "output" stream
  // see http://www.php.net/manual/en/wrappers.php.php#refsect2-wrappers.php-unknown-unknown-unknown-descriptioq
  $f = fopen('php://output', 'w');

  fputcsv($f, $output_headers, ',');
  foreach($output_product_datas as $output_product_data){
    fputcsv($f, $output_product_data, ',');
  }
});

$router->map( "GET", "/ebay/recommend", function() {
  Analog::log('GET /ebay/recommend');
  require __DIR__ . '/ebay-recommend.html';
});

$router->map( "GET", "/ebay/recommend/", function() {
  Analog::log('GET /ebay/recommend/');
  require __DIR__ . '/ebay-recommend.html';
});

$router->map( 'GET', '/ebay/recommend/list/', function() {
  Analog::log('/ebay/recommend/list/');
  
  $mysqli = new mysqli('localhost', 'grape', 'f|yE4g|eSf|y', 'zeroaqua');
  
  if($mysqli->connect_errno){
    header('HTTP/1.1 500 Internal Server Error');
    die(json_encode(array('message' => 'Failed to connect to MySQL: ' . $mysqli->connect_error, 'code' => 500)));
  }

  //query
  if($result = $mysqli->query('SELECT * FROM ebay_recommends WHERE deleted = false')){
    $myArray = [];
    while($row = $result->fetch_array(MYSQLI_ASSOC)) {
      $myArray[] = $row;
    }
    echo json_encode($myArray);
  }else{
    header('HTTP/1.1 500 Internal Server Error');
    die(json_encode(array('message' => 'Error getting record: ' . $mysqli->error, 'code' => 500)));
  }

  $result->free_result();
  $mysqli->close();
});

$router->map( 'POST', '/ebay/recommend/add/', function() {
  Analog::log('/ebay/recommend/add/');

  if(isset($_POST['sku'])){
    $sku = $_POST['sku'];
  }else{
    header('HTTP/1.1 500 Internal Server Error');
    die(json_encode(array('message' => 'sku is not set', 'code' => 500)));
  }

  if(isset($_POST['url'])){
    $url = $_POST['url'];
  }else{
    header('HTTP/1.1 500 Internal Server Error');
    die(json_encode(array('message' => 'url is not set', 'code' => 500)));
  }

  Analog::log('params. sku='.$sku.',url='.$url);

  $mysqli = new mysqli('localhost', 'grape', 'f|yE4g|eSf|y', 'zeroaqua');
  
  if($mysqli->connect_errno){
    header('HTTP/1.1 500 Internal Server Error');
    die(json_encode(array('message' => 'Failed to connect to MySQL: ' . $mysqli->connect_error, 'code' => 500)));
  }

  //insert
  if($result = $mysqli->query('INSERT INTO ebay_recommends (sku,url) VALUE ("'.$sku.'","'.$url.'")')){
    echo json_encode(array('message' => 'success'));
  }else{
    header('HTTP/1.1 500 Internal Server Error');
    die(json_encode(array('message' => 'Error updating record: ' . $mysqli->error, 'code' => 500)));
  }

  $mysqli->close();
});

$router->map( 'POST', '/ebay/recommend/delete/', function() {
  Analog::log('/ebay/recommend/delete/');

  if(isset($_POST['id'])){
    $id = $_POST['id'];
  }else{
    header('HTTP/1.1 500 Internal Server Error');
    die(json_encode(array('message' => 'id is not set', 'code' => 500)));
  }

  Analog::log('id.'. $id);

  $mysqli = new mysqli('localhost', 'grape', 'f|yE4g|eSf|y', 'zeroaqua');
  
  if($mysqli->connect_errno){
    header('HTTP/1.1 500 Internal Server Error');
    die(json_encode(array('message' => 'Failed to connect to MySQL: ' . $mysqli->connect_error, 'code' => 500)));
  }

  //update deleted
  if($result = $mysqli->query('UPDATE ebay_recommends SET deleted = true WHERE id = ' . $id)){
    echo json_encode(array('message' => 'success'));
  }else{
    header('HTTP/1.1 500 Internal Server Error');
    die(json_encode(array('message' => 'Error updating record: ' . $mysqli->error, 'code' => 500)));
  }

  //$result->free_result();
  $mysqli->close();
});

$router->map( "GET", "/youtube", function() {
  Analog::log('GET /youtube');
  require __DIR__ . '/youtube-queue.html';
});

$router->map( "GET", "/youtube/", function() {
  Analog::log('GET /youtube/');
  require __DIR__ . '/youtube-queue.html';
});

$router->map( "GET", "/login", function() {
  Analog::log('GET /login');
  require __DIR__ . '/login.php';
});

$router->map( "GET", "/login/", function() {
  Analog::log('GET /login/');
  require __DIR__ . '/login.php';
});

$router->map( "POST", "/login", function() {
  Analog::log('POST /login');
  require __DIR__ . '/login.php';
});

$router->map( "POST", "/login/", function() {
  Analog::log('POST /login/');
  require __DIR__ . '/login.php';
});

$router->map( "GET", "/logout", function() {
  Analog::log('GET /logout');
  require __DIR__ . '/logout.php';
});

$router->map( "GET", "/logout/", function() {
  Analog::log('GET /logout/');
  require __DIR__ . '/logout.php';
});

$router->map( "GET", "/user-add", function() {
  Analog::log('GET /user-add');
  require __DIR__ . '/user-add.php';
});

$router->map( "GET", "/user-add/", function() {
  Analog::log('GET /user-add/');
  require __DIR__ . '/user-add.php';
});

$router->map( "POST", "/user-add", function() {
  Analog::log('POST /user-add');
  require __DIR__ . '/user-add.php';
});

$router->map( "POST", "/user-add/", function() {
  Analog::log('POST /user-add/');
  require __DIR__ . '/user-add.php';
});

$router->map( 'GET', '/youtube/list/', function() {
  Analog::log('/youtube/list/');
  
  $mysqli = new mysqli('localhost', 'grape', 'f|yE4g|eSf|y', 'zeroaqua');
  
  if($mysqli->connect_errno){
    header('HTTP/1.1 500 Internal Server Error');
    die(json_encode(array('message' => 'Failed to connect to MySQL: ' . $mysqli->connect_error, 'code' => 500)));
  }

  //query
  if($result = $mysqli->query('SELECT * FROM youtube_queues WHERE posted = false AND deleted = false')){
    $myArray = [];
    while($row = $result->fetch_array(MYSQLI_ASSOC)) {
      $myArray[] = $row;
    }
    echo json_encode($myArray);
  }else{
    header('HTTP/1.1 500 Internal Server Error');
    die(json_encode(array('message' => 'Error getting record: ' . $mysqli->error, 'code' => 500)));
  }

  $result->free_result();
  $mysqli->close();
});

$router->map( 'POST', '/youtube/delete/', function() {
  Analog::log('/youtube/delete/');

  if(isset($_POST['id'])){
    $id = $_POST['id'];
  }else{
    header('HTTP/1.1 500 Internal Server Error');
    die(json_encode(array('message' => 'id is not set', 'code' => 500)));
  }

  Analog::log('id.'. $id);

  $mysqli = new mysqli('localhost', 'grape', 'f|yE4g|eSf|y', 'zeroaqua');
  
  if($mysqli->connect_errno){
    header('HTTP/1.1 500 Internal Server Error');
    die(json_encode(array('message' => 'Failed to connect to MySQL: ' . $mysqli->connect_error, 'code' => 500)));
  }

  //update deleted
  if($result = $mysqli->query('UPDATE youtube_queues SET deleted = true WHERE id = ' . $id)){
    echo json_encode(array('message' => 'success'));
  }else{
    header('HTTP/1.1 500 Internal Server Error');
    die(json_encode(array('message' => 'Error updating record: ' . $mysqli->error, 'code' => 500)));
  }

  //$result->free_result();
  $mysqli->close();
});

$router->map( "POST", "/wordpress", function() {
  if(!(file_exists('./log/post_worker_'.(new DateTime())->format('Y-m-d').'.log'))){
    $log_file = fopen('./log/post_worker_'.(new DateTime())->format('Y-m-d').'.log', "w") or die("can't open file");
    fclose($log_file);
  }

  Analog::log('POST /wordpress');
  Analog::log('post_params.'.var_export($_POST, true));
  Analog::log('post_files.'.var_export($_FILES, true));

  \Cloudinary::config([
    "cloud_name" => "dc66tq50m",
    "api_key" => "978426257176292",
    "api_secret" => "oIcYcoVstSp2IgK1FEzJ-hT7Ak8",
    "secure" => true
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

  $tiktok_username = '@buybettaonline';
  $skus = [];
  $meta_datas = [];
  $tags = [];
  $premium = false;
  $penny = false;
  $price = 0;
  $buy_now_price = '';
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

  $color_str_jp = '';
  $color_str_comma_jp = '';

  $tiktok_url = '';

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
      case 'price':
        $price = $value;
        break;
      case 'buy_now_price':
        $buy_now_price = $value;
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
          $color_str_jp = $color_str_jp . ' ブラック';
          $color_str_comma_jp = $color_str_comma_jp . 'ブラック,';
          array_push($tags, ['id' => 41]);
        }
        break;
      case 'color-white':
        if($value = 'on'){
          $color_white = true;
          $color_str = $color_str . ' White';
          $color_str_comma = $color_str_comma . 'White,';
          $color_str_jp = $color_str_jp . ' ホワイト';
          $color_str_comma_jp = $color_str_comma_jp . 'ホワイト,';
          array_push($tags, ['id' => 39]);
        }
        break;
      case 'color-silver':
        if($value = 'on'){
          $color_silver = true;
          $color_str = $color_str . ' Silver';
          $color_str_comma = $color_str_comma . 'Silver,';
          $color_str_jp = $color_str_jp . ' シルバー';
          $color_str_comma_jp = $color_str_comma_jp . 'シルバー,';
          array_push($tags, ['id' => 60]);
        }
        break;
      case 'color-gold':
        if($value = 'on'){
          $color_gold = true;
          $color_str = $color_str . ' Gold';
          $color_str_comma = $color_str_comma . 'Gold,';
          $color_str_jp = $color_str_jp . ' ゴールド';
          $color_str_comma_jp = $color_str_comma_jp . 'ゴールド,';
          array_push($tags, ['id' => 61]);
        }
        break;
      case 'color-copper':
        if($value = 'on'){
          $color_copper = true;
          $color_str = $color_str . ' Copper';
          $color_str_comma = $color_str_comma . 'Copper,';
          $color_str_jp = $color_str_jp . ' カッパー';
          $color_str_comma_jp = $color_str_comma_jp . 'カッパー,';
          array_push($tags, ['id' => 62]);
        }
        break;
      case 'color-yellow':
        if($value = 'on'){
          $color_yellow = true;
          $color_str = $color_str . ' Yellow';
          $color_str_comma = $color_str_comma . 'Yellow,';
          $color_str_jp = $color_str_jp . ' イエロー';
          $color_str_comma_jp = $color_str_comma_jp . 'イエロー,';
          array_push($tags, ['id' => 63]);
        }
        break;
      case 'color-orange':
        if($value = 'on'){
          $color_orange = true;
          $color_str = $color_str . ' Orange';
          $color_str_comma = $color_str_comma . 'Orange,';
          $color_str_jp = $color_str_jp . ' オレンジ';
          $color_str_comma_jp = $color_str_comma_jp . 'オレンジ,';
          array_push($tags, ['id' => 40]);
        }
        break;
      case 'color-red':
        if($value = 'on'){
          $color_red = true;
          $color_str = $color_str . ' Red';
          $color_str_comma = $color_str_comma . 'Red,';
          $color_str_jp = $color_str_jp . ' レッド';
          $color_str_comma_jp = $color_str_comma_jp . 'レッド,';
          array_push($tags, ['id' => 38]);
        }
        break;
      case 'color-purple':
        if($value = 'on'){
          $color_purple = true;
          $color_str = $color_str . ' Purple';
          $color_str_comma = $color_str_comma . 'Purple,';
          $color_str_jp = $color_str_jp . ' パープル';
          $color_str_comma_jp = $color_str_comma_jp . 'パープル,';
          array_push($tags, ['id' => 64]);
        }
        break;
      case 'color-blue':
        if($value = 'on'){
          $color_blue = true;
          $color_str = $color_str . ' Blue';
          $color_str_comma = $color_str_comma . 'Blue,';
          $color_str_jp = $color_str_jp . ' ブルー';
          $color_str_comma_jp = $color_str_comma_jp . 'ブルー,';
          array_push($tags, ['id' => 65]);
        }
        break;
      case 'color-green':
        if($value = 'on'){
          $color_green = true;
          $color_str = $color_str . ' Green';
          $color_str_comma = $color_str_comma . 'Green,';
          $color_str_jp = $color_str_jp . ' グリーン';
          $color_str_comma_jp = $color_str_comma_jp . 'グリーン,';
          array_push($tags, ['id' => 66]);
        }
        break;
      case 'color-turquoise':
        if($value = 'on'){
          $color_turquoise = true;
          $color_str = $color_str . ' Turquoise';
          $color_str_comma = $color_str_comma . 'Turquoise,';
          $color_str_jp = $color_str_jp . ' ターコイズ';
          $color_str_comma_jp = $color_str_comma_jp . 'ターコイズ,';
          array_push($tags, ['id' => 67]);
        }
        break;
      case 'tiktok_url':
        $tiktok_url = $value;
        break;
      case 'post_to_youtube':
        $post_to_youtube = $value;
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
  $sale_price_thb = 0;
  $ebay_us_start_price = 0;
  $ebay_us_buy_price = 0;
  $yahoo_auction_start_price = 0;
  $yahoo_auction_buy_price = 0;
  $yahoo_shopping_price = 0;
  $shopee_price = 0;

  if($price == 1980){
    $regular_price = 20;
    $sale_price = 15;
    $regular_price_jpy = 2500;
    $sale_price_jpy = 1980;
    $ebay_us_buy_price = 20;
    $yahoo_auction_start_price = 1980;
    $yahoo_auction_buy_price = 2500;
    $yahoo_shopping_price = 2500;
  }
  if($price == 2500){
    $regular_price = 25;
    $sale_price = 20;
    $regular_price_jpy = 2980;
    $sale_price_jpy = 2500;
    $ebay_us_buy_price = 25;
    $yahoo_auction_start_price = 2500;
    $yahoo_auction_buy_price = 2980;
    $yahoo_shopping_price = 2980;
  }
  if($price == 3980){
    $regular_price = 40;
    $sale_price = 30;
    $regular_price_jpy = 4980;
    $sale_price_jpy = 3980;
    $ebay_us_buy_price = 40;
    $yahoo_auction_start_price = 3980;
    $yahoo_auction_buy_price = 4980;
    $yahoo_shopping_price = 4980;
  }
  if($price == 7980){
    $regular_price = 80;
    $sale_price = 60;
    $regular_price_jpy = 10000;
    $sale_price_jpy = 7980;
    $ebay_us_buy_price = 80;
    $yahoo_auction_start_price = 7980;
    $yahoo_auction_buy_price = 10000;
    $yahoo_shopping_price = 10000;
  }
  if($price == 15000){
    $regular_price = 150;
    $sale_price = 120;
    $regular_price_jpy = 18000;
    $sale_price_jpy = 15000;
    $ebay_us_buy_price = 150;
    $yahoo_auction_start_price = 15000;
    $yahoo_auction_buy_price = 18000;
    $yahoo_shopping_price = 18000;
  }
  if($price == 30000){
    $regular_price = 290;
    $sale_price = 240;
    $regular_price_jpy = 35000;
    $sale_price_jpy = 30000;
    $ebay_us_buy_price = 290;
    $yahoo_auction_start_price = 30000;
    $yahoo_auction_buy_price = 35000;
    $yahoo_shopping_price = 35000;
  }
  if($buy_now_price != ''){
    $yahoo_auction_buy_price = (int)$buy_now_price;
    $regular_price_jpy = (int)$buy_now_price;
  }

  /*if($price == 2980){
    $regular_price = 16;
    $sale_price = 12;
    $regular_price_jpy = 2000;
    $sale_price_jpy = 1480;
    $regular_price_thb = 550;
    $sale_price_thb = 390;
    $ebay_us_buy_price = 16;
    $yahoo_auction_start_price = 2980;
    $yahoo_auction_buy_price = 3500;
    $yahoo_shopping_price = 3500;
  }
  
  if($price == 4980){
    $regular_price = 32;
    $sale_price = 28;
    $regular_price_jpy = 4000;
    $sale_price_jpy = 3480;
    $regular_price_thb = 1050;
    $sale_price_thb = 920;
    $ebay_us_buy_price = 32;
    $yahoo_auction_start_price = 4980;
    $yahoo_auction_buy_price = 5500;
    $yahoo_shopping_price = 5500;
  }
  
  if($price == 7980){
    $regular_price = 66;
    $sale_price = 50;
    $regular_price_jpy = 8500;
    $sale_price_jpy = 6480;
    $regular_price_thb = 2250;
    $sale_price_thb = 1700;
    $ebay_us_buy_price = 66;
    $yahoo_auction_start_price = 7980;
    $yahoo_auction_buy_price = 10000;
    $yahoo_shopping_price = 10000;
  }
  
  if($price == 15000){
    $regular_price = 120;
    $regular_price_jpy = 15000;
    $regular_price_thb = 3950;
    $ebay_us_buy_price = 120;
    $yahoo_auction_buy_price = 15000;
    $yahoo_shopping_price = 15000;
  }
  
  if($price == 30000){
    $regular_price = 250;
    $regular_price_jpy = 30000;
    $regular_price_thb = 7920;
    $ebay_us_buy_price = 250;
    $yahoo_auction_buy_price = 30000;
    $yahoo_shopping_price = 30000;
  }*/

  /*if($penny){
    if(!$premium){ //Premium off + Penny Start
      $regular_price = 25;
      $sale_price = 19.95;
      $regular_price_jpy = 2500;
      $sale_price_jpy = 1980;
      $regular_price_thb = 490;
      $ebay_us_start_price = 0.01;
      $yahoo_auction_start_price = 1;
      $shopee_price = 249;
    }else if($premium){ //Premium on + Penny Start
      $regular_price = 350;
      $regular_price_jpy = 35000;
      $regular_price_thb = 12000;
      $ebay_us_start_price = 0.01;
      $yahoo_auction_start_price = 1;
      $shopee_price = 750;
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
      $shopee_price = 249;
    }else if($premium && ($gender == 1 || $gender == 2)){ //Premium on (male or female)
      $regular_price = 55;
      $sale_price = 49.95;
      $regular_price_jpy = 5500;
      $sale_price_jpy = 4980;
      $regular_price_thb = 1450;
      $ebay_us_start_price = 49.95;
      $ebay_us_buy_price = 65;
      $yahoo_auction_start_price = 1980;
      $yahoo_auction_buy_price = 5500;
      $yahoo_shopping_price = 4980;
      $shopee_price = 750;
    }else if(!$premium && $gender == 3){ //Premium off (pair)
      $regular_price = 110;
      $sale_price = 99.95;
      $regular_price_jpy = 11000;
      $sale_price_jpy = 9980;
      $regular_price_thb = 2450;
      $ebay_us_start_price = 29.95;
      $ebay_us_buy_price = 99.95;
      $yahoo_auction_start_price = 2980;
      $yahoo_auction_buy_price = 11000;
      $yahoo_shopping_price = 9980;
      $shopee_price = 249;
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
      $shopee_price = 750;
    }
  }*/

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
  if($shopee_price > 0){
    array_push($meta_datas,[
      'key' => 'shopee_price',
      'value' => $shopee_price
    ]);
  }

  $type_str = '';
  switch($type){
    case 15:
      $type_str = ' Plakat';
      break;
    case 49:
      $type_str = ' Half Moon';
      break;
    case 50:
      $type_str = ' Crowntail';
      break;
    case 51:
      $type_str = ' Dumbo';
      break;
    case 78:
      $type_str = ' Wild Alien';
      break;
    case 79:
      $type_str = ' Dumbo Half Moon';
      break;
    case 80:
      $type_str = ' Double Tail';
      break;
    case 123:
      $type_str = ' Isan';
      break;
  }
  array_push($meta_datas,[
    'key' => 'type',
    'value' => ltrim($type_str, ' ')
  ]);

  $nickname_str = '';
  switch($nickname){
    case 54:
      $nickname_str = ' Nemo';
      break;
    case 55:
      $nickname_str = ' Nemo Galaxy';
      break;
    case 73:
        $nickname_str = ' Dragon';
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

  $images = [];
  $videos = [];
  $youtube_description_names = [];
  $youtube_title_names = [];
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
    /*$slug = 'Aquarium-Live-Betta-Fish';
    if($type_str != ''){
      $slug = $slug.'-'.ltrim($type_str, ' ');
    }
    if($nickname_str != ''){
      $slug = $slug.'-'.ltrim($nickname_str, ' ');
    }
    if($gender_str != ''){
      $slug = $slug.'-'.ltrim($gender_str, ' ');
    }
    if($color_str != ''){
      $slug = $slug.'-'.ltrim($color_str, ' ');
    }
    $slug = $slug.'-'.$sku_main;*/

    if(preg_match('/^([a-zA-Z0-9\s_\\.\-:])+(.mp4|.mov)$/', strtolower($file['name']))){
      Analog::log('upload_video.'.var_export($file, true));

      /*\Cloudinary::config([
        "cloud_name" => "dc66tq50m",
        "api_key" => "978426257176292",
        "api_secret" => "oIcYcoVstSp2IgK1FEzJ-hT7Ak8",
        "secure" => true
      ]);

      //$res = \Cloudinary\Uploader::upload_large($file['tmp_name'], ['resource_type' => 'video', 'public_id' => $sku_main, 'chunk_size' => 6000000]);
      $res = \Cloudinary\Uploader::upload($file['tmp_name'], ['resource_type' => 'video', 'public_id' => $sku_main]);
      Analog::log('cloudinary_video_res.'.var_export($res, true));*/

      //save file to vps
      $info = pathinfo($file['name']);
      $ext = $info['extension']; // get the extension of the file
      $newname = $sku_main.'.'.$ext;
      $target = '/var/www/home/post/files/'.$newname;
      move_uploaded_file($file['tmp_name'], $target);
      $url = 'http://156.67.219.175/post/files/'.$newname;

      $color_str_youtube = '';
      if($color_str != ''){
        $color_str_youtube = $color_str.' Color';
      }
      $youtube_description_name = ltrim($type_str,' ').' Betta'.$nickname_str.' Fish'.$gender_str.$color_str_youtube;
      $youtube_title_name = '☆SALE☆ Betta'.$type_str.$nickname_str.' #shorts #aquarium #fish #ベタ';

      array_push($videos, $url);
      array_push($youtube_description_names, $youtube_description_name);
      array_push($youtube_title_names, $youtube_title_name);

      /*// Define the $video object, which will be uploaded as the request body.
      $video = new Google_Service_YouTube_Video();

      // Add 'snippet' object to the $video object.
      $videoSnippet = new Google_Service_YouTube_VideoSnippet();
      $videoSnippet->setCategoryId('15');
      $videoSnippet->setTitle('☆SALE☆ Betta Fish #shorts #aquarium #ベタ No:'.$sku_main);
      $color_str_youtube = '';
      if($color_str != ''){
        $color_str_youtube = $color_str.' Color';
      }
      $videoSnippet->setDescription(ltrim($type_str,' ').' Betta'.$nickname_str.' Fish'.$gender_str.$color_str_youtube.'

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

Email: info@zeroaqua.com

Terms:
https://zeroaqua.com/Terms-of-Use/

Privacy Policy:
https://zeroaqua.com/privacy-policy/');
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

      /*\Cloudinary::config([
        "cloud_name" => "domr7bm4t",
        "api_key" => "393941319363972",
        "api_secret" => "lGJYpU83kOW6Eg3X8_L6h3E77gM",
        "secure" => true
      ]);

      //$res = \Cloudinary\Uploader::upload_large($file['tmp_name'], ['resource_type' => 'image', 'public_id' => $sku_main.'-'.$img_count, 'width' => 1080, 'height' => 1080, 'crop' => 'fit', 'chunk_size' => 6000000]);
      $res = \Cloudinary\Uploader::upload($file['tmp_name'], ['resource_type' => 'image', 'public_id' => $sku_main.'-'.$img_count, 'width' => 1080, 'height' => 1080, 'crop' => 'fit']);
      Analog::log('cloudinary_img_res.'.var_export($res, true));*/

      //save file to vps
      $info = pathinfo($file['name']);
      $ext = $info['extension']; // get the extension of the file
      $newname = $sku_main.'-'.$img_count.'.'.$ext;
      $target = '/var/www/home/post/files/'.$newname;
      move_uploaded_file($file['tmp_name'], $target);
      $url = 'http://156.67.219.175/post/files/'.$newname;

      array_push($images, ['src' => $url]);
    }
  }

  /*if($vid_count > 0){
    $description = $description . '*No color enhancement filters applied to the footage, recorded under white led lights.';
  }*/

  if($tiktok_url != ''){
    if(preg_match_all('/\/video\/([\d]*)/', $tiktok_url, $out)){
      if(is_array($out)){
        if(count($out) > 1){
          $tiktok_video_id = $out[1][0];
          $description = $description . '<blockquote class="tiktok-embed" cite="https://www.tiktok.com/'.$tiktok_username.'/video/'.$tiktok_video_id.'" data-video-id="'.$tiktok_video_id.'" style="max-width: 605px;min-width: 325px;"><section></section></blockquote>';
        }
      }
    }else{
      $headers = get_headers($tiktok_url, 1);
      $full_url = $headers['Location'];
      if(is_array($full_url)){
        $full_url = $full_url[0];
      }
      if(preg_match_all('/\/video\/([\d]*)/', $full_url, $out)){
        if(is_array($out)){
          if(count($out) > 1){
            $tiktok_video_id = $out[1][0];
            $description = $description . '<blockquote class="tiktok-embed" cite="https://www.tiktok.com/'.$tiktok_username.'/video/'.$tiktok_video_id.'" data-video-id="'.$tiktok_video_id.'" style="max-width: 605px;min-width: 325px;"><section></section></blockquote>';
          }
        }
      }
    }
  }

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
    /*$sku_bool = false;
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
    }*/

    $slug = 'Aquarium-Live-Betta-Fish';
    if($type_str != ''){
      $slug = $slug.'-'.ltrim($type_str, ' ');
    }
    if($nickname_str != ''){
      $slug = $slug.'-'.ltrim($nickname_str, ' ');
    }
    if($gender_str != ''){
      $slug = $slug.'-'.ltrim($gender_str, ' ');
    }
    if($color_str != ''){
      $slug = $slug.'-'.ltrim($color_str, ' ');
    }
    $slug = $slug;
    $slug = str_replace(' ', '-', $slug);

    $data = [
      'name' => 'Betta Aquarium Fish' . $type_str . $nickname_str . $gender_str . $color_str,
      'sku' => $sku,
      'slug' => $slug,
      'type' => 'simple',
      'description' => $description,
      'short_description' => '<div class="za-short-description"><i class="fas fa-heart fa-lg text-heart"></i> 100% No-Risk Money Back Guarantee! You are fully protected by our D.O.A. 100% Money Back Guarantee.</div>',
      'regular_price' => (string)$regular_price,
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

    if($sale_price > 0){
      $data['sale_price'] = (string)$sale_price;
    }

    array_push($products, $data);
    Analog::log('woocommerce_params.'.var_export($data, true));

    /*$res = $woocommerce->post('products' ,$data);
    Analog::log('woocommerce_res.'.var_export($res, true));*/
  }

  $queue_data = [
    'videos' => $videos,
    'youtube_description_names' => $youtube_description_names,
    'youtube_title_names' => $youtube_title_names,
    'post_to_youtube' => $post_to_youtube,
    'products' => $products
  ];

  //$client = new SimpleClient('file:///home/u892-pnwjixzcfqyf/www/zeroaqua.com/public_html/post/foo/bar');
  //$client = new SimpleClient('file:///var/www/home/post/foo/bar');
  $client = new SimpleClient('file://C:/xampp/htdocs/post/foo/bar');
  $client->sendEvent('post', $queue_data);
  //Analog::log('queue_data.'.var_export($queue_data, true));

  echo 'success';
});

// match current request url
$match = $router->match();

// call closure or throw 404 status
if( is_array($match) && is_callable( $match['target'] ) ) {
  call_user_func_array( $match['target'], $match['params'] );
} else {
  Analog::log('not match');
  // no route was matched
  header( $_SERVER["SERVER_PROTOCOL"] . ' 404 Not Found');
}

function check_session(){
  session_save_path(SESSION_PATH);
  session_start();
  if(!(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true)){
    header("location: ".URL_LOGIN);
    exit;
  }
}
?>
