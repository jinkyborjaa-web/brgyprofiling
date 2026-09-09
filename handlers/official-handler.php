<?php
session_start();
if (($_SESSION['role'] ?? '') !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Administrator access required.']);
    exit;
}
require_once '../server/config.php';

header('Content-Type: application/json');

// Read JSON input
$input = json_decode(file_get_contents('php://input'), true);

// Handle delete request
if ($input && isset($input['delete']) && $input['delete']) {
    $official_id = $input['official_id'] ?? null;
    
    if (!$official_id) {
        echo json_encode(['success' => false, 'message' => 'Official ID is required.']);
        exit;
    }
    
    try {
        $stmt = $pdo->prepare("DELETE FROM officials WHERE Official_ID = ?");
        $stmt->execute([$official_id]);
        
        if ($stmt->rowCount() > 0) {
            echo json_encode(['success' => true, 'message' => 'Official deleted successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Official not found.']);
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

// Handle insert/update requests
if ($input) {
    $resident_id = $input['resident_id'] ?? null;
    $position = $input['position'] ?? '';
    $term_start = $input['term_start'] ?? '';
    $term_end = $input['term_end'] ?? '';
    $editing = (bool)($input['editing'] ?? false);
    $official_id = $input['official_id'] ?? null;

    // Validate resident_id is numeric and exists
    if (!$resident_id || !is_numeric($resident_id)) {
        echo json_encode(['success' => false, 'message' => 'Valid Resident ID is required.']);
        exit;
    }
    
    // Check if resident exists
    $check = $pdo->prepare("SELECT Resident_ID FROM residents WHERE Resident_ID = ?");
    $check->execute([$resident_id]);
    if ($check->rowCount() === 0) {
        echo json_encode(['success' => false, 'message' => 'Resident ID does not exist. Please ensure the resident exists in the system.']);
        exit;
    }
    
    $final_resident_id = intval($resident_id);

    try {
        if ($editing && $official_id) {
            // Update existing official
            $stmt = $pdo->prepare("UPDATE officials SET Resident_ID = ?, Position = ?, Term_start = ?, Term_end = ? WHERE Official_ID = ?");
            $stmt->execute([$final_resident_id, $position, $term_start, $term_end, $official_id]);
            echo json_encode(['success' => true, 'message' => 'Official updated successfully']);
        } else {
            // Insert new official
            $stmt = $pdo->prepare("INSERT INTO officials (Resident_ID, Position, Term_start, Term_end) VALUES (?, ?, ?, ?)");
            $stmt->execute([$final_resident_id, $position, $term_start, $term_end]);
            $new_id = $pdo->lastInsertId();
            echo json_encode(['success' => true, 'message' => 'Official added successfully', 'Official_ID' => $new_id]);
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
            SELECT o.Official_ID, o.Resident_ID, o.Position, o.Term_start, o.Term_end,
                   r.First_Name, r.Last_Name, r.Contact_Number
            FROM officials o
            LEFT JOIN residents r ON o.Resident_ID = r.Resident_ID
            ORDER BY o.Official_ID DESC
        ");
        $officials = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($officials as &$o) {
            $o['Name'] = ($o['First_Name'] && $o['Last_Name']) ? trim($o['First_Name'] . ' ' . $o['Last_Name']) : '—';
            $o['Contact'] = $o['Contact_Number'] ?? '—';
            $o['Email'] = '—'; // Email not in database schema yet
            $o['Term_Start'] = $o['Term_start'];
            $o['Term_End'] = $o['Term_end'];
            
            // Calculate status based on term_end date
            $term_end = new DateTime($o['Term_end']);
            $today = new DateTime();
            $o['Status'] = ($term_end >= $today) ? 'Active' : 'Inactive';
            
            unset($o['First_Name'], $o['Last_Name'], $o['Contact_Number'], $o['Term_start']);
        }

        echo json_encode($officials);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

// Handle fetch single official request
if (isset($_GET['action']) && $_GET['action'] === 'get') {
    $official_id = $_GET['official_id'] ?? null;
    if (!$official_id) {
        echo json_encode(null);
        exit;
    }

    try {
        $stmt = $pdo->prepare("
            SELECT o.Official_ID, o.Resident_ID, o.Position, o.Term_start, o.Term_end,
                   r.First_Name, r.Last_Name, r.Contact_Number
            FROM officials o
            LEFT JOIN residents r ON o.Resident_ID = r.Resident_ID
            WHERE o.Official_ID = ?
        ");
        $stmt->execute([$official_id]);
        $official = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($official) {
            $official['Name'] = ($official['First_Name'] && $official['Last_Name']) ? trim($official['First_Name'] . ' ' . $official['Last_Name']) : '—';
            $official['Contact'] = $official['Contact_Number'] ?? '—';
            $official['Email'] = '—'; // Email not in database schema yet
            $official['Term_Start'] = $official['Term_start'];
            $official['Term_End'] = $official['Term_end'];
            
            // Calculate status
            $term_end = new DateTime($official['Term_end']);
            $today = new DateTime();
            $official['Status'] = ($term_end >= $today) ? 'Active' : 'Inactive';
            
            unset($official['First_Name'], $official['Last_Name'], $official['Contact_Number'], $official['Term_start']);
        }

        echo json_encode($official);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

// If no input and no action
echo json_encode(['success' => false, 'message' => 'No action specified']);
?>

