<?php
// api/fetch.php
header('Content-Type: application/json');
require_once '../config/db.php';

// Allow Public Access (or add token check)
// Returns stats for the frontend or external integration

try {
    // 1. Total Weight Collected
    $stmt = $pdo->query("SELECT SUM(weight_kg) as total_weight FROM waste_logs WHERE action_type = 'received'");
    $total = $stmt->fetch()['total_weight'] ?? 0;

    // 2. Total Users
    $stmt = $pdo->query("SELECT COUNT(*) as total_users FROM users WHERE role_level != 1");
    $users = $stmt->fetch()['total_users'] ?? 0;

    // 3. Recent Activity
    $stmt = $pdo->query("SELECT waste_type, weight_kg, created_at FROM waste_logs WHERE action_type='received' ORDER BY created_at DESC LIMIT 5");
    $recent = $stmt->fetchAll();

    echo json_encode([
        'status' => 'success',
        'data' => [
            'total_waste_collected_kg' => (float)$total,
            'registered_users' => (int)$users,
            'recent_logs' => $recent
        ]
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
