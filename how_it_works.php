<?php
require_once __DIR__ . '/includes_header.php';
require_once __DIR__ . '/includes_navbar.php';
?>

<div class="container py-5 mt-5">
    <div class="glass-panel p-5 animate-up">
        <h1 class="hero-title mb-4 fw-bold text-center">How ParkNova <span class="text-gradient">Works</span></h1>
        <p class="lead text-secondary mb-5 fs-4 text-center">ParkNova simplifies parking in a few easy steps.</p>
        
        <div class="row g-5">
            <div class="col-md-4 text-center">
                <div class="card-3d p-4 border-0 h-100 position-relative">
                    <div class="position-absolute top-0 start-50 translate-middle-y bg-primary text-white rounded-circle d-flex align-items-center justify-content-center fw-bold fs-3 shadow-lg border border-white border-2" style="width: 60px; height: 60px; transform: translateY(-30px);">1</div>
                    <div class="mt-4">
                        <h4 class="text-primary fw-bold mb-3">Find Parking</h4>
                        <p class="text-secondary">Users search for available parking locations available in the system.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 text-center">
                <div class="card-3d p-4 border-0 h-100 position-relative">
                    <div class="position-absolute top-0 start-50 translate-middle-y bg-warning text-dark rounded-circle d-flex align-items-center justify-content-center fw-bold fs-3 shadow-lg border border-white border-2" style="width: 60px; height: 60px; transform: translateY(-30px);">2</div>
                    <div class="mt-4">
                        <h4 class="text-primary fw-bold mb-3">Select Parking Slot</h4>
                        <p class="text-secondary">Users check available slots and choose a suitable parking space.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 text-center">
                <div class="card-3d p-4 border-0 h-100 position-relative">
                    <div class="position-absolute top-0 start-50 translate-middle-y bg-info text-white rounded-circle d-flex align-items-center justify-content-center fw-bold fs-3 shadow-lg border border-white border-2" style="width: 60px; height: 60px; transform: translateY(-30px);">3</div>
                    <div class="mt-4">
                        <h4 class="text-primary fw-bold mb-3">Enter Booking Details</h4>
                        <p class="text-secondary">Users enter booking details such as vehicle type, booking date, entry time, and exit time.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 text-center mt-lg-5">
                <div class="card-3d p-4 border-0 h-100 position-relative">
                    <div class="position-absolute top-0 start-50 translate-middle-y bg-success text-white rounded-circle d-flex align-items-center justify-content-center fw-bold fs-3 shadow-lg border border-white border-2" style="width: 60px; height: 60px; transform: translateY(-30px);">4</div>
                    <div class="mt-4">
                        <h4 class="text-primary fw-bold mb-3">Secure Payment</h4>
                        <p class="text-secondary">Users complete the booking using secure online payment through integrated gateways.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 text-center mt-lg-5">
                <div class="card-3d p-4 border-0 h-100 position-relative">
                    <div class="position-absolute top-0 start-50 translate-middle-y bg-danger text-white rounded-circle d-flex align-items-center justify-content-center fw-bold fs-3 shadow-lg border border-white border-2" style="width: 60px; height: 60px; transform: translateY(-30px);">5</div>
                    <div class="mt-4">
                        <h4 class="text-primary fw-bold mb-3">Parking Entry</h4>
                        <p class="text-secondary">At the parking location, the manager verifies the <strong>Booking ID or Vehicle Number</strong> before allowing the vehicle to enter.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes_footer.php'; ?>


