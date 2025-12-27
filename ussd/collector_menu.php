<?php
// ussd/collector_menu.php - Logic for Waste Collectors

function handleWasteCollector($user, $subInputs, $pdo, $fullInputs) {
    // Menu Options
    // 1. Log Collection (Input: Weight) -> For simplicity we assumes mixed waste or asks type? Prompt says "log all waste collected... and log all waste delivered"
    // Let's keep it simple:
    // 1. Log Collection (House/Shop)
    // 2. Log Delivery (To Community Point)
    
    $level = count($subInputs);

    if ($level == 0) {
        $res = "CON Welcome, " . $user['name'] . "\n";
        $res .= "1. Log Collection (From Community)\n";
        $res .= "2. Log Delivery (To Center)";
        echo_response($res);
        return;
    }

    $action = $subInputs[0];

    if ($action == "1") {
        // --- LOG COLLECTION ---
        if ($level == 1) {
            echo_response("CON Enter approximate weight (kg) collected:");
        } elseif ($level == 2) {
            $weight = floatval($subInputs[1]);
            // Save to DB
            $stmt = $pdo->prepare("INSERT INTO waste_logs (user_id, action_type, weight_kg) VALUES (?, 'collection', ?)");
            if ($stmt->execute([$user['id'], $weight])) {
               // Post to Blockfrost (async or placeholder)
               // For now, confirm users
               echo_response("END Collection of {$weight}kg recorded successfully.");
            } else {
               echo_response("END Error recording data.");
            }
        }
    } elseif ($action == "2") {
        // --- LOG DELIVERY ---
        if ($level == 1) {
             echo_response("CON Enter weight (kg) delivered to Center:");
        } elseif ($level == 2) {
            $weight = floatval($subInputs[1]);
            // Save
            $stmt = $pdo->prepare("INSERT INTO waste_logs (user_id, action_type, weight_kg) VALUES (?, 'delivery', ?)");
            $stmt->execute([$user['id'], $weight]);
            echo_response("END Delivery of {$weight}kg recorded. Good job!");
        }
    } else {
        echo_response("END Invalid Option");
    }
}
?>
