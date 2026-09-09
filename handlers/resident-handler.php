<?php
session_start();
require_once '../server/config.php';

header('Content-Type: application/json');

// Read JSON input
$input = json_decode(file_get_contents('php://input'), true);

// Handle delete request
if ($input && isset($input['delete']) && $input['delete']) {
    $resident_id = $input['resident_id'] ?? null;
    
    if (!$resident_id) {
        echo json_encode(['success' => false, 'message' => 'Resident ID is required.']);
        exit;
    }
    
    try {
        $stmt = $pdo->prepare("DELETE FROM residents WHERE Resident_ID = ?");
        $stmt->execute([$resident_id]);
        
        if ($stmt->rowCount() > 0) {
            echo json_encode(['success' => true, 'message' => 'Resident deleted successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Resident not found.']);
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

// Handle insert/update requests
if ($input) {
    $resident_id = $input['resident_id'] ?? null;
    $family_id = $input['family_id'] ?? null;
    $first_name = $input['first_name'] ?? '';
    $middle_name = $input['middle_name'] ?? null;
    $last_name = $input['last_name'] ?? '';
    $suffix = $input['suffix'] ?? null;
    $gender = $input['gender'] ?? '';
    $date_of_birth = $input['date_of_birth'] ?? '';
    $civil_status = $input['civil_status'] ?? null;
    $relationship = $input['relationship'] ?? null;
    $occupation = $input['occupation'] ?? null;
    $education = $input['education'] ?? '';
    $contact_number = $input['contact_number'] ?? null;
    $disability = $input['disability'] ?? null;
    $religion = $input['religion'] ?? null;
    $editing = (bool)($input['editing'] ?? false);

    // Validate required fields
    if (!$first_name || !$last_name || !$gender || !$date_of_birth) {
        echo json_encode(['success' => false, 'message' => 'First Name, Last Name, Gender, and Date of Birth are required.']);
        exit;
    }

    // Handle Family_ID - can be null or must exist
    $final_family_id = null;
    if ($family_id) {
        if (is_numeric($family_id)) {
            // Check if family exists
            $check = $pdo->prepare("SELECT Family_ID FROM family WHERE Family_ID = ?");
            $check->execute([$family_id]);
            if ($check->rowCount() > 0) {
                $final_family_id = $family_id;
            } else {
                echo json_encode(['success' => false, 'message' => 'Family ID does not exist.']);
                exit;
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Family ID must be a number.']);
            exit;
        }
    }

    try {
        if ($editing && $resident_id) {
            // Update existing resident
            $stmt = $pdo->prepare("
                UPDATE residents SET 
                    Family_ID = ?, 
                    First_Name = ?, 
                    Middle_Name = ?, 
                    Last_Name = ?, 
                    Suffix = ?, 
                    Gender = ?, 
                    Date_of_Birth = ?, 
                    Civil_Status = ?, 
                    Relationship_to_Head = ?, 
                    Occupation_Employment_Status = ?, 
                    Educational_Attainment = ?, 
                    Contact_Number = ?, 
                    Disability_Status = ?, 
                    Religion = ?
                WHERE Resident_ID = ?
            ");
            $stmt->execute([
                $final_family_id,
                $first_name,
                $middle_name,
                $last_name,
                $suffix,
                $gender,
                $date_of_birth,
                $civil_status,
                $relationship,
                $occupation,
                $education,
                $contact_number,
                $disability,
                $religion,
                $resident_id
            ]);
            echo json_encode(['success' => true, 'message' => 'Resident updated successfully']);
        } else {
            // Insert new resident
            // If resident_id is provided and numeric, use it; otherwise let it auto-increment
            if ($resident_id && is_numeric($resident_id)) {
                // Check if Resident_ID already exists
                $check = $pdo->prepare("SELECT Resident_ID FROM residents WHERE Resident_ID = ?");
                $check->execute([$resident_id]);
                if ($check->rowCount() > 0) {
                    echo json_encode(['success' => false, 'message' => 'Resident ID already exists.']);
                    exit;
                }
                
                $stmt = $pdo->prepare("
                    INSERT INTO residents (
                        Resident_ID, Family_ID, First_Name, Middle_Name, Last_Name, Suffix, 
                        Gender, Date_of_Birth, Civil_Status, Relationship_to_Head, 
                        Occupation_Employment_Status, Educational_Attainment, Contact_Number, 
                        Disability_Status, Religion
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $resident_id,
                    $final_family_id,
                    $first_name,
                    $middle_name,
                    $last_name,
                    $suffix,
                    $gender,
                    $date_of_birth,
                    $civil_status,
                    $relationship,
                    $occupation,
                    $education,
                    $contact_number,
                    $disability,
                    $religion
                ]);
                $new_id = $resident_id;
            } else {
                // Auto-increment
                $stmt = $pdo->prepare("
                    INSERT INTO residents (
                        Family_ID, First_Name, Middle_Name, Last_Name, Suffix, 
                        Gender, Date_of_Birth, Civil_Status, Relationship_to_Head, 
                        Occupation_Employment_Status, Educational_Attainment, Contact_Number, 
                        Disability_Status, Religion
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $final_family_id,
                    $first_name,
                    $middle_name,
                    $last_name,
                    $suffix,
                    $gender,
                    $date_of_birth,
                    $civil_status,
                    $relationship,
                    $occupation,
                    $education,
                    $contact_number,
                    $disability,
                    $religion
                ]);
                $new_id = $pdo->lastInsertId();
            }
            echo json_encode(['success' => true, 'message' => 'Resident added successfully', 'Resident_ID' => $new_id]);
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
            SELECT 
                r.Resident_ID, r.Family_ID, r.First_Name, r.Middle_Name, r.Last_Name, r.Suffix,
                r.Gender, r.Date_of_Birth, r.Civil_Status, r.Relationship_to_Head,
                r.Occupation_Employment_Status, r.Educational_Attainment, r.Contact_Number,
                r.Disability_Status, r.Religion
            FROM residents r
            ORDER BY r.Resident_ID DESC
        ");
        $residents = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($residents as &$r) {
            // Calculate age from Date_of_Birth
            if ($r['Date_of_Birth'] && $r['Date_of_Birth'] !== '0000-00-00') {
                $birthDate = new DateTime($r['Date_of_Birth']);
                $today = new DateTime();
                $age = $today->diff($birthDate)->y;
                $r['Age'] = $age;
            } else {
                $r['Age'] = null;
            }
            
            // Format null values for display
            $r['Middle_Name'] = $r['Middle_Name'] ?? '';
            $r['Suffix'] = $r['Suffix'] ?? '';
            $r['Family_ID'] = $r['Family_ID'] ?? '—';
            $r['Civil_Status'] = $r['Civil_Status'] ?? '—';
            $r['Relationship_to_Head'] = $r['Relationship_to_Head'] ?? '—';
            $r['Occupation_Employment_Status'] = $r['Occupation_Employment_Status'] ?? '—';
            $r['Educational_Attainment'] = $r['Educational_Attainment'] ?? '—';
            $r['Contact_Number'] = $r['Contact_Number'] ?? '—';
            $r['Disability_Status'] = $r['Disability_Status'] ?? 'None';
            $r['Religion'] = $r['Religion'] ?? '—';
        }

        echo json_encode($residents);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

// Handle fetch single resident request
if (isset($_GET['action']) && $_GET['action'] === 'get') {
    $resident_id = $_GET['resident_id'] ?? null;
    if (!$resident_id) {
        echo json_encode(null);
        exit;
    }

    try {
        $stmt = $pdo->prepare("
            SELECT 
                r.Resident_ID, r.Family_ID, r.First_Name, r.Middle_Name, r.Last_Name, r.Suffix,
                r.Gender, r.Date_of_Birth, r.Civil_Status, r.Relationship_to_Head,
                r.Occupation_Employment_Status, r.Educational_Attainment, r.Contact_Number,
                r.Disability_Status, r.Religion
            FROM residents r
            WHERE r.Resident_ID = ?
        ");
        $stmt->execute([$resident_id]);
        $resident = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($resident) {
            // Calculate age
            if ($resident['Date_of_Birth'] && $resident['Date_of_Birth'] !== '0000-00-00') {
                $birthDate = new DateTime($resident['Date_of_Birth']);
                $today = new DateTime();
                $age = $today->diff($birthDate)->y;
                $resident['Age'] = $age;
            } else {
                $resident['Age'] = null;
            }
        }

        echo json_encode($resident);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

// If no input and no action
echo json_encode(['success' => false, 'message' => 'No action specified']);
?>

