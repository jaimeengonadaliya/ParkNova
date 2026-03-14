<?php
// Admin uses the same global login page. Redirecting to user_login.php
require_once __DIR__ . '/config_db.php';
redirect($base_url . '/user_login.php');
?>



