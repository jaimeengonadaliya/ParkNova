<?php
require_once __DIR__ . '/config_db.php';
if (!isLoggedIn() || !isManager()) redirect($base_url . '/user_login.php');

$manager_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM parking_locations WHERE manager_id = ?");
$stmt->execute([$manager_id]);
$manager_parking = $stmt->fetch();
if (!$manager_parking) die('<div class="container py-5 text-center"><h3>No parking assigned.</h3></div>');

$parking_id = $manager_parking['parking_id'];
$success = ''; $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_price = (float)$_POST['price_per_hour'];
    if ($new_price <= 0) {
        $error = "Please enter a valid price greater than ₹0.";
    } else {
        $pdo->prepare("UPDATE parking_locations SET price_per_hour = ? WHERE parking_id = ? AND manager_id = ?")->execute([$new_price, $parking_id, $manager_id]);
        $success = "Price updated to ₹" . number_format($new_price, 2) . " per hour.";
        // Refresh
        $stmt = $pdo->prepare("SELECT * FROM parking_locations WHERE manager_id = ?");
        $stmt->execute([$manager_id]);
        $manager_parking = $stmt->fetch();
    }
}

require_once __DIR__ . '/includes_header.php';
require_once __DIR__ . '/includes_manager_navbar.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-7">
            <div class="d-flex align-items-center gap-3 mb-4">
                <div class="text-primary"><i class="fa-solid fa-tag fa-2x"></i></div>
                <div>
                    <h2 class="fw-bold mb-0 text-primary">Update Parking Price</h2>
                    <p class="text-secondary small fw-medium mb-0"><?= htmlspecialchars($manager_parking['parking_name']) ?> — <?= htmlspecialchars($manager_parking['city']) ?></p>
                </div>
            </div>

            <?php if ($error): ?><div class="alert glass-panel border-start border-4 border-danger mb-4 d-flex align-items-center gap-3"><i class="fa-solid fa-circle-exclamation text-danger fs-4"></i><span class="text-primary fw-medium"><?= $error ?></span></div><?php endif; ?>
            <?php if ($success): ?><div class="alert glass-panel border-start border-4 border-success mb-4 d-flex align-items-center gap-3"><i class="fa-solid fa-circle-check text-success fs-4"></i><span class="text-primary fw-medium"><?= $success ?></span></div><?php endif; ?>

            <div class="card-3d overflow-hidden">
                <!-- Current Price Banner -->
                <div class="text-white p-5 position-relative overflow-hidden" style="background: linear-gradient(135deg, #1e3a5f, #0f5132);">
                    <div class="position-absolute top-0 end-0 rounded-circle bg-white opacity-10" style="width:200px;height:200px;transform:translate(30%,-30%)"></div>
                    <p class="mb-2 opacity-75 fw-medium text-uppercase small"><i class="fa-solid fa-building me-2"></i>Current Rate</p>
                    <h1 class="display-4 fw-bold mb-1" style="text-shadow:0 2px 8px rgba(0,0,0,0.3)">
                        ₹<?= number_format($manager_parking['price_per_hour'], 2) ?>
                    </h1>
                    <p class="mb-0 opacity-75 fs-5">per hour — <?= htmlspecialchars($manager_parking['parking_name']) ?></p>
                </div>

                <div class="card-body p-5 bg-surface">
                    <form action="" method="POST" class="needs-validation" novalidate>
                        <div class="mb-4">
                            <label class="form-label text-secondary fw-bold small text-uppercase">New Price Per Hour (₹)</label>
                            <div class="input-group">
                                <span class="input-group-text bg-transparent text-primary fw-bold fs-5">₹</span>
                                <input type="number" name="price_per_hour" class="form-control fs-5 fw-bold"
                                    value="<?= $manager_parking['price_per_hour'] ?>"
                                    min="1" max="10000" step="0.5" required placeholder="Enter new hourly rate">
                                <div class="invalid-feedback">Please enter a valid price (₹1 to ₹10000).</div>
                            </div>
                            <div class="form-text text-secondary mt-2"><i class="fa-solid fa-circle-info me-1"></i> This will immediately affect all new bookings for your parking location.</div>
                        </div>
                        <div class="d-flex gap-3 mt-4">
                            <a href="<?= $base_url ?>/manager_dashboard.php" class="btn btn-outline-secondary rounded-pill px-4 fw-bold">Cancel</a>
                            <button type="submit" class="btn-primary-3d"><i class="fa-solid fa-floppy-disk me-2"></i>Update Price</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes_footer.php'; ?>



