<?php
require_once __DIR__ . '/config_db.php';
require_once __DIR__ . '/config_razorpay.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['status' => 'error', 'message' => 'Not authenticated']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $parking_id     = $_POST['parking_id']    ?? null;
    $slot_id        = $_POST['slot_id']        ?? null;
    $amount         = $_POST['amount']         ?? null;
    $date           = $_POST['booking_date']   ?? null;   // booking date (Y-m-d)
    $entry          = $_POST['entry']          ?? null;   // start_time (HH:MM)
    $exit           = $_POST['exit']           ?? null;   // end_time (HH:MM)
    $vehicle_type   = $_POST['vehicleType']    ?? null;
    $vehicle_number = $_POST['vehicleNumber']  ?? null;
    
    if (!$parking_id || !$slot_id || !$amount) {
        echo json_encode(['status' => 'error', 'message' => 'Missing payment details']);
        exit;
    }
    
    // Amount must be in paise (multiply by 100)
    $amountInPaise = $amount * 100;
    
    // Create cURL request to Razorpay API directly since SDK might not be installed via composer
    // Alternatively, if vendor/autoload.php is available with the SDK, we'd use that.
    // The prompt mentions 'vendor/autoload.php' but we'll use cURL to be safe and dependency-free.
    
    $receipt = 'rcptid_' . time() . '_' . $_SESSION['user_id'];
    
    $data = [
        "amount" => $amountInPaise,
        "currency" => RAZORPAY_CURRENCY,
        "receipt" => $receipt
    ];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://api.razorpay.com/v1/orders');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_USERPWD, RAZORPAY_KEY_ID . ':' . RAZORPAY_KEY_SECRET);
    $headers = [
        'Content-Type: application/json'
    ];
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    
    $response = curl_exec($ch);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        echo json_encode(['status' => 'error', 'message' => 'cURL Error: ' . $error]);
        exit;
    }
    
    $orderData = json_decode($response, true);
    
    if (isset($orderData['id'])) {
        // Return success with order ID and key for the frontend to open Checkout
        
        // We also send back a payload token to verify against tampering later
        $tempPayload = [
            'parking_id' => $parking_id,
            'slot_id' => $slot_id,
            'amount' => $amount,
            'booking_date' => $date,
            'start_time' => $entry,
            'end_time' => $exit,
            'vehicle_type' => $vehicle_type,
            'vehicle_number' => $vehicle_number
        ];
        
        // Using session array to temporarily hold pending booking details tied to this order ID
        $_SESSION['pending_orders'][$orderData['id']] = $tempPayload;
        
        echo json_encode([
            'status' => 'success', 
            'order_id' => $orderData['id'], 
            'amount' => $amountInPaise,
            'key' => RAZORPAY_KEY_ID,
            'booking_payload' => $tempPayload // Sending back just for reference if needed
        ]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Razorpay Order Creation Failed: ' . json_encode($orderData)]);
    }
}



