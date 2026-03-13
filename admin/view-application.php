<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit;
}

require_once '../config/database.php';

$applicationId = $_GET['id'] ?? null;
if (!$applicationId) {
    header('Location: applications.php');
    exit;
}

try {
    $pdo = getConnection();
    
    // Get application details with proper joins
    $stmt = $pdo->prepare("
        SELECT a.*, u.first_name, u.last_name, u.email, u.phone, u.date_of_birth,
               s.name as service_name, s.base_price, s.currency as service_currency,
               st.status_name, c.name as destination_country, pm.name as payment_method_name
        FROM applications a
        JOIN users u ON a.user_id = u.id
        JOIN services s ON a.service_id = s.id
        JOIN application_statuses st ON a.status_id = st.id
        LEFT JOIN countries c ON a.destination_country_id = c.id
        LEFT JOIN payment_methods pm ON a.payment_method_id = pm.id
        WHERE a.id = ?
    ");
    $stmt->execute([$applicationId]);
    $application = $stmt->fetch();
    
    if (!$application) {
        header('Location: applications.php');
        exit;
    }
    
    // Get documents
    $docsStmt = $pdo->prepare("SELECT * FROM documents WHERE application_id = ? ORDER BY uploaded_at DESC");
    $docsStmt->execute([$applicationId]);
    $documents = $docsStmt->fetchAll();
    
} catch (Exception $e) {
    header('Location: applications.php');
    exit;
}

$pageTitle = 'View Application';
?>

<?php include 'includes/header.php'; ?>
<?php include 'includes/sidebar.php'; ?>

<main class="main-content">
    <div class="page-header">
        <div class="header-content">
            <div class="header-info">
                <h1><i class="fas fa-file-alt"></i> Application Details</h1>
                <p class="application-number"><?= htmlspecialchars($application['application_number']) ?></p>
            </div>
            <div class="header-actions">
                <div class="status-badge status-<?= strtolower($application['status_name']) ?>">
                    <?= htmlspecialchars($application['status_name']) ?>
                </div>
                <?php if ($application['application_type'] === 'manual'): ?>
                <button onclick="toggleEditMode()" class="btn btn-warning" id="editBtn">
                    <i class="fas fa-edit"></i> Edit
                </button>
                <button onclick="saveChanges()" class="btn btn-success" id="saveBtn" style="display: none;">
                    <i class="fas fa-save"></i> Save
                </button>
                <button onclick="cancelEdit()" class="btn btn-secondary" id="cancelBtn" style="display: none;">
                    <i class="fas fa-times"></i> Cancel
                </button>
                <?php endif; ?>
                <button onclick="exportApplication()" class="btn btn-primary">
                    <i class="fas fa-download"></i> Export Report
                </button>
                <a href="applications.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back
                </a>
            </div>
        </div>
    </div>
    
    <div class="application-container">
        <div class="tab-navigation">
            <button class="tab-btn active" data-tab="overview">
                <i class="fas fa-info-circle"></i>
                <span>Overview</span>
            </button>
            <button class="tab-btn" data-tab="applicant">
                <i class="fas fa-user"></i>
                <span>Applicant</span>
            </button>
            <button class="tab-btn" data-tab="documents">
                <i class="fas fa-file-upload"></i>
                <span>Documents</span>
                <span class="badge"><?= count($documents) ?></span>
            </button>
            <button class="tab-btn" data-tab="payments">
                <i class="fas fa-credit-card"></i>
                <span>Payments</span>
            </button>
        </div>
        
        <div class="tab-content">
            <!-- Overview Tab -->
            <div class="tab-pane active" id="overview">
                <div class="overview-grid">
                    <div class="info-card">
                        <div class="card-header">
                            <h3><i class="fas fa-clipboard-list"></i> Application Information</h3>
                        </div>
                        <div class="card-body">
                            <div class="info-grid">
                                <div class="info-item">
                                    <label>Service</label>
                                    <span><?= htmlspecialchars($application['service_name']) ?></span>
                                </div>
                                <div class="info-item">
                                    <label>Application Type</label>
                                    <span class="type-badge type-<?= $application['application_type'] ?>">
                                        <?= ucfirst($application['application_type']) ?>
                                    </span>
                                </div>
                                <div class="info-item">
                                    <label>Destination</label>
                                    <span class="view-mode"><?= htmlspecialchars($application['destination_country'] ?? 'Not specified') ?></span>
                                    <select class="form-control edit-mode" name="destination_country_id" style="display: none;">
                                        <option value="">Select Country</option>
                                        <?php
                                        $countries = $pdo->query("SELECT id, name FROM countries WHERE is_active = 1 ORDER BY name")->fetchAll();
                                        foreach ($countries as $country):
                                        ?>
                                        <option value="<?= $country['id'] ?>" <?= $application['destination_country_id'] == $country['id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($country['name']) ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="info-item">
                                    <label>Travel Purpose</label>
                                    <span class="view-mode"><?= htmlspecialchars($application['travel_purpose'] ?? 'Not specified') ?></span>
                                    <input type="text" class="form-control edit-mode" name="travel_purpose" value="<?= htmlspecialchars($application['travel_purpose'] ?? '') ?>" style="display: none;">
                                </div>
                                <div class="info-item">
                                    <label>Travel Date</label>
                                    <span class="view-mode"><?= $application['intended_travel_date'] ? date('M j, Y', strtotime($application['intended_travel_date'])) : 'Not specified' ?></span>
                                    <input type="date" class="form-control edit-mode" name="intended_travel_date" value="<?= $application['intended_travel_date'] ?>" style="display: none;">
                                </div>
                                <div class="info-item">
                                    <label>Duration</label>
                                    <span class="view-mode"><?= htmlspecialchars($application['duration_of_stay'] ?? 'Not specified') ?></span>
                                    <input type="text" class="form-control edit-mode" name="duration_of_stay" value="<?= htmlspecialchars($application['duration_of_stay'] ?? '') ?>" style="display: none;">
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="info-card">
                        <div class="card-header">
                            <h3><i class="fas fa-dollar-sign"></i> Payment Summary</h3>
                        </div>
                        <div class="card-body">
                            <div class="payment-summary">
                                <div class="amount-display">
                                    <div class="total-amount">
                                        <span class="currency"><?= $application['currency'] ?></span>
                                        <span class="amount"><?= number_format($application['total_amount'], 2) ?></span>
                                    </div>
                                    <div class="amount-label">Total Amount</div>
                                </div>
                                
                                <div class="payment-details">
                                    <div class="payment-item">
                                        <span>Payment Plan</span>
                                        <span class="plan-badge plan-<?= $application['payment_plan'] ?>">
                                            <?= ucfirst($application['payment_plan']) ?>
                                        </span>
                                    </div>
                                    <?php if ($application['payment_method_name']): ?>
                                    <div class="payment-item">
                                        <span>Payment Method</span>
                                        <span class="payment-method"><?= htmlspecialchars($application['payment_method_name']) ?></span>
                                    </div>
                                    <?php endif; ?>
                                    <div class="payment-item">
                                        <span>Amount Paid</span>
                                        <span><?= $application['currency'] ?> <?= number_format($application['amount_paid_so_far'], 2) ?></span>
                                    </div>
                                    <div class="payment-item">
                                        <span>Completion</span>
                                        <div class="progress-container">
                                            <div class="progress-bar">
                                                <div class="progress-fill" style="width: <?= $application['payment_completion_percentage'] ?>%"></div>
                                            </div>
                                            <span class="progress-text"><?= number_format($application['payment_completion_percentage'], 1) ?>%</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <?php if ($application['internal_notes']): ?>
                <div class="info-card">
                    <div class="card-header">
                        <h3><i class="fas fa-sticky-note"></i> Internal Notes</h3>
                    </div>
                    <div class="card-body">
                        <div class="notes-content">
                            <?= nl2br(htmlspecialchars($application['internal_notes'])) ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- Applicant Tab -->
            <div class="tab-pane" id="applicant">
                <div class="info-card">
                    <div class="card-header">
                        <h3><i class="fas fa-user-circle"></i> Applicant Information</h3>
                    </div>
                    <div class="card-body">
                        <div class="applicant-profile">
                            <div class="profile-avatar">
                                <i class="fas fa-user"></i>
                            </div>
                            <div class="profile-info">
                                <h4><?= htmlspecialchars($application['first_name'] . ' ' . $application['last_name']) ?></h4>
                                <p class="profile-email"><?= htmlspecialchars($application['email']) ?></p>
                            </div>
                        </div>
                        
                        <div class="info-grid">
                            <div class="info-item">
                                <label>Phone Number</label>
                                <span><?= htmlspecialchars($application['phone'] ?? 'Not provided') ?></span>
                            </div>
                            <div class="info-item">
                                <label>Date of Birth</label>
                                <span><?= $application['date_of_birth'] ? date('M j, Y', strtotime($application['date_of_birth'])) : 'Not provided' ?></span>
                            </div>
                            <div class="info-item">
                                <label>Application Submitted</label>
                                <span><?= date('M j, Y g:i A', strtotime($application['submitted_at'])) ?></span>
                            </div>
                            <div class="info-item">
                                <label>Last Updated</label>
                                <span><?= date('M j, Y g:i A', strtotime($application['last_updated_at'])) ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Documents Tab -->
            <div class="tab-pane" id="documents">
                <div class="info-card">
                    <div class="card-header">
                        <h3><i class="fas fa-folder-open"></i> Uploaded Documents</h3>
                    </div>
                    <div class="card-body">
                        <?php if (empty($documents)): ?>
                            <div class="empty-state">
                                <i class="fas fa-file-alt"></i>
                                <h4>No Documents</h4>
                                <p>No documents have been uploaded for this application yet.</p>
                            </div>
                        <?php else: ?>
                            <div class="documents-grid">
                                <?php foreach ($documents as $doc): ?>
                                    <div class="document-item">
                                        <div class="document-icon">
                                            <i class="fas fa-file-<?= strpos($doc['mime_type'], 'image') !== false ? 'image' : 'pdf' ?>"></i>
                                        </div>
                                        <div class="document-info">
                                            <h5><?= htmlspecialchars($doc['document_type']) ?></h5>
                                            <p class="document-name"><?= htmlspecialchars($doc['original_filename']) ?></p>
                                            <div class="document-meta">
                                                <span class="file-size"><?= number_format($doc['file_size'] / 1024 / 1024, 2) ?> MB</span>
                                                <span class="upload-date"><?= date('M j, Y', strtotime($doc['uploaded_at'])) ?></span>
                                            </div>
                                        </div>
                                        <div class="document-actions">
                                            <a href="../<?= htmlspecialchars($doc['file_path']) ?>" target="_blank" class="btn btn-sm btn-primary">
                                                <i class="fas fa-eye"></i> View
                                            </a>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Payments Tab -->
            <div class="tab-pane" id="payments">
                <div class="info-card">
                    <div class="card-header">
                        <h3><i class="fas fa-receipt"></i> Payment Breakdown</h3>
                    </div>
                    <div class="card-body">
                        <div class="payment-breakdown">
                            <div class="breakdown-item">
                                <span>Base Amount</span>
                                <span><?= $application['currency'] ?> <?= number_format($application['base_amount'], 2) ?></span>
                            </div>
                            <?php if ($application['additional_fees'] > 0): ?>
                            <div class="breakdown-item">
                                <span>Additional Fees</span>
                                <span><?= $application['currency'] ?> <?= number_format($application['additional_fees'], 2) ?></span>
                            </div>
                            <?php endif; ?>
                            <?php if ($application['discount_amount'] > 0): ?>
                            <div class="breakdown-item discount">
                                <span>Discount</span>
                                <span>-<?= $application['currency'] ?> <?= number_format($application['discount_amount'], 2) ?></span>
                            </div>
                            <?php endif; ?>
                            <div class="breakdown-item total">
                                <span>Total Amount</span>
                                <span><?= $application['currency'] ?> <?= number_format($application['total_amount'], 2) ?></span>
                            </div>
                        </div>
                        
                        <?php if ($application['payment_plan'] === 'partial'): ?>
                        <div class="installment-info">
                            <h4>Payment Plan Details</h4>
                            <div class="installment-grid">
                                <div class="installment-item">
                                    <div class="installment-header">
                                        <span>Initial Payment</span>
                                        <span class="status-badge status-<?= strtolower($application['initial_payment_status']) ?>">
                                            <?= ucfirst($application['initial_payment_status']) ?>
                                        </span>
                                    </div>
                                    <div class="installment-amount">
                                        <?= $application['currency'] ?> <?= number_format($application['initial_payment_amount'], 2) ?>
                                        <small>(<?= number_format($application['initial_payment_percentage'], 1) ?>%)</small>
                                    </div>
                                    <?php 
                                    // Online applications: Need approval for all payments
                                    if ($application['application_type'] === 'online' && $application['initial_payment_status'] === 'pending' && $application['initial_payment_proof']): ?>
                                    <div class="payment-actions">
                                        <button class="btn btn-success btn-sm" onclick="updatePaymentStatus(<?= $applicationId ?>, 'initial', 'completed')">
                                            <i class="fas fa-check"></i> Approve
                                        </button>
                                        <button class="btn btn-danger btn-sm" onclick="updatePaymentStatus(<?= $applicationId ?>, 'initial', 'failed')">
                                            <i class="fas fa-times"></i> Reject
                                        </button>
                                    </div>
                                    <?php 
                                    // Manual applications: Auto-complete initial payment
                                    elseif ($application['application_type'] === 'manual' && $application['initial_payment_status'] === 'pending'): ?>
                                    <div class="payment-actions">
                                        <button class="btn btn-success btn-sm" onclick="updatePaymentStatus(<?= $applicationId ?>, 'initial', 'completed')">
                                            <i class="fas fa-check"></i> Mark as Completed
                                        </button>
                                    </div>
                                    <?php endif; ?>
                                </div>
                                
                                <?php if ($application['remaining_payment_amount'] > 0): ?>
                                <div class="installment-item">
                                    <div class="installment-header">
                                        <span>Remaining Payment</span>
                                        <span class="status-badge status-<?= strtolower($application['final_payment_status']) ?>">
                                            <?= ucfirst($application['final_payment_status']) ?>
                                        </span>
                                    </div>
                                    <div class="installment-amount">
                                        <?= $application['currency'] ?> <?= number_format($application['remaining_payment_amount'], 2) ?>
                                        <small>(<?= number_format($application['remaining_payment_percentage'], 1) ?>%)</small>
                                    </div>
                                    <?php 
                                    // Online applications: Need approval for final payment
                                    if ($application['application_type'] === 'online' && $application['final_payment_status'] === 'pending' && $application['final_payment_proof']): ?>
                                    <div class="payment-actions">
                                        <button class="btn btn-success btn-sm" onclick="updatePaymentStatus(<?= $applicationId ?>, 'final', 'completed')">
                                            <i class="fas fa-check"></i> Approve
                                        </button>
                                        <button class="btn btn-danger btn-sm" onclick="updatePaymentStatus(<?= $applicationId ?>, 'final', 'failed')">
                                            <i class="fas fa-times"></i> Reject
                                        </button>
                                    </div>
                                    <?php 
                                    // Manual applications: Different logic for cash vs non-cash
                                    elseif ($application['application_type'] === 'manual'):
                                        if ($application['payment_method_name'] === 'Cash Payment' && $application['final_payment_status'] === 'not_required'): ?>
                                    <div class="payment-actions">
                                        <button class="btn btn-success btn-sm" onclick="updatePaymentStatus(<?= $applicationId ?>, 'final', 'completed')">
                                            <i class="fas fa-check"></i> Accept Payment
                                        </button>
                                        <button class="btn btn-danger btn-sm" onclick="updatePaymentStatus(<?= $applicationId ?>, 'final', 'failed')">
                                            <i class="fas fa-times"></i> Reject Payment
                                        </button>
                                    </div>
                                    <?php elseif ($application['payment_method_name'] !== 'Cash Payment' && $application['final_payment_status'] === 'not_required' && !$application['final_payment_proof'] && $application['initial_payment_status'] === 'completed'): ?>
                                    <div class="payment-upload">
                                        <form id="remainingPaymentForm" enctype="multipart/form-data">
                                            <input type="hidden" name="application_id" value="<?= $applicationId ?>">
                                            <div class="upload-section">
                                                <input type="file" id="remainingPaymentFile" name="payment_proof" accept="image/*,.pdf" required>
                                                <label for="remainingPaymentFile" class="upload-btn">
                                                    <i class="fas fa-cloud-upload-alt"></i> Upload Remaining Payment Proof
                                                </label>
                                                <div class="file-info" id="remainingFileInfo" style="display: none;"></div>
                                            </div>
                                            <button type="submit" class="btn btn-primary btn-sm" style="margin-top: 0.5rem;">
                                                <i class="fas fa-upload"></i> Submit Proof
                                            </button>
                                        </form>
                                    </div>
                                    <?php elseif ($application['final_payment_status'] === 'pending'): ?>
                                    <div class="payment-actions">
                                        <button class="btn btn-success btn-sm" onclick="updatePaymentStatus(<?= $applicationId ?>, 'final', 'completed')">
                                            <i class="fas fa-check"></i> Accept Payment
                                        </button>
                                    </div>
                                    <?php endif; endif; ?>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php 
                        // Handle full payment scenarios
                        elseif ($application['payment_plan'] === 'full'): ?>
                        <div class="installment-info">
                            <h4>Full Payment</h4>
                            <div class="installment-grid">
                                <div class="installment-item">
                                    <div class="installment-header">
                                        <span>Full Payment</span>
                                        <span class="status-badge status-<?= strtolower($application['initial_payment_status']) ?>">
                                            <?= ucfirst($application['initial_payment_status']) ?>
                                        </span>
                                    </div>
                                    <div class="installment-amount">
                                        <?= $application['currency'] ?> <?= number_format($application['total_amount'], 2) ?>
                                        <small>(100%)</small>
                                    </div>
                                    <?php 
                                    // Online applications: Need approval
                                    if ($application['application_type'] === 'online' && $application['initial_payment_status'] === 'pending' && $application['initial_payment_proof']): ?>
                                    <div class="payment-actions">
                                        <button class="btn btn-success btn-sm" onclick="updatePaymentStatus(<?= $applicationId ?>, 'initial', 'completed')">
                                            <i class="fas fa-check"></i> Approve
                                        </button>
                                        <button class="btn btn-danger btn-sm" onclick="updatePaymentStatus(<?= $applicationId ?>, 'initial', 'failed')">
                                            <i class="fas fa-times"></i> Reject
                                        </button>
                                    </div>
                                    <?php 
                                    // Manual applications: Auto-complete
                                    elseif ($application['application_type'] === 'manual' && $application['initial_payment_status'] === 'pending'): ?>
                                    <div class="payment-actions">
                                        <button class="btn btn-success btn-sm" onclick="updatePaymentStatus(<?= $applicationId ?>, 'initial', 'completed')">
                                            <i class="fas fa-check"></i> Mark as Completed
                                        </button>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <?php if ($application['initial_payment_proof'] || $application['final_payment_proof']): ?>
                        <div class="payment-proofs">
                            <h4>Payment Proofs</h4>
                            
                            <?php if ($application['initial_payment_proof']): ?>
                            <div class="proof-item">
                                <div class="proof-icon">
                                    <?php 
                                    $fileExt = strtolower(pathinfo($application['initial_payment_proof'], PATHINFO_EXTENSION));
                                    $isImage = in_array($fileExt, ['jpg', 'jpeg', 'png', 'gif', 'webp']);
                                    ?>
                                    <i class="fas fa-file-<?= $isImage ? 'image' : 'pdf' ?>"></i>
                                </div>
                                <div class="proof-info">
                                    <h5>Initial Payment Proof</h5>
                                    <p class="proof-name"><?= htmlspecialchars($application['initial_payment_proof']) ?></p>
                                    <div class="proof-meta">
                                        <?php if ($application['payment_method_name']): ?>
                                            <span class="payment-method"><?= htmlspecialchars($application['payment_method_name']) ?></span>
                                        <?php endif; ?>
                                        <?php if ($application['initial_transaction_reference']): ?>
                                            <span class="transaction-ref">Ref: <?= htmlspecialchars($application['initial_transaction_reference']) ?></span>
                                        <?php endif; ?>
                                        <span class="payment-status status-<?= strtolower($application['initial_payment_status']) ?>">
                                            <?= ucfirst($application['initial_payment_status']) ?>
                                        </span>
                                    </div>
                                </div>
                                <div class="proof-actions">
                                    <button class="btn btn-sm btn-primary" onclick="viewProof('../<?= htmlspecialchars($application['initial_payment_proof']) ?>', <?= $isImage ? 'true' : 'false' ?>)">
                                        <i class="fas fa-eye"></i> View
                                    </button>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <?php if ($application['final_payment_proof']): ?>
                            <div class="proof-item">
                                <div class="proof-icon">
                                    <?php 
                                    $fileExt = strtolower(pathinfo($application['final_payment_proof'], PATHINFO_EXTENSION));
                                    $isImage = in_array($fileExt, ['jpg', 'jpeg', 'png', 'gif', 'webp']);
                                    ?>
                                    <i class="fas fa-file-<?= $isImage ? 'image' : 'pdf' ?>"></i>
                                </div>
                                <div class="proof-info">
                                    <h5>Final Payment Proof</h5>
                                    <p class="proof-name"><?= htmlspecialchars($application['final_payment_proof']) ?></p>
                                    <div class="proof-meta">
                                        <?php if ($application['payment_method_name']): ?>
                                            <span class="payment-method"><?= htmlspecialchars($application['payment_method_name']) ?></span>
                                        <?php endif; ?>
                                        <?php if ($application['final_transaction_reference']): ?>
                                            <span class="transaction-ref">Ref: <?= htmlspecialchars($application['final_transaction_reference']) ?></span>
                                        <?php endif; ?>
                                        <span class="payment-status status-<?= strtolower($application['final_payment_status']) ?>">
                                            <?= ucfirst($application['final_payment_status']) ?>
                                        </span>
                                    </div>
                                </div>
                                <div class="proof-actions">
                                    <button class="btn btn-sm btn-primary" onclick="viewProof('../<?= htmlspecialchars($application['final_payment_proof']) ?>', <?= $isImage ? 'true' : 'false' ?>)">
                                        <i class="fas fa-eye"></i> View
                                    </button>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<style>
.main-content {
    margin-left: 260px;
    margin-top: 70px;
    padding: 2rem;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    min-height: calc(100vh - 70px);
}

.page-header {
    background: rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(10px);
    border-radius: 16px;
    padding: 2rem;
    margin-bottom: 2rem;
    color: white;
}

.header-content {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.header-info h1 {
    margin: 0 0 0.5rem 0;
    font-size: 1.75rem;
    font-weight: 600;
}

.application-number {
    font-size: 1.1rem;
    opacity: 0.9;
    margin: 0;
}

.header-actions {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.status-badge {
    padding: 0.5rem 1rem;
    border-radius: 20px;
    font-weight: 600;
    font-size: 0.875rem;
    text-transform: uppercase;
}

.status-draft { background: #f3f4f6; color: #374151; }
.status-submitted { background: #dbeafe; color: #1e40af; }
.status-under_review { background: #fef3c7; color: #92400e; }
.status-approved { background: #d1fae5; color: #065f46; }
.status-rejected { background: #fee2e2; color: #991b1b; }
.status-completed { background: #d1fae5; color: #065f46; }

.application-container {
    background: white;
    border-radius: 20px;
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
    overflow: hidden;
}

.tab-navigation {
    display: flex;
    background: #f8fafc;
    border-bottom: 1px solid #e5e7eb;
}

.tab-btn {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    padding: 1.5rem;
    border: none;
    background: transparent;
    color: #6b7280;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.3s ease;
    position: relative;
}

.tab-btn:hover {
    background: #f3f4f6;
    color: #374151;
}

.tab-btn.active {
    background: linear-gradient(135deg, #3b82f6, #1d4ed8);
    color: white;
}

.tab-btn .badge {
    background: rgba(255, 255, 255, 0.2);
    color: white;
    padding: 0.25rem 0.5rem;
    border-radius: 10px;
    font-size: 0.75rem;
    margin-left: 0.25rem;
}

.tab-content {
    padding: 2rem;
}

.tab-pane {
    display: none;
}

.tab-pane.active {
    display: block;
}

.overview-grid {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 2rem;
    margin-bottom: 2rem;
}

.info-card {
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
}

.card-header {
    background: linear-gradient(135deg, #f8fafc, #e2e8f0);
    padding: 1.5rem;
    border-bottom: 1px solid #e5e7eb;
}

.card-header h3 {
    margin: 0;
    color: #1e293b;
    font-size: 1.25rem;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.card-body {
    padding: 2rem;
}

.info-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1.5rem;
}

.info-item {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.info-item label {
    font-weight: 600;
    color: #6b7280;
    font-size: 0.875rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.info-item span {
    color: #1e293b;
    font-weight: 500;
}

.type-badge {
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    width: fit-content;
}

.type-manual { background: #fef3c7; color: #92400e; }
.type-online { background: #dbeafe; color: #1e40af; }

.payment-summary {
    text-align: center;
}

.amount-display {
    margin-bottom: 2rem;
}

.total-amount {
    font-size: 2.5rem;
    font-weight: 700;
    color: #1e293b;
    margin-bottom: 0.5rem;
}

.currency {
    font-size: 1.5rem;
    opacity: 0.7;
}

.amount-label {
    color: #6b7280;
    font-weight: 500;
}

.payment-details {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.payment-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.75rem 0;
    border-bottom: 1px solid #f3f4f6;
}

.plan-badge {
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
}

.plan-full { background: #d1fae5; color: #065f46; }
.plan-partial { background: #fef3c7; color: #92400e; }

.progress-container {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.progress-bar {
    width: 100px;
    height: 8px;
    background: #f3f4f6;
    border-radius: 4px;
    overflow: hidden;
}

.progress-fill {
    height: 100%;
    background: linear-gradient(135deg, #3b82f6, #1d4ed8);
    transition: width 0.3s ease;
}

.progress-text {
    font-size: 0.875rem;
    font-weight: 600;
    color: #3b82f6;
}

.notes-content {
    background: #f8fafc;
    padding: 1.5rem;
    border-radius: 12px;
    border-left: 4px solid #3b82f6;
    color: #374151;
    line-height: 1.6;
}

.applicant-profile {
    display: flex;
    align-items: center;
    gap: 1.5rem;
    margin-bottom: 2rem;
    padding-bottom: 2rem;
    border-bottom: 1px solid #e5e7eb;
}

.profile-avatar {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    background: linear-gradient(135deg, #3b82f6, #1d4ed8);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 2rem;
}

.profile-info h4 {
    margin: 0 0 0.5rem 0;
    color: #1e293b;
    font-size: 1.5rem;
}

.profile-email {
    color: #6b7280;
    margin: 0;
}

.empty-state {
    text-align: center;
    padding: 3rem 2rem;
    color: #6b7280;
}

.empty-state i {
    font-size: 3rem;
    margin-bottom: 1rem;
    color: #9ca3af;
}

.documents-grid {
    display: grid;
    gap: 1rem;
}

.document-item {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1.5rem;
    border: 2px solid #e5e7eb;
    border-radius: 12px;
    transition: all 0.3s ease;
}

.document-item:hover {
    border-color: #3b82f6;
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.1);
}

.document-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    background: #eff6ff;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #3b82f6;
    font-size: 1.5rem;
}

.document-info {
    flex: 1;
}

.document-info h5 {
    margin: 0 0 0.25rem 0;
    color: #1e293b;
    font-weight: 600;
}

.document-name {
    color: #6b7280;
    margin: 0 0 0.5rem 0;
    font-size: 0.875rem;
}

.document-meta {
    display: flex;
    gap: 1rem;
    font-size: 0.75rem;
    color: #9ca3af;
}

.payment-breakdown {
    display: flex;
    flex-direction: column;
    gap: 1rem;
    margin-bottom: 2rem;
}

.breakdown-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem 0;
    border-bottom: 1px solid #f3f4f6;
}

.breakdown-item.discount {
    color: #059669;
}

.breakdown-item.total {
    border-bottom: none;
    border-top: 2px solid #e5e7eb;
    font-weight: 600;
    font-size: 1.1rem;
    color: #1e293b;
}

.installment-info h4 {
    margin: 0 0 1rem 0;
    color: #1e293b;
}

.installment-grid {
    display: grid;
    gap: 1rem;
}

.installment-item {
    padding: 1.5rem;
    border: 2px solid #e5e7eb;
    border-radius: 12px;
    background: #f8fafc;
}

.installment-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 0.75rem;
}

.installment-amount {
    font-size: 1.25rem;
    font-weight: 600;
    color: #1e293b;
}

.installment-amount small {
    color: #6b7280;
    font-weight: 400;
}

.payment-actions {
    display: flex;
    gap: 0.5rem;
    margin-top: 1rem;
}

.payment-proofs {
    margin-top: 2rem;
    padding-top: 2rem;
    border-top: 1px solid #e5e7eb;
}

.payment-proofs h4 {
    margin: 0 0 1rem 0;
    color: #1e293b;
}

.proofs-grid {
    display: grid;
    gap: 1rem;
}

.proof-item {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1.5rem;
    border: 2px solid #e5e7eb;
    border-radius: 12px;
    transition: all 0.3s ease;
}

.proof-item:hover {
    border-color: #3b82f6;
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.1);
}

.proof-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    background: #eff6ff;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #3b82f6;
    font-size: 1.5rem;
}

.proof-info {
    flex: 1;
}

.proof-info h5 {
    margin: 0 0 0.25rem 0;
    color: #1e293b;
    font-weight: 600;
}

.proof-name {
    color: #6b7280;
    margin: 0 0 0.5rem 0;
    font-size: 0.875rem;
}

.proof-meta {
    display: flex;
    gap: 1rem;
    font-size: 0.75rem;
    color: #9ca3af;
}

.proof-amount {
    font-weight: 600;
    color: #059669;
}

.payment-method {
    background: #dbeafe;
    color: #1e40af;
    padding: 0.25rem 0.5rem;
    border-radius: 12px;
    font-size: 0.75rem;
    font-weight: 600;
}

.transaction-ref {
    background: #f3f4f6;
    color: #374151;
    padding: 0.25rem 0.5rem;
    border-radius: 12px;
    font-size: 0.75rem;
    font-weight: 500;
}

.payment-status {
    padding: 0.25rem 0.5rem;
    border-radius: 12px;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
}

.status-pending { background: #fef3c7; color: #92400e; }
.status-proof_submitted { background: #dbeafe; color: #1e40af; }
.status-under_review { background: #fef3c7; color: #92400e; }
.status-approved { background: #d1fae5; color: #065f46; }
.status-rejected { background: #fee2e2; color: #991b1b; }

.proof-modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: 10000;
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    visibility: hidden;
    transition: all 0.3s ease;
}

.proof-modal.show {
    opacity: 1;
    visibility: visible;
}

.proof-modal-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.85);
    backdrop-filter: blur(8px);
}

.proof-modal-content {
    position: relative;
    background: white;
    border-radius: 20px;
    max-width: 90vw;
    max-height: 90vh;
    display: flex;
    flex-direction: column;
    box-shadow: 0 25px 50px rgba(0, 0, 0, 0.5);
    transform: scale(0.9);
    transition: transform 0.3s ease;
}

.proof-modal.show .proof-modal-content {
    transform: scale(1);
}

.proof-modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1.5rem 2rem;
    background: linear-gradient(135deg, #3b82f6, #1d4ed8);
    border-radius: 20px 20px 0 0;
    color: white;
}

.proof-modal-header h3 {
    margin: 0;
    font-size: 1.25rem;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.proof-modal-close {
    background: rgba(255, 255, 255, 0.2);
    border: none;
    width: 36px;
    height: 36px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    cursor: pointer;
    transition: all 0.3s ease;
    font-size: 1.1rem;
}

.proof-modal-close:hover {
    background: rgba(255, 255, 255, 0.3);
    transform: rotate(90deg);
}

.proof-modal-body {
    padding: 2rem;
    flex: 1;
    overflow: auto;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #f8fafc;
}

.proof-image {
    max-width: 100%;
    max-height: 70vh;
    object-fit: contain;
    border-radius: 12px;
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
}

.proof-pdf {
    width: 80vw;
    height: 70vh;
    border: none;
    border-radius: 12px;
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
}

.proof-modal-footer {
    display: flex;
    justify-content: flex-end;
    gap: 1rem;
    padding: 1.5rem 2rem;
    background: white;
    border-radius: 0 0 20px 20px;
    border-top: 1px solid #e5e7eb;
}

.proof-viewer-modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: 1000;
    background: rgba(0, 0, 0, 0.9);
    align-items: center;
    justify-content: center;
}

.proof-viewer-modal.show {
    display: flex;
}

@media (max-width: 768px) {
    .proof-modal-content {
        max-width: 95vw;
        max-height: 95vh;
    }
    
    .proof-pdf {
        width: 85vw;
        height: 60vh;
    }
    
    .proof-modal-footer {
        flex-direction: column;
    }
    
    .proof-modal-footer .btn {
        width: 100%;
    }
}

.payment-upload {
    margin-top: 1rem;
    padding: 1rem;
    background: #f8fafc;
    border-radius: 8px;
    border: 2px dashed #d1d5db;
}

.upload-section {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.upload-section input[type="file"] {
    display: none;
}

.upload-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1rem;
    background: #3b82f6;
    color: white;
    border-radius: 8px;
    cursor: pointer;
    font-size: 0.875rem;
    font-weight: 500;
    transition: all 0.3s ease;
    width: fit-content;
}

.upload-btn:hover {
    background: #2563eb;
    transform: translateY(-1px);
}

.btn-success {
    background: linear-gradient(135deg, #10b981, #059669);
    color: white;
}

.btn-danger {
    background: linear-gradient(135deg, #ef4444, #dc2626);
    color: white;
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

.timeline {
    position: relative;
    padding-left: 2rem;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 0.75rem;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #e5e7eb;
}

.timeline-item {
    position: relative;
    margin-bottom: 2rem;
}

.timeline-marker {
    position: absolute;
    left: -2rem;
    top: 0.25rem;
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: #3b82f6;
    border: 3px solid white;
    box-shadow: 0 0 0 3px #e5e7eb;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 0.875rem;
}

.timeline-marker.created { background: #10b981; }
.timeline-marker.status { background: #f59e0b; }
.timeline-marker.payment { background: #3b82f6; }
.timeline-marker.document { background: #8b5cf6; }
.timeline-marker.proof { background: #06b6d4; }
.timeline-marker.note { background: #6b7280; }
.timeline-marker.approved { background: #10b981; }
.timeline-marker.rejected { background: #ef4444; }
.timeline-marker.completed { background: #10b981; }

.timeline-content h5 {
    margin: 0 0 0.5rem 0;
    color: #1e293b;
    font-weight: 600;
}

.timeline-content p {
    margin: 0 0 0.5rem 0;
    color: #6b7280;
}

.timeline-user {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin: 0.5rem 0;
    font-size: 0.875rem;
    color: #9ca3af;
    font-style: italic;
}

.timeline-date {
    font-size: 0.875rem;
    color: #9ca3af;
}

.btn-warning {
    background: linear-gradient(135deg, #f59e0b, #d97706);
    color: white;
}

.form-control {
    padding: 0.75rem;
    border: 2px solid #e5e7eb;
    border-radius: 8px;
    font-size: 0.9rem;
    width: 100%;
}

.form-control:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.btn {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1.5rem;
    border-radius: 12px;
    font-weight: 600;
    text-decoration: none;
    border: none;
    cursor: pointer;
    transition: all 0.3s ease;
}

.btn-primary {
    background: linear-gradient(135deg, #3b82f6, #1d4ed8);
    color: white;
}

.btn-secondary {
    background: #6b7280;
    color: white;
}

.btn-sm {
    padding: 0.5rem 1rem;
    font-size: 0.875rem;
}

@media (max-width: 768px) {
    .main-content {
        margin-left: 0;
        padding: 1rem;
    }
    
    .overview-grid {
        grid-template-columns: 1fr;
    }
    
    .info-grid {
        grid-template-columns: 1fr;
    }
    
    .tab-navigation {
        flex-wrap: wrap;
    }
    
    .tab-btn {
        flex: 1 1 50%;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const tabBtns = document.querySelectorAll('.tab-btn');
    const tabPanes = document.querySelectorAll('.tab-pane');
    
    tabBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const targetTab = this.dataset.tab;
            
            // Remove active class from all tabs and panes
            tabBtns.forEach(b => b.classList.remove('active'));
            tabPanes.forEach(p => p.classList.remove('active'));
            
            // Add active class to clicked tab and corresponding pane
            this.classList.add('active');
            document.getElementById(targetTab).classList.add('active');
        });
    });
    
    // Payment proof upload form
    const paymentProofForm = document.getElementById('paymentProofForm');
    if (paymentProofForm) {
        const fileInput = document.getElementById('paymentProofFile');
        const fileInfo = document.getElementById('uploadFileInfo');
        
        fileInput.addEventListener('change', function() {
            if (this.files.length > 0) {
                const file = this.files[0];
                fileInfo.innerHTML = `<i class="fas fa-file"></i> ${file.name} (${(file.size / 1024 / 1024).toFixed(2)} MB)`;
                fileInfo.style.display = 'block';
            } else {
                fileInfo.style.display = 'none';
            }
        });
        
        paymentProofForm.addEventListener('submit', function(e) {
            e.preventDefault();
            uploadPaymentProof(this);
        });
    }
    
    // Remaining payment proof upload form
    const remainingPaymentForm = document.getElementById('remainingPaymentForm');
    if (remainingPaymentForm) {
        const fileInput = document.getElementById('remainingPaymentFile');
        const fileInfo = document.getElementById('remainingFileInfo');
        
        fileInput.addEventListener('change', function() {
            if (this.files.length > 0) {
                const file = this.files[0];
                fileInfo.innerHTML = `<i class="fas fa-file"></i> ${file.name} (${(file.size / 1024 / 1024).toFixed(2)} MB)`;
                fileInfo.style.display = 'block';
            } else {
                fileInfo.style.display = 'none';
            }
        });
        
        remainingPaymentForm.addEventListener('submit', function(e) {
            e.preventDefault();
            uploadPaymentProof(this);
        });
    }
});

function uploadPaymentProof(form) {
    const formData = new FormData(form);
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Uploading...';
    submitBtn.disabled = true;
    
    fetch('ajax/upload_payment_proof.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showSuccessModal('Payment Proof Uploaded', 'Payment proof has been uploaded successfully.', function() {
                location.reload();
            });
        } else {
            showErrorModal('Upload Failed', data.message);
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        }
    })
    .catch(error => {
        showErrorModal('Network Error', 'A network error occurred. Please try again.');
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    });
}

function toggleEditMode() {
    document.querySelectorAll('.view-mode').forEach(el => el.style.display = 'none');
    document.querySelectorAll('.edit-mode').forEach(el => el.style.display = 'block');
    document.getElementById('editBtn').style.display = 'none';
    document.getElementById('saveBtn').style.display = 'inline-flex';
    document.getElementById('cancelBtn').style.display = 'inline-flex';
}

function cancelEdit() {
    document.querySelectorAll('.view-mode').forEach(el => el.style.display = 'block');
    document.querySelectorAll('.edit-mode').forEach(el => el.style.display = 'none');
    document.getElementById('editBtn').style.display = 'inline-flex';
    document.getElementById('saveBtn').style.display = 'none';
    document.getElementById('cancelBtn').style.display = 'none';
}

function saveChanges() {
    const formData = new FormData();
    formData.append('application_id', <?= $applicationId ?>);
    
    document.querySelectorAll('.edit-mode').forEach(el => {
        if (el.name) {
            formData.append(el.name, el.value);
        }
    });
    
    fetch('ajax/update_application.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showSuccessModal('Changes Saved', 'Application updated successfully.', () => {
                location.reload();
            });
        } else {
            showErrorModal('Error', data.message);
        }
    })
    .catch(error => {
        showErrorModal('Network Error', 'Failed to save changes.');
    });
}

function viewProof(filePath, isImage) {
    const modal = document.createElement('div');
    modal.className = 'proof-modal';
    modal.innerHTML = `
        <div class="proof-modal-overlay" onclick="closeProofModal()"></div>
        <div class="proof-modal-content">
            <div class="proof-modal-header">
                <h3><i class="fas fa-receipt"></i> Payment Proof</h3>
                <button class="proof-modal-close" onclick="closeProofModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="proof-modal-body">
            </div>
            <div class="proof-modal-footer">
                <a href="" class="btn btn-secondary" id="downloadProof">
                    <i class="fas fa-download"></i> Download
                </a>
                <button class="btn btn-primary" onclick="closeProofModal()">
                    <i class="fas fa-check"></i> Close
                </button>
            </div>
        </div>
    `;
    document.body.appendChild(modal);
    
    const body = modal.querySelector('.proof-modal-body');
    const downloadBtn = modal.querySelector('#downloadProof');
    downloadBtn.href = filePath;
    
    if (isImage) {
        const img = document.createElement('img');
        img.src = filePath;
        img.alt = 'Payment Proof';
        img.className = 'proof-image';
        body.appendChild(img);
    } else {
        const iframe = document.createElement('iframe');
        iframe.src = filePath;
        iframe.className = 'proof-pdf';
        body.appendChild(iframe);
    }
    
    setTimeout(() => modal.classList.add('show'), 10);
}

function closeProofModal() {
    const modal = document.querySelector('.proof-modal');
    if (modal) {
        modal.classList.remove('show');
        setTimeout(() => modal.remove(), 300);
    }
}

function updatePaymentStatus(applicationId, paymentType, status) {
    const action = status === 'completed' ? 'approve' : 'reject';
    const title = status === 'completed' ? 'Approve Payment' : 'Reject Payment';
    const message = `Are you sure you want to ${action} this payment?`;
    const icon = status === 'completed' ? 'fa-check-circle' : 'fa-times-circle';
    const iconColor = status === 'completed' ? '#10b981' : '#ef4444';
    const buttonText = status === 'completed' ? 'Approve' : 'Reject';
    const buttonClass = status === 'completed' ? 'btn-success' : 'btn-danger';
    
    showConfirmModal(title, message, icon, iconColor, buttonText, buttonClass, function() {
        fetch('ajax/update_payment_status.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `application_id=${applicationId}&payment_type=${paymentType}&status=${status}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showSuccessModal('Payment Updated', 'Payment status has been updated successfully.', function() {
                    location.reload();
                });
            } else {
                showErrorModal('Error', data.message);
            }
        })
        .catch(error => {
            showErrorModal('Network Error', 'A network error occurred. Please try again.');
        });
    });
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
}

function showSuccessModal(title, message, callback) {
    const modal = document.createElement('div');
    modal.className = 'modern-modal show';
    modal.innerHTML = `
        <div class="modern-modal-overlay"></div>
        <div class="modern-modal-content">
            <div class="modal-header">
                <div class="modal-icon" style="color: #10b981">
                    <i class="fas fa-check-circle"></i>
                </div>
                <h3>${title}</h3>
            </div>
            <div class="modal-body">
                <p>${message}</p>
            </div>
            <div class="modal-footer">
                <button class="btn btn-primary modal-btn" onclick="closeSuccessModal(this)">
                    Continue
                </button>
            </div>
        </div>
    `;
    
    document.body.appendChild(modal);
    
    window.closeSuccessModal = function(btn) {
        modal.remove();
        if (callback) callback();
    };
}

function showErrorModal(title, message) {
    const modal = document.createElement('div');
    modal.className = 'modern-modal show';
    modal.innerHTML = `
        <div class="modern-modal-overlay"></div>
        <div class="modern-modal-content">
            <div class="modal-header">
                <div class="modal-icon" style="color: #ef4444">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <h3>${title}</h3>
            </div>
            <div class="modal-body">
                <p>${message}</p>
            </div>
            <div class="modal-footer">
                <button class="btn btn-primary modal-btn" onclick="closeErrorModal(this)">
                    Try Again
                </button>
            </div>
        </div>
    `;
    
    document.body.appendChild(modal);
    
    window.closeErrorModal = function(btn) {
        modal.remove();
    };
}

function exportApplication() {
    showNotification('Generating application report...', 'success');
    
    const reportWindow = window.open('export-single-application.php?id=<?= $applicationId ?>', '_blank');
    
    if (reportWindow) {
        reportWindow.focus();
    } else {
        showNotification('Please allow popups to download the report', 'error');
    }
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
</script>

<?php include 'includes/footer.php'; ?>