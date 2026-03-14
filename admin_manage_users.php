<?php
require_once __DIR__ . '/config_db.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect($base_url . '/user_login.php');
}

$success = ''; $error = '';

// --- Create Manager ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create_manager') {
    $name     = trim($_POST['mgr_name']);
    $email    = trim($_POST['mgr_email']);
    $mobile   = trim($_POST['mgr_mobile']);
    $password = $_POST['mgr_password'];
    $assign_p = !empty($_POST['mgr_parking']) ? (int)$_POST['mgr_parking'] : null;

    if (empty($name) || empty($email) || empty($password)) {
        $error = "Name, email, and password are required.";
    } else {
        $check = $pdo->prepare("SELECT user_id FROM users WHERE email = ?");
        $check->execute([$email]);
        if ($check->rowCount() > 0) {
            $error = "A user with this email already exists.";
        } else {
            try {
                $pdo->beginTransaction();
                $hash = password_hash($password, PASSWORD_BCRYPT);
                $stmt = $pdo->prepare("INSERT INTO users (name, email, mobile, password, role, status) VALUES (?, ?, ?, ?, 'manager', 'active')");
                $stmt->execute([$name, $email, $mobile, $hash]);
                $mgr_id = $pdo->lastInsertId();

                if ($assign_p) {
                    $pdo->prepare("UPDATE parking_locations SET manager_id = ? WHERE parking_id = ?")->execute([$mgr_id, $assign_p]);
                }
                $pdo->commit();
                $success = "Manager <strong>$name</strong> created successfully" . ($assign_p ? " and assigned to parking." : ".");
            } catch (PDOException $e) {
                $pdo->rollBack();
                $error = "Failed: " . $e->getMessage();
            }
        }
    }
}

// --- Toggle User Status ---
if (isset($_POST['toggle_user_id'])) {
    $uid = (int)$_POST['toggle_user_id'];
    $stmt = $pdo->prepare("SELECT status FROM users WHERE user_id = ?");
    $stmt->execute([$uid]);
    $cur  = $stmt->fetchColumn();
    $new  = ($cur === 'active') ? 'inactive' : 'active';
    $pdo->prepare("UPDATE users SET status = ? WHERE user_id = ?")->execute([$new, $uid]);
    $success = "User status changed to <strong>" . ucfirst($new) . "</strong>.";
}

// --- Delete User ---
if (isset($_POST['delete_id'])) {
    $del = (int)$_POST['delete_id'];
    $check = $pdo->prepare("SELECT role FROM users WHERE user_id = ?");
    $check->execute([$del]);
    $role_del = $check->fetchColumn();
    if ($role_del === 'super_admin') {
        $error = "Cannot delete the Super Admin account.";
    } elseif ($del === (int)$_SESSION['user_id']) {
        $error = "You cannot delete your own account.";
    } else {
        try {
            // Release their parking if manager
            $pdo->prepare("UPDATE parking_locations SET manager_id = NULL WHERE manager_id = ?")->execute([$del]);
            $pdo->prepare("DELETE FROM users WHERE user_id = ?")->execute([$del]);
            $success = "Account deleted successfully.";
        } catch (PDOException $e) {
            $error = "Cannot delete: active bookings may exist.";
        }
    }
}

// Fetch Managers with Parking info
$managers = $pdo->query("
    SELECT u.*, p.parking_name, p.city, p.parking_id AS assigned_parking_id
    FROM users u
    LEFT JOIN parking_locations p ON p.manager_id = u.user_id
    WHERE u.role = 'manager'
    ORDER BY u.name
")->fetchAll();

// Fetch Regular Users
$users = $pdo->query("SELECT * FROM users WHERE role = 'user' ORDER BY created_at DESC")->fetchAll();

// Fetch unassigned parkings (for assignment dropdown)
$free_parkings = $pdo->query("SELECT parking_id, parking_name, city FROM parking_locations WHERE manager_id IS NULL ORDER BY city")->fetchAll();
$all_parkings  = $pdo->query("SELECT parking_id, parking_name, city FROM parking_locations ORDER BY city")->fetchAll();

require_once __DIR__ . '/includes_header.php';
require_once __DIR__ . '/includes_admin_navbar.php';
?>

<div class="container-fluid py-4 px-lg-5">
    <div class="d-flex flex-wrap justify-content-between align-items-start mb-5 gap-3">
        <div>
            <h3 class="fw-bold text-primary mb-1"><i class="fa-solid fa-users me-2"></i>Users & Managers</h3>
            <p class="text-secondary small mb-0">Create managers, assign parking lots, and manage registered drivers.</p>
        </div>
        <button class="btn-primary-3d" data-bs-toggle="modal" data-bs-target="#createManagerModal">
            <i class="fa-solid fa-user-tie me-2"></i>Create New Manager
        </button>
    </div>

    <?php if (!empty($error)): ?>
        <div class="alert glass-panel border-start border-4 border-danger mb-4"><i class="fa-solid fa-triangle-exclamation me-2 text-danger"></i><?= $error ?></div>
    <?php endif; ?>
    <?php if (!empty($success)): ?>
        <div class="alert glass-panel border-start border-4 border-success mb-4"><i class="fa-solid fa-check-circle me-2 text-success"></i><?= $success ?></div>
    <?php endif; ?>

    <!-- ── MANAGERS ── -->
    <div class="mb-5">
        <h5 class="fw-bold text-primary mb-4">
            <i class="fa-solid fa-user-tie me-2"></i>Parking Managers
            <span class="badge bg-warning text-dark rounded-pill ms-2"><?= count($managers) ?></span>
        </h5>
        <?php if (empty($managers)): ?>
            <div class="glass-panel p-5 text-center text-secondary">
                <i class="fa-solid fa-user-tie fa-3x opacity-25 d-block mb-3"></i>
                No managers yet. Click "Create New Manager" above.
            </div>
        <?php else: ?>
        <div class="row g-3">
            <?php foreach ($managers as $m): ?>
            <div class="col-md-6 col-xl-4">
                <div class="card-3d p-4 h-100 position-relative overflow-hidden">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="bg-warning bg-opacity-15 border border-warning border-opacity-25 rounded-circle d-flex align-items-center justify-content-center fw-bold text-warning flex-shrink-0" style="width:52px;height:52px;font-size:1.3rem;">
                            <?= strtoupper(substr($m['name'], 0, 1)) ?>
                        </div>
                        <div class="overflow-hidden flex-grow-1">
                            <div class="fw-bold text-primary text-truncate"><?= htmlspecialchars($m['name']) ?></div>
                            <div class="small text-secondary text-truncate"><?= htmlspecialchars($m['email']) ?></div>
                            <?php if ($m['mobile']): ?><div class="small text-muted"><?= htmlspecialchars($m['mobile']) ?></div><?php endif; ?>
                        </div>
                        <span class="badge rounded-pill px-2 py-1 <?= $m['status'] === 'active' ? 'bg-success bg-opacity-15 text-success border border-success border-opacity-25' : 'bg-secondary bg-opacity-15 text-secondary border border-secondary border-opacity-25' ?>" style="font-size:0.65rem;"><?= ucfirst($m['status']) ?></span>
                    </div>

                    <?php if ($m['parking_name']): ?>
                        <div class="glass-panel p-2 px-3 rounded-3 text-center mb-3">
                            <div class="small fw-bold text-primary"><i class="fa-solid fa-building me-1"></i><?= htmlspecialchars($m['parking_name']) ?></div>
                            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 rounded-pill px-2 mt-1"><?= htmlspecialchars($m['city']) ?></span>
                        </div>
                    <?php else: ?>
                        <div class="text-center p-2 mb-3">
                            <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 rounded-pill px-3 py-2"><i class="fa-solid fa-exclamation-circle me-1"></i>No Parking Assigned</span>
                        </div>
                    <?php endif; ?>

                    <div class="d-flex gap-2">
                        <a href="<?= $base_url ?>/admin_manage_parking.php" class="btn btn-sm btn-outline-primary rounded-pill flex-grow-1 fw-medium">
                            <i class="fa-solid fa-pen-to-square me-1"></i>Assign Parking
                        </a>
                        <form method="POST" class="d-inline">
                            <input type="hidden" name="toggle_user_id" value="<?= $m['user_id'] ?>">
                            <button type="submit" class="btn btn-sm <?= $m['status'] === 'active' ? 'btn-outline-warning' : 'btn-outline-success' ?> rounded-pill fw-medium px-2" title="<?= $m['status'] === 'active' ? 'Deactivate' : 'Activate' ?>">
                                <i class="fa-solid <?= $m['status'] === 'active' ? 'fa-user-slash' : 'fa-user-check' ?>"></i>
                            </button>
                        </form>
                        <form method="POST" class="d-inline" onsubmit="return confirm('Delete this manager?')">
                            <input type="hidden" name="delete_id" value="<?= $m['user_id'] ?>">
                            <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-2" title="Delete">
                                <i class="fa-solid fa-trash-can"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>

    <!-- ── REGISTERED USERS ── -->
    <h5 class="fw-bold text-primary mb-4">
        <i class="fa-solid fa-users me-2"></i>Registered Drivers
        <span class="badge bg-primary rounded-pill ms-2"><?= count($users) ?></span>
    </h5>
    <div class="glass-panel overflow-hidden">
        <div class="p-4">
            <div class="table-responsive">
                <table class="table table-hover align-middle admin-datatable w-100">
                    <thead>
                        <tr>
                            <th class="py-3 text-muted small fw-bold text-uppercase">#</th>
                            <th class="py-3 text-muted small fw-bold text-uppercase">Name</th>
                            <th class="py-3 text-muted small fw-bold text-uppercase">Email</th>
                            <th class="py-3 text-muted small fw-bold text-uppercase">Mobile</th>
                            <th class="py-3 text-muted small fw-bold text-uppercase">Joined</th>
                            <th class="py-3 text-muted small fw-bold text-uppercase text-center">Status</th>
                            <th class="py-3 text-muted small fw-bold text-uppercase text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $u): ?>
                        <tr>
                            <td class="text-muted fw-bold">#<?= $u['user_id'] ?></td>
                            <td class="fw-bold"><?= htmlspecialchars($u['name']) ?></td>
                            <td class="text-muted"><?= htmlspecialchars($u['email']) ?></td>
                            <td class="text-muted"><?= htmlspecialchars($u['mobile'] ?? '—') ?></td>
                            <td class="text-muted small"><?= date('M d, Y', strtotime($u['created_at'])) ?></td>
                            <td class="text-center">
                                <span class="badge rounded-pill px-2 <?= ($u['status'] ?? 'active') === 'active' ? 'bg-success bg-opacity-15 text-success border border-success border-opacity-25' : 'bg-secondary bg-opacity-15 text-secondary' ?>">
                                    <?= ucfirst($u['status'] ?? 'active') ?>
                                </span>
                            </td>
                            <td class="text-center">
                                <div class="d-flex justify-content-center gap-1">
                                    <form method="POST" class="d-inline">
                                        <input type="hidden" name="toggle_user_id" value="<?= $u['user_id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-light rounded-circle p-2 <?= ($u['status'] ?? 'active') === 'active' ? 'text-warning' : 'text-success' ?>" title="Toggle Status">
                                            <i class="fa-solid <?= ($u['status'] ?? 'active') === 'active' ? 'fa-user-slash' : 'fa-user-check' ?>"></i>
                                        </button>
                                    </form>
                                    <form method="POST" class="d-inline" onsubmit="return confirm('Permanently delete this user?')">
                                        <input type="hidden" name="delete_id" value="<?= $u['user_id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-light text-danger rounded-circle p-2" title="Delete">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- ─── CREATE MANAGER MODAL ─── -->
<div class="modal fade" id="createManagerModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 rounded-4 shadow-lg overflow-hidden glass-panel">
            <div class="modal-header border-bottom border-secondary border-opacity-25 p-4" style="background: linear-gradient(135deg, rgba(245,158,11,0.12), rgba(217,119,6,0.12));">
                <h5 class="modal-title fw-bold text-warning"><i class="fa-solid fa-user-tie me-2"></i>Create New Manager</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="" method="POST" class="needs-validation" novalidate>
                <div class="modal-body p-4">
                    <input type="hidden" name="action" value="create_manager">
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label text-secondary fw-bold small text-uppercase">Full Name <span class="text-danger">*</span></label>
                            <input type="text" name="mgr_name" class="form-control bg-surface" required minlength="3" placeholder="e.g. Rahul Patel">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-secondary fw-bold small text-uppercase">Email Address <span class="text-danger">*</span></label>
                            <input type="email" name="mgr_email" class="form-control bg-surface" required placeholder="e.g. rahul@jaimeengondaliya.com">
                        </div>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label text-secondary fw-bold small text-uppercase">Mobile Number</label>
                            <input type="tel" name="mgr_mobile" class="form-control bg-surface" placeholder="10-digit mobile" pattern="[0-9]{10}" maxlength="10">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-secondary fw-bold small text-uppercase">Password <span class="text-danger">*</span></label>
                            <input type="password" name="mgr_password" class="form-control bg-surface" required minlength="6" placeholder="Min 6 characters">
                        </div>
                    </div>
                    <div class="mb-3 border-top border-secondary border-opacity-25 pt-3">
                        <label class="form-label text-secondary fw-bold small text-uppercase"><i class="fa-solid fa-building text-primary me-2"></i>Assign Parking Location (optional)</label>
                        <select name="mgr_parking" class="form-select bg-surface border-primary border-opacity-25">
                            <option value="">-- Assign Later --</option>
                            <?php foreach ($all_parkings as $ap): ?>
                                <option value="<?= $ap['parking_id'] ?>"><?= htmlspecialchars($ap['parking_name']) ?> — <?= htmlspecialchars($ap['city']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-top border-secondary border-opacity-25 p-4">
                    <button type="button" class="btn btn-light rounded-pill px-4 text-secondary fw-bold" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn-primary-3d px-5"><i class="fa-solid fa-user-plus me-2"></i>Create Manager</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
echo '<script src="' . $base_url . '/js_admin.js"></script>';
require_once __DIR__ . '/includes_footer.php';
?>



