<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

require_once '../config/database.php';
require_once 'includes/permissions.php';
requirePermission('view_dashboard');

// Get dashboard statistics
try {
    $pdo = getConnection();
    
    // Applications stats
    $totalApplications = $pdo->query("SELECT COUNT(*) FROM applications")->fetchColumn();
    $pendingApplications = $pdo->query("SELECT COUNT(*) FROM applications WHERE status_id IN (2,3)")->fetchColumn();
    $approvedApplications = $pdo->query("SELECT COUNT(*) FROM applications WHERE status_id = 6")->fetchColumn();
    $rejectedApplications = $pdo->query("SELECT COUNT(*) FROM applications WHERE status_id = 7")->fetchColumn();
    
    // Users stats
    $totalUsers = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    $newUsersToday = $pdo->query("SELECT COUNT(*) FROM users WHERE DATE(created_at) = CURDATE()")->fetchColumn();
    
    // Services stats
    $totalServices = $pdo->query("SELECT COUNT(*) FROM services WHERE is_active = 1")->fetchColumn();
    $totalCategories = $pdo->query("SELECT COUNT(*) FROM service_categories WHERE is_active = 1")->fetchColumn();
    $totalCountries = $pdo->query("SELECT COUNT(*) FROM countries WHERE is_active = 1")->fetchColumn();
    $totalDestinations = $pdo->query("SELECT COUNT(*) FROM destination_countries")->fetchColumn();
    
    // Payment stats
    $totalRevenue = $pdo->query("SELECT COALESCE(SUM(amount), 0) FROM payments WHERE payment_status IN ('completed', 'approved')")->fetchColumn();
    $pendingPayments = $pdo->query("SELECT COUNT(*) FROM payments WHERE payment_status = 'pending'")->fetchColumn();
    
    // Recent applications
    $recentApplications = $pdo->query("
        SELECT a.*, 
               CONCAT(u.first_name, ' ', u.last_name) as full_name,
               s.name as service_name,
               st.status_name
        FROM applications a 
        JOIN users u ON a.user_id = u.id 
        JOIN services s ON a.service_id = s.id 
        JOIN application_statuses st ON a.status_id = st.id
        ORDER BY a.submitted_at DESC 
        LIMIT 5
    ")->fetchAll();
    
} catch (Exception $e) {
    $totalApplications = $pendingApplications = $approvedApplications = $rejectedApplications = 0;
    $totalUsers = $newUsersToday = $totalServices = $totalRevenue = $pendingPayments = 0;
    $totalCategories = $totalCountries = $totalDestinations = 0;
    $recentApplications = [];
}

$pageTitle = 'Dashboard';
?>

<?php include 'includes/header.php'; ?>
<?php include 'includes/sidebar.php'; ?>

<main class="main-content">
    <div class="dashboard-header">
        <h1>Dashboard Overview</h1>
        <p>Welcome back, <?= htmlspecialchars($_SESSION['admin_username']) ?>! Here's what's happening today.</p>
    </div>
    
    <!-- Statistics Cards -->
    <div class="stats-grid">
        <div class="stat-card applications">
            <div class="stat-icon">
                <i class="fas fa-file-alt"></i>
            </div>
            <div class="stat-content">
                <h3><?= number_format($totalApplications) ?></h3>
                <p>Total Applications</p>
                <div class="stat-trend">
                    <span class="pending"><?= $pendingApplications ?> pending</span>
                </div>
            </div>
        </div>
        
        <div class="stat-card users">
            <div class="stat-icon">
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-content">
                <h3><?= number_format($totalUsers) ?></h3>
                <p>Total Users</p>
                <div class="stat-trend">
                    <span class="new"><?= $newUsersToday ?> new today</span>
                </div>
            </div>
        </div>
        
        <div class="stat-card services">
            <div class="stat-icon">
                <i class="fas fa-cogs"></i>
            </div>
            <div class="stat-content">
                <h3><?= number_format($totalServices) ?></h3>
                <p>Active Services</p>
                <div class="stat-trend">
                    <span class="active"><?= $totalCategories ?> categories</span>
                </div>
            </div>
        </div>
        
        <div class="stat-card countries">
            <div class="stat-icon">
                <i class="fas fa-globe"></i>
            </div>
            <div class="stat-content">
                <h3><?= number_format($totalCountries) ?></h3>
                <p>Countries</p>
                <div class="stat-trend">
                    <span class="active"><?= $totalDestinations ?> destinations</span>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Application Status Overview -->
    <div class="overview-grid">
        <div class="overview-card">
            <div class="card-header">
                <h3>Application Status</h3>
                <a href="applications.php" class="view-all">View All</a>
            </div>
            <div class="status-overview">
                <div class="status-item approved">
                    <div class="status-count"><?= $approvedApplications ?></div>
                    <div class="status-label">Approved</div>
                </div>
                <div class="status-item pending">
                    <div class="status-count"><?= $pendingApplications ?></div>
                    <div class="status-label">Pending</div>
                </div>
                <div class="status-item rejected">
                    <div class="status-count"><?= $rejectedApplications ?></div>
                    <div class="status-label">Rejected</div>
                </div>
            </div>
        </div>
        
        <div class="overview-card">
            <div class="card-header">
                <h3>Recent Applications</h3>
                <a href="applications.php" class="view-all">View All</a>
            </div>
            <div class="recent-list">
                <?php if (empty($recentApplications)): ?>
                    <div class="empty-state">
                        <i class="fas fa-inbox"></i>
                        <p>No recent applications</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($recentApplications as $app): ?>
                        <div class="recent-item">
                            <div class="recent-info">
                                <div class="recent-name"><?= htmlspecialchars($app['full_name']) ?></div>
                                <div class="recent-service"><?= htmlspecialchars($app['service_name']) ?></div>
                            </div>
                            <div class="recent-status">
                                <span class="status-badge status-<?= getStatusClass($app['status_id']) ?>">
                                    <?= htmlspecialchars($app['status_name']) ?>
                                </span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Quick Actions -->
    <div class="quick-actions">
        <h3>Quick Actions</h3>
        <div class="actions-grid">
            <a href="applications.php" class="action-card">
                <i class="fas fa-plus"></i>
                <span>Add Application</span>
            </a>
            <a href="users.php" class="action-card">
                <i class="fas fa-user-plus"></i>
                <span>Add User</span>
            </a>
            <a href="services.php" class="action-card">
                <i class="fas fa-cog"></i>
                <span>Manage Services</span>
            </a>
            <a href="applications.php?status=2" class="action-card">
                <i class="fas fa-eye"></i>
                <span>Review Pending</span>
            </a>
        </div>
    </div>
</main>

<style>
.main-content {
    margin-left: 260px;
    margin-top: 75px;
    padding: 2rem;
    background: #f8fafc;
    min-height: calc(100vh - 75px);
}

.dashboard-header {
    margin-bottom: 2rem;
}

.dashboard-header h1 {
    font-size: 2rem;
    font-weight: 700;
    color: #111827;
    margin-bottom: 0.5rem;
}

.dashboard-header p {
    color: #6b7280;
    font-size: 1rem;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.stat-card {
    background: white;
    padding: 1.5rem;
    border-radius: 12px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    border: 1px solid #e5e7eb;
    display: flex;
    align-items: center;
    gap: 1rem;
    transition: all 0.2s;
}

.stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

.stat-icon {
    width: 60px;
    height: 60px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    color: white;
}

.stat-card.applications .stat-icon { background: linear-gradient(135deg, #3b82f6, #1e40af); }
.stat-card.users .stat-icon { background: linear-gradient(135deg, #10b981, #059669); }
.stat-card.revenue .stat-icon { background: linear-gradient(135deg, #f59e0b, #d97706); }
.stat-card.services .stat-icon { background: linear-gradient(135deg, #8b5cf6, #7c3aed); }
.stat-card.countries .stat-icon { background: linear-gradient(135deg, #ef4444, #dc2626); }

.stat-content h3 {
    font-size: 1.875rem;
    font-weight: 700;
    color: #111827;
    margin-bottom: 0.25rem;
}

.stat-content p {
    color: #6b7280;
    font-size: 0.875rem;
    font-weight: 500;
    margin-bottom: 0.5rem;
}

.stat-trend {
    font-size: 0.75rem;
    font-weight: 500;
}

.stat-trend .pending { color: #f59e0b; }
.stat-trend .new { color: #10b981; }
.stat-trend .active { color: #3b82f6; }

.overview-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.overview-card {
    background: white;
    border-radius: 12px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    border: 1px solid #e5e7eb;
    overflow: hidden;
}

.card-header {
    padding: 1.5rem;
    border-bottom: 1px solid #f3f4f6;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.card-header h3 {
    font-size: 1.125rem;
    font-weight: 600;
    color: #111827;
}

.view-all {
    color: #3b82f6;
    text-decoration: none;
    font-size: 0.875rem;
    font-weight: 500;
}

.view-all:hover {
    color: #2563eb;
}

.status-overview {
    display: flex;
    padding: 1.5rem;
    gap: 2rem;
}

.status-item {
    text-align: center;
    flex: 1;
}

.status-count {
    font-size: 2rem;
    font-weight: 700;
    margin-bottom: 0.25rem;
}

.status-item.approved .status-count { color: #10b981; }
.status-item.pending .status-count { color: #f59e0b; }
.status-item.rejected .status-count { color: #ef4444; }

.status-label {
    font-size: 0.875rem;
    color: #6b7280;
    font-weight: 500;
}

.recent-list {
    padding: 1rem 1.5rem 1.5rem;
}

.recent-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.75rem 0;
    border-bottom: 1px solid #f3f4f6;
}

.recent-item:last-child {
    border-bottom: none;
}

.recent-name {
    font-weight: 500;
    color: #111827;
    font-size: 0.875rem;
}

.recent-service {
    font-size: 0.75rem;
    color: #6b7280;
    margin-top: 0.125rem;
}

.status-badge {
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 500;
}

.status-submitted { background: #dbeafe; color: #1e40af; }
.status-under_review { background: #fef3c7; color: #92400e; }
.status-approved { background: #d1fae5; color: #065f46; }
.status-rejected { background: #fee2e2; color: #991b1b; }

.empty-state {
    text-align: center;
    padding: 2rem;
    color: #6b7280;
}

.empty-state i {
    font-size: 2rem;
    margin-bottom: 0.5rem;
    opacity: 0.5;
}

.quick-actions {
    background: white;
    padding: 1.5rem;
    border-radius: 12px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    border: 1px solid #e5e7eb;
}

.quick-actions h3 {
    font-size: 1.125rem;
    font-weight: 600;
    color: #111827;
    margin-bottom: 1rem;
}

.actions-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
}

.action-card {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 1rem;
    background: #f8fafc;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    text-decoration: none;
    color: #374151;
    font-weight: 500;
    transition: all 0.2s;
}

.action-card:hover {
    background: #3b82f6;
    color: white;
    transform: translateY(-1px);
    text-decoration: none;
}

.action-card i {
    font-size: 1.25rem;
}

@media (max-width: 768px) {
    .main-content {
        margin-left: 0;
        margin-top: 75px;
        padding: 1rem;
    }
    
    .stats-grid {
        grid-template-columns: 1fr;
    }
    
    .overview-grid {
        grid-template-columns: 1fr;
    }
    
    .status-overview {
        flex-direction: column;
        gap: 1rem;
    }
    
    .actions-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<?php 
function getStatusClass($statusId) {
    switch($statusId) {
        case 2: return 'submitted';
        case 3: return 'under_review';
        case 6: return 'approved';
        case 7: return 'rejected';
        default: return 'submitted';
    }
}

include 'includes/footer.php'; 
?>