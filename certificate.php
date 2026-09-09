<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$username = $_SESSION['username'] ?? "User";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Certificate Information - Barangay System</title>
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
            overflow-x: hidden;
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
            overflow-y: auto;
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
            width: calc(100% - var(--sidebar-width));
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
            display: flex;
            flex-direction: column;
            overflow: hidden;
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

        .btn-warning {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: white;
        }

        .btn-warning:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 18px rgba(217, 119, 6, 0.4);
        }

        .btn-danger {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: white;
        }

        .btn-danger:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 18px rgba(220, 38, 38, 0.4);
        }

        .btn-success {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
        }

        .btn-success:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 18px rgba(5, 150, 105, 0.4);
        }

        /* Enhanced Table Design */
        .table-wrapper {
            flex: 1;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            position: relative;
        }

        .table-wrapper::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--accent-color) 100%);
            border-radius: 16px 16px 0 0;
            z-index: 10;
        }

        .table-container {
            overflow: auto;
            flex: 1;
            border-radius: 16px;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            background-color: white;
        }

        .data-table th {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--accent-color) 100%);
            color: white;
            padding: 15px 20px;
            text-align: left;
            position: sticky;
            top: 0;
            font-weight: 600;
            font-size: 15px;
            white-space: nowrap;
        }

        .data-table td {
            padding: 15px 20px;
            border-bottom: 1px solid #e2e8f0;
            color: #4b5563;
            white-space: nowrap;
        }

        .data-table tr {
            transition: all 0.3s ease;
        }

        .data-table tr:hover {
            background-color: #f0f9ff;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }

        .data-table tr:last-child td {
            border-bottom: none;
        }

        /* Enhanced Filter Section */
        .filter-section {
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

        .filter-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--accent-color) 100%);
        }

        .filter-header {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
        }

        .filter-header i {
            margin-right: 10px;
            color: var(--primary-color);
            font-size: 20px;
        }

        .filter-header h3 {
            color: var(--primary-color);
            font-size: 20px;
            font-weight: 600;
        }

        .filter-row {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            margin-bottom: 20px;
        }

        .filter-group {
            flex: 1;
            min-width: 200px;
        }

        .filter-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--primary-color);
            font-size: 14px;
        }

        .filter-group input, .filter-group select {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s ease;
            background: white;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
        }

        .filter-group input:focus, .filter-group select:focus {
            border-color: var(--accent-color);
            outline: none;
            box-shadow: 0 0 0 3px rgba(41, 137, 216, 0.1);
        }

        .filter-actions {
            display: flex;
            gap: 15px;
            justify-content: flex-end;
        }

        /* Action buttons in table */
        .action-buttons-cell {
            display: flex;
            gap: 8px;
        }

        .action-btn {
            padding: 8px 12px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 13px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 5px;
            transition: all 0.3s ease;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            white-space: nowrap;
        }

        .action-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
        }

        .status-issued {
            color: #10b981;
            font-weight: 600;
            background: rgba(16, 185, 129, 0.1);
            padding: 5px 10px;
            border-radius: 20px;
            display: inline-block;
        }

        .status-pending {
            color: #f59e0b;
            font-weight: 600;
            background: rgba(245, 158, 11, 0.1);
            padding: 5px 10px;
            border-radius: 20px;
            display: inline-block;
        }

        .status-cancelled {
            color: #ef4444;
            font-weight: 600;
            background: rgba(239, 68, 68, 0.1);
            padding: 5px 10px;
            border-radius: 20px;
            display: inline-block;
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 2000;
            justify-content: center;
            align-items: center;
        }

        .modal-content {
            background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
            border-radius: 16px;
            width: 90%;
            max-width: 700px;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            position: relative;
        }

        .modal-content::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--accent-color) 100%);
            border-radius: 16px 16px 0 0;
        }

        .modal-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--accent-color) 100%);
            color: white;
            padding: 25px 30px;
            border-radius: 16px 16px 0 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-header h3 {
            margin: 0;
            font-size: 22px;
            font-weight: 700;
        }

        .close-btn {
            background: none;
            border: none;
            color: white;
            font-size: 24px;
            cursor: pointer;
            padding: 0;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            transition: all 0.3s ease;
        }

        .close-btn:hover {
            background: rgba(255, 255, 255, 0.2);
        }

        .modal-body {
            padding: 30px;
        }

        .form-row {
            display: flex;
            flex-wrap: wrap;
            margin: 0 -10px;
        }

        .form-group {
            flex: 1 0 300px;
            margin: 0 10px 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--primary-color);
        }

        .form-group input, .form-group select, .form-group textarea {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s ease;
            background: white;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
        }

        .form-group input:focus, .form-group select:focus, .form-group textarea:focus {
            border-color: var(--accent-color);
            outline: none;
            box-shadow: 0 0 0 3px rgba(41, 137, 216, 0.1);
        }

        .form-group textarea {
            resize: vertical;
            min-height: 80px;
        }

        .modal-footer {
            padding: 20px 30px;
            border-top: 1px solid #e2e8f0;
            display: flex;
            justify-content: flex-end;
            gap: 15px;
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
                width: calc(100% - 80px);
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
            
            .filter-row {
                flex-direction: column;
            }
            
            .table-container {
                overflow-x: auto;
            }
            
            .data-table {
                min-width: 1000px;
            }
            
            .modal-content {
                width: 95%;
                margin: 20px;
            }
        }

        @media (max-width: 480px) {
            .content {
                padding: 20px 15px;
            }
            
            .filter-section {
                padding: 20px;
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
            
            .action-buttons-cell {
                flex-direction: column;
                gap: 5px;
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
            <div class="menu-item" onclick="location.href='info.php'">
                <i class="fas fa-info-circle"></i>
                <span>Barangay Info</span>
            </div>
            <div class="menu-item active" onclick="location.href='certificate.php'">
                <i class="fas fa-certificate"></i>
                <span>Certificate Information</span>
            </div>
        </div>
    </div>

    <div class="main-content">
        <div class="header">
            <div class="header-left">
                <h1>Certificate Information</h1>
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
                <h2 class="module-title">Certificate Information</h2>
                <div class="action-buttons">
                    <button class="btn btn-primary" id="add-certificate-btn">
                        <i class="fas fa-plus"></i> Issue Certificate
                    </button>
                    <button class="btn btn-secondary" id="filter-toggle-btn">
                        <i class="fas fa-filter"></i> Filter Certificates
                    </button>
                </div>
            </div>

            <!-- Filter Section -->
            <div class="filter-section" id="filter-section" style="display: none;">
                <div class="filter-header">
                    <i class="fas fa-filter"></i>
                    <h3>Filter Certificates</h3>
                </div>
                <div class="filter-row">
                    <div class="filter-group">
                        <label for="filter-certificate-id">Certificate ID</label>
                        <input type="text" id="filter-certificate-id" placeholder="Enter Certificate ID">
                    </div>
                    <div class="filter-group">
                        <label for="filter-recipient">Recipient Name</label>
                        <input type="text" id="filter-recipient" placeholder="Search by recipient">
                    </div>
                    <div class="filter-group">
                        <label for="filter-type">Certificate Type</label>
                        <select id="filter-type">
                            <option value="">All Types</option>
                            <option value="Barangay Clearance">Barangay Clearance</option>
                            <option value="Indigency Certificate">Indigency Certificate</option>
                            <option value="Residency Certificate">Residency Certificate</option>
                            <option value="Business Permit">Business Permit</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                </div>
                <div class="filter-row">
                    <div class="filter-group">
                        <label for="filter-date-from">Date From</label>
                        <input type="date" id="filter-date-from">
                    </div>
                    <div class="filter-group">
                        <label for="filter-date-to">Date To</label>
                        <input type="date" id="filter-date-to">
                    </div>
                </div>
                <div class="filter-actions">
                    <button class="btn btn-secondary" id="clear-filters">
                        <i class="fas fa-times"></i> Clear Filters
                    </button>
                    <button class="btn btn-primary" id="apply-filters">
                        <i class="fas fa-check"></i> Apply Filters
                    </button>
                </div>
            </div>
            
            <div class="table-wrapper">
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Certificate ID</th>
                                <th>Type</th>
                                <th>Recipient</th>
                                <th>Purpose</th>
                                <th>Date Issued</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="certificates-table-body">
                            <!-- Certificates will be dynamically added here -->
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="footer">
                <p>© 2024 Barangay San Isidro Information System. All rights reserved.</p>
            </div>
        </div>
    </div>

    <!-- Add/Edit Certificate Modal -->
    <div class="modal" id="certificate-modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="modal-title">Issue New Certificate</h3>
                <button class="close-btn" id="close-modal">&times;</button>
            </div>
            <div class="modal-body">
                <form id="certificate-form">
                    <input type="hidden" id="edit-certificate-id">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="certificate-id">Certificate ID <span class="optional">(Auto-generated if empty)</span></label>
                            <input type="number" id="certificate-id" name="certificate_id" min="1" placeholder="Leave empty for auto-generation">
                        </div>
                        <div class="form-group">
                            <label for="resident-id">Resident ID *</label>
                            <input type="number" id="resident-id" name="resident_id" required min="1" placeholder="Enter Resident ID">
                            <small style="color: #6b7280; font-size: 12px; display: block; margin-top: 5px;">The resident must exist in the system</small>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="certificate-type">Certificate Type *</label>
                            <select id="certificate-type" name="certificate_type" required>
                                <option value="">Select Type</option>
                                <option value="Barangay Clearance">Barangay Clearance</option>
                                <option value="Indigency Certificate">Indigency Certificate</option>
                                <option value="Residency Certificate">Residency Certificate</option>
                                <option value="Business Permit">Business Permit</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="date-issued">Date Issued *</label>
                            <input type="date" id="date-issued" name="date_issued" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="purpose">Purpose</label>
                            <textarea id="purpose" name="purpose" placeholder="Enter purpose for the certificate"></textarea>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="issued-by">Issued By (Official ID) <span class="optional">(Optional)</span></label>
                            <input type="number" id="issued-by" name="issued_by" min="1" placeholder="Enter Official ID">
                            <small style="color: #6b7280; font-size: 12px; display: block; margin-top: 5px;">Leave empty if not applicable</small>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" id="cancel-certificate">Cancel</button>
                <button class="btn btn-primary" id="save-certificate">Save Certificate</button>
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


        // Modal functionality
        const modal = document.getElementById('certificate-modal');
        const addCertificateBtn = document.getElementById('add-certificate-btn');
        const closeModalBtn = document.getElementById('close-modal');
        const cancelBtn = document.getElementById('cancel-certificate');
        const saveBtn = document.getElementById('save-certificate');
        const certificateForm = document.getElementById('certificate-form');
        const modalTitle = document.getElementById('modal-title');

        // Filter functionality
        const filterToggleBtn = document.getElementById('filter-toggle-btn');
        const filterSection = document.getElementById('filter-section');
        const applyFiltersBtn = document.getElementById('apply-filters');
        const clearFiltersBtn = document.getElementById('clear-filters');

        let currentFilters = {};
        let editingCertificateId = null;

        // Open modal for adding new certificate
        addCertificateBtn.addEventListener('click', function() {
            editingCertificateId = null;
            modalTitle.textContent = 'Issue New Certificate';
            document.getElementById('certificate-id').value = ''; // Will be auto-generated if empty
            document.getElementById('date-issued').value = new Date().toISOString().split('T')[0];
            certificateForm.reset();
            document.getElementById('date-issued').value = new Date().toISOString().split('T')[0]; // Set today's date
            modal.style.display = 'flex';
        });

        // Close modal
        function closeModal() {
            modal.style.display = 'none';
            editingCertificateId = null;
        }

        closeModalBtn.addEventListener('click', closeModal);
        cancelBtn.addEventListener('click', closeModal);

        // Save certificate (both add and edit)
        saveBtn.addEventListener('click', function() {
            // Validate form
            if (!certificateForm.checkValidity()) {
                certificateForm.reportValidity();
                return;
            }

            // Get form data
            const certificateIdValue = document.getElementById('certificate-id').value;
            const formData = {
                certificate_id: certificateIdValue || null, // null if empty (auto-generate)
                resident_id: document.getElementById('resident-id').value,
                certificate_type: document.getElementById('certificate-type').value,
                purpose: document.getElementById('purpose').value || null,
                date_issued: document.getElementById('date-issued').value,
                issued_by: document.getElementById('issued-by').value || null,
                editing: editingCertificateId ? 1 : 0
            };

            fetch('handlers/certificate-handler.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(formData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    loadCertificates();
                    closeModal();
                    alert(data.message);
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Something went wrong.');
            });
        });

        // Edit certificate
        function editCertificate(certificateId) {
            fetch(`handlers/certificate-handler.php?action=get&certificate_id=${certificateId}`)
                .then(res => res.json())
                .then(certificate => {
                    if (certificate) {
                        editingCertificateId = certificateId;
                        modalTitle.textContent = 'Edit Certificate';
                        
                        // Fill form with certificate data
                        document.getElementById('certificate-id').value = certificate.Certificate_ID;
                        document.getElementById('resident-id').value = certificate.Resident_ID;
                        document.getElementById('certificate-type').value = certificate.Certificate_Type;
                        document.getElementById('purpose').value = certificate.Purpose || '';
                        document.getElementById('date-issued').value = certificate.Date_Issued;
                        document.getElementById('issued-by').value = certificate.Issued_by || '';
                        
                        // Show modal
                        modal.style.display = 'flex';
                    } else {
                        alert('Certificate not found.');
                    }
                })
                .catch(err => {
                    console.error('Error fetching certificate:', err);
                    alert('Failed to fetch certificate details.');
                });
        }

        // Delete certificate
        function deleteCertificate(certificateId) {
            if (confirm('Are you sure you want to delete this certificate?')) {
                fetch('handlers/certificate-handler.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ certificate_id: certificateId, delete: 1 })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        loadCertificates();
                        alert(data.message);
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(err => {
                    console.error('Error deleting certificate:', err);
                    alert('Failed to delete certificate.');
                });
            }
        }

        // Print certificate
        function printCertificate(certificateId) {
            fetch(`handlers/certificate-handler.php?action=get&certificate_id=${certificateId}`)
                .then(res => res.json())
                .then(certificate => {
                    if (certificate) {
                        // In a real application, this would generate a printable certificate
                        alert(`Printing certificate: ${certificate.Certificate_ID}\nRecipient: ${certificate.Recipient_Name}\nType: ${certificate.Certificate_Type}`);
                        // window.print() could be called here for actual printing
                    } else {
                        alert('Certificate not found.');
                    }
                })
                .catch(err => {
                    console.error('Error fetching certificate:', err);
                    alert('Failed to fetch certificate for printing.');
                });
        }

        // Filter functionality
        filterToggleBtn.addEventListener('click', function() {
            filterSection.style.display = filterSection.style.display === 'none' ? 'block' : 'none';
        });

        applyFiltersBtn.addEventListener('click', function() {
            currentFilters = {
                certificateId: document.getElementById('filter-certificate-id').value.toLowerCase(),
                recipient: document.getElementById('filter-recipient').value.toLowerCase(),
                type: document.getElementById('filter-type').value,
                dateFrom: document.getElementById('filter-date-from').value,
                dateTo: document.getElementById('filter-date-to').value
            };
            loadCertificates();
        });

        clearFiltersBtn.addEventListener('click', function() {
            document.getElementById('filter-certificate-id').value = '';
            document.getElementById('filter-recipient').value = '';
            document.getElementById('filter-type').value = '';
            document.getElementById('filter-date-from').value = '';
            document.getElementById('filter-date-to').value = '';
            currentFilters = {};
            loadCertificates();
        });

        // Load certificates from database with filters
        function loadCertificates() {
            fetch('handlers/certificate-handler.php?action=fetch')
                .then(res => res.json())
                .then(certificates => {
                    const tableBody = document.getElementById('certificates-table-body');
                    tableBody.innerHTML = '';

                    // Apply filters
                    const filteredCertificates = certificates.filter(certificate => {
                        if (currentFilters.certificateId && !certificate.Certificate_ID.toString().toLowerCase().includes(currentFilters.certificateId)) {
                            return false;
                        }
                        if (currentFilters.recipient && !certificate.Recipient_Name.toLowerCase().includes(currentFilters.recipient)) {
                            return false;
                        }
                        if (currentFilters.type && certificate.Certificate_Type !== currentFilters.type) {
                            return false;
                        }
                        if (currentFilters.dateFrom && certificate.Date_Issued < currentFilters.dateFrom) {
                            return false;
                        }
                        if (currentFilters.dateTo && certificate.Date_Issued > currentFilters.dateTo) {
                            return false;
                        }
                        return true;
                    });

                    filteredCertificates.forEach(certificate => {
                        const row = document.createElement('tr');
                        row.innerHTML = `
                            <td>${certificate.Certificate_ID}</td>
                            <td>${certificate.Certificate_Type}</td>
                            <td>${certificate.Recipient_Name}</td>
                            <td>${certificate.Purpose}</td>
                            <td>${certificate.Date_Issued}</td>
                            <td><span class="status-issued">Issued</span></td>
                            <td>
                                <div class="action-buttons-cell">
                                    <button class="action-btn btn-warning" onclick="editCertificate(${certificate.Certificate_ID})">
                                        <i class="fas fa-edit"></i> Edit
                                    </button>
                                    <button class="action-btn btn-success" onclick="printCertificate(${certificate.Certificate_ID})">
                                        <i class="fas fa-print"></i> Print
                                    </button>
                                    <button class="action-btn btn-danger" onclick="deleteCertificate(${certificate.Certificate_ID})">
                                        <i class="fas fa-trash"></i> Delete
                                    </button>
                                </div>
                            </td>
                        `;
                        tableBody.appendChild(row);
                    });
                })
                .catch(err => {
                    console.error('Error loading certificates:', err);
                    alert('Failed to load certificates.');
                });
        }

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
                loadCertificates();
            }
        });
    </script>
</body>
</html>