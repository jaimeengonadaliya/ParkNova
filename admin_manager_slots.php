<?php
require_once __DIR__ . '/config_db.php';

if (!isLoggedIn() || !isManager()) {
    redirect($base_url . '/user_login.php');
}

$manager_id = $_SESSION['user_id'];

// Get this manager's assigned parking lot
$stmt = $pdo->prepare("SELECT * FROM parking_locations WHERE manager_id = ?");
$stmt->execute([$manager_id]);
$manager_parking = $stmt->fetch();

if (!$manager_parking) {
    redirect($base_url . '/admin_manager_dashboard.php');
}

$parking_id = $manager_parking['parking_id'];
$success = ''; $error = '';

// Handle add slot
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_single') {
    $slot_number = strtoupper(trim($_POST['slot_number']));
    if (preg_match('/^[A-Za-z0-9\-]{2,}$/', $slot_number)) {
        try {
            $check = $pdo->prepare("SELECT slot_id FROM parking_slots WHERE parking_id = ? AND slot_number = ?");
            $check->execute([$parking_id, $slot_number]);
            if ($check->rowCount() > 0) {
                $error = "Slot '$slot_number' already exists.";
            } else {
                $pdo->prepare("INSERT INTO parking_slots (parking_id, slot_number, status) VALUES (?, ?, 'available')")
                    ->execute([$parking_id, $slot_number]);
                $success = "Slot '$slot_number' added successfully.";
            }
        } catch (PDOException $e) {
            $error = "Failed to add slot.";
        }
    } else {
        $error = "Invalid slot name.";
    }
}

// Handle delete slot
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $slot_id = (int)$_POST['slot_id'];
    // Only delete if it belongs to manager's parking and is available
    $check = $pdo->prepare("SELECT * FROM parking_slots WHERE slot_id = ? AND parking_id = ? AND status = 'available'");
    $check->execute([$slot_id, $parking_id]);
    if ($check->rowCount() > 0) {
        $pdo->prepare("DELETE FROM parking_slots WHERE slot_id = ?")->execute([$slot_id]);
        $success = "Slot deleted.";
    } else {
        $error = "Cannot delete: Slot not found or is currently booked/reserved.";
    }
}

// Fetch all slots
$stmt = $pdo->prepare("SELECT * FROM parking_slots WHERE parking_id = ? ORDER BY slot_number ASC");
$stmt->execute([$parking_id]);
$slots = $stmt->fetchAll();

require_once __DIR__ . '/includes_header.php';
require_once __DIR__ . '/includes_manager_navbar.php';
?>

<div class="container-fluid py-4 px-lg-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold mb-0 text-primary"><i class="fa-solid fa-grip me-2"></i>Slot Management</h3>
            <p class="text-secondary small fw-medium">Manage parking slots for <strong class="text-info"><?= htmlspecialchars($manager_parking['parking_name']) ?></strong></p>
        </div>
    </div>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger border-0 border-start border-4 border-danger shadow-sm mb-4">
            <i class="fa-solid fa-triangle-exclamation me-2"></i><?= $error ?>
        </div>
    <?php endif; ?>
    <?php if (!empty($success)): ?>
        <div class="alert alert-success border-0 border-start border-4 border-success shadow-sm mb-4">
            <i class="fa-solid fa-check-circle me-2"></i><?= $success ?>
        </div>
    <?php endif; ?>

    <div class="row g-4">
        <!-- Add Slot Form -->
        <div class="col-lg-4">
            <div class="card-3d mb-4">
                <div class="p-4 border-bottom border-secondary border-opacity-25 bg-surface">
                    <h5 class="fw-bold mb-0 text-primary"><i class="fa-solid fa-plus-circle me-2"></i>Add Single Slot</h5>
                </div>
                <div class="card-body p-4 bg-surface">
                    <form action="" method="POST" class="needs-validation" novalidate>
                        <input type="hidden" name="action" value="add_single">
                        <div class="mb-3">
                            <label class="form-label text-secondary fw-bold small text-uppercase">Slot Number/Name</label>
                            <input type="text" name="slot_number" class="form-control" placeholder="e.g. A1, B12" required style="text-transform: uppercase;" pattern="[A-Za-z0-9-]+" minlength="2">
                            <div class="invalid-feedback">Please enter a valid slot name (min 2 chars).</div>
                        </div>
                        <button type="submit" class="btn-primary-3d w-100 justify-content-center"><i class="fa-solid fa-plus me-2"></i>Add Slot</button>
                    </form>
                </div>
            </div>

            <div class="card bg-info bg-opacity-10 border-0 rounded-4">
                <div class="card-body p-4">
                    <h6 class="fw-bold text-info border-bottom border-info border-opacity-25 pb-2 mb-3"><i class="fa-solid fa-circle-info me-2"></i>Legend & Rules</h6>
                    <ul class="list-unstyled mb-0 small text-dark">
                        <li class="mb-2"><span class="d-inline-block bg-success rounded-circle me-2" style="width: 12px; height: 12px;"></span> <strong>Available:</strong> Can be deleted.</li>
                        <li class="mb-2"><span class="d-inline-block bg-danger rounded-circle me-2" style="width: 12px; height: 12px;"></span> <strong>Booked:</strong> Cannot be deleted.</li>
                        <li><span class="d-inline-block bg-warning rounded-circle me-2" style="width: 12px; height: 12px;"></span> <strong>Reserved:</strong> Admin lock.</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Slot Grid -->
        <div class="col-lg-8">
            <div class="glass-panel h-100">
                <div class="bg-transparent border-bottom border-secondary border-opacity-25 p-4">
                    <h5 class="fw-bold mb-0 text-primary"><i class="fa-solid fa-grip me-2"></i>Visual Grid Layout</h5>
                </div>
                <div class="p-4">
                    <?php if (count($slots) === 0): ?>
                        <div class="text-center py-5">
                            <div class="bg-white d-inline-block p-4 rounded-circle mb-3 shadow-sm"><i class="fa-solid fa-grip fa-3x text-muted opacity-50"></i></div>
                            <h5 class="text-muted fw-bold">No slots added yet</h5>
                            <p class="text-muted small">Use the form on the left to add parking slots.</p>
                        </div>
                    <?php else: ?>
                        <div class="slot-grid admin-mode bg-white border rounded-4 shadow-sm p-4">
                            <?php foreach ($slots as $slot): ?>
                                <?php
                                    if ($slot['status'] === 'available') $statusClass = 'available';
                                    elseif ($slot['status'] === 'booked') $statusClass = 'booked';
                                    else $statusClass = 'reserved';
                                ?>
                                <div class="position-relative group">
                                    <div class="parking-slot <?= $statusClass ?> w-100 fs-5" title="Status: <?= ucfirst($slot['status']) ?>">
                                        <?= htmlspecialchars($slot['slot_number']) ?>
                                    </div>
                                    <?php if ($slot['status'] === 'available'): ?>
                                        <form action="" method="POST" class="position-absolute top-0 start-100 translate-middle" style="z-index: 5;" onsubmit="return confirm('Delete slot <?= $slot['slot_number'] ?>?');">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="slot_id" value="<?= $slot['slot_id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-danger rounded-circle p-1 shadow" style="width: 24px; height: 24px; display: flex; align-items: center; justify-content: center;">
                                                <i class="fa-solid fa-xmark" style="font-size: 0.7rem;"></i>
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
echo '<script src="' . $base_url . '/js_admin.js"></script>';
require_once __DIR__ . '/includes_footer.php';
?>



