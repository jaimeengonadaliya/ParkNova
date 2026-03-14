<?php
require_once __DIR__ . '/includes_header.php';
require_once __DIR__ . '/includes_navbar.php';
?>

<div class="container py-5 mt-5">
    <div class="glass-panel p-5 animate-up">
        <h1 class="hero-title mb-4 fw-bold text-center">About <span class="text-gradient">ParkNova</span></h1>
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <p class="lead text-secondary mb-4 fs-4 text-center">ParkNova is a smart parking management system created to address the common challenge of finding parking in crowded city areas.</p>
                <p class="text-secondary mb-5 text-center px-lg-5">This platform was <strong>designed and developed by Jaimeen Gondaliya</strong>, an IT student passionate about building practical software solutions that improve everyday life.</p>
                
                <h4 class="text-primary fw-bold mb-4 text-center">ParkNova connects three types of users:</h4>
                <div class="row g-4 mb-5">
                    <div class="col-md-4">
                        <div class="card-3d p-4 h-100 text-center">
                            <div class="stat-icon p-3 rounded-circle bg-primary bg-opacity-10 text-primary mb-3 mx-auto shadow-sm">
                                <i class="fa-solid fa-shield-halved fs-3"></i>
                            </div>
                            <h5 class="text-primary fw-bold">Admin</h5>
                            <p class="text-secondary small mb-0">Manages parking locations and assigns managers.</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card-3d p-4 h-100 text-center">
                            <div class="stat-icon p-3 rounded-circle bg-warning bg-opacity-10 text-warning mb-3 mx-auto shadow-sm">
                                <i class="fa-solid fa-user-tie fs-3"></i>
                            </div>
                            <h5 class="text-primary fw-bold">Manager</h5>
                            <p class="text-secondary small mb-0">Monitors parking operations and vehicle entry/exit.</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card-3d p-4 h-100 text-center">
                            <div class="stat-icon p-3 rounded-circle bg-success bg-opacity-10 text-success mb-3 mx-auto shadow-sm">
                                <i class="fa-solid fa-car fs-3"></i>
                            </div>
                            <h5 class="text-primary fw-bold">User</h5>
                            <p class="text-secondary small mb-0">Finds and books parking slots easily.</p>
                        </div>
                    </div>
                </div>

                <div class="card-3d p-5 text-center bg-primary text-white shadow-lg border-0">
                    <p class="fs-5 mb-0 fw-medium">The system includes features such as real-time slot monitoring, online booking, and secure online payments. ParkNova aims to reduce traffic congestion and save time by providing an efficient and user-friendly parking system.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes_footer.php'; ?>


