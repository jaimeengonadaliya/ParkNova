<!-- Navbar -->
<nav class="navbar navbar-expand-lg sticky-top shadow-sm py-3 navbar-light bg-surface" style="background: var(--nav-bg) !important;">
    <div class="container">
        <a class="navbar-brand fw-bold fs-4 d-flex align-items-center" href="<?= $base_url ?>/index.php">
            <img src="<?= $base_url ?>/logo_icon.png" alt="Logo" style="width: 32px; height: 32px; margin-right: 10px; object-fit: contain;"> ParkNova
        </a>
        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto align-items-center">
                <li class="nav-item px-2">
                    <a class="nav-link" href="<?= $base_url ?>/index.php">Home</a>
                </li>
                <li class="nav-item px-2">
                    <a class="nav-link" href="<?= $base_url ?>/user_search_parking.php">Search Parking</a>
                </li>
                
                <li class="nav-item px-2 d-flex align-items-center">
                    <button type="button" id="themeToggleBtn" class="theme-toggle" title="Toggle Dark/Light Mode">
                        <i class="fa-solid fa-moon"></i>
                    </button>
                </li>

                <?php if (isLoggedIn()): ?>
                    <?php if (isSuperAdmin()): ?>
                        <li class="nav-item px-2">
                            <a class="nav-link" href="<?= $base_url ?>/admin_dashboard.php">Admin Dashboard</a>
                        </li>
                    <?php elseif (isManager()): ?>
                        <li class="nav-item px-2">
                            <a class="nav-link" href="<?= $base_url ?>/manager_dashboard.php">Manager Dashboard</a>
                        </li>
                    <?php endif; ?>
                    <li class="nav-item px-2">
                        <a class="nav-link" href="<?= $base_url ?>/user_history.php">My Bookings</a>
                    </li>
                    <li class="nav-item dropdown ms-3">
                        <a class="nav-link dropdown-toggle btn btn-light text-primary px-4 rounded-pill fw-medium d-flex align-items-center" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fa-regular fa-user me-2"></i> <?= htmlspecialchars($_SESSION['name']) ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-2" aria-labelledby="userDropdown">
                            <li><a class="dropdown-item py-2" href="<?= $base_url ?>/user_profile.php"><i class="fa-solid fa-user-gear me-2"></i> Profile</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger py-2" href="<?= $base_url ?>/user_logout.php"><i class="fa-solid fa-arrow-right-from-bracket me-2"></i> Logout</a></li>
                        </ul>
                    </li>
                <?php else: ?>
                    <li class="nav-item ms-3">
                        <a class="nav-link bg-light text-primary px-4 rounded-pill fw-bold shadow-sm hover-lift" href="<?= $base_url ?>/user_login.php">Login / Register</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>



