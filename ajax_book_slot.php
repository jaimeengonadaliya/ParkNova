<?php
session_start();
require_once __DIR__ . '/config_db.php';

if (!isLoggedIn()) {
    die("Unauthorized");
}

$user_id = $_SESSION['user_id'];
$slot_id = $_POST['slot_id'];
$vehicle_number = $_POST['vehicle_number'] ?? 'TEST-123';

try {
    $pdo->beginTransaction();

    // Check status with lock
    $stmt = $pdo->prepare("SELECT status, parking_id FROM parking_slots WHERE slot_id = ? FOR UPDATE");
    $stmt->execute([$slot_id]);
    $slot = $stmt->fetch();

    if ($slot && $slot['status'] == 'available') {
        // Create booking
        $stmt = $pdo->prepare("INSERT INTO bookings (user_id, parking_id, slot_id, vehicle_number, status) VALUES (?, ?, ?, ?, 'pending')");
        $stmt->execute([$user_id, $slot['parking_id'], $slot_id, $vehicle_number]);

        // Update slot status
        $stmt = $pdo->prepare("UPDATE parking_slots SET status = 'booked' WHERE slot_id = ?");
        $stmt->execute([$slot_id]);

        $pdo->commit();
        echo "success";
    } else {
        $pdo->rollBack();
        echo "already booked";
    }
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo "error: " . $e->getMessage();
}
?>



