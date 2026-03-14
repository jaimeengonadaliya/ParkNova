<?php
require_once __DIR__ . '/config_db.php';

if (!isLoggedIn()) {
    redirect($base_url . '/user_login.php');
}

$user_id = $_SESSION['user_id'];

// Fetch user's bookings with payment data
$stmt = $pdo->prepare("
    SELECT 
        b.*, 
        p.parking_name, 
        p.city, 
        s.slot_number
    FROM bookings b
    JOIN parking_locations p ON b.parking_id = p.parking_id
    JOIN parking_slots s ON b.slot_id = s.slot_id
    WHERE b.user_id = ?
    ORDER BY b.created_at DESC
");
$stmt->execute([$user_id]);
$bookings = $stmt->fetchAll();

require_once __DIR__ . '/includes_header.php';
require_once __DIR__ . '/includes_navbar.php';
?>

<div class="container py-5">
    <?php if (isset($_GET['success']) && $_GET['success'] == 1): ?>
    <div class="alert border-0 mb-4 d-flex align-items-center gap-3 shadow-sm" style="background:linear-gradient(135deg,#d1fae5,#a7f3d0);border-radius:16px;">
        <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width:48px;height:48px;">
            <i class="fa-solid fa-check fs-5"></i>
        </div>
        <div>
            <div class="fw-bold text-success fs-5">🎉 Booking Confirmed!</div>
            <div class="text-success opacity-75 small">Your payment was successful and your parking slot is reserved.</div>
        </div>
        <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1 text-primary"><i class="fa-solid fa-clock-rotate-left me-2"></i>My Bookings</h2>
            <p class="text-secondary mb-0 fw-medium">Track and manage your parking reservations.</p>
        </div>
        <a href="<?= $base_url ?>/user_search_parking.php" class="btn-primary-3d text-decoration-none"><i class="fa-solid fa-plus me-2"></i>New Booking</a>
    </div>

    <div class="glass-panel overflow-hidden">
        <div class="card-body p-0">
            <div class="table-responsive p-4">
                <table id="bookingsTable" class="table table-hover align-middle mb-0" style="width:100%">
                    <thead class="table-light">
                        <tr>
                            <th class="py-3 text-muted small fw-bold text-uppercase">ID</th>
                            <th class="py-3 text-muted small fw-bold text-uppercase">Parking Area</th>
                            <th class="py-3 text-muted small fw-bold text-uppercase">Slot</th>
                            <th class="py-3 text-muted small fw-bold text-uppercase">Vehicle</th>
                            <th class="py-3 text-muted small fw-bold text-uppercase">Date & Time</th>
                            <th class="py-3 text-muted small fw-bold text-uppercase">Amount</th>
                            <th class="py-3 text-muted small fw-bold text-uppercase text-center">Status</th>
                            <th class="py-3 text-muted small fw-bold text-uppercase text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody class="border-top-0">
                        <?php foreach ($bookings as $b): ?>
                            <tr>
                                <td class="fw-bold text-muted">#<?= str_pad($b['booking_id'], 4, '0', STR_PAD_LEFT) ?></td>
                                <td>
                                    <div class="fw-bold text-dark"><?= htmlspecialchars($b['parking_name']) ?></div>
                                    <div class="small text-muted"><?= htmlspecialchars($b['city']) ?></div>
                                </td>
                                <td><span class="badge bg-dark bg-opacity-10 text-dark border px-2 py-1 fs-6"><?= htmlspecialchars($b['slot_number']) ?></span></td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="fa-solid <?= $b['vehicle_type'] === 'Bike' ? 'fa-motorcycle' : 'fa-car' ?> text-muted"></i>
                                        <span class="fw-medium font-monospace"><?= htmlspecialchars($b['vehicle_number']) ?></span>
                                    </div>
                                </td>
                                <td>
                                    <div class="fw-medium text-dark"><?= $b['booking_date'] ? date('M d, Y', strtotime($b['booking_date'])) : date('M d, Y', strtotime($b['created_at'])) ?></div>
                                    <div class="small text-muted"><?= date('h:i A', strtotime($b['start_time'])) ?> → <?= date('h:i A', strtotime($b['end_time'])) ?></div>
                                </td>
                                <td class="fw-bold text-primary">₹<?= number_format($b['amount'], 2) ?></td>
                                <td class="text-center">
                                    <span class="status-badge status-<?= $b['status'] ?>">
                                        <?= ucfirst($b['status']) ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <?php
                                        $bookingDate = $b['booking_date'] ?? date('Y-m-d', strtotime($b['created_at']));
                                        $bookingDateTime = strtotime($bookingDate . ' ' . $b['start_time']);
                                        $isFuture = $bookingDateTime > time();
                                    ?>
                                    <?php if ($b['status'] !== 'cancelled' && $isFuture): ?>
                                        <button class="btn btn-sm btn-outline-danger rounded-pill px-3 hover-lift cancel-btn" data-id="<?= $b['booking_id'] ?>">
                                            Cancel
                                        </button>
                                    <?php else: ?>
                                        <button class="btn btn-sm btn-light text-muted rounded-pill px-3" disabled>
                                            <i class="fa-solid fa-ban"></i>
                                        </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    $('#bookingsTable').DataTable({
        "order": [[ 0, "desc" ]], // Sort by ID desc by default
        "pageLength": 10,
        "language": {
            "search": "",
            "searchPlaceholder": "Search bookings..."
        },
        "dom": "<'row mb-3'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6 d-flex align-items-center justify-content-end'f>>" +
               "<'row'<'col-sm-12'tr>>" +
               "<'row mt-3'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>"
    });
    
    // Style DataTables search input
    $('.dataTables_filter input').addClass('form-control rounded-pill').css('width', '250px');
    $('.dataTables_length select').addClass('form-select border-0 bg-light');

    // Cancel Booking Handler
    const cancelBtns = document.querySelectorAll('.cancel-btn');
    cancelBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            if (confirm('Are you sure you want to cancel this booking? This action cannot be undone.')) {
                const bookingId = this.getAttribute('data-id');
                const btnRef = this;
                
                btnRef.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>';
                btnRef.disabled = true;

                const formData = new FormData();
                formData.append('booking_id', bookingId);

                fetch('<?= $base_url ?>/ajax_cancel_booking.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        location.reload(); // Reload to reflect changes
                    } else {
                        alert('Error: ' + data.message);
                        btnRef.innerHTML = 'Cancel';
                        btnRef.disabled = false;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Network error occurred.');
                    btnRef.innerHTML = 'Cancel';
                    btnRef.disabled = false;
                });
            }
        });
    });
});
</script>

<?php require_once __DIR__ . '/includes_footer.php'; ?>



