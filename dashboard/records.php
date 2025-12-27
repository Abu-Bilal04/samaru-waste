<?php
// dashboard/records.php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit;
}
require_once '../config/db.php';

// Layout Config
$pageTitle = "Global Records";
$activePage = "records";
require_once 'includes/header.php';
?>

<div class="page-header">
    <div>
        <h2 class="page-title">Waste Records Log</h2>
        <p class="text-muted mb-0">Immutably recorded transactions of waste collection and value generation.</p>
    </div>
</div>

<div class="row">
    <!-- Alert Message -->
    <?php if(isset($_SESSION['msg'])): ?>
        <div class="col-12 mb-3">
            <div class="alert alert-<?php echo $_SESSION['msg_type']; ?> alert-dismissible fade show" role="alert">
                <?php echo $_SESSION['msg']; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['msg']); unset($_SESSION['msg_type']); ?>
        </div>
    <?php endif; ?>



    <!-- WASTE LOGS -->
    <div class="col-12 mb-5">
        <div class="content-card">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="fw-bold text-success m-0"><i class="bi bi-recycle me-2"></i> Waste Collection Log</h5>
                <span class="badge bg-success bg-opacity-10 text-success">Live Updates</span>
            </div>
            
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Date & Time</th>
                            <th>Collector ID</th>
                            <th>Action Type</th>
                            <th>Category</th>
                            <th>Weight</th>
                            <th>Blockchain Verification</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Limit to 50 for performance
                        $sql = "SELECT w.*, u.user_unique_id FROM waste_logs w 
                                JOIN users u ON w.user_id = u.id 
                                ORDER BY w.created_at DESC LIMIT 50";
                        $logs = $pdo->query($sql);
                        $found = false;

                        while($row = $logs->fetch()) {
                            $found = true;
                            $hash = $row['tx_hash'];
                            $hashDisplay = $hash ? 
                                "<span class='font-monospace small text-primary'><i class='bi bi-link-45deg'></i> " . substr($hash, 0, 10) . "...</span>" : 
                                "<span class='badge bg-warning bg-opacity-10 text-warning'>Pending</span>";

                            echo "<tr>";
                            echo "<td class='text-muted small'>" . date('M j, H:i', strtotime($row['created_at'])) . "</td>";
                            echo "<td class='fw-medium'>{$row['user_unique_id']}</td>";
                            echo "<td><span class='badge bg-light text-dark border'>" . ucfirst($row['action_type']) . "</span></td>";
                            echo "<td>" . ($row['waste_type'] ? ucfirst($row['waste_type']) : 'Mixed Waste') . "</td>";
                            echo "<td class='fw-bold'>{$row['weight_kg']} kg</td>";
                            echo "<td>$hashDisplay</td>";
                            echo "</tr>";
                        }

                        if (!$found) {
                             echo '<tr><td colspan="6" class="text-center py-5 text-muted">No waste records logged yet.</td></tr>';
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>


</div>

<?php require_once 'includes/footer.php'; ?>
