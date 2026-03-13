<?php
require_once __DIR__ . '/../../config/database.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/jpeg" href="../logo/logo.jpg">
    <link rel="shortcut icon" type="image/jpeg" href="../logo/logo.jpg">
    <title><?= isset($pageTitle) ? $pageTitle . ' - A to Z Global Link Solutions Admin' : 'A to Z Global Link Solutions - Admin Dashboard' ?></title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: #f8fafc;
            color: #1f2937;
        }
        
        /* Modern Professional Header */
        .header {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            height: 75px;
            background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 2rem;
            z-index: 1000;
            box-shadow: 0 4px 20px rgba(59, 130, 246, 0.08);
            backdrop-filter: blur(10px);
        }
        
        .header-left {
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }
        
        .brand {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        
        .brand-logo {
            width: 40px;
            height: 40px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            background: #ffffff;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }
        
        .brand-logo img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }
        
        .brand-text h1 {
            font-size: 1.4rem;
            font-weight: 800;
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            line-height: 1;
            margin-bottom: 0.125rem;
        }
        
        .brand-text p {
            font-size: 0.75rem;
            color: #6b7280;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .header-right {
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }
        
        /* Profile Dropdown */
        .profile-dropdown {
            position: relative;
        }
        
        .profile-trigger {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.5rem 1rem;
            background: rgba(255, 255, 255, 0.9);
            border: 2px solid rgba(59, 130, 246, 0.1);
            border-radius: 16px;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(59, 130, 246, 0.08);
        }
        
        .profile-trigger:hover {
            background: rgba(255, 255, 255, 1);
            border-color: rgba(59, 130, 246, 0.2);
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(59, 130, 246, 0.15);
        }
        
        .admin-info {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
        }
        
        .admin-name {
            font-size: 0.875rem;
            font-weight: 700;
            color: #1e293b;
            line-height: 1;
        }
        
        .admin-role {
            font-size: 0.75rem;
            color: #64748b;
            font-weight: 500;
        }
        
        .admin-avatar {
            width: 40px;
            height: 40px;
            border-radius: 12px;
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.3rem;
            box-shadow: 0 4px 15px rgba(59, 130, 246, 0.3);
        }
        
        .dropdown-arrow {
            font-size: 0.8rem;
            color: #64748b;
            transition: transform 0.3s ease;
        }
        
        .profile-trigger.active .dropdown-arrow {
            transform: rotate(180deg);
        }
        
        /* Dropdown Menu */
        .dropdown-menu {
            position: absolute;
            top: calc(100% + 10px);
            right: 0;
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
            border: 1px solid rgba(59, 130, 246, 0.1);
            min-width: 280px;
            opacity: 0;
            visibility: hidden;
            transform: translateY(-10px);
            transition: all 0.3s ease;
            z-index: 1000;
            backdrop-filter: blur(10px);
        }
        
        .dropdown-menu.show {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }
        
        .dropdown-header {
            padding: 1.5rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            background: linear-gradient(135deg, #f8fafc, #f1f5f9);
            border-radius: 20px 20px 0 0;
        }
        
        .dropdown-avatar {
            width: 50px;
            height: 50px;
            border-radius: 14px;
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5rem;
            box-shadow: 0 4px 15px rgba(59, 130, 246, 0.3);
        }
        
        .dropdown-info {
            flex: 1;
        }
        
        .dropdown-name {
            font-size: 1rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 0.25rem;
        }
        
        .dropdown-email {
            font-size: 0.875rem;
            color: #64748b;
            font-weight: 500;
        }
        
        .dropdown-divider {
            height: 1px;
            background: linear-gradient(90deg, transparent, rgba(59, 130, 246, 0.1), transparent);
            margin: 0.5rem 0;
        }
        
        .dropdown-items {
            padding: 1rem;
        }
        
        .dropdown-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.875rem 1rem;
            border-radius: 12px;
            text-decoration: none;
            color: #374151;
            font-weight: 500;
            transition: all 0.3s ease;
            margin-bottom: 0.25rem;
        }
        
        .dropdown-item:hover {
            background: linear-gradient(135deg, #f8fafc, #f1f5f9);
            color: #1e293b;
            text-decoration: none;
            transform: translateX(5px);
        }
        
        .dropdown-item.logout {
            color: #dc2626;
        }
        
        .dropdown-item.logout:hover {
            background: linear-gradient(135deg, #fef2f2, #fee2e2);
            color: #b91c1c;
        }
        
        .dropdown-item i {
            width: 18px;
            text-align: center;
            font-size: 1rem;
        }
        
        .sidebar-toggle {
            display: none;
            background: none;
            border: none;
            font-size: 1.25rem;
            color: #6b7280;
            cursor: pointer;
            padding: 0.5rem;
            border-radius: 6px;
            transition: all 0.2s ease;
        }
        
        .sidebar-toggle:hover {
            background: #f3f4f6;
            color: #374151;
        }
        
        /* Mobile Responsive */
        @media (max-width: 768px) {
            .header {
                padding: 0 1rem;
            }
            
            .sidebar-toggle {
                display: block;
            }
            
            .brand-text p {
                display: none;
            }
            
            .admin-info {
                display: none;
            }
            
            .admin-avatar {
                width: 35px;
                height: 35px;
                font-size: 1.1rem;
            }
            
            .dropdown-arrow {
                display: none;
            }
            
            .dropdown-menu {
                min-width: 250px;
                right: -10px;
            }
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="header-left">
            <button class="sidebar-toggle" id="sidebarToggle">
                <i class="fas fa-bars"></i>
            </button>
            <div class="brand">
                <div class="brand-logo">
                    <img src="../logo/logo.jpg" alt="A to Z Global Link Solutions">
                </div>
                <div class="brand-text">
                    <h1>A to Z Global Link Solutions</h1>
                    <p>Admin Dashboard</p>
                </div>
            </div>
        </div>
        
        <div class="header-right">
            <div class="profile-dropdown">
                <div class="profile-trigger" onclick="toggleProfileDropdown()">
                    <div class="admin-info">
                        <span class="admin-name"><?= $_SESSION['admin_username'] ?? 'Admin' ?></span>
                        <span class="admin-role">Administrator</span>
                    </div>
                    <div class="admin-avatar">
                        <i class="fas fa-user-circle"></i>
                    </div>
                    <i class="fas fa-chevron-down dropdown-arrow"></i>
                </div>
                
                <div class="dropdown-menu" id="profileDropdown">
                    <div class="dropdown-header">
                        <div class="dropdown-avatar">
                            <i class="fas fa-user-circle"></i>
                        </div>
                        <div class="dropdown-info">
                            <div class="dropdown-name"><?= $_SESSION['admin_username'] ?? 'Admin' ?></div>
                            <div class="dropdown-email"><?= $_SESSION['admin_email'] ?? 'admin@example.com' ?></div>
                        </div>
                    </div>
                    
                    <div class="dropdown-divider"></div>
                    
                    <div class="dropdown-items">
                        <a href="profile.php" class="dropdown-item">
                            <i class="fas fa-user-cog"></i>
                            <span>Profile Settings</span>
                        </a>
                        <div class="dropdown-divider"></div>
                        <a href="logout.php" class="dropdown-item logout">
                            <i class="fas fa-sign-out-alt"></i>
                            <span>Logout</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const toggle = document.getElementById('sidebarToggle');
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebarOverlay');
        
        // Sidebar functionality
        function openSidebar() {
            sidebar.classList.add('open');
            overlay.classList.add('show');
        }
        
        function closeSidebar() {
            sidebar.classList.remove('open');
            overlay.classList.remove('show');
        }
        
        toggle.addEventListener('click', function() {
            if (sidebar.classList.contains('open')) {
                closeSidebar();
            } else {
                openSidebar();
            }
        });
        
        overlay.addEventListener('click', closeSidebar);
        
        window.addEventListener('resize', function() {
            if (window.innerWidth > 768) {
                closeSidebar();
            }
        });
        
        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            const dropdown = document.getElementById('profileDropdown');
            const trigger = document.querySelector('.profile-trigger');
            
            if (!trigger.contains(e.target) && !dropdown.contains(e.target)) {
                dropdown.classList.remove('show');
                trigger.classList.remove('active');
            }
        });
    });
    
    function toggleProfileDropdown() {
        const dropdown = document.getElementById('profileDropdown');
        const trigger = document.querySelector('.profile-trigger');
        
        dropdown.classList.toggle('show');
        trigger.classList.toggle('active');
    }
    </script>