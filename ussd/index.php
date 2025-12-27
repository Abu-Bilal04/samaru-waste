<?php
// ussd/index.php - Main Logic

// Include configuration
require_once '../config/db.php';
require_once '../config/roles.php';

// --- Functions for Modules ---
// We include them here or require the files. 
// For simplicity in state management, we'll route to functions within included files or handle simple logic here.

$sessionId   = $_POST['sessionId'] ?? '';
$serviceCode = $_POST['serviceCode'] ?? '';
$phoneNumber = $_POST['phoneNumber'] ?? '';
$text        = $_POST['text'] ?? '';

// Split text input to track state
// Split text input AND handle "0" as Back navigation
$exploded = explode("*", $text);
$inputs = [];
foreach ($exploded as $val) {
    if ($val === '0') {
        array_pop($inputs); // Remove last input (Go Back)
    } elseif ($val !== "") {
        $inputs[] = $val;
    }
}
$level = count($inputs);

$response = "";

// 1. MAIN MENU (Level 0 - Empty Cleaned Inputs)
if (empty($inputs)) {
    $response  = "CON Welcome to Samaru Waste System\n";
    $response .= "1. Enter User ID\n";
    $response .= "2. View Waste Stats\n";
    $response .= "3. View Collectors";
    echo_response($response);
    exit;
}

// 2. FIRST SELECTION
$mainOption = $inputs[0];

if ($mainOption == "1") {
    // --- AUTHENTICATION FLOW ---
    handleAuthFlow($inputs, $pdo);
} elseif ($mainOption == "2") {
    // --- PUBLIC STATS ---
    handlePublicStats($pdo);
} elseif ($mainOption == "3") {
    // --- VIEW COLLECTORS ---
    handlePublicCollectors($pdo);
} else {
    echo_response("END Invalid option selected.");
}


// --- HANDLER FUNCTIONS ---

function echo_response($response) {
    header('Content-type: text/plain');
    echo $response;
}

function handleAuthFlow($inputs, $pdo) {
    // Step 1: Prompt for ID
    if (count($inputs) == 1) {
        echo_response("CON Please Enter your User ID:");
    }
    // Step 2: Validate ID and Route
    elseif (count($inputs) >= 2) {
        $userId = sanitize($inputs[1]);
        
        // Check DB for User
        $stmt = $pdo->prepare("SELECT * FROM users WHERE user_unique_id = ? LIMIT 1");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();

        if (!$user) {
            echo_response("END User ID not found. Please contact Admin.");
            return;
        }

        // Route based on Role
        switch ($user['role_level']) {
            case ROLE_WASTE_COLLECTOR:
                require_once 'collector_menu.php';
                handleWasteCollector($user, array_slice($inputs, 2), $pdo, $inputs);
                break;
            case ROLE_COMMUNITY_COLLECTOR:
                require_once 'community_menu.php';
                handleCommunityCollector($user, array_slice($inputs, 2), $pdo, $inputs);
                break;
            case ROLE_SUPER_ADMIN:
                echo_response("END Super Admins should use the Web Dashboard.");
                break;
            default:
                echo_response("END Access Denied.");
        }
    }
}

function handlePublicStats($pdo) {
    // Fetch some aggregate stats
    // Total Organic
    $org = $pdo->query("SELECT SUM(weight_kg) FROM waste_logs WHERE waste_type='organic' AND action_type='received'")->fetchColumn() ?: 0;
    $rec = $pdo->query("SELECT SUM(weight_kg) FROM waste_logs WHERE waste_type='recyclable' AND action_type='received'")->fetchColumn() ?: 0;

    $response = "END Current Samaru Waste Stats:\n";
    $response .= "Organic Processed: {$org} kg\n";
    $response .= "Recyclables: {$rec} kg\n";
    $response .= "Join us in keeping Samaru clean!";
    echo_response($response);
}

function handlePublicCollectors($pdo) {
    $stmt = $pdo->query("SELECT name FROM users WHERE role_level = " . ROLE_WASTE_COLLECTOR . " LIMIT 5");
    $response = "END Registered Collectors (Top 5):\n";
    while ($row = $stmt->fetch()) {
        $response .= "- " . $row['name'] . "\n";
    }
    echo_response($response);
}
?>
