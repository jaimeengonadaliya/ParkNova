<?php
require_once __DIR__ . '/config_db.php';

// Unset all session variables
$_SESSION = array();

// Destroy the session
session_destroy();

// Redirect to login page or home page
redirect($base_url . '/user_login.php');
exit();
?>



