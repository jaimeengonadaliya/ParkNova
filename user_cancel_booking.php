<?php
require_once __DIR__ . '/config_db.php';
// Cancel booking is handled via AJAX in ajax_cancel_booking.php
// This is just a fallback redirect
redirect($base_url . '/user_history.php');
?>



