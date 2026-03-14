<?php
require_once __DIR__ . '/config_db.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect($base_url . '/user_login.php');
}

// Fetch dashboard statistics
// 1. Total regular users only
$stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'user'");
$total_users = $stmt->fetchColumn();

// 1b. Total managers
$stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'manager'");
$total_managers = $stmt->fetchColumn();

// 2. Total Parking Locations
$stmt = $pdo->query("SELECT COUNT(*) FROM parking_locations");
$total_parkings = $stmt->fetchColumn();

// 3. Total Bookings Today
$today = date('Y-m-d');
$stmt = $pdo->prepare("SELECT COUNT(*) FROM bookings WHERE DATE(created_at) = ?");
$stmt->execute([$today]);
$bookings_today = $stmt->fetchColumn();

// 4. Total Revenue (Completed Bookings)
$stmt = $pdo->query("SELECT SUM(amount) FROM bookings WHERE status = 'completed'");
$total_revenue = $stmt->fetchColumn() ?: 0;

// 5. Recent Bookings for table
$stmt = $pdo->query("
    SELECT b.booking_id, u.name, p.parking_name, b.created_at, b.amount, b.status 
    FROM bookings b
    JOIN users u ON b.user_id = u.user_id
    JOIN parking_locations p ON b.parking_id = p.parking_id
    ORDER BY b.created_at DESC LIMIT 5
");
$recent_bookings = $stmt->fetchAll();

// 6. Data for Revenue Chart (Last 7 days)
$chart_dates = [];
$chart_revenues = [];
for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $chart_dates[] = date('M d', strtotime($date));
    
    $stmt = $pdo->prepare("SELECT SUM(amount) FROM bookings WHERE DATE(created_at) = ? AND status = 'completed'");
    $stmt->execute([$date]);
    $rev = $stmt->fetchColumn() ?: 0;
    $chart_revenues[] = $rev;
}

require_once __DIR__ . '/includes_header.php';
// Include custom admin CSS
require_once __DIR__ . '/includes_admin_navbar.php';
?>

<div class="container-fluid py-4 px-lg-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold mb-0 text-primary"><i class="fa-solid fa-shield-halved me-2"></i>Super Admin Dashboard</h3>
            <p class="text-secondary small fw-medium">Welcome back, <?= htmlspecialchars($_SESSION['name']) ?>. Here is the active tracking overview.</p>
        </div>
        <div class="d-flex gap-3 mt-3 mt-md-0">
            <a href="<?= $base_url ?>/admin_manage_parking.php" class="btn btn-success-3d px-4 rounded-pill shadow-sm">
                <i class="fa-solid fa-plus-circle me-2"></i>Add New Parking
            </a>
            <span class="badge bg-surface text-primary border border-secondary border-opacity-25 px-3 py-2 shadow-sm rounded-pill fs-6 d-none d-md-inline-flex align-items-center">
                <i class="fa-regular fa-calendar me-2"></i><?= date('F d, Y') ?>
            </span>
        </div>
    </div>

    <!-- Stats Row -->
    <div class="row g-4 mb-5">
        <div class="col-sm-6 col-xl-3">
            <div class="card-3d h-100 p-4 d-flex align-items-center">
                <div class="stat-icon p-3 rounded-circle bg-primary bg-opacity-10 text-primary me-3 border border-primary border-opacity-25 shadow-sm">
                    <i class="fa-solid fa-users fs-4"></i>
                </div>
                <div>
                    <h6 class="text-secondary mb-1 text-uppercase small fw-bolder tracking-wider">Total Drivers</h6>
                    <h3 class="fw-bold mb-0 text-primary"><?= number_format($total_users) ?></h3>
                </div>
            </div>
        </div>
        <!-- Managers KPI -->
        <div class="col-sm-6 col-xl-3">
            <div class="card-3d h-100 p-4 d-flex align-items-center">
                <div class="stat-icon p-3 rounded-circle bg-warning bg-opacity-15 text-warning me-3 border border-warning border-opacity-25 shadow-sm">
                    <i class="fa-solid fa-user-tie fs-4"></i>
                </div>
                <div>
                    <h6 class="text-secondary mb-1 text-uppercase small fw-bolder">Managers</h6>
                    <h3 class="fw-bold mb-0 text-warning"><?= number_format($total_managers) ?></h3>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card-3d h-100 p-4 d-flex align-items-center">
                <div class="stat-icon p-3 rounded-circle bg-success bg-opacity-10 text-success me-3 border border-success border-opacity-25 shadow-sm">
                    <i class="fa-solid fa-location-dot fs-4"></i>
                </div>
                <div>
                    <h6 class="text-secondary mb-1 text-uppercase small fw-bolder tracking-wider">Parking Lots</h6>
                    <h3 class="fw-bold mb-0 text-primary"><?= number_format($total_parkings) ?></h3>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card-3d h-100 p-4 d-flex align-items-center">
                <div class="stat-icon p-3 rounded-circle bg-warning bg-opacity-10 text-warning me-3 border border-warning border-opacity-25 shadow-sm">
                    <i class="fa-solid fa-calendar-check fs-4"></i>
                </div>
                <div>
                    <h6 class="text-secondary mb-1 text-uppercase small fw-bolder tracking-wider">Bookings Today</h6>
                    <h3 class="fw-bold mb-0 text-primary"><?= number_format($bookings_today) ?></h3>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card-3d h-100 p-4 d-flex align-items-center">
                <div class="stat-icon p-3 rounded-circle bg-info bg-opacity-10 text-info me-3 border border-info border-opacity-25 shadow-sm">
                    <i class="fa-solid fa-indian-rupee-sign fs-4"></i>
                </div>
                <div>
                    <h6 class="text-secondary mb-1 text-uppercase small fw-bolder tracking-wider">Total Revenue</h6>
                    <h3 class="fw-bold mb-0 text-gradient">₹<?= number_format($total_revenue, 2) ?></h3>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <!-- Chart Column -->
        <div class="col-lg-8">
            <div class="glass-panel h-100">
                <div class="border-bottom border-secondary border-opacity-25 p-4 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0 text-primary">Revenue Overview (Last 7 Days)</h5>
                    <div class="d-flex gap-2">
                        <a href="<?= $base_url ?>/admin_reports.php" class="btn btn-sm btn-outline-secondary rounded-pill">Detailed Reports</a>
                        <a href="<?= $base_url ?>/admin_manage_managers.php" class="btn btn-sm btn-outline-primary rounded-pill">Assign Managers</a>
                    </div>
                </div>
                <div class="card-body p-4 bg-transparent">
                    <div class="chart-container" style="height: 350px;">
                        <canvas id="revenueChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Bookings Column -->
        <div class="col-lg-4">
            <div class="card-3d overflow-hidden h-100">
                <div class="bg-transparent border-bottom border-secondary border-opacity-25 p-4 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0 text-primary">Recent Bookings</h5>
                    <a href="admin_bookings.php" class="btn btn-sm btn-outline-primary rounded-pill fw-bold">View All</a>
                </div>
                <div class="card-body p-0 bg-transparent">
                    <div class="list-group list-group-flush border-0">
                        <?php if (count($recent_bookings) === 0): ?>
                            <div class="p-4 text-center text-secondary">No recent bookings found.</div>
                        <?php else: ?>
                            <?php foreach ($recent_bookings as $b): ?>
                                <div class="list-group-item bg-transparent p-4 border-bottom border-secondary border-opacity-10 text-primary">
                                    <div class="d-flex justify-content-between align-items-start mb-1">
                                        <h6 class="fw-bold mb-0 text-dark"><?= htmlspecialchars($b['name']) ?></h6>
                                        <span class="fw-bold text-gradient">₹<?= number_format($b['amount'], 2) ?></span>
                                    </div>
                                    <p class="small text-secondary mb-2 fw-medium"><i class="fa-solid fa-location-dot me-1"></i> <?= htmlspecialchars($b['parking_name']) ?></p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="small fw-medium text-secondary"><?= date('M d, Y', strtotime($b['created_at'])) ?></span>
                                        <span class="status-badge status-<?= $b['status'] ?> py-1 px-2 border-0 shadow-sm" style="font-size: 0.7rem;">
                                            <?= ucfirst($b['status']) ?>
                                        </span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('revenueChart').getContext('2d');
    
    // Gradient for line chart
    let gradient = ctx.createLinearGradient(0, 0, 0, 400);
    gradient.addColorStop(0, 'rgba(67, 97, 238, 0.5)');   
    gradient.addColorStop(1, 'rgba(67, 97, 238, 0.05)');
    
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
                pointBackgroundColor: '#ffffff',
                pointBorderColor: '#4361ee',
                pointBorderWidth: 2,
                pointRadius: 4,
                pointHoverRadius: 6,
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: 'rgba(15, 23, 42, 0.9)',
                    titleFont: { size: 13, family: "'Outfit', sans-serif" },
                    bodyFont: { size: 14, family: "'Outfit', sans-serif", weight: 'bold' },
                    padding: 12,
                    cornerRadius: 8,
                    displayColors: false,
                    callbacks: {
                        label: function(context) {
                            return '₹' + context.parsed.y.toLocaleString();
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(0, 0, 0, 0.05)',
                        drawBorder: false
                    },
                    ticks: {
                        font: { family: "'Outfit', sans-serif" },
                        callback: function(value) {
                            return '₹' + value;
                        }
                    }
                },
                x: {
                    grid: {
                        display: false,
                        drawBorder: false
                    },
                    ticks: {
                        font: { family: "'Outfit', sans-serif" }
                    }
                }
            }
        }
    });
});
</script>

<?php 
// Include specific admin.js before footer
echo '<script src="' . $base_url . '/js_admin.js"></script>';
require_once __DIR__ . '/includes_footer.php'; 
?>



