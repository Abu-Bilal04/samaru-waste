<?php
// config/db.php - Database Connection & Helper Functions

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "samaru_waste";

try {
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    // Set the PDO error mode to exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    // If database doesn't exist, try to create it (for initial setup convenience)
    try {
        $pdo = new PDO("mysql:host=$servername", $username, $password);
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbname`");
        $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $ex) {
        // In a real app, log error and exit gracefully
        error_log("Connection failed: " . $ex->getMessage());
        die("System Error: Database connection failed.");
    }
}

// Helper to sanitize input
function sanitize($data) {
    // Trim whitespace
    $data = trim($data);
    // Strip tags to remove HTML/PHP tags
    $data = strip_tags($data);
    // Convert special characters to HTML entities to prevent XSS
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

// Generate a random numeric ID (simple 6 digit for demo)
function generateParamsId() {
    return str_pad(mt_rand(0, 999999), 6, '0', STR_PAD_LEFT);
}
?>
