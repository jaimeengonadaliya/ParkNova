<?php
require_once __DIR__ . '/config_db.php';

if (!isLoggedIn() || !isManager()) {
    redirect($base_url . '/user_login.php');
}

$manager_id = $_SESSION['user_id'];

// Get assigned parking
$stmt = $pdo->prepare("SELECT * FROM parking_locations WHERE manager_id = ?");
$stmt->execute([$manager_id]);
$manager_parking = $stmt->fetch();

if (!$manager_parking) {
    require_once __DIR__ . '/includes_header.php';
    require_once __DIR__ . '/includes_manager_navbar.php';
    echo '<div class="container py-5 text-center">
        <div class="card-3d p-5 d-inline-block">
            <i class="fa-solid fa-building-circle-xmark fa-4x text-muted opacity-25 d-block mb-4"></i>
            <h3 class="fw-bold text-primary">No Parking Assigned</h3>
            <p class="text-secondary">Your manager account hasn\'t been assigned to a parking location yet.<br>Please contact the <strong>Super Admin</strong> to get assigned.</p>
        </div>
    </div>';
    require_once __DIR__ . '/includes_footer.php';
    exit;
}

$parking_id = $manager_parking['parking_id'];
$today      = date('Y-m-d');

// 1. Slot Stats
$stmt = $pdo->prepare("SELECT
    COUNT(*) AS total,
    SUM(CASE WHEN status='available' THEN 1 ELSE 0 END) AS avail,
    SUM(CASE WHEN status='booked'    THEN 1 ELSE 0 END) AS booked,
    SUM(CASE WHEN status='occupied'  THEN 1 ELSE 0 END) AS occupied
    FROM parking_slots WHERE parking_id = ?");
$stmt->execute([$parking_id]);
$slot_stats = $stmt->fetch();

// 2. Bookings Today
$stmt = $pdo->prepare("SELECT COUNT(*) FROM bookings WHERE parking_id = ? AND DATE(created_at) = ?");
$stmt->execute([$parking_id, $today]);
$bookings_today = $stmt->fetchColumn();

// 3. Revenue Today (completed bookings only)
$stmt = $pdo->prepare("SELECT COALESCE(SUM(amount),0) FROM bookings WHERE parking_id = ? AND DATE(created_at) = ? AND status = 'completed'");
$stmt->execute([$parking_id, $today]);
$revenue_today = $stmt->fetchColumn();

// 4. Active Vehicles (currently occupied slots)
$active_vehicles = $slot_stats['occupied'] ?? 0;

// 5. Last 7 Days chart
$chart_dates = [];
$chart_revenues = [];
for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $chart_dates[] = date('M d', strtotime($date));
    $stmt = $pdo->prepare("SELECT COALESCE(SUM(amount),0) FROM bookings WHERE parking_id = ? AND DATE(created_at) = ? AND status = 'completed'");
    $stmt->execute([$parking_id, $date]);
    $chart_revenues[] = (float)$stmt->fetchColumn();
}

// 6. Recent Bookings (last 6)
$stmt = $pdo->prepare("
    SELECT b.booking_id, u.name, u.email, b.created_at, b.start_time, b.end_time,
           b.vehicle_number, b.vehicle_type, b.amount, b.status, s.slot_number
    FROM bookings b
    JOIN users u ON b.user_id = u.user_id
    JOIN parking_slots s ON b.slot_id = s.slot_id
    WHERE b.parking_id = ?
    ORDER BY b.created_at DESC LIMIT 6
");
$stmt->execute([$parking_id]);
$recent_bookings = $stmt->fetchAll();

// 7. Active Bookings count (for quick actions)
$stmt = $pdo->prepare("SELECT COUNT(*) FROM bookings WHERE parking_id = ? AND status = 'pending'");
$stmt->execute([$parking_id]);
$active_bookings_count = $stmt->fetchColumn();

require_once __DIR__ . '/includes_header.php';
require_once __DIR__ . '/includes_manager_navbar.php';
?>

<div class="container-fluid py-4 px-lg-5">

    <!-- Header -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <h3 class="fw-bold mb-1 text-primary"><i class="fa-solid fa-gauge-high me-2"></i>Manager Dashboard</h3>
            <p class="text-secondary small mb-0">
                Welcome, <strong><?= htmlspecialchars($_SESSION['name']) ?></strong> &bull;
                <i class="fa-solid fa-building me-1 text-primary opacity-75"></i>
                <?= htmlspecialchars($manager_parking['parking_name']) ?>, <?= htmlspecialchars($manager_parking['city']) ?>
            </p>
        </div>
        <span class="badge bg-surface border border-secondary border-opacity-25 text-secondary px-3 py-2 rounded-pill shadow-sm fs-6">
            <i class="fa-regular fa-calendar me-2"></i><?= date('l, d F Y') ?>
        </span>
    </div>

    <!-- KPI Cards Row -->
    <div class="row g-4 mb-4">
        <div class="col-6 col-xl-2">
            <div class="card-3d p-4 text-center h-100">
                <div class="rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center bg-primary bg-opacity-10 border border-primary border-opacity-25" style="width:48px;height:48px;">
                    <i class="fa-solid fa-grip text-primary"></i>
                </div>
                <h4 class="fw-bold text-primary mb-0"><?= $slot_stats['total'] ?></h4>
                <div class="small text-secondary fw-medium mt-1">Total Slots</div>
            </div>
        </div>
        <div class="col-6 col-xl-2">
            <div class="card-3d p-4 text-center h-100">
                <div class="rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center bg-success bg-opacity-10 border border-success border-opacity-25" style="width:48px;height:48px;">
                    <i class="fa-solid fa-circle-check text-success"></i>
                </div>
                <h4 class="fw-bold text-success mb-0"><?= $slot_stats['avail'] ?></h4>
                <div class="small text-secondary fw-medium mt-1">Available</div>
            </div>
        </div>
        <div class="col-6 col-xl-2">
            <div class="card-3d p-4 text-center h-100">
                <div class="rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center bg-danger bg-opacity-10 border border-danger border-opacity-25" style="width:48px;height:48px;">
                    <i class="fa-solid fa-ban text-danger"></i>
                </div>
                <h4 class="fw-bold text-danger mb-0"><?= ($slot_stats['booked'] + $slot_stats['occupied']) ?></h4>
                <div class="small text-secondary fw-medium mt-1">In Use</div>
            </div>
        </div>
        <div class="col-6 col-xl-2">
            <div class="card-3d p-4 text-center h-100">
                <div class="rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center bg-warning bg-opacity-10 border border-warning border-opacity-25" style="width:48px;height:48px;">
                    <i class="fa-solid fa-car text-warning"></i>
                </div>
                <h4 class="fw-bold text-warning mb-0"><?= $active_vehicles ?></h4>
                <div class="small text-secondary fw-medium mt-1">Active Vehicles</div>
            </div>
        </div>
        <div class="col-6 col-xl-2">
            <div class="card-3d p-4 text-center h-100">
                <div class="rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center bg-info bg-opacity-10 border border-info border-opacity-25" style="width:48px;height:48px;">
                    <i class="fa-solid fa-calendar-check text-info"></i>
                </div>
                <h4 class="fw-bold text-info mb-0"><?= $bookings_today ?></h4>
                <div class="small text-secondary fw-medium mt-1">Bookings Today</div>
            </div>
        </div>
        <div class="col-6 col-xl-2">
            <div class="card-3d p-4 text-center h-100" style="background: linear-gradient(135deg, rgba(67,97,238,0.07), rgba(109,40,217,0.07));">
                <div class="rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center bg-primary bg-opacity-15 border border-primary border-opacity-25" style="width:48px;height:48px;">
                    <i class="fa-solid fa-indian-rupee-sign text-primary"></i>
                </div>
                <h4 class="fw-bold text-gradient mb-0">₹<?= number_format($revenue_today, 0) ?></h4>
                <div class="small text-secondary fw-medium mt-1">Revenue Today</div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Quick Actions Sidebar -->
        <div class="col-lg-4 col-xl-3">
            <!-- Parking Info Card -->
            <div class="card-3d overflow-hidden mb-4">
                <div class="text-white p-4 position-relative" style="background: linear-gradient(135deg, #1e3a5f, #0d3349);">
                    <div class="position-absolute top-0 end-0 rounded-circle bg-white opacity-10" style="width:120px;height:120px;transform:translate(30%,-30%)"></div>
                    <div class="small text-white text-opacity-60 fw-bold text-uppercase mb-1">Your Parking</div>
                    <h5 class="fw-bold mb-1"><?= htmlspecialchars($manager_parking['parking_name']) ?></h5>
                    <p class="opacity-75 small mb-3"><i class="fa-solid fa-location-dot me-1"></i><?= htmlspecialchars($manager_parking['address']) ?>, <?= htmlspecialchars($manager_parking['city']) ?></p>
                    <div class="row g-2 text-center">
                        <div class="col-6"><div class="bg-white bg-opacity-10 rounded-3 p-2"><div class="fw-bold fs-5"><?= $slot_stats['total'] ?></div><div class="small opacity-75">Slots</div></div></div>
                        <div class="col-6"><div class="bg-white bg-opacity-10 rounded-3 p-2"><div class="fw-bold fs-5">₹<?= number_format($manager_parking['price_per_hour'], 0) ?></div><div class="small opacity-75">Per Hour</div></div></div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card-3d p-4 bg-surface mb-4">
                <h6 class="fw-bold text-primary mb-3"><i class="fa-solid fa-bolt me-2"></i>Quick Actions</h6>
                <div class="d-grid gap-2">
                    <a href="<?= $base_url ?>/manager_vehicle_entry.php" class="btn btn-success-3d justify-content-center py-3 fw-bold">
                        <i class="fa-solid fa-right-to-bracket me-2"></i>Vehicle Entry
                    </a>
                    <a href="<?= $base_url ?>/manager_vehicle_exit.php" class="btn btn-danger-3d justify-content-center py-3 fw-bold">
                        <i class="fa-solid fa-right-from-bracket me-2"></i>Vehicle Exit
                    </a>
                    <a href="<?= $base_url ?>/manager_manage_slots.php" class="btn-primary-3d justify-content-center py-2">
                        <i class="fa-solid fa-grip me-2"></i>Live Slot Grid
                    </a>
                    <a href="<?= $base_url ?>/manager_bookings.php" class="btn btn-outline-primary rounded-pill fw-bold py-2 justify-content-center d-flex align-items-center gap-2">
                        <i class="fa-solid fa-book-bookmark"></i>All Bookings
                        <?php if ($active_bookings_count > 0): ?>
                            <span class="badge bg-danger rounded-pill"><?= $active_bookings_count ?></span>
                        <?php endif; ?>
                    </a>
                </div>
            </div>
        </div>

        <!-- Right Column: Chart + Bookings -->
        <div class="col-lg-8 col-xl-9">
            <!-- Revenue Chart -->
            <div class="glass-panel mb-4">
                <div class="border-bottom border-secondary border-opacity-25 p-4">
                    <h5 class="fw-bold mb-0 text-primary"><i class="fa-solid fa-chart-area me-2"></i>Revenue — Last 7 Days</h5>
                </div>
                <div class="p-4">
                    <canvas id="revenueChart" height="100"></canvas>
                </div>
            </div>

            <!-- Recent Bookings -->
            <div class="glass-panel overflow-hidden">
                <div class="border-bottom border-secondary border-opacity-25 p-4 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0 text-primary"><i class="fa-solid fa-clock-rotate-left me-2"></i>Recent Bookings</h5>
                    <a href="<?= $base_url ?>/manager_bookings.php" class="btn btn-sm btn-outline-primary rounded-pill px-3 fw-bold">View All</a>
                </div>
                <div class="p-4">
                <?php if (empty($recent_bookings)): ?>
                    <div class="text-center py-4 text-secondary">
                        <i class="fa-solid fa-calendar-xmark fa-3x opacity-25 d-block mb-3"></i>
                        No bookings yet for this location.
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th class="text-secondary small fw-bold text-uppercase py-3">#</th>
                                    <th class="text-secondary small fw-bold text-uppercase py-3">Customer</th>
                                    <th class="text-secondary small fw-bold text-uppercase py-3">Slot</th>
                                    <th class="text-secondary small fw-bold text-uppercase py-3">Vehicle</th>
                                    <th class="text-secondary small fw-bold text-uppercase py-3">Time</th>
                                    <th class="text-secondary small fw-bold text-uppercase py-3">Amount</th>
                                    <th class="text-secondary small fw-bold text-uppercase py-3 text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recent_bookings as $b): ?>
                                <tr>
                                    <td class="text-muted fw-bold">#<?= $b['booking_id'] ?></td>
                                    <td>
                                        <div class="fw-bold"><?= htmlspecialchars($b['name']) ?></div>
                                        <div class="small text-secondary"><?= htmlspecialchars($b['email']) ?></div>
                                    </td>
                                    <td><span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 rounded-pill px-3 fw-bold"><?= htmlspecialchars($b['slot_number']) ?></span></td>
                                    <td class="fw-bold small"><?= htmlspecialchars($b['vehicle_number']) ?></td>
                                    <td>
                                        <div class="small fw-medium"><?= date('M d', strtotime($b['created_at'])) ?></div>
                                        <div class="small text-secondary"><?= date('h:i A', strtotime($b['start_time'])) ?></div>
                                    </td>
                                    <td class="fw-bold text-primary">₹<?= number_format($b['amount'], 2) ?></td>
                                    <td class="text-center">
                                        <span class="status-badge status-<?= $b['status'] ?> text-uppercase py-1 px-2 d-inline-block" style="font-size:0.7rem"><?= $b['status'] ?></span>
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

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const ctx = document.getElementById('revenueChart').getContext('2d');
let gradient = ctx.createLinearGradient(0, 0, 0, 300);
gradient.addColorStop(0, 'rgba(67,97,238,0.45)');
gradient.addColorStop(1, 'rgba(67,97,238,0.02)');
new Chart(ctx, {
    type: 'line',
    data: {
        labels: <?= json_encode($chart_dates) ?>,
        datasets: [{
            label: 'Revenue (₹)',
            data: <?= json_encode($chart_revenues) ?>,
            borderColor: '#4361ee',
            backgroundColor: gradient,
            borderWidth: 3,
            pointBackgroundColor: '#fff',
            pointBorderColor: '#4361ee',
            pointBorderWidth: 2,
            pointRadius: 5,
            fill: true,
            tension: 0.4
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: { display: false },
            tooltip: {
                backgroundColor: 'rgba(15,23,42,0.9)',
                callbacks: { label: ctx => '₹' + ctx.parsed.y.toLocaleString() }
            }
        },
        scales: {
            y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.04)' }, ticks: { callback: v => '₹' + v } },
            x: { grid: { display: false } }
        }
    }
});
</script>

<?php require_once __DIR__ . '/includes_footer.php'; ?>



