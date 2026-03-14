<?php
require_once __DIR__ . '/config_db.php';

if (!isset($_GET['id']) || empty($_GET['id'])) {
    redirect($base_url . '/user_search_parking.php');
}

$parking_id = (int)$_GET['id'];

// Get parking details
$stmt = $pdo->prepare("SELECT * FROM parking_locations WHERE parking_id = ?");
$stmt->execute([$parking_id]);
$parking = $stmt->fetch();

if (!$parking) {
    redirect($base_url . '/user_search_parking.php');
}

// Check if form was submitted
$is_searched = isset($_GET['check']);
$created_at = $_GET['created_at'] ?? date('Y-m-d');
$start_time = $_GET['start_time'] ?? '';
$end_time = $_GET['end_time'] ?? '';
$vehicle_type = $_GET['vehicle_type'] ?? '';

$slots = [];
$availableCount = 0;

if ($is_searched) {
    // Get slots for specific vehicle type
    $stmt = $pdo->prepare("SELECT * FROM parking_slots WHERE parking_id = ? AND vehicle_type = ? ORDER BY slot_number ASC");
    $stmt->execute([$parking_id, $vehicle_type]);
    $slots = $stmt->fetchAll();

    foreach ($slots as &$slot) {
        if ($slot['slot_status'] === 'reserved') {
            continue; // keep reserved
        }

        // Check for conflicting active bookings on the requested date and time
        $conflict = $pdo->prepare("
            SELECT COUNT(*) FROM bookings
            WHERE slot_id = ? 
              AND created_at = ? 
              AND status NOT IN ('cancelled')
              AND start_time < ?
              AND end_time  > ?
        ");
        $conflict->execute([$slot['slot_id'], $created_at, $end_time, $start_time]);

        if ($conflict->fetchColumn() > 0) {
            $slot['slot_status'] = 'booked';
        } else {
            $slot['slot_status'] = 'available';
            $availableCount++;
        }
    }
    unset($slot);
}

// Calculate hours and price for the summary
$estimated_hours = 0;
$amount = 0;
if ($is_searched && $start_time && $end_time) {
    $start = strtotime($created_at . ' ' . $start_time);
    $end = strtotime($created_at . ' ' . $end_time);
    $estimated_hours = ($end - $start) / 3600;
    if ($estimated_hours < 1) $estimated_hours = 1; // min 1 hour
    $amount = round($estimated_hours * $parking['price_per_hour'], 2);
}

// CSRF for AJAX booking
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

require_once __DIR__ . '/includes_header.php';
require_once __DIR__ . '/includes_navbar.php';
?>

<style>
/* 2D Flat Grid */
.slot-grid-3d {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(130px, 1fr));
    gap: 15px;
    padding: 30px 20px;
    justify-content: center;
}

.slot-3d {
    height: 80px;
    border-radius: 8px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    color: white;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    transition: all 0.2s ease;
    position: relative;
    border: 1px solid rgba(255,255,255,0.2);
}

.slot-3d:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 15px rgba(0,0,0,0.15);
}

.slot-3d.available {
    background: linear-gradient(135deg, #10b981, #059669);
    cursor: pointer;
}

.slot-3d.booked {
    background: linear-gradient(135deg, #ef4444, #dc2626);
    opacity: 0.9;
}

.slot-3d.reserved {
    background: linear-gradient(135deg, #f59e0b, #d97706);
    opacity: 0.9;
}

.slot-3d.selected {
    border: 3px solid #fff;
    transform: scale(1.05);
    box-shadow: 0 0 15px rgba(16, 185, 129, 0.5);
    z-index: 10;
}

.slot-3d .vehicle-icon {
    font-size: 1.8rem;
    opacity: 0.7;
    margin-top: 5px;
}
</style>

<div class="container py-5">
    
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= $base_url ?>/index.php" class="text-decoration-none">Home</a></li>
            <li class="breadcrumb-item"><a href="<?= $base_url ?>/user_search_parking.php" class="text-decoration-none">Search</a></li>
            <li class="breadcrumb-item active" aria-current="page"><?= htmlspecialchars($parking['parking_name']) ?></li>
        </ol>
    </nav>

    <!-- STEP 1: Time & Vehicle Form -->
    <div class="card-3d rounded-4 mb-5 overflow-hidden">
        <div class="bg-primary pt-4 pb-3 px-4 position-relative" style="background: linear-gradient(135deg, var(--primary-color), var(--accent-color));">
            <h4 class="text-white fw-bold mb-1"><i class="fa-solid fa-clock me-2"></i>Step 1: Reservation Details</h4>
            <p class="text-white text-opacity-75 mb-0 small">Enter your vehicle info and time to check live slot availability.</p>
        </div>
        
        <div class="card-body p-4 bg-surface">
            <form method="GET" action="" id="searchForm" class="needs-validation" novalidate onsubmit="return validateTimes()">
                <input type="hidden" name="id" value="<?= $parking_id ?>">
                <input type="hidden" name="check" value="1">
                
                <div class="row g-4">
                    <div class="col-md-3">
                        <label class="form-label text-secondary fw-bold small text-uppercase">Vehicle Type</label>
                        <select name="vehicle_type" class="form-select" required>
                            <option value="">Select Type</option>
                            <option value="Car" <?= $vehicle_type === 'Car' ? 'selected' : '' ?>>Car / SUV</option>
                            <option value="Bike" <?= $vehicle_type === 'Bike' ? 'selected' : '' ?>>Motorcycle / Scooter</option>
                        </select>
                    </div>
                    
                    <div class="col-md-3">
                        <label class="form-label text-secondary fw-bold small text-uppercase">Date</label>
                        <input type="date" name="created_at" class="form-control" value="<?= $created_at ?>" required min="<?= date('Y-m-d') ?>">
                    </div>
                    
                    <div class="col-md-3">
                        <label class="form-label text-secondary fw-bold small text-uppercase">Entry Time</label>
                        <input type="time" name="start_time" id="search_entry" class="form-control" value="<?= $start_time ?>" required>
                    </div>
                    
                    <div class="col-md-3">
                        <label class="form-label text-secondary fw-bold small text-uppercase">Exit Time</label>
                        <input type="time" name="end_time" id="search_exit" class="form-control" value="<?= $end_time ?>" required>
                        <div class="invalid-feedback d-block" id="timeError" style="display:none !important;">Exit must be after entry.</div>
                    </div>
                </div>
                
                <div class="mt-4 border-top border-secondary border-opacity-25 pt-3 text-end">
                    <button type="submit" class="btn-primary-3d px-5"><i class="fa-solid fa-search me-2"></i>Check Available Slots</button>
                </div>
            </form>
        </div>
    </div>

    <!-- STEP 2: Slots Grid (Hidden until searched) -->
    <?php if ($is_searched): ?>
    <div class="row g-4" id="slotsSection">
        <div class="col-lg-8">
            <div class="glass-panel h-100 overflow-hidden">
                <div class="card-header bg-transparent border-bottom border-secondary border-opacity-25 p-4 d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="fw-bold mb-1 text-primary"><i class="fa-solid fa-grip me-2"></i>Step 2: Select a Slot</h5>
                        <p class="small text-secondary mb-0">Filtered for <?= $vehicle_type ?>s only.</p>
                    </div>
                    <div class="d-flex gap-3 small fw-bold text-secondary">
                        <div class="d-flex align-items-center gap-2"><span class="bg-success rounded-circle shadow-sm" style="width:14px; height:14px;"></span> Available (<?= $availableCount ?>)</div>
                        <div class="d-flex align-items-center gap-2"><span class="bg-danger rounded-circle shadow-sm" style="width:14px; height:14px;"></span> Booked</div>
                    </div>
                </div>
                
                <div class="card-body p-0 bg-transparent">
                    <?php if (count($slots) === 0): ?>
                        <div class="text-center py-5">
                            <div class="bg-light d-inline-block p-4 rounded-circle mb-3"><i class="fa-solid <?= $vehicle_type === 'Bike' ? 'fa-motorcycle' : 'fa-car' ?> fa-3x text-muted opacity-50"></i></div>
                            <h5 class="text-muted fw-bold">No <?= $vehicle_type ?> slots configured</h5>
                            <p class="text-muted small">The administrator has not added any slots for this vehicle type yet.</p>
                        </div>
                    <?php else: ?>
                        <div class="slot-grid-3d bg-white bg-opacity-5">
                            <?php foreach ($slots as $slot): ?>
                                <div class="slot-3d <?= $slot['slot_status'] ?>" 
                                     data-id="<?= $slot['slot_id'] ?>" 
                                     data-number="<?= htmlspecialchars($slot['slot_number']) ?>"
                                     title="Slot <?= htmlspecialchars($slot['slot_number']) ?> - <?= ucfirst($slot['slot_status']) ?>">
                                    <span class="fs-4"><?= htmlspecialchars($slot['slot_number']) ?></span>
                                    <i class="fa-solid <?= $slot['vehicle_type'] === 'Bike' ? 'fa-motorcycle' : 'fa-car' ?> vehicle-icon"></i>
                                    <?php if ($slot['slot_status'] !== 'available'): ?>
                                        <i class="fa-solid fa-lock position-absolute text-white shadow-sm" style="top: -10px; right: -10px; background: rgba(0,0,0,0.5); padding: 5px; border-radius: 50%; width: 25px; height: 25px; font-size: 0.7rem; display: flex; align-items: center; justify-content: center;"></i>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- STEP 3: Summary & Checkout (Hidden until slot selected) -->
        <div class="col-lg-4">
            <div id="checkoutPanel" class="card-3d overflow-hidden position-sticky" style="top: 100px; display: none; opacity: 0; transition: opacity 0.3s ease;">
                <div class="text-white p-5 position-relative overflow-hidden" style="background: linear-gradient(135deg, var(--primary-color), #6d28d9);">
                    <div class="position-absolute top-0 end-0 rounded-circle bg-white opacity-10" style="width: 200px; height: 200px; transform: translate(30%, -30%);"></div>
                    
                    <h5 class="fw-bold mb-4 opacity-75 text-uppercase small"><i class="fa-solid fa-check-circle me-2"></i>Step 3: Confirm</h5>
                    
                    <h3 class="fw-bold mb-1" style="text-shadow: 0 2px 4px rgba(0,0,0,0.3);"><?= htmlspecialchars($parking['parking_name']) ?></h3>
                    
                    <div class="d-flex justify-content-between align-items-center mt-3 pt-3 border-top border-white border-opacity-25 mb-4 pb-4 border-bottom">
                        <div>
                            <span class="d-block small opacity-75 mb-1 fw-medium">Selected Slot</span>
                            <span class="fs-1 fw-bold" id="displaySlotNumber">--</span>
                        </div>
                        <div class="bg-white bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center border border-white border-opacity-25" style="width: 65px; height: 65px;">
                            <i class="fa-solid <?= $vehicle_type === 'Bike' ? 'fa-motorcycle' : 'fa-car' ?> fa-2x"></i>
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-between mb-2 fw-medium">
                        <span class="opacity-75">Date</span>
                        <span class="fw-bold"><?= date('d M Y', strtotime($created_at)) ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-2 fw-medium">
                        <span class="opacity-75">Time</span>
                        <span class="fw-bold"><?= date('h:i A', strtotime($start_time)) ?> to <?= date('h:i A', strtotime($end_time)) ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-2 fw-medium">
                        <span class="opacity-75">Duration</span>
                        <span class="fw-bold"><?= round($estimated_hours, 1) ?> hour(s)</span>
                    </div>
                    <div class="d-flex justify-content-between mb-4 pb-4 border-bottom border-white border-opacity-25 fw-medium">
                        <span class="opacity-75">Rate</span>
                        <span class="fw-bold">₹<?= number_format($parking['price_per_hour'], 2) ?>/hr</span>
                    </div>
                    
                    <div class="d-flex justify-content-between align-items-center mt-2 mb-4">
                        <span class="fs-5 opacity-75 fw-bold">Total Pay</span>
                        <span class="fs-1 fw-bold" style="text-shadow: 0 2px 8px rgba(0,0,0,0.3);">₹<?= number_format($amount, 2) ?></span>
                    </div>
                    
                    <button type="button" class="btn btn-light text-primary w-100 py-3 fw-bold rounded-pill shadow" onclick="promptVehicleNumber()">
                        <i class="fa-solid fa-lock me-2"></i> Confirm & Book
                    </button>
                    
                    <div id="bookingSpinner" class="text-center mt-3 d-none">
                        <div class="spinner-border text-light spinner-border-sm" role="status"></div> Processing...
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- Vehicle Number Modal -->
<div class="modal fade" id="vehicleNumberModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow-lg overflow-hidden glass-panel">
            <div class="modal-header border-bottom border-secondary border-opacity-25 p-4">
                <h5 class="fw-bold text-primary mb-0"><i class="fa-solid fa-car-side me-2"></i>Final Step: Vehicle Number</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <p class="text-secondary small mb-3">Please enter the license plate number of the <strong class="text-primary"><?= $vehicle_type ?></strong> you are parking.</p>
                <div class="mb-4">
                    <input type="text" id="final_vehicle_number" class="form-control form-control-lg text-center fw-bold fs-4 tracking-wider" placeholder="GJ01AB1234" style="text-transform: uppercase;" pattern="[A-Z0-9-]{6,12}">
                    <div class="invalid-feedback text-center mt-2" id="vNumError">Format: Letters and numbers only, 6-12 chars.</div>
                </div>
                <button type="button" id="executeBookingBtn" class="btn-primary-3d w-100 py-3 fs-5"><i class="fa-solid fa-check-double me-2"></i>Pay ₹<?= number_format($amount, 2) ?> & Book</button>
            </div>
        </div>
    </div>
</div>

<!-- Success Modal -->
<div class="modal fade" id="successModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow-lg overflow-hidden">
            <div class="modal-body p-5 text-center">
                <div class="bg-success text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-4" style="width: 80px; height: 80px;">
                    <i class="fa-solid fa-check fa-2x"></i>
                </div>
                <h3 class="fw-bold mb-3">Booking Confirmed!</h3>
                <p class="text-muted mb-4">Your parking slot <strong class="text-dark bg-light px-2 py-1 rounded" id="successSlotNum"></strong> has been successfully reserved.</p>
                <div class="bg-light p-3 rounded-3 mb-4 border border-info border-start-0 border-end-0 border-bottom-0 border-4">
                    <p class="small text-muted mb-0"><i class="fa-solid fa-circle-info text-info me-1"></i> Added to your history.</p>
                </div>
                <a href="<?= $base_url ?>/user_history.php" class="btn btn-primary rounded-pill px-5 fw-bold w-100 mb-2 hover-lift">View Ticket</a>
                <a href="<?= $base_url ?>/index.php" class="btn btn-light text-primary rounded-pill px-5 fw-bold w-100">Home</a>
            </div>
        </div>
    </div>
</div>

<script>
function validateTimes() {
    const entry = document.getElementById('search_entry').value;
    const exit = document.getElementById('search_exit').value;
    const err = document.getElementById('timeError');
    if (entry && exit && exit <= entry) {
        err.style.setProperty('display', 'block', 'important');
        return false;
    }
    err.style.setProperty('display', 'none', 'important');
    return true;
}

document.addEventListener('DOMContentLoaded', function() {
    const slots = document.querySelectorAll('.slot-3d.available');
    const panel = document.getElementById('checkoutPanel');
    const displayNum = document.getElementById('displaySlotNumber');
    let selectedSlotId = null;

    slots.forEach(slot => {
        slot.addEventListener('click', function() {
            slots.forEach(s => s.classList.remove('selected'));
            this.classList.add('selected');
            selectedSlotId = this.getAttribute('data-id');
            displayNum.textContent = this.getAttribute('data-number');
            
            panel.style.display = 'block';
            setTimeout(() => { panel.style.opacity = '1'; }, 10);
            
            // Scroll down a bit on mobile
            if (window.innerWidth < 992) {
                panel.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            }
        });
    });
});

let vehicleModal;
function promptVehicleNumber() {
    <?php if (!isLoggedIn()): ?>
        window.location.href = '<?= $base_url ?>/user_login.php';
        return;
    <?php endif; ?>
    
    vehicleModal = new bootstrap.Modal(document.getElementById('vehicleNumberModal'));
    vehicleModal.show();
}

document.getElementById('executeBookingBtn')?.addEventListener('click', function() {
    const vNumInput = document.getElementById('final_vehicle_number');
    const vNum = vNumInput.value.trim().toUpperCase();
    
    // Basic regex: alphanumeric and hyphens, 6 to 12 chars
    const regex = /^[A-Z0-9-]{6,12}$/;
    
    if (!regex.test(vNum)) {
        vNumInput.classList.add('is-invalid');
        return;
    }
    vNumInput.classList.remove('is-invalid');
    
    // Disable button, show spinner
    const btn = this;
    const originalText = btn.innerHTML;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Processing...';
    btn.disabled = true;
    
    const selectedSlotId = document.querySelector('.slot-3d.selected')?.getAttribute('data-id');
    const slotNumber = document.querySelector('.slot-3d.selected')?.getAttribute('data-number');
    // 1. First, create a Razorpay Order ID on the backend
    const amount = <?= $amount ?>; 
    
    fetch('<?= $base_url ?>/ajax_create_razorpay_order.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({ amount: amount })
    })
    .then(r => r.text())
    .then(text => {
        let orderData;
        try {
            orderData = JSON.parse(text);
        } catch (e) {
            console.error('Raw response:', text);
            alert('Server error: Could not parse response. Check console.');
            btn.innerHTML = originalText;
            btn.disabled = false;
            return;
        }

        if (orderData.status !== 'success') {
            alert('Could not initialize payment: ' + orderData.message);
            btn.innerHTML = originalText;
            btn.disabled = false;
            return;
        }

        // 2. Open Razorpay Checkout Pop-up
        const options = {
            "key": orderData.key_id,
            "amount": orderData.amount,
            "currency": orderData.currency,
            "name": "ParkNova",
            "description": "Parking Reservation - " + slotNumber,
            "image": "https://img.icons8.com/color/48/000000/parking.png", // Sample logo
            "order_id": orderData.order_id,
            "handler": function (response) {
                // 3. Payment Success - Now save the complete booking to the DB
                
                const formData = new FormData();
                formData.append('parking_id', '<?= $parking_id ?? '' ?>');
                formData.append('slot_id', selectedSlotId);
                formData.append('vehicle_number', vNum);
                formData.append('vehicle_type', '<?= $vehicle_type ?? '' ?>');
                formData.append('created_at', '<?= $created_at ?? '' ?>');
                formData.append('start_time', '<?= $start_time ?? '' ?>');
                formData.append('end_time', '<?= $end_time ?? '' ?>');
                formData.append('csrf_token', '<?= $_SESSION['csrf_token'] ?? '' ?>');
                
                // Add razorpay payment details
                formData.append('razorpay_payment_id', response.razorpay_payment_id);
                formData.append('razorpay_order_id', response.razorpay_order_id);
                formData.append('razorpay_signature', response.razorpay_signature);

                fetch('<?= $base_url ?>/ajax_book_slot.php', {
                    method: 'POST',
                    body: formData
                })
                .then(r => r.json())
                .then(data => {
                    vehicleModal.hide();
                    if (data.status === 'success') {
                        document.getElementById('successSlotNum').textContent = slotNumber;
                        new bootstrap.Modal(document.getElementById('successModal')).show();
                    } else {
                        alert('Booking Error: ' + data.message);
                        btn.innerHTML = originalText;
                        btn.disabled = false;
                    }
                })
                .catch(err => {
                    vehicleModal.hide();
                    alert('Network error after payment. Please contact admin.');
                    btn.innerHTML = originalText;
                    btn.disabled = false;
                });
            },
            "prefill": {
                "name": "<?= $_SESSION['user_name'] ?? '' ?>",
                "email": "<?= $_SESSION['user_email'] ?? 'test@example.com' ?>",
                "contact": "<?= $_SESSION['user_mobile'] ?? '9999999999' ?>"
            },
            "theme": {
                "color": "#3399cc"
            },
            "modal": {
                "ondismiss": function() {
                    btn.innerHTML = originalText;
                    btn.disabled = false;
                }
            }
        };
        const rzp1 = new Razorpay(options);
        rzp1.on('payment.failed', function (response){
            alert("Payment Failed: " + response.error.description);
            btn.innerHTML = originalText;
            btn.disabled = false;
        });
        rzp1.open();
    })
    .catch(err => {
        alert('Network error contacting payment server.');
        btn.innerHTML = originalText;
        btn.disabled = false;
    });
});
</script>

<!-- Add Razorpay Checkout script -->
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>

<?php require_once __DIR__ . '/includes_footer.php'; ?>



