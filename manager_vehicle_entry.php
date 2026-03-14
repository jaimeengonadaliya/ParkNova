<?php
require_once __DIR__ . '/config_db.php';
if (!isLoggedIn() || !isManager()) redirect($base_url . '/user_login.php');

$manager_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM parking_locations WHERE manager_id = ?");
$stmt->execute([$manager_id]);
$manager_parking = $stmt->fetch();
if (!$manager_parking) redirect($base_url . '/manager_dashboard.php');

$parking_id = $manager_parking['parking_id'];
$success = ''; $error = ''; $found_booking = null;

// --- SEARCH / VERIFY BOOKING ---
if (isset($_POST['search'])) {
    $search_val = trim($_POST['search_val']);
    $stmt = $pdo->prepare("
        SELECT b.*, u.name, u.email, s.slot_number, s.slot_id
        FROM bookings b
        JOIN users u ON b.user_id = u.user_id
        JOIN parking_slots s ON b.slot_id = s.slot_id
        WHERE b.parking_id = ?
          AND b.status = 'pending'
          AND (b.booking_id = ? OR b.vehicle_number = ? OR u.mobile = ?)
        LIMIT 1
    ");
    $stmt->execute([$parking_id, (int)$search_val, $search_val, $search_val]);
    $found_booking = $stmt->fetch();
    if (!$found_booking) {
        $error = "No active booking found for: <strong>" . htmlspecialchars($search_val) . "</strong>";
    }
}

// --- ALLOW ENTRY (mark slot as occupied) ---
if (isset($_POST['allow_entry'])) {
    $booking_id = (int)$_POST['booking_id'];
    $slot_id    = (int)$_POST['slot_id'];
    try {
        $pdo->beginTransaction();
        $pdo->prepare("UPDATE parking_slots SET status = 'occupied' WHERE slot_id = ? AND parking_id = ?")->execute([$slot_id, $parking_id]);
        $pdo->prepare("UPDATE bookings SET start_time = NOW() WHERE booking_id = ?")->execute([$booking_id]);
        $pdo->commit();
        $success = "✅ Vehicle entry confirmed! Slot marked as <strong>Occupied</strong>.";
    } catch (PDOException $e) {
        $pdo->rollBack();
        $error = "Entry failed: " . $e->getMessage();
    }
}

// Available slots for walk-in
$stmt = $pdo->prepare("SELECT * FROM parking_slots WHERE parking_id = ? AND status = 'available' ORDER BY slot_number ASC");
$stmt->execute([$parking_id]);
$available_slots = $stmt->fetchAll();

require_once __DIR__ . '/includes_header.php';
require_once __DIR__ . '/includes_manager_navbar.php';
?>

<div class="container-fluid py-4 px-lg-5">
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= $base_url ?>/manager_dashboard.php" class="text-primary text-decoration-none fw-medium"><i class="fa-solid fa-gauge me-1"></i>Dashboard</a></li>
            <li class="breadcrumb-item active fw-bold">Vehicle Entry</li>
        </ol>
    </nav>

    <div class="d-flex flex-wrap justify-content-between align-items-center mb-5 gap-3">
        <div>
            <h3 class="fw-bold text-primary mb-1"><i class="fa-solid fa-right-to-bracket me-2 text-success"></i>Vehicle Entry Management</h3>
            <p class="text-secondary small mb-0"><?= htmlspecialchars($manager_parking['parking_name']) ?> — Search by Booking ID, Vehicle Number, or Mobile</p>
        </div>
        <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-3 py-2 fs-6">
            <i class="fa-solid fa-circle-check me-2"></i><?= count($available_slots) ?> Free Slots
        </span>
    </div>

    <?php if ($error): ?>
        <div class="alert glass-panel border-start border-4 border-danger mb-4"><i class="fa-solid fa-triangle-exclamation me-2 text-danger"></i><?= $error ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div class="alert glass-panel border-start border-4 border-success mb-4"><i class="fa-solid fa-check-circle me-2 text-success"></i><?= $success ?></div>
    <?php endif; ?>

    <div class="row g-4">
        <!-- Search Panel -->
        <div class="col-lg-5">
            <div class="card-3d p-4 bg-surface mb-4">
                <h5 class="fw-bold text-primary mb-4"><i class="fa-solid fa-magnifying-glass me-2"></i>Find & Verify Booking</h5>
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label fw-bold text-secondary small text-uppercase">Booking ID / Vehicle No. / Mobile</label>
                        <div class="input-group">
                            <span class="input-group-text bg-surface border-secondary border-opacity-25"><i class="fa-solid fa-search text-primary"></i></span>
                            <input type="text" name="search_val"
                                   class="form-control border-secondary border-opacity-25 fw-bold fs-5 py-3"
                                   placeholder="e.g. GJ05AB1234"
                                   value="<?= isset($_POST['search_val']) ? htmlspecialchars($_POST['search_val']) : '' ?>"
                                   required style="text-transform:uppercase" autofocus>
                        </div>
                        <div class="form-text text-muted">Enter Booking ID, Vehicle Number (e.g. GJ05AB1234), or 10-digit mobile.</div>
                    </div>
                    <button name="search" type="submit" class="btn-primary-3d w-100 justify-content-center py-3 fs-5">
                        <i class="fa-solid fa-qrcode me-2"></i>Verify Booking
                    </button>
                </form>
            </div>

            <!-- Available Slots Summary -->
            <div class="glass-panel p-4 rounded-4">
                <h6 class="fw-bold text-primary mb-3"><i class="fa-solid fa-grip me-2"></i>Available Slots (<?= count($available_slots) ?>)</h6>
                <div class="d-flex flex-wrap gap-2">
                    <?php foreach (array_slice($available_slots, 0, 20) as $s): ?>
                        <span class="badge bg-success bg-opacity-15 text-success border border-success border-opacity-25 rounded-pill px-3 py-2 fw-bold"><?= htmlspecialchars($s['slot_number']) ?></span>
                    <?php endforeach; ?>
                    <?php if (count($available_slots) > 20): ?>
                        <span class="badge bg-secondary bg-opacity-15 text-secondary rounded-pill px-3 py-2">+<?= count($available_slots) - 20 ?> more</span>
                    <?php endif; ?>
                    <?php if (empty($available_slots)): ?>
                        <span class="text-danger fw-bold"><i class="fa-solid fa-exclamation-circle me-1"></i>Parking Full!</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Booking Result -->
        <div class="col-lg-7">
            <?php if ($found_booking): ?>
            <div class="card-3d overflow-hidden animate__animated animate__fadeIn">
                <div class="p-4 text-white" style="background: linear-gradient(135deg, #0d6efd, #0a58ca);">
                    <div class="d-flex align-items-center gap-3">
                        <div class="bg-white bg-opacity-20 rounded-circle p-3">
                            <i class="fa-solid fa-car fa-2x"></i>
                        </div>
                        <div>
                            <h5 class="fw-bold mb-1">Booking #<?= $found_booking['booking_id'] ?></h5>
                            <p class="mb-0 opacity-75 small">Verified Booking — Ready for Entry</p>
                        </div>
                        <span class="ms-auto badge bg-success fs-6 px-3 py-2 rounded-pill">✓ Valid</span>
                    </div>
                </div>
                <div class="p-4 bg-surface">
                    <div class="row g-3 mb-4">
                        <div class="col-6">
                            <div class="glass-panel p-3 rounded-3 text-center">
                                <div class="small text-secondary fw-bold text-uppercase mb-1">Driver Name</div>
                                <div class="fw-bold"><?= htmlspecialchars($found_booking['name']) ?></div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="glass-panel p-3 rounded-3 text-center">
                                <div class="small text-secondary fw-bold text-uppercase mb-1">Assigned Slot</div>
                                <div class="fw-bold fs-4 text-primary"><?= htmlspecialchars($found_booking['slot_number']) ?></div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="glass-panel p-3 rounded-3 text-center">
                                <div class="small text-secondary fw-bold text-uppercase mb-1">Vehicle Number</div>
                                <div class="fw-bold text-dark" style="letter-spacing:0.1em"><?= htmlspecialchars($found_booking['vehicle_number']) ?></div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="glass-panel p-3 rounded-3 text-center">
                                <div class="small text-secondary fw-bold text-uppercase mb-1">Amount Paid</div>
                                <div class="fw-bold text-success fs-5">₹<?= number_format($found_booking['amount'], 2) ?></div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="glass-panel p-3 rounded-3 text-center">
                                <div class="small text-secondary fw-bold text-uppercase mb-1">Booking Time</div>
                                <div class="fw-bold"><?= date('d M Y, h:i A', strtotime($found_booking['created_at'])) ?></div>
                            </div>
                        </div>
                    </div>

                    <!-- Allow Entry Button -->
                    <form method="POST">
                        <input type="hidden" name="booking_id" value="<?= $found_booking['booking_id'] ?>">
                        <input type="hidden" name="slot_id"    value="<?= $found_booking['slot_id'] ?>">
                        <button name="allow_entry" type="submit" class="btn btn-success w-100 rounded-pill py-3 fw-bold fs-5 shadow-lg">
                            <i class="fa-solid fa-barrier-block me-2"></i>Allow Entry — Mark Slot <?= htmlspecialchars($found_booking['slot_number']) ?> as Occupied
                        </button>
                    </form>
                </div>
            </div>
            <?php else: ?>
                <div class="glass-panel p-5 text-center rounded-4 h-100 d-flex flex-column justify-content-center align-items-center">
                    <div class="bg-primary bg-opacity-10 rounded-circle p-4 mb-4">
                        <i class="fa-solid fa-qrcode fa-4x text-primary opacity-50"></i>
                    </div>
                    <h5 class="fw-bold text-primary">Search for a Booking</h5>
                    <p class="text-secondary">Enter the customer's <strong>Booking ID</strong>, <strong>Vehicle Number</strong>,<br>or <strong>Mobile Number</strong> to verify their booking.</p>
                    <div class="mt-3 d-flex gap-3 flex-wrap justify-content-center">
                        <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-3 py-2"><i class="fa-solid fa-hashtag me-1"></i>Booking ID</span>
                        <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-3 py-2"><i class="fa-solid fa-car me-1"></i>Vehicle No.</span>
                        <span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25 px-3 py-2"><i class="fa-solid fa-mobile me-1"></i>Mobile No.</span>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes_footer.php'; ?>



