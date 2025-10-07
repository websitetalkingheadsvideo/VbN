<?php
session_start();
if ( !isset( $_SESSION[ 'user_id' ] ) ) {
  header( "Location: login.php" );
  exit();
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Dashboard</title>
</head>
<body>
<h2>Welcome, <?php echo $_SESSION['username']; ?>!</h2>
<p>Role: <?php echo $_SESSION['role']; ?></p>

<div class="dashboard-links">
    <h3>Character Management</h3>
    <a href="lotn_char_create.php" class="dashboard-link">⚜ Create New Character</a>
</div>

<a href="logout.php">Logout</a>
</body>
</html>