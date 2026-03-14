<?php
require_once __DIR__ . '/config_db.php';

// Redirect if already logged in
if (isLoggedIn()) {
    $role = $_SESSION['role'] ?? 'user';
    if ($role === 'super_admin') redirect($base_url . '/admin_dashboard.php');
    elseif ($role === 'manager') redirect($base_url . '/manager_dashboard.php');
    else redirect($base_url . '/user_dashboard.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
    if (empty($email) || empty($password)) {
        $error = "Please enter both email and password.";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        
        if ($stmt->rowCount() > 0) {
            $user = $stmt->fetch();
            
            if (password_verify($password, $user['password'])) {
                // Set session variables
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['role']    = $user['role'];
                $_SESSION['name']    = $user['name'];
                $_SESSION['email']   = $user['email'];
                
                // Role-based redirect
                if ($user['role'] === 'super_admin') {
                    redirect($base_url . '/admin_dashboard.php');
                } elseif ($user['role'] === 'manager') {
                    redirect($base_url . '/manager_dashboard.php');
                } else {
                    redirect($base_url . '/user_dashboard.php');
                }
            } else {
                $error = "Invalid password. Please try again.";
            }
        } else {
            $error = "No account found with that email address.";
        }
    }
}

require_once __DIR__ . '/includes_header.php';
require_once __DIR__ . '/includes_navbar.php';
?>

<div class="container py-4" style="min-height: calc(100vh - 180px); display: flex; align-items: center;">
    <div class="row w-100 justify-content-center">
        <div class="col-md-7 col-lg-5">
            <div class="card-3d overflow-hidden">
                <!-- Gradient Header -->
                <div class="text-white text-center py-4 px-4 position-relative overflow-hidden" style="background: linear-gradient(135deg, var(--primary-color), #6d28d9);">
                    <div class="position-absolute top-0 start-0 rounded-circle bg-white opacity-10" style="width: 200px; height: 200px; transform: translate(-50%,-50%);"></div>
                    <div class="position-absolute bottom-0 end-0 rounded-circle bg-white opacity-10" style="width: 150px; height: 150px; transform: translate(40%,40%);"></div>
                    <div class="bg-white bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3 border border-white border-opacity-25 shadow" style="width: 75px; height: 75px; position: relative; z-index: 1;">
                        <i class="fa-solid fa-square-parking fa-2x text-white"></i>
                    </div>
                    <h3 class="fw-bold mb-1" style="position: relative; z-index: 1; text-shadow: 0 2px 8px rgba(0,0,0,0.3);">Welcome Back</h3>
                    <p class="mb-0 opacity-75 small fw-medium" style="position: relative; z-index: 1;">Sign in to your ParkNova account.</p>
                </div>
                <div class="card-body p-4 p-md-4 bg-surface">
                    
                    <?php if (!empty($error)): ?>
                        <div class="alert glass-panel border-0 border-start border-4 border-danger rounded-4 mb-4 shadow-sm d-flex align-items-center gap-3">
                            <i class="fa-solid fa-circle-exclamation text-danger fs-4"></i> <span class="text-primary fw-medium"><?= $error ?></span>
                        </div>
                    <?php endif; ?>


                    <form action="" method="POST" class="needs-validation" novalidate>
                        <div class="mb-4">
                            <label class="form-label text-secondary fw-bold small text-uppercase">Email Address</label>
                            <div class="input-group flex-nowrap">
                                <span class="input-group-text border-end-0 bg-transparent text-primary"><i class="fa-solid fa-envelope"></i></span>
                                <input type="email" name="email" class="form-control border-start-0" placeholder="name@example.com" required>
                                <div class="invalid-feedback">Please provide a registered email address.</div>
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <label class="form-label text-secondary fw-bold small text-uppercase">Password</label>
                            <div class="input-group flex-nowrap">
                                <span class="input-group-text border-end-0 bg-transparent text-primary"><i class="fa-solid fa-lock"></i></span>
                                <input type="password" name="password" class="form-control border-start-0" placeholder="Enter your password" required>
                                <div class="invalid-feedback">Password is required.</div>
                            </div>
                        </div>
                        
                        <div class="mb-4 form-check mt-3">
                            <input type="checkbox" class="form-check-input" id="rememberMe">
                            <label class="form-check-label small text-secondary fw-medium" for="rememberMe">Remember me for 30 days</label>
                        </div>
                        
                        <div class="d-grid mb-4">
                            <button type="submit" class="btn-primary-3d w-100 justify-content-center fs-6 py-3"><i class="fa-solid fa-right-to-bracket me-2"></i>Login Securely</button>
                        </div>
                        
                        <div class="text-center text-secondary small border-top border-secondary border-opacity-25 pt-4">
                            Don't have an account? <a href="user_register.php" class="text-primary fw-bold text-decoration-none">Create one free</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes_footer.php'; ?>



