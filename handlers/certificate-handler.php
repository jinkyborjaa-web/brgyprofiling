<?php
session_start();
require_once '../server/config.php';

header('Content-Type: application/json');

// Read JSON input
$input = json_decode(file_get_contents('php://input'), true);

// Handle delete request
if ($input && isset($input['delete']) && $input['delete']) {
    $certificate_id = $input['certificate_id'] ?? null;
    
    if (!$certificate_id) {
        echo json_encode(['success' => false, 'message' => 'Certificate ID is required.']);
        exit;
    }
    
    try {
        $stmt = $pdo->prepare("DELETE FROM certificates WHERE Certificate_ID = ?");
        $stmt->execute([$certificate_id]);
        
        if ($stmt->rowCount() > 0) {
            echo json_encode(['success' => true, 'message' => 'Certificate deleted successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Certificate not found.']);
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

// Handle insert/update requests
if ($input) {
    $certificate_id = $input['certificate_id'] ?? null;
    $resident_id = $input['resident_id'] ?? null;
    $certificate_type = $input['certificate_type'] ?? '';
    $purpose = $input['purpose'] ?? null;
    $date_issued = $input['date_issued'] ?? '';
    $issued_by = $input['issued_by'] ?? null; // Official ID who issued it
    $editing = (bool)($input['editing'] ?? false);

    // Validate required fields
    if (!$resident_id || !is_numeric($resident_id)) {
        echo json_encode(['success' => false, 'message' => 'Valid Resident ID is required.']);
        exit;
    }

    if (!$certificate_type) {
        echo json_encode(['success' => false, 'message' => 'Certificate Type is required.']);
        exit;
    }

    if (!$date_issued) {
        echo json_encode(['success' => false, 'message' => 'Date Issued is required.']);
        exit;
    }

    // Check if resident exists
    $check = $pdo->prepare("SELECT Resident_ID FROM residents WHERE Resident_ID = ?");
    $check->execute([$resident_id]);
    if ($check->rowCount() === 0) {
        echo json_encode(['success' => false, 'message' => 'Resident ID does not exist.']);
        exit;
    }

    // If issued_by is provided, validate it exists
    if ($issued_by && is_numeric($issued_by)) {
        $check = $pdo->prepare("SELECT Official_ID FROM officials WHERE Official_ID = ?");
        $check->execute([$issued_by]);
        if ($check->rowCount() === 0) {
            echo json_encode(['success' => false, 'message' => 'Official ID does not exist.']);
            exit;
        }
    }

    try {
        if ($editing && $certificate_id) {
            // Update existing certificate
            $stmt = $pdo->prepare("UPDATE certificates SET Resident_ID = ?, Certificate_Type = ?, Purpose = ?, Date_Issued = ?, Issued_by = ? WHERE Certificate_ID = ?");
            $stmt->execute([$resident_id, $certificate_type, $purpose, $date_issued, $issued_by, $certificate_id]);
            echo json_encode(['success' => true, 'message' => 'Certificate updated successfully']);
        } else {
            // Auto-generate Certificate ID if not provided
            if (!$certificate_id || $certificate_id === '') {
                // Get the next available Certificate ID
                $stmt = $pdo->query("SELECT MAX(Certificate_ID) as max_id FROM certificates");
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                $certificate_id = ($result['max_id'] ?? 0) + 1;
            } else {
                $certificate_id = intval($certificate_id);
                
                if ($certificate_id <= 0) {
                    echo json_encode(['success' => false, 'message' => 'Certificate ID must be a positive number.']);
                    exit;
                }

                // Check if Certificate ID already exists
                $check = $pdo->prepare("SELECT Certificate_ID FROM certificates WHERE Certificate_ID = ?");
                $check->execute([$certificate_id]);

                if ($check->rowCount() > 0) {
                    echo json_encode(['success' => false, 'message' => 'Certificate ID already exists.']);
                    exit;
                }
            }

            // Insert new certificate
            $stmt = $pdo->prepare("INSERT INTO certificates (Certificate_ID, Resident_ID, Certificate_Type, Purpose, Date_Issued, Issued_by) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$certificate_id, $resident_id, $certificate_type, $purpose, $date_issued, $issued_by]);
            echo json_encode(['success' => true, 'message' => 'Certificate issued successfully', 'Certificate_ID' => $certificate_id]);
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
            SELECT c.Certificate_ID, c.Resident_ID, c.Certificate_Type, c.Purpose, c.Date_Issued, c.Issued_by,
                   r.First_Name, r.Last_Name,
                   o.Position as Issuer_Position
            FROM certificates c
            LEFT JOIN residents r ON c.Resident_ID = r.Resident_ID
            LEFT JOIN officials o ON c.Issued_by = o.Official_ID
            ORDER BY c.Certificate_ID DESC
        ");
        $certificates = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($certificates as &$c) {
            $c['Recipient_Name'] = ($c['First_Name'] && $c['Last_Name']) ? trim($c['First_Name'] . ' ' . $c['Last_Name']) : '—';
            $c['Issuer'] = $c['Issuer_Position'] ?? '—';
            $c['Purpose'] = $c['Purpose'] ?? '—';
            unset($c['First_Name'], $c['Last_Name'], $c['Issuer_Position']);
        }

        echo json_encode($certificates);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

// Handle fetch single certificate request
if (isset($_GET['action']) && $_GET['action'] === 'get') {
    $certificate_id = $_GET['certificate_id'] ?? null;
    if (!$certificate_id) {
        echo json_encode(null);
        exit;
    }

    try {
        $stmt = $pdo->prepare("
            SELECT c.Certificate_ID, c.Resident_ID, c.Certificate_Type, c.Purpose, c.Date_Issued, c.Issued_by,
                   r.First_Name, r.Last_Name
            FROM certificates c
            LEFT JOIN residents r ON c.Resident_ID = r.Resident_ID
            WHERE c.Certificate_ID = ?
        ");
        $stmt->execute([$certificate_id]);
        $certificate = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($certificate) {
            $certificate['Recipient_Name'] = ($certificate['First_Name'] && $certificate['Last_Name']) ? trim($certificate['First_Name'] . ' ' . $certificate['Last_Name']) : '—';
            unset($certificate['First_Name'], $certificate['Last_Name']);
        }

        echo json_encode($certificate);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

// If no input and no action
echo json_encode(['success' => false, 'message' => 'No action specified']);
?>

