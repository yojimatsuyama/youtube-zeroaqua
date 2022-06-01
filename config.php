<?php
// Database credentials.
define('DB_SERVER', '156.67.219.175');
define('DB_USERNAME', 'grape');
define('DB_PASSWORD', 'f|yE4g|eSf|y');
define('DB_NAME', 'zeroaqua');
 
// Attempt to connect to MySQL database 
$link = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
 
// Check connection
if($link === false){
    die("ERROR: Could not connect. " . mysqli_connect_error());
}
?>