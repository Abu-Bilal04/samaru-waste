<?php
// dashboard/index.php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit;
}
require_once '../config/db.php';
require_once '../config/roles.php';

// Fetch Live Data
$totalUsers = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$totalWaste = $pdo->query("SELECT SUM(weight_kg) FROM waste_logs WHERE action_type='received'")->fetchColumn() ?: 0;
$collectionsCounts = $pdo->query("SELECT COUNT(*) FROM waste_logs")->fetchColumn();
$totalBenefits = $pdo->query("SELECT SUM(amount_value) FROM benefits_logs")->fetchColumn() ?: 0;
$recentLogs = $pdo->query("SELECT w.*, u.user_unique_id FROM waste_logs w JOIN users u ON w.user_id = u.id ORDER BY w.created_at DESC LIMIT 5")->fetchAll();

// Chart Data (Mocking some distribution logic for demo if DB is empty)
$organic = $pdo->query("SELECT SUM(weight_kg) FROM waste_logs WHERE waste_type='organic'")->fetchColumn() ?: 12;
$recyclable = $pdo->query("SELECT SUM(weight_kg) FROM waste_logs WHERE waste_type='recyclable'")->fetchColumn() ?: 8;
$nonRecyclable = $pdo->query("SELECT SUM(weight_kg) FROM waste_logs WHERE waste_type='non_recyclable'")->fetchColumn() ?: 4;

$pageTitle = "Overview";
$activePage = "overview";
require_once 'includes/header.php';
?>

<!-- Added Chart.js just for this page -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
    .stat-card {
        background: white; border-radius: 12px; padding: 1.5rem;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05); transition: transform 0.2s; border: 1px solid #f0f0f0;
    }
    .stat-card:hover { transform: translateY(-3px); box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
    .stat-icon-wrapper {
        width: 48px; height: 48px; border-radius: 10px; display: flex; align-items: center; justify-content: center;
        font-size: 1.5rem; margin-bottom: 1rem;
    }
    .icon-green { background: #e8f5e9; color: #2e7d32; }
    .icon-blue { background: #e3f2fd; color: #1565c0; }
    .icon-orange { background: #fff3e0; color: #ef6c00; }
    .icon-purple { background: #f3e5f5; color: #7b1fa2; }
    .stat-value { font-size: 1.8rem; font-weight: 700; margin-bottom: 5px; color: #111827; }
    .stat-label { font-size: 0.875rem; color: var(--text-muted); font-weight: 500; }
    .fade-in { animation: fadeIn 0.6s ease-out forwards; opacity: 0; }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
</style>

<div class="page-header">
    <div>
        <h2 class="page-title">Dashboard Overview</h2>
        <p class="text-muted mb-0">Welcome back, Super Admin.</p>
    </div>
    <div class="text-end d-none d-md-block">
        <div class="fw-bold date-display"><?php echo date("l, F j, Y"); ?></div>
    </div>
</div>

<!-- Stats Grid -->
<div class="row g-4 mb-4">
    <div class="col-md-6 col-lg-3 fade-in" style="animation-delay: 0.1s;">
        <div class="stat-card">
            <div class="stat-icon-wrapper icon-green"><i class="bi bi-trash"></i></div>
            <div class="stat-value counter" data-target="<?php echo $totalWaste; ?>">0</div>
            <div class="stat-label">Kg Waste Collected</div>
        </div>
    </div>
    <div class="col-md-6 col-lg-3 fade-in" style="animation-delay: 0.2s;">
        <div class="stat-card">
            <div class="stat-icon-wrapper icon-blue"><i class="bi bi-people-fill"></i></div>
            <div class="stat-value counter" data-target="<?php echo $totalUsers; ?>">0</div>
            <div class="stat-label">Active Collectors</div>
        </div>
    </div>
    <div class="col-md-6 col-lg-3 fade-in" style="animation-delay: 0.3s;">
        <div class="stat-card">
            <div class="stat-icon-wrapper icon-orange"><i class="bi bi-cash-coin"></i></div>
            <div class="stat-value counter" data-target="<?php echo $totalBenefits; ?>">0</div>
            <div class="stat-label">Benefits Generated</div>
        </div>
    </div>
    <div class="col-md-6 col-lg-3 fade-in" style="animation-delay: 0.4s;">
        <div class="stat-card">
            <div class="stat-icon-wrapper icon-purple"><i class="bi bi-link"></i></div>
            <div class="stat-value counter" data-target="<?php echo $collectionsCounts; ?>">0</div>
            <div class="stat-label">On-Chain Records</div>
        </div>
    </div>
</div>

<!-- Charts Area -->
<div class="row g-4 mb-4">
    <div class="col-lg-8 fade-in" style="animation-delay: 0.5s;">
        <div class="content-card h-100">
            <h5 class="fw-bold mb-4">Waste Collection Trends</h5>
            <canvas id="mainChart"></canvas>
        </div>
    </div>
    <div class="col-lg-4 fade-in" style="animation-delay: 0.6s;">
        <div class="content-card h-100">
            <h5 class="fw-bold mb-4">Waste Composition</h5>
            <canvas id="pieChart"></canvas>
        </div>
    </div>
</div>

<!-- Recent Activity -->
<div class="content-card fade-in" style="animation-delay: 0.7s;">
    <h5 class="fw-bold mb-4">Recent Blockchain Records</h5>
    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>User ID</th>
                    <th>Action</th>
                    <th>Weight</th>
                    <th>Status Description</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($recentLogs as $row): ?>
                <tr>
                    <td class="fw-bold"><?php echo htmlspecialchars($row['user_unique_id']); ?></td>
                    <td><?php echo ucfirst($row['action_type']); ?></td>
                    <td><?php echo $row['weight_kg']; ?> kg</td>
                    <td class="text-muted small"><?php echo $row['waste_type'] ? ucfirst($row['waste_type']) : 'General Waste'; ?></td>
                    <td>
                        <?php if($row['tx_hash']): ?>
                            <span class="badge bg-success bg-opacity-10 text-success">Verified</span>
                        <?php else: ?>
                            <span class="badge bg-warning bg-opacity-10 text-warning">Pending</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if(empty($recentLogs)): ?>
                <tr>
                    <td colspan="5" class="text-center py-5 text-muted">
                        No records found yet.
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
    // Counter Animation
    const counters = document.querySelectorAll('.counter');
    counters.forEach(counter => {
        const updateCount = () => {
            const target = +counter.getAttribute('data-target');
            const count = +counter.innerText;
            const speed = 200; 
            const inc = target / speed;
            if (count < target) {
                counter.innerText = Math.ceil(count + inc);
                setTimeout(updateCount, 1);
            } else { counter.innerText = target; }
        };
        updateCount();
    });

    // Charts
    const organicData = <?php echo $organic; ?>;
    const recyclableData = <?php echo $recyclable; ?>;
    const nonRecyclableData = <?php echo $nonRecyclable; ?>;

    const ctxMain = document.getElementById('mainChart').getContext('2d');
    new Chart(ctxMain, {
        type: 'line',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
            datasets: [{
                label: 'Collection (kg)',
                data: [12, 19, 15, 25, 22, 30],
                borderColor: '#2e7d32',
                backgroundColor: 'rgba(46, 125, 50, 0.1)',
                borderWidth: 2,
                fill: true,
                tension: 0.4
            }]
        },
        options: { responsive: true, plugins: { legend: { display: false } } }
    });

    const ctxPie = document.getElementById('pieChart').getContext('2d');
    new Chart(ctxPie, {
        type: 'doughnut',
        data: {
            labels: ['Organic', 'Recyclable', 'Other'],
            datasets: [{
                data: [organicData, recyclableData, nonRecyclableData],
                backgroundColor: ['#66bb6a', '#42a5f5', '#bdbdbd'],
                borderWidth: 0,
                hoverOffset: 4
            }]
        },
        options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
    });
</script>

<?php require_once 'includes/footer.php'; ?>
