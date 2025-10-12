<?php
/**
 * Logout - Valley by Night
 * Destroys session and redirects to login page
 */
session_start();
session_destroy();
header("Location: login.php");
exit();
?>
