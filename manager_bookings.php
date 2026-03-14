<?php
require_once __DIR__ . '/config_db.php';
if (!isLoggedIn() || !isManager()) redirect($base_url . '/user_login.php');

$manager_id = $_SESSION['user_id'];

// Get assigned parking
$stmt = $pdo->prepare("SELECT * FROM parking_locations WHERE manager_id = ?");
$stmt->execute([$manager_id]);
$manager_parking = $stmt->fetch();
if (!$manager_parking) die('<div class="container py-5 text-center"><h3>No parking assigned.</h3></div>');

$parking_id = $manager_parking['parking_id'];
$success = ''; $error = '';

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $booking_id = (int)$_POST['booking_id'];
    $new_status = $_POST['status'];
    if (in_array($new_status, ['pending','completed','cancelled'])) {
        try {
            $pdo->beginTransaction();
            $stmt = $pdo->prepare("SELECT slot_id, status FROM bookings WHERE booking_id = ? AND parking_id = ?");
            $stmt->execute([$booking_id, $parking_id]);
            $booking = $stmt->fetch();
            if ($booking) {
                $pdo->prepare("UPDATE bookings SET status = ? WHERE booking_id = ?")->execute([$new_status, $booking_id]);
                if ($new_status === 'cancelled' && $booking['status'] !== 'cancelled') {
                    $pdo->prepare("UPDATE parking_slots SET status='available' WHERE slot_id=?")->execute([$booking['slot_id']]);
                } elseif ($new_status !== 'cancelled' && $booking['status'] === 'cancelled') {
                    $pdo->prepare("UPDATE parking_slots SET status='booked' WHERE slot_id=?")->execute([$booking['slot_id']]);
                }
                $pdo->commit(); $success = "Booking status updated.";
            } else { $pdo->rollBack(); $error = "Booking not found."; }
        } catch (PDOException $e) { $pdo->rollBack(); $error = $e->getMessage(); }
    }
}

// Fetch all bookings for this parking
$stmt = $pdo->prepare("
    SELECT b.*, u.name, u.email, s.slot_number
    FROM bookings b
    JOIN users u ON b.user_id = u.user_id
    JOIN parking_slots s ON b.slot_id = s.slot_id
    WHERE b.parking_id = ?
    ORDER BY b.created_at DESC
");
$stmt->execute([$parking_id]);
$bookings = $stmt->fetchAll();

require_once __DIR__ . '/includes_header.php';
require_once __DIR__ . '/includes_manager_navbar.php';
?>

<div class="container-fluid py-4 px-lg-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold mb-0 text-primary"><i class="fa-solid fa-book-bookmark me-2"></i>Booking Records</h3>
            <p class="text-secondary small fw-medium"><?= htmlspecialchars($manager_parking['parking_name']) ?> — <?= htmlspecialchars($manager_parking['city']) ?></p>
        </div>
    </div>

    <?php if ($error): ?><div class="alert glass-panel border-start border-4 border-danger mb-4"><i class="fa-solid fa-triangle-exclamation me-2 text-danger"></i><?= $error ?></div><?php endif; ?>
    <?php if ($success): ?><div class="alert glass-panel border-start border-4 border-success mb-4"><i class="fa-solid fa-check-circle me-2 text-success"></i><?= $success ?></div><?php endif; ?>

    <div class="glass-panel overflow-hidden">
        <div class="card-body p-4">
            <div class="table-responsive">
                <table class="table table-hover align-middle admin-datatable w-100">
                    <thead class="table-light">
                        <tr>
                            <th class="py-3 text-secondary small fw-bold text-uppercase">B. ID</th>
                            <th class="py-3 text-secondary small fw-bold text-uppercase">Customer</th>
                            <th class="py-3 text-secondary small fw-bold text-uppercase">Slot</th>
                            <th class="py-3 text-secondary small fw-bold text-uppercase">Booking Date</th>
                            <th class="py-3 text-secondary small fw-bold text-uppercase">Vehicle No.</th>
                            <th class="py-3 text-secondary small fw-bold text-uppercase">Amount</th>
                            <th class="py-3 text-secondary small fw-bold text-uppercase text-center">Status</th>
                            <th class="py-3 text-secondary small fw-bold text-uppercase text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($bookings as $b): ?>
                        <tr>
                            <td class="fw-bold text-secondary">#<?= $b['booking_id'] ?></td>
                            <td>
                                <div class="fw-bold"><?= htmlspecialchars($b['name']) ?></div>
                                <div class="small text-secondary"><?= htmlspecialchars($b['email']) ?></div>
                            </td>
                            <td><span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 rounded-pill px-3"><?= htmlspecialchars($b['slot_number']) ?></span></td>
                            <td>
                                <div class="fw-medium"><?= date('M d, Y', strtotime($b['created_at'])) ?></div>
                                <div class="small text-secondary"><?= date('h:i A', strtotime($b['start_time'])) ?> – <?= date('h:i A', strtotime($b['end_time'])) ?></div>
                            </td>
                            <td class="fw-bold"><?= htmlspecialchars($b['vehicle_number']) ?></td>
                            <td class="fw-bold text-primary">₹<?= number_format($b['amount'], 2) ?></td>
                            <td class="text-center"><span class="status-badge status-<?= $b['status'] ?> text-uppercase py-1 px-3 d-inline-block" style="font-size:0.75rem"><?= $b['status'] ?></span></td>
                            <td class="text-center">
                                <button class="btn btn-sm btn-light border rounded-circle p-2 edit-status-btn" data-bs-toggle="modal" data-bs-target="#statusModal" data-id="<?= $b['booking_id'] ?>" data-status="<?= $b['status'] ?>" title="Update Status">
                                    <i class="fa-solid fa-gear text-secondary"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Status Modal -->
<div class="modal fade" id="statusModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 rounded-4 shadow-lg bg-surface">
            <div class="modal-header border-bottom border-secondary border-opacity-25 p-3">
                <h6 class="modal-title fw-bold text-primary">Update Booking Status</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="" method="POST">
                <div class="modal-body p-4">
                    <input type="hidden" name="update_status" value="1">
                    <input type="hidden" name="booking_id" id="status_booking_id">
                    <div class="mb-3">
                        <label class="form-label text-secondary fw-bold small text-uppercase">New Status</label>
                        <select name="status" id="status_select" class="form-select" required>
                            <option value="pending">Pending</option>
                            <option value="completed">Completed</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-top border-secondary border-opacity-25 p-3 bg-surface">
                    <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-sm btn-primary rounded-pill px-4 fw-bold">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.querySelectorAll('.edit-status-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        document.getElementById('status_booking_id').value = this.dataset.id;
        document.getElementById('status_select').value = this.dataset.status;
    });
});
</script>

<?php
echo '<script src="' . $base_url . '/js_admin.js"></script>';
require_once __DIR__ . '/includes_footer.php';
?>



