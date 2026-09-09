<?php
session_start();
require_once '../server/config.php';

header('Content-Type: application/json');

// Helper function to get time ago
function getTimeAgo($date) {
    if (!$date || $date === '0000-00-00' || $date === null) {
        return 'Recently';
    }
    
    try {
        $dateTime = new DateTime($date);
        $now = new DateTime();
        $diff = $now->diff($dateTime);
        
        if ($diff->days > 30) {
            return $dateTime->format('M d, Y');
        } elseif ($diff->days > 7) {
            $weeks = floor($diff->days / 7);
            return $weeks . ' week' . ($weeks > 1 ? 's' : '') . ' ago';
        } elseif ($diff->days > 0) {
            return $diff->days . ' day' . ($diff->days > 1 ? 's' : '') . ' ago';
        } elseif ($diff->h > 0) {
            return $diff->h . ' hour' . ($diff->h > 1 ? 's' : '') . ' ago';
        } elseif ($diff->i > 0) {
            return $diff->i . ' minute' . ($diff->i > 1 ? 's' : '') . ' ago';
        } else {
            return 'Just now';
        }
    } catch (Exception $e) {
        return 'Recently';
    }
}

// Handle fetch recent activities
if (isset($_GET['action']) && $_GET['action'] === 'activities') {
    try {
        $activities = [];
        
        // Get recent certificates (last 5)
        $stmt = $pdo->query("
            SELECT c.Certificate_ID, c.Certificate_Type, c.Date_Issued,
                   r.First_Name, r.Last_Name
            FROM certificates c
            LEFT JOIN residents r ON c.Resident_ID = r.Resident_ID
            ORDER BY c.Date_Issued DESC, c.Certificate_ID DESC
            LIMIT 5
        ");
        $certificates = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($certificates as $cert) {
            $name = trim(($cert['First_Name'] ?? '') . ' ' . ($cert['Last_Name'] ?? ''));
            if (empty($name)) $name = 'Resident #' . $cert['Certificate_ID'];
            
            $activities[] = [
                'type' => 'certificate',
                'icon' => 'fa-certificate',
                'title' => $cert['Certificate_Type'] . ' issued for ' . $name,
                'date' => $cert['Date_Issued'],
                'time' => getTimeAgo($cert['Date_Issued'])
            ];
        }
        
        // Get recent residents (last 3) - use Resident_ID as proxy for registration order
        $stmt = $pdo->query("
            SELECT Resident_ID, First_Name, Last_Name
            FROM residents
            ORDER BY Resident_ID DESC
            LIMIT 3
        ");
        $residents = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($residents as $res) {
            $name = trim(($res['First_Name'] ?? '') . ' ' . ($res['Last_Name'] ?? ''));
            if (empty($name)) $name = 'Resident #' . $res['Resident_ID'];
            
            $activities[] = [
                'type' => 'resident',
                'icon' => 'fa-user-plus',
                'title' => 'New resident registered - ' . $name,
                'date' => date('Y-m-d'), // Approximate since we don't have created_at
                'time' => 'Recently'
            ];
        }
        
        // Get recent families (last 2)
        $stmt = $pdo->query("
            SELECT f.Family_ID, f.Family_Name
            FROM family f
            ORDER BY f.Family_ID DESC
            LIMIT 2
        ");
        $families = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($families as $fam) {
            $activities[] = [
                'type' => 'family',
                'icon' => 'fa-users',
                'title' => 'New family registered - ' . ($fam['Family_Name'] ?? 'Family #' . $fam['Family_ID']) . ' Family',
                'date' => date('Y-m-d'),
                'time' => 'Recently'
            ];
        }
        
        // Sort by date (most recent first) and limit to 5
        usort($activities, function($a, $b) {
            $dateA = strtotime($a['date']);
            $dateB = strtotime($b['date']);
            return $dateB - $dateA;
        });
        
        $activities = array_slice($activities, 0, 5);
        
        echo json_encode($activities);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

// Handle fetch statistics
if (isset($_GET['action']) && $_GET['action'] === 'stats') {
    try {
        $stats = [];
        
        // Total families
        $stats['total_families'] = $pdo->query("SELECT COUNT(*) FROM family")->fetchColumn();
        
        // Total residents
        $stats['total_residents'] = $pdo->query("SELECT COUNT(*) FROM residents")->fetchColumn();
        
        // Total officials
        $stats['total_officials'] = $pdo->query("SELECT COUNT(*) FROM officials")->fetchColumn();
        
        // Active officials (Term_end >= today)
        $stats['active_officials'] = $pdo->query("
            SELECT COUNT(*) FROM officials 
            WHERE Term_end >= CURDATE()
        ")->fetchColumn();
        
        // Inactive officials
        $stats['inactive_officials'] = $stats['total_officials'] - $stats['active_officials'];
        
        // Total certificates
        $stats['total_certificates'] = $pdo->query("SELECT COUNT(*) FROM certificates")->fetchColumn();
        
        // Certificates this month
        $stats['certificates_this_month'] = $pdo->query("
            SELECT COUNT(*) FROM certificates 
            WHERE MONTH(Date_Issued) = MONTH(CURRENT_DATE()) 
            AND YEAR(Date_Issued) = YEAR(CURRENT_DATE())
        ")->fetchColumn();
        
        // Families this month - approximate by checking if any new residents were added to families
        // Since we don't have created_at, we'll use a simpler approach
        // Count families that have at least one resident (this is just a placeholder)
        // In a real system, you'd track when families were created
        $stats['families_this_month'] = 0; // Can't accurately determine without created_at field
        
        // Recent residents this month (approximate - using Resident_ID as proxy)
        $stats['residents_this_month'] = 0; // Can't determine from schema without created_at field
        
        echo json_encode($stats);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

// If no action specified
echo json_encode(['success' => false, 'message' => 'No action specified']);
?>

