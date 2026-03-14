<?php
require_once __DIR__ . '/config_db.php';
header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['status' => 'error', 'message' => 'Not authenticated.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $booking_id = $_POST['booking_id'];
    $user_id = $_SESSION['user_id'];
    
    try {
        $pdo->beginTransaction();

        // Check if booking exists and belongs to user
        $stmt = $pdo->prepare("SELECT slot_id, status FROM bookings WHERE booking_id = ? AND user_id = ?");
        $stmt->execute([$booking_id, $user_id]);
        $booking = $stmt->fetch();
        
        if (!$booking) {
            echo json_encode(['status' => 'error', 'message' => 'Booking not found or access denied.']);
            exit;
        }
        
        if ($booking['status'] === 'cancelled') {
            echo json_encode(['status' => 'error', 'message' => 'Booking is already cancelled.']);
            exit;
        }

        // Update booking status
        $stmt2 = $pdo->prepare("UPDATE bookings SET status = 'cancelled' WHERE booking_id = ?");
        $stmt2->execute([$booking_id]);
        
        // Free up the slot
        $stmt3 = $pdo->prepare("UPDATE parking_slots SET slot_status = 'available' WHERE slot_id = ?");
        $stmt3->execute([$booking['slot_id']]);

        // Update payment status if applicable
        $stmt4 = $pdo->prepare("UPDATE payments SET payment_status = 'Failed' WHERE booking_id = ?");
        $stmt4->execute([$booking_id]);

        $pdo->commit();
        echo json_encode(['status' => 'success', 'message' => 'Booking cancelled successfully.']);
        
    } catch (PDOException $e) {
        $pdo->rollBack();
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
}
?>



