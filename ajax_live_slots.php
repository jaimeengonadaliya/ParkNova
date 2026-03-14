<?php
require_once __DIR__ . '/config_db.php';

$parking_id = isset($_GET['parking_id']) ? (int)$_GET['parking_id'] : 0;

if (!$parking_id) {
    echo json_encode([]);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT slot_id, slot_number, status FROM parking_slots WHERE parking_id = ?");
    $stmt->execute([$parking_id]);
    $slots = $stmt->fetchAll();
    
    header('Content-Type: application/json');
    echo json_encode($slots);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>



