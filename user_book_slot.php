<?php
require_once __DIR__ . '/config_db.php';

if (!isset($_GET['parking'])) {
    redirect('user_dashboard.php');
}
if (!isLoggedIn()) {
    redirect('user_login.php');
}

$parking_id = (int)$_GET['parking'];

$stmt = $pdo->prepare("SELECT * FROM parking_locations WHERE parking_id = ? AND status = 'active'");
$stmt->execute([$parking_id]);
$parking = $stmt->fetch();

if (!$parking) redirect('user_dashboard.php');

$stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

require_once __DIR__ . '/includes_header.php';
require_once __DIR__ . '/includes_navbar.php';
?>

<!-- Razorpay Script -->
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>

<style>
/* ── Stepper ───────────────────────────────────────── */
.stepper-wrap { display:flex; justify-content:space-between; position:relative; margin-bottom:2.5rem; }
.stepper-wrap::before {
    content:''; position:absolute; top:24px; left:0; right:0; height:3px;
    background:rgba(var(--bs-secondary-rgb),.15); z-index:0;
}
.stepper-progress {
    position:absolute; top:24px; left:0; height:3px;
    background:var(--primary-color); z-index:1; transition:width .4s ease;
}
.stepper-step { display:flex; flex-direction:column; align-items:center; gap:.5rem; z-index:2; }
.step-circle {
    width:48px; height:48px; border-radius:50%; background:var(--surface-color);
    border:3px solid rgba(var(--bs-secondary-rgb),.25);
    display:flex; align-items:center; justify-content:center;
    font-weight:800; font-size:1rem; color:var(--bs-secondary);
    transition:all .35s ease;
}
.stepper-step.active .step-circle {
    background:var(--primary-color); border-color:var(--primary-color);
    color:#fff; box-shadow:0 6px 18px rgba(109,40,217,.35);
    transform:scale(1.1);
}
.stepper-step.done .step-circle {
    background:#10b981; border-color:#10b981; color:#fff;
}
.step-label { font-size:.7rem; font-weight:700; text-transform:uppercase; color:var(--bs-secondary); }
.stepper-step.active .step-label { color:var(--primary-color); }
.stepper-step.done .step-label  { color:#10b981; }

/* ── Slot Grid ─────────────────────────────────────── */
.slot-grid-3d {
    display:grid;
    grid-template-columns:repeat(auto-fill, minmax(90px, 1fr));
    gap:16px; padding:12px 0;
}
.slot-card {
    height:110px; border-radius:10px; display:flex; flex-direction:column;
    align-items:center; justify-content:center; gap:6px; cursor:pointer;
    transition:all .3s cubic-bezier(.175,.885,.32,1.275);
    transform:rotateX(12deg) rotateY(-4deg); transform-style:preserve-3d;
    font-weight:800; color:#fff; position:relative; font-size:.85rem;
}
.slot-card::before {
    content:''; position:absolute; inset:4px; border:2px dashed rgba(255,255,255,.35);
    border-radius:7px; pointer-events:none;
}
.slot-available { background:linear-gradient(135deg,#2ecc71,#27ae60);
    box-shadow:0 8px 18px rgba(46,204,113,.25); }
.slot-available:hover {
    transform:rotateX(4deg) rotateY(0) translateY(-10px) scale(1.06);
    box-shadow:0 16px 30px rgba(46,204,113,.4);
}
.slot-booked   { background:linear-gradient(135deg,#e74c3c,#c0392b);
    opacity:.8; cursor:not-allowed; }
.slot-occupied { background:linear-gradient(135deg,#f39c12,#e67e22);
    opacity:.8; cursor:not-allowed; }
.slot-selected { background:linear-gradient(135deg,#6d28d9,#4c1d95) !important;
    box-shadow:0 0 0 3px #fff,0 0 0 5px #6d28d9 !important;
    transform:rotateX(4deg) rotateY(0) translateY(-12px) scale(1.08) !important; }

/* ── Vehicle Step ──────────────────────────────────── */
.vehicle-hero {
    background:linear-gradient(135deg,rgba(67,97,238,.06),rgba(109,40,217,.06));
    border:1.5px solid rgba(109,40,217,.12);
    border-radius:16px; padding:1.5rem;
}
@media(max-width:768px){ .stepper-wrap::before,.stepper-progress{display:none} }
</style>

<div class="container py-5">

    <!-- Header -->
    <div class="text-center mb-5">
        <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-3 py-2 mb-3 rounded-pill fw-semibold">
            <i class="fa-solid fa-location-dot me-1"></i> <?= htmlspecialchars($parking['city']) ?>
        </span>
        <h1 class="fw-bold text-primary mb-1"><?= htmlspecialchars($parking['parking_name']) ?></h1>
        <p class="text-secondary"><i class="fa-solid fa-location-arrow me-1 opacity-50"></i><?= htmlspecialchars($parking['address']) ?></p>
        <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-3 py-2 fs-6 rounded-pill fw-bold">
            ₹<span id="parkingRate"><?= $parking['price_per_hour'] ?></span> / Hour
        </span>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-10">

            <!-- Stepper -->
            <div class="stepper-wrap px-3 mb-5" id="stepper">
                <div class="stepper-step active" id="si1">
                    <div class="step-circle">1</div><div class="step-label">Date &amp; Time</div>
                </div>
                <div class="stepper-step" id="si2">
                    <div class="step-circle">2</div><div class="step-label">Select Slot</div>
                </div>
                <div class="stepper-step" id="si3">
                    <div class="step-circle">3</div><div class="step-label">Vehicle</div>
                </div>
                <div class="stepper-step" id="si4">
                    <div class="step-circle">4</div><div class="step-label">Payment</div>
                </div>
                <div class="stepper-progress" id="stepperBar" style="width:0%"></div>
            </div>

            <div class="card-3d overflow-hidden">
                <div class="card-body p-4 p-md-5 bg-surface" style="min-height:360px">

                    <!-- ════ STEP 1 — Booking Details ════ -->
                    <div id="step1" class="step-content">
                        <h4 class="fw-bold text-primary mb-4 pb-2 border-bottom border-secondary border-opacity-25">
                            <i class="fa-solid fa-calendar-check me-2"></i>Booking Details
                        </h4>
                        <form id="detailsForm" onsubmit="checkAvailability(event)" novalidate>
                            <div class="row g-4">
                                <!-- Vehicle Type -->
                                <div class="col-md-6">
                                    <label class="form-label fw-bold small text-uppercase text-secondary">Vehicle Type <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-transparent text-primary border-end-0"><i class="fa-solid fa-car-side"></i></span>
                                        <select id="vehicleType" class="form-select border-start-0" required>
                                            <option value="Car"  <?= ($user['vehicle_type']??'Car')==='Car'  ? 'selected':''; ?>>🚗 Car</option>
                                            <option value="Bike" <?= ($user['vehicle_type']??'')==='Bike' ? 'selected':''; ?>>🏍️ Bike</option>
                                        </select>
                                    </div>
                                </div>
                                <!-- Booking Date -->
                                <div class="col-md-6">
                                    <label class="form-label fw-bold small text-uppercase text-secondary">Booking Date <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-transparent text-primary border-end-0"><i class="fa-solid fa-calendar"></i></span>
                                        <input type="date" id="bookingDate" class="form-control border-start-0" required
                                               min="<?= date('Y-m-d') ?>" value="<?= date('Y-m-d') ?>">
                                    </div>
                                </div>
                                <!-- Entry Time -->
                                <div class="col-md-6">
                                    <label class="form-label fw-bold small text-uppercase text-secondary">Entry Time <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-transparent text-primary border-end-0"><i class="fa-solid fa-clock"></i></span>
                                        <input type="time" id="entryTime" class="form-control border-start-0" required>
                                    </div>
                                </div>
                                <!-- Exit Time -->
                                <div class="col-md-6">
                                    <label class="form-label fw-bold small text-uppercase text-secondary">Exit Time <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-transparent text-primary border-end-0"><i class="fa-solid fa-clock"></i></span>
                                        <input type="time" id="exitTime" class="form-control border-start-0" required>
                                    </div>
                                    <div class="form-text text-danger d-none" id="timeError">❌ Exit time must be after entry time.</div>
                                </div>
                            </div>
                            <div class="mt-5 d-flex justify-content-end">
                                <button type="submit" class="btn-primary-3d px-5 py-3 fs-6" id="checkBtn">
                                    <i class="fa-solid fa-magnifying-glass me-2"></i>Check Availability
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- ════ STEP 2 — Slot Selection ════ -->
                    <div id="step2" class="step-content d-none">
                        <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 pb-3 border-bottom border-secondary border-opacity-25 gap-3">
                            <h4 class="fw-bold text-primary mb-0"><i class="fa-solid fa-grip me-2"></i>Select Your Slot</h4>
                            <div class="d-flex gap-3 flex-wrap">
                                <span class="d-flex align-items-center gap-2 small fw-bold text-success">
                                    <span class="bg-success rounded-circle d-inline-block" style="width:12px;height:12px;"></span>Available
                                </span>
                                <span class="d-flex align-items-center gap-2 small fw-bold text-danger">
                                    <span class="bg-danger rounded-circle d-inline-block" style="width:12px;height:12px;"></span>Booked
                                </span>
                                <span class="d-flex align-items-center gap-2 small fw-bold text-warning">
                                    <span class="bg-warning rounded-circle d-inline-block" style="width:12px;height:12px;"></span>Occupied
                                </span>
                            </div>
                        </div>

                        <div id="slotLoader" class="text-center py-5">
                            <div class="spinner-border text-primary" role="status"></div>
                            <div class="mt-2 text-secondary fw-medium">Finding available slots…</div>
                        </div>
                        <div id="slotGrid" class="slot-grid-3d d-none"></div>

                        <div class="alert glass-panel border border-primary border-opacity-25 mt-4 d-none" id="selectedSlotBanner">
                            <i class="fa-solid fa-circle-check text-primary me-2"></i>
                            Selected: <strong id="selSlotName" class="text-primary ms-1"></strong>
                        </div>

                        <div class="mt-4 pt-3 border-top border-secondary border-opacity-25 d-flex justify-content-between">
                            <button class="btn btn-outline-secondary rounded-pill px-4" onclick="goTo(1)">
                                <i class="fa-solid fa-arrow-left me-2"></i>Back
                            </button>
                            <button class="btn-primary-3d px-5 d-none" id="continueSlotBtn" onclick="goTo(3)">
                                Continue <i class="fa-solid fa-arrow-right ms-2"></i>
                            </button>
                        </div>
                    </div>

                    <!-- ════ STEP 3 — Vehicle Details ════ -->
                    <div id="step3" class="step-content d-none">
                        <h4 class="fw-bold text-primary mb-4 pb-2 border-bottom border-secondary border-opacity-25">
                            <i class="fa-solid fa-id-card me-2"></i>Vehicle Details
                        </h4>

                        <div class="vehicle-hero mb-4">
                            <div class="d-flex align-items-center gap-3 mb-2">
                                <i class="fa-solid fa-circle-info text-primary fs-5"></i>
                                <div>
                                    <div class="fw-bold text-dark small">Slot Selected: <span class="text-primary" id="v3SlotName">—</span></div>
                                    <div class="text-secondary" style="font-size:0.75rem">Please enter your vehicle details to continue</div>
                                </div>
                            </div>
                        </div>

                        <form id="vehicleForm" onsubmit="submitVehicleDetails(event)" novalidate>
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold small text-uppercase text-secondary">Vehicle Number <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-transparent text-primary border-end-0"><i class="fa-solid fa-id-card"></i></span>
                                        <input type="text" id="vehicleNumber" class="form-control border-start-0 text-uppercase fw-bold"
                                               placeholder="e.g. GJ05AB1234"
                                               value="<?= htmlspecialchars($user['vehicle_number'] ?? '') ?>"
                                               pattern="^[A-Za-z]{2}\s?[0-9]{1,2}\s?[A-Za-z]{1,3}\s?[0-9]{4}$"
                                               title="Enter valid vehicle number (e.g. GJ05AB1234)"
                                               required
                                               style="letter-spacing:2px">
                                    </div>
                                    <div class="form-text text-muted">Format: GJ05AB1234</div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold small text-uppercase text-secondary">Driver Name <span class="text-muted fw-normal">(Optional)</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-transparent text-primary border-end-0"><i class="fa-solid fa-user"></i></span>
                                        <input type="text" id="driverName" class="form-control border-start-0"
                                               placeholder="e.g. Jaimeen Gondaliya"
                                               value="<?= htmlspecialchars($user['name'] ?? '') ?>">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold small text-uppercase text-secondary">Contact Number</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-transparent text-primary border-end-0"><i class="fa-solid fa-phone"></i></span>
                                        <input type="tel" id="contactNumber" class="form-control border-start-0"
                                               placeholder="10-digit mobile"
                                               value="<?= htmlspecialchars($user['mobile'] ?? '') ?>"
                                               pattern="[0-9]{10}" maxlength="10">
                                    </div>
                                </div>
                            </div>
                            <div class="mt-5 d-flex justify-content-between">
                                <button type="button" class="btn btn-outline-secondary rounded-pill px-4" onclick="goTo(2)">
                                    <i class="fa-solid fa-arrow-left me-2"></i>Back
                                </button>
                                <button type="submit" class="btn-primary-3d px-5">
                                    Continue <i class="fa-solid fa-arrow-right ms-2"></i>
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- ════ STEP 4 — Summary + Payment ════ -->
                    <div id="step4" class="step-content d-none">
                        <h4 class="fw-bold text-primary mb-4 pb-2 border-bottom border-secondary border-opacity-25">
                            <i class="fa-solid fa-receipt me-2"></i>Booking Summary
                        </h4>

                        <div class="row g-4 mb-5">
                            <div class="col-md-5">
                                <div class="glass-panel p-4 rounded-4 h-100">
                                    <div class="mb-3">
                                        <div class="small fw-bold text-secondary text-uppercase mb-1">Vehicle</div>
                                        <div class="fw-bold fs-5 font-monospace text-primary" id="s4Vehicle">—</div>
                                        <div class="small text-muted mt-1" id="s4VehicleType">—</div>
                                    </div>
                                    <hr class="border-secondary opacity-25">
                                    <div>
                                        <div class="small fw-bold text-secondary text-uppercase mb-1">Slot</div>
                                        <div class="fw-bold fs-3 text-primary" id="s4Slot">—</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-7">
                                <div class="p-4 rounded-4" style="background:var(--surface-color); border:1.5px solid rgba(var(--bs-secondary-rgb),.12);">
                                    <div class="d-flex justify-content-between mb-3">
                                        <span class="text-secondary"><i class="fa-solid fa-calendar me-2 opacity-50"></i>Date</span>
                                        <span class="fw-bold" id="s4Date">—</span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-3">
                                        <span class="text-secondary"><i class="fa-solid fa-clock me-2 opacity-50"></i>Time</span>
                                        <span class="fw-bold"><span id="s4Entry">—</span> → <span id="s4Exit">—</span></span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-3">
                                        <span class="text-secondary"><i class="fa-solid fa-hourglass-half me-2 opacity-50"></i>Duration</span>
                                        <span class="fw-bold" id="s4Duration">—</span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-3">
                                        <span class="text-secondary"><i class="fa-solid fa-indian-rupee-sign me-2 opacity-50"></i>Rate</span>
                                        <span class="fw-bold">₹<?= $parking['price_per_hour'] ?>/hr</span>
                                    </div>
                                    <hr class="border-secondary opacity-25">
                                    <div class="d-flex justify-content-between align-items-center mt-3 p-3 bg-primary bg-opacity-10 rounded-3 border border-primary border-opacity-25">
                                        <span class="fw-bold text-primary fs-5">Total Amount</span>
                                        <span class="fw-bold text-primary fs-3">₹<span id="s4Total">0</span></span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center">
                            <button type="button" class="btn btn-outline-secondary rounded-pill px-4" onclick="goTo(3)">
                                <i class="fa-solid fa-arrow-left me-2"></i>Back
                            </button>
                            <button type="button" class="btn-primary-3d px-5 fs-5" id="payBtn" onclick="initiatePayment()">
                                <i class="fa-solid fa-lock me-2"></i>Pay Securely ₹<span id="payBtnAmt">0</span>
                            </button>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div>
</div>

<script>
const parkingId   = <?= $parking_id ?>;
const ratePerHour = <?= (float)$parking['price_per_hour'] ?>;

// ── Booking state ──────────────────────────────────────────────
let booking = {
    vehicleType: 'Car',
    date: '', entry: '', exit: '',
    duration: 0, total: 0,
    slotId: null, slotNo: '',
    vehicleNumber: '', driverName: '', contactNumber: ''
};

// ── Step Navigation ────────────────────────────────────────────
function goTo(step) {
    document.querySelectorAll('.step-content').forEach(el => el.classList.add('d-none'));
    document.getElementById('step' + step).classList.remove('d-none');
    window.scrollTo({ top: 200, behavior: 'smooth' });

    const pct = (step - 1) / 3 * 100;
    document.getElementById('stepperBar').style.width = pct + '%';

    for (let i = 1; i <= 4; i++) {
        const el = document.getElementById('si' + i);
        el.classList.remove('active', 'done');
        if (i < step)  el.classList.add('done');
        if (i === step) el.classList.add('active');
    }
    if (step === 4) populateSummary();
}

// ── Step 1: Check Availability ────────────────────────────────
function checkAvailability(e) {
    e.preventDefault();

    const entry    = document.getElementById('entryTime').value;
    const exit     = document.getElementById('exitTime').value;
    const date     = document.getElementById('bookingDate').value;
    const vehType  = document.getElementById('vehicleType').value;

    const entryMins = entry.split(':').reduce((a,b) => a*60 + +b);
    const exitMins  = exit.split(':').reduce((a, b) => a*60 + +b);
    const duration  = (exitMins - entryMins) / 60;

    if (duration <= 0) {
        document.getElementById('timeError').classList.remove('d-none');
        return;
    }
    document.getElementById('timeError').classList.add('d-none');

    booking.date     = date;
    booking.entry    = entry;
    booking.exit     = exit;
    booking.duration = duration;
    booking.total    = Math.ceil(duration * ratePerHour);
    booking.vehicleType = vehType;

    const btn = document.getElementById('checkBtn');
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Checking…';
    btn.disabled  = true;

    fetch(`ajax_check_slots.php?parking_id=${parkingId}&date=${date}&entry=${entry}&exit=${exit}&type=${encodeURIComponent(vehType)}`)
        .then(r => r.json())
        .then(res => {
            btn.innerHTML = '<i class="fa-solid fa-magnifying-glass me-2"></i>Check Availability';
            btn.disabled  = false;

            if (res.status === 'success') {
                renderSlots(res.slots);
                goTo(2);
            } else {
                alert('⚠️ ' + res.message);
            }
        })
        .catch(() => {
            btn.innerHTML = '<i class="fa-solid fa-magnifying-glass me-2"></i>Check Availability';
            btn.disabled = false;
            alert('Network error. Please retry.');
        });
}

// ── Step 2: Render Slots ──────────────────────────────────────
function renderSlots(slots) {
    const grid = document.getElementById('slotGrid');
    document.getElementById('slotLoader').classList.add('d-none');
    grid.innerHTML = '';
    grid.classList.remove('d-none');

    if (!slots.length) {
        grid.innerHTML = '<div class="col-12 text-center py-4 text-secondary"><i class="fa-solid fa-ban fa-3x mb-3 d-block"></i>No slots configured yet for this parking.</div>';
        return;
    }

    const carIcon = booking.vehicleType === 'Bike' ? 'fa-motorcycle' : 'fa-car';
    slots.forEach(slot => {
        const el = document.createElement('div');
        const cls = slot.status === 'available' ? 'slot-available' :
                    slot.status === 'occupied'  ? 'slot-occupied'  : 'slot-booked';
        el.className = `slot-card ${cls}`;
        el.innerHTML = `<i class="fa-solid ${carIcon} fs-4"></i><div>${slot.slot_number}</div>`;
        if (slot.status === 'available') {
            el.onclick = () => selectSlot(slot.slot_id, slot.slot_number, el);
        }
        grid.appendChild(el);
    });
}

function selectSlot(id, number, el) {
    // Deselect all
    document.querySelectorAll('.slot-card').forEach(c => c.classList.remove('slot-selected'));
    el.classList.add('slot-selected');

    booking.slotId = id;
    booking.slotNo = number;

    document.getElementById('selSlotName').textContent = number;
    document.getElementById('selectedSlotBanner').classList.remove('d-none');
    document.getElementById('continueSlotBtn').classList.remove('d-none');
}

// ── Step 3: Vehicle Details ───────────────────────────────────
function submitVehicleDetails(e) {
    e.preventDefault();

    const vn = document.getElementById('vehicleNumber').value.trim().toUpperCase();
    if (!vn) { alert('Please enter your vehicle number.'); return; }

    booking.vehicleNumber  = vn;
    booking.driverName     = document.getElementById('driverName').value.trim();
    booking.contactNumber  = document.getElementById('contactNumber').value.trim();

    goTo(4);
}

// ── Step 4: Summary ───────────────────────────────────────────
function formatTime(t24) {
    const [h, m] = t24.split(':');
    let hrs = parseInt(h), ampm = hrs >= 12 ? 'PM' : 'AM';
    hrs = hrs % 12 || 12;
    return `${hrs}:${m} ${ampm}`;
}

function populateSummary() {
    document.getElementById('s4Vehicle').textContent    = booking.vehicleNumber;
    document.getElementById('s4VehicleType').textContent = booking.vehicleType;
    document.getElementById('s4Slot').textContent       = booking.slotNo;
    document.getElementById('v3SlotName').textContent   = booking.slotNo;

    const d = new Date(booking.date + 'T00:00:00');
    document.getElementById('s4Date').textContent     = d.toLocaleDateString('en-IN', {day:'2-digit', month:'short', year:'numeric'});
    document.getElementById('s4Entry').textContent    = formatTime(booking.entry);
    document.getElementById('s4Exit').textContent     = formatTime(booking.exit);
    document.getElementById('s4Duration').textContent = booking.duration.toFixed(1) + ' Hrs';
    document.getElementById('s4Total').textContent    = booking.total.toFixed(2);
    document.getElementById('payBtnAmt').textContent  = booking.total.toFixed(2);
}

// ── Payment ───────────────────────────────────────────────────
function initiatePayment() {
    const btn = document.getElementById('payBtn');
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Creating Order…';
    btn.disabled  = true;

    const fd = new FormData();
    fd.append('parking_id',    parkingId);
    fd.append('slot_id',       booking.slotId);
    fd.append('vehicleType',   booking.vehicleType);
    fd.append('vehicleNumber', booking.vehicleNumber);
    fd.append('booking_date',  booking.date);
    fd.append('entry',         booking.entry);
    fd.append('exit',          booking.exit);
    fd.append('amount',        booking.total);

    fetch('ajax_create_razorpay_order.php', { method:'POST', body:fd })
        .then(r => r.json())
        .then(res => {
            if (res.status === 'success') {
                openRazorpay(res.order_id, res.amount, res.key, res.booking_payload);
            } else {
                alert('Payment init failed: ' + res.message);
                btn.innerHTML = `<i class="fa-solid fa-lock me-2"></i>Pay Securely ₹<span id="payBtnAmt">${booking.total.toFixed(2)}</span>`;
                btn.disabled = false;
            }
        })
        .catch(() => {
            alert('Network error.');
            btn.innerHTML = `<i class="fa-solid fa-lock me-2"></i>Pay Securely ₹<span id="payBtnAmt">${booking.total.toFixed(2)}</span>`;
            btn.disabled = false;
        });
}

function openRazorpay(orderId, amount, key, payload) {
    const rzp = new Razorpay({
        key, amount, currency: 'INR',
        name: 'ParkNova',
        description: `Slot ${booking.slotNo} — ${booking.vehicleNumber}`,
        order_id: orderId,
        prefill: {
            name:  "<?= addslashes($user['name'] ?? '') ?>",
            email: "<?= addslashes($user['email'] ?? '') ?>",
            contact: "<?= addslashes($user['mobile'] ?? '') ?>"
        },
        theme: { color: '#6d28d9' },
        handler(response) { verifyPayment(response, payload); },
        modal: {
            ondismiss() {
                const btn = document.getElementById('payBtn');
                btn.innerHTML = `<i class="fa-solid fa-lock me-2"></i>Pay Securely ₹<span id="payBtnAmt">${booking.total.toFixed(2)}</span>`;
                btn.disabled = false;
            }
        }
    });
    rzp.on('payment.failed', r => alert('Payment Failed: ' + r.error.description));
    rzp.open();
}

function verifyPayment(payData, payload) {
    document.getElementById('step4').innerHTML = `
        <div class="text-center py-5">
            <div class="spinner-grow text-success mb-3" role="status" style="width:3rem;height:3rem;"></div>
            <h3 class="fw-bold text-dark">Payment Successful!</h3>
            <p class="text-secondary">Confirming your booking…</p>
        </div>`;

    const fd = new FormData();
    fd.append('razorpay_payment_id', payData.razorpay_payment_id);
    fd.append('razorpay_order_id',   payData.razorpay_order_id);
    fd.append('razorpay_signature',  payData.razorpay_signature);
    fd.append('payload', JSON.stringify(payload));

    fetch('ajax_verify_payment.php', { method:'POST', body:fd })
        .then(r => r.json())
        .then(res => {
            if (res.status === 'success') {
                window.location.href = `user_history.php?booking=${res.booking_id}&success=1`;
            } else {
                alert('Booking confirmation failed: ' + res.message);
                window.location.reload();
            }
        })
        .catch(() => alert('Critical error. Contact support with payment ID: ' + payData.razorpay_payment_id));
}
</script>

<?php require_once __DIR__ . '/includes_footer.php'; ?>



