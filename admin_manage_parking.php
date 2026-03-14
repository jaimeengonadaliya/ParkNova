<?php
require_once __DIR__ . '/config_db.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect($base_url . '/user_login.php');
}

$success = '';
$error = '';

// Handle Add Parking
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {

    $parking_name   = trim($_POST['parking_name']);
    $address        = trim($_POST['address']);
    $city           = trim($_POST['city']);
    $parking_type   = $_POST['parking_type'] ?? 'Public';
    $total_slots    = (int)$_POST['total_slots'];
    $price_per_hour = (float)$_POST['price_per_hour'];
    $latitude       = trim($_POST['latitude'] ?? '');
    $longitude      = trim($_POST['longitude'] ?? '');
    $slot_type      = $_POST['slot_type'] ?? '4W';
    $auto_generate  = isset($_POST['auto_generate']);

    if ($_POST['action'] === 'add') {
        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare("INSERT INTO parking_locations (parking_name, address, city, parking_type, total_slots, price_per_hour, latitude, longitude, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'active')");
            $stmt->execute([$parking_name, $address, $city, $parking_type, $total_slots, $price_per_hour, $latitude, $longitude]);
            $parking_id = $pdo->lastInsertId();

            if ($auto_generate && $total_slots > 0) {
                $rows   = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H'];
                $col    = 1;
                $row_i  = 0;

                for ($s = 1; $s <= $total_slots; $s++) {
                    $slot_number = $rows[$row_i] . $col;
                    $stmt2 = $pdo->prepare("INSERT INTO parking_slots (parking_id, slot_number, slot_type, status) VALUES (?, ?, ?, 'available')");
                    $stmt2->execute([$parking_id, $slot_number, $slot_type]);
                    $col++;
                    if ($col > 5) { $col = 1; $row_i++; }
                    if ($row_i >= count($rows)) break;
                }
            }

            $pdo->commit();
            $success = "Parking location <strong>$parking_name</strong> added successfully" . ($auto_generate ? " with $total_slots auto-generated slots." : ".");
        } catch (PDOException $e) {
            $pdo->rollBack();
            $error = "Failed to add parking: " . $e->getMessage();
        }
    }

    elseif ($_POST['action'] === 'edit') {
        $parking_id      = (int)$_POST['parking_id'];
        $manager_id_val  = !empty($_POST['manager_id']) ? (int)$_POST['manager_id'] : null;
        $new_status      = $_POST['parking_status'] ?? 'active';
        try {
            $stmt = $pdo->prepare("UPDATE parking_locations SET parking_name=?, address=?, city=?, parking_type=?, total_slots=?, price_per_hour=?, latitude=?, longitude=?, manager_id=?, status=? WHERE parking_id=?");
            $stmt->execute([$parking_name, $address, $city, $parking_type, $total_slots, $price_per_hour, $latitude, $longitude, $manager_id_val, $new_status, $parking_id]);
            $success = "Parking location updated successfully.";
        } catch (PDOException $e) {
            $error = "Failed to update: " . $e->getMessage();
        }
    }
}

// Handle Delete
if (isset($_POST['delete_id'])) {
    $delete_id = (int)$_POST['delete_id'];
    try {
        $pdo->prepare("DELETE FROM parking_slots WHERE parking_id = ?")->execute([$delete_id]);
        $pdo->prepare("DELETE FROM parking_locations WHERE parking_id = ?")->execute([$delete_id]);
        $success = "Parking location and all its slots deleted.";
    } catch (PDOException $e) {
        $error = "Cannot delete: " . $e->getMessage();
    }
}

// Toggle Status
if (isset($_POST['toggle_id'])) {
    $toggle_id = (int)$_POST['toggle_id'];
    $stmt = $pdo->prepare("SELECT status FROM parking_locations WHERE parking_id = ?");
    $stmt->execute([$toggle_id]);
    $cur = $stmt->fetchColumn();
    $new = ($cur === 'active') ? 'inactive' : 'active';
    $pdo->prepare("UPDATE parking_locations SET status = ? WHERE parking_id = ?")->execute([$new, $toggle_id]);
    $success = "Parking location status changed to <strong>" . ucfirst($new) . "</strong>.";
}

// Fetch All Parking Locations
$stmt = $pdo->query("
    SELECT p.*, u.name AS manager_name, u.email AS manager_email,
           (SELECT COUNT(*) FROM parking_slots ps WHERE ps.parking_id = p.parking_id) AS slot_count,
           (SELECT COUNT(*) FROM parking_slots ps WHERE ps.parking_id = p.parking_id AND ps.status = 'available') AS available_count
    FROM parking_locations p
    LEFT JOIN users u ON p.manager_id = u.user_id
    ORDER BY p.created_date DESC
");
$parkings = $stmt->fetchAll();

// Fetch Managers
$all_managers = $pdo->query("SELECT user_id, name, email FROM users WHERE role = 'manager' ORDER BY name")->fetchAll();

require_once __DIR__ . '/includes_header.php';
require_once __DIR__ . '/includes_admin_navbar.php';
?>

<div class="container-fluid py-4 px-lg-5">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-5 gap-3">
        <div>
            <h3 class="fw-bold mb-1 text-primary"><i class="fa-solid fa-building me-2"></i>Parking Locations</h3>
            <p class="text-secondary small fw-medium mb-0">Add, configure, and manage all ParkNova locations.</p>
        </div>
        <button class="btn-primary-3d" data-bs-toggle="modal" data-bs-target="#addParkingModal">
            <i class="fa-solid fa-plus me-2"></i>Add New Parking
        </button>
    </div>

    <?php if (!empty($error)): ?>
        <div class="alert glass-panel border-start border-4 border-danger mb-4"><i class="fa-solid fa-triangle-exclamation me-2 text-danger"></i><?= $error ?></div>
    <?php endif; ?>
    <?php if (!empty($success)): ?>
        <div class="alert glass-panel border-start border-4 border-success mb-4"><i class="fa-solid fa-check-circle me-2 text-success"></i><?= $success ?></div>
    <?php endif; ?>

    <!-- Stats Bar -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="glass-panel p-3 text-center">
                <div class="fw-bold fs-3 text-primary"><?= count($parkings) ?></div>
                <div class="small text-secondary">Total Locations</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="glass-panel p-3 text-center">
                <div class="fw-bold fs-3 text-success"><?= count(array_filter($parkings, fn($p) => $p['status'] === 'active')) ?></div>
                <div class="small text-secondary">Active</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="glass-panel p-3 text-center">
                <div class="fw-bold fs-3 text-warning"><?= array_sum(array_column($parkings, 'slot_count')) ?></div>
                <div class="small text-secondary">Total Slots</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="glass-panel p-3 text-center">
                <div class="fw-bold fs-3 text-info"><?= count(array_filter($parkings, fn($p) => !$p['manager_name'])) ?></div>
                <div class="small text-secondary">Unassigned</div>
            </div>
        </div>
    </div>

    <div class="glass-panel overflow-hidden">
        <div class="p-4">
            <div class="table-responsive">
                <table class="table table-hover align-middle admin-datatable w-100">
                    <thead>
                        <tr>
                            <th class="py-3 text-muted small fw-bold text-uppercase">#</th>
                            <th class="py-3 text-muted small fw-bold text-uppercase">Facility</th>
                            <th class="py-3 text-muted small fw-bold text-uppercase">City / Type</th>
                            <th class="py-3 text-muted small fw-bold text-uppercase">Slots</th>
                            <th class="py-3 text-muted small fw-bold text-uppercase">Rate/Hr</th>
                            <th class="py-3 text-muted small fw-bold text-uppercase">Manager</th>
                            <th class="py-3 text-muted small fw-bold text-uppercase text-center">Status</th>
                            <th class="py-3 text-muted small fw-bold text-uppercase text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($parkings as $p): ?>
                        <tr>
                            <td class="fw-medium text-muted">#<?= $p['parking_id'] ?></td>
                            <td>
                                <div class="fw-bold text-primary"><i class="fa-solid fa-building me-2 opacity-50"></i><?= htmlspecialchars($p['parking_name']) ?></div>
                                <div class="small text-muted"><?= htmlspecialchars($p['address']) ?></div>
                            </td>
                            <td>
                                <div class="fw-medium"><?= htmlspecialchars($p['city']) ?></div>
                                <span class="badge rounded-pill bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-2 mt-1"><?= $p['parking_type'] ?></span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill px-2"><?= $p['available_count'] ?> free</span>
                                    <span class="text-muted small">/ <?= $p['slot_count'] ?></span>
                                </div>
                                <a href="manage_slots.php?parking_id=<?= $p['parking_id'] ?>" class="btn btn-sm btn-outline-secondary rounded-pill px-2 py-0 mt-1" style="font-size:0.7rem">
                                    <i class="fa-solid fa-grip me-1"></i>Slots
                                </a>
                            </td>
                            <td class="fw-bold text-success">₹<?= number_format($p['price_per_hour'], 0) ?></td>
                            <td>
                                <?php if ($p['manager_name']): ?>
                                    <div class="fw-bold small"><?= htmlspecialchars($p['manager_name']) ?></div>
                                    <div class="small text-muted"><?= htmlspecialchars($p['manager_email']) ?></div>
                                <?php else: ?>
                                    <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 rounded-pill px-2">Unassigned</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <span class="badge rounded-pill px-3 py-2 <?= $p['status'] === 'active' ? 'bg-success bg-opacity-15 text-success border border-success border-opacity-25' : 'bg-secondary bg-opacity-15 text-secondary border border-secondary border-opacity-25' ?>">
                                    <?= ucfirst($p['status']) ?>
                                </span>
                            </td>
                            <td class="text-center">
                                <div class="d-flex justify-content-center gap-1">
                                    <button class="btn btn-sm btn-light text-primary rounded-circle p-2 edit-btn"
                                            data-bs-toggle="modal" data-bs-target="#editParkingModal"
                                            data-id="<?= $p['parking_id'] ?>"
                                            data-name="<?= htmlspecialchars($p['parking_name'], ENT_QUOTES) ?>"
                                            data-address="<?= htmlspecialchars($p['address'], ENT_QUOTES) ?>"
                                            data-city="<?= htmlspecialchars($p['city'], ENT_QUOTES) ?>"
                                            data-type="<?= $p['parking_type'] ?>"
                                            data-slots="<?= $p['total_slots'] ?>"
                                            data-price="<?= $p['price_per_hour'] ?>"
                                            data-lat="<?= $p['latitude'] ?>"
                                            data-lng="<?= $p['longitude'] ?>"
                                            data-manager="<?= $p['manager_id'] ?>"
                                            data-status="<?= $p['status'] ?>" title="Edit">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </button>
                                    <form method="POST" class="d-inline">
                                        <input type="hidden" name="toggle_id" value="<?= $p['parking_id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-light rounded-circle p-2 <?= $p['status'] === 'active' ? 'text-warning' : 'text-success' ?>" title="<?= $p['status'] === 'active' ? 'Deactivate' : 'Activate' ?>">
                                            <i class="fa-solid <?= $p['status'] === 'active' ? 'fa-toggle-off' : 'fa-toggle-on' ?>"></i>
                                        </button>
                                    </form>
                                    <form method="POST" class="d-inline" onsubmit="return confirm('Delete <?= htmlspecialchars($p['parking_name'], ENT_QUOTES) ?> and ALL its slots? This cannot be undone.')">
                                        <input type="hidden" name="delete_id" value="<?= $p['parking_id'] ?>">
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

<!-- ─── ADD MODAL ─── -->
<div class="modal fade" id="addParkingModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 rounded-4 shadow-lg overflow-hidden glass-panel">
            <div class="modal-header border-bottom border-secondary border-opacity-25 p-4" style="background: linear-gradient(135deg, rgba(67,97,238,0.12), rgba(109,40,217,0.12));">
                <h5 class="modal-title fw-bold text-primary"><i class="fa-solid fa-plus-circle me-2"></i>Add New Parking Location</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="" method="POST" class="needs-validation" novalidate>
                <div class="modal-body p-4">
                    <input type="hidden" name="action" value="add">

                    <!-- Row 1: Name + Type -->
                    <div class="row g-3 mb-3">
                        <div class="col-md-8">
                            <label class="form-label text-secondary fw-bold small text-uppercase">Parking / Facility Name <span class="text-danger">*</span></label>
                            <input type="text" name="parking_name" class="form-control bg-surface border-secondary border-opacity-25" required minlength="3" placeholder="e.g. Surat City Mall Parking">
                            <div class="invalid-feedback">Minimum 3 characters required.</div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-secondary fw-bold small text-uppercase">Parking Type <span class="text-danger">*</span></label>
                            <select name="parking_type" class="form-select bg-surface border-secondary border-opacity-25" required>
                                <option value="Public">Public</option>
                                <option value="Mall">Mall</option>
                                <option value="Private">Private</option>
                            </select>
                        </div>
                    </div>

                    <!-- Row 2: Address + City -->
                    <div class="row g-3 mb-3">
                        <div class="col-md-8">
                            <label class="form-label text-secondary fw-bold small text-uppercase">Street Address <span class="text-danger">*</span></label>
                            <input type="text" name="address" class="form-control bg-surface border-secondary border-opacity-25" required minlength="5" placeholder="e.g. Ring Road, Surat">
                            <div class="invalid-feedback">A detailed address is required.</div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-secondary fw-bold small text-uppercase">City <span class="text-danger">*</span></label>
                            <input type="text" name="city" class="form-control bg-surface border-secondary border-opacity-25" required placeholder="e.g. Surat">
                            <div class="invalid-feedback">City name required.</div>
                        </div>
                    </div>

                    <!-- Row 3: Lat/Lng -->
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label text-secondary fw-bold small text-uppercase">Latitude</label>
                            <input type="text" name="latitude" class="form-control bg-surface border-secondary border-opacity-25" placeholder="e.g. 21.1702">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-secondary fw-bold small text-uppercase">Longitude</label>
                            <input type="text" name="longitude" class="form-control bg-surface border-secondary border-opacity-25" placeholder="e.g. 72.8311">
                        </div>
                    </div>

                    <!-- Row 4: Capacity + Price -->
                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label text-secondary fw-bold small text-uppercase">Total Capacity <span class="text-danger">*</span></label>
                            <input type="number" name="total_slots" class="form-control bg-surface border-secondary border-opacity-25" required min="1" max="500" placeholder="e.g. 50">
                            <div class="invalid-feedback">Enter a capacity between 1-500.</div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-secondary fw-bold small text-uppercase">Slot Type</label>
                            <select name="slot_type" class="form-select bg-surface border-secondary border-opacity-25">
                                <option value="4W">4-Wheeler (Car)</option>
                                <option value="2W">2-Wheeler (Bike)</option>
                                <option value="EV">EV Charging</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-secondary fw-bold small text-uppercase">Price Rate (₹/hr) <span class="text-danger">*</span></label>
                            <input type="number" name="price_per_hour" class="form-control bg-surface border-secondary border-opacity-25 fw-bold" required min="1" step="0.01" placeholder="e.g. 40">
                            <div class="invalid-feedback">Enter a valid price.</div>
                        </div>
                    </div>

                    <!-- Auto-Generate Slots -->
                    <div class="glass-panel p-3 rounded-3 border border-success border-opacity-25">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="auto_generate" id="autoGenSlots" checked>
                            <label class="form-check-label fw-bold text-success" for="autoGenSlots">
                                <i class="fa-solid fa-wand-magic-sparkles me-2"></i>Auto-Generate Slots (A1–A5, B1–B5…)
                            </label>
                        </div>
                        <small class="text-muted d-block mt-1 ms-4">Slots will be named automatically based on the capacity entered above.</small>
                    </div>
                </div>
                <div class="modal-footer border-top border-secondary border-opacity-25 p-4 bg-light bg-opacity-50">
                    <button type="button" class="btn btn-light rounded-pill px-4 fw-bold text-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn-primary-3d px-5"><i class="fa-solid fa-save me-2"></i>Create Parking</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ─── EDIT MODAL ─── -->
<div class="modal fade" id="editParkingModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 rounded-4 shadow-lg overflow-hidden glass-panel">
            <div class="modal-header border-bottom border-secondary border-opacity-25 p-4" style="background: linear-gradient(135deg, rgba(67,97,238,0.12), rgba(109,40,217,0.12));">
                <h5 class="modal-title fw-bold text-primary"><i class="fa-solid fa-pen-to-square me-2"></i>Edit Parking Location</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="" method="POST" class="needs-validation" novalidate>
                <div class="modal-body p-4">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="parking_id" id="edit_id">

                    <div class="row g-3 mb-3">
                        <div class="col-md-8">
                            <label class="form-label text-secondary fw-bold small text-uppercase">Facility Name</label>
                            <input type="text" name="parking_name" id="edit_name" class="form-control bg-surface border-secondary border-opacity-25" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-secondary fw-bold small text-uppercase">Parking Type</label>
                            <select name="parking_type" id="edit_type" class="form-select bg-surface border-secondary border-opacity-25">
                                <option value="Public">Public</option>
                                <option value="Mall">Mall</option>
                                <option value="Private">Private</option>
                            </select>
                        </div>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-8">
                            <label class="form-label text-secondary fw-bold small text-uppercase">Street Address</label>
                            <input type="text" name="address" id="edit_address" class="form-control bg-surface border-secondary border-opacity-25" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-secondary fw-bold small text-uppercase">City</label>
                            <input type="text" name="city" id="edit_city" class="form-control bg-surface border-secondary border-opacity-25" required>
                        </div>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label text-secondary fw-bold small text-uppercase">Latitude</label>
                            <input type="text" name="latitude" id="edit_lat" class="form-control bg-surface border-secondary border-opacity-25">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-secondary fw-bold small text-uppercase">Longitude</label>
                            <input type="text" name="longitude" id="edit_lng" class="form-control bg-surface border-secondary border-opacity-25">
                        </div>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label text-secondary fw-bold small text-uppercase">Total Capacity</label>
                            <input type="number" name="total_slots" id="edit_slots" class="form-control bg-surface border-secondary border-opacity-25" required min="1">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-secondary fw-bold small text-uppercase">Price Rate (₹/hr)</label>
                            <input type="number" name="price_per_hour" id="edit_price" class="form-control bg-surface border-secondary border-opacity-25 fw-bold" required min="1" step="0.01">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-secondary fw-bold small text-uppercase">Parking Status</label>
                            <select name="parking_status" id="edit_status" class="form-select bg-surface border-secondary border-opacity-25">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive (Closed)</option>
                            </select>
                        </div>
                    </div>

                    <!-- Assign Manager -->
                    <div class="border-top border-secondary border-opacity-25 pt-4 mt-2">
                        <label class="form-label text-secondary fw-bold small text-uppercase"><i class="fa-solid fa-user-tie text-warning me-2"></i>Assign Manager</label>
                        <select name="manager_id" id="edit_manager" class="form-select bg-surface border-warning border-opacity-25">
                            <option value="">-- No Manager --</option>
                            <?php foreach ($all_managers as $am): ?>
                                <option value="<?= $am['user_id'] ?>"><?= htmlspecialchars($am['name']) ?> (<?= htmlspecialchars($am['email']) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-top border-secondary border-opacity-25 p-4 bg-light bg-opacity-50">
                    <button type="button" class="btn btn-light rounded-pill px-4 fw-bold text-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn-primary-3d px-5"><i class="fa-solid fa-cloud-arrow-up me-2"></i>Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.edit-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            document.getElementById('edit_id').value       = this.dataset.id;
            document.getElementById('edit_name').value     = this.dataset.name;
            document.getElementById('edit_address').value  = this.dataset.address;
            document.getElementById('edit_city').value     = this.dataset.city;
            document.getElementById('edit_type').value     = this.dataset.type;
            document.getElementById('edit_slots').value    = this.dataset.slots;
            document.getElementById('edit_price').value    = this.dataset.price;
            document.getElementById('edit_lat').value      = this.dataset.lat;
            document.getElementById('edit_lng').value      = this.dataset.lng;
            document.getElementById('edit_manager').value  = this.dataset.manager || '';
            document.getElementById('edit_status').value   = this.dataset.status;
        });
    });
});
</script>

<?php
echo '<script src="' . $base_url . '/js_admin.js"></script>';
require_once __DIR__ . '/includes_footer.php';
?>



