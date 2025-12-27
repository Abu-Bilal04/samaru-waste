<?php
// dashboard/verify.php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit;
}
require_once '../config/db.php';
require_once '../api/cardano.php'; // Include our blockchain logic

if (isset($_POST['verify_all'])) {
    
    // 1. Fetch all pending waste logs
    $stmt = $pdo->prepare("SELECT * FROM waste_logs WHERE tx_hash IS NULL OR tx_hash = ''");
    $stmt->execute();
    $pendingLogs = $stmt->fetchAll();
    
    $count = 0;
    
    foreach ($pendingLogs as $log) {
        // 2. Call Blockfrost/Cardano Logic
        // In a real app, we would send $log data as metadata
        $hash = logToBlockchain($log);
        
        // 3. Update Database
        $update = $pdo->prepare("UPDATE waste_logs SET tx_hash = ? WHERE id = ?");
        $update->execute([$hash, $log['id']]);
        
        $count++;
    }
    
    // Store result in session to show on redirect
    $_SESSION['msg'] = "Successfully verified $count records on the blockchain.";
    $_SESSION['msg_type'] = "success";
    
    header("Location: records.php");
    exit;
} else {
    header("Location: records.php");
    exit;
}
?>
