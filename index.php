<?php
require_once __DIR__ . '/includes_header.php';
require_once __DIR__ . '/includes_navbar.php';
?>

<!-- Hero Section -->
<section class="hero text-center text-lg-start position-relative overflow-hidden py-5 mt-4">
    <div class="container position-relative" style="z-index: 2;">
        <div class="row align-items-center">
            <div class="col-lg-6 mb-5 mb-lg-0 animate-up">
                <span class="badge bg-primary bg-opacity-10 text-primary mb-3 px-3 py-2 rounded-pill shadow-sm border border-primary"><i class="fa-solid fa-bolt me-2 text-warning"></i>ParkNova Desktop Web App</span>
                <h1 class="hero-title mb-4 fw-bold" style="font-size: clamp(3rem, 5vw, 5rem); letter-spacing: -1.5px;">Park <span class="text-gradient">Smarter</span>,<br>Not Harder.</h1>
                <p class="lead text-secondary mb-5 fs-4">ParkNova is an advanced smart parking management system designed to help users find parking locations, view available slots in real time, and reserve parking spaces quickly while improving parking management efficiency..</p>
                <div class="d-flex flex-column flex-sm-row gap-3 justify-content-center justify-content-lg-start">
                    <a href="<?= $base_url ?>/user_search_parking.php" class="btn-primary-3d px-5 text-decoration-none text-center"><i class="fa-solid fa-magnifying-glass me-2"></i> Find Parking</a>
                    <?php if (!isLoggedIn()): ?>
                        <a href="<?= $base_url ?>/user_register.php" class="btn-3d px-5 text-decoration-none text-center"><i class="fa-solid fa-user-plus me-2"></i> Sign Up</a>
                    <?php endif; ?>
                </div>
                
                <div class="mt-5 d-flex align-items-center justify-content-center justify-content-lg-start gap-4">
                    <div class="d-flex flex-column text-center text-lg-start">
                        <h2 class="text-primary fw-bold mb-0 fs-1">1M+</h2>
                        <span class="text-secondary small fw-bold text-uppercase tracking-wider">Successful Bookings</span>
                    </div>
                    <div style="width: 2px; height: 50px; background: var(--border-color);"></div>
                    <div class="d-flex flex-column text-center text-lg-start">
                        <h2 class="text-primary fw-bold mb-0 fs-1">500+</h2>
                        <span class="text-secondary small fw-bold text-uppercase tracking-wider">Smart Parking Lots</span>
                    </div>
                </div>
            </div>
            
           <div class="col-lg-6 d-none d-lg-block position-relative">

    <div class="glass-panel p-4 ms-5 animate-up">

        <div class="d-flex justify-content-between align-items-center mb-4 border-bottom border-secondary border-opacity-25 pb-3">

    <div class="d-flex align-items-center gap-2">
        <i class="fa-solid fa-location-dot text-danger"></i>
        <h5 class="mb-0 fw-bold text-primary">Downtown Plaza Parking</h5>
    </div>

    <span class="badge bg-success bg-opacity-25 text-success border border-success border-opacity-50 px-3 py-2 rounded-pill">
        <i class="fa-solid fa-square-parking me-1"></i> 8 Slots Available
    </span>

</div>

<!-- Parking Slot Preview -->

<div class="slot-grid pb-3">

    <div class="parking-slot booked">A1</div>
    <div class="parking-slot booked">A2</div>
    <div class="parking-slot available selected">A3</div>
    <div class="parking-slot available">A4</div>

    <div class="parking-slot available">B1</div>
    <div class="parking-slot reserved">B2</div>
    <div class="parking-slot booked">B3</div>
    <div class="parking-slot available">B4</div>

</div>

<!-- Slot Legend -->

<div class="d-flex justify-content-center gap-4 small text-secondary mb-3">

    <div class="d-flex align-items-center gap-1">
        <span class="parking-slot available legend-box"></span> Available
    </div>

    <div class="d-flex align-items-center gap-1">
        <span class="parking-slot booked legend-box"></span> Booked
    </div>

    <div class="d-flex align-items-center gap-1">
        <span class="parking-slot reserved legend-box"></span> Reserved
    </div>

</div>

<div class="text-center">

    <button class="btn-3d w-100 justify-content-center border border-primary">
        <i class="fa-solid fa-square-parking me-2 text-primary"></i>
        View Live Parking Slots
    </button>

</div>
    </div>


    <!-- Booking Confirmation Floating Card -->

    

    <div class="position-absolute top-0 start-0 translate-middle p-3 glass-floating rounded-4 shadow-lg d-flex align-items-center gap-3 border border-secondary"
style="z-index:10;animation:translateY 4s ease-in-out infinite alternate;">

<div class="bg-success bg-opacity-10 text-success p-2 rounded-3 fs-4">
<i class="fa-solid fa-check-circle"></i>
</div>

<div>
<div class="fw-bold text-primary">Booking Confirmed</div>
<small class="text-secondary">Slot A3 Reserved</small>
</div>

</div>



    <!-- Feature Floating Card -->

    <div class="position-absolute bottom-0 end-0 translate-middle-y p-3 glass-panel rounded-4 d-flex align-items-center gap-3 border border-secondary"
         style="z-index: 10; transform: translateX(20px); animation: translateY 5s ease-in-out infinite alternate-reverse;">

        <div class="text-warning fs-2">
            <i class="fa-solid fa-bolt"></i>
        </div>

        <div>
            <div class="fw-bold fs-5 text-primary">Fast Parking</div>
            <div class="small fw-bold text-secondary text-uppercase">
                Instant Slot Reservation
            </div>
        </div>

    </div>

</div>
    </div>
</section>

<!-- Features Section -->
<section class="py-5 mt-4 border-top border-secondary border-opacity-10">
    <div class="container py-5">
        <div class="text-center mb-5 pb-3">
            <span class="text-gradient fw-bold text-uppercase tracking-wider">Features</span>
            <h2 class="fw-bold fs-1 mt-2 text-primary">Why choose ParkNova?</h2>
            <p class="text-secondary fs-5 col-md-8 mx-auto">We provide a seamless web experience from finding a spot to paying the ticket.</p>
        </div>
        
        <div class="row g-4 mb-5">
            <div class="col-md-4">
                <div class="card-3d p-4 h-100 text-center">
                    <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-inline-flex align-items-center justify-content-center mb-4 border border-primary border-opacity-25" style="width: 80px; height: 80px;">
                        <i class="fa-solid fa-location-crosshairs fs-1"></i>
                    </div>
                    <h4 class="fw-bold text-primary">Real-time Tracking</h4>
                    <p class="text-secondary mb-0">See live slot availability before you even leave your house. No more circling the block looking for a spot.</p>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card-3d p-4 h-100 text-center">
                    <div class="bg-info bg-opacity-10 text-info rounded-circle d-inline-flex align-items-center justify-content-center mb-4 border border-info border-opacity-25" style="width: 80px; height: 80px;">
                        <i class="fa-solid fa-calendar-check fs-1"></i>
                    </div>
                    <h4 class="fw-bold text-primary">Advanced Booking</h4>
                    <p class="text-secondary mb-0">Reserve your parking slot hours or days in advance. Guarantee a spot for your important meetings or events.</p>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card-3d p-4 h-100 text-center">
                    <div class="bg-success bg-opacity-10 text-success rounded-circle d-inline-flex align-items-center justify-content-center mb-4 border border-success border-opacity-25" style="width: 80px; height: 80px;">
                        <i class="fa-solid fa-wallet fs-1"></i>
                    </div>
                    <h4 class="fw-bold text-primary">Seamless Payments</h4>
                    <p class="text-secondary mb-0">Pay securely online or choose to pay via cash at the exit. Transparent pricing with no hidden fees.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Call to Action -->
<section class="py-5 mb-5 position-relative overflow-hidden park-cta">

    <!-- Background Glow -->
    <div class="cta-glow"></div>

    <div class="container py-5 text-center position-relative" style="z-index:2;">

        <h2 class="fw-bold mb-4 display-5 text-white">
            Ready to park smarter?
        </h2>

        <p class="fs-5 text-white-50 mb-5 col-md-7 mx-auto">
            Join thousands of drivers using ParkNova to quickly find and reserve parking spaces. 
            Create your account today and start parking without stress.
        </p>

        <a href="<?= $base_url ?>/user_register.php"
           class="btn btn-light btn-lg px-5 shadow fw-semibold">
           Create Free Account
           <i class="fa-solid fa-arrow-right ms-2"></i>
        </a>

    </div>

</section>

<style>
@keyframes translateY {
    0% { transform: translateY(0); }
    100% { transform: translateY(-15px); }
}


.glass-panel{
background: rgba(255,255,255,0.08); /* light transparency */
backdrop-filter: blur(14px);
-webkit-backdrop-filter: blur(14px);
border: 1px solid rgba(255,255,255,0.25);
box-shadow: 0 8px 20px rgba(0,0,0,0.15);
border-radius: 16px;
}

.glass-floating{
background: rgba(255,255,255,0.06); /* slightly lighter */
backdrop-filter: blur(12px);
-webkit-backdrop-filter: blur(12px);
border: 1px solid rgba(255,255,255,0.20);
border-radius: 16px;
}

.slot-grid{
display:grid;
grid-template-columns:repeat(4,1fr);
gap:14px;
}

.parking-slot{
height:60px;
border-radius:12px;
display:flex;
align-items:center;
justify-content:center;
font-weight:600;
color:white;
box-shadow:0 4px 10px rgba(0,0,0,0.15);
}

.parking-slot.available{background:#22c55e;}
.parking-slot.booked{background:#ef4444;}
.parking-slot.reserved{background:#f59e0b;}
.parking-slot.selected{outline:3px solid #3b82f6;}

.legend-box{
width:14px;
height:14px;
border-radius:3px;
display:inline-block;
}

.legend-box.available{background:#22c55e;}
.legend-box.booked{background:#ef4444;}
.legend-box.reserved{background:#f59e0b;}

@keyframes translateY{
0%{transform:translateY(0);}
100%{transform:translateY(-15px);}
}

.park-cta{
background: linear-gradient(135deg,#2563eb,#3b82f6,#1d4ed8);
border-radius:20px;
position:relative;
}

.cta-glow{
position:absolute;
top:50%;
left:50%;
transform:translate(-50%,-50%);
width:80%;
height:200%;
background: radial-gradient(circle, rgba(255,255,255,0.25), transparent 70%);
filter: blur(120px);
opacity:.6;
}


</style>

<?php require_once __DIR__ . '/includes_footer.php'; ?>


