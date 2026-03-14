<?php
require_once __DIR__ . '/config_db.php';

if (!isLoggedIn() || !isManager()) {
    redirect($base_url . '/user_login.php');
}

$manager_id = $_SESSION['user_id'];

// Get the parking lot assigned to this manager
$stmt = $pdo->prepare("SELECT * FROM parking_locations WHERE manager_id = ?");
$stmt->execute([$manager_id]);
$manager_parking = $stmt->fetch();

if (!$manager_parking) {
    // Not assigned to any parking lot yet
    require_once __DIR__ . '/includes_header.php';
    require_once __DIR__ . '/includes_manager_navbar.php';
    echo '<div class="container py-5 text-center">';
    echo '<div class="card-3d p-5 d-inline-block">';
    echo '<i class="fa-solid fa-circle-exclamation fa-3x text-warning mb-3"></i>';
    echo '<h4 class="fw-bold">No Parking Lot Assigned</h4>';
    echo '<p class="text-secondary">Please contact the Super Admin to assign you a parking location.</p>';
    echo '</div></div>';
    require_once __DIR__ . '/includes_footer.php';
    exit;
}

$parking_id = $manager_parking['parking_id'];

// Stats for this parking only
$today = date('Y-m-d');

// Bookings today
$stmt = $pdo->prepare("SELECT COUNT(*) FROM bookings WHERE parking_id = ? AND created_at = ?");
$stmt->execute([$parking_id, $today]);
$bookings_today = $stmt->fetchColumn();

// Total revenue (completed)
$stmt = $pdo->prepare("SELECT SUM(amount) FROM bookings WHERE parking_id = ? AND status = 'completed'");
$stmt->execute([$parking_id]);
$total_revenue = $stmt->fetchColumn() ?: 0;

// Available slots
$stmt = $pdo->prepare("SELECT COUNT(*) FROM parking_slots WHERE parking_id = ? AND status = 'available'");
$stmt->execute([$parking_id]);
$available_slots = $stmt->fetchColumn();

// Booked slots
$stmt = $pdo->prepare("SELECT COUNT(*) FROM parking_slots WHERE parking_id = ? AND status = 'booked'");
$stmt->execute([$parking_id]);
$booked_slots = $stmt->fetchColumn();

// Recent 5 bookings
$stmt = $pdo->prepare("
    SELECT b.booking_id, u.name, u.email, s.slot_number, b.vehicle_number, b.vehicle_type,
           b.created_at, b.start_time, b.end_time, b.amount, b.status
    FROM bookings b
    JOIN users u ON b.user_id = u.user_id
    JOIN parking_slots s ON b.slot_id = s.slot_id
    WHERE b.parking_id = ?
    ORDER BY b.created_at DESC LIMIT 6
");
$stmt->execute([$parking_id]);
$recent_bookings = $stmt->fetchAll();

// Chart data (last 7 days)
$chart_dates = [];
$chart_revenues = [];
for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $chart_dates[] = date('M d', strtotime($date));
    $stmt = $pdo->prepare("SELECT SUM(amount) FROM bookings WHERE parking_id = ? AND created_at = ? AND status = 'completed'");
    $stmt->execute([$parking_id, $date]);
    $chart_revenues[] = (float)($stmt->fetchColumn() ?: 0);
}

require_once __DIR__ . '/includes_header.php';
require_once __DIR__ . '/includes_manager_navbar.php';
?>

<div class="container-fluid py-4 px-lg-5">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold mb-0 text-primary"><i class="fa-solid fa-user-tie me-2"></i>Manager Dashboard</h3>
            <p class="text-secondary small fw-medium">Welcome, <?= htmlspecialchars($_SESSION['name']) ?>. Managing <strong class="text-info"><?= htmlspecialchars($manager_parking['parking_name']) ?></strong></p>
        </div>
        <div>
            <span class="badge bg-surface text-primary border border-secondary border-opacity-25 px-3 py-2 shadow-sm rounded-pill fs-6"><i class="fa-regular fa-calendar me-2"></i><?= date('F d, Y') ?></span>
        </div>
    </div>

    <!-- Parking Info Banner -->
    <div class="glass-panel p-4 mb-4 overflow-hidden position-relative">
        <div class="position-absolute end-0 top-0 h-100 d-flex align-items-center pe-5 opacity-10">
            <i class="fa-solid fa-square-parking" style="font-size: 8rem;"></i>
        </div>
        <div class="row align-items-center g-3 position-relative">
            <div class="col-md-7">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-info bg-opacity-15 text-info rounded-circle d-flex align-items-center justify-content-center shadow-sm border border-info border-opacity-25" style="width: 60px; height: 60px; min-width:60px;">
                        <i class="fa-solid fa-building fs-4"></i>
                    </div>
                    <div>
                        <h5 class="fw-bold mb-0 text-primary"><?= htmlspecialchars($manager_parking['parking_name']) ?></h5>
                        <p class="text-secondary mb-0 small"><i class="fa-solid fa-location-dot text-danger me-1"></i><?= htmlspecialchars($manager_parking['address']) ?>, <?= htmlspecialchars($manager_parking['city']) ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-5">
                <div class="d-flex gap-3 flex-wrap">
                    <div class="bg-surface rounded-3 px-3 py-2 shadow-sm border border-secondary border-opacity-25 text-center">
                        <div class="fw-bold text-primary fs-5"><?= $manager_parking['total_slots'] ?></div>
                        <div class="text-secondary small">Total Capacity</div>
                    </div>
                    <div class="bg-surface rounded-3 px-3 py-2 shadow-sm border border-secondary border-opacity-25 text-center">
                        <div class="fw-bold text-success fs-5"><?= $available_slots ?></div>
                        <div class="text-secondary small">Available</div>
                    </div>
                    <div class="bg-surface rounded-3 px-3 py-2 shadow-sm border border-secondary border-opacity-25 text-center">
                        <div class="fw-bold text-danger fs-5"><?= $booked_slots ?></div>
                        <div class="text-secondary small">Occupied</div>
                    </div>
                    <div class="bg-surface rounded-3 px-3 py-2 shadow-sm border border-secondary border-opacity-25 text-center">
                        <div class="fw-bold text-primary fs-5">₹<?= number_format($manager_parking['price_per_hour'], 0) ?></div>
                        <div class="text-secondary small">Per Hour</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Row -->
    <div class="row g-4 mb-5">
        <div class="col-sm-6 col-xl-3">
            <div class="card-3d h-100 p-4 d-flex align-items-center">
                <div class="stat-icon p-3 rounded-circle bg-primary bg-opacity-10 text-primary me-3 border border-primary border-opacity-25 shadow-sm">
                    <i class="fa-solid fa-car-side fs-4"></i>
                </div>
                <div>
                    <h6 class="text-secondary mb-1 text-uppercase small fw-bolder">Bookings Today</h6>
                    <h3 class="fw-bold mb-0 text-primary"><?= number_format($bookings_today) ?></h3>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card-3d h-100 p-4 d-flex align-items-center">
                <div class="stat-icon p-3 rounded-circle bg-success bg-opacity-10 text-success me-3 border border-success border-opacity-25 shadow-sm">
                    <i class="fa-solid fa-circle-check fs-4"></i>
                </div>
                <div>
                    <h6 class="text-secondary mb-1 text-uppercase small fw-bolder">Available Slots</h6>
                    <h3 class="fw-bold mb-0 text-success"><?= number_format($available_slots) ?></h3>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card-3d h-100 p-4 d-flex align-items-center">
                <div class="stat-icon p-3 rounded-circle bg-danger bg-opacity-10 text-danger me-3 border border-danger border-opacity-25 shadow-sm">
                    <i class="fa-solid fa-ban fs-4"></i>
                </div>
                <div>
                    <h6 class="text-secondary mb-1 text-uppercase small fw-bolder">Occupied Slots</h6>
                    <h3 class="fw-bold mb-0 text-danger"><?= number_format($booked_slots) ?></h3>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card-3d h-100 p-4 d-flex align-items-center overflow-hidden position-relative" style="background: linear-gradient(135deg, var(--primary-color), #6d28d9);">
                <div class="position-absolute end-0 opacity-10"><i class="fa-solid fa-money-bill-trend-up" style="font-size: 5rem;"></i></div>
                <div class="stat-icon p-3 rounded-circle bg-white bg-opacity-15 text-white me-3 border border-white border-opacity-25 shadow-sm">
                    <i class="fa-solid fa-indian-rupee-sign fs-4"></i>
                </div>
                <div>
                    <h6 class="text-white text-opacity-75 mb-1 text-uppercase small fw-bolder">Total Revenue</h6>
                    <h3 class="fw-bold mb-0 text-white">₹<?= number_format($total_revenue, 0) ?></h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Chart + Recent Bookings -->
    <div class="row g-4">
        <div class="col-lg-7">
            <div class="glass-panel h-100 overflow-hidden">
                <div class="bg-transparent border-bottom border-secondary border-opacity-25 p-4">
                    <h5 class="fw-bold mb-0 text-primary"><i class="fa-solid fa-chart-bar me-2"></i>7-Day Revenue Trend</h5>
                </div>
                <div class="p-4 bg-transparent">
                    <canvas id="managerChart" style="height: 280px;"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="glass-panel h-100 overflow-hidden">
                <div class="bg-transparent border-bottom border-secondary border-opacity-25 p-4 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0 text-primary"><i class="fa-solid fa-clock-rotate-left me-2"></i>Recent Bookings</h5>
                    <a href="<?= $base_url ?>/admin_manager_bookings.php" class="btn btn-sm btn-outline-primary rounded-pill">View All</a>
                </div>
                <div class="p-3 bg-transparent">
                    <?php if (empty($recent_bookings)): ?>
                        <div class="text-center py-5 text-muted">
                            <i class="fa-solid fa-inbox fa-3x opacity-25 mb-3"></i>
                            <p>No bookings yet for this location.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($recent_bookings as $b): ?>
                        <div class="d-flex align-items-center gap-3 p-3 rounded-3 hover-lift mb-2 bg-surface border border-secondary border-opacity-10">
                            <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width:40px;height:40px;min-width:40px;">
                                <i class="fa-solid <?= $b['vehicle_type'] === 'Bike' ? 'fa-motorcycle' : 'fa-car' ?> small"></i>
                            </div>
                            <div class="flex-grow-1 overflow-hidden">
                                <div class="fw-bold text-truncate small"><?= htmlspecialchars($b['name']) ?></div>
                                <div class="text-secondary" style="font-size:0.72rem;">Slot <strong><?= $b['slot_number'] ?></strong> • <?= date('d M', strtotime($b['created_at'])) ?></div>
                            </div>
                            <div class="text-end">
                                <div class="fw-bold text-primary small">₹<?= number_format($b['amount'], 0) ?></div>
                                <span class="status-badge status-<?= $b['status'] ?>" style="font-size:0.65rem; padding: 2px 8px;"><?= $b['status'] ?></span>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('managerChart').getContext('2d');
    const grad = ctx.createLinearGradient(0, 0, 0, 280);
    grad.addColorStop(0, 'rgba(67,97,238,0.5)');
    grad.addColorStop(1, 'rgba(67,97,238,0)');

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: <?= json_encode($chart_dates) ?>,
            datasets: [{
                label: 'Revenue (₹)',
                data: <?= json_encode($chart_revenues) ?>,
                backgroundColor: grad,
                borderColor: 'rgba(67,97,238,1)',
                borderWidth: 2,
                borderRadius: 6,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false }, tooltip: { callbacks: { label: ctx => '₹' + ctx.parsed.y.toFixed(2) } } },
            scales: {
                y: { beginAtZero: true, grid: { color: 'rgba(150,150,150,0.1)' }, ticks: { callback: val => '₹' + val } },
                x: { grid: { display: false } }
            }
        }
    });
});
</script>

<?php
echo '<script src="' . $base_url . '/js_admin.js"></script>';
require_once __DIR__ . '/includes_footer.php';
?>



