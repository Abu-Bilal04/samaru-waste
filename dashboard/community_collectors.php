<?php
// dashboard/community_collectors.php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit;
}
require_once '../config/db.php';
require_once '../config/roles.php';

// Handle Form Submission
$message = "";
$msgType = "info"; // success or danger

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name']);
    $phone = sanitize($_POST['phone']);
    $address = sanitize($_POST['address']);
    $newId = "CC" . rand(100, 999);

    $stmt = $pdo->prepare("INSERT INTO users (user_unique_id, name, phone, address, role_level) VALUES (?, ?, ?, ?, ?)");
    try {
        $stmt->execute([$newId, $name, $phone, $address, ROLE_COMMUNITY_COLLECTOR]);
        $message = "Success! New Community Collector created with ID: <strong>$newId</strong>";
        $msgType = "success";
    } catch (Exception $e) {
        $message = "Error: " . $e->getMessage();
        $msgType = "danger";
    }
}

// Layout Config
$pageTitle = "Community Managers";
$activePage = "community";
require_once 'includes/header.php';
?>

<div class="page-header">
    <div>
        <h2 class="page-title">Community Managers</h2>
        <p class="text-muted mb-0">Manage the supervisors who oversee zones and verification.</p>
    </div>
</div>

<div class="row">
    <!-- Registration Form -->
    <div class="col-lg-4 mb-4">
        <div class="content-card h-100">
            <h5 class="fw-bold mb-4"><i class="bi bi-person-plus-fill text-success me-2"></i> Register New Manager</h5>
            
            <?php if($message): ?>
                <div class="alert alert-<?php echo $msgType; ?>"><?php echo $message; ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="mb-3">
                    <label class="form-label text-muted small fw-bold">FULL NAME</label>
                    <input type="text" name="name" class="form-control" placeholder="e.g. Sarah Connor" required>
                </div>
                <div class="mb-3">
                    <label class="form-label text-muted small fw-bold">PHONE NUMBER</label>
                    <input type="text" name="phone" class="form-control" placeholder="e.g. 08012345678" required>
                </div>
                <div class="mb-3">
                    <label class="form-label text-muted small fw-bold">ZONE / ADDRESS</label>
                    <input type="text" name="address" class="form-control" placeholder="e.g. Zone A, Main Market" required>
                </div>
                <div class="d-grid mt-4">
                    <button type="submit" class="btn btn-primary">Create Account</button>
                </div>
            </form>
        </div>
    </div>

    <!-- List -->
    <div class="col-lg-8">
        <div class="content-card">
            <h5 class="fw-bold mb-4">Registered Managers</h5>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Manager ID</th>
                            <th>Name</th>
                            <th>Contact</th>
                            <th>Zone</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $stm = $pdo->prepare("SELECT * FROM users WHERE role_level = ? ORDER BY created_at DESC");
                        $stm->execute([ROLE_COMMUNITY_COLLECTOR]);
                        $rows = $stm->fetchAll();

                        if(count($rows) > 0) {
                            foreach($rows as $row) {
                                echo "<tr>";
                                echo "<td><span class='badge bg-light text-dark border'>{$row['user_unique_id']}</span></td>";
                                echo "<td class='fw-medium'>{$row['name']}</td>";
                                echo "<td class='text-muted'>{$row['phone']}</td>";
                                echo "<td>{$row['address']}</td>";
                                echo "<td><span class='badge bg-success bg-opacity-10 text-success'>Active</span></td>";
                                echo "</tr>";
                            }
                        } else {
                            echo '<tr><td colspan="5" class="text-center py-5 text-muted">No managers found.</td></tr>';
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
