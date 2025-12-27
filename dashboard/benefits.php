<?php
// dashboard/benefits.php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit;
}
require_once '../config/db.php';

// Layout Config
$pageTitle = "Benefits & Sales";
$activePage = "benefits";
require_once 'includes/header.php';
?>

<div class="page-header">
    <div>
        <h2 class="page-title">Benefits & Value Generated</h2>
        <p class="text-muted mb-0">Tracking the economic value created from waste (e.g., Manure Sales, Plastic Recycled).</p>
    </div>
</div>

<div class="row">
    <!-- BENEFITS LOGS -->
    <div class="col-12">
        <div class="content-card">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="fw-bold text-primary m-0"><i class="bi bi-graph-up-arrow me-2"></i> Value Generation Log</h5>
                <span class="badge bg-primary bg-opacity-10 text-primary">Benefits</span>
            </div>
            
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Manager ID</th>
                            <th>Benefit Type</th>
                            <th>Details</th>
                            <th>Value Generated</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $sql = "SELECT b.*, u.user_unique_id FROM benefits_logs b 
                                JOIN users u ON b.user_id = u.id 
                                ORDER BY b.created_at DESC LIMIT 50";
                        $logs = $pdo->query($sql);
                        $found = false;

                        while($row = $logs->fetch()) {
                            $found = true;
                            echo "<tr>";
                            echo "<td class='text-muted small'>" . date('M j, Y', strtotime($row['created_at'])) . "</td>";
                            echo "<td class='fw-medium'>{$row['user_unique_id']}</td>";
                            echo "<td><span class='badge bg-info bg-opacity-10 text-info'>" . ucwords(str_replace('_', ' ', $row['benefit_type'])) . "</span></td>";
                            echo "<td class='text-muted small'>" . htmlspecialchars($row['description']) . "</td>";
                            echo "<td class='fw-bold text-success fs-5'>{$row['amount_value']}</td>";
                            echo "</tr>";
                        }
                        
                        if(!$found) {
                             echo '<tr><td colspan="5" class="text-center py-5 text-muted">No benefits recorded yet.</td></tr>';
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
