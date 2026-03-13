<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once 'config/database.php';

if (!isset($_GET['id'])) {
    header('Location: profile.php');
    exit;
}

try {
    $pdo = getConnection();
    
    // Get comprehensive application details
    $stmt = $pdo->prepare("
        SELECT a.*, s.name as service_name, s.currency as service_currency, s.base_price,
               ast.status_name, 
               LOWER(REPLACE(ast.status_name, ' ', '-')) as status_code,
               c.name as destination_country_name,
               pm.name as payment_method_name,
               CONCAT(u.first_name, ' ', u.last_name) as user_name,
               u.email as user_email, u.phone as user_phone,
               COALESCE(a.amount_paid_so_far, 0) as amount_paid_so_far,
               COALESCE(a.payment_completion_percentage, 0) as payment_completion_percentage,
               COALESCE(a.refund_status, 'none') as refund_status
        FROM applications a
        LEFT JOIN services s ON a.service_id = s.id
        LEFT JOIN application_statuses ast ON a.status_id = ast.id
        LEFT JOIN countries c ON a.destination_country_id = c.id
        LEFT JOIN payment_methods pm ON a.payment_method_id = pm.id
        LEFT JOIN users u ON a.user_id = u.id
        WHERE a.id = ? AND a.user_id = ?
    ");
    $stmt->execute([$_GET['id'], $_SESSION['user_id']]);
    $application = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$application) {
        $_SESSION['error_message'] = 'Application not found or access denied.';
        header('Location: profile.php');
        exit;
    }
    
    // Get documents
    $stmt = $pdo->prepare("
        SELECT * FROM documents 
        WHERE application_id = ? 
        ORDER BY uploaded_at DESC
    ");
    $stmt->execute([$_GET['id']]);
    $documents = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get payments with installments
    $stmt = $pdo->prepare("
        SELECT p.*, pm.name as payment_method_name
        FROM payments p
        LEFT JOIN payment_methods pm ON p.payment_method_id = pm.id
        WHERE p.application_id = ?
        ORDER BY p.created_at DESC
    ");
    $stmt->execute([$_GET['id']]);
    $payments = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get messages
    $stmt = $pdo->prepare("
        SELECT am.*, 
               CASE 
                   WHEN am.sender_type = 'admin' THEN 'Support Team'
                   ELSE CONCAT(u.first_name, ' ', u.last_name)
               END as sender_name
        FROM application_messages am
        LEFT JOIN users u ON am.sender_id = u.id AND am.sender_type = 'user'
        WHERE am.application_id = ? 
        ORDER BY am.created_at ASC
    ");
    $stmt->execute([$_GET['id']]);
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get status history
    $stmt = $pdo->prepare("
        SELECT ash.*, 
               fs.status_name as from_status_name,
               ts.status_name as to_status_name
        FROM application_status_history ash
        LEFT JOIN application_statuses fs ON ash.from_status_id = fs.id
        LEFT JOIN application_statuses ts ON ash.to_status_id = ts.id
        WHERE ash.application_id = ?
        ORDER BY ash.changed_at DESC
    ");
    $stmt->execute([$_GET['id']]);
    $status_history = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (Exception $e) {
    error_log('View Application Error: ' . $e->getMessage());
    $_SESSION['error_message'] = 'Error loading application: ' . $e->getMessage();
    header('Location: profile.php');
    exit;
}

$pageTitle = 'Application Details - A to Z Global Link Solutions';
// DEBUG: This is the NEW REDESIGNED version
echo '<!-- NEW REDESIGNED VERSION LOADED -->';
include 'includes/header.php';
?>

<style>
:root {
    --primary: #3b82f6;
    --primary-dark: #1d4ed8;
    --success: #10b981;
    --warning: #f59e0b;
    --danger: #ef4444;
    --gray-50: #f8fafc;
    --gray-100: #f1f5f9;
    --gray-200: #e2e8f0;
    --gray-300: #cbd5e1;
    --gray-400: #94a3b8;
    --gray-500: #64748b;
    --gray-600: #475569;
    --gray-700: #334155;
    --gray-800: #1e293b;
    --gray-900: #0f172a;
}

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
    background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
    color: var(--gray-800);
    line-height: 1.6;
}

.app-container {
    max-width: 1400px;
    margin: 0 auto;
    padding: 2rem;
    margin-top: 80px;
}

/* Modern Header */
.app-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 24px;
    padding: 3rem;
    color: white;
    margin-bottom: 2rem;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
    position: relative;
    overflow: hidden;
}

.app-header::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -20%;
    width: 400px;
    height: 400px;
    background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, transparent 70%);
    border-radius: 50%;
    animation: float 6s ease-in-out infinite;
}

@keyframes float {
    0%, 100% { transform: translateY(0px); }
    50% { transform: translateY(-20px); }
}

.header-content {
    position: relative;
    z-index: 1;
}

.app-title-section {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 2rem;
    flex-wrap: wrap;
    gap: 1rem;
}

.app-number {
    font-size: 2.5rem;
    font-weight: 800;
    margin-bottom: 0.5rem;
    background: linear-gradient(45deg, #fff, #e2e8f0);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

.app-service {
    font-size: 1.3rem;
    opacity: 0.9;
    font-weight: 500;
}

.status-badge {
    padding: 1rem 2rem;
    border-radius: 50px;
    font-weight: 700;
    font-size: 1rem;
    text-transform: uppercase;
    letter-spacing: 1px;
    border: 2px solid rgba(255, 255, 255, 0.3);
    backdrop-filter: blur(10px);
}

.status-draft { background: rgba(107, 114, 128, 0.2); color: #f3f4f6; }
.status-submitted { background: rgba(59, 130, 246, 0.2); color: #dbeafe; }
.status-under-review { background: rgba(245, 158, 11, 0.2); color: #fef3c7; }
.status-processing { background: rgba(245, 158, 11, 0.2); color: #fef3c7; }
.status-approved { background: rgba(16, 185, 129, 0.2); color: #d1fae5; }
.status-rejected { background: rgba(239, 68, 68, 0.2); color: #fee2e2; }
.status-completed { background: rgba(16, 185, 129, 0.2); color: #d1fae5; }

.app-overview {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 2rem;
    margin-top: 2rem;
}

.overview-item {
    text-align: center;
    padding: 1.5rem;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 20px;
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.2);
}

.overview-label {
    font-size: 0.9rem;
    opacity: 0.8;
    margin-bottom: 0.5rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.overview-value {
    font-size: 1.2rem;
    font-weight: 700;
}

/* Modern Tabs */
.tabs-container {
    background: white;
    border-radius: 24px;
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    overflow: hidden;
    border: 1px solid var(--gray-200);
}

.tabs-nav {
    display: flex;
    background: var(--gray-50);
    border-bottom: 1px solid var(--gray-200);
    padding: 0.5rem;
    gap: 0.5rem;
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
    scrollbar-width: none;
    -ms-overflow-style: none;
}

.tabs-nav::-webkit-scrollbar {
    display: none;
}

.tab-btn {
    padding: 1rem 2rem;
    border: none;
    background: transparent;
    color: var(--gray-600);
    font-weight: 600;
    font-size: 1rem;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 0.75rem;
    border-radius: 16px;
    white-space: nowrap;
    position: relative;
    flex-shrink: 0;
    min-width: fit-content;
}

.tab-btn.active {
    background: white;
    color: var(--primary);
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    transform: translateY(-2px);
}

.tab-btn:hover:not(.active) {
    color: var(--primary);
    background: rgba(59, 130, 246, 0.05);
}

.tab-content {
    display: none;
    padding: 3rem;
}

.tab-content.active {
    display: block;
}

/* Content Cards */
.content-card {
    background: var(--gray-50);
    border-radius: 20px;
    padding: 2rem;
    margin-bottom: 2rem;
    border: 1px solid var(--gray-200);
}

.card-title {
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--gray-800);
    margin-bottom: 1.5rem;
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding-bottom: 1rem;
    border-bottom: 2px solid var(--gray-200);
}

.card-title i {
    color: var(--primary);
    font-size: 1.25rem;
}

/* Application Details Grid */
.details-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 2rem;
}

.detail-item {
    background: white;
    padding: 1.5rem;
    border-radius: 16px;
    border-left: 4px solid var(--primary);
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
}

.detail-label {
    color: var(--gray-600);
    font-size: 0.9rem;
    font-weight: 600;
    margin-bottom: 0.5rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.detail-value {
    color: var(--gray-800);
    font-weight: 700;
    font-size: 1.1rem;
}

/* Documents */
.document-item {
    background: white;
    border: 1px solid var(--gray-200);
    border-radius: 16px;
    padding: 1.5rem;
    margin-bottom: 1rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    transition: all 0.3s ease;
}

.document-item:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
    border-color: var(--primary);
}

.doc-info {
    flex: 1;
}

.doc-name {
    font-weight: 700;
    color: var(--gray-800);
    margin-bottom: 0.5rem;
    font-size: 1.1rem;
}

.doc-meta {
    color: var(--gray-600);
    font-size: 0.9rem;
    display: flex;
    align-items: center;
    gap: 1rem;
    flex-wrap: wrap;
}

.doc-meta-item {
    display: flex;
    align-items: center;
    gap: 0.25rem;
}

.btn-download {
    background: linear-gradient(135deg, var(--primary), var(--primary-dark));
    color: white;
    padding: 0.75rem 1.5rem;
    border-radius: 12px;
    text-decoration: none;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    transition: all 0.3s ease;
    box-shadow: 0 4px 6px -1px rgba(59, 130, 246, 0.3);
}

.btn-download:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 15px -3px rgba(59, 130, 246, 0.4);
    color: white;
    text-decoration: none;
}

/* Payments */
.payment-item {
    background: white;
    border: 1px solid var(--gray-200);
    border-radius: 16px;
    padding: 1.5rem;
    margin-bottom: 1rem;
    transition: all 0.3s ease;
}

.payment-item:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
}

.payment-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
    flex-wrap: wrap;
    gap: 1rem;
}

.payment-amount {
    font-size: 1.3rem;
    font-weight: 800;
    color: var(--gray-800);
}

.payment-status {
    padding: 0.5rem 1rem;
    border-radius: 50px;
    font-size: 0.8rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.status-pending { background: #fef3c7; color: #92400e; }
.status-proof_submitted { background: #dbeafe; color: #1e40af; }
.status-under_review { background: #fef3c7; color: #92400e; }
.status-approved { background: #d1fae5; color: #065f46; }
.status-rejected { background: #fee2e2; color: #991b1b; }

.payment-details {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
    color: var(--gray-600);
    font-size: 0.9rem;
}

.payment-detail {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

/* Messages */
.message-item {
    padding: 1.5rem;
    margin-bottom: 1rem;
    border-radius: 16px;
    position: relative;
}

.message-admin {
    background: linear-gradient(135deg, #f1f5f9, #e2e8f0);
    border-left: 4px solid var(--primary);
}

.message-user {
    background: linear-gradient(135deg, #f0fdf4, #dcfce7);
    border-left: 4px solid var(--success);
}

.message-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
    flex-wrap: wrap;
    gap: 0.5rem;
}

.message-sender {
    font-weight: 700;
    color: var(--gray-800);
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.message-time {
    color: var(--gray-600);
    font-size: 0.9rem;
}

.message-content {
    color: var(--gray-700);
    line-height: 1.6;
}

/* Status History */
.timeline {
    position: relative;
    padding-left: 2rem;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 1rem;
    top: 0;
    bottom: 0;
    width: 2px;
    background: var(--gray-300);
}

.timeline-item {
    position: relative;
    padding-bottom: 2rem;
}

.timeline-item::before {
    content: '';
    position: absolute;
    left: -1.5rem;
    top: 0.5rem;
    width: 1rem;
    height: 1rem;
    background: var(--primary);
    border-radius: 50%;
    border: 3px solid white;
    box-shadow: 0 0 0 3px var(--primary);
}

.timeline-content {
    background: white;
    padding: 1.5rem;
    border-radius: 12px;
    border: 1px solid var(--gray-200);
    margin-left: 1rem;
}

.timeline-title {
    font-weight: 700;
    color: var(--gray-800);
    margin-bottom: 0.5rem;
}

.timeline-meta {
    color: var(--gray-600);
    font-size: 0.9rem;
    margin-bottom: 0.5rem;
}

.timeline-notes {
    color: var(--gray-700);
    font-style: italic;
}

/* Action Buttons */
.action-buttons {
    display: flex;
    gap: 1rem;
    margin-top: 2rem;
    flex-wrap: wrap;
}

.btn-action {
    padding: 1rem 2rem;
    border: none;
    border-radius: 12px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    text-decoration: none;
    display: flex;
    align-items: center;
    gap: 0.75rem;
    font-size: 1rem;
}

.btn-primary {
    background: linear-gradient(135deg, var(--primary), var(--primary-dark));
    color: white;
    box-shadow: 0 4px 6px -1px rgba(59, 130, 246, 0.3);
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 15px -3px rgba(59, 130, 246, 0.4);
    color: white;
    text-decoration: none;
}

.btn-warning {
    background: linear-gradient(135deg, var(--warning), #d97706);
    color: white;
    box-shadow: 0 4px 6px -1px rgba(245, 158, 11, 0.3);
}

.btn-warning:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 15px -3px rgba(245, 158, 11, 0.4);
    color: white;
    text-decoration: none;
}

/* Empty States */
.empty-state {
    text-align: center;
    padding: 3rem;
    color: var(--gray-500);
}

.empty-state-icon {
    font-size: 4rem;
    margin-bottom: 1rem;
    opacity: 0.5;
}

.empty-state h3 {
    margin-bottom: 0.5rem;
    color: var(--gray-700);
}

/* Responsive Design */
@media (max-width: 1024px) {
    .details-grid {
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    }
}

@media (max-width: 768px) {
    .app-container {
        padding: 1rem;
        margin-top: 60px;
    }
    
    .app-header {
        padding: 2rem 1.5rem;
    }
    
    .app-title-section {
        flex-direction: column;
        text-align: center;
    }
    
    .app-number {
        font-size: 2rem;
    }
    
    .overview-item {
        padding: 1rem;
    }
    
    .tabs-nav {
        padding: 0.5rem;
        gap: 0.25rem;
        justify-content: flex-start;
    }
    
    .tab-btn {
        padding: 0.875rem 1.5rem;
        font-size: 0.9rem;
        min-width: auto;
        flex-shrink: 0;
    }
    
    .tab-content {
        padding: 2rem 1.5rem;
    }
    
    .details-grid {
        grid-template-columns: 1fr;
    }
    
    .document-item,
    .payment-item {
        flex-direction: column;
        gap: 1rem;
        text-align: center;
    }
    
    .action-buttons {
        flex-direction: column;
    }
    
    .doc-meta {
        justify-content: center;
        text-align: center;
    }
    
    .payment-header {
        flex-direction: column;
        text-align: center;
        gap: 0.5rem;
    }
    
    .payment-details {
        grid-template-columns: 1fr;
        text-align: center;
    }
}

@media (max-width: 480px) {
    .app-header {
        padding: 1.5rem 1rem;
    }
    
    .app-number {
        font-size: 1.5rem;
    }
    
    .tab-content {
        padding: 1.5rem 1rem;
    }
    
    .content-card {
        padding: 1.5rem;
    }
    
    .tab-btn {
        padding: 0.75rem 1.25rem;
        font-size: 0.85rem;
        gap: 0.5rem;
    }
    
    .overview-item {
        padding: 0.875rem;
    }
    
    .overview-value {
        font-size: 1rem;
    }
    
    .detail-item {
        padding: 1.25rem;
    }
    
    .document-item,
    .payment-item {
        padding: 1.25rem;
    }
    
    .btn-action {
        padding: 0.875rem 1.5rem;
        font-size: 0.9rem;
    }
}
</style>

<div class="app-container">
    <!-- Modern Application Header -->
    <div class="app-header">
        <div class="header-content">
            <div class="app-title-section">
                <div>
                    <div class="app-number"><?= htmlspecialchars($application['application_number']) ?></div>
                    <div class="app-service"><?= htmlspecialchars($application['service_name']) ?></div>
                </div>
                <div class="status-badge status-<?= htmlspecialchars($application['status_code']) ?>">
                    <?= htmlspecialchars($application['status_name']) ?>
                </div>
            </div>
            
            <div class="app-overview">
                <div class="overview-item">
                    <div class="overview-label">Submitted</div>
                    <div class="overview-value"><?= date('M j, Y', strtotime($application['submitted_at'])) ?></div>
                </div>
                <div class="overview-item">
                    <div class="overview-label">Destination</div>
                    <div class="overview-value"><?= htmlspecialchars($application['destination_country_name'] ?: 'Not specified') ?></div>
                </div>
                <div class="overview-item">
                    <div class="overview-label">Total Amount</div>
                    <div class="overview-value"><?= number_format($application['total_amount'], 2) ?> <?= htmlspecialchars($application['currency']) ?></div>
                </div>
                <div class="overview-item">
                    <div class="overview-label">Payment Plan</div>
                    <div class="overview-value"><?= ucfirst($application['payment_plan']) ?></div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Modern Tabs Container -->
    <div class="tabs-container">
        <div class="tabs-nav">
            <button class="tab-btn active" onclick="switchTab('overview')">
                <i class="fas fa-info-circle"></i>
                Overview
            </button>
            <button class="tab-btn" onclick="switchTab('documents')">
                <i class="fas fa-file-alt"></i>
                Documents
                <span style="background: var(--primary); color: white; padding: 0.25rem 0.5rem; border-radius: 50px; font-size: 0.75rem; margin-left: 0.5rem;"><?= count($documents) ?></span>
            </button>
            <button class="tab-btn" onclick="switchTab('payments')">
                <i class="fas fa-credit-card"></i>
                Payments
                <span style="background: var(--success); color: white; padding: 0.25rem 0.5rem; border-radius: 50px; font-size: 0.75rem; margin-left: 0.5rem;"><?= count($payments) ?></span>
            </button>
            <button class="tab-btn" onclick="switchTab('messages')" style="display: none;">
                <i class="fas fa-comments"></i>
                Messages
                <span style="background: var(--warning); color: white; padding: 0.25rem 0.5rem; border-radius: 50px; font-size: 0.75rem; margin-left: 0.5rem;"><?= count($messages) ?></span>
            </button>
            <button class="tab-btn" onclick="switchTab('timeline')" style="display: none;">
                <i class="fas fa-history"></i>
                Timeline
            </button>
        </div>

        <!-- Overview Tab -->
        <div id="overview-tab" class="tab-content active">
            <div class="content-card">
                <h3 class="card-title">
                    <i class="fas fa-info-circle"></i>
                    Application Details
                </h3>
                <div class="details-grid">
                    <div class="detail-item">
                        <div class="detail-label">Application Type</div>
                        <div class="detail-value"><?= ucfirst($application['application_type']) ?></div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Travel Purpose</div>
                        <div class="detail-value"><?= htmlspecialchars($application['travel_purpose'] ?: 'Not specified') ?></div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Intended Travel Date</div>
                        <div class="detail-value"><?= $application['intended_travel_date'] ? date('M j, Y', strtotime($application['intended_travel_date'])) : 'Not specified' ?></div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Duration of Stay</div>
                        <div class="detail-value"><?= htmlspecialchars($application['duration_of_stay'] ?: 'Not specified') ?></div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Base Amount</div>
                        <div class="detail-value"><?= number_format($application['base_amount'], 2) ?> <?= htmlspecialchars($application['currency']) ?></div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Additional Fees</div>
                        <div class="detail-value"><?= number_format($application['additional_fees'], 2) ?> <?= htmlspecialchars($application['currency']) ?></div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Discount Amount</div>
                        <div class="detail-value"><?= number_format($application['discount_amount'], 2) ?> <?= htmlspecialchars($application['currency']) ?></div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Payment Method</div>
                        <div class="detail-value"><?= htmlspecialchars($application['payment_method_name'] ?: 'Not specified') ?></div>
                    </div>
                </div>
            </div>
            
            <?php if ($application['payment_plan'] === 'partial'): ?>
            <div class="content-card">
                <h3 class="card-title">
                    <i class="fas fa-calculator"></i>
                    Payment Breakdown
                </h3>
                <div class="details-grid">
                    <div class="detail-item">
                        <div class="detail-label">Initial Payment</div>
                        <div class="detail-value"><?= number_format($application['initial_payment_amount'], 2) ?> <?= htmlspecialchars($application['currency']) ?> (<?= number_format($application['initial_payment_percentage'], 1) ?>%)</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Remaining Payment</div>
                        <div class="detail-value"><?= number_format($application['remaining_payment_amount'], 2) ?> <?= htmlspecialchars($application['currency']) ?> (<?= number_format($application['remaining_payment_percentage'], 1) ?>%)</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Amount Paid So Far</div>
                        <div class="detail-value"><?= number_format($application['amount_paid_so_far'], 2) ?> <?= htmlspecialchars($application['currency']) ?></div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Payment Completion</div>
                        <div class="detail-value"><?= number_format($application['payment_completion_percentage'], 1) ?>%</div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Action Buttons -->
            <div class="action-buttons">
                <?php if ($application['status_code'] === 'documents_required'): ?>
                    <a href="application-documents.php?app=<?= $application['id'] ?>" class="btn-action btn-warning">
                        <i class="fas fa-upload"></i>
                        Upload Required Documents
                    </a>
                <?php endif; ?>
                
                <?php if (in_array($application['status_code'], ['submitted', 'approved']) && ($application['total_amount'] - $application['amount_paid_so_far']) > 0): ?>
                    <a href="application-payment.php?app=<?= $application['id'] ?>" class="btn-action btn-primary">
                        <i class="fas fa-credit-card"></i>
                        Pay Remaining (<?= number_format($application['total_amount'] - $application['amount_paid_so_far'], 2) ?> <?= htmlspecialchars($application['currency']) ?>)
                    </a>
                <?php endif; ?>
                
                <?php if (in_array($application['status_code'], ['approved', 'completed']) && $application['refund_status'] === 'none' && $application['amount_paid_so_far'] > 0): ?>
                    <button onclick="requestRefund(<?= $application['id'] ?>, <?= $application['amount_paid_so_far'] ?>, '<?= $application['currency'] ?>')" class="btn-action" style="background: linear-gradient(135deg, #f59e0b, #d97706); color: white;">
                        <i class="fas fa-undo-alt"></i>
                        Request Refund
                    </button>
                <?php endif; ?>
                
                <a href="profile.php" class="btn-action" style="background: var(--gray-600); color: white;">
                    <i class="fas fa-arrow-left"></i>
                    Back to Applications
                </a>
            </div>
        </div>

        <!-- Documents Tab -->
        <div id="documents-tab" class="tab-content">
            <div class="content-card">
                <h3 class="card-title">
                    <i class="fas fa-file-alt"></i>
                    Uploaded Documents
                </h3>
                <?php if (empty($documents)): ?>
                    <div class="empty-state">
                        <div class="empty-state-icon">
                            <i class="fas fa-file-alt"></i>
                        </div>
                        <h3>No Documents Uploaded</h3>
                        <p>No documents have been uploaded for this application yet.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($documents as $doc): ?>
                        <div class="document-item">
                            <div class="doc-info">
                                <div class="doc-name"><?= htmlspecialchars($doc['document_type']) ?></div>
                                <div class="doc-meta">
                                    <div class="doc-meta-item">
                                        <i class="fas fa-file"></i>
                                        <?= htmlspecialchars($doc['original_filename']) ?>
                                    </div>
                                    <div class="doc-meta-item">
                                        <i class="fas fa-weight"></i>
                                        <?= number_format($doc['file_size'] / 1024, 1) ?> KB
                                    </div>
                                    <div class="doc-meta-item">
                                        <i class="fas fa-calendar"></i>
                                        <?= date('M j, Y g:i A', strtotime($doc['uploaded_at'])) ?>
                                    </div>
                                    <?php if ($doc['is_verified']): ?>
                                        <div class="doc-meta-item" style="color: var(--success);">
                                            <i class="fas fa-check-circle"></i>
                                            Verified
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <a href="<?= htmlspecialchars($doc['file_path']) ?>" class="btn-download" target="_blank">
                                <i class="fas fa-download"></i>
                                Download
                            </a>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Payments Tab -->
        <div id="payments-tab" class="tab-content">
            <!-- Payment Summary Card -->
            <div class="content-card">
                <h3 class="card-title">
                    <i class="fas fa-wallet"></i>
                    Payment Summary
                </h3>
                <div class="details-grid">
                    <div class="detail-item">
                        <div class="detail-label">Total Amount</div>
                        <div class="detail-value"><?= number_format($application['total_amount'], 2) ?> <?= htmlspecialchars($application['currency']) ?></div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Amount Paid</div>
                        <div class="detail-value" style="color: var(--success);"><?= number_format($application['amount_paid_so_far'], 2) ?> <?= htmlspecialchars($application['currency']) ?></div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Remaining Balance</div>
                        <div class="detail-value" style="color: var(--danger);"><?= number_format($application['total_amount'] - $application['amount_paid_so_far'], 2) ?> <?= htmlspecialchars($application['currency']) ?></div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Payment Progress</div>
                        <div class="detail-value"><?= number_format($application['payment_completion_percentage'], 1) ?>%</div>
                    </div>
                </div>
                
                <?php if ($application['payment_plan'] === 'partial'): ?>
                <div style="margin-top: 2rem; padding: 1.5rem; background: white; border-radius: 12px; border: 2px solid var(--gray-200);">
                    <h4 style="margin-bottom: 1rem; color: var(--gray-800);"><i class="fas fa-list"></i> Payment Plan Breakdown</h4>
                    <div class="details-grid">
                        <div class="detail-item" style="border-left-color: var(--primary);">
                            <div class="detail-label">Initial Payment</div>
                            <div class="detail-value"><?= number_format($application['initial_payment_amount'], 2) ?> <?= htmlspecialchars($application['currency']) ?></div>
                            <div style="margin-top: 0.5rem;">
                                <span class="payment-status status-<?= strtolower(str_replace(' ', '_', $application['initial_payment_status'])) ?>">
                                    <?= ucfirst(str_replace('_', ' ', $application['initial_payment_status'])) ?>
                                </span>
                            </div>
                        </div>
                        <div class="detail-item" style="border-left-color: var(--warning);">
                            <div class="detail-label">Final Payment</div>
                            <div class="detail-value"><?= number_format($application['remaining_payment_amount'], 2) ?> <?= htmlspecialchars($application['currency']) ?></div>
                            <div style="margin-top: 0.5rem;">
                                <span class="payment-status status-<?= strtolower(str_replace(' ', '_', $application['final_payment_status'])) ?>">
                                    <?= ucfirst(str_replace('_', ' ', $application['final_payment_status'])) ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- Payment Reminder for Final Payment -->
            <?php if ($application['payment_plan'] === 'partial' && $application['initial_payment_status'] === 'approved' && $application['remaining_payment_amount'] > 0): ?>
            <div class="content-card" style="background: linear-gradient(135deg, #fef3c7, #fde68a); border: 2px solid #f59e0b;">
                <h3 class="card-title" style="color: #92400e; border-bottom-color: #f59e0b;">
                    <i class="fas fa-exclamation-triangle"></i>
                    Final Payment Required
                </h3>
                <div style="color: #92400e; margin-bottom: 1.5rem;">
                    <p style="font-size: 1.1rem; font-weight: 600; margin-bottom: 0.5rem;">
                        Your initial payment of <?= number_format($application['initial_payment_amount'], 2) ?> <?= htmlspecialchars($application['currency']) ?> has been approved!
                    </p>
                    <p style="font-size: 1rem;">
                        Please complete your final payment of <strong><?= number_format($application['remaining_payment_amount'], 2) ?> <?= htmlspecialchars($application['currency']) ?></strong> to proceed with your application.
                    </p>
                </div>
                <a href="application-payment.php?app=<?= $application['id'] ?>&type=final" class="btn-action btn-warning" style="display: inline-flex;">
                    <i class="fas fa-credit-card"></i>
                    Pay Final Amount (<?= number_format($application['remaining_payment_amount'], 2) ?> <?= htmlspecialchars($application['currency']) ?>)
                </a>
            </div>
            <?php endif; ?>
            
            <!-- Payment History -->
            <div class="content-card">
                <h3 class="card-title">
                    <i class="fas fa-history"></i>
                    Payment History & Status
                </h3>
                <?php if (empty($payments)): ?>
                    <div class="empty-state">
                        <div class="empty-state-icon">
                            <i class="fas fa-credit-card"></i>
                        </div>
                        <h3>No Payments Made</h3>
                        <p>No payments have been recorded for this application yet.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($payments as $payment): ?>
                        <div class="payment-item">
                            <div class="payment-header">
                                <div class="payment-amount">
                                    <?= number_format($payment['amount'], 2) ?> <?= htmlspecialchars($payment['currency']) ?>
                                    <?php if (isset($payment['payment_type']) && $payment['payment_type'] !== 'full'): ?>
                                        <span style="font-size: 0.8rem; color: var(--gray-600);">
                                            (<?= ucfirst(str_replace('_', ' ', $payment['payment_type'])) ?> Payment)
                                        </span>
                                    <?php endif; ?>
                                </div>
                                <span class="payment-status status-<?= htmlspecialchars($payment['payment_status']) ?>">
                                    <?= ucfirst(str_replace('_', ' ', $payment['payment_status'])) ?>
                                </span>
                            </div>
                            <div class="payment-details">
                                <div class="payment-detail">
                                    <i class="fas fa-credit-card"></i>
                                    <?= htmlspecialchars($payment['payment_method_name'] ?? 'N/A') ?>
                                </div>
                                <?php if (!empty($payment['transaction_reference'])): ?>
                                <div class="payment-detail">
                                    <i class="fas fa-hashtag"></i>
                                    Ref: <?= htmlspecialchars($payment['transaction_reference']) ?>
                                </div>
                                <?php endif; ?>
                                <div class="payment-detail">
                                    <i class="fas fa-calendar"></i>
                                    <?= date('M j, Y g:i A', strtotime($payment['created_at'])) ?>
                                </div>
                            </div>
                            
                            <?php if ($payment['payment_status'] === 'approved'): ?>
                            <div style="margin-top: 1rem; padding: 1rem; background: #d1fae5; border-radius: 8px; border-left: 3px solid var(--success);">
                                <div style="font-weight: 600; color: #065f46; display: flex; align-items: center; gap: 0.5rem;">
                                    <i class="fas fa-check-circle"></i> Payment Approved
                                </div>
                            </div>
                            <?php elseif ($payment['payment_status'] === 'rejected'): ?>
                            <div style="margin-top: 1rem; padding: 1rem; background: #fee2e2; border-radius: 8px; border-left: 3px solid var(--danger);">
                                <div style="font-weight: 600; color: #991b1b; display: flex; align-items: center; gap: 0.5rem;">
                                    <i class="fas fa-times-circle"></i> Payment Rejected
                                </div>
                            </div>
                            <?php elseif ($payment['payment_status'] === 'proof_submitted'): ?>
                            <div style="margin-top: 1rem; padding: 1rem; background: #dbeafe; border-radius: 8px; border-left: 3px solid var(--primary);">
                                <div style="font-weight: 600; color: #1e40af; display: flex; align-items: center; gap: 0.5rem;">
                                    <i class="fas fa-clock"></i> Awaiting Admin Review
                                </div>
                                <div style="color: #1d4ed8; font-size: 0.9rem; margin-top: 0.25rem;">
                                    Your payment proof is being reviewed by our team.
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Messages Tab -->
        <div id="messages-tab" class="tab-content">
            <div class="content-card">
                <h3 class="card-title">
                    <i class="fas fa-comments"></i>
                    Communication History
                </h3>
                <?php if (empty($messages)): ?>
                    <div class="empty-state">
                        <div class="empty-state-icon">
                            <i class="fas fa-comments"></i>
                        </div>
                        <h3>No Messages</h3>
                        <p>No messages have been exchanged for this application yet.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($messages as $msg): ?>
                        <div class="message-item message-<?= $msg['sender_type'] ?>">
                            <div class="message-header">
                                <div class="message-sender">
                                    <i class="fas fa-<?= $msg['sender_type'] === 'admin' ? 'user-tie' : 'user' ?>"></i>
                                    <?= htmlspecialchars($msg['sender_name']) ?>
                                </div>
                                <div class="message-time">
                                    <?= date('M j, Y g:i A', strtotime($msg['created_at'])) ?>
                                </div>
                            </div>
                            <div class="message-content">
                                <?= nl2br(htmlspecialchars($msg['message'])) ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Timeline Tab -->
        <div id="timeline-tab" class="tab-content">
            <div class="content-card">
                <h3 class="card-title">
                    <i class="fas fa-history"></i>
                    Status History
                </h3>
                <?php if (empty($status_history)): ?>
                    <div class="empty-state">
                        <div class="empty-state-icon">
                            <i class="fas fa-history"></i>
                        </div>
                        <h3>No Status Changes</h3>
                        <p>No status changes have been recorded for this application yet.</p>
                    </div>
                <?php else: ?>
                    <div class="timeline">
                        <?php foreach ($status_history as $history): ?>
                            <div class="timeline-item">
                                <div class="timeline-content">
                                    <div class="timeline-title">
                                        Status changed from "<?= htmlspecialchars($history['from_status_name'] ?: 'Initial') ?>" 
                                        to "<?= htmlspecialchars($history['to_status_name']) ?>"
                                    </div>
                                    <div class="timeline-meta">
                                        <?= date('M j, Y g:i A', strtotime($history['changed_at'])) ?>
                                    </div>
                                    <?php if ($history['change_reason']): ?>
                                        <div class="timeline-notes">
                                            Reason: <?= htmlspecialchars($history['change_reason']) ?>
                                        </div>
                                    <?php endif; ?>
                                    <?php if ($history['notes']): ?>
                                        <div class="timeline-notes">
                                            Notes: <?= htmlspecialchars($history['notes']) ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
function switchTab(tabName) {
    document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
    document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));
    
    event.target.classList.add('active');
    document.getElementById(tabName + '-tab').classList.add('active');
}

function requestRefund(appId, amount, currency) {
    const modal = document.createElement('div');
    modal.className = 'modal-overlay';
    modal.style.cssText = 'position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.6);display:flex;align-items:center;justify-content:center;z-index:1000;';
    modal.innerHTML = `
        <div style="background:white;border-radius:20px;padding:2rem;max-width:500px;width:90%;">
            <h3 style="margin-bottom:1rem;color:#1e293b;"><i class="fas fa-undo-alt"></i> Request Refund</h3>
            <form id="refundForm">
                <div style="margin-bottom:1rem;">
                    <label style="display:block;margin-bottom:0.5rem;font-weight:600;">Refund Amount</label>
                    <input type="number" name="amount" value="${amount}" readonly style="width:100%;padding:0.75rem;border:2px solid #e2e8f0;border-radius:8px;">
                </div>
                <div style="margin-bottom:1rem;">
                    <label style="display:block;margin-bottom:0.5rem;font-weight:600;">Reason</label>
                    <textarea name="reason" required style="width:100%;padding:0.75rem;border:2px solid #e2e8f0;border-radius:8px;min-height:80px;"></textarea>
                </div>
                <div style="margin-bottom:1rem;">
                    <label style="display:block;margin-bottom:0.5rem;font-weight:600;">Bank Name</label>
                    <input type="text" name="bank_name" required style="width:100%;padding:0.75rem;border:2px solid #e2e8f0;border-radius:8px;">
                </div>
                <div style="margin-bottom:1rem;">
                    <label style="display:block;margin-bottom:0.5rem;font-weight:600;">Account Number</label>
                    <input type="text" name="account_number" required style="width:100%;padding:0.75rem;border:2px solid #e2e8f0;border-radius:8px;">
                </div>
                <div style="margin-bottom:1rem;">
                    <label style="display:block;margin-bottom:0.5rem;font-weight:600;">Account Name</label>
                    <input type="text" name="account_name" required style="width:100%;padding:0.75rem;border:2px solid #e2e8f0;border-radius:8px;">
                </div>
                <div style="display:flex;gap:0.5rem;">
                    <button type="button" onclick="this.closest('.modal-overlay').remove()" style="flex:1;padding:0.75rem;border:none;border-radius:8px;background:#e2e8f0;cursor:pointer;">Cancel</button>
                    <button type="submit" style="flex:1;padding:0.75rem;border:none;border-radius:8px;background:#f59e0b;color:white;cursor:pointer;">Submit</button>
                </div>
            </form>
        </div>
    `;
    document.body.appendChild(modal);
    
    modal.querySelector('#refundForm').onsubmit = function(e) {
        e.preventDefault();
        const formData = new URLSearchParams({
            application_id: appId,
            amount: this.amount.value,
            reason: this.reason.value,
            bank_name: this.bank_name.value,
            account_number: this.account_number.value,
            account_name: this.account_name.value
        });
        
        fetch('request-refund.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: formData
        })
        .then(r => r.json())
        .then(d => {
            alert(d.message);
            if (d.success) location.reload();
        })
        .catch(() => alert('Error submitting refund request'));
    };
}
</script>

<?php include 'includes/footer.php'; ?>