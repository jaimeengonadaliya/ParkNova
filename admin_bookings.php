<?php
require_once __DIR__ . '/config_db.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect($base_url . '/user_login.php');
}

$success = '';
$error = '';

// Handle Status Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $booking_id = (int)$_POST['booking_id'];
    $new_status = $_POST['status']; // pending, completed, cancelled
    
    // Validate status
    if (in_array($new_status, ['pending', 'completed', 'cancelled'])) {
        try {
            $pdo->beginTransaction();
            
            // Get current booking
            $stmt = $pdo->prepare("SELECT slot_id, status FROM bookings WHERE booking_id = ?");
            $stmt->execute([$booking_id]);
            $booking = $stmt->fetch();
            
            if ($booking) {
                // Update booking
                $stmt = $pdo->prepare("UPDATE bookings SET status = ? WHERE booking_id = ?");
                $stmt->execute([$new_status, $booking_id]);
                
                // If cancelled, free up slot
                if ($new_status === 'cancelled' && $booking['status'] !== 'cancelled') {
                    $stmt = $pdo->prepare("UPDATE parking_slots SET status = 'available' WHERE slot_id = ?");
                    $stmt->execute([$booking['slot_id']]);
                } 
                // If it was cancelled but changed to completed/pending, book slot
                elseif ($new_status !== 'cancelled' && $booking['status'] === 'cancelled') {
                    $stmt = $pdo->prepare("UPDATE parking_slots SET status = 'booked' WHERE slot_id = ?");
                    $stmt->execute([$booking['slot_id']]);
                }
                
                $pdo->commit();
                $success = "Booking status updated successfully.";
            } else {
                $pdo->rollBack();
                $error = "Booking not found.";
            }
        } catch (PDOException $e) {
            $pdo->rollBack();
            $error = "Failed to update status. Error: " . $e->getMessage();
        }
    }
}

// Fetch all bookings
$stmt = $pdo->query("
    SELECT b.*, u.name, u.email, p.parking_name, s.slot_number 
    FROM bookings b
    JOIN users u ON b.user_id = u.user_id
    JOIN parking_locations p ON b.parking_id = p.parking_id
    JOIN parking_slots s ON b.slot_id = s.slot_id
    ORDER BY b.created_at DESC
");
$bookings = $stmt->fetchAll();

require_once __DIR__ . '/includes_header.php';
require_once __DIR__ . '/includes_admin_navbar.php';
?>

<div class="container-fluid py-4 px-lg-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold mb-0 text-primary"><i class="fa-solid fa-book-bookmark me-2"></i>Booking Records</h3>
            <p class="text-secondary small fw-medium">Manage user reservations and override statuses.</p>
        </div>
    </div>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger border-0 border-start border-4 border-danger shadow-sm mb-4">
            <i class="fa-solid fa-triangle-exclamation me-2"></i><?= $error ?>
        </div>
    <?php endif; ?>
    
    <?php if (!empty($success)): ?>
        <div class="alert alert-success border-0 border-start border-4 border-success shadow-sm mb-4">
            <i class="fa-solid fa-check-circle me-2"></i><?= $success ?>
        </div>
    <?php endif; ?>

    <div class="glass-panel overflow-hidden">
        <div class="card-body p-4">
            <div class="table-responsive">
                <table class="table table-hover align-middle admin-datatable w-100">
                    <thead class="table-light">
                        <tr>
                            <th class="py-3 text-muted small fw-bold text-uppercase">B. ID</th>
                            <th class="py-3 text-muted small fw-bold text-uppercase">Customer Info</th>
                            <th class="py-3 text-muted small fw-bold text-uppercase">Location / Slot</th>
                            <th class="py-3 text-muted small fw-bold text-uppercase">Booking Timing</th>
                            <th class="py-3 text-muted small fw-bold text-uppercase">Vehicle & Amount</th>
                            <th class="py-3 text-muted small fw-bold text-uppercase text-center">Status</th>
                            <th class="py-3 text-muted small fw-bold text-uppercase text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody class="border-top-0">
                        <?php foreach ($bookings as $b): ?>
                            <tr>
                                <td class="fw-bold text-muted">#<?= $b['booking_id'] ?></td>
                                <td>
                                    <div class="fw-bold text-dark"><?= htmlspecialchars($b['name']) ?></div>
                                    <div class="small text-muted"><a href="mailto:<?= htmlspecialchars($b['email']) ?>" class="text-decoration-none"><?= htmlspecialchars($b['email']) ?></a></div>
                                </td>
                                <td>
                                    <div class="fw-medium text-dark"><?= htmlspecialchars($b['parking_name']) ?></div>
                                    <div class="small text-muted">Slot <span class="badge bg-light text-dark border"><?= htmlspecialchars($b['slot_number']) ?></span></div>
                                </td>
                                <td>
                                    <div class="fw-medium text-dark"><?= date('M d, Y', strtotime($b['created_at'])) ?></div>
                                    <div class="small text-muted"><?= date('h:i A', strtotime($b['start_time'])) ?> - <?= date('h:i A', strtotime($b['end_time'])) ?></div>
                                </td>
                                <td>
                                    <div class="fw-bold text-dark"><i class="fa-solid <?= $b['vehicle_type'] === 'Bike' ? 'fa-motorcycle' : 'fa-car' ?> text-muted me-1"></i> <?= htmlspecialchars($b['vehicle_number']) ?></div>
                                    <div class="fw-bold text-primary mt-1">₹<?= number_format($b['amount'], 2) ?></div>
                                </td>
                                <td class="text-center">
                                    <span class="status-badge status-<?= $b['status'] ?> text-uppercase py-1 px-3 d-inline-block" style="font-size: 0.75rem;">
                                        <?= $b['status'] ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-light border rounded-circle p-2 edit-status-btn" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#statusModal"
                                            data-id="<?= $b['booking_id'] ?>"
                                            data-status="<?= $b['status'] ?>"
                                            title="Update Status">
                                        <i class="fa-solid fa-gear text-muted"></i>
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

<!-- Status Update Modal -->
<div class="modal fade" id="statusModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 rounded-4 shadow-lg overflow-hidden glass-panel">
            <div class="modal-header border-bottom border-secondary border-opacity-25 p-4" style="background: linear-gradient(135deg, rgba(67, 97, 238, 0.1), rgba(109, 40, 217, 0.1));">
                <h6 class="modal-title fw-bold text-primary"><i class="fa-solid fa-gear me-2"></i>Update Status</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="" method="POST">
                <div class="modal-body p-4">
                    <input type="hidden" name="update_status" value="1">
                    <input type="hidden" name="booking_id" id="status_booking_id">
                    
                    <div class="mb-3">
                        <label class="form-label text-secondary fw-bold small text-uppercase">Booking Status</label>
                        <select name="status" id="status_select" class="form-select bg-surface border-secondary border-opacity-25" required>
                            <option value="pending">Pending</option>
                            <option value="completed">Completed (Confirmed)</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                        <small class="text-danger mt-2 d-none fw-medium" id="cancelWarning"><i class="fa-solid fa-circle-info me-1"></i> This frees up the parking slot.</small>
                    </div>
                </div>
                <div class="modal-footer border-top border-secondary border-opacity-25 p-4 bg-light bg-opacity-50">
                    <button type="button" class="btn btn-light rounded-pill px-4 text-secondary fw-bold hover-lift" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn-primary-3d px-4"><i class="fa-solid fa-save me-2"></i>Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const editBtns = document.querySelectorAll('.edit-status-btn');
    const statusSelect = document.getElementById('status_select');
    const cancelWarning = document.getElementById('cancelWarning');
    
    editBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            document.getElementById('status_booking_id').value = this.getAttribute('data-id');
            const currentStatus = this.getAttribute('data-status');
            statusSelect.value = currentStatus;
            
            checkWarning();
        });
    });
    
    statusSelect.addEventListener('change', checkWarning);
    
    function checkWarning() {
        if(statusSelect.value === 'cancelled') {
            cancelWarning.classList.remove('d-none');
        } else {
            cancelWarning.classList.add('d-none');
        }
    }
});
</script>

<?php 
echo '<script src="' . $base_url . '/js_admin.js"></script>';
require_once __DIR__ . '/includes_footer.php'; 
?>



