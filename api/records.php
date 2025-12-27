<?php
// api/records.php
// Endpoint to receive waste logs from IoT devices or external apps
header('Content-Type: application/json');
require_once '../config/db.php';

// Validate Request Method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Method Not Allowed
    echo json_encode(['status' => 'error', 'message' => 'Only POST requests allowed']);
    exit;
}

// Get JSON Input
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Invalid JSON']);
    exit;
}

// Validate Fields
if (!isset($input['user_unique_id']) || !isset($input['waste_type']) || !isset($input['weight_kg'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Missing required fields: user_unique_id, waste_type, weight_kg']);
    exit;
}

$userIdStr = sanitize($input['user_unique_id']);
$wasteType = sanitize($input['waste_type']);
$weight    = floatval($input['weight_kg']);

try {
    // Find User Internal ID
    $stmt = $pdo->prepare("SELECT id FROM users WHERE user_unique_id = ?");
    $stmt->execute([$userIdStr]);
    $user = $stmt->fetch();

    if (!$user) {
        http_response_code(404);
        echo json_encode(['status' => 'error', 'message' => 'User ID not found']);
        exit;
    }

    // Insert Log
    $logStmt = $pdo->prepare("INSERT INTO waste_logs (user_id, action_type, waste_type, weight_kg) VALUES (?, 'received', ?, ?)");
    $logStmt->execute([$user['id'], $wasteType, $weight]);

    echo json_encode([
        'status' => 'success', 
        'message' => 'Waste record log saved successfully',
        'data' => [
            'id' => $pdo->lastInsertId(),
            'user' => $userIdStr
        ]
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
