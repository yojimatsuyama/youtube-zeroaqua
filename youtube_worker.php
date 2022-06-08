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

$path = '/var/www/home/post/';
//$path = './';//test

$date = DateTime::createFromFormat('Y-m-d', SHIPPING_DATE);
$prev_date = DateTime::createFromFormat('Y-m-d', $date->format('Y-m-d'));
$prev_date->modify('-1 day');
$next_date = DateTime::createFromFormat('Y-m-d', $date->format('Y-m-d'));
$next_date->modify('+1 day');
$dow = ['日', '月', '火', '水', '木', '金', '土'];

if(!(file_exists($path.'log/youtube_worker_'.(new DateTime())->format('Y-m-d').'.log'))){
  $log_file = fopen($path.'log/youtube_worker_'.(new DateTime())->format('Y-m-d').'.log', "w") or die("can't open file");
  fclose($log_file);
}
Analog::handler(File::init($path.'log/youtube_worker_'.(new DateTime())->format('Y-m-d').'.log'));
Analog::log('youtube_worker');

$mysqli = new mysqli('156.67.219.175', 'grape', 'f|yE4g|eSf|y', 'zeroaqua');
  
if($mysqli->connect_errno){
  header('HTTP/1.1 500 Internal Server Error');
  die(json_encode(array('message' => 'Failed to connect to MySQL: ' . $mysqli->connect_error, 'code' => 500)));
}

if($result = $mysqli->query('SELECT * FROM youtube_queues WHERE sku NOT IN ("0073", "0103", "0104") AND posted = false AND deleted = false')){
  $queues = [];
  while($row = $result->fetch_array(MYSQLI_ASSOC)) {
    $queues[] = $row;
  }
}else{
  header('HTTP/1.1 500 Internal Server Error');
  die(json_encode(array('message' => 'Error updating record: ' . $mysqli->error, 'code' => 500)));
}

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

/*$date = new DateTime('tomorrow');
$next_date = new DateTime('tomorrow');
$next_date->modify('+1 day');
$publish_time = [
  $date->format('Y-m-d').'T09:30:00.000+07:00',
  $date->format('Y-m-d').'T19:30:00.000+07:00',
  $date->format('Y-m-d').'T07:30:00.000+07:00',
  $date->format('Y-m-d').'T15:30:00.000+07:00',
  $next_date->format('Y-m-d').'T02:30:00.000+07:00'
];*/

Analog::log('queues.'.var_export($queues, true));
//Analog::log('publish_time.'.var_export($publish_time, true));

$description_template = '<div class="ms--167">
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

$i = 0;
foreach($queues as $queue){
  $data = [
    'sku' => $queue['sku']
  ];
  $product = $woocommerce->get('products', $data);
  $product_jp = $woocommerce_jp->get('products', $data);

  if((count($product) > 0) && (count($product_jp) > 0)){
    Analog::log('sku.'.var_export($queue['sku'], true));

    $product = $product[0];
    $product_jp = $product_jp[0];

    //if(count($woocommerce_product) > 1){
    //  $woocommerce_product = $woocommerce_product[0];
    //}
    Analog::log('product.'.var_export($product, true));
    Analog::log('product_jp.'.var_export($product_jp, true));

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
    $videoSnippet->setTitle($queue['title'].' No:'.$queue['sku']);

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
    //$videoStatus->setPrivacyStatus('private');
    //$videoStatus->setPublishAt($publish_time[$i%5]);
    $videoStatus->setPrivacyStatus('public');
    $video->setStatus($videoStatus);
    Analog::log('youtube_video.'.var_export($video, true));

    $response = $service->videos->insert(
      'snippet,status',
      $video,
      array(
        'data' => file_get_contents('https://cms.zeroaqua.com/post/files/'.$queue['sku'].'.mp4'),
        'mimeType' => 'application/octet-stream',
        'uploadType' => 'multipart'
      )
    );
    $description = '<div class="video-holder">[iframe src="http://www.youtube.com/embed/'.$response['id'].'" width="560" height="315" frameborder="0" allowfullscreen="allowfullscreen"]</div><span ="text-secondary">*No color enhancement filters applied to the footage, recorded under white led lights.</span>'.$product->description;
    Analog::log('google_api_res.'.var_export($response, true));

    if($product->description == ''){
      $description = $description.$description_template;
    }

    $update_data = [
      'description' => $description
    ];
    $res = $woocommerce->put('products/'.$queue['woocommerce_id'], $update_data);
    Analog::log('woocommerce_update_res.'.var_export($res, true));

    sleep(60);

    $png = imagecreatefrompng('https://zeroaqua.com/asset/tmp/overlay-min.png');
    $jpeg = imagecreatefromjpeg('https://img.youtube.com/vi/'.$response['id'].'/0.jpg');
    list($width, $height) = getimagesize('https://img.youtube.com/vi/'.$response['id'].'/0.jpg');
    list($newwidth, $newheight) = getimagesize('https://zeroaqua.com/asset/tmp/overlay-min.png');
    $out = imagecreatetruecolor($newwidth/2, $newheight/2);
    imagecopyresampled($out, $jpeg, 0, 0, 0, 0, $newwidth/2, $newheight/2, $width, $height);
    imagecopyresampled($out, $png, 0, 0, 0, 0, $newwidth/2, $newheight/2, $newwidth, $newheight);
    imagejpeg($out, $path.'files/'.$queue['sku'].'-youtube-thumbnail.jpg', 100);
          
    $youtube_short_url = 'https://youtu.be/'.$response['id'];
    $description_youtube = '      <tr>
          <td>
            <div>Youtube動画はこちら:</div>
            <a href="'.$youtube_short_url.'"><img class="content-outer-box" width="100%" src="https://cms.zeroaqua.com/post/files/'.$queue['sku'].'-youtube-thumbnail.jpg"></a>
            <div><a href="'.$youtube_short_url.'"><br>'.$youtube_short_url.'<br></a></div>
          </td>
        </tr>';

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
            '.$description_youtube.'
            <tr>
            <td style="text-align:center;">
              <img class="content-image" width="100%"
                src="https://cms.zeroaqua.com/post/files/'.$queue['sku'].'-jp-merged.jpg">
            </td>
          </tr>';
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
                商品コード: '.$queue['sku'].'<br><br>
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
    
    $description_jp = $description_top.$description_bottom;

    $update_data = [
      'description' => $description_jp
    ];
    $res = $woocommerce_jp->put('products/'.$product_jp->id, $update_data);
    Analog::log('woocommerce_update_res.'.var_export($res, true));

    if($result = $mysqli->query('UPDATE youtube_queues SET posted = true WHERE id = '.$queue['id'])){
      
    }else{
      header('HTTP/1.1 500 Internal Server Error');
      die(json_encode(array('message' => 'Error updating record: ' . $mysqli->error, 'code' => 500)));
    }

    $i = $i + 1;
  }
}
$mysqli->close();
?>