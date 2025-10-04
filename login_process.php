<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>login_process.php</title>
</head>

<body>
<?php
session_start();
error_reporting( 2 );

include 'includes/connect.php';

if ( $_SERVER[ 'REQUEST_METHOD' ] == 'POST' ) {
  $user = mysqli_real_escape_string( $conn, $_POST[ 'username' ] );
  $pass = $_POST[ 'password' ];

  $sql = "SELECT * FROM users WHERE username = '$user'";
  $result = mysqli_query( $conn, $sql );

  if ( mysqli_num_rows( $result ) == 1 ) {
    $row = mysqli_fetch_assoc( $result );

    if ( password_verify( $pass, $row[ 'password' ] ) ) {
      $_SESSION[ 'user_id' ] = $row[ 'id' ];
      $_SESSION[ 'username' ] = $row[ 'username' ];
      $_SESSION[ 'role' ] = $row[ 'role' ];

      // Update last login
      $update = "UPDATE users SET last_login = NOW() WHERE id = " . $row[ 'id' ];
      mysqli_query( $conn, $update );

      header( "Location: dashboard.php" );
      exit();
    } else {
      $_SESSION[ 'error' ] = "Invalid username or password";
      header( "Location: login.php" );
      exit();
    }
  } else {
    $_SESSION[ 'error' ] = "Invalid username or password";
    header( "Location: login.php" );
    exit();
  }
}

mysqli_close( $conn );
?>
</body>
</html>