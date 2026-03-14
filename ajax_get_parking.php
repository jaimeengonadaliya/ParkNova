<?php
require_once __DIR__ . '/config_db.php';
header('Content-Type: application/json');

$query = isset($_GET['q']) ? trim($_GET['q']) : '';

try {
    if (!empty($query)) {
        $stmt = $pdo->prepare("SELECT * FROM parking_locations WHERE city LIKE ? OR address LIKE ? OR parking_name LIKE ?");
        $term = "%$query%";
        $stmt->execute([$term, $term, $term]);
    } else {
        $stmt = $pdo->prepare("SELECT * FROM parking_locations");
        $stmt->execute();
    }
    
    $results = $stmt->fetchAll();
    echo json_encode(['status' => 'success', 'data' => $results]);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>



