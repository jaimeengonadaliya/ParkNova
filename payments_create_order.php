<?php
// NOTE: This requires Razorpay PHP SDK: composer require razorpay/razorpay
// require '../vendor/autoload.php';

// use Razorpay\Api\Api;

$key="rzp_test_xxxxxx";
$secret="xxxxxxx";

// Mock Order for demonstration if SDK not present
$order = [
    'id' => 'order_'.bin2hex(random_bytes(8)),
    'amount' => 50000,
    'currency' => 'INR',
    'status' => 'created',
    'created_at' => time()
];

/*
$api=new Api($key,$secret);

$order=$api->order->create([
'amount'=>50000,
'currency'=>'INR'
]);
*/

header('Content-Type: application/json');
echo json_encode($order);
?>



