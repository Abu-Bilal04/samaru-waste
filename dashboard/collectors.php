<?php
// dashboard/collectors.php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit;
}
require_once '../config/db.php';
require_once '../config/roles.php';

// Layout Config
$pageTitle = "Waste Collectors";
$activePage = "collectors";
require_once 'includes/header.php';
?>

<div class="page-header">
    <div>
        <h2 class="page-title">Waste Collectors</h2>
        <p class="text-muted mb-0">Registered field collectors gathering waste from house-to-house.</p>
    </div>
    <!-- Optional Action Button -->
    <a href="community_collectors.php" class="btn btn-outline-secondary btn-sm"><i class="bi bi-people"></i> Manage Managers</a>
</div>

<div class="content-card">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h5 class="fw-bold m-0">Collector Directory</h5>
        <div class="input-group" style="max-width: 300px;">
            <span class="input-group-text bg-white border-end-0"><i class="bi bi-search"></i></span>
            <input type="text" class="form-control border-start-0" placeholder="Search collectors...">
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Collector ID</th>
                    <th>Name</th>
                    <th>Phone</th>
                    <th>Zone/Address</th>
                    <th>Joined Date</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $stmt = $pdo->prepare("SELECT * FROM users WHERE role_level = ? ORDER BY created_at DESC");
                $stmt->execute([ROLE_WASTE_COLLECTOR]);
                $rows = $stmt->fetchAll();
                
                if(count($rows) > 0) {
                    foreach($rows as $row) {
                        echo "<tr>";
                        echo "<td><span class='badge bg-light text-dark border'>{$row['user_unique_id']}</span></td>";
                        echo "<td class='fw-medium'>{$row['name']}</td>";
                        echo "<td class='text-muted'>{$row['phone']}</td>";
                        echo "<td>{$row['address']}</td>";
                        echo "<td>" . date('M j, Y', strtotime($row['created_at'])) . "</td>";
                        echo "<td><span class='badge bg-success bg-opacity-10 text-success'>Verified</span></td>";
                        echo "</tr>";
                    }
                } else {
                     echo '<tr><td colspan="6" class="text-center py-5 text-muted">No collectors registered yet.</td></tr>';
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
