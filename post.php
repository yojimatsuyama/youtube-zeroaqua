<?php
// Include config file
require_once "config.php";

use Analog\Handler\File;
 
// Initialize the session
session_save_path(SESSION_PATH);
session_start();

if(!(file_exists('./log/post_'.(new DateTime())->format('Y-m-d').'.log'))){
  $log_file = fopen('./log/post_'.(new DateTime())->format('Y-m-d').'.log', "w") or die("can't open file");
  fclose($log_file);
}
Analog::handler(File::init('./log/post_'.(new DateTime())->format('Y-m-d').'.log'));

$msg = $err = "";

if(!(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true)){
  header("location: ".URL_LOGIN);
  exit;
}

$sku = $price = $buy_now_price = $type = $nickname = $gender = $age = "";

// Processing form data when form is submitted
if($_SERVER["REQUEST_METHOD"] == "POST"){
  Analog::log('test');
  Analog::log('post_params.'.var_export($_POST, true));
  Analog::log('post_files.'.var_export($_FILES, true));
  $sku = trim($_POST["sku"]);
  $price = trim($_POST["price"]);
  $buy_now_price = trim($_POST["buy_now_price"]);
  $type = trim($_POST["type"]);
  $nickname = trim($_POST["nickname"]);
  $gender = trim($_POST["gender"]);
  $age = trim($_POST["age"]);
  $color = "";
  $status = "waiting";

  if(!empty($_POST["color-black"])){
    if($_POST["color-black"] == 'on'){
      $color = $color . 'black';
    }
  }
  if(!empty($_POST["color-white"])){
    if($_POST["color-white"] == 'on'){
      $color = $color . 'white';
    }
  }
  if(!empty($_POST["color-silver"])){
    if($_POST["color-silver"] == 'on'){
      $color = $color . 'silver';
    }
  }
  if(!empty($_POST["color-gold"])){
    if($_POST["color-gold"] == 'on'){
      $color = $color . 'gold';
    }
  }
  if(!empty($_POST["color-copper"])){
    if($_POST["color-copper"] == 'on'){
      $color = $color . 'copper';
    }
  }
  if(!empty($_POST["color-yellow"])){
    if($_POST["color-yellow"] == 'on'){
      $color = $color . 'yellow';
    }
  }
  if(!empty($_POST["color-orange"])){
    if($_POST["color-orange"] == 'on'){
      $color = $color . 'orange';
    }
  }
  if(!empty($_POST["color-red"])){
    if($_POST["color-red"] == 'on'){
      $color = $color . 'red';
    }
  }
  if(!empty($_POST["color-purple"])){
    if($_POST["color-purple"] == 'on'){
      $color = $color . 'purple';
    }
  }
  if(!empty($_POST["color-blue"])){
    if($_POST["color-blue"] == 'on'){
      $color = $color . 'blue';
    }
  }
  if(!empty($_POST["color-green"])){
    if($_POST["color-green"] == 'on'){
      $color = $color . 'green';
    }
  }
  if(!empty($_POST["color-turquoise"])){
    if($_POST["color-turquoise"] == 'on'){
      $color = $color . 'turquoise';
    }
  }

  if($gender == 2 && $price > 2500){
    $err = "Female price must be 1980 or 2500.";
  }

  global $link;
  $skus = explode(',', str_replace(' ', '', $_POST['sku']));
  // Check input errors before inserting in database
  if(empty($err)){
    foreach($skus as $sku){
      // Prepare an insert statement
      $sql = "INSERT INTO items (sku,price,buy_now_price,type,nickname,gender,age,color,status) VALUES (?,?,?,?,?,?,?,?,?)";

      if($stmt = mysqli_prepare($link, $sql)){
        // to fix:process isn't got there
        // Bind variables to the prepared statement as parameters
        mysqli_stmt_bind_param($stmt, "ss", $sku, $price, $buy_now_price, $type, $nickname, $gender, $age, $color, $status);
        
        // Attempt to execute the prepared statement
        if(mysqli_stmt_execute($stmt)){
          $msg = $sku." added\n";
        } else{
          $err = "Oops! Something went wrong. Please try again later.";
        }
        
        // Close statement
        mysqli_stmt_close($stmt);
      }
    }
  }

  // Close connection
  mysqli_close($link);
}
?>

<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="description" content="">
  <meta name="author" content="Mark Otto, Jacob Thornton, and Bootstrap contributors">
  <meta name="generator" content="Hugo 0.87.0">
  <title>Dashboard Template ?? Bootstrap v5.1</title>
  <link rel="canonical" href="https://getbootstrap.com/docs/5.1/examples/dashboard/">
  <!-- thai font -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Mitr&display=swap" rel="stylesheet">
  <!-- Bootstrap core CSS -->
  <link href="css/bootstrap.min.css" rel="stylesheet">
  <!-- Custom styles for this template -->
  <link href="css/dashboard.css" rel="stylesheet">
</head>

<body>

  <div class="loading">
    <div class='uil-ring-css' style='transform:scale(0.79);'>
      <div></div>
    </div>
  </div>

  <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 11;width:20%">
    <div id="liveToast" class="toast hide" role="alert" aria-live="assertive" aria-atomic="true">
      <div class="toast-header">
        <strong class="me-auto"></strong>
        <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
      </div>
      <div class="toast-body">
        Hello, world! This is a toast message.
      </div>
    </div>
  </div>

  <div class="container-fluid">
    <div class="row">
      <main class="col-md-12 ms-sm-auto col-lg-12 px-md-12">
        <form id="form" action="<?php echo URL_POST; ?>" method="POST">
          <div
            class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
            <h1 class="h2">/post</h1>
            <!--p><a href="https://cms.zeroaqua.com/post/logout/">logout</a></p-->
            <p><a href="<?php echo URL_LOGOUT; ?>">logout</a></p>
          </div>

<!--
          <div class="container mt-3">
            <div class="row">

              <div class="col-md-12">
                <div class="input-group mb-3">
                  <div class="input-group-prepend">
                    <span class="input-group-text" id="basic-addon">URL</span>
                  </div>
                  <input name="video-url" type="text" class="form-control" placeholder=""
                    aria-label="video-url" aria-describedby="basic-addon" required>
                </div>
              </div>
            </div>
          </div>
-->
          <div class="container">
            <div class="panel panel-default">
              <div class="panel-body">
              <?php 
                if(!empty($msg)){
                  echo '<div class="alert alert-success">' . $msg . '</div>';
                }
                if(!empty($err)){
                  echo '<div class="alert alert-danger">' . $err . '</div>';
                }
              ?>

                <!-- Drop Zone -->
                <div class="upload-drop-zone" id="drop-zone">
                  <input type="file" id="upload" style="visibility: hidden; width: 1px; height: 1px" multiple />
                  <a href="" onclick="document.getElementById('upload').click(); return false">?????????????????????????????? (Upload)</a>
                </div>

                <div id="filePreview">
                </div>

                <!--
                <div class="progress">
                  <div class="progress-bar" role="progressbar" aria-valuenow="60" aria-valuemin="0" aria-valuemax="100" style="width: 60%;">
                  <span class="sr-only">60% Complete</span>
                  </div>
                </div>
                -->
              
              </div>

              <!-- code from: https://bootsnipp.com/snippets/D7MvX -->

              <!-- Note:
                post to https://cloudinary.com/ then to youtube
                its important to post to cloudinary first because download via url doesnt work on cludinary from you tube because youtube doesnt give mp4 format

                we need to use mp 4 for ebay positng, and show youtube on website and for yahoo 

                for cloudinary needs to be find it via sku

                multiple people may use /post page
                let the user submit when upload is completed to cloudinary, posting to youtube, inserting it to product description can be done at the backend. queue the items in case multiple submit at same time. 

                Be able to drag and move place of images 
                Be able to deleet images
                resize image to 490 x 490
              -->

            </div>
          </div> <!-- /container -->

          <div class="container">
            <div class="row">
              <div class="col-xs-12">
                <h2>?????? (color)</h2>
                <input name="color-black" type="checkbox" class="btn-check" id="btn-check" autocomplete="off" />
                <label class="btn btn-black" for="btn-check">?????? (black)</label>

                <input name="color-white" type="checkbox" class="btn-check" id="btn-check2" autocomplete="off" />
                <label class="btn btn-white" for="btn-check2">????????? (white)</label>

                <input name="color-silver" type="checkbox" class="btn-check" id="btn-check3" autocomplete="off" />
                <label class="btn btn-silver" for="btn-check3">???????????? (silver)</label>

                <input name="color-gold" type="checkbox" class="btn-check" id="btn-gold" autocomplete="off" />
                <label class="btn btn-gold" for="btn-gold">????????? (gold)</label>

                <input name="color-copper" type="checkbox" class="btn-check" id="btn-check4" autocomplete="off" />
                <label class="btn btn-copper" for="btn-check4">?????????????????? (copper)</label>

                <input name="color-yellow" type="checkbox" class="btn-check" id="btn-check5" autocomplete="off" />
                <label class="btn btn-yellow" for="btn-check5">?????????????????? (yellow)</label>

                <input name="color-orange" type="checkbox" class="btn-check" id="btn-check6" autocomplete="off" />
                <label class="btn btn-orange" for="btn-check6">????????? (orange)</label>

                <input name="color-red" type="checkbox" class="btn-check" id="btn-check7" autocomplete="off" />
                <label class="btn btn-red" for="btn-check7">????????? (red)</label>

                <input name="color-purple" type="checkbox" class="btn-check" id="btn-check8" autocomplete="off" />
                <label class="btn btn-purple" for="btn-check8">???????????? (purple)</label>

                <input name="color-blue" type="checkbox" class="btn-check" id="btn-check9" autocomplete="off" />
                <label class="btn btn-blue" for="btn-check9">????????????????????? (blue)</label>

                <input name="color-green" type="checkbox" class="btn-check" id="btn-check10" autocomplete="off" />
                <label class="btn btn-green" for="btn-check10">??????????????? (green)</label>

                <input name="color-turquoise" type="checkbox" class="btn-check" id="btn-check11" autocomplete="off" />
                <label class="btn btn-turquoise" for="btn-check11">????????????????????????????????? (turquoise)</label>
              </div>
            </div>
          </div>

          <div class="container mt-3">
            <div class="row">
              <div class="col-md-4">
                <div class="input-group mb-3">
                  <div class="input-group-prepend">
                    <span class="input-group-text" id="basic-addon1">ID (sku)</span>

                    <!-- Note:
                      1: if ID is duplicated add numeric count (2 for 1st duplicate)
                      ex: 1R1A -> 1R1A2 -> 1R1A3 -> 1R1A4 -> 1R1A5 and so on...
                      2: allow to insert multiple ID with comma separated
                      ex: 1R1A, 1R1B. 1R1C, 1R1D ... create and or dott separate products in woocommerce (ignore space)
                    -->

                  </div>
                  <input name="sku" type="text" onkeyup="this.value = this.value.toUpperCase();" class="form-control" placeholder=""
                    aria-label="id-sku" aria-describedby="basic-addon1" value="<?php echo $sku ?>" required>
                </div>
              </div>

              <div class="col-md-4">
                <div class="input-group mb-3">
                  <div class="input-group-prepend">
                    <span class="input-group-text" id="basic-addon1">???????????? (price)</span>
                  </div>
                  <select id="price" name="price" class="form-select" aria-label="Default select example">
                    <option value="1980" <?php echo $price == '1980' ? 'selected' : ''?>>1980</option>
                    <option value="2500" <?php echo $price == '2500' ? 'selected' : ''?>>2500</option>
                    <option value="3980" <?php echo $price == '3980' ? 'selected' : ''?>>3980</option>
                    <option value="7980" <?php echo $price == '7980' ? 'selected' : ''?>>7980</option>
                    <option value="15000" <?php echo $price == '15000' ? 'selected' : ''?>>15000</option>
                    <option value="30000" <?php echo $price == '30000' ? 'selected' : ''?>>30000</option>
                  </select> 
                </div>
              </div>

              <div class="col-md-4">
                <div class="input-group mb-3">
                  <div class="input-group-prepend">
                    <span class="input-group-text" id="basic-addon1">buy now price</span>
                  </div>
                  <input name="buy_now_price" type="number" class="form-control" placeholder=""
                    aria-label="id-buy_now_price" aria-describedby="basic-addon1" value="<?php echo $buy_now_price ?>">
                </div>
              </div>

              <div class="col-md-2 d-none">
                <div class="form-check form-switch">
                  <input name="premium" class="form-check-input" type="checkbox" id="flexSwitchCheckDefault">
                  <label class="form-check-label" for="flexSwitchCheckDefault">??????????????????????????? (Premium)</label>

                  <!-- Note:
                    Premium off (male or female):
                      woocommerce - regular price: 35.00 sale price: 29.95 | JPY: Regular price: 3500 Sales price: 2980 | THB: Regular price: regular price: 350
                      
                      *custom filed*
                      ebay_us | start price: 9.95 buy now price: 35.00
                      yahoo_auction | start price: 980 buy now price: 3500
                      yahoo_shopping | 2980

                    Premium on  (male or female)
                      woocommerce - regular price: 55.00 sale price: 49.95 | JPY: Regular price: 5500 Sales price: 4980 | THB: Regular price: regular price: 950

                      *custom filed*
                      ebay_us | start price: 19.95 buy now price: 55.00
                      yahoo_auction | start price: 1980 buy now price: 5500
                      yahoo_shopping | 4980

                    Premium off (pair):
                      woocommerce - regular price: 110.00 sale price: 99.95 | JPY: Regular price: 11000 Sales price: 9980 | THB: Regular price: regular price: 1000

                      *custom filed*
                      ebay_us | start price: 29.95 buy now price: 99.95
                      yahoo_auction | start price: 2980 buy now price: 11000
                      yahoo_shopping | 9980

                    Premium on (pair):
                      woocommerce - regular price: 350.00 sale price: 299.95 | JPY: Regular price: 35000 Sales price: 29800 | THB: Regular price: 9500

                      *custom filed*
                      ebay_us | start price: 299.95 buy now price: 299.95
                      yahoo_auction | start price: 29800 buy now price: 29800
                      yahoo_shopping | 29800

                      *need to add custom field to woocommerce and store those values
                      https://www.thecreativedev.com/to-create-custom-field-in-woocommerce-products-admin-panel/
                  -->
                </div>
              </div>
              <div class="col-md-3 d-none">
                <div class="form-check form-switch">
                  <input name="penny" class="form-check-input" type="checkbox" id="flexSwitchCheckDefault2">
                  <label class="form-check-label" for="flexSwitchCheckDefault2">???????????????1????????? (Penny Start)</label>

                  <!--
                    overwrite price setting above if penny start is selected
                    Premium off + Penny Start:
                      woocommerce - regular price: 35.00 sale price: 29.95 | JPY: Regular price: 3500 Sales price: 2980 | THB: Regular price: regular price: 350

                      *custom filed*
                      ebay_us | start price: 0.01 buy now price: -
                      yahoo_auction | start price: 1 buy now price: -
                      yahoo_shopping | -

                    Premium on + Penny Start:
                      woocommerce - regular price: 350.00 sale price: - | JPY: Regular price: 35000 Sales price: - | THB: Regular price: 12000

                      *custom filed*
                      ebay_us | start price: 0.01 buy now price: -
                      yahoo_auction | start price: 1 buy now price: -
                      yahoo_shopping | -

                      *need to add custom field to woocommerce and store those values
                      https://www.thecreativedev.com/to-create-custom-field-in-woocommerce-products-admin-panel/
                  -->

                </div>
              </div>
            </div>
          </div>

          <div class="container">
            <div class="row">
              <div class="col-md-3">
                <h2>??????????????? (type)</h2>

                <select name="type" class="form-select" aria-label="Default select example">
                  <option value="15" <?php echo $type == '15' ? 'selected' : ''?>>?????????????????? (Plakat)</option>
                  <option value="49" <?php echo $type == '49' ? 'selected' : ''?>>?????????????????????????????????????????????????????????????????????????????? (Half Moon)</option>
                  <option value="50" <?php echo $type == '50' ? 'selected' : ''?>>?????????????????????????????????????????? (Crowntail)</option>
                  <option value="51" <?php echo $type == '51' ? 'selected' : ''?>>???????????????????????????????????? (Dumbo)</option>
                  <option value="78" <?php echo $type == '78' ? 'selected' : ''?>>?????????????????????????????????????????? (Wild Alien)</option>
                  <option value="79" <?php echo $type == '79' ? 'selected' : ''?>>???????????????????????????????????????????????????????????????????????????????????????????????? (Dumbo Half Moon)</option>
                  <option value="80" <?php echo $type == '80' ? 'selected' : ''?>>???????????????????????????????????? (Double Tail)</option>
                  <option value="123" <?php echo $type == '123' ? 'selected' : ''?>>????????????????????????????????? (Isan)</option>
                </select>

              </div>
              <div class="col-md-3">
                <h2>???????????????????????? (nickname)</h2>

                <select name="nickname" class="form-select" aria-label="Default select example">
                  <option selected>---</option>
                  <option value="54" <?php echo $nickname == '54' ? 'selected' : ''?>>??????????????? (Nemo)</option>
                  <option value="55" <?php echo $nickname == '55' ? 'selected' : ''?>>??????????????????????????????????????? (Nemo Galaxy)</option>
                  <option value="73" <?php echo $nickname == '73' ? 'selected' : ''?>>??????????????? (Dragon)</option>
                  <option value="57" <?php echo $nickname == '57' ? 'selected' : ''?>>??????????????? (Avatar)</option>
                </select>
              </div>
              <div class="col-md-3">
                <h2>????????? (gender)</h2>

                <select id="gender" name="gender" class="form-select" aria-label="Default select example">
                  <option value="1" <?php echo $gender == '1' ? 'selected' : ''?>>?????????????????? (Male)</option>
                  <option value="2" <?php echo $gender == '2' ? 'selected' : ''?>>????????????????????? (Female)</option>
                  <option value="3" <?php echo $gender == '3' ? 'selected' : ''?>>????????? (Pair)</option>
                </select>
              </div>
              <div class="col-md-3">
                <h2>???????????? (age)</h2>
                <div class="input-group mb-3">
                  <input name="age" type="text" class="form-control" placeholder="" aria-label="Recipient's username"
                    aria-describedby="basic-addon2" value="<?php echo $age ?>" required>
                  <div class="input-group-append">
                    <span class="input-group-text" id="basic-addon2">??????????????? (month)</span>
                    <!-- Note:
                      backdate and record brithday ex: if age = 1 , save currecnt date - 30 days 
                    -->
                  </div>
                </div>
              </div>

              <div class="container mb-200 d-none">
                <div class="row">
                  <div class="col-md-3">
                    <h2>youtube</h2>

                    <select name="post_to_youtube" class="form-select">
                      <!--option value="1" selected>Schedule</option-->
                      <option value="2">Post Now</option>
                    </select>
                  </div>
                </div>
              </div>

              <!--div class="container mb-200">
                <div class="row">
                  <div class="col-md-4">
                    <h2>tiktok url</h2>
                    <div class="input-group mb-3">
                      <input name="tiktok_url" type="text" class="form-control" placeholder="">
                    </div>
                  </div>
                </div>
              </div-->

              <div class="container mb-200">
                <div class="row">
                  <div class="col-md-3 submit">
                    <input type="submit" class="btn btn-primary"></input>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </form>
      </main>

      <div class="col-center text-center" style="margin-bottom:20px;display:none;" width="50%">
        <button onclick="myFunction()">Agree to Terms of Use & Privacy Policy</button>
      </div>

      <div id="myDIV" class="container" style="display:none;">
        <div class="row">
          <div class="col-md-8 offset-md-2 col-xs-12 ta-center">
            <h3>API Client Terms of Use and Privacy Policies</h3>
            <p>This website use YouTube API Servcie to collect data to authorize access to YouTube from this page. By using this website you agree to YouTube's Terms of Service (<a href="https://www.youtube.com/t/terms">https://www.youtube.com/t/terms</a>) and Google Privacy Policy (<a href="http://www.google.com/policies/privacy">http://www.google.com/policies/privacy</a>) You can revoke data collected by Youtube API, find more information at (<a href="https://security.google.com/settings/security/permissions">https://security.google.com/settings/security/permissions</a>)
          </div>
        </div>
      </div>

<script>
function myFunction() {
  var x = document.getElementById("myDIV");
  if (x.style.display === "none") {
    x.style.display = "block";
  } else {
    x.style.display = "none";
  }
}
</script>


      <footer class="footer mt-auto py-3 bg-light" style="display:none;">
        <div class="container">
          <p class="text-muted text-center">ZEROAQUA :: <a href="https://zeroaqua.com/Terms-of-Use/">Terms of Use</a> | <a href="https://zeroaqua.com/privacy-policy/">Pricacy Policy</a></p>
        </div>
      </footer>

    </div>
  </div>

  <script src="js/bootstrap.bundle.min.js"></script>

  <script src="https://cdn.jsdelivr.net/npm/feather-icons@4.28.0/dist/feather.min.js"
    integrity="sha384-uO3SXW5IuS1ZpFPKugNNWqTZRRglnUJK6UAZ/gxOX80nxEkN9NcGZTftn6RzhGWE"
    crossorigin="anonymous"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js@2.9.4/dist/Chart.min.js"
    integrity="sha384-zNy6FEbO50N+Cg5wap8IKA4M/ZnLJgzc6w2NqACZaK0u0FXfOWRRJOnQtpZun8ha"
    crossorigin="anonymous"></script>

  <script
    src="https://code.jquery.com/jquery-3.6.0.js"
    integrity="sha256-H+K7U5CnXl1h5ywQfKtSj8PCmoN9aaq30gDh27Xc0jk="
    crossorigin="anonymous"></script>
    
  <!--script src="js/post.js"></script-->
  <!--script src="js/dashboard.js"></script-->

  <script>
    $(document).ready(function () {
      /*$("#form").submit(function(e) {
        //e.preventDefault();
        $(".loading").show();

        let req = new XMLHttpRequest();
        let formData = new FormData($('#form')[0]);
        var gender = '';
        var price = '';
        for (var p of formData) {
          let name = p[0];
          let value = p[1];
          if(name == 'gender'){
            gender = value;
          }
          if(name == 'price'){
            price = value;
          }
        }
        if(gender == 2 && price > 2500){
          alert('Female price must be 1980 or 2500.');
          $(".loading").hide();
          return
        }

        for(var i = 0; i < document.getElementById("upload").files.length; i++){
          formData.append("file" + i, document.getElementById("upload").files[i]);
        }
        
        req.open("POST", "<?php echo URL_POST; ?>");
        req.send(formData);

        req.onreadystatechange = function() {
          if (req.readyState == XMLHttpRequest.DONE) {
            $(".loading").hide();
            console.log(req.responseText);
            if(req.responseText.trim() == 'success'){
              $('#form')[0].reset();
              resetFile();
              $('.toast-body').text('success');
              var toastElList = [].slice.call(document.querySelectorAll('.toast'))
              var toastList = toastElList.map(function (toastEl) {
                return new bootstrap.Toast(toastEl, {delay:5000})
              })
              toastList.forEach(toast => toast.show());
            }else{
              $('.toast-body').text('failed');
              var toastElList = [].slice.call(document.querySelectorAll('.toast'))
              var toastList = toastElList.map(function (toastEl) {
                return new bootstrap.Toast(toastEl, {delay:5000})
              })
              toastList.forEach(toast => toast.show());
            }
          }
        }
      });*/
    });
  </script>

  <script>
    + function ($) {
      'use strict';

      var dt = new DataTransfer();
      var fileUpload = document.getElementById("upload");
      var dvPreview = document.getElementById("filePreview");

      var dropZone = document.getElementById('drop-zone');

      window.resetFile = resetFile;
      function resetFile(ev) {
        dt.items.clear();
        fileUpload.onchange();
      }

      var startUpload = function (files) {
        console.log(files)
      }

      dropZone.ondrop = function (e) {
        e.preventDefault();
        this.className = 'upload-drop-zone';

        fileUpload.files = e.dataTransfer.files;
        fileUpload.onchange();

        //startUpload(e.dataTransfer.files)
      }

      dropZone.ondragover = function () {
        this.className = 'upload-drop-zone drop';
        return false;
      }

      dropZone.ondragleave = function () {
        this.className = 'upload-drop-zone';
        return false;
      }

      fileUpload.onchange = function () {
        var regex_img = /^([a-zA-Z0-9\s_\\.\-:])+(.jpg|.jpeg|.gif|.png|.bmp)$/;
        var regex_vid = /^([a-zA-Z0-9\s_\\.\-:])+(.mp4|.mov)$/;
        for(var i = 0; i < fileUpload.files.length; i++){
          if(regex_img.test(fileUpload.files[i].name.toLowerCase()) || regex_vid.test(fileUpload.files[i].name.toLowerCase())){
            dt.items.add(fileUpload.files[i]);
          }
        }

        fileUpload.files = dt.files;
        var count = 0;
        var divs = [];

        if(fileUpload.files.length == 0){
          dvPreview.innerHTML = "";
        }

        for(var i = 0; i < fileUpload.files.length; i++){
          const file = fileUpload.files[i];
          const id = i;
          var reader = new FileReader();
          reader.onload = function (e) {
            var div = document.createElement("div");
            div.classList.add('d-inline-block');
            div.classList.add('position-relative');
            div.classList.add('mx-1');
            div.classList.add('mt-2');
            div.setAttribute('draggable', true);
            div.setAttribute('ondrop', 'drop(event, ' + id + ')');
            div.setAttribute('ondragover', 'allowDrop(event)');
            div.setAttribute('ondragstart', 'drag(event, ' + id + ')');
            var img = document.createElement("img");
            img.height = "136";
            img.width = "136";
            if(regex_vid.test(file.name.toLowerCase())){
              img.src = 'fontawesome-free-5.15.4-web/svgs/solid/video.svg';
              var span = document.createElement("span");
              span.classList.add('align-middle');
              span.classList.add('video-name');
              span.classList.add('bg-white');
              span.classList.add('text-center');
              span.innerHTML = file.name;
              div.appendChild(span);
            }else{
              img.src = e.target.result;
            }
            var btn = document.createElement("button");
            btn.classList.add('align-top');
            btn.classList.add('img-close-btn');
            btn.classList.add('btn');
            btn.classList.add('btn-danger');
            btn.classList.add('btn-sm');
            btn.innerHTML = 'X';
            btn.setAttribute('onclick', 'imgRemove(' + id + ')');
            div.appendChild(btn);
            div.appendChild(img);
            divs[id] = div
          }
          reader.onloadend = function(e) {
            if(++count === fileUpload.files.length){
              dvPreview.innerHTML = "";
              for(var i = 0; i < fileUpload.files.length; i++){
                dvPreview.appendChild(divs[i]);
              }
            }
          }
          reader.readAsDataURL(file);
        }
      }

      window.imgRemove = imgRemove;
      function imgRemove(index){
        dt.items.clear();
        for(var i = 0; i < fileUpload.files.length - 1; i++){
          if(i < index){
            dt.items.add(fileUpload.files[i]);
          }else if(i >= index){
            dt.items.add(fileUpload.files[i + 1]);
          }
        }

        fileUpload.files = new DataTransfer().files;
        fileUpload.onchange();
      }

      window.allowDrop = allowDrop;
      function allowDrop(ev) {
        ev.preventDefault();
      }
      
      window.drag = drag;
      function drag(ev, index) {
        ev.dataTransfer.setData("index", index);
      }
      
      window.drop = drop;
      function drop(ev, index) {
        ev.preventDefault();
        var drag_id = ev.dataTransfer.getData("index");
        if(index < drag_id){
          dt.items.clear();
          for(var i = 0; i < fileUpload.files.length; i++){
            if(i < index){
              dt.items.add(fileUpload.files[i]);
            }else if(i == index){
              dt.items.add(fileUpload.files[drag_id]);
            }else if(i > index && i <= drag_id){
              dt.items.add(fileUpload.files[i-1]);
            }else if(i > drag_id){
              dt.items.add(fileUpload.files[i]);
            }
          }

          fileUpload.files = new DataTransfer().files;
          fileUpload.onchange();
        }else if(drag_id < index){
          dt.items.clear();
          for(var i = 0; i < fileUpload.files.length; i++){
            if(i < drag_id){
              dt.items.add(fileUpload.files[i]);
            }else if(i >= drag_id && i < index){
              dt.items.add(fileUpload.files[i+1]);
            }else if(i == index){
              dt.items.add(fileUpload.files[drag_id]);
            }else if(i > index){
              dt.items.add(fileUpload.files[i]);
            }
          }

          fileUpload.files = new DataTransfer().files;
          fileUpload.onchange();
        }else{
          return;
        }
      }
    }();
  </script>
  <style>
    *.hidden {
      display: none !important;
    }

    div.loading{
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background-color: rgba(16, 16, 16, 0.5);
      z-index: 100;
      display: none;
    }

    @-webkit-keyframes uil-ring-anim {
      0% {
        -ms-transform: rotate(0deg);
        -moz-transform: rotate(0deg);
        -webkit-transform: rotate(0deg);
        -o-transform: rotate(0deg);
        transform: rotate(0deg);
      }
      100% {
        -ms-transform: rotate(360deg);
        -moz-transform: rotate(360deg);
        -webkit-transform: rotate(360deg);
        -o-transform: rotate(360deg);
        transform: rotate(360deg);
      }
    }
    @-webkit-keyframes uil-ring-anim {
      0% {
        -ms-transform: rotate(0deg);
        -moz-transform: rotate(0deg);
        -webkit-transform: rotate(0deg);
        -o-transform: rotate(0deg);
        transform: rotate(0deg);
      }
      100% {
        -ms-transform: rotate(360deg);
        -moz-transform: rotate(360deg);
        -webkit-transform: rotate(360deg);
        -o-transform: rotate(360deg);
        transform: rotate(360deg);
      }
    }
    @-moz-keyframes uil-ring-anim {
      0% {
        -ms-transform: rotate(0deg);
        -moz-transform: rotate(0deg);
        -webkit-transform: rotate(0deg);
        -o-transform: rotate(0deg);
        transform: rotate(0deg);
      }
      100% {
        -ms-transform: rotate(360deg);
        -moz-transform: rotate(360deg);
        -webkit-transform: rotate(360deg);
        -o-transform: rotate(360deg);
        transform: rotate(360deg);
      }
    }
    @-ms-keyframes uil-ring-anim {
      0% {
        -ms-transform: rotate(0deg);
        -moz-transform: rotate(0deg);
        -webkit-transform: rotate(0deg);
        -o-transform: rotate(0deg);
        transform: rotate(0deg);
      }
      100% {
        -ms-transform: rotate(360deg);
        -moz-transform: rotate(360deg);
        -webkit-transform: rotate(360deg);
        -o-transform: rotate(360deg);
        transform: rotate(360deg);
      }
    }
    @-moz-keyframes uil-ring-anim {
      0% {
        -ms-transform: rotate(0deg);
        -moz-transform: rotate(0deg);
        -webkit-transform: rotate(0deg);
        -o-transform: rotate(0deg);
        transform: rotate(0deg);
      }
      100% {
        -ms-transform: rotate(360deg);
        -moz-transform: rotate(360deg);
        -webkit-transform: rotate(360deg);
        -o-transform: rotate(360deg);
        transform: rotate(360deg);
      }
    }
    @-webkit-keyframes uil-ring-anim {
      0% {
        -ms-transform: rotate(0deg);
        -moz-transform: rotate(0deg);
        -webkit-transform: rotate(0deg);
        -o-transform: rotate(0deg);
        transform: rotate(0deg);
      }
      100% {
        -ms-transform: rotate(360deg);
        -moz-transform: rotate(360deg);
        -webkit-transform: rotate(360deg);
        -o-transform: rotate(360deg);
        transform: rotate(360deg);
      }
    }
    @-o-keyframes uil-ring-anim {
      0% {
        -ms-transform: rotate(0deg);
        -moz-transform: rotate(0deg);
        -webkit-transform: rotate(0deg);
        -o-transform: rotate(0deg);
        transform: rotate(0deg);
      }
      100% {
        -ms-transform: rotate(360deg);
        -moz-transform: rotate(360deg);
        -webkit-transform: rotate(360deg);
        -o-transform: rotate(360deg);
        transform: rotate(360deg);
      }
    }
    @keyframes uil-ring-anim {
      0% {
        -ms-transform: rotate(0deg);
        -moz-transform: rotate(0deg);
        -webkit-transform: rotate(0deg);
        -o-transform: rotate(0deg);
        transform: rotate(0deg);
      }
      100% {
        -ms-transform: rotate(360deg);
        -moz-transform: rotate(360deg);
        -webkit-transform: rotate(360deg);
        -o-transform: rotate(360deg);
        transform: rotate(360deg);
      }
    }
    .uil-ring-css {
      margin: auto;
      position: absolute;
      top: 0;
      left: 0;
      bottom: 0;
      right: 0;
      width: 200px;
      height: 200px;
    }
    .uil-ring-css > div {
      position: absolute;
      display: block;
      width: 160px;
      height: 160px;
      top: 20px;
      left: 20px;
      border-radius: 80px;
      box-shadow: 0 6px 0 0 #ffffff;
      -ms-animation: uil-ring-anim 1s linear infinite;
      -moz-animation: uil-ring-anim 1s linear infinite;
      -webkit-animation: uil-ring-anim 1s linear infinite;
      -o-animation: uil-ring-anim 1s linear infinite;
      animation: uil-ring-anim 1s linear infinite;
    }
  </style>
</body>

</html>

<?php
//   - other parameters on product data
// 	- permalink - zeroaqua.com/product/sku
// 	- simple product
// 	- manage stock - tick
// 	- stock qty 1
// 	- allow backorders - do not allow
// 	- low stock threshold store-wide threshold (0)
// 	- sold individually - tick
	
// 	- linked products - none
// 	- Custom fields (show on right side of image with other elemets such as category and tags)
// 		- Category | (already shown)
// 		- Type | selected value + $nickname if any
// 		- Gender | selected value
// 		- Age | selected value
// 		- Color | selected value (allow multiple)
// 	- advaned - none
// 	- shipping
// 	- weight 200g
// 	- size 10cm x 20cm x 6cm

// 	- add category
// 		<option selected>Prakat</option> -> Prakat (HMPK)
// 		<option value="1">Half Moon</option> -> Half Moon
// 		<option value="2">Crowntail</option> -> Crowntail
// 		<option value="3">Dumbo</option> -> Dumbo
// 		<option value="3">Wild</option> -> Wild
// 		Pair -> Pair
// 		<option value="1">Nemo</option> -> Nemo Koi Candy
// 		<option value="2">Nemo Candy Galaxy</option> -> Nemo Koi Candy Galaxy
// 		<option value="3">Black Samurai</option> -> Black Samurai
// 		<option value="3">Avatar</option> -> Avatar
// 		<option value="3">Alien</option> -> Alien
// 		Premium -> Premium 

// 	- product short description
// 		EN: 100% DOA Money Back Guarantee! Shipping Fee Include Transshipment and Delivery to Your Door Step
// 		JP: ?????????????????????????????? 100%????????????! ???????????????????????? + ???????????? + ??????????????????????????????????????????????????????  
	
// 	- Generate title:
// 		EN: Betta + $type + $nickname (if any) + $gender + $color + Aquarium Live Fish
// 		JP: ?????????????????? ?????? + $type + $nickname (if any) + $gender + $color

// 	- youtube title:
// 		#shorts Betta Aquarium ?????? ????????? + $sku

// 	- Generate description

// 	- other: fetch next shipping date from departure database and insert to description "Next Shipping" Section

// fixed the price

// Male:
// 1980 | 2500
// 2500 | 2980
// 3980 | 4980
// 3980 | 5500
// 7980 | 10000
// 15000 | 18000
// 30000 | 35000
// Female:
// 1980 | 2500
// 2500 | 2980

// with 3980, we want to have 2 options, 4980 or 5500
// 90% of 3980 will end with 4980 so that can be default
// but we want to have ability to change to 5500

// show 2 inputs
// first select (1980,2500,3980,7980, 15000,30000), show second input (balnk) if blank use default value (2500,2980,4980,5500,10000,18000,35000) but if there is input , that value shold be used (so ex: i can put 5500 then it will be 3980 | 5500)

// CREATE TABLE items (
//   id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
//   sku VARCHAR(20) NOT NULL,
//   price MEDIUMINT NOT NULL,
//   buy_now_price MEDIUMINT NOT NULL,
//   type SMALLINT NOT NULL,
//   nickname SMALLINT NOT NULL,
//   gender TINYINT NOT NULL,
//   age TINYINT NOT NULL,
//   status VARCHAR(20) NOT NULL,
//   created_at DATETIME DEFAULT CURRENT_TIMESTAMP
// );

// CREATE TABLE item_files (
//   item_id INT NOT NULL,
//   type VARCHAR(20) NOT NULL,
//   url VARCHAR(255) NOT NULL PRIMARY KEY,
//   created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
//   FOREIGN KEY (item_id) REFERENCES items(id)
// );
?>