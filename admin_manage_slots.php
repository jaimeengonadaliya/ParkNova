<?php
require_once __DIR__ . '/config_db.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect($base_url . '/user_login.php');
}

if (!isset($_GET['parking_id'])) {
    redirect($base_url . '/admin_manage_parking.php');
}

$parking_id = (int)$_GET['parking_id'];

$stmt = $pdo->prepare("SELECT * FROM parking_locations WHERE parking_id = ?");
$stmt->execute([$parking_id]);
$parking = $stmt->fetch();

if (!$parking) redirect($base_url . '/admin_manage_parking.php');

$success = ''; $error = '';

// --- ACTIONS ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {

    if ($_POST['action'] === 'add_single') {
        $slot_number = strtoupper(trim($_POST['slot_number']));
        $slot_type   = $_POST['slot_type'] ?? '4W';
        $stmt = $pdo->prepare("SELECT slot_id FROM parking_slots WHERE parking_id = ? AND slot_number = ?");
        $stmt->execute([$parking_id, $slot_number]);
        if ($stmt->rowCount() > 0) {
            $error = "Slot <strong>$slot_number</strong> already exists.";
        } else {
            $pdo->prepare("INSERT INTO parking_slots (parking_id, slot_number, slot_type, status) VALUES (?, ?, ?, 'available')")->execute([$parking_id, $slot_number, $slot_type]);
            $success = "Slot <strong>$slot_number</strong> added.";
        }
    }

    elseif ($_POST['action'] === 'bulk_generate') {
        $count     = min((int)$_POST['bulk_count'], 200);
        $slot_type = $_POST['bulk_slot_type'] ?? '4W';
        $prefix    = strtoupper(trim($_POST['prefix'] ?? ''));
        $rows      = ['A','B','C','D','E','F','G','H','I','J'];

        $existing = $pdo->prepare("SELECT COUNT(*) FROM parking_slots WHERE parking_id = ?");
        $existing->execute([$parking_id]);
        $existing_count = (int)$existing->fetchColumn();

        $added = 0;
        $row_i = 0; $col = 1;

        // Advance to next free row based on existing slots
        $skip_rows = intdiv($existing_count, 5);
        $row_i = $skip_rows;

        for ($s = 0; $s < $count; $s++) {
            if ($row_i >= count($rows)) break;
            $slot_number = ($prefix ? $prefix . '-' : '') . $rows[$row_i] . $col;
            $check = $pdo->prepare("SELECT slot_id FROM parking_slots WHERE parking_id = ? AND slot_number = ?");
            $check->execute([$parking_id, $slot_number]);
            if ($check->rowCount() === 0) {
                $pdo->prepare("INSERT INTO parking_slots (parking_id, slot_number, slot_type, status) VALUES (?, ?, ?, 'available')")->execute([$parking_id, $slot_number, $slot_type]);
                $added++;
            }
            $col++;
            if ($col > 5) { $col = 1; $row_i++; }
        }
        $success = "<strong>$added</strong> slots generated successfully.";
    }

    elseif ($_POST['action'] === 'delete') {
        $slot_id = (int)$_POST['slot_id'];
        $stmt    = $pdo->prepare("SELECT status FROM parking_slots WHERE slot_id = ?");
        $stmt->execute([$slot_id]);
        $status  = $stmt->fetchColumn();
        if ($status !== 'available') {
            $error = "Cannot delete a booked/occupied slot.";
        } else {
            $pdo->prepare("DELETE FROM parking_slots WHERE slot_id = ?")->execute([$slot_id]);
            $success = "Slot deleted.";
        }
    }

    elseif ($_POST['action'] === 'change_status') {
        $slot_id    = (int)$_POST['slot_id'];
        $new_status = in_array($_POST['new_status'], ['available', 'booked', 'occupied']) ? $_POST['new_status'] : 'available';
        $pdo->prepare("UPDATE parking_slots SET status = ? WHERE slot_id = ? AND parking_id = ?")->execute([$new_status, $slot_id, $parking_id]);
        $success = "Slot status updated.";
    }
}

// Fetch slots
$stmt = $pdo->prepare("SELECT * FROM parking_slots WHERE parking_id = ? ORDER BY slot_number ASC");
$stmt->execute([$parking_id]);
$slots = $stmt->fetchAll();

$total   = count($slots);
$avail   = count(array_filter($slots, fn($s) => $s['status'] === 'available'));
$booked  = count(array_filter($slots, fn($s) => $s['status'] === 'booked'));
$occup   = count(array_filter($slots, fn($s) => $s['status'] === 'occupied'));

require_once __DIR__ . '/includes_header.php';
require_once __DIR__ . '/includes_admin_navbar.php';
?>

<div class="container-fluid py-4 px-lg-5">
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= $base_url ?>/admin_manage_parking.php" class="text-primary fw-medium text-decoration-none"><i class="fa-solid fa-building me-1"></i>Parking Lots</a></li>
            <li class="breadcrumb-item active fw-bold"><?= htmlspecialchars($parking['parking_name']) ?></li>
        </ol>
    </nav>

    <div class="d-flex flex-wrap justify-content-between align-items-start mb-4 gap-3">
        <div>
            <h3 class="fw-bold text-primary mb-1"><i class="fa-solid fa-grip me-2"></i>Slot Management</h3>
            <p class="text-secondary small mb-0"><?= htmlspecialchars($parking['parking_name']) ?> · <?= htmlspecialchars($parking['city']) ?></p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <span class="badge bg-success bg-opacity-15 text-success border border-success border-opacity-25 px-3 py-2 fs-6"><i class="fa-solid fa-circle-check me-1"></i><?= $avail ?> Available</span>
            <span class="badge bg-danger bg-opacity-15 text-danger border border-danger border-opacity-25 px-3 py-2 fs-6"><i class="fa-solid fa-ban me-1"></i><?= $booked ?> Booked</span>
            <span class="badge bg-warning bg-opacity-15 text-warning border border-warning border-opacity-25 px-3 py-2 fs-6"><i class="fa-solid fa-car me-1"></i><?= $occup ?> Occupied</span>
        </div>
    </div>

    <?php if (!empty($error)): ?>
        <div class="alert glass-panel border-start border-4 border-danger mb-4"><i class="fa-solid fa-triangle-exclamation me-2 text-danger"></i><?= $error ?></div>
    <?php endif; ?>
    <?php if (!empty($success)): ?>
        <div class="alert glass-panel border-start border-4 border-success mb-4"><i class="fa-solid fa-check-circle me-2 text-success"></i><?= $success ?></div>
    <?php endif; ?>

    <div class="row g-4">
        <!-- LEFT: Controls -->
        <div class="col-lg-4">
            <!-- Single Slot Add -->
            <div class="card-3d mb-4">
                <div class="p-4 border-bottom border-secondary border-opacity-25">
                    <h6 class="fw-bold text-primary mb-0"><i class="fa-solid fa-plus-circle me-2"></i>Add Single Slot</h6>
                </div>
                <div class="card-body p-4 bg-surface">
                    <form method="POST" class="needs-validation" novalidate>
                        <input type="hidden" name="action" value="add_single">
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-secondary text-uppercase">Slot Name</label>
                            <input type="text" name="slot_number" class="form-control" placeholder="e.g. A1, VIP-2" required style="text-transform:uppercase">
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-secondary text-uppercase">Slot Type</label>
                            <select name="slot_type" class="form-select">
                                <option value="4W">4-Wheeler (Car)</option>
                                <option value="2W">2-Wheeler (Bike)</option>
                                <option value="EV">EV Charging</option>
                            </select>
                        </div>
                        <button type="submit" class="btn-primary-3d w-100 justify-content-center"><i class="fa-solid fa-plus me-2"></i>Add Slot</button>
                    </form>
                </div>
            </div>

            <!-- Bulk Generate -->
            <div class="card-3d mb-4">
                <div class="p-4 border-bottom border-secondary border-opacity-25" style="background: linear-gradient(135deg,rgba(16,185,129,0.08),rgba(5,150,105,0.08));">
                    <h6 class="fw-bold text-success mb-0"><i class="fa-solid fa-wand-magic-sparkles me-2"></i>Bulk Generate Slots</h6>
                </div>
                <div class="card-body p-4 bg-surface">
                    <form method="POST">
                        <input type="hidden" name="action" value="bulk_generate">
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-secondary text-uppercase">Number of Slots</label>
                            <input type="number" name="bulk_count" class="form-control" value="10" min="1" max="200">
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-secondary text-uppercase">Slot Type</label>
                            <select name="bulk_slot_type" class="form-select">
                                <option value="4W">4-Wheeler (Car)</option>
                                <option value="2W">2-Wheeler (Bike)</option>
                                <option value="EV">EV Charging</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-secondary text-uppercase">Custom Prefix (optional)</label>
                            <input type="text" name="prefix" class="form-control" placeholder="e.g. EV, VIP">
                        </div>
                        <button type="submit" class="btn btn-success rounded-pill w-100 fw-bold py-2"><i class="fa-solid fa-layer-group me-2"></i>Generate Now</button>
                    </form>
                </div>
            </div>

            <!-- Legend -->
            <div class="glass-panel p-4 rounded-4">
                <h6 class="fw-bold text-primary mb-3">Slot Color Legend</h6>
                <div class="d-flex flex-column gap-2 small">
                    <div><span class="d-inline-block bg-success rounded-circle me-2" style="width:12px;height:12px;"></span><strong>Green</strong> — Available</div>
                    <div><span class="d-inline-block bg-danger rounded-circle me-2" style="width:12px;height:12px;"></span><strong>Red</strong> — Booked</div>
                    <div><span class="d-inline-block bg-warning rounded-circle me-2" style="width:12px;height:12px;"></span><strong>Yellow</strong> — Occupied</div>
                    <div><span class="d-inline-block bg-secondary rounded-circle me-2" style="width:12px;height:12px;"></span><strong>Gray</strong> — Maintenance</div>
                </div>
            </div>
        </div>

        <!-- RIGHT: Slot Grid -->
        <div class="col-lg-8">
            <div class="glass-panel h-100">
                <div class="p-4 border-bottom border-secondary border-opacity-25 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold text-primary mb-0"><i class="fa-solid fa-grip me-2"></i>Live Visual Grid</h5>
                    <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-3 py-2">
                        <?= $total ?> / <?= $parking['total_slots'] ?> configured
                    </span>
                </div>
                <div class="p-4">
                    <?php if (empty($slots)): ?>
                        <div class="text-center py-5">
                            <i class="fa-solid fa-grip fa-3x text-muted opacity-25 d-block mb-3"></i>
                            <h5 class="text-muted">No slots configured yet</h5>
                            <p class="text-muted small">Use the Bulk Generate tool on the left to create slots instantly.</p>
                        </div>
                    <?php else: ?>
                        <div class="slot-grid admin-mode bg-light rounded-4 p-3">
                            <?php foreach ($slots as $slot):
                                $sc = $slot['status'] === 'available' ? 'available' : ($slot['status'] === 'booked' ? 'booked' : 'reserved');
                                $typeIcon = $slot['slot_type'] === '2W' ? 'fa-motorcycle' : ($slot['slot_type'] === 'EV' ? 'fa-plug-circle-bolt' : 'fa-car');
                            ?>
                                <div class="position-relative" style="margin:4px;">
                                    <!-- Status Change Form on hover -->
                                    <div class="d-flex flex-column align-items-center">
                                        <div class="parking-slot <?= $sc ?> w-100 text-center" style="font-size:0.75rem; padding:6px;" title="<?= $slot['slot_type'] ?> | <?= ucfirst($slot['status']) ?>">
                                            <i class="fa-solid <?= $typeIcon ?> d-block mb-1 opacity-50" style="font-size:0.8rem;"></i>
                                            <?= htmlspecialchars($slot['slot_number']) ?>
                                        </div>
                                        <?php if ($slot['status'] === 'available'): ?>
                                            <form method="POST" class="mt-1" style="width:100%">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="slot_id" value="<?= $slot['slot_id'] ?>">
                                                <button type="submit" class="btn btn-danger btn-sm w-100 rounded-pill py-0" style="font-size:0.6rem;" onclick="return confirm('Delete <?= $slot['slot_number'] ?>?')">
                                                    <i class="fa-solid fa-xmark"></i>
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
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



