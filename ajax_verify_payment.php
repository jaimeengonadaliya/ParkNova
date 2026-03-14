<?php
require_once __DIR__ . '/config_db.php';
require_once __DIR__ . '/config_razorpay.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['status' => 'error', 'message' => 'Not authenticated']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $payment_id = $_POST['razorpay_payment_id'] ?? null;
    $order_id   = $_POST['razorpay_order_id']   ?? null;
    $signature  = $_POST['razorpay_signature']  ?? null;
    $payload_json = $_POST['payload']           ?? null;

    if (!$payment_id || !$order_id || !$signature) {
        echo json_encode(['status' => 'error', 'message' => 'Missing payment verification data']);
        exit;
    }

    // Verify Signature
    $expectedSignature = hash_hmac('sha256', $order_id . '|' . $payment_id, RAZORPAY_KEY_SECRET);

    if ($expectedSignature !== $signature) {
        echo json_encode(['status' => 'error', 'message' => 'Signature Verification Failed']);
        exit;
    }

    // Get booking payload
    $payload = null;
    if (isset($_SESSION['pending_orders'][$order_id])) {
        $payload = $_SESSION['pending_orders'][$order_id];
    } elseif ($payload_json) {
        $payload = json_decode($payload_json, true);
    }

    if (!$payload) {
        echo json_encode(['status' => 'error', 'message' => 'Booking details lost. Contact support with Payment ID: ' . $payment_id]);
        exit;
    }

    try {
        $pdo->beginTransaction();

        // Insert booking — use booking_date + start_time + end_time as separate columns
        $stmt = $pdo->prepare("
            INSERT INTO bookings
                (user_id, parking_id, slot_id, vehicle_number, vehicle_type, booking_date, start_time, end_time, amount, status)
            VALUES
                (?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')
        ");
        $stmt->execute([
            $_SESSION['user_id'],
            $payload['parking_id'],
            $payload['slot_id'],
            strtoupper($payload['vehicle_number']),
            $payload['vehicle_type'],
            $payload['booking_date'],     // DATE  e.g. 2026-03-20
            $payload['start_time'],       // TIME  e.g. 10:00
            $payload['end_time'],         // TIME  e.g. 12:30
            $payload['amount']
        ]);

        $booking_id = $pdo->lastInsertId();

        // Mark slot as booked
        $pdo->prepare("UPDATE parking_slots SET status = 'booked' WHERE slot_id = ?")
            ->execute([$payload['slot_id']]);

        // Insert Payment record (using actual column names from DB)
        $stmt = $pdo->prepare("
            INSERT INTO payments (booking_id, payment_method, payment_status)
            VALUES (?, 'Online', 'Success')
        ");
        $stmt->execute([$booking_id]);

        $pdo->commit();

        // Clear session pending order
        unset($_SESSION['pending_orders'][$order_id]);

        echo json_encode(['status' => 'success', 'booking_id' => $booking_id]);

    } catch (PDOException $e) {
        $pdo->rollBack();
        echo json_encode(['status' => 'error', 'message' => 'Database failed but payment was captured. Payment ID: ' . $payment_id . ' — Error: ' . $e->getMessage()]);
    }
}



