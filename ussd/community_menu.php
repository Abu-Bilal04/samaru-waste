<?php
// ussd/community_menu.php - Logic for Community Collectors

function handleCommunityCollector($user, $subInputs, $pdo, $fullInputs) {
    // 1. Register Waste Collector
    // 2. Log Waste Received (Sorted)
    // 3. Log Benefits
    
    $level = count($subInputs);

    if ($level == 0) {
        $res = "CON Community Manager Menu:\n";
        $res .= "1. Register Waste Collector\n";
        $res .= "2. Log Received Waste\n";
        $res .= "3. Check Available Stock\n";
        $res .= "4. Log Sales / Benefits\n";
        $res .= "0. Back";
        echo_response($res);
        return;
    }

    $action = $subInputs[0];

    // --- 1. REGISTER COLLECTOR ---
    if ($action == "1") {
        // Steps: Name -> Phone -> Address -> Submit
        if ($level == 1) {
            echo_response("CON Enter Collector Full Name:");
        } elseif ($level == 2) {
            echo_response("CON Enter Collector Phone Number:");
        } elseif ($level == 3) {
            echo_response("CON Enter Collector Address:\n0. Back");
        } elseif ($level == 4) {
            // Save logic
            $name    = sanitize($subInputs[1]);
            $phone   = sanitize($subInputs[2]);
            $address = sanitize($subInputs[3]);
            $newId   = "WC" . rand(1000, 9999); // Simple auto ID
            
            // Insert
            $stmt = $pdo->prepare("INSERT INTO users (user_unique_id, name, phone, address, role_level) VALUES (?, ?, ?, ?, ?)");
            try {
                $stmt->execute([$newId, $name, $phone, $address, ROLE_WASTE_COLLECTOR]);
                echo_response("END Collector Registered Successfully!\nAssigned ID: $newId");
            } catch (Exception $e) {
                echo_response("END Error: Could not register. " . $e->getMessage());
            }
        }
    } 
    // --- 2. LOG RECEIVED WASTE ---
    elseif ($action == "2") {
        // Type -> Weight
        if ($level == 1) {
            $res = "CON Select Waste Type:\n";
            $res .= "1. Organic\n";
            $res .= "2. Recyclable\n";
            $res .= "3. Non-Recyclable\n";
            $res .= "0. Back";
            echo_response($res);
        } elseif ($level == 2) {
            echo_response("CON Enter Weight (kg):\n0. Back");
        } elseif ($level == 3) {
            $typeMap = [1 => 'organic', 2 => 'recyclable', 3 => 'non_recyclable'];
            $typeIndex = (int)$subInputs[1];
            
            if (!isset($typeMap[$typeIndex])) {
                 echo_response("END Invalid Waste Type selected.");
                 return;
            }
            
            $wType  = $typeMap[$typeIndex];
            $weight = floatval($subInputs[2]);

            // Save
            // Save
            require_once '../api/cardano.php'; // Include blockchain logic

            // 1. Generate Hash Immediately
            $mockData = ['user' => $user['id'], 'type' => $wType, 'weight' => $weight];
            $txHash = logToBlockchain($mockData);

            // 2. Save with Hash
            $stmt = $pdo->prepare("INSERT INTO waste_logs (user_id, action_type, waste_type, weight_kg, tx_hash) VALUES (?, 'received', ?, ?, ?)");
            $stmt->execute([$user['id'], $wType, $weight, $txHash]);
            
            echo_response("END Received {$weight}kg of {$wType} waste recorded & Verified on Chain!");
        }
    }
    // --- 3. LOG BENEFITS ---
    // --- 3. CHECK AVAILABLE STOCK ---
    elseif ($action == "3") {
         // Calculate sums of 'received' minus 'sold'
         $orgReceived = $pdo->query("SELECT SUM(weight_kg) FROM waste_logs WHERE waste_type='organic' AND action_type='received'")->fetchColumn() ?: 0;
         $orgSold = $pdo->query("SELECT SUM(weight_kg) FROM waste_logs WHERE waste_type='organic' AND action_type='sold'")->fetchColumn() ?: 0;
         
         $recReceived = $pdo->query("SELECT SUM(weight_kg) FROM waste_logs WHERE waste_type='recyclable' AND action_type='received'")->fetchColumn() ?: 0;
         $recSold = $pdo->query("SELECT SUM(weight_kg) FROM waste_logs WHERE waste_type='recyclable' AND action_type='sold'")->fetchColumn() ?: 0;

         $availOrganic = $orgReceived - $orgSold;
         $availRecycle = $recReceived - $recSold;

         $res = "END Available for Sale:\n";
         $res .= "Organic Manure/Raw: {$availOrganic} kg\n";
         $res .= "Recyclables: {$availRecycle} kg\n";
         $res .= "Check back later for updates.";
         echo_response($res);
    }
    // --- 4. LOG BENEFITS / SALES ---
    elseif ($action == "4") {
        if ($level == 1) {
             $res = "CON Select Action:\n";
             $res .= "1. Record Manure Produced (Add Value)\n";
             $res .= "2. Sell Organic Manure\n";
             $res .= "3. Sell Recyclables\n";
             $res .= "0. Back";
             echo_response($res);
        } elseif ($level == 2) {
             if ($subInputs[1] == "1") echo_response("CON Enter Manure Quantity Produced (kg):");
             elseif ($subInputs[1] == "2") echo_response("CON Enter Organic Kg to Sell:");
             elseif ($subInputs[1] == "3") echo_response("CON Enter Recyclable Kg to Sell:");
        } elseif ($level == 3) {
             $subAct = $subInputs[1];
             $qty = floatval($subInputs[2]);

             // Logic
             if ($subAct == "1") {
                 // Produced Manure -> Just a Benefit Record (Value creation)
                 // This doesn't necessarily reduce RAW waste stock unless we link them, but simplified for now
                 $stmt = $pdo->prepare("INSERT INTO benefits_logs (user_id, benefit_type, amount_value, description) VALUES (?, ?, ?, ?)");
                 $stmt->execute([$user['id'], 'manure_produced', $qty, "$qty kg Manure Produced"]);
                 echo_response("END Recorded $qty kg of Manure Produced.");
             } else {
                 // Selling -> Deduct Waste Stock AND Add Money Value
                 $wType = ($subAct == "2") ? 'organic' : 'recyclable';
                 
                 // Check Stock
                 $in = $pdo->query("SELECT SUM(weight_kg) FROM waste_logs WHERE waste_type='$wType' AND action_type='received'")->fetchColumn() ?: 0;
                 $out = $pdo->query("SELECT SUM(weight_kg) FROM waste_logs WHERE waste_type='$wType' AND action_type='sold'")->fetchColumn() ?: 0;
                 
                 if (($in - $out) < $qty) {
                     echo_response("END Insufficient Stock! Only " . ($in - $out) . "kg available.");
                 } else {
                     // 1. Log "Sold" Waste (Deduct Stock)
                     $stmt = $pdo->prepare("INSERT INTO waste_logs (user_id, action_type, waste_type, weight_kg) VALUES (?, 'sold', ?, ?)");
                     $stmt->execute([$user['id'], $wType, $qty]);

                     // 2. Ask for Sale Price (Next Step needed for real currency, but assuming 1 unit for now or auto-calc)
                     // For simplicity in this flow, we will assume a standard rate or just log the weight sold as benefit
                     // Let's Add a revenue step
                     echo_response("CON Enter Total Sale Price (Currency):\n0. Back");
                 }
             }
        } elseif ($level == 4) {
             // Confirming Sale Price
             $subAct = $subInputs[1];
             if ($subAct == "1") return; // Handled above

             $qty = floatval($subInputs[2]);
             $price = floatval($subInputs[3]);
             $bType = ($subAct == "2") ? 'manure_sales' : 'recycling_sales';

             // Log Revenue
             $stmt = $pdo->prepare("INSERT INTO benefits_logs (user_id, benefit_type, amount_value, description) VALUES (?, ?, ?, ?)");
             $stmt->execute([$user['id'], $bType, $price, "Sold $qty kg for $price"]);

             echo_response("END Sale Recorded!\nStock deducted & Revenue logged.");
        }
    }
}
?>
