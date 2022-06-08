<?php
// Database credentials.

define('DB_SERVER', '156.67.219.175');
define('DB_USERNAME', 'grape');
define('DB_PASSWORD', 'f|yE4g|eSf|y');
define('DB_NAME', 'zeroaqua');
/*
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'zeroaqua');
*/
define('URL_POST', 'https://cms.zeroaqua.com/post/');
define('URL_LOGIN', 'https://cms.zeroaqua.com/post/login/');
define('URL_LOGOUT', 'https://cms.zeroaqua.com/post/logout/');
define('URL_USER_ADD', 'https://cms.zeroaqua.com/post/user-add/');
define('SESSION_PATH', '/tmp');
/*
define('URL_POST', 'http://localhost/post/');//test
define('URL_LOGIN', 'http://localhost/post/login/');//test
define('URL_LOGOUT', 'http://localhost/post/logout/');//test
define('URL_USER_ADD', 'http://localhost/post/user-add/');//test
define('SESSION_PATH', '');//test
*/

define('SHIPPING_DATE', '2022-06-10');

// Attempt to connect to MySQL database
$link = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
 
// Check connection
if($link === false){
  die("ERROR: Could not connect. " . mysqli_connect_error());
}

// c!YQBaYh3GyQ
// ssh -p 22 root@156.67.219.175
// admin
// &p9B*kzgYRkF4Dd&
// grape
// BCbyUAnFZI
?>