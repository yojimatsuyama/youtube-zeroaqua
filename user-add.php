<?php
// Include config file
require_once "config.php";
 
// Initialize the session
session_save_path(SESSION_PATH);
session_start();

// Define variables and initialize with empty values
$username = $password = $confirm_password = "";
$username_err = $password_err = $confirm_password_err = "";
$msg = "";

if(!(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true)){
  header("location: ".URL_LOGIN);
  exit;
}

if(!(isset($_SESSION["role"]) && $_SESSION["role"] == "admin")){
  header("location: ".URL_POST);
  exit;
}
 
// Processing form data when form is submitted
if($_SERVER["REQUEST_METHOD"] == "POST"){
  $username = trim($_POST["username"]);

  // Validate username
  if(empty(trim($_POST["username"]))){
    $username_err = "Please enter a username.";
  }elseif(!preg_match('/^[a-zA-Z0-9_]+$/', trim($_POST["username"]))){
    $username_err = "Username can only contain letters, numbers, and underscores.";
  }else{
    // Prepare a select statement
    $sql = "SELECT id FROM users WHERE username = ?";

    global $link;
    
    if($stmt = mysqli_prepare($link, $sql)){
      // Bind variables to the prepared statement as parameters
      mysqli_stmt_bind_param($stmt, "s", $param_username);
      
      // Set parameters
      $param_username = trim($_POST["username"]);
      
      // Attempt to execute the prepared statement
      if(mysqli_stmt_execute($stmt)){
        /* store result */
        mysqli_stmt_store_result($stmt);
        
        if(mysqli_stmt_num_rows($stmt) == 1){
          $username_err = "This username is already taken.";
        }else{
          $username = trim($_POST["username"]);
        }
      }else{
        echo "Oops! Something went wrong. Please try again later.";
      }

      // Close statement
      mysqli_stmt_close($stmt);
    }
  }
    
  // Validate password
  if(empty(trim($_POST["password"]))){
    $password_err = "Please enter a password.";     
  }elseif(strlen(trim($_POST["password"])) < 6){
    $password_err = "Password must have atleast 6 characters.";
  }else{
    $password = trim($_POST["password"]);
  }
    
  // Validate confirm password
  if(empty(trim($_POST["confirm_password"]))){
    $confirm_password_err = "Please confirm password.";     
  } else{
    $confirm_password = trim($_POST["confirm_password"]);
    if(empty($password_err) && ($password != $confirm_password)){
      $confirm_password_err = "Password did not match.";
    }
  }
    
  // Check input errors before inserting in database
  if(empty($username_err) && empty($password_err) && empty($confirm_password_err)){

    // Prepare an insert statement
    $sql = "INSERT INTO users (username, password) VALUES (?, ?)";
      
    global $link;

    if($stmt = mysqli_prepare($link, $sql)){
      // Bind variables to the prepared statement as parameters
      mysqli_stmt_bind_param($stmt, "ss", $param_username, $param_password);
      
      // Set parameters
      $param_username = $username;
      $param_password = password_hash($password, PASSWORD_DEFAULT); // Creates a password hash
      
      // Attempt to execute the prepared statement
      if(mysqli_stmt_execute($stmt)){
        // Redirect to login page
        //header("location: ".$redirect_url."user-add");
        $msg = "user added";
      } else{
        echo "Oops! Something went wrong. Please try again later.";
      }
      
      // Close statement
      mysqli_stmt_close($stmt);
    }
  }
    
  // Close connection
  mysqli_close($link);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>User add</title>
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
  <style>
  body{
    font: 14px sans-serif;
  }
  .wrapper{
    width: 360px;
    padding: 20px;
    margin: auto;
  }
  </style>
</head>
<body>
  
  <div class="container-fluid">
    <div class="row">
      <main class="col-md-12 ms-sm-auto col-lg-12 px-md-12">
        <div
          class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
          <h1 class="h2">/user-add</h1>
          <p><a href="<?php echo URL_LOGOUT; ?>">logout</a></p>
        </div>
      </div>
    </div>
  </div>

  <div class="wrapper">
    <h2>User add</h2>

    <?php 
      if(!empty($msg)){
        echo '<div class="alert alert-success">' . $msg . '</div>';
      }
    ?>

    <form action="<?php echo URL_USER_ADD ?>" method="post">
      <div class="form-group">
        <label>Username</label>
        <input type="text" name="username" class="form-control <?php echo (!empty($username_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $username; ?>">
        <span class="invalid-feedback"><?php echo $username_err; ?></span>
      </div>    
      <div class="form-group">
        <label>Password</label>
        <input type="password" name="password" class="form-control <?php echo (!empty($password_err)) ? 'is-invalid' : ''; ?>">
        <span class="invalid-feedback"><?php echo $password_err; ?></span>
      </div>
      <div class="form-group">
        <label>Confirm Password</label>
        <input type="password" name="confirm_password" class="form-control <?php echo (!empty($confirm_password_err)) ? 'is-invalid' : ''; ?>">
        <span class="invalid-feedback"><?php echo $confirm_password_err; ?></span>
      </div>
      <div class="form-group">
        <input type="submit" class="btn btn-primary" value="Submit">
        <input type="reset" class="btn btn-secondary ml-2" value="Reset">
      </div>
    </form>
  </div>    
</body>
</html>