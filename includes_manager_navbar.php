<!-- Manager Navbar -->
<nav class="navbar navbar-expand-lg sticky-top shadow-sm py-3 bg-surface" style="background: var(--nav-bg) !important;">
    <div class="container-fluid px-4">
        <a class="navbar-brand fw-bold fs-5 d-flex align-items-center gap-2" href="<?= $base_url ?>/manager_dashboard.php">
            <img src="<?= $base_url ?>/logo_icon.png" alt="Logo" style="width: 28px; height: 28px; object-fit: contain;">
            ParkNova
            <span class="badge bg-info bg-opacity-10 text-info ms-1 rounded-pill border border-info border-opacity-25" style="font-size:0.7rem">Manager</span>
        </a>
        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#managerNavbar">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="managerNavbar">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0 ms-lg-4">
                <li class="nav-item px-1">
                    <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'manager_dashboard.php' ? 'active fw-bold' : '' ?>" href="<?= $base_url ?>/manager_dashboard.php">
                        <i class="fa-solid fa-gauge-high me-1"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item px-1">
                    <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'manager_vehicle_entry.php' ? 'active fw-bold' : '' ?>" href="<?= $base_url ?>/manager_vehicle_entry.php">
                        <i class="fa-solid fa-right-to-bracket me-1 text-success"></i> Entry
                    </a>
                </li>
                <li class="nav-item px-1">
                    <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'manager_vehicle_exit.php' ? 'active fw-bold' : '' ?>" href="<?= $base_url ?>/manager_vehicle_exit.php">
                        <i class="fa-solid fa-right-from-bracket me-1 text-danger"></i> Exit
                    </a>
                </li>
                <li class="nav-item px-1">
                    <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'manager_bookings.php' ? 'active fw-bold' : '' ?>" href="<?= $base_url ?>/manager_bookings.php">
                        <i class="fa-solid fa-book-bookmark me-1"></i> Bookings
                    </a>
                </li>
                <li class="nav-item px-1">
                    <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'manager_manage_slots.php' ? 'active fw-bold' : '' ?>" href="<?= $base_url ?>/manager_manage_slots.php">
                        <i class="fa-solid fa-grip me-1"></i> Live Slots
                    </a>
                </li>
            </ul>
            <div class="d-flex align-items-center gap-3">
                <button type="button" id="themeToggleBtn" class="theme-toggle" title="Toggle Dark/Light Mode">
                    <i class="fa-solid fa-moon"></i>
                </button>
                <?php if (isset($manager_parking) && $manager_parking): ?>
                <span class="badge bg-info text-dark rounded-pill px-3 py-2 fw-bold d-none d-md-inline-flex align-items-center gap-1">
                    <i class="fa-solid fa-location-dot"></i><?= htmlspecialchars($manager_parking['parking_name'] ?? '') ?>
                </span>
                <?php endif; ?>
                <div class="dropdown">
                    <a class="nav-link dropdown-toggle btn btn-light text-primary px-3 rounded-pill fw-medium d-flex align-items-center" href="#" role="button" data-bs-toggle="dropdown">
                        <div class="bg-info rounded-circle d-inline-flex align-items-center justify-content-center me-2" style="width:32px;height:32px;">
                            <i class="fa-solid fa-user-tie text-dark small"></i>
                        </div>
                        <span class="fw-medium"><?= htmlspecialchars($_SESSION['name']) ?></span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-2">
                        <li><span class="dropdown-item-text text-muted small"><?= htmlspecialchars($_SESSION['email'] ?? '') ?></span></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger py-2" href="<?= $base_url ?>/user_logout.php"><i class="fa-solid fa-arrow-right-from-bracket me-2"></i> Logout</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</nav>



