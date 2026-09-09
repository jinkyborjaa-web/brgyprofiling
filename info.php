<?php
session_start();

$isGuest = !isset($_SESSION['user_id']);

require_once __DIR__ . '/server/config.php';

$username = $_SESSION['username'] ?? "Guest visitor";

$totalResidents = 0;
$totalFamilies = 0;
$totalOfficials = 0;
$totalCertificates = 0;
$barangayCaptain = 'Not set';

try {
    $totalResidents = (int)$pdo->query("SELECT COUNT(*) FROM residents")->fetchColumn();
    $totalFamilies = (int)$pdo->query("SELECT COUNT(*) FROM family")->fetchColumn();
    $totalOfficials = (int)$pdo->query("SELECT COUNT(*) FROM officials")->fetchColumn();
    $totalCertificates = (int)$pdo->query("SELECT COUNT(*) FROM certificates")->fetchColumn();

    $captainStmt = $pdo->query("
        SELECT r.First_Name, r.Last_Name, r.Suffix
        FROM officials o
        INNER JOIN residents r ON o.Resident_ID = r.Resident_ID
        WHERE LOWER(o.Position) LIKE '%captain%'
        ORDER BY o.Term_end DESC
        LIMIT 1
    ");

    if ($captain = $captainStmt->fetch(PDO::FETCH_ASSOC)) {
        $parts = array_filter([
            $captain['First_Name'] ?? '',
            $captain['Last_Name'] ?? '',
            $captain['Suffix'] ?? ''
        ]);
        $barangayCaptain = trim(implode(' ', $parts));
    }
} catch (PDOException $e) {
    error_log('Info page stats error: ' . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Barangay Info - Barangay System</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        :root {
            --primary-color: #1e5799;
            --primary-light: #4a86d4;
            --secondary-color: #16457a;
            --accent-color: #2989d8;
            --accent-light: #5ca8f0;
            --sidebar-width: 280px;
            --header-height: 70px;
            --sidebar-bg: #1a2530;
            --sidebar-hover: #2c3e50;
            --success-color: #10b981;
            --warning-color: #f59e0b;
            --info-color: #3b82f6;
        }

        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #e4e8f0 100%);
            display: flex;
            min-height: 100vh;
            color: #333;
            overflow-x: hidden; /* Prevent horizontal scroll */
        }

        /* Enhanced Sidebar */
        .sidebar {
            width: var(--sidebar-width);
            background: linear-gradient(180deg, var(--sidebar-bg) 0%, #15202b 100%);
            color: white;
            height: 100vh;
            position: fixed;
            transition: all 0.3s ease;
            z-index: 1000;
            box-shadow: 4px 0 15px rgba(0, 0, 0, 0.1);
            overflow-y: auto; /* Allow sidebar scrolling if needed */
        }

        .sidebar-header {
            padding: 25px 20px;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            text-align: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            position: relative;
            overflow: hidden;
        }

        .sidebar-header::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0) 70%);
        }

        .sidebar-header h2 {
            font-size: 22px;
            margin-bottom: 8px;
            font-weight: 700;
            position: relative;
        }

        .sidebar-header p {
            font-size: 13px;
            opacity: 0.9;
            font-weight: 300;
            position: relative;
        }

        .sidebar-menu {
            padding: 20px 0;
        }

        .menu-item {
            padding: 15px 25px;
            display: flex;
            align-items: center;
            cursor: pointer;
            transition: all 0.3s ease;
            border-left: 4px solid transparent;
            margin: 5px 15px;
            border-radius: 8px;
            position: relative;
            overflow: hidden;
        }

        .menu-item::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.1), transparent);
            transition: left 0.5s;
        }

        .menu-item:hover::before {
            left: 100%;
        }

        .menu-item:hover {
            background-color: var(--sidebar-hover);
            border-left: 4px solid var(--accent-light);
            transform: translateX(5px);
        }

        .menu-item.active {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--accent-color) 100%);
            border-left: 4px solid white;
            box-shadow: 0 4px 12px rgba(30, 87, 153, 0.3);
        }

        .menu-item i {
            margin-right: 15px;
            font-size: 18px;
            width: 20px;
            text-align: center;
            transition: transform 0.3s ease;
        }

        .menu-item:hover i {
            transform: scale(1.1);
        }

        .menu-item span {
            font-size: 15px;
            font-weight: 500;
        }

        /* Mobile menu toggle */
        .menu-toggle {
            display: none;
            position: fixed;
            top: 15px;
            left: 15px;
            z-index: 1100;
            background: var(--primary-color);
            color: white;
            border: none;
            border-radius: 5px;
            width: 40px;
            height: 40px;
            font-size: 20px;
            cursor: pointer;
            box-shadow: 0 2px 10px rgba(0,0,0,0.2);
        }

        /* Enhanced Main Content */
        .main-content {
            flex: 1;
            margin-left: var(--sidebar-width);
            transition: all 0.3s ease;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .header {
            height: var(--header-height);
            background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 30px;
            position: sticky;
            top: 0;
            z-index: 100;
            border-bottom: 1px solid #e2e8f0;
        }

        .header-left h1 {
            font-size: 26px;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--accent-color) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            font-weight: 700;
        }

        .header-right {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 8px 15px;
            background: white;
            border-radius: 50px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
        }

        .user-info:hover {
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            transform: translateY(-2px);
        }

        .user-avatar {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--accent-color) 100%);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 16px;
            box-shadow: 0 4px 8px rgba(30, 87, 153, 0.3);
        }

        .logout-btn {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 4px 12px rgba(220, 38, 38, 0.3);
        }

        .logout-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 18px rgba(220, 38, 38, 0.4);
        }

        .content {
            padding: 30px;
            flex: 1;
        }

        .module-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #e2e8f0;
        }

        .module-title {
            font-size: 32px;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--accent-color) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            font-weight: 700;
            position: relative;
        }

        .module-title::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 0;
            width: 60px;
            height: 4px;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--accent-color) 100%);
            border-radius: 2px;
        }

        .action-buttons {
            display: flex;
            gap: 15px;
        }

        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--accent-color) 100%);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 18px rgba(30, 87, 153, 0.4);
        }

        .btn-secondary {
            background: linear-gradient(135deg, #6b7280 0%, #4b5563 100%);
            color: white;
        }

        .btn-secondary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 18px rgba(75, 85, 99, 0.4);
        }

        /* Enhanced Card Design */
        .card {
            background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            padding: 30px;
            max-width: 100%; /* Changed from 900px to prevent overflow */
            margin-bottom: 30px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            position: relative;
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--accent-color) 100%);
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.12);
        }

        .card-header {
            display: flex;
            align-items: center;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid #e2e8f0;
        }

        .card-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--accent-color) 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            color: white;
            font-size: 20px;
            box-shadow: 0 4px 12px rgba(30, 87, 153, 0.3);
        }

        .card h3 {
            font-size: 24px;
            color: var(--primary-color);
            font-weight: 700;
            margin: 0;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 25px;
        }

        .info-item {
            display: flex;
            align-items: flex-start;
            padding: 15px;
            background: rgba(255, 255, 255, 0.7);
            border-radius: 12px;
            transition: all 0.3s ease;
            border-left: 4px solid transparent;
        }

        .info-item:hover {
            background: white;
            border-left: 4px solid var(--accent-color);
            transform: translateX(5px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }

        .info-item strong {
            display: inline-block;
            min-width: 180px;
            color: var(--primary-color);
            font-weight: 600;
        }

        .info-item span {
            color: #4b5563;
            flex: 1;
        }

        .contact-section {
            background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
            border-radius: 12px;
            padding: 25px;
            margin-top: 20px;
            border: 1px solid rgba(59, 130, 246, 0.2);
        }

        .contact-section h4 {
            color: var(--primary-color);
            margin-bottom: 15px;
            font-size: 18px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .contact-section h4::before {
            content: '📞';
            font-size: 20px;
        }

        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-top: 30px;
        }

        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            text-align: center;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            border: 1px solid #e2e8f0;
            transition: all 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.08);
        }

        .stat-number {
            font-size: 32px;
            font-weight: 700;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--accent-color) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 5px;
        }

        .stat-label {
            color: #6b7280;
            font-size: 14px;
            font-weight: 500;
        }

        /* Enhanced Footer */
        .footer {
            text-align: center;
            padding: 20px;
            margin-top: 40px;
            color: #6b7280;
            font-size: 14px;
            border-top: 1px solid #e2e8f0;
        }

        /* Mobile Responsive */
        @media (max-width: 1024px) {
            .sidebar {
                width: 80px;
                transform: translateX(0);
            }
            
            .sidebar-header h2, .sidebar-header p, .menu-item span {
                display: none;
            }
            
            .menu-item {
                justify-content: center;
                padding: 15px;
                margin: 5px 10px;
            }
            
            .menu-item i {
                margin-right: 0;
                font-size: 20px;
            }
            
            .main-content {
                margin-left: 80px;
            }
            
            .info-grid {
                grid-template-columns: 1fr;
            }
            
            .info-item strong {
                min-width: 140px;
            }
        }

        @media (max-width: 768px) {
            .menu-toggle {
                display: block;
            }
            
            .sidebar {
                transform: translateX(-100%);
                width: 280px;
            }
            
            .sidebar.active {
                transform: translateX(0);
            }
            
            .sidebar.active ~ .main-content::before {
                content: '';
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0,0,0,0.5);
                z-index: 999;
            }
            
            .main-content {
                margin-left: 0;
                width: 100%;
            }
            
            .header {
                padding: 0 20px;
            }
            
            .module-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }
            
            .action-buttons {
                width: 100%;
                justify-content: flex-start;
                flex-wrap: wrap;
            }
            
            .stats-container {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .info-item {
                flex-direction: column;
                gap: 5px;
            }
            
            .info-item strong {
                min-width: auto;
            }
        }

        @media (max-width: 480px) {
            .content {
                padding: 20px 15px;
            }
            
            .card {
                padding: 20px;
            }
            
            .stats-container {
                grid-template-columns: 1fr;
            }
            
            .header-right {
                gap: 10px;
            }
            
            .user-info span {
                display: none;
            }
            
            .btn {
                padding: 10px 15px;
                font-size: 13px;
            }
        }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <button class="menu-toggle" id="menuToggle">
        <i class="fas fa-bars"></i>
    </button>

    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <h2>Community Profiling</h2>
            <p>Information Management System</p>
        </div>
        <div class="sidebar-menu">
            <div class="menu-item" onclick="location.href='dashboard.php'">
                <i class="fas fa-tachometer-alt"></i>
                <span>Dashboard</span>
            </div>
            <div class="menu-item" onclick="location.href='family.php'">
                <i class="fas fa-users"></i>
                <span>Family Information</span>
            </div>
            <div class="menu-item" onclick="location.href='resident.php'">
                <i class="fas fa-user"></i>
                <span>Resident Information</span>
            </div>
            <div class="menu-item" onclick="location.href='official.php'">
                <i class="fas fa-user-tie"></i>
                <span>Barangay Official</span>
            </div>
            <div class="menu-item active" onclick="location.href='info.php'">
                <i class="fas fa-info-circle"></i>
                <span>Barangay Info</span>
            </div>
            <div class="menu-item" onclick="location.href='certificate.php'">
                <i class="fas fa-certificate"></i>
                <span>Certificate Information</span>
            </div>
        </div>
    </div>

    <div class="main-content">
        <div class="header">
            <div class="header-left">
                <h1>Barangay Information</h1>
            </div>
            <div class="header-right">
                <div class="user-info">
                    <div class="user-avatar" id="user-avatar">AD</div>
                    <span id="username-display">Admin User</span>
                </div>
                <button class="logout-btn" id="logout-btn">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </button>
            </div>
        </div>

        <div class="content">
            <div class="module-header">
                <h2 class="module-title">Barangay Information</h2>
                <div class="action-buttons">
                    <button class="btn btn-primary" id="edit-info-btn">
                        <i class="fas fa-edit"></i> Edit Information
                    </button>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="stats-container">
                <div class="stat-card">
                    <div class="stat-number"><?= number_format($totalResidents); ?></div>
                    <div class="stat-label">Total Residents</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?= number_format($totalFamilies); ?></div>
                    <div class="stat-label">Registered Families</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?= number_format($totalOfficials); ?></div>
                    <div class="stat-label">Barangay Officials</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?= number_format($totalCertificates); ?></div>
                    <div class="stat-label">Certificates Issued</div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <div class="card-icon">
                        <i class="fas fa-landmark"></i>
                    </div>
                    <h3>Barangay Profile</h3>
                </div>
                
                <div class="info-grid">
                    <div>
                        <div class="info-item">
                            <strong>Barangay Name:</strong>
                            <span>Barangay San Isidro</span>
                        </div>
                        <div class="info-item">
                            <strong>Barangay Captain:</strong>
                            <span><?= htmlspecialchars($barangayCaptain) ?: 'Not set'; ?></span>
                        </div>
                        <div class="info-item">
                            <strong>Population:</strong>
                            <span><?= number_format($totalResidents); ?> residents</span>
                        </div>
                        <div class="info-item">
                            <strong>Number of Families:</strong>
                            <span><?= number_format($totalFamilies); ?> families</span>
                        </div>
                    </div>
                    <div>
                        <div class="info-item">
                            <strong>Area:</strong>
                            <span>45.2 hectares</span>
                        </div>
                        <div class="info-item">
                            <strong>District:</strong>
                            <span>District 5</span>
                        </div>
                        <div class="info-item">
                            <strong>City/Municipality:</strong>
                            <span>City of San Jose</span>
                        </div>
                        <div class="info-item">
                            <strong>Province:</strong>
                            <span>Laguna</span>
                        </div>
                    </div>
                </div>

                <div class="contact-section">
                    <h4>Contact Information</h4>
                    <div class="info-grid">
                        <div>
                            <div class="info-item">
                                <strong>Address:</strong>
                                <span>San Isidro Street, City of San Jose, Laguna</span>
                            </div>
                            <div class="info-item">
                                <strong>Telephone:</strong>
                                <span>(049) 123-4567</span>
                            </div>
                        </div>
                        <div>
                            <div class="info-item">
                                <strong>Email:</strong>
                                <span>sanisidro.barangay@cityofsanjose.gov.ph</span>
                            </div>
                            <div class="info-item">
                                <strong>Facebook Page:</strong>
                                <span>facebook.com/BarangaySanIsidroOfficial</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div style="margin-top: 25px;">
                    <h4 style="color: var(--primary-color); margin-bottom: 15px; display: flex; align-items: center; gap: 10px;">
                        <i class="fas fa-history"></i> Barangay History
                    </h4>
                    <p style="color: #6b7280; line-height: 1.7; background: rgba(255,255,255,0.7); padding: 20px; border-radius: 12px; border-left: 4px solid var(--accent-color);">
                        Barangay San Isidro was established in 1952 and named after Saint Isidore the Laborer, 
                        the patron saint of farmers. Originally an agricultural community, it has grown into 
                        a vibrant residential area while maintaining its rich cultural heritage and strong 
                        community spirit. The barangay takes pride in its annual fiesta celebration every May 15th, 
                        bringing together residents for cultural events, games, and community feasts.
                    </p>
                </div>
            </div>

            <div class="footer">
                <p>© 2024 Barangay San Isidro Information System. All rights reserved.</p>
            </div>
        </div>
    </div>

    <script>
    // Check if user is logged in
    function checkAuth() {
        // Define current user from PHP
        const currentUser = {
            username: "<?php echo $username; ?>"
        };
        return currentUser;
    }

    // Update user interface with current user info
    function updateUserInterface(user) {
        if (user) {
            const firstLetter = user.username.charAt(0).toUpperCase();
            document.getElementById('user-avatar').textContent = firstLetter;
            document.getElementById('username-display').textContent = user.username;
        }
    }

    // Handle logout
    document.getElementById('logout-btn').addEventListener('click', function() {
        localStorage.removeItem('currentUser');
        window.location.href = 'index.php';
    });

    // Edit information functionality
    document.getElementById('edit-info-btn').addEventListener('click', function() {
        alert('Edit information feature would open a modal form here.');
        // In the full implementation, this would open the edit modal
    });

    // Mobile menu toggle
    document.getElementById('menuToggle').addEventListener('click', function() {
        document.getElementById('sidebar').classList.toggle('active');
    });

    // Close sidebar when clicking outside on mobile
    document.addEventListener('click', function(event) {
        const sidebar = document.getElementById('sidebar');
        const menuToggle = document.getElementById('menuToggle');
        
        if (window.innerWidth <= 768 && 
            sidebar.classList.contains('active') && 
            !sidebar.contains(event.target) && 
            !menuToggle.contains(event.target)) {
            sidebar.classList.remove('active');
        }
    });

    // Initialize the page
    document.addEventListener('DOMContentLoaded', function() {
        const user = checkAuth();
        if (user) {
            updateUserInterface(user);
        }
    });
</script>

</body>
</html>