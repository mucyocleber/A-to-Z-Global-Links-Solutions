<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit;
}

require_once '../config/database.php';

try {
    $pdo = getConnection();
    
    // Pagination
    $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
    $limit = 12;
    $offset = ($page - 1) * $limit;
    
    // Get total count for pagination
    $countQuery = "SELECT COUNT(*) FROM applications a JOIN users u ON a.user_id = u.id";
    $totalStmt = $pdo->prepare($countQuery);
    $totalStmt->execute();
    $totalApplications = $totalStmt->fetchColumn();
    $totalPages = ceil($totalApplications / $limit);
    
    $query = "
        SELECT a.*, 
               CONCAT(u.first_name, ' ', u.last_name) as full_name, 
               u.email, u.country, u.date_of_birth,
               s.name as service_name, s.currency,
               st.status_name as status,
               st.status_name,
               c.name as destination_country,
               pm.name as payment_method_name,
               a.payment_plan, a.initial_payment_status, a.final_payment_status, a.refund_status,
               a.application_type
        FROM applications a 
        JOIN users u ON a.user_id = u.id 
        JOIN services s ON a.service_id = s.id 
        JOIN application_statuses st ON a.status_id = st.id
        LEFT JOIN countries c ON a.destination_country_id = c.id
        LEFT JOIN payment_methods pm ON a.payment_method_id = pm.id
        ORDER BY a.submitted_at DESC
        LIMIT :limit OFFSET :offset
    ";
    
    $stmt = $pdo->prepare($query);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $applications = $stmt->fetchAll();
    
    // Get all statuses for dropdown
    $statusStmt = $pdo->prepare("SELECT * FROM application_statuses ORDER BY id");
    $statusStmt->execute();
    $statuses = $statusStmt->fetchAll();
} catch (Exception $e) {
    $applications = [];
    $totalPages = 1;
    $page = 1;
    $statuses = [];
}

$pageTitle = 'Applications';
?>

<?php include 'includes/header.php'; ?>
<?php include 'includes/sidebar.php'; ?>

<main class="main-content">
    <div class="page-header">
        <div>
            <h1>Applications Management</h1>
            <p>Manage all visa applications and payments</p>
        </div>
        <div class="header-actions">
            <button onclick="downloadReport()" class="btn btn-secondary">
                <i class="fas fa-download"></i> Download Report
            </button>
            <button onclick="addNewApplication()" class="btn btn-primary">
                <i class="fas fa-plus"></i> Add New Application
            </button>
        </div>
    </div>
    
    <div class="search-section">
        <div class="search-input-group">
            <i class="fas fa-search"></i>
            <input type="text" id="searchInput" placeholder="Search by name, email, or application number..." class="search-input">
            <div id="searchClear" class="clear-btn" style="display: none;" onclick="clearSearch()">
                <i class="fas fa-times"></i>
            </div>
        </div>
    </div>
    
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon pending"><i class="fas fa-clock"></i></div>
            <div class="stat-info">
                <div class="stat-number"><?= count(array_filter($applications, function($a) { return in_array($a['status_id'], [1,2,3]); })) ?></div>
                <div class="stat-label">Pending</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon documents"><i class="fas fa-file-alt"></i></div>
            <div class="stat-info">
                <div class="stat-number"><?= count($applications) ?></div>
                <div class="stat-label">Total Apps</div>
            </div>
        </div>
        
        <div class="stat-card revenue-card">
            <div class="stat-icon revenue"><i class="fas fa-check-circle"></i></div>
            <div class="stat-info">
                <div class="stat-label">Paid Revenue</div>
                <?php
                try {
                    $paidStmt = $pdo->prepare("
                        SELECT currency, SUM(COALESCE(amount_paid_so_far, 0)) as total
                        FROM applications
                        GROUP BY currency
                        ORDER BY currency
                    ");
                    $paidStmt->execute();
                    $paidRevenue = $paidStmt->fetchAll();
                    
                    if (empty($paidRevenue)) {
                        echo '<div class="currency-amount"><span class="currency">USD</span><span class="amount">0</span></div>';
                        echo '<div class="currency-amount"><span class="currency">EUR</span><span class="amount">0</span></div>';
                    } else {
                        $currencies = ['USD' => 0, 'EUR' => 0];
                        foreach($paidRevenue as $rev) {
                            $currencies[$rev['currency']] = $rev['total'];
                        }
                        foreach($currencies as $curr => $amount) {
                            $formatted = $amount > 0 ? number_format($amount, 0) : '0';
                            echo '<div class="currency-amount"><span class="currency">' . $curr . '</span><span class="amount">' . $formatted . '</span></div>';
                        }
                    }
                } catch (Exception $e) {
                    echo '<div class="currency-amount"><span class="currency">USD</span><span class="amount">0</span></div>';
                    echo '<div class="currency-amount"><span class="currency">EUR</span><span class="amount">0</span></div>';
                }
                ?>
            </div>
        </div>
        
        <div class="stat-card revenue-card unpaid">
            <div class="stat-icon" style="background: #ef4444;"><i class="fas fa-exclamation-circle"></i></div>
            <div class="stat-info">
                <div class="stat-label">Unpaid Balance</div>
                <?php
                try {
                    $unpaidStmt = $pdo->prepare("
                        SELECT currency, SUM(total_amount - COALESCE(amount_paid_so_far, 0)) as total
                        FROM applications
                        GROUP BY currency
                        ORDER BY currency
                    ");
                    $unpaidStmt->execute();
                    $unpaidRevenue = $unpaidStmt->fetchAll();
                    
                    $currencies = ['USD' => 0, 'EUR' => 0];
                    foreach($unpaidRevenue as $rev) {
                        $currencies[$rev['currency']] = $rev['total'];
                    }
                    foreach($currencies as $curr => $amount) {
                        $formatted = $amount > 0 ? number_format($amount, 0) : '0';
                        echo '<div class="currency-amount"><span class="currency">' . $curr . '</span><span class="amount">' . $formatted . '</span></div>';
                    }
                } catch (Exception $e) {
                    echo '<div class="currency-amount"><span class="currency">USD</span><span class="amount">0</span></div>';
                    echo '<div class="currency-amount"><span class="currency">EUR</span><span class="amount">0</span></div>';
                }
                ?>
            </div>
        </div>
    </div>
        
    <div class="tabs-container">
        <div class="tabs">
            <button class="tab-btn active" data-tab="all">All Applications</button>
            <button class="tab-btn" data-tab="manual">Manual Applications</button>
            <button class="tab-btn" data-tab="online">Online Applications</button>
        </div>
    </div>
    
    <div class="applications-grid" id="applicationsGrid">
        <?php foreach ($applications as $app): ?>
            <div class="app-card" data-search="<?= strtolower($app['full_name'] . ' ' . $app['email'] . ' ' . $app['application_number']) ?>" data-type="<?= $app['application_type'] ?? 'online' ?>">
                <div class="card-header">
                    <div class="user-info">
                        <div class="avatar"><?= strtoupper(substr($app['full_name'], 0, 1)) ?></div>
                        <div>
                            <h3><?= htmlspecialchars($app['full_name']) ?></h3>
                            <p><?= htmlspecialchars($app['service_name']) ?></p>
                        </div>
                    </div>
                    <div class="status-badges">
                        <div class="type-badge type-<?= $app['application_type'] ?? 'online' ?>">
                            <?= ucfirst($app['application_type'] ?? 'online') ?>
                        </div>
                        <div class="status-badge status-<?= strtolower(str_replace(' ', '_', $app['status'])) ?>">
                            <?= htmlspecialchars($app['status']) ?>
                        </div>
                    </div>
                </div>
                
                <div class="card-body">
                    <div class="info-row">
                        <span>App ID:</span>
                        <span><?= htmlspecialchars($app['application_number']) ?></span>
                    </div>
                    <div class="info-row">
                        <span>Email:</span>
                        <span><?= htmlspecialchars($app['email']) ?></span>
                    </div>
                    <div class="info-row">
                        <span>Service Amount:</span>
                        <span class="amount"><?= htmlspecialchars($app['currency']) ?> <?= number_format($app['base_amount'], 2) ?></span>
                    </div>
                    <div class="info-row">
                        <span>Payment Plan:</span>
                        <span><?= ucfirst($app['payment_plan'] ?? 'full') ?> Payment</span>
                    </div>
                    <div class="info-row">
                        <span>Payment Method:</span>
                        <span class="payment-method"><?= $app['payment_method_name'] ? htmlspecialchars($app['payment_method_name']) : 'Not specified' ?></span>
                    </div>
                    <div class="info-row">
                        <span>Payment Status:</span>
                        <span class="payment-status">
                            <?php
                            $paidAmount = $app['amount_paid_so_far'] ?? 0;
                            $totalAmount = $app['total_amount'] ?? 0;
                            $percentage = $totalAmount > 0 ? ($paidAmount / $totalAmount) * 100 : 0;
                            
                            if ($percentage >= 100) {
                                echo '<span style="color: #10b981;">✓ Fully Paid</span>';
                            } elseif ($percentage > 0) {
                                echo '<span style="color: #f59e0b;">⚡ ' . number_format($percentage, 0) . '% Paid</span>';
                            } else {
                                echo '<span style="color: #ef4444;">⏳ Unpaid</span>';
                            }
                            ?>
                        </span>
                    </div>
                    
                    <?php if (($app['refund_status'] ?? 'none') !== 'none'): ?>
                    <div class="info-row">
                        <span>Refund Status:</span>
                        <span style="color: #f59e0b;">
                            <i class="fas fa-undo"></i> <?= ucfirst($app['refund_status']) ?>
                        </span>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($app['destination_country']): ?>
                    <div class="destination-tag">
                        <i class="fas fa-plane"></i>
                        <?= htmlspecialchars($app['destination_country']) ?>
                    </div>
                    <?php endif; ?>
                </div>
                
                <div class="card-actions">
                    <a href="view-application.php?id=<?= $app['id'] ?>" class="btn-view">
                        <i class="fas fa-eye"></i> View
                    </a>
                    
                    <div class="status-dropdown">
                        <select onchange="changeStatus(<?= $app['id'] ?>, this.value)" class="status-select">
                            <option value="">Change Status</option>
                            <?php foreach($statuses as $status): ?>
                                <option value="<?= $status['id'] ?>" <?= $status['id'] == $app['status_id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($status['status_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <?php 
                    $initialPaid = ($app['initial_payment_status'] ?? 'pending') === 'completed';
                    $finalPaid = ($app['final_payment_status'] ?? 'not_required') === 'completed';
                    $isPartial = ($app['payment_plan'] ?? 'full') === 'partial';
                    
                    if (!$initialPaid): ?>
                        <button class="btn-approve" onclick="approvePayment(<?= $app['id'] ?>, 'initial')" title="Approve Initial Payment">
                            <i class="fas fa-check"></i>
                        </button>
                        <button class="btn-reject" onclick="rejectPayment(<?= $app['id'] ?>, 'initial')" title="Reject Initial Payment">
                            <i class="fas fa-times"></i>
                        </button>
                    <?php elseif ($isPartial && !$finalPaid && ($app['final_payment_status'] ?? 'not_required') === 'pending'): ?>
                        <button class="btn-approve" onclick="approvePayment(<?= $app['id'] ?>, 'final')" title="Approve Final Payment">
                            <i class="fas fa-check"></i>
                        </button>
                        <button class="btn-reject" onclick="rejectPayment(<?= $app['id'] ?>, 'final')" title="Reject Final Payment">
                            <i class="fas fa-times"></i>
                        </button>
                    <?php endif; ?>
                    
                    <button class="btn-delete" onclick="deleteApplication(<?= $app['id'] ?>)" title="Delete Application">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    
    <div id="noResults" class="empty-state" style="display: none;">
        <i class="fas fa-search"></i>
        <h3>No Applications Found</h3>
        <p>Try adjusting your search terms</p>
        <button onclick="clearSearch()" class="btn btn-primary">Clear Search</button>
    </div>
    
    <?php if ($totalPages > 1): ?>
    <div class="pagination">
        <div class="pagination-info">
            Showing <?= count($applications) ?> of <?= $totalApplications ?> applications
        </div>
        <div class="pagination-controls">
            <?php if ($page > 1): ?>
                <a href="?page=<?= $page - 1 ?>" class="pagination-btn">
                    <i class="fas fa-chevron-left"></i> Previous
                </a>
            <?php endif; ?>
            
            <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                <a href="?page=<?= $i ?>" class="pagination-btn <?= $i == $page ? 'active' : '' ?>">
                    <?= $i ?>
                </a>
            <?php endfor; ?>
            
            <?php if ($page < $totalPages): ?>
                <a href="?page=<?= $page + 1 ?>" class="pagination-btn">
                    Next <i class="fas fa-chevron-right"></i>
                </a>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
</main>

<style>
.tabs-container {
    margin-bottom: 2rem;
}

.tabs {
    display: flex;
    background: white;
    border-radius: 12px;
    padding: 0.5rem;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    gap: 0.5rem;
}

.tab-btn {
    flex: 1;
    padding: 0.875rem 1.5rem;
    border: none;
    background: transparent;
    border-radius: 8px;
    font-weight: 500;
    color: #6b7280;
    cursor: pointer;
    transition: all 0.2s;
}

.tab-btn.active {
    background: #3b82f6;
    color: white;
    box-shadow: 0 2px 8px rgba(59, 130, 246, 0.3);
}

.tab-btn:hover:not(.active) {
    background: #f3f4f6;
    color: #374151;
}

.status-badges {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    align-items: flex-end;
}

.type-badge {
    padding: 0.25rem 0.75rem;
    border-radius: 12px;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
}

.type-manual {
    background: #fef3c7;
    color: #92400e;
}

.type-online {
    background: #dbeafe;
    color: #1e40af;
}

.main-content {
    margin-left: 260px;
    margin-top: 70px;
    padding: 2rem;
    background: #f8fafc;
    min-height: calc(100vh - 70px);
}

.page-header {
    margin-bottom: 2rem;
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
}

.page-header h1 {
    font-size: 2rem;
    font-weight: 700;
    color: #1e293b;
    margin-bottom: 0.5rem;
}

.page-header p {
    color: #64748b;
    font-size: 1.1rem;
}

.header-actions {
    display: flex;
    gap: 1rem;
}

.btn {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1.5rem;
    border-radius: 8px;
    font-weight: 500;
    text-decoration: none;
    border: none;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-primary {
    background: #3b82f6;
    color: white;
}

.btn-primary:hover {
    background: #2563eb;
    color: white;
}

.btn-secondary {
    background: #6b7280;
    color: white;
}

.btn-secondary:hover {
    background: #4b5563;
}

.search-section {
    margin-bottom: 2rem;
    max-width: 600px;
}

.search-input-group {
    display: flex;
    align-items: center;
    background: white;
    border-radius: 12px;
    padding: 0.75rem 1rem;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    gap: 1rem;
}

.search-input-group i {
    color: #6b7280;
}

.search-input {
    flex: 1;
    border: none;
    outline: none;
    font-size: 1rem;
}

.clear-btn {
    color: #6b7280;
    cursor: pointer;
    padding: 0.25rem;
    border-radius: 4px;
    transition: all 0.2s;
}

.clear-btn:hover {
    color: #374151;
    background: #f3f4f6;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 1rem;
    margin-bottom: 2rem;
}

.stat-card {
    background: white;
    border-radius: 16px;
    padding: 1.5rem;
    display: flex;
    align-items: center;
    gap: 1rem;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    transition: all 0.3s ease;
    border: 1px solid #f1f5f9;
}

.stat-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
}

.stat-card.revenue-card {
    flex-direction: column;
    align-items: stretch;
}

.stat-card.revenue-card .stat-icon {
    align-self: flex-start;
}

.stat-card.revenue-card .stat-info {
    width: 100%;
}

.stat-icon {
    width: 56px;
    height: 56px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.4rem;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.stat-icon.pending { background: linear-gradient(135deg, #f59e0b, #d97706); }
.stat-icon.approved { background: linear-gradient(135deg, #10b981, #059669); }
.stat-icon.documents { background: linear-gradient(135deg, #3b82f6, #2563eb); }
.stat-icon.revenue { background: linear-gradient(135deg, #10b981, #059669); }

.stat-number {
    font-size: 2rem;
    font-weight: 700;
    color: #1e293b;
    line-height: 1.2;
}

.stat-label {
    color: #64748b;
    font-size: 0.875rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 0.75rem;
}

.currency-amount {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.75rem 0;
    border-bottom: 1px solid #f1f5f9;
}

.currency-amount:last-child {
    border-bottom: none;
    padding-bottom: 0;
}

.currency-amount:first-of-type {
    padding-top: 0;
}

.currency-amount .currency {
    font-size: 0.875rem;
    font-weight: 600;
    color: #64748b;
    background: #f1f5f9;
    padding: 0.25rem 0.75rem;
    border-radius: 6px;
}

.currency-amount .amount {
    font-size: 1.5rem;
    font-weight: 700;
    color: #1e293b;
}

.stat-card.unpaid .currency-amount .amount {
    color: #ef4444;
}

.applications-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
    gap: 1.5rem;
}

.app-card {
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    overflow: hidden;
    transition: all 0.2s;
}

.app-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.15);
}

.card-header {
    padding: 1.5rem;
    background: #f8fafc;
    border-bottom: 1px solid #e2e8f0;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.user-info {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.avatar {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: 600;
    font-size: 1.2rem;
}

.user-info h3 {
    font-size: 1.1rem;
    font-weight: 600;
    color: #1f2937;
    margin: 0 0 0.25rem 0;
}

.user-info p {
    font-size: 0.9rem;
    color: #6b7280;
    margin: 0;
}

.status-badge {
    padding: 0.5rem 1rem;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 600;
    text-transform: uppercase;
}

.status-draft, .status-submitted, .status-pending { background: #dbeafe; color: #1e40af; }
.status-under_review, .status-processing { background: #fef3c7; color: #92400e; }
.status-approved, .status-completed { background: #d1fae5; color: #065f46; }
.status-rejected { background: #fee2e2; color: #991b1b; }

.card-body {
    padding: 1.5rem;
}

.info-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.75rem 0;
    border-bottom: 1px solid #f3f4f6;
}

.info-row:last-child {
    border-bottom: none;
}

.info-row span:first-child {
    font-size: 0.9rem;
    color: #6b7280;
    font-weight: 500;
}

.info-row span:last-child {
    font-size: 0.9rem;
    font-weight: 600;
    color: #1f2937;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.amount {
    color: #059669 !important;
    font-weight: 700 !important;
}

.destination-tag {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    background: #eff6ff;
    color: #1d4ed8;
    padding: 0.5rem 1rem;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 500;
    margin-top: 1rem;
}

.card-actions {
    padding: 1rem;
    background: #f9fafb;
    border-top: 1px solid #e2e8f0;
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
    align-items: center;
}

.btn-view {
    flex: 1;
    background: #3b82f6;
    color: white;
    border: none;
    padding: 0.6rem 1rem;
    border-radius: 8px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    font-size: 0.85rem;
    text-decoration: none;
    min-width: 80px;
}

.btn-view:hover {
    background: #2563eb;
    color: white;
}

.status-dropdown {
    flex: 1;
    min-width: 120px;
}

.status-select {
    width: 100%;
    padding: 0.6rem 0.75rem;
    border: 1px solid #d1d5db;
    border-radius: 8px;
    font-size: 0.85rem;
    background: white;
    cursor: pointer;
    transition: all 0.2s;
}

.status-select:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.btn-approve, .btn-reject, .btn-delete {
    padding: 0.6rem;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.85rem;
    min-width: 36px;
    height: 36px;
}

.btn-approve { background: #10b981; color: white; }
.btn-reject { background: #f59e0b; color: white; }
.btn-delete { background: #ef4444; color: white; }

.btn-approve:hover { background: #059669; }
.btn-reject:hover { background: #d97706; }
.btn-delete:hover { background: #dc2626; }

.pagination {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 2rem;
    padding: 1.5rem;
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.pagination-info {
    color: #64748b;
    font-size: 0.9rem;
}

.pagination-controls {
    display: flex;
    gap: 0.5rem;
    align-items: center;
}

.pagination-btn {
    padding: 0.5rem 0.75rem;
    border: 1px solid #d1d5db;
    border-radius: 8px;
    color: #374151;
    text-decoration: none;
    font-size: 0.9rem;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    gap: 0.25rem;
}

.pagination-btn:hover {
    background: #f3f4f6;
    border-color: #9ca3af;
    color: #374151;
}

.pagination-btn.active {
    background: #3b82f6;
    border-color: #3b82f6;
    color: white;
}

.pagination-btn.active:hover {
    background: #2563eb;
    color: white;
}

.empty-state {
    grid-column: 1 / -1;
    text-align: center;
    padding: 4rem 2rem;
    color: #64748b;
    background: white;
    border-radius: 12px;
}

.empty-state i {
    font-size: 3rem;
    margin-bottom: 1rem;
    color: #94a3b8;
}

.empty-state h3 {
    font-size: 1.5rem;
    color: #1e293b;
    margin-bottom: 0.5rem;
}

@media (max-width: 768px) {
    .main-content {
        margin-left: 0;
        padding: 1rem;
    }
    
    .page-header {
        flex-direction: column;
        gap: 1rem;
    }
    
    .header-actions {
        width: 100%;
        justify-content: stretch;
    }
    
    .stats-grid {
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
    }
    
    .applications-grid {
        grid-template-columns: 1fr;
        gap: 1rem;
    }
    
    .pagination {
        flex-direction: column;
        gap: 1rem;
        text-align: center;
    }
    
    .card-actions {
        flex-direction: column;
        gap: 0.5rem;
    }
    
    .btn-view, .status-dropdown {
        flex: none;
        width: 100%;
    }
}

.notification {
    position: fixed;
    top: 20px;
    right: 20px;
    background: white;
    border-radius: 12px;
    padding: 1rem 1.5rem;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
    display: flex;
    align-items: center;
    gap: 0.75rem;
    z-index: 1000;
    transform: translateX(400px);
    opacity: 0;
    transition: all 0.3s ease;
    min-width: 300px;
}

.notification.show {
    transform: translateX(0);
    opacity: 1;
}

.notification.success {
    border-left: 4px solid #10b981;
    color: #065f46;
}

.notification.success i {
    color: #10b981;
}

.notification.error {
    border-left: 4px solid #ef4444;
    color: #991b1b;
}

.notification.error i {
    color: #ef4444;
}

.modern-modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: 1000;
    align-items: center;
    justify-content: center;
}

.modern-modal.show {
    display: flex;
}

.modern-modal-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.6);
    backdrop-filter: blur(8px);
}

.modern-modal-content {
    position: relative;
    background: white;
    border-radius: 20px;
    max-width: 450px;
    width: 90%;
    box-shadow: 0 25px 50px rgba(0, 0, 0, 0.25);
    overflow: hidden;
    animation: modalSlideIn 0.3s ease-out;
}

@keyframes modalSlideIn {
    from {
        opacity: 0;
        transform: translateY(-20px) scale(0.95);
    }
    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

.modal-header {
    padding: 2rem 2rem 1rem;
    text-align: center;
    background: linear-gradient(135deg, #f8fafc, #e2e8f0);
}

.modal-icon {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.9);
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1rem;
    font-size: 2rem;
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
}

.modal-header h3 {
    color: #1e293b;
    font-size: 1.5rem;
    font-weight: 700;
    margin: 0;
}

.modal-body {
    padding: 1.5rem 2rem;
    text-align: center;
}

.modal-body p {
    color: #64748b;
    font-size: 1.1rem;
    line-height: 1.6;
    margin: 0;
}

.modal-footer {
    padding: 1.5rem 2rem 2rem;
    display: flex;
    gap: 1rem;
    justify-content: center;
}

.modal-btn {
    min-width: 120px;
    padding: 0.875rem 2rem;
    font-size: 1rem;
    font-weight: 600;
    border-radius: 12px;
    transition: all 0.3s ease;
}

.modal-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(59, 130, 246, 0.4);
}

.btn-success {
    background: linear-gradient(135deg, #10b981, #059669);
    color: white;
}

.btn-warning {
    background: linear-gradient(135deg, #f59e0b, #d97706);
    color: white;
}

.btn-danger {
    background: linear-gradient(135deg, #ef4444, #dc2626);
    color: white;
}
</style>

<script>
// Tab functionality
document.querySelectorAll('.tab-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const tab = this.dataset.tab;
        
        // Update active tab
        document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
        this.classList.add('active');
        
        // Filter applications
        filterApplications(tab);
    });
});

function filterApplications(type) {
    const cards = document.querySelectorAll('.app-card');
    const noResults = document.getElementById('noResults');
    let visibleCount = 0;
    
    cards.forEach(card => {
        const cardType = card.getAttribute('data-type');
        const searchQuery = document.getElementById('searchInput').value.toLowerCase().trim();
        
        let shouldShow = false;
        
        if (type === 'all') {
            shouldShow = true;
        } else if (type === 'manual') {
            shouldShow = cardType === 'manual';
        } else if (type === 'online') {
            shouldShow = cardType === 'online' || !cardType;
        }
        
        // Apply search filter if there's a search query
        if (shouldShow && searchQuery.length > 0) {
            const searchData = card.getAttribute('data-search');
            shouldShow = searchData.includes(searchQuery);
        }
        
        if (shouldShow) {
            card.style.display = 'block';
            visibleCount++;
        } else {
            card.style.display = 'none';
        }
    });
    
    noResults.style.display = visibleCount === 0 ? 'block' : 'none';
}

// Live search functionality
document.getElementById('searchInput').addEventListener('input', function() {
    const query = this.value.toLowerCase().trim();
    const clearBtn = document.getElementById('searchClear');
    const activeTab = document.querySelector('.tab-btn.active').dataset.tab;
    
    if (query.length > 0) {
        clearBtn.style.display = 'block';
    } else {
        clearBtn.style.display = 'none';
    }
    
    // Apply both search and tab filters
    filterApplications(activeTab);
});

function clearSearch() {
    document.getElementById('searchInput').value = '';
    document.getElementById('searchClear').style.display = 'none';
    const activeTab = document.querySelector('.tab-btn.active').dataset.tab;
    filterApplications(activeTab);
}

function addNewApplication() {
    window.location.href = 'add-application.php';
}

function downloadReport() {
    showNotification('Generating PDF report...', 'success');
    
    // Open in new window for printing/saving
    const reportWindow = window.open('export-applications.php', '_blank');
    
    // Focus the new window
    if (reportWindow) {
        reportWindow.focus();
    } else {
        showNotification('Please allow popups to download the report', 'error');
    }
}

function approvePayment(id, type) {
    showConfirmModal(
        'Approve Payment',
        'Are you sure you want to approve this payment?',
        'fa-check-circle',
        '#10b981',
        'Approve',
        'btn-success',
        function() {
            fetch('ajax/update_payment_status.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `application_id=${id}&payment_type=${type}&status=completed`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification('Payment approved successfully', 'success');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showNotification('Error: ' + (data.message || 'Unknown error'), 'error');
                }
            })
            .catch(error => {
                showNotification('Network error occurred', 'error');
            });
        }
    );
}

function rejectPayment(id, type) {
    showConfirmModal(
        'Reject Payment',
        'Are you sure you want to reject this payment?',
        'fa-times-circle',
        '#f59e0b',
        'Reject',
        'btn-warning',
        function() {
            fetch('ajax/update_payment_status.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `application_id=${id}&payment_type=${type}&status=failed`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification('Payment rejected successfully', 'success');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showNotification('Error: ' + (data.message || 'Unknown error'), 'error');
                }
            })
            .catch(error => {
                showNotification('Network error occurred', 'error');
            });
        }
    );
}

function changeStatus(id, statusId) {
    if (!statusId) return;
    
    showConfirmModal(
        'Change Status',
        'Are you sure you want to change the application status?',
        'fa-exchange-alt',
        '#3b82f6',
        'Change Status',
        'btn-primary',
        function() {
            fetch('ajax/change_status.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `application_id=${id}&status_id=${statusId}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification('Status updated successfully', 'success');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showNotification('Error: ' + (data.message || 'Unknown error'), 'error');
                }
            })
            .catch(error => {
                showNotification('Network error occurred', 'error');
            });
        }
    );
}

function deleteApplication(id) {
    showConfirmModal(
        'Delete Application',
        'Are you sure you want to delete this application? This action cannot be undone.',
        'fa-trash',
        '#ef4444',
        'Delete',
        'btn-danger',
        function() {
            fetch('ajax/delete_application.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `application_id=${id}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification('Application deleted successfully', 'success');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showNotification('Error: ' + (data.message || 'Unknown error'), 'error');
                }
            })
            .catch(error => {
                showNotification('Network error occurred', 'error');
            });
        }
    );
}

function showNotification(message, type) {
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    notification.innerHTML = `
        <i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-triangle'}"></i>
        <span>${message}</span>
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.classList.add('show');
    }, 100);
    
    setTimeout(() => {
        notification.classList.remove('show');
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}

function showConfirmModal(title, message, icon, iconColor, buttonText, buttonClass, callback) {
    const modal = document.createElement('div');
    modal.className = 'modern-modal show';
    modal.innerHTML = `
        <div class="modern-modal-overlay"></div>
        <div class="modern-modal-content">
            <div class="modal-header">
                <div class="modal-icon" style="color: ${iconColor}">
                    <i class="fas ${icon}"></i>
                </div>
                <h3>${title}</h3>
            </div>
            <div class="modal-body">
                <p>${message}</p>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary modal-btn" onclick="closeModal(this)">
                    Cancel
                </button>
                <button class="btn ${buttonClass} modal-btn" onclick="confirmAction(this)">
                    ${buttonText}
                </button>
            </div>
        </div>
    `;
    
    document.body.appendChild(modal);
    
    window.confirmAction = function(btn) {
        modal.remove();
        callback();
    };
    
    window.closeModal = function(btn) {
        modal.remove();
    };
    
    // Close on overlay click
    modal.querySelector('.modern-modal-overlay').addEventListener('click', function() {
        modal.remove();
    });
}
</script>

<?php include 'includes/footer.php'; ?>