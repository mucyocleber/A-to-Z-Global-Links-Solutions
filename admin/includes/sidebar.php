<?php require_once __DIR__ . '/permissions.php'; ?>
<!-- Sidebar -->
<aside class="sidebar" id="sidebar">
    <nav class="sidebar-nav">
        <!-- Main Navigation -->
        <?php if (hasPermission('view_dashboard')): ?>
        <div class="nav-section">
            <div class="nav-section-title">
                <i class="fas fa-tachometer-alt"></i>
                <span>Overview</span>
            </div>
            <ul class="nav-menu">
                <li>
                    <a href="dashboard.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : '' ?>">
                        <div class="nav-icon">
                            <i class="fas fa-chart-pie"></i>
                        </div>
                        <span class="nav-text">Dashboard</span>
                    </a>
                </li>
            </ul>
        </div>
        <?php endif; ?>
        
        <!-- Application Management -->
        <?php if (hasAnyPermission(['view_applications', 'view_users'])): ?>
        <div class="nav-section">
            <div class="nav-section-title">
                <i class="fas fa-file-contract"></i>
                <span>Application Management</span>
            </div>
            <ul class="nav-menu">
                <?php if (hasPermission('view_applications')): ?>
                <li>
                    <a href="applications.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'applications.php' ? 'active' : '' ?>">
                        <div class="nav-icon">
                            <i class="fas fa-file-alt"></i>
                        </div>
                        <span class="nav-text">Applications</span>
                        <?php
                        try {
                            $pdo = getConnection();
                            $pending = $pdo->query("SELECT COUNT(*) FROM applications WHERE status_id IN (1,2,3)")->fetchColumn();
                            if ($pending > 0) echo '<span class="nav-badge">' . $pending . '</span>';
                        } catch (Exception $e) {}
                        ?>
                    </a>
                </li>
                <?php endif; ?>
                <?php if (hasPermission('view_users')): ?>
                <li>
                    <a href="users.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'users.php' ? 'active' : '' ?>">
                        <div class="nav-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <span class="nav-text">Users</span>
                    </a>
                </li>
                <?php endif; ?>
            </ul>
        </div>
        <?php endif; ?>
        
        <!-- Service Management -->
        <?php if (hasAnyPermission(['view_services', 'view_categories'])): ?>
        <div class="nav-section">
            <div class="nav-section-title">
                <i class="fas fa-cogs"></i>
                <span>Service Management</span>
            </div>
            <ul class="nav-menu">
                <?php if (hasPermission('view_services')): ?>
                <li>
                    <a href="services.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'services.php' ? 'active' : '' ?>">
                        <div class="nav-icon">
                            <i class="fas fa-concierge-bell"></i>
                        </div>
                        <span class="nav-text">Services</span>
                    </a>
                </li>
                <?php endif; ?>
                <?php if (hasPermission('view_categories')): ?>
                <li>
                    <a href="service-categories.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'service-categories.php' ? 'active' : '' ?>">
                        <div class="nav-icon">
                            <i class="fas fa-tags"></i>
                        </div>
                        <span class="nav-text">Categories</span>
                    </a>
                </li>
                <?php endif; ?>
            </ul>
        </div>
        <?php endif; ?>
        
        <!-- Location Management -->
        <?php if (hasAnyPermission(['view_countries', 'view_destinations'])): ?>
        <div class="nav-section">
            <div class="nav-section-title">
                <i class="fas fa-map-marked-alt"></i>
                <span>Location Management</span>
            </div>
            <ul class="nav-menu">
                <?php if (hasPermission('view_countries')): ?>
                <li>
                    <a href="countries.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'countries.php' ? 'active' : '' ?>">
                        <div class="nav-icon">
                            <i class="fas fa-globe"></i>
                        </div>
                        <span class="nav-text">Countries</span>
                    </a>
                </li>
                <?php endif; ?>
                <?php if (hasPermission('view_destinations')): ?>
                <li>
                    <a href="destination-countries.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'destination-countries.php' ? 'active' : '' ?>">
                        <div class="nav-icon">
                            <i class="fas fa-plane-departure"></i>
                        </div>
                        <span class="nav-text">Destinations</span>
                    </a>
                </li>
                <?php endif; ?>
            </ul>
        </div>
        <?php endif; ?>
        
        <!-- Role Management (Super Admin Only) -->
        <?php if (hasPermission('view_roles')): ?>
        <div class="nav-section">
            <div class="nav-section-title">
                <i class="fas fa-shield-alt"></i>
                <span>Access Control</span>
            </div>
            <ul class="nav-menu">
                <li>
                    <a href="roles.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'roles.php' ? 'active' : '' ?>">
                        <div class="nav-icon">
                            <i class="fas fa-user-shield"></i>
                        </div>
                        <span class="nav-text">Roles & Permissions</span>
                    </a>
                </li>
                <li>
                    <a href="admin-users.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'admin-users.php' ? 'active' : '' ?>">
                        <div class="nav-icon">
                            <i class="fas fa-user-tie"></i>
                        </div>
                        <span class="nav-text">Admin Users</span>
                    </a>
                </li>
            </ul>
        </div>
        <?php endif; ?>
        
        <!-- Account Settings -->
        <div class="nav-section">
            <div class="nav-section-title">
                <i class="fas fa-user-cog"></i>
                <span>Account Settings</span>
            </div>
            <ul class="nav-menu">
                <li>
                    <a href="profile.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'profile.php' ? 'active' : '' ?>">
                        <div class="nav-icon">
                            <i class="fas fa-user-edit"></i>
                        </div>
                        <span class="nav-text">Profile Settings</span>
                    </a>
                </li>
            </ul>
        </div>
    </nav>
    
    <div class="sidebar-footer">
        <div class="footer-info">
            <div class="footer-icon">
                <i class="fas fa-shield-alt"></i>
            </div>
            <div class="footer-text">
                <span class="footer-title">Secure Admin</span>
                <span class="footer-subtitle">v2.0.1</span>
            </div>
        </div>
    </div>
</aside>

<div class="sidebar-overlay" id="sidebarOverlay"></div>

<style>
/* Modern Sidebar Styles */
.sidebar {
    position: fixed;
    left: 0;
    top: 75px;
    width: 280px;
    height: calc(100vh - 75px);
    background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
    border-right: 1px solid rgba(59, 130, 246, 0.1);
    z-index: 200;
    overflow-y: auto;
    box-shadow: 4px 0 20px rgba(59, 130, 246, 0.08);
    display: flex;
    flex-direction: column;
}

/* Sidebar Navigation */
.sidebar-nav {
    flex: 1;
    padding: 1.5rem 0;
    overflow-y: auto;
}

.nav-section {
    margin-bottom: 2rem;
}

.nav-section-title {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem 1.5rem;
    margin-bottom: 0.5rem;
    font-size: 0.8rem;
    font-weight: 700;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    border-bottom: 1px solid rgba(59, 130, 246, 0.05);
}

.nav-section-title i {
    font-size: 0.9rem;
    color: #3b82f6;
}

.nav-menu {
    list-style: none;
    padding: 0;
    margin: 0;
}

.nav-menu li {
    margin-bottom: 0.25rem;
}

.nav-link {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem 1.5rem;
    color: #64748b;
    text-decoration: none;
    font-weight: 500;
    font-size: 0.9rem;
    transition: all 0.3s ease;
    border-radius: 0 25px 25px 0;
    margin-right: 1rem;
    position: relative;
    overflow: hidden;
}

.nav-link::before {
    content: '';
    position: absolute;
    left: 0;
    top: 0;
    width: 4px;
    height: 100%;
    background: linear-gradient(135deg, #3b82f6, #1d4ed8);
    transform: scaleY(0);
    transition: transform 0.3s ease;
}

.nav-link:hover {
    background: linear-gradient(135deg, rgba(59, 130, 246, 0.08), rgba(59, 130, 246, 0.05));
    color: #1e293b;
    transform: translateX(8px);
}

.nav-link:hover::before {
    transform: scaleY(1);
}

.nav-link.active {
    background: linear-gradient(135deg, rgba(59, 130, 246, 0.15), rgba(59, 130, 246, 0.08));
    color: #1d4ed8;
    font-weight: 600;
    transform: translateX(8px);
    box-shadow: 0 4px 15px rgba(59, 130, 246, 0.2);
}

.nav-link.active::before {
    transform: scaleY(1);
}

.nav-icon {
    width: 20px;
    height: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1rem;
    flex-shrink: 0;
}

.nav-text {
    flex: 1;
    font-size: 0.9rem;
}

.nav-badge {
    background: linear-gradient(135deg, #ef4444, #dc2626);
    color: white;
    font-size: 0.7rem;
    font-weight: 700;
    padding: 0.2rem 0.5rem;
    border-radius: 12px;
    min-width: 20px;
    height: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 2px 8px rgba(239, 68, 68, 0.3);
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.1); }
}

/* Sidebar Footer */
.sidebar-footer {
    padding: 1.5rem;
    border-top: 1px solid rgba(59, 130, 246, 0.1);
    background: linear-gradient(135deg, #f8fafc, #f1f5f9);
}

.footer-info {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.footer-icon {
    width: 35px;
    height: 35px;
    border-radius: 10px;
    background: linear-gradient(135deg, #10b981, #059669);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1rem;
    box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3);
}

.footer-text {
    display: flex;
    flex-direction: column;
}

.footer-title {
    font-size: 0.85rem;
    font-weight: 600;
    color: #1e293b;
    line-height: 1;
}

.footer-subtitle {
    font-size: 0.7rem;
    color: #64748b;
    font-weight: 500;
}

/* Sidebar Overlay */
.sidebar-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.6);
    backdrop-filter: blur(4px);
    z-index: 150;
    display: none;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.sidebar-overlay.show {
    display: block;
    opacity: 1;
}

/* Scrollbar Styling */
.sidebar::-webkit-scrollbar {
    width: 6px;
}

.sidebar::-webkit-scrollbar-track {
    background: rgba(59, 130, 246, 0.05);
}

.sidebar::-webkit-scrollbar-thumb {
    background: rgba(59, 130, 246, 0.2);
    border-radius: 3px;
}

.sidebar::-webkit-scrollbar-thumb:hover {
    background: rgba(59, 130, 246, 0.3);
}

/* Mobile Responsive */
@media (max-width: 768px) {
    .sidebar {
        transform: translateX(-100%);
        transition: transform 0.3s ease;
        width: 100vw;
        max-width: 320px;
    }
    
    .sidebar.open {
        transform: translateX(0);
    }
    
    .nav-link {
        padding: 1.2rem 1.5rem;
        font-size: 1rem;
    }
    
    .nav-section-title {
        padding: 1rem 1.5rem;
        font-size: 0.85rem;
    }
    
    .sidebar-header {
        padding: 1.25rem 1.5rem;
    }
    
    .sidebar-footer {
        padding: 1.25rem 1.5rem;
    }
}

@media (max-width: 480px) {
    .sidebar {
        width: 100vw;
        max-width: none;
    }
}
</style>