<?php
require_once __DIR__ . '/includes_header.php';
require_once __DIR__ . '/includes_navbar.php';
?>

<div class="container py-5 mt-5">
    <div class="glass-panel p-5 animate-up">
        <h1 class="hero-title mb-4 fw-bold text-center">Our <span class="text-gradient">Services</span></h1>
        <p class="lead text-secondary mb-5 fs-4 text-center">ParkNova provides smart digital parking services designed to improve the parking experience.</p>
        
        <div class="row g-4">
            <div class="col-md-6 col-lg-4">
                <div class="card-3d p-4 h-100">
                    <div class="bg-primary bg-opacity-10 text-primary p-3 rounded-4 d-inline-block mb-3 border border-primary border-opacity-25">
                        <i class="fa-solid fa-calendar-check fs-2"></i>
                    </div>
                    <h4 class="text-primary fw-bold mb-3">Smart Parking Slot Booking</h4>
                    <p class="text-secondary small">Users can reserve parking slots in advance using an easy-to-use booking system.</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="card-3d p-4 h-100">
                    <div class="bg-warning bg-opacity-10 text-warning p-3 rounded-4 d-inline-block mb-3 border border-warning border-opacity-25">
                        <i class="fa-solid fa-satellite-dish fs-2"></i>
                    </div>
                    <h4 class="text-primary fw-bold mb-3">Real-Time Slot Availability</h4>
                    <p class="text-secondary small">The system shows available parking slots instantly, helping users find parking quickly.</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="card-3d p-4 h-100">
                    <div class="bg-success bg-opacity-10 text-success p-3 rounded-4 d-inline-block mb-3 border border-success border-opacity-25">
                        <i class="fa-solid fa-credit-card fs-2"></i>
                    </div>
                    <h4 class="text-primary fw-bold mb-3">Secure Online Payment</h4>
                    <p class="text-secondary small">ParkNova integrates secure online payment systems such as <strong>Razorpay</strong> to allow users to pay for parking conveniently.</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-6">
                <div class="card-3d p-4 h-100 text-center">
                    <div class="bg-info bg-opacity-10 text-info p-3 rounded-4 d-inline-block mb-3 border border-info border-opacity-25">
                        <i class="fa-solid fa-sliders fs-2"></i>
                    </div>
                    <h4 class="text-primary fw-bold mb-3">Parking Management System</h4>
                    <p class="text-secondary">Admins and managers can efficiently manage parking locations, monitor slot status, and track bookings through dedicated control panels.</p>
                </div>
            </div>
            <div class="col-md-12 col-lg-6">
                <div class="card-3d p-4 h-100 text-center">
                    <div class="bg-danger bg-opacity-10 text-danger p-3 rounded-4 d-inline-block mb-3 border border-danger border-opacity-25">
                        <i class="fa-solid fa-car-side fs-2"></i>
                    </div>
                    <h4 class="text-primary fw-bold mb-3">Vehicle-Based Entry System</h4>
                    <p class="text-secondary">Users enter the parking area using their <strong>Booking ID or Vehicle Number</strong>, which is verified by the parking manager.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes_footer.php'; ?>


