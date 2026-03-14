<?php
require_once __DIR__ . '/config_db.php';

if (!isLoggedIn()) {
    redirect($base_url . '/user_login.php');
}

$booking_id = $_GET['booking'] ?? null;

if (!$booking_id) {
    redirect($base_url . '/user_dashboard.php');
}

// Fetch Booking details
$stmt = $pdo->prepare("
    SELECT 
        b.*, 
        p.parking_name, 
        p.address, 
        p.city, 
        s.slot_number, 
        pay.razorpay_payment_id 
    FROM bookings b
    JOIN parking_locations p ON b.parking_id = p.parking_id
    JOIN parking_slots s ON b.slot_id = s.slot_id
    LEFT JOIN payments pay ON b.booking_id = pay.booking_id
    WHERE b.booking_id = ? AND b.user_id = ?
");
$stmt->execute([$booking_id, $_SESSION['user_id']]);
$booking = $stmt->fetch();

if (!$booking) {
    echo "Booking not found or access denied.";
    exit;
}

// Generates a mock "QR string" to be rendered by a JS library
$qrData = "PARKNOVA|BK-" . $booking['booking_id'] . "|" . $booking['vehicle_number'] . "|" . $booking['slot_number'];

require_once __DIR__ . '/includes_header.php';
require_once __DIR__ . '/includes_navbar.php';
?>

<!-- Include basic QR Code JS Library -->
<script src="https://cdnjs.cloudflare.com/ajax_libs/qrcodejs/1.0.0/qrcode.min.js"></script>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-10 col-lg-8">
            <div class="text-center mb-4">
                <div class="d-inline-flex bg-success bg-opacity-10 text-success p-4 rounded-circle mb-3 align-items-center justify-content-center border border-success border-opacity-25" style="width: 100px; height: 100px;">
                    <i class="fa-solid fa-check fs-1"></i>
                </div>
                <h1 class="fw-bold text-success mb-2">Payment Successful!</h1>
                <p class="text-secondary fs-5">Your parking slot has been securely booked.</p>
            </div>

            <div class="card-3d overflow-hidden position-relative">
                <!-- Receipt Jagged Edges -->
                <div class="position-absolute top-0 start-0 w-100" style="height: 10px; background: repeating-linear-gradient(45deg, transparent, transparent 10px, white 10px, white 20px);"></div>
                
                <div class="card-body p-4 p-md-5 bg-surface mt-2">
                    <div class="row align-items-center">
                        <div class="col-md-7 border-end border-secondary border-opacity-25 pe-md-4">
                            <h4 class="fw-bold text-primary mb-1"><?= htmlspecialchars($booking['parking_name']) ?></h4>
                            <p class="text-secondary mb-4 pb-3 border-bottom border-secondary border-opacity-25"><i class="fa-solid fa-map-pin me-1"></i> <?= htmlspecialchars($booking['address'] . ', ' . $booking['city']) ?></p>
                            
                            <div class="row g-4 mb-4">
                                <div class="col-6">
                                    <div class="small fw-bold text-secondary text-uppercase mb-1">Booking ID</div>
                                    <div class="fw-medium text-dark font-monospace">#<?= str_pad($booking['booking_id'], 6, '0', STR_PAD_LEFT) ?></div>
                                </div>
                                <div class="col-6">
                                    <div class="small fw-bold text-secondary text-uppercase mb-1">Payment Ref</div>
                                    <div class="fw-medium text-dark font-monospace small text-truncate" title="<?= htmlspecialchars($booking['razorpay_payment_id']) ?>">
                                        <?= htmlspecialchars($booking['razorpay_payment_id']) ?>
                                    </div>
                                </div>
                                
                                <div class="col-6">
                                    <div class="small fw-bold text-secondary text-uppercase mb-1">Slot Assigned</div>
                                    <div class="fw-bold text-primary fs-4"><?= htmlspecialchars($booking['slot_number']) ?></div>
                                </div>
                                <div class="col-6">
                                    <div class="small fw-bold text-secondary text-uppercase mb-1">Vehicle</div>
                                    <div class="fw-bold text-dark fs-5 font-monospace"><?= htmlspecialchars($booking['vehicle_number']) ?> <small class="text-muted fw-normal fs-6">(<?= $booking['vehicle_type'] ?>)</small></div>
                                </div>
                            </div>
                            
                            <div class="bg-light p-3 rounded-3 mb-4">
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-secondary fw-medium">Date</span>
                                    <span class="fw-bold text-dark"><?= date('F j, Y', strtotime($booking['booking_date'])) ?></span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-secondary fw-medium">Time</span>
                                    <span class="fw-bold text-dark"><?= date('h:i A', strtotime($booking['start_time'])) ?> to <?= date('h:i A', strtotime($booking['end_time'])) ?></span>
                                </div>
                                <hr class="border-secondary opacity-25">
                                <div class="d-flex justify-content-between">
                                    <span class="text-secondary fw-bold text-uppercase pt-1">Total Paid</span>
                                    <span class="fw-bold text-success fs-4">₹<?= number_format($booking['amount'], 2) ?></span>
                                </div>
                            </div>
                            
                        </div>
                        
                        <!-- QR Code Section -->
                        <div class="col-md-5 ps-md-4 text-center mt-4 mt-md-0">
                            <h5 class="fw-bold text-primary mb-3">Gate Pass</h5>
                            <div class="d-inline-block bg-white p-3 rounded-4 shadow-sm border border-secondary border-opacity-25 mb-3" id="qrcode"></div>
                            <p class="small text-secondary fw-medium lh-sm">Scan this QR code at the entry gate of the parking lot.</p>
                            
                            <button onclick="window.print()" class="btn btn-outline-primary rounded-pill px-4 mt-2">
                                <i class="fa-solid fa-download me-2"></i> Download Ticket
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="text-center mt-5">
                <a href="<?= $base_url ?>/user_dashboard.php" class="btn btn-light rounded-pill px-4 fw-medium text-secondary shadow-sm">
                    <i class="fa-solid fa-house me-2"></i> Back to Dashboard
                </a>
            </div>
        </div>
    </div>
</div>

<script>
    // Generate QR Code dynamically on client side utilizing the hidden QR data string
    var qrcode = new QRCode(document.getElementById("qrcode"), {
        text: "<?= $qrData ?>",
        width: 180,
        height: 180,
        colorDark : "#1e1e1e",
        colorLight : "#ffffff",
        correctLevel : QRCode.CorrectLevel.H
    });
</script>

<style>
@media print {
    body * {
        visibility: hidden;
    }
    .card-3d, .card-3d * {
        visibility: visible;
    }
    .card-3d {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
        box-shadow: none !important;
        border: 2px solid #ccc;
    }
    .btn, nav, footer {
        display: none !important;
    }
}
</style>

<?php require_once __DIR__ . '/includes_footer.php'; ?>



