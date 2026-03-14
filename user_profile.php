<?php
require_once __DIR__ . '/config_db.php';

if (!isLoggedIn()) {
    redirect($base_url . '/user_login.php');
}

$user_id = $_SESSION['user_id'];
$success = '';
$error = '';

// Fetch current user data
$stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $mobile = trim($_POST['mobile']);
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    
    if (empty($name) || empty($mobile)) {
        $error = "Name and Mobile number are required.";
    } else {
        $updateQuery = "UPDATE users SET name = ?, mobile = ?";
        $params = [$name, $mobile];
        $updateFields = true;
        
        // Handle password update if fields are filled
        if (!empty($current_password) && !empty($new_password)) {
            if (password_verify($current_password, $user['password'])) {
                if (strlen($new_password) >= 6) {
                    $updateQuery .= ", password = ?";
                    $params[] = password_hash($new_password, PASSWORD_DEFAULT);
                } else {
                    $error = "New password must be at least 6 characters long.";
                    $updateFields = false;
                }
            } else {
                $error = "Current password is incorrect.";
                $updateFields = false;
            }
        }
        
        $updateQuery .= " WHERE user_id = ?";
        $params[] = $user_id;
        
        if ($updateFields) {
            try {
                $stmt = $pdo->prepare($updateQuery);
                $stmt->execute($params);
                $_SESSION['name'] = $name;
                $success = "Profile updated successfully.";
                $stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
                $stmt->execute([$user_id]);
                $user = $stmt->fetch();
            } catch (PDOException $e) {
                $error = "Database Error: " . $e->getMessage();
            }
        }
    }
}

require_once __DIR__ . '/includes_header.php';
require_once __DIR__ . '/includes_navbar.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-9">
            <div class="d-flex align-items-center gap-3 mb-4">
                <div class="text-primary"><i class="fa-solid fa-user-circle fa-2x"></i></div>
                <div>
                    <h2 class="fw-bold mb-0 text-primary">My Profile</h2>
                    <p class="text-secondary small mb-0 fw-medium">Manage your account information and password.</p>
                </div>
            </div>
            
            <div class="card-3d overflow-hidden">
                <div class="row g-0">
                    <!-- Left Identity Panel -->
                    <div class="col-md-4 text-white text-center p-5 d-flex flex-column justify-content-center position-relative overflow-hidden" style="background: linear-gradient(135deg, var(--primary-color), #6d28d9);">
                        <div class="position-absolute top-0 start-0 rounded-circle bg-white opacity-10" style="width: 200px; height: 200px; transform: translate(-50%,-50%);"></div>
                        <div class="position-absolute bottom-0 end-0 rounded-circle bg-white opacity-10" style="width: 150px; height: 150px; transform: translate(40%,40%);"></div>
                        
                        <!-- Avatar Initial -->
                        <div class="bg-white bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3 mx-auto shadow-lg border border-white border-opacity-25" style="width: 100px; height: 100px; position: relative; z-index: 1; font-size: 2.5rem; font-weight: 900;">
                            <?= strtoupper(substr($user['name'], 0, 1)) ?>
                        </div>
                        <h4 class="fw-bold mb-1" style="position: relative; z-index: 1; text-shadow: 0 2px 4px rgba(0,0,0,0.2);"><?= htmlspecialchars($user['name']) ?></h4>
                        <p class="opacity-75 small mb-2 fw-bold text-uppercase" style="position: relative; z-index: 1; letter-spacing: 1px;"><?= ucfirst($user['role']) ?> Account</p>
                        <hr class="border-white border-opacity-25 w-50 mx-auto my-3">
                        <div class="small opacity-75 fw-medium" style="position: relative; z-index: 1;">
                            <i class="fa-regular fa-calendar me-1"></i> Member since <?= date('M Y', strtotime($user['created_at'])) ?>
                        </div>
                        <div class="mt-3" style="position: relative; z-index: 1;">
                            <span class="badge bg-white bg-opacity-10 text-white border border-white border-opacity-25 rounded-pill px-3 py-2">
                                <i class="fa-solid fa-envelope me-1"></i><?= htmlspecialchars($user['email']) ?>
                            </span>
                        </div>
                    </div>
                    
                    <!-- Right Form Panel -->
                    <div class="col-md-8 p-4 p-md-5 bg-surface">
                        
                        <?php if (!empty($error)): ?>
                            <div class="alert glass-panel border-0 border-start border-4 border-danger rounded-4 mb-4 d-flex align-items-center gap-3">
                                <i class="fa-solid fa-circle-exclamation text-danger fs-4"></i>
                                <span class="text-primary fw-medium"><?= $error ?></span>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($success)): ?>
                            <div class="alert glass-panel border-0 border-start border-4 border-success rounded-4 mb-4 d-flex align-items-center gap-3">
                                <i class="fa-solid fa-circle-check text-success fs-4"></i>
                                <span class="text-primary fw-medium"><?= $success ?></span>
                            </div>
                        <?php endif; ?>

                        <form action="" method="POST" class="needs-validation" novalidate>
                            <h5 class="fw-bold border-bottom border-secondary border-opacity-25 pb-3 mb-4 text-primary">Personal Information</h5>
                            
                            <div class="mb-4">
                                <label class="form-label text-secondary fw-bold small text-uppercase">Email Address <span class="text-muted fw-normal">(Read-only)</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-transparent text-primary"><i class="fa-solid fa-at"></i></span>
                                    <input type="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" readonly style="opacity: 0.7; cursor: not-allowed;">
                                </div>
                            </div>
                            
                            <div class="mb-4">
                                <label class="form-label text-secondary fw-bold small text-uppercase">Full Name</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-transparent text-primary"><i class="fa-solid fa-user"></i></span>
                                    <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($user['name']) ?>" required minlength="3" pattern="[A-Za-z\s]+">
                                    <div class="invalid-feedback">Please enter a valid name (letters only).</div>
                                </div>
                            </div>

                            <div class="mb-5">
                                <label class="form-label text-secondary fw-bold small text-uppercase">Mobile Number</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-transparent text-primary"><i class="fa-solid fa-phone"></i></span>
                                    <input type="tel" name="mobile" class="form-control" value="<?= htmlspecialchars($user['mobile']) ?>" required pattern="[0-9]{10}">
                                    <div class="invalid-feedback">Please provide a 10-digit mobile number.</div>
                                </div>
                            </div>

                            <h5 class="fw-bold border-bottom border-secondary border-opacity-25 pb-3 mb-4 text-primary">
                                Change Password <span class="text-secondary fw-normal small">(Leave blank to keep current)</span>
                            </h5>

                            <div class="row g-3 mb-5">
                                <div class="col-12">
                                    <label class="form-label text-secondary fw-bold small text-uppercase">Current Password</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-transparent text-primary"><i class="fa-solid fa-lock"></i></span>
                                        <input type="password" name="current_password" class="form-control" placeholder="Enter current password">
                                    </div>
                                </div>
                                <div class="col-12">
                                    <label class="form-label text-secondary fw-bold small text-uppercase">New Password</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-transparent text-primary"><i class="fa-solid fa-key"></i></span>
                                        <input type="password" name="new_password" class="form-control" placeholder="Min. 6 characters" minlength="6">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="d-flex justify-content-end gap-3">
                                <a href="<?= $base_url ?>/user_history.php" class="btn btn-outline-secondary rounded-pill px-4 fw-bold">My Bookings</a>
                                <button type="submit" class="btn-primary-3d"><i class="fa-solid fa-floppy-disk me-2"></i>Save Changes</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes_footer.php'; ?>



