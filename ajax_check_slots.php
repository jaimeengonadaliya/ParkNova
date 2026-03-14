<?php
require_once __DIR__ . '/config_db.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['status' => 'error', 'message' => 'Not logged in']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $parking_id = $_GET['parking_id'] ?? null;
    $date       = $_GET['date']        ?? null;
    $entry      = $_GET['entry']       ?? null;
    $exit       = $_GET['exit']        ?? null;
    $type       = $_GET['type']        ?? 'Car';

    if (!$parking_id || !$date || !$entry || !$exit) {
        echo json_encode(['status' => 'error', 'message' => 'Missing required parameters']);
        exit;
    }

    // Validate date is not in the past
    $todayDate = date('Y-m-d');
    if ($date < $todayDate) {
        echo json_encode(['status' => 'error', 'message' => 'Booking date cannot be in the past']);
        exit;
    }

    // For today: entry time must be at least the current time
    if ($date === $todayDate) {
        $nowTime = date('H:i');
        if ($entry <= $nowTime) {
            echo json_encode(['status' => 'error', 'message' => 'Entry time must be in the future. Current time is ' . date('h:i A') . ' IST.']);
            exit;
        }
    }

    // Entry must be before exit
    $entryDT = new DateTime("2000-01-01 $entry");
    $exitDT  = new DateTime("2000-01-01 $exit");
    if ($exitDT <= $entryDT) {
        echo json_encode(['status' => 'error', 'message' => 'Exit time must be after entry time']);
        exit;
    }

    // Map vehicle type to slot_type
    $slotTypeMap = ['Car' => '4W', 'Bike' => '2W', 'EV' => 'EV'];
    $slot_type   = $slotTypeMap[$type] ?? '4W';

    try {
        // Get all slots matching parking + slot type
        // Also include slots with empty/null slot_type as fallback
        $stmt = $pdo->prepare("
            SELECT slot_id, slot_number, status
            FROM parking_slots
            WHERE parking_id = :parking_id
              AND (slot_type = :slot_type OR slot_type = '' OR slot_type IS NULL)
            ORDER BY slot_number ASC
        ");
        $stmt->execute(['parking_id' => $parking_id, 'slot_type' => $slot_type]);
        $all_slots = $stmt->fetchAll();

        // Find slots already booked on the same date with overlapping times
        // Overlap condition: existing.start_time < requested.exit AND existing.end_time > requested.entry
        $conflictStmt = $pdo->prepare("
            SELECT slot_id
            FROM bookings
            WHERE parking_id   = :parking_id
              AND booking_date  = :date
              AND status       IN ('pending', 'completed')
              AND start_time    < :exit
              AND end_time      > :entry
        ");
        $conflictStmt->execute([
            'parking_id' => $parking_id,
            'date'       => $date,
            'entry'      => $entry,
            'exit'       => $exit
        ]);

        $conflictIds = $conflictStmt->fetchAll(PDO::FETCH_COLUMN, 0);
        $conflictSet = array_flip($conflictIds);

        $resultSlots = [];
        foreach ($all_slots as $slot) {
            // If slot is physically occupied/booked by manager, treat as unavailable
            $status = ($slot['status'] === 'occupied') ? 'occupied' :
                      (isset($conflictSet[$slot['slot_id']]) ? 'booked' : 'available');

            $resultSlots[] = [
                'slot_id'     => $slot['slot_id'],
                'slot_number' => $slot['slot_number'],
                'status'      => $status
            ];
        }

        echo json_encode(['status' => 'success', 'slots' => $resultSlots]);

    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
    }

} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
}



