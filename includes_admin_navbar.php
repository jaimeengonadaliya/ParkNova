<!-- Admin Navbar -->
<nav class="navbar navbar-expand-lg sticky-top shadow-sm py-3 bg-surface" style="background: var(--nav-bg) !important;">
    <div class="container-fluid px-4">
        <a class="navbar-brand fw-bold fs-4 d-flex align-items-center" href="<?= $base_url ?>/admin_dashboard.php">
            <img src="<?= $base_url ?>/logo_icon.png" alt="Logo" style="width: 32px; height: 32px; margin-right: 10px; object-fit: contain;"> ParkNova <span class="badge bg-primary bg-opacity-10 text-primary ms-2 fs-6 rounded-pill border border-primary border-opacity-25">Super Admin</span>
        </a>
        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#adminNavbar">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="adminNavbar">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0 ms-lg-4">
                <li class="nav-item px-1">
                    <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'admin_dashboard.php' ? 'active fw-bold' : '' ?>" href="<?= $base_url ?>/admin_dashboard.php"><i class="fa-solid fa-chart-pie me-1"></i> Dashboard</a>
                </li>
                <li class="nav-item px-1">
                    <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'admin_manage_users.php' ? 'active fw-bold' : '' ?>" href="<?= $base_url ?>/admin_manage_users.php"><i class="fa-solid fa-users me-1"></i> Users</a>
                </li>
                <li class="nav-item px-1">
                    <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'admin_manage_parking.php' ? 'active fw-bold' : '' ?>" href="<?= $base_url ?>/admin_manage_parking.php"><i class="fa-solid fa-map-location-dot me-1"></i> Parking Lots</a>
                </li>
                <li class="nav-item px-1">
                    <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'admin_bookings.php' ? 'active fw-bold' : '' ?>" href="<?= $base_url ?>/admin_bookings.php"><i class="fa-solid fa-book me-1"></i> Bookings</a>
                </li>
                <li class="nav-item px-1">
                    <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'admin_reports.php' ? 'active fw-bold' : '' ?>" href="<?= $base_url ?>/admin_reports.php"><i class="fa-solid fa-file-invoice-dollar me-1"></i> Reports</a>
                </li>
            </ul>
            <div class="d-flex align-items-center gap-2">
                <button type="button" id="themeToggleBtn" class="theme-toggle" title="Toggle Dark/Light Mode">
                    <i class="fa-solid fa-moon"></i>
                </button>
                <a href="<?= $base_url ?>/index.php" class="btn btn-outline-primary btn-sm rounded-pill px-3 fw-bold me-2" target="_blank"><i class="fa-solid fa-globe me-1"></i> View Site</a>
                <div class="dropdown">
                    <a class="nav-link dropdown-toggle btn btn-light text-primary px-3 rounded-pill fw-medium d-flex align-items-center" href="#" role="button" data-bs-toggle="dropdown">
                        <div class="bg-primary rounded-circle d-inline-flex align-items-center justify-content-center me-2" style="width: 32px; height: 32px;">
                            <i class="fa-solid fa-user text-white small"></i>
                        </div>
                        <span class="fw-medium"><?= htmlspecialchars($_SESSION['name']) ?></span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-2">
                        <li><a class="dropdown-item text-danger py-2" href="<?= $base_url ?>/user_logout.php"><i class="fa-solid fa-arrow-right-from-bracket me-2"></i> Logout</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</nav>



