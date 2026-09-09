<?php
session_start();
require_once '../server/config.php';

header('Content-Type: application/json');

// Read JSON input
$input = json_decode(file_get_contents('php://input'), true);

// Handle delete request
if ($input && isset($input['delete']) && $input['delete']) {
    $family_id = $input['family_id'] ?? null;
    
    if (!$family_id) {
        echo json_encode(['success' => false, 'message' => 'Family ID is required.']);
        exit;
    }
    
    try {
        $stmt = $pdo->prepare("DELETE FROM family WHERE Family_ID = ?");
        $stmt->execute([$family_id]);
        
        if ($stmt->rowCount() > 0) {
            echo json_encode(['success' => true, 'message' => 'Family deleted successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Family not found.']);
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

// Handle insert/update requests
if ($input) {
    $address = $input['address'];
    $household_id = trim($input['household_id'] ?? '');
    $head_of_family_id = $input['head_of_family'] ?? null; // Now expects Resident ID
    $editing = (bool)($input['editing'] ?? false);
    $family_id = $input['family_id'] ?? null;

    if ($household_id === '') {
        echo json_encode(['success' => false, 'message' => 'Household ID is required.']);
        exit;
    }

    // Validate Resident ID
    if (!$head_of_family_id || !is_numeric($head_of_family_id)) {
        echo json_encode(['success' => false, 'message' => 'Valid Resident ID is required for head of family.']);
        exit;
    }

    // Get resident info to get last name for family name
    $stmt = $pdo->prepare("SELECT Resident_ID, First_Name, Last_Name FROM residents WHERE Resident_ID = ?");
    $stmt->execute([$head_of_family_id]);
    $resident = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$resident) {
        echo json_encode(['success' => false, 'message' => 'Resident ID does not exist. Please ensure the resident exists in the system.']);
        exit;
    }

    // Use last name as family name (or use provided family_name if editing)
    $family_name = $input['family_name'] ?? $resident['Last_Name'];
    
    // Store Resident ID in Head_of_Family
    $head_of_family = $head_of_family_id;

    try {
        if ($editing && $family_id) {
            // Ensure household ID unique (excluding current)
            $check = $pdo->prepare("SELECT Family_ID FROM family WHERE Household_ID = ? AND Family_ID != ?");
            $check->execute([$household_id, $family_id]);
            if ($check->rowCount() > 0) {
                echo json_encode(['success' => false, 'message' => 'Household ID already exists.']);
                exit;
            }

            // Update existing family
            $stmt = $pdo->prepare("UPDATE family SET Household_ID = ?, Family_Name = ?, Address = ?, Head_of_Family = ? WHERE Family_ID = ?");
            $stmt->execute([$household_id, $family_name, $address, $head_of_family, $family_id]);
            echo json_encode(['success' => true, 'message' => 'Family updated successfully']);
        } else {
            // Auto-generate Family ID if not provided
            if (!$family_id || $family_id === '') {
                // Get the next available Family ID
                $stmt = $pdo->query("SELECT MAX(Family_ID) as max_id FROM family");
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                $family_id = ($result['max_id'] ?? 0) + 1;
            } else {
                $family_id = intval($family_id);
                
                if ($family_id <= 0) {
                    echo json_encode(['success' => false, 'message' => 'Family ID must be a positive number.']);
                    exit;
                }

                // Check if Family ID already exists
                $check = $pdo->prepare("SELECT Family_ID FROM family WHERE Family_ID = ?");
                $check->execute([$family_id]);

                if ($check->rowCount() > 0) {
                    echo json_encode(['success' => false, 'message' => 'Family ID already exists.']);
                    exit;
                }
            }

            // Ensure household ID unique
            $check = $pdo->prepare("SELECT Household_ID FROM family WHERE Household_ID = ?");
            $check->execute([$household_id]);
            if ($check->rowCount() > 0) {
                echo json_encode(['success' => false, 'message' => 'Household ID already exists.']);
                exit;
            }

            // Insert new family
            $stmt = $pdo->prepare("INSERT INTO family (Family_ID, Household_ID, Family_Name, Address, Head_of_Family) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$family_id, $household_id, $family_name, $address, $head_of_family]);

            echo json_encode(['success' => true, 'message' => 'Family added successfully', 'Family_ID' => $family_id]);
        }

    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

// Handle fetch-all request
if (isset($_GET['action']) && $_GET['action'] === 'fetch') {
    try {
        $stmt = $pdo->query("
            SELECT f.Family_ID, f.Household_ID, f.Family_Name, f.Address, f.Head_of_Family,
                   r.First_Name, r.Last_Name
            FROM family f
            LEFT JOIN residents r ON f.Head_of_Family = r.Resident_ID
            ORDER BY f.Family_ID DESC
        ");
        $families = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($families as &$f) {
            $f['Head_of_Family_Name'] = ($f['First_Name'] && $f['Last_Name']) ? $f['First_Name'] . ' ' . $f['Last_Name'] : '—';
            // ⭐ Add typed head of family field for frontend
            $f['Head_of_Family_Typed'] = $f['Head_of_Family'] ?? '';
            unset($f['First_Name'], $f['Last_Name']);
        }

        echo json_encode($families);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

// Handle fetch single family request
if (isset($_GET['action']) && $_GET['action'] === 'get') {
    $family_id = $_GET['family_id'] ?? null;
    if (!$family_id) {
        echo json_encode(null);
        exit;
    }

    try {
        $stmt = $pdo->prepare("
            SELECT f.Family_ID, f.Household_ID, f.Family_Name, f.Address, f.Head_of_Family,
                   r.First_Name, r.Last_Name
            FROM family f
            LEFT JOIN residents r ON f.Head_of_Family = r.Resident_ID
            WHERE f.Family_ID = ?
        ");
        $stmt->execute([$family_id]);
        $family = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($family) {
            $family['Head_of_Family_Name'] = ($family['First_Name'] && $family['Last_Name']) ? $family['First_Name'] . ' ' . $family['Last_Name'] : '—';
            // ⭐ Add typed head of family field for frontend
            $family['Head_of_Family_Typed'] = $family['Head_of_Family'] ?? '';
            unset($family['First_Name'], $family['Last_Name']);
        }

        echo json_encode($family);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

// If no input and no action
echo json_encode(['success' => false, 'message' => 'No action specified']);
?>
