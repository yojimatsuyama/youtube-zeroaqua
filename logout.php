<?php
// Include config file
require_once "config.php";

// Initialize the session
session_save_path(SESSION_PATH);
session_start();
 
// Unset all of the session variables
$_SESSION = array();
 
// Destroy the session.
session_destroy();
 
// Redirect to login page
header("location: ".URL_LOGIN);
exit;
?>