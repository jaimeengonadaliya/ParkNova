<?php
require_once __DIR__ . '/config_db.php';
if (!isLoggedIn() || !isManager()) redirect($base_url . '/user_login.php');

$manager_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM parking_locations WHERE manager_id = ?");
$stmt->execute([$manager_id]);
$manager_parking = $stmt->fetch();
if (!$manager_parking) die('<div class="container py-5 text-center"><h3>No parking assigned.</h3></div>');

$parking_id = $manager_parking['parking_id'];
$success = ''; $error = '';

// Handle Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'add_single') {
        $slot_number = strtoupper(trim($_POST['slot_number']));
        $vehicle_type = $_POST['vehicle_type'];
        $stmt = $pdo->prepare("SELECT slot_id FROM parking_slots WHERE parking_id = ? AND slot_number = ?");
        $stmt->execute([$parking_id, $slot_number]);
        if ($stmt->rowCount() > 0) {
            $error = "Slot number already exists in this parking area.";
        } else {
            $pdo->prepare("INSERT INTO parking_slots (parking_id, slot_number, slot_type, status) VALUES (?, ?, ?, 'available')")->execute([$parking_id, $slot_number, $vehicle_type]);
            $success = "Slot $slot_number added successfully.";
        }
    } elseif ($_POST['action'] === 'delete') {
        $slot_id = (int)$_POST['slot_id'];
        $stmt = $pdo->prepare("SELECT status, parking_id FROM parking_slots WHERE slot_id = ?");
        $stmt->execute([$slot_id]);
        $slot = $stmt->fetch();
        if ($slot && $slot['parking_id'] == $parking_id) {
            $today = date('Y-m-d');
            $active_bookings = $pdo->prepare("SELECT COUNT(*) FROM bookings WHERE slot_id = ? AND status NOT IN ('cancelled') AND created_at >= ?");
            $active_bookings->execute([$slot_id, $today]);
            $has_bookings = $active_bookings->fetchColumn() > 0;

            if ($slot['status'] === 'reserved') {
                $error = "Cannot delete a reserved slot.";
            } elseif ($has_bookings) {
                $error = "Cannot delete this slot because it has active future or current bookings.";
            } else {
                $pdo->prepare("DELETE FROM parking_slots WHERE slot_id = ?")->execute([$slot_id]);
                $success = "Slot deleted successfully.";
            }
        } else {
            $error = "Slot not found in your parking area.";
        }
    }
}

$stmt = $pdo->prepare("SELECT * FROM parking_slots WHERE parking_id = ? ORDER BY slot_number ASC");
$stmt->execute([$parking_id]);
$slots = $stmt->fetchAll();

$today = date('Y-m-d');
$now   = date('H:i:s');
foreach ($slots as &$slot) {
    if ($slot['status'] === 'reserved') continue;

    // A slot is "booked" today if there's an active booking for today that overlaps NOW or future
    $chk = $pdo->prepare("
        SELECT COUNT(*) FROM bookings
        WHERE slot_id     = ?
          AND status     NOT IN ('cancelled')
          AND booking_date = ?
          AND end_time    > ?
    ");
    $chk->execute([$slot['slot_id'], $today, $now]);

    if ($chk->fetchColumn() > 0) {
        $slot['status'] = 'booked';
    } else {
        $slot['status'] = 'available';
    }
}
unset($slot);

require_once __DIR__ . '/includes_header.php';
require_once __DIR__ . '/includes_manager_navbar.php';
?>

<div class="container-fluid py-4 px-lg-5">
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= $base_url ?>/manager_dashboard.php" class="text-decoration-none text-primary fw-medium">Dashboard</a></li>
            <li class="breadcrumb-item active fw-bold">Manage Slots</li>
        </ol>
    </nav>

    <div class="row align-items-center mb-4">
        <div class="col-md-8">
            <h3 class="fw-bold mb-0 text-primary"><i class="fa-solid fa-parking me-2"></i>Manage Slots: <?= htmlspecialchars($manager_parking['parking_name']) ?></h3>
            <p class="text-secondary small fw-medium"><?= htmlspecialchars($manager_parking['city']) ?> — Configure individual parking spaces.</p>
        </div>
        <div class="col-md-4 text-md-end">
            <div class="glass-panel d-inline-block p-2 px-4">
                <span class="text-secondary small fw-medium me-2">Capacity:</span>
                <span class="fs-5 fw-bold <?= count($slots) > $manager_parking['total_slots'] ? 'text-danger' : 'text-primary' ?>">
                    <?= count($slots) ?> / <?= $manager_parking['total_slots'] ?>
                </span>
            </div>
        </div>
    </div>

    <?php if ($error): ?><div class="alert glass-panel border-start border-4 border-danger mb-4"><i class="fa-solid fa-triangle-exclamation me-2 text-danger"></i><?= $error ?></div><?php endif; ?>
    <?php if ($success): ?><div class="alert glass-panel border-start border-4 border-success mb-4"><i class="fa-solid fa-check-circle me-2 text-success"></i><?= $success ?></div><?php endif; ?>

    <div class="row g-4">
        <div class="col-lg-4">
            <div class="card-3d mb-4">
                <div class="p-4 border-bottom border-secondary border-opacity-25 bg-surface">
                    <h5 class="fw-bold mb-0 text-primary"><i class="fa-solid fa-plus-circle me-2"></i>Add Single Slot</h5>
                </div>
                <div class="card-body p-4 bg-surface">
                    <form action="" method="POST" class="needs-validation" novalidate>
                        <input type="hidden" name="action" value="add_single">
                        
                        <div class="mb-3">
                            <label class="form-label text-secondary fw-bold small text-uppercase">Vehicle Type</label>
                            <select name="vehicle_type" class="form-select" required>
                                <option value="Car">Car</option>
                                <option value="Bike">Bike</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-secondary fw-bold small text-uppercase">Slot Number/Name</label>
                            <input type="text" name="slot_number" class="form-control" placeholder="e.g. A1, B12, VIP-1" required style="text-transform:uppercase" pattern="[A-Za-z0-9-]+" minlength="2">
                            <div class="invalid-feedback">Min 2 chars, letters/numbers/hyphens only.</div>
                        </div>
                        <button type="submit" class="btn-primary-3d w-100 justify-content-center"><i class="fa-solid fa-plus me-2"></i>Add Slot</button>
                    </form>
                </div>
            </div>
            <div class="glass-panel p-4">
                <h6 class="fw-bold text-primary border-bottom border-secondary border-opacity-25 pb-2 mb-3"><i class="fa-solid fa-circle-info me-2"></i>Legend</h6>
                <ul class="list-unstyled mb-0 small">
                    <li class="mb-2"><span class="d-inline-block bg-success rounded-circle me-2" style="width:12px;height:12px;"></span><strong>Available:</strong> Can be booked & deleted.</li>
                    <li class="mb-2"><span class="d-inline-block bg-danger rounded-circle me-2" style="width:12px;height:12px;"></span><strong>Booked:</strong> User reserved. Cannot delete.</li>
                    <li><span class="d-inline-block bg-warning rounded-circle me-2" style="width:12px;height:12px;"></span><strong>Reserved:</strong> Admin locked slot.</li>
                </ul>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="glass-panel h-100">
                <div class="bg-transparent border-bottom border-secondary border-opacity-25 p-4">
                    <h5 class="fw-bold mb-0 text-primary"><i class="fa-solid fa-grip me-2"></i>Visual Grid Layout</h5>
                </div>
                <div class="p-4">
                    <?php if (count($slots) === 0): ?>
                        <div class="text-center py-5 text-secondary">
                            <i class="fa-solid fa-grip fa-3x opacity-25 mb-3 d-block"></i>
                            <h5 class="fw-bold">No slots added yet</h5>
                            <p class="small">Use the form on the left to add parking slots.</p>
                        </div>
                    <?php else: ?>
                        <div class="slot-grid admin-mode bg-white bg-opacity-5 border border-secondary border-opacity-25 rounded-4 p-4">
                            <?php foreach ($slots as $slot): ?>
                                <?php
                                    $sc = ($slot['status'] ?? 'available') === 'available' ? 'available' : (($slot['status'] ?? '') === 'booked' ? 'booked' : 'reserved');
                                ?>
                                <div class="position-relative group">
                                    <div class="parking-slot <?= $sc ?> w-100 fs-5" title="Status: <?= ucfirst($slot['status'] ?? 'available') ?>">
                                        <?= htmlspecialchars($slot['slot_number']) ?>
                                        <i class="fa-solid <?= ($slot['slot_type'] ?? '4W') === '2W' ? 'fa-motorcycle' : (($slot['slot_type'] ?? '') === 'EV' ? 'fa-plug-circle-bolt' : 'fa-car') ?> position-absolute fs-6 opacity-50" style="bottom: 5px; right: 5px;"></i>
                                    </div>
                                    <?php if (($slot['status'] ?? 'available') === 'available'): ?>
                                        <form action="" method="POST" class="position-absolute top-0 start-100 translate-middle" style="z-index:5" onsubmit="return confirm('Delete slot <?= $slot['slot_number'] ?>?')">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="slot_id" value="<?= $slot['slot_id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-danger rounded-circle p-1 shadow" style="width:24px;height:24px;display:flex;align-items:center;justify-content:center;">
                                                <i class="fa-solid fa-xmark" style="font-size:0.7rem"></i>
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

<?php require_once __DIR__ . '/includes_footer.php'; ?>



