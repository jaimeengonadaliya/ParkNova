<?php
require_once __DIR__ . '/config_db.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirect($base_url . (isSuperAdmin() ? '/admin_dashboard.php' : (isManager() ? '/manager_dashboard.php' : '/user_dashboard.php')));
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $mobile = trim($_POST['mobile']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    $vehicle_type = $_POST['vehicle_type'] ?? 'Car';
    $vehicle_number = trim($_POST['vehicle_number'] ?? '');
    
    // Validation
    if (empty($name) || empty($email) || empty($mobile) || empty($password) || empty($vehicle_number)) {
        $error = "All fields are required.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters.";
    } else {
        // Check if email already exists
        $stmt = $pdo->prepare("SELECT user_id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->rowCount() > 0) {
            $error = "Email is already registered.";
        } else {
            // Hash password and insert
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            try {
                $stmt = $pdo->prepare("INSERT INTO users (name, email, mobile, password, role, vehicle_type, vehicle_number) VALUES (?, ?, ?, ?, 'user', ?, ?)");
                if ($stmt->execute([$name, $email, $mobile, $hashed_password, $vehicle_type, $vehicle_number])) {
                    $success = "Registration successful! You can now <a href='user_login.php' class='alert-link'>login here</a>.";
                } else {
                    $error = "Something went wrong. Please try again.";
                }
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
        <div class="col-md-8 col-lg-7">
            <div class="card-3d overflow-hidden">
                <!-- Gradient Header -->
                <div class="text-white text-center py-5 px-4 position-relative overflow-hidden" style="background: linear-gradient(135deg, var(--primary-color), var(--accent-color));">
                    <div class="position-absolute top-0 start-0 rounded-circle bg-white opacity-10" style="width: 200px; height: 200px; transform: translate(-50%,-50%);"></div>
                    <div class="position-absolute bottom-0 end-0 rounded-circle bg-white opacity-10" style="width: 150px; height: 150px; transform: translate(40%,40%);"></div>
                    <div class="bg-white bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3 border border-white border-opacity-25 shadow" style="width: 75px; height: 75px; position: relative; z-index: 1;">
                        <i class="fa-solid fa-user-plus fa-2x text-white"></i>
                    </div>
                    <h3 class="fw-bold mb-1" style="position: relative; z-index: 1; text-shadow: 0 2px 8px rgba(0,0,0,0.3);">Create Account</h3>
                    <p class="mb-0 opacity-75 small fw-medium" style="position: relative; z-index: 1;">Join ParkNova and start parking smarter.</p>
                </div>
                <div class="card-body p-4 p-md-5 bg-surface">
                    
                    <?php if (!empty($error)): ?>
                        <div class="alert glass-panel border-0 border-start border-4 border-danger rounded-4 mb-4 d-flex align-items-center gap-3">
                            <i class="fa-solid fa-circle-exclamation text-danger fs-4"></i> <span class="text-primary fw-medium"><?= $error ?></span>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($success)): ?>
                        <div class="alert glass-panel border-0 border-start border-4 border-success rounded-4 mb-4 d-flex align-items-center gap-3">
                            <i class="fa-solid fa-check-circle text-success fs-4"></i> <span class="text-primary fw-medium"><?= $success ?></span>
                        </div>
                    <?php endif; ?>

                    <form action="" method="POST" class="needs-validation" novalidate>
                        <h5 class="fw-bold text-primary mb-3 pb-2 border-bottom border-secondary border-opacity-25">1. Personal Information</h5>
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label text-secondary fw-bold small text-uppercase">Full Name</label>
                                <div class="input-group">
                                    <span class="input-group-text border-end-0 bg-transparent text-primary"><i class="fa-solid fa-user"></i></span>
                                    <input type="text" name="name" class="form-control border-start-0" placeholder="John Doe" required minlength="3" maxlength="50" pattern="[A-Za-z\s]+">
                                    <div class="invalid-feedback">Valid name required (letters only, min 3 chars).</div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label text-secondary fw-bold small text-uppercase">Email Address</label>
                                <div class="input-group">
                                    <span class="input-group-text border-end-0 bg-transparent text-primary"><i class="fa-solid fa-envelope"></i></span>
                                    <input type="email" name="email" class="form-control border-start-0" placeholder="name@example.com" required>
                                    <div class="invalid-feedback">Please provide a valid email address.</div>
                                </div>
                            </div>

                            <div class="col-md-12">
                                <label class="form-label text-secondary fw-bold small text-uppercase">Mobile Number</label>
                                <div class="input-group">
                                    <span class="input-group-text border-end-0 bg-transparent text-primary"><i class="fa-solid fa-phone"></i></span>
                                    <input type="tel" name="mobile" class="form-control border-start-0" placeholder="10-digit mobile number" required pattern="[0-9]{10}" maxlength="10">
                                    <div class="invalid-feedback">Please enter a valid 10-digit mobile number.</div>
                                </div>
                            </div>
                        </div>

                        <h5 class="fw-bold text-primary mb-3 mt-4 pb-2 border-bottom border-secondary border-opacity-25">2. Vehicle Details</h5>
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label text-secondary fw-bold small text-uppercase">Vehicle Type</label>
                                <div class="input-group">
                                    <span class="input-group-text border-end-0 bg-transparent text-primary"><i class="fa-solid fa-car-side"></i></span>
                                    <select name="vehicle_type" class="form-select border-start-0" required>
                                        <option value="Car">Car</option>
                                        <option value="Bike">Bike</option>
                                        <option value="EV">Electric Vehicle (EV)</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-secondary fw-bold small text-uppercase">Vehicle Number</label>
                                <div class="input-group">
                                    <span class="input-group-text border-end-0 bg-transparent text-primary"><i class="fa-solid fa-id-card"></i></span>
                                    <input type="text" name="vehicle_number" class="form-control border-start-0 text-uppercase" placeholder="GJ05AB1234" required>
                                    <div class="invalid-feedback">Vehicle number is required.</div>
                                </div>
                            </div>
                        </div>
                        
                        <h5 class="fw-bold text-primary mb-3 mt-4 pb-2 border-bottom border-secondary border-opacity-25">3. Security</h5>
                        <div class="row g-3 mb-5">
                            <div class="col-md-6">
                                <label class="form-label text-secondary fw-bold small text-uppercase">Password</label>
                                <div class="input-group">
                                    <span class="input-group-text border-end-0 bg-transparent text-primary"><i class="fa-solid fa-lock"></i></span>
                                    <input type="password" name="password" id="password" class="form-control border-start-0" placeholder="Min. 6 characters" required minlength="6">
                                    <div class="invalid-feedback">Password must be at least 6 characters long.</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-secondary fw-bold small text-uppercase">Confirm Password</label>
                                <div class="input-group">
                                    <span class="input-group-text border-end-0 bg-transparent text-primary"><i class="fa-solid fa-lock"></i></span>
                                    <input type="password" name="confirm_password" id="confirm_password" class="form-control border-start-0" placeholder="Repeat password" required>
                                    <div class="invalid-feedback">Passwords must match.</div>
                                </div>
                            </div>
                        </div>

                        <div class="d-grid mb-4">
                            <button type="submit" class="btn-primary-3d w-100 justify-content-center fs-6 py-3"><i class="fa-solid fa-user-plus me-2"></i>Create My Account</button>
                        </div>
                        
                        <div class="text-center text-secondary small border-top border-secondary border-opacity-25 pt-4">
                            Already have an account? <a href="user_login.php" class="text-primary fw-bold text-decoration-none">Login here</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>


<?php require_once __DIR__ . '/includes_footer.php'; ?>




