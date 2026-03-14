<?php
require_once __DIR__ . '/config_db.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect($base_url . '/user_login.php');
}

$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01'); // First of current month
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d'); // Today

// Fetch Summary Data within date range
// Only include 'completed' bookings for revenue
$stmt = $pdo->prepare("
    SELECT 
        COUNT(*) as total_bookings,
        SUM(amount) as total_revenue
    FROM bookings 
    WHERE created_at BETWEEN ? AND ? 
    AND status = 'completed'
");
$stmt->execute([$start_date, $end_date]);
$summary = $stmt->fetch();

// Fetch daily data for chart
$stmt = $pdo->prepare("
    SELECT created_at, SUM(amount) as daily_revenue, COUNT(*) as daily_count
    FROM bookings 
    WHERE created_at BETWEEN ? AND ? 
    AND status = 'completed'
    GROUP BY created_at
    ORDER BY created_at ASC
");
$stmt->execute([$start_date, $end_date]);
$dailyData = $stmt->fetchAll();

$chartLabels = [];
$chartRevenue = [];
$chartCounts = [];

foreach ($dailyData as $row) {
    $chartLabels[] = date('M d', strtotime($row['created_at']));
    $chartRevenue[] = (float)$row['daily_revenue'];
    $chartCounts[] = (int)$row['daily_count'];
}

require_once __DIR__ . '/includes_header.php';
require_once __DIR__ . '/includes_admin_navbar.php';
?>

<div class="container-fluid py-4 px-lg-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold mb-0 text-primary"><i class="fa-solid fa-chart-line me-2"></i>Financial &amp; Sales Reports</h3>
            <p class="text-secondary small fw-medium">Analyze parking revenue trends over time.</p>
        </div>
        <button class="btn-primary-3d" onclick="window.print()">
            <i class="fa-solid fa-print me-2"></i>Print Report
        </button>
    </div>

    <!-- Filter Form -->
    <div class="card-3d mb-4 d-print-none">
        <div class="card-body p-4 bg-surface">
            <form action="" method="GET" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label text-muted small fw-bold">From Date</label>
                    <input type="date" name="start_date" class="form-control bg-light" value="<?= $start_date ?>" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label text-muted small fw-bold">To Date</label>
                    <input type="date" name="end_date" class="form-control bg-light" value="<?= $end_date ?>" required>
                </div>
                <div class="col-md-4 text-md-end">
                    <button type="submit" class="btn btn-primary rounded-pill px-5 fw-bold w-100 shadow-sm">Generate Report</button>
                </div>
            </form>
        </div>
    </div>

    <div class="row g-4 print-container">
        <!-- Summary KPI Cards -->
        <div class="col-md-6">
            <div class="card-3d h-100 overflow-hidden">
                <div class="card-body p-5 position-relative text-white" style="background: linear-gradient(135deg, var(--primary-color), #6d28d9);">
                    <i class="fa-solid fa-money-bill-trend-up position-absolute opacity-10" style="font-size: 8rem; right: -20px; bottom: -20px;"></i>
                    <h6 class="text-white text-opacity-75 mb-2 text-uppercase fw-bold tracking-wider">Total Revenue Selected Period</h6>
                    <h1 class="display-4 fw-bold mb-0">₹<?= number_format($summary['total_revenue'] ?: 0, 2) ?></h1>
                    <p class="mt-4 mb-0"><i class="fa-regular fa-calendar me-2"></i> <?= date('d M, Y', strtotime($start_date)) ?> - <?= date('d M, Y', strtotime($end_date)) ?></p>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card-3d h-100 overflow-hidden">
                <div class="card-body p-5 position-relative text-white" style="background: linear-gradient(135deg, #10b981, #059669);">
                    <i class="fa-solid fa-car-on position-absolute opacity-10" style="font-size: 8rem; right: -20px; bottom: -20px;"></i>
                    <h6 class="text-white text-opacity-75 mb-2 text-uppercase fw-bold tracking-wider">Total Cars Parked</h6>
                    <h1 class="display-4 fw-bold mb-0"><?= number_format($summary['total_bookings'] ?: 0) ?></h1>
                    <p class="mt-4 mb-0"><i class="fa-solid fa-circle-check me-2"></i> Successfully Completed</p>
                </div>
            </div>
        </div>

        <!-- Chart -->
        <div class="col-12">
            <div class="glass-panel overflow-hidden mb-4">
                <div class="bg-transparent border-bottom border-secondary border-opacity-25 p-4">
                    <h5 class="fw-bold mb-0 text-primary">Daily Revenue Trend</h5>
                </div>
                <div class="card-body p-4 bg-transparent">
                    <?php if (empty($chartLabels)): ?>
                        <div class="text-center py-5">
                            <i class="fa-solid fa-chart-line fa-3x text-muted opacity-25 mb-3"></i>
                            <p class="text-muted">No completed bookings found for the selected date range.</p>
                        </div>
                    <?php else: ?>
                        <div style="height: 400px;">
                            <canvas id="reportChart"></canvas>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if (!empty($chartLabels)): ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('reportChart').getContext('2d');
    
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: <?= json_encode($chartLabels) ?>,
            datasets: [
                {
                    label: 'Revenue (₹)',
                    data: <?= json_encode($chartRevenue) ?>,
                    backgroundColor: 'rgba(67, 97, 238, 0.8)',
                    borderColor: 'rgba(67, 97, 238, 1)',
                    borderWidth: 1,
                    borderRadius: 4,
                    yAxisID: 'y'
                },
                {
                    label: 'Bookings (Count)',
                    data: <?= json_encode($chartCounts) ?>,
                    type: 'line',
                    borderColor: '#f59e0b',
                    backgroundColor: '#ffffff',
                    pointBackgroundColor: '#f59e0b',
                    pointBorderColor: '#ffffff',
                    borderWidth: 3,
                    pointRadius: 5,
                    tension: 0.3,
                    yAxisID: 'y1'
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                mode: 'index',
                intersect: false,
            },
            plugins: {
                tooltip: {
                    backgroundColor: 'rgba(15, 23, 42, 0.9)',
                    titleFont: { size: 13, family: "'Outfit', sans-serif" },
                    bodyFont: { size: 14, family: "'Outfit', sans-serif" },
                    padding: 12,
                    cornerRadius: 8
                }
            },
            scales: {
                y: {
                    type: 'linear',
                    display: true,
                    position: 'left',
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Revenue (₹)'
                    },
                    grid: {
                        color: 'rgba(0, 0, 0, 0.05)'
                    }
                },
                y1: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Number of Bookings'
                    },
                    grid: {
                        drawOnChartArea: false
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            }
        }
    });
});
</script>
<?php endif; ?>

<style>
@media print {
    body { background-color: #fff; }
    .navbar { display: none !important; }
    .admin-card { box-shadow: none !important; border: 1px solid #ddd !important; }
    .bg-primary { background-color: #4361ee !important; -webkit-print-color-adjust: exact; }
    .text-white { color: #fff !important; }
}
</style>

<?php require_once __DIR__ . '/includes_footer.php'; ?>



