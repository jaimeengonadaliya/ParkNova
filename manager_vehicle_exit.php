<?php
require_once __DIR__ . '/config_db.php';
if (!isLoggedIn() || !isManager()) redirect($base_url . '/user_login.php');

$manager_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM parking_locations WHERE manager_id = ?");
$stmt->execute([$manager_id]);
$manager_parking = $stmt->fetch();
if (!$manager_parking) redirect($base_url . '/manager_dashboard.php');

$parking_id   = $manager_parking['parking_id'];
$rate_per_hr  = (float)$manager_parking['price_per_hour'];
$success = ''; $error = ''; $found_booking = null; $bill = null;

// --- SEARCH FOR ACTIVE/OCCUPIED BOOKING ---
if (isset($_POST['search'])) {
    $search_val = trim($_POST['search_val']);
    $stmt = $pdo->prepare("
        SELECT b.*, u.name, u.email, u.mobile, s.slot_number, s.slot_id
        FROM bookings b
        JOIN users u ON b.user_id = u.user_id
        JOIN parking_slots s ON b.slot_id = s.slot_id
        WHERE b.parking_id = ?
          AND b.status IN ('pending', 'completed')
          AND s.status IN ('booked', 'occupied')
          AND (b.booking_id = ? OR b.vehicle_number = ? OR u.mobile = ?)
        ORDER BY b.created_at DESC LIMIT 1
    ");
    $stmt->execute([$parking_id, (int)$search_val, $search_val, $search_val]);
    $found_booking = $stmt->fetch();

    if ($found_booking) {
        $paid_amount = (float)$found_booking['amount'];
        // Calculate billing based on entry and exit time
        $entry_time = new DateTime($found_booking['start_time'] ?: $found_booking['created_at']);
        $exit_time  = new DateTime();
        $diff       = $entry_time->diff($exit_time);
        $total_hrs  = max(1, ceil((($diff->days * 24 * 60) + ($diff->h * 60) + $diff->i) / 60)); // Total hours ceil
        $total_fee  = $total_hrs * $rate_per_hr;
        $balance    = max(0, $total_fee - $paid_amount);

        $bill = [
            'entry'     => $entry_time->format('h:i A'),
            'exit'      => $exit_time->format('h:i A'),
            'duration'  => $total_hrs,
            'rate'      => $rate_per_hr,
            'total'     => $total_fee,
            'paid'      => $paid_amount,
            'balance'   => $balance,
            'exit_dt'   => $exit_time->format('Y-m-d H:i:s'),
        ];
    } else {
        $error = "No active booking found for: <strong>" . htmlspecialchars($search_val) . "</strong>";
    }
}

    if (isset($_POST['process_exit'])) {
    $booking_id = (int)$_POST['booking_id'];
    $slot_id    = (int)$_POST['slot_id_exit'];
    $balance    = (float)$_POST['balance'];
    $exit_dt    = $_POST['exit_dt'];

    try {
        $pdo->beginTransaction();
        // Update booking: set end_time, final amount, completed
        $pdo->prepare("UPDATE bookings SET end_time = ?, amount = amount + ?, status = 'completed' WHERE booking_id = ? AND parking_id = ?")
            ->execute([$exit_dt, $balance, $booking_id, $parking_id]);
        // Free the parking slot
        $pdo->prepare("UPDATE parking_slots SET status = 'available' WHERE slot_id = ? AND parking_id = ?")
            ->execute([$slot_id, $parking_id]);
        $pdo->commit();

        $success = "✅ Vehicle exit processed! Slot freed. Collected Balance: <strong>₹" . number_format($balance, 2) . "</strong>";
        $found_booking = null;
        $bill = null;
    } catch (PDOException $e) {
        $pdo->rollBack();
        $error = "Exit failed: " . $e->getMessage();
    }
}

// Active (occupied) bookings list
$stmt = $pdo->prepare("
    SELECT b.*, u.name, s.slot_number, s.slot_id
    FROM bookings b
    JOIN users u ON b.user_id = u.user_id
    JOIN parking_slots s ON b.slot_id = s.slot_id
    WHERE b.parking_id = ? AND s.status = 'occupied'
    ORDER BY b.start_time ASC
");
$stmt->execute([$parking_id]);
$active_vehicles = $stmt->fetchAll();

require_once __DIR__ . '/includes_header.php';
require_once __DIR__ . '/includes_manager_navbar.php';
?>

<div class="container-fluid py-4 px-lg-5">
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= $base_url ?>/manager_dashboard.php" class="text-primary text-decoration-none fw-medium"><i class="fa-solid fa-gauge me-1"></i>Dashboard</a></li>
            <li class="breadcrumb-item active fw-bold">Vehicle Exit</li>
        </ol>
    </nav>

    <div class="d-flex flex-wrap justify-content-between align-items-center mb-5 gap-3">
        <div>
            <h3 class="fw-bold text-primary mb-1"><i class="fa-solid fa-right-from-bracket me-2 text-danger"></i>Vehicle Exit & Billing</h3>
            <p class="text-secondary small mb-0"><?= htmlspecialchars($manager_parking['parking_name']) ?> · Rate: ₹<?= $rate_per_hr ?>/hr</p>
        </div>
        <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 px-3 py-2 fs-6">
            <i class="fa-solid fa-car me-2"></i><?= count($active_vehicles) ?> Active Vehicles
        </span>
    </div>

    <?php if ($error): ?>
        <div class="alert glass-panel border-start border-4 border-danger mb-4"><i class="fa-solid fa-triangle-exclamation me-2 text-danger"></i><?= $error ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div class="alert glass-panel border-start border-4 border-success mb-4"><i class="fa-solid fa-check-circle me-2 text-success"></i><?= $success ?></div>
    <?php endif; ?>

    <div class="row g-4">
        <!-- LEFT: Search + Billing -->
        <div class="col-lg-5">
            <!-- Search Form -->
            <div class="card-3d p-4 bg-surface mb-4">
                <h5 class="fw-bold text-danger mb-4"><i class="fa-solid fa-magnifying-glass me-2"></i>Find Active Booking</h5>
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label fw-bold text-secondary small text-uppercase">Booking ID / Vehicle No. / Mobile</label>
                        <div class="input-group">
                            <span class="input-group-text bg-danger bg-opacity-10 border-danger border-opacity-25"><i class="fa-solid fa-search text-danger"></i></span>
                            <input type="text" name="search_val"
                                   class="form-control border-secondary border-opacity-25 fw-bold py-3 fs-5"
                                   placeholder="e.g. GJ05AB1234"
                                   value="<?= isset($_POST['search_val']) ? htmlspecialchars($_POST['search_val']) : '' ?>"
                                   style="text-transform:uppercase" autofocus required>
                        </div>
                    </div>
                    <button name="search" type="submit" class="btn btn-outline-danger rounded-pill w-100 py-3 fw-bold fs-5">
                        <i class="fa-solid fa-search me-2"></i>Find Vehicle
                    </button>
                </form>
            </div>

            <!-- Bill Card -->
            <?php if ($found_booking && $bill): ?>
            <div class="glass-panel overflow-hidden rounded-4 border border-danger border-opacity-25">
                <div class="p-4 text-white" style="background: linear-gradient(135deg,#dc2626,#b91c1c);">
                    <h5 class="fw-bold mb-1"><i class="fa-solid fa-receipt me-2"></i>Parking Bill</h5>
                    <p class="mb-0 opacity-75 small">Booking #<?= $found_booking['booking_id'] ?></p>
                </div>
                <div class="p-4 bg-surface">
                    <table class="table mb-4">
                        <tr><td class="text-secondary fw-medium">Driver</td><td class="text-end fw-bold"><?= htmlspecialchars($found_booking['name']) ?></td></tr>
                        <tr><td class="text-secondary fw-medium">Vehicle</td><td class="text-end fw-bold" style="letter-spacing:0.1em"><?= htmlspecialchars($found_booking['vehicle_number']) ?></td></tr>
                        <tr><td class="text-secondary fw-medium">Slot</td><td class="text-end"><span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 rounded-pill px-3 fw-bold"><?= htmlspecialchars($found_booking['slot_number']) ?></span></td></tr>
                        <tr><td class="text-secondary fw-medium">Entry Time</td><td class="text-end fw-bold text-success"><?= $bill['entry'] ?></td></tr>
                        <tr><td class="text-secondary fw-medium">Exit Time</td><td class="text-end fw-bold text-danger"><?= $bill['exit'] ?></td></tr>
                        <tr><td class="text-secondary fw-medium">Duration</td><td class="text-end fw-bold"><?= $bill['duration'] ?> hour(s)</td></tr>
                        <tr class="border-top border-2">
                            <td class="fw-bold text-uppercase text-dark">Rate</td>
                            <td class="text-end fw-bold">₹<?= $bill['rate'] ?>/hr</td>
                        </tr>
                        <tr>
                            <td class="text-secondary fw-medium">Calculated Cost</td>
                            <td class="text-end fw-bold">₹<?= number_format($bill['total'], 2) ?></td>
                        </tr>
                        <tr>
                            <td class="text-secondary fw-medium">Pre-Paid Online</td>
                            <td class="text-end fw-bold text-success">- ₹<?= number_format($bill['paid'], 2) ?></td>
                        </tr>
                        <tr>
                            <td class="fw-bolder text-uppercase text-dark fs-5">Balance Due</td>
                            <td class="text-end fw-bolder text-danger fs-4">₹<?= number_format($bill['balance'], 2) ?></td>
                        </tr>
                    </table>

                    <form method="POST">
                        <input type="hidden" name="booking_id"  value="<?= $found_booking['booking_id'] ?>">
                        <input type="hidden" name="slot_id_exit" value="<?= $found_booking['slot_id'] ?>">
                        <input type="hidden" name="balance"   value="<?= $bill['balance'] ?>">
                        <input type="hidden" name="exit_dt"   value="<?= $bill['exit_dt'] ?>">
                        <button name="process_exit" type="submit"
                                class="btn <?= $bill['balance'] > 0 ? 'btn-danger' : 'btn-success' ?> w-100 rounded-pill py-3 fw-bold fs-5 shadow-lg"
                                onclick="return confirm('Confirm exit? Collect Balance: ₹<?= number_format($bill['balance'], 2) ?>')">
                            <i class="fa-solid fa-check-circle me-2"></i>Confirm Exit & Collect ₹<?= number_format($bill['balance'], 2) ?>
                        </button>
                    </form>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- RIGHT: Active Vehicles Table -->
        <div class="col-lg-7">
            <div class="glass-panel overflow-hidden h-100">
                <div class="border-bottom border-secondary border-opacity-25 p-4">
                    <h5 class="fw-bold text-primary mb-0"><i class="fa-solid fa-car me-2"></i>Currently Parked Vehicles</h5>
                </div>
                <div class="p-4">
                    <?php if (empty($active_vehicles)): ?>
                        <div class="text-center py-5 text-secondary">
                            <i class="fa-solid fa-parking fa-3x opacity-25 d-block mb-3"></i>
                            <p>No vehicles currently occupying slots.</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th class="py-3 text-secondary small fw-bold text-uppercase">#</th>
                                        <th class="py-3 text-secondary small fw-bold text-uppercase">Driver</th>
                                        <th class="py-3 text-secondary small fw-bold text-uppercase">Slot</th>
                                        <th class="py-3 text-secondary small fw-bold text-uppercase">Vehicle</th>
                                        <th class="py-3 text-secondary small fw-bold text-uppercase">Entry</th>
                                        <th class="py-3 text-secondary small fw-bold text-uppercase">Est. Hrs</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($active_vehicles as $v):
                                        $entry = new DateTime($v['start_time'] ?: $v['created_at']);
                                        $now   = new DateTime();
                                        $diff  = $entry->diff($now);
                                        $hrs   = $diff->h + ($diff->days * 24);
                                        $mins  = $diff->i;
                                    ?>
                                    <tr>
                                        <td class="text-muted fw-bold">#<?= $v['booking_id'] ?></td>
                                        <td class="fw-bold"><?= htmlspecialchars($v['name']) ?></td>
                                        <td><span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 rounded-pill px-3 fw-bold"><?= htmlspecialchars($v['slot_number']) ?></span></td>
                                        <td class="fw-bold small" style="letter-spacing:0.08em"><?= htmlspecialchars($v['vehicle_number']) ?></td>
                                        <td class="small fw-medium"><?= $entry->format('h:i A') ?></td>
                                        <td>
                                            <span class="badge bg-warning bg-opacity-15 text-warning border border-warning border-opacity-25 rounded-pill px-2 fw-bold">
                                                <?= $hrs ?>h <?= $mins ?>m
                                            </span>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes_footer.php'; ?>



