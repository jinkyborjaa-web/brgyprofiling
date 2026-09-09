<?php
// Increase session lifetime BEFORE session_start()
ini_set('session.gc_maxlifetime', 36000); // 10 hours
ini_set('session.cookie_lifetime', 36000); // 10 hours

session_start();

// Check login
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

if (($_SESSION['role'] ?? 'user') !== 'admin') {
    header("Location: user-dashboard.php");
    exit();
}

$username = $_SESSION['username']; // store username for JS

require_once "server/config.php"; // your DB connection

// TOTAL FAMILIES
$total_families = $pdo->query("SELECT COUNT(*) FROM family")->fetchColumn();

// TOTAL RESIDENTS
$total_residents = $pdo->query("SELECT COUNT(*) FROM residents")->fetchColumn();

// TOTAL OFFICIALS
$total_officials = $pdo->query("SELECT COUNT(*) FROM officials")->fetchColumn();

// TOTAL CERTIFICATES ISSUED
$total_certificates = $pdo->query("SELECT COUNT(*) FROM certificates")->fetchColumn();

// CERTIFICATES THIS MONTH
$certificates_this_month = $pdo->query("
    SELECT COUNT(*) FROM certificates 
    WHERE MONTH(Date_Issued) = MONTH(CURRENT_DATE()) 
    AND YEAR(Date_Issued) = YEAR(CURRENT_DATE())
")->fetchColumn();

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Barangay System</title>
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

        /* Welcome Message */
        .welcome-message {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--accent-color) 100%);
            color: white;
            padding: 30px;
            border-radius: 16px;
            margin-bottom: 30px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(30, 87, 153, 0.3);
            position: relative;
            overflow: hidden;
        }

        .welcome-message::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0) 70%);
        }

        .welcome-message h2 {
            margin-bottom: 10px;
            font-size: 28px;
            position: relative;
        }

        .welcome-message p {
            opacity: 0.9;
            font-size: 18px;
            position: relative;
        }

        /* Enhanced Card Design */
        .card {
            background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            padding: 30px;
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

        /* Dashboard Cards */
        .dashboard-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 25px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            border-radius: 16px;
            padding: 25px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            border: 1px solid #e2e8f0;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--accent-color) 100%);
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.12);
        }

        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            font-size: 24px;
            color: white;
        }

        .stat-icon.family {
            background: linear-gradient(135deg, #4caf50 0%, #2e7d32 100%);
        }

        .stat-icon.resident {
            background: linear-gradient(135deg, #2196f3 0%, #0d47a1 100%);
        }

        .stat-icon.official {
            background: linear-gradient(135deg, #ff9800 0%, #e65100 100%);
        }

        .stat-icon.certificate {
            background: linear-gradient(135deg, #9c27b0 0%, #4a148c 100%);
        }

        .stat-number {
            font-size: 36px;
            font-weight: 700;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--accent-color) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 5px;
        }

        .stat-label {
            color: var(--primary-color);
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 10px;
        }

        .stat-footer {
            font-size: 14px;
            color: #6b7280;
            font-weight: 500;
        }

        /* Activity Section */
        .activity-section {
            background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            padding: 30px;
            margin-bottom: 30px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            position: relative;
            overflow: hidden;
        }

        .activity-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--accent-color) 100%);
        }

        .section-title {
            font-size: 24px;
            color: var(--primary-color);
            margin-bottom: 20px;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .activity-list {
            list-style-type: none;
        }

        .activity-item {
            padding: 15px 0;
            border-bottom: 1px solid #f0f0f0;
            display: flex;
            align-items: center;
            transition: all 0.3s ease;
        }

        .activity-item:hover {
            background: rgba(255, 255, 255, 0.7);
            border-radius: 8px;
            padding-left: 10px;
            padding-right: 10px;
        }

        .activity-item:last-child {
            border-bottom: none;
        }

        .activity-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--accent-color) 100%);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            font-size: 16px;
            box-shadow: 0 4px 8px rgba(30, 87, 153, 0.3);
        }

        .activity-details {
            flex: 1;
        }

        .activity-title {
            font-weight: 600;
            margin-bottom: 5px;
            color: #333;
        }

        .activity-time {
            font-size: 14px;
            color: #6b7280;
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
            
            .dashboard-cards {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 480px) {
            .content {
                padding: 20px 15px;
            }
            
            .card, .activity-section {
                padding: 20px;
            }
            
            .dashboard-cards {
                grid-template-columns: 1fr;
            }
            
            .header-right {
                gap: 10px;
            }
            
            .user-info span {
                display: none;
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
            <div class="menu-item active" onclick="location.href='dashboard.php'">
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
            <div class="menu-item" onclick="location.href='info.php'">
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
                <h1>Dashboard</h1>
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
                <h2 class="module-title">Dashboard Overview</h2>
            </div>

            <!-- Welcome Message -->
            <div class="welcome-message" id="welcome-message">
                <h2>Welcome to Barangay System!</h2>
                <p>You are logged in as: <strong id="logged-in-user">Admin User</strong></p>
            </div>

            <!-- Statistics Cards -->
            <div class="dashboard-cards">

                <!-- TOTAL FAMILIES -->
                <div class="stat-card">
                    <div class="stat-icon family">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-number" id="stat-families"><?= number_format($total_families); ?></div>
                    <div class="stat-label">Total Families</div>
                    <div class="stat-footer" id="stat-families-month">Loading...</div>
                </div>

                <!-- TOTAL RESIDENTS -->
                <div class="stat-card">
                    <div class="stat-icon resident">
                        <i class="fas fa-user"></i>
                    </div>
                    <div class="stat-number" id="stat-residents"><?= number_format($total_residents); ?></div>
                    <div class="stat-label">Total Residents</div>
                    <div class="stat-footer" id="stat-residents-month">All registered</div>
                </div>

                <!-- TOTAL OFFICIALS -->
                <div class="stat-card">
                    <div class="stat-icon official">
                        <i class="fas fa-user-tie"></i>
                    </div>
                    <div class="stat-number" id="stat-officials"><?= number_format($total_officials); ?></div>
                    <div class="stat-label">Barangay Officials</div>
                    <div class="stat-footer" id="stat-officials-month">Loading...</div>
                </div>

                <!-- TOTAL CERTIFICATES -->
                <div class="stat-card">
                    <div class="stat-icon certificate">
                        <i class="fas fa-certificate"></i>
                    </div>
                    <div class="stat-number" id="stat-certificates"><?= number_format($total_certificates); ?></div>
                    <div class="stat-label">Certificates Issued</div>
                    <div class="stat-footer" id="stat-certificates-month"><?= $certificates_this_month > 0 ? '+' . number_format($certificates_this_month) . ' this month' : 'No certificates this month'; ?></div>
                </div>

            </div>

            <!-- Recent Activities -->
            <div class="activity-section">
                <h3 class="section-title">
                    <i class="fas fa-history"></i> Recent Activities
                </h3>
                <ul class="activity-list" id="activity-list">
                    <li class="activity-item">
                        <div class="activity-icon">
                            <i class="fas fa-spinner fa-spin"></i>
                        </div>
                        <div class="activity-details">
                            <div class="activity-title">Loading activities...</div>
                            <div class="activity-time">Please wait</div>
                        </div>
                    </li>
                </ul>
            </div>

            <!-- Quick Actions -->
            <div class="card">
                <div class="card-header">
                    <div class="card-icon">
                        <i class="fas fa-bolt"></i>
                    </div>
                    <h3>Quick Actions</h3>
                </div>
                <div class="info-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;">
                    <div class="info-item" style="cursor: pointer; padding: 20px; background: white; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); transition: all 0.3s ease;" 
                         onmouseover="this.style.transform='translateY(-3px)'; this.style.boxShadow='0 4px 10px rgba(0,0,0,0.15)'"
                         onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 2px 5px rgba(0,0,0,0.1)'"
                         onclick="location.href='family.php'">
                        <strong style="display: block; margin-bottom: 5px; color: var(--primary-color);"><i class="fas fa-users"></i> Add Family</strong>
                        <span style="color: #6b7280; font-size: 14px;">Register new family</span>
                    </div>
                    <div class="info-item" style="cursor: pointer; padding: 20px; background: white; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); transition: all 0.3s ease;"
                         onmouseover="this.style.transform='translateY(-3px)'; this.style.boxShadow='0 4px 10px rgba(0,0,0,0.15)'"
                         onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 2px 5px rgba(0,0,0,0.1)'"
                         onclick="location.href='resident.php'">
                        <strong style="display: block; margin-bottom: 5px; color: var(--primary-color);"><i class="fas fa-user-plus"></i> Add Resident</strong>
                        <span style="color: #6b7280; font-size: 14px;">Register new resident</span>
                    </div>
                    <div class="info-item" style="cursor: pointer; padding: 20px; background: white; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); transition: all 0.3s ease;"
                         onmouseover="this.style.transform='translateY(-3px)'; this.style.boxShadow='0 4px 10px rgba(0,0,0,0.15)'"
                         onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 2px 5px rgba(0,0,0,0.1)'"
                         onclick="location.href='certificate.php'">
                        <strong style="display: block; margin-bottom: 5px; color: var(--primary-color);"><i class="fas fa-file-certificate"></i> Issue Certificate</strong>
                        <span style="color: #6b7280; font-size: 14px;">Create new certificate</span>
                    </div>
                    <div class="info-item" style="cursor: pointer; padding: 20px; background: white; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); transition: all 0.3s ease;"
                         onmouseover="this.style.transform='translateY(-3px)'; this.style.boxShadow='0 4px 10px rgba(0,0,0,0.15)'"
                         onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 2px 5px rgba(0,0,0,0.1)'"
                         onclick="location.href='info.php'">
                        <strong style="display: block; margin-bottom: 5px; color: var(--primary-color);"><i class="fas fa-chart-bar"></i> View Reports</strong>
                        <span style="color: #6b7280; font-size: 14px;">Generate system reports</span>
                    </div>
                </div>
            </div>

            <div class="footer">
                <p>© 2024 Barangay San Isidro Information System. All rights reserved.</p>
            </div>
        </div>
    </div>

    <script>
    // Get username from PHP session
    const username = "<?php echo htmlspecialchars($username); ?>";

    // Update user interface with current user info
    function updateUserInterface(user) {
        if (user) {
            const firstLetter = user.charAt(0).toUpperCase();
            document.getElementById('user-avatar').textContent = firstLetter;
            document.getElementById('username-display').textContent = user;
            document.getElementById('logged-in-user').textContent = user;
        }
    }

    // Handle logout
    document.getElementById('logout-btn').addEventListener('click', function() {
        fetch('bootstrap/logout.php', { method: 'POST' })
            .then(() => window.location.href = 'index.php');
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

    // Load recent activities
    function loadRecentActivities() {
        fetch('handlers/dashboard-handler.php?action=activities')
            .then(res => res.json())
            .then(activities => {
                const activityList = document.getElementById('activity-list');
                activityList.innerHTML = '';

                if (activities.length === 0) {
                    activityList.innerHTML = `
                        <li class="activity-item">
                            <div class="activity-icon">
                                <i class="fas fa-info-circle"></i>
                            </div>
                            <div class="activity-details">
                                <div class="activity-title">No recent activities</div>
                                <div class="activity-time">Start by adding families, residents, or certificates</div>
                            </div>
                        </li>
                    `;
                    return;
                }

                activities.forEach(activity => {
                    const item = document.createElement('li');
                    item.className = 'activity-item';
                    item.innerHTML = `
                        <div class="activity-icon">
                            <i class="fas ${activity.icon}"></i>
                        </div>
                        <div class="activity-details">
                            <div class="activity-title">${activity.title}</div>
                            <div class="activity-time">${activity.time}</div>
                        </div>
                    `;
                    activityList.appendChild(item);
                });
            })
            .catch(err => {
                console.error('Error loading activities:', err);
                const activityList = document.getElementById('activity-list');
                activityList.innerHTML = `
                    <li class="activity-item">
                        <div class="activity-icon">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        <div class="activity-details">
                            <div class="activity-title">Failed to load activities</div>
                            <div class="activity-time">Please refresh the page</div>
                        </div>
                    </li>
                `;
            });
    }

    // Load statistics
    function loadStatistics() {
        fetch('handlers/dashboard-handler.php?action=stats')
            .then(res => res.json())
            .then(stats => {
                // Update certificates this month
                if (stats.certificates_this_month !== undefined) {
                    const footer = document.getElementById('stat-certificates-month');
                    if (footer) {
                        footer.textContent = stats.certificates_this_month > 0 
                            ? '+' + stats.certificates_this_month + ' this month' 
                            : 'No certificates this month';
                    }
                }
                
                // Update families footer
                const familiesFooter = document.getElementById('stat-families-month');
                if (familiesFooter) {
                    familiesFooter.textContent = 'All registered families';
                }
                
                // Update officials footer
                const officialsFooter = document.getElementById('stat-officials-month');
                if (officialsFooter && stats.active_officials !== undefined && stats.inactive_officials !== undefined) {
                    if (stats.inactive_officials === 0) {
                        officialsFooter.textContent = 'All Active';
                    } else {
                        officialsFooter.textContent = stats.inactive_officials + ' inactive';
                    }
                }
            })
            .catch(err => {
                console.error('Error loading statistics:', err);
                // Set default values on error
                const familiesFooter = document.getElementById('stat-families-month');
                if (familiesFooter) {
                    familiesFooter.textContent = 'All registered families';
                }
                const officialsFooter = document.getElementById('stat-officials-month');
                if (officialsFooter) {
                    officialsFooter.textContent = 'Active Members';
                }
            });
    }

    // Initialize the page
    document.addEventListener('DOMContentLoaded', function() {
        if (!username) {
            window.location.href = 'index.php';
        } else {
            updateUserInterface(username);
            loadRecentActivities();
            loadStatistics();
        }
    });
</script>

</body>
</html>