<?php
require_once __DIR__ . '/config_db.php';

if (!isManager() && !isSuperAdmin()) {
    die("Unauthorized");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $booking_id = (int)$_POST['booking_id'];
    
    try {
        $pdo->beginTransaction();
        
        // Get booking and parking details
        $stmt = $pdo->prepare("
            SELECT b.*, l.price_per_hour, b.slot_id 
            FROM bookings b 
            JOIN parking_locations l ON b.parking_id = l.parking_id 
            WHERE b.booking_id = ? FOR UPDATE
        ");
        $stmt->execute([$booking_id]);
        $booking = $stmt->fetch();
        
        if ($booking && $booking['status'] !== 'completed') {
            $start = new DateTime($booking['start_time']);
            $end = new DateTime(); // Now
            $interval = $start->diff($end);
            $hours = max(1, ceil($interval->h + ($interval->days * 24) + ($interval->i / 60)));
            
            $total_amount = $hours * $booking['price_per_hour'];
            
            // Update booking
            $stmt = $pdo->prepare("UPDATE bookings SET end_time = ?, amount = ?, status = 'completed' WHERE booking_id = ?");
            $stmt->execute([$end->format('H:i:s'), $total_amount, $booking_id]);
            
            // Free slot
            $stmt = $pdo->prepare("UPDATE parking_slots SET status = 'available' WHERE slot_id = ?");
            $stmt->execute([$booking['slot_id']]);
            
            $pdo->commit();
            header("Location: dashboard.php?msg=Exit processed. Amount: ₹" . $total_amount);
        } else {
            $pdo->rollBack();
            header("Location: dashboard.php?error=Booking already completed or not found.");
        }
    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        die("Error: " . $e->getMessage());
    }
}
?>



