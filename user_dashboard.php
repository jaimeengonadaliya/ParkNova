<?php
require_once __DIR__ . '/config_db.php';

if (!isLoggedIn()) {
    redirect($base_url . '/user_login.php');
}

$query = "SELECT * FROM parking_locations";
$stmt = $pdo->prepare($query);
$stmt->execute();
$parking_lots = $stmt->fetchAll();


require_once __DIR__ . '/includes_header.php';
require_once __DIR__ . '/includes_navbar.php';
?>

<div class="container py-5">
    <div class="row mb-5 align-items-center">
        <div class="col-md-6">
            <h1 class="fw-bold text-primary mb-2">My Dashboard</h1>
            <p class="text-secondary mb-0">Select a parking location to book your slot.</p>
        </div>
        <div class="col-md-6 text-md-end mt-3 mt-md-0">
            <span class="badge bg-primary bg-opacity-10 text-primary p-2 px-3 rounded-pill border border-primary border-opacity-25">
                <i class="fa-solid fa-clock me-2"></i> Last Login: <?= date('d M, H:i') ?>
            </span>
        </div>
    </div>

    <div class="row g-4">
        <?php foreach ($parking_lots as $lot): ?>
            <div class="col-md-6 col-lg-4">
                <div class="card-3d h-100 overflow-hidden">
                    <div class="position-relative">
                        <img src="<?= getCityImage($lot['city']) ?>" 
                             class="card-img-top" alt="<?= htmlspecialchars($lot['parking_name']) ?>" style="height: 180px; object-fit: cover;">
                        <div class="position-absolute top-0 end-0 p-3">
                            <span class="badge bg-success shadow-lg">Open</span>
                        </div>
                    </div>
                    <div class="card-body p-4 bg-surface">
                        <h4 class="fw-bold text-primary mb-2"><?= htmlspecialchars($lot['parking_name']) ?></h4>
                        <p class="text-secondary small mb-3">
                            <i class="fa-solid fa-location-dot me-2 text-primary"></i> <?= htmlspecialchars($lot['address']) ?>, <?= htmlspecialchars($lot['city']) ?>
                        </p>
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div class="text-primary fw-bold fs-4">₹<?= number_format($lot['price_per_hour'], 2) ?><span class="small text-secondary fw-normal">/hr</span></div>
                            <div class="text-secondary small fw-bold text-uppercase"><i class="fa-solid fa-grip me-1"></i> <?= $lot['total_slots'] ?> Slots</div>
                        </div>
                        <a href="user_book_slot.php?parking=<?= $lot['parking_id'] ?>" class="btn-primary-3d w-100 justify-content-center text-decoration-none">
                            <i class="fa-solid fa-calendar-check me-2"></i> View Slots
                        </a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php require_once __DIR__ . '/includes_footer.php'; ?>



