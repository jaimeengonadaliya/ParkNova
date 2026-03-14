<?php
require_once __DIR__ . '/includes_header.php';
require_once __DIR__ . '/includes_navbar.php';
?>

<div class="container py-5 mt-5">
    <div class="glass-panel p-5 animate-up">
        <h1 class="hero-title mb-4 fw-bold text-center">Contact <span class="text-gradient">ParkNova</span></h1>
        <p class="lead text-secondary mb-5 fs-4 text-center">If you have questions or need assistance, feel free to contact us.</p>
        
        <div class="row g-5">
            <div class="col-lg-6">
                <div class="card-3d p-4 h-100">
                    <h4 class="text-primary mb-4 fw-bold">Get in Touch</h4>
                    <form>
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label text-secondary small fw-bold text-uppercase">Your Name</label>
                                <input type="text" class="form-control rounded-pill border-secondary border-opacity-25 py-2 px-3" placeholder="Jaimeen Gondaliya">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-secondary small fw-bold text-uppercase">Email Address</label>
                                <input type="email" class="form-control rounded-pill border-secondary border-opacity-25 py-2 px-3" placeholder="jaimeen@example.com">
                            </div>
                        </div>
                        <div class="mb-4">
                            <label class="form-label text-secondary small fw-bold text-uppercase">Message</label>
                            <textarea class="form-control rounded-4 border-secondary border-opacity-25 p-3" rows="5" placeholder="How can we assist you today?"></textarea>
                        </div>
                        <button type="submit" class="btn-primary-3d w-100 py-3 fs-5">Send Message <i class="fa-solid fa-paper-plane ms-2"></i></button>
                    </form>
                </div>
            </div>
            
            <div class="col-lg-6">
                <div class="row g-4 h-100">
                    <div class="col-12">
                        <div class="card-3d p-4 d-flex align-items-center gap-4">
                            <div class="bg-primary bg-opacity-10 text-primary p-3 rounded-circle border border-primary border-opacity-25" style="width: 70px; height: 70px; display: flex; align-items: center; justify-content: center;">
                                <i class="fa-solid fa-code fs-3"></i>
                            </div>
                            <div>
                                <h5 class="text-primary fw-bold mb-1">Developer</h5>
                                <p class="text-secondary mb-0 fs-5">Jaimeen Gondaliya</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="card-3d p-4 d-flex align-items-center gap-4">
                            <div class="bg-success bg-opacity-10 text-success p-3 rounded-circle border border-success border-opacity-25" style="width: 70px; height: 70px; display: flex; align-items: center; justify-content: center;">
                                <i class="fa-solid fa-envelope fs-3"></i>
                            </div>
                            <div>
                                <h5 class="text-primary fw-bold mb-1">Email</h5>
                                <p class="text-secondary mb-0 fs-5"><a href="mailto:jaimeengondaliya@gmail.com" class="text-decoration-none text-secondary">jaimeengondaliya@gmail.com</a></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="card-3d p-4 d-flex align-items-center gap-4">
                            <div class="bg-warning bg-opacity-10 text-warning p-3 rounded-circle border border-warning border-opacity-25" style="width: 70px; height: 70px; display: flex; align-items: center; justify-content: center;">
                                <i class="fa-solid fa-phone fs-3"></i>
                            </div>
                            <div>
                                <h5 class="text-primary fw-bold mb-1">Phone</h5>
                                <p class="text-secondary mb-0 fs-5">+91 99136 90245</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="card-3d p-4 d-flex align-items-center gap-4">
                            <div class="bg-info bg-opacity-10 text-info p-3 rounded-circle border border-info border-opacity-25" style="width: 70px; height: 70px; display: flex; align-items: center; justify-content: center;">
                                <i class="fa-solid fa-location-dot fs-3"></i>
                            </div>
                            <div>
                                <h5 class="text-primary fw-bold mb-1">Location</h5>
                                <p class="text-secondary mb-0 fs-5">ParkNova Office Surat, Gujarat, India</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes_footer.php'; ?>


