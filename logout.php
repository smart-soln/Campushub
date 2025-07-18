<?php
session_start();

// Database configuration
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'campushub1';

// Destroy session
session_unset();
session_destroy();

// Redirect to login page
header("Location: admin_login.php");
exit();
?>