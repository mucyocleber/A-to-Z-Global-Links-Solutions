<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit;
}

require_once '../config/database.php';

try {
    $pdo = getConnection();
    
    $applications = $pdo->query("
        SELECT a.*, 
               CONCAT(u.first_name, ' ', u.last_name) as full_name, 
               u.email, u.country, u.date_of_birth,
               s.name as service_name, s.currency,
               st.status_code as status,
               st.status_name,
               c.name as destination_country,
               a.payment_plan, a.initial_payment_status, a.final_payment_status, a.refund_status,
               (SELECT COUNT(*) FROM documents d WHERE d.application_id = a.id) as document_count,
               (SELECT COUNT(*) FROM documents d WHERE d.application_id = a.id AND d.is_verified = 1) as verified_documents,
               (SELECT COUNT(*) FROM payments p WHERE p.application_id = a.id) as payment_count,
               (SELECT SUM(p.amount) FROM payments p WHERE p.application_id = a.id AND p.payment_status = 'completed') as total_paid,
               (SELECT r.status FROM refund_requests r WHERE r.application_id = a.id ORDER BY r.requested_at DESC LIMIT 1) as refund_request_status
        FROM applications a 
        JOIN users u ON a.user_id = u.id 
        JOIN services s ON a.service_id = s.id 
        JOIN application_statuses st ON a.status_id = st.id
        LEFT JOIN countries c ON a.destination_country_id = c.id
        ORDER BY a.submitted_at DESC
    ")->fetchAll();
} catch (Exception $e) {
    $applications = [];
}

$pageTitle = 'Applications';
?>

<?php include 'includes/header.php'; ?>
<?php include 'includes/sidebar.php'; ?>

<main class="main-content">
    <div class="page-header">
        <h1>Applications Management</h1>
        <p>Manage all visa applications and payments</p>
    </div>
    
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon pending"><i class="fas fa-clock"></i></div>
            <div class="stat-info">
                <div class="stat-number"><?= count(array_filter($applications, function($a) { return in_array($a['status'], ['draft', 'submitted', 'under_review']); })) ?></div>
                <div class="stat-label">Pending</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon approved"><i class="fas fa-check-circle"></i></div>
            <div class="stat-info">
                <div class="stat-number"><?= count(array_filter($applications, function($a) { return ($a['initial_payment_status'] ?? 'pending') === 'completed'; })) ?></div>
                <div class="stat-label">Paid</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon documents"><i class="fas fa-file-alt"></i></div>
            <div class="stat-info">
                <div class="stat-number"><?= array_sum(array_column($applications, 'document_count')) ?></div>
                <div class="stat-label">Documents</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon revenue"><i class="fas fa-money-bill"></i></div>
            <div class="stat-info">
                <div class="stat-number"><?= number_format(array_sum(array_column($applications, 'total_amount'))) ?></div>
                <div class="stat-label">Revenue (<?= !empty($applications) ? $applications[0]['currency'] : 'USD' ?>)</div>
            </div>
        </div>
    </div>
        
    <div class="applications-grid">
        <?php if (empty($applications)): ?>
            <div class="empty-state">
                <i class="fas fa-file-alt"></i>
                <h3>No Applications Found</h3>
                <p>Applications will appear here when submitted</p>
            </div>
        <?php else: ?>
            <?php foreach ($applications as $app): ?>
                <div class="app-card">
                    <div class="card-header">
                        <div class="user-info">
                            <div class="avatar"><?= strtoupper(substr($app['full_name'], 0, 1)) ?></div>
                            <div>
                                <h3><?= htmlspecialchars($app['full_name']) ?></h3>
                                <p><?= htmlspecialchars($app['service_name']) ?></p>
                            </div>
                        </div>
                        <div class="status-badge status-<?= $app['status'] ?>">
                            <?= ucfirst(str_replace('_', ' ', $app['status'])) ?>
                        </div>
                    </div>
                    
                    <div class="card-body">
                        <div class="info-row">
                            <span>App ID:</span>
                            <span><?= htmlspecialchars($app['application_number']) ?></span>
                        </div>
                        <div class="info-row">
                            <span>Amount:</span>
                            <span class="amount"><?= number_format($app['total_amount']) ?> <?= htmlspecialchars($app['currency'] ?: 'USD') ?></span>
                        </div>
                        <div class="info-row">
                            <span>Payment Plan:</span>
                            <span><?= ucfirst($app['payment_plan'] ?? 'full') ?> Payment</span>
                        </div>
                        <div class="info-row">
                            <span>Payment Status:</span>
                            <span class="payment-status">
                                <?php
                                $isPartial = ($app['payment_plan'] ?? 'full') === 'partial';
                                $initialPaid = ($app['initial_payment_status'] ?? 'pending') === 'completed';
                                $finalPaid = ($app['final_payment_status'] ?? 'not_required') === 'completed';
                                
                                if ($isPartial) {
                                    if ($initialPaid && $finalPaid) {
                                        echo '<span style="color: #10b981;">✓ Fully Paid</span>';
                                    } elseif ($initialPaid) {
                                        echo '<span style="color: #f59e0b;">⚡ 50% Paid</span>';
                                    } else {
                                        echo '<span style="color: #ef4444;">⏳ Pending</span>';
                                    }
                                } else {
                                    echo $initialPaid ? '<span style="color: #10b981;">✓ Fully Paid</span>' : '<span style="color: #ef4444;">⏳ Pending</span>';
                                }
                                ?>
                            </span>
                        </div>
                        <div class="info-row">
                            <span>Documents:</span>
                            <span><?= $app['verified_documents'] ?>/<?= $app['document_count'] ?> 
                                <i class="fas fa-<?= $app['verified_documents'] == $app['document_count'] && $app['document_count'] > 0 ? 'check-circle' : 'clock' ?>" style="color: <?= $app['verified_documents'] == $app['document_count'] && $app['document_count'] > 0 ? '#10b981' : '#f59e0b' ?>;"></i>
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
                            <i class="fas fa-eye"></i> View Details
                        </a>
                        
                        <?php if (($app['refund_status'] ?? 'none') === 'requested'): ?>
                            <button class="btn-review" onclick="reviewRefund(<?= $app['id'] ?>)">
                                <i class="fas fa-undo"></i> Review Refund
                            </button>
                        <?php endif; ?>
                        
                        <?php 
                        $initialPaid = ($app['initial_payment_status'] ?? 'pending') === 'completed';
                        $finalPaid = ($app['final_payment_status'] ?? 'not_required') === 'completed';
                        $isPartial = ($app['payment_plan'] ?? 'full') === 'partial';
                        
                        if (!$initialPaid): ?>
                            <button class="btn-approve" onclick="approvePayment(<?= $app['id'] ?>, 'initial')">
                                <i class="fas fa-check"></i> Approve Payment
                            </button>
                        <?php elseif ($isPartial && !$finalPaid && ($app['final_payment_status'] ?? 'not_required') === 'pending'): ?>
                            <button class="btn-approve" onclick="approvePayment(<?= $app['id'] ?>, 'final')">
                                <i class="fas fa-check"></i> Approve Final
                            </button>
                        <?php endif; ?>
                        
                        <button class="btn-delete" onclick="deleteApplication(<?= $app['id'] ?>)">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</main>

<!-- Modals -->
<div id="viewModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Application Details</h3>
            <span class="close" onclick="closeModal('viewModal')">&times;</span>
        </div>
        <div class="modal-body" id="viewModalBody">
            <!-- Content loaded here -->
        </div>
    </div>
</div>

<div id="imageModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="imageModalTitle">Document</h3>
            <span class="close" onclick="closeModal('imageModal')">&times;</span>
        </div>
        <div class="modal-body">
            <img id="modalImage" src="" style="max-width: 100%; border-radius: 8px;" alt="Document">
        </div>
    </div>
</div>

<div id="confirmModal" class="modal">
    <div class="modal-content confirm-modal">
        <div class="modal-header">
            <h3 id="confirmTitle">Confirm Action</h3>
        </div>
        <div class="modal-body">
            <p id="confirmMessage">Are you sure?</p>
        </div>
        <div class="modal-footer">
            <button class="btn-cancel" onclick="closeModal('confirmModal')">Cancel</button>
            <button class="btn-confirm" id="confirmBtn" onclick="confirmAction()">Confirm</button>
        </div>
    </div>
</div>

<div id="toast" class="toast">
    <div class="toast-content">
        <i class="toast-icon fas fa-check-circle"></i>
        <span class="toast-message">Success message</span>
    </div>
</div>

<style>
.main-content {
    margin-left: 260px;
    margin-top: 70px;
    padding: 2rem;
    background: #f8fafc;
    min-height: calc(100vh - 70px);
}

.page-header {
    margin-bottom: 2rem;
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

.stats-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 1rem;
    margin-bottom: 2rem;
}

.stat-card {
    background: white;
    border-radius: 12px;
    padding: 1.5rem;
    display: flex;
    align-items: center;
    gap: 1rem;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    transition: transform 0.2s;
}

.stat-card:hover {
    transform: translateY(-2px);
}

.stat-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.2rem;
}

.stat-icon.pending { background: #f59e0b; }
.stat-icon.approved { background: #10b981; }
.stat-icon.documents { background: #3b82f6; }
.stat-icon.revenue { background: #8b5cf6; }

.stat-number {
    font-size: 1.8rem;
    font-weight: 700;
    color: #1e293b;
}

.stat-label {
    color: #64748b;
    font-size: 0.9rem;
    font-weight: 500;
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

.status-submitted { background: #dbeafe; color: #1e40af; }
.status-under_review { background: #fef3c7; color: #92400e; }
.status-approved { background: #d1fae5; color: #065f46; }
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

.payment-status.approved { color: #10b981; }
.payment-status.proof_submitted { color: #f59e0b; }
.payment-status.under_review { color: #3b82f6; }
.payment-status.rejected { color: #ef4444; }

.proof-icon {
    cursor: pointer;
    opacity: 0.7;
    transition: opacity 0.2s;
}

.proof-icon:hover {
    opacity: 1;
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
    padding: 1.5rem;
    background: #f9fafb;
    border-top: 1px solid #e2e8f0;
    display: flex;
    gap: 0.75rem;
    flex-wrap: wrap;
}

.btn-view {
    flex: 1;
    background: #3b82f6;
    color: white;
    border: none;
    padding: 0.75rem 1rem;
    border-radius: 8px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    font-size: 0.9rem;
}

.btn-view:hover {
    background: #2563eb;
}

.btn-review, .btn-approve, .btn-reject, .btn-delete {
    padding: 0.75rem;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.9rem;
    min-width: 40px;
}

.btn-review { background: #3b82f6; color: white; }
.btn-approve { background: #10b981; color: white; }
.btn-reject { background: #ef4444; color: white; }
.btn-delete { background: #ef4444; color: white; }

.btn-review:hover { background: #2563eb; }
.btn-approve:hover { background: #059669; }
.btn-reject:hover { background: #dc2626; }
.btn-delete:hover { background: #dc2626; }

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

/* Modal Styles */
.modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.5);
    backdrop-filter: blur(4px);
}

.modal.show {
    display: flex;
    align-items: center;
    justify-content: center;
}

.modal-content {
    background: white;
    border-radius: 12px;
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
    max-width: 600px;
    width: 90%;
    max-height: 90vh;
    overflow: hidden;
}

.confirm-modal {
    max-width: 400px;
}

.modal-header {
    padding: 1.5rem;
    border-bottom: 1px solid #e2e8f0;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.modal-header h3 {
    margin: 0;
    color: #1f2937;
    font-size: 1.25rem;
    font-weight: 600;
}

.close {
    color: #6b7280;
    font-size: 1.5rem;
    font-weight: bold;
    cursor: pointer;
    transition: color 0.2s;
}

.close:hover {
    color: #374151;
}

.modal-body {
    padding: 1.5rem;
    max-height: 60vh;
    overflow-y: auto;
}

.modal-footer {
    padding: 1.5rem;
    border-top: 1px solid #e2e8f0;
    display: flex;
    gap: 1rem;
    justify-content: flex-end;
}

.btn-cancel, .btn-confirm {
    padding: 0.75rem 1.5rem;
    border: none;
    border-radius: 8px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-cancel {
    background: #f3f4f6;
    color: #374151;
}

.btn-cancel:hover {
    background: #e5e7eb;
}

.btn-confirm {
    background: #ef4444;
    color: white;
}

.btn-confirm:hover {
    background: #dc2626;
}

.btn-confirm.approve {
    background: #10b981;
}

.btn-confirm.approve:hover {
    background: #059669;
}

/* Toast */
.toast {
    position: fixed;
    top: 20px;
    right: 20px;
    background: white;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    padding: 1rem 1.5rem;
    z-index: 2000;
    transform: translateX(400px);
    opacity: 0;
    transition: all 0.3s ease;
}

.toast.show {
    transform: translateX(0);
    opacity: 1;
}

.toast-content {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.toast-icon {
    font-size: 1.2rem;
    color: #10b981;
}

.toast-icon.error {
    color: #ef4444;
}

.toast-message {
    font-weight: 500;
    color: #1f2937;
}

@media (max-width: 768px) {
    .main-content {
        margin-left: 0;
        padding: 1rem;
    }
    
    .stats-grid {
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
    }
    
    .applications-grid {
        grid-template-columns: 1fr;
        gap: 1rem;
    }
    
    .card-actions {
        flex-direction: column;
    }
    
    .btn-view {
        width: 100%;
    }
}
</style>

<script>
let confirmCallback = null;

function viewApplication(id) {
    document.getElementById('viewModalBody').innerHTML = '<div style="text-align: center; padding: 2rem;"><i class="fas fa-spinner fa-spin"></i> Loading...</div>';
    document.getElementById('viewModal').classList.add('show');
    
    fetch('ajax/get_application.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'application_id=' + id
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('viewModalBody').innerHTML = data.html;
        } else {
            document.getElementById('viewModalBody').innerHTML = '<div style="color: #ef4444;">Error: ' + data.error + '</div>';
        }
    })
    .catch(error => {
        document.getElementById('viewModalBody').innerHTML = '<div style="color: #ef4444;">Network error</div>';
    });
}

function deleteApplication(id) {
    showConfirm('Delete Application', 'Are you sure you want to delete this application?', () => {
        fetch('ajax/delete_application.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({application_id: id})
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast('Application deleted successfully');
                setTimeout(() => location.reload(), 1000);
            } else {
                showToast('Error: ' + data.message, 'error');
            }
        })
        .catch(error => {
            showToast('Error deleting application', 'error');
        });
    });
}

function reviewPayment(id, status) {
    const action = status.replace('_', ' ');
    showConfirm(`${action.charAt(0).toUpperCase() + action.slice(1)} Payment`, `Are you sure you want to ${action} this payment?`, () => {
        fetch('ajax/review_payment.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({application_id: id, status: status})
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast(`Payment ${action}d successfully`);
                setTimeout(() => location.reload(), 1000);
            } else {
                showToast('Error: ' + data.message, 'error');
            }
        })
        .catch(error => {
            showToast('Error processing payment', 'error');
        });
    });
}

function approvePayment(id, type) {
    const paymentType = type === 'initial' ? 'initial payment' : 'final payment';
    showConfirm(`Approve ${paymentType.charAt(0).toUpperCase() + paymentType.slice(1)}`, `Are you sure you want to approve this ${paymentType}?`, () => {
        fetch('ajax/approve_payment.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({application_id: id, payment_type: type})
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast(`${paymentType.charAt(0).toUpperCase() + paymentType.slice(1)} approved successfully`);
                setTimeout(() => location.reload(), 1000);
            } else {
                showToast('Error: ' + data.message, 'error');
            }
        })
        .catch(error => {
            showToast('Error approving payment', 'error');
        });
    });
}

function reviewRefund(id) {
    window.location.href = `view-application.php?id=${id}#refund`;
}

function viewProof(filename) {
    document.getElementById('imageModalTitle').textContent = 'Payment Proof';
    document.getElementById('modalImage').src = '../uploads/' + filename;
    document.getElementById('imageModal').classList.add('show');
}

function showConfirm(title, message, callback) {
    document.getElementById('confirmTitle').textContent = title;
    document.getElementById('confirmMessage').textContent = message;
    confirmCallback = callback;
    document.getElementById('confirmModal').classList.add('show');
}

function confirmAction() {
    if (confirmCallback) {
        confirmCallback();
        closeModal('confirmModal');
    }
}

function closeModal(modalId) {
    document.getElementById(modalId).classList.remove('show');
    if (modalId === 'confirmModal') {
        confirmCallback = null;
    }
}

function showToast(message, type = 'success') {
    const toast = document.getElementById('toast');
    const icon = toast.querySelector('.toast-icon');
    const messageEl = toast.querySelector('.toast-message');
    
    messageEl.textContent = message;
    
    if (type === 'error') {
        icon.className = 'toast-icon fas fa-exclamation-circle error';
    } else {
        icon.className = 'toast-icon fas fa-check-circle';
    }
    
    toast.classList.add('show');
    
    setTimeout(() => {
        toast.classList.remove('show');
    }, 3000);
}

// Close modals when clicking outside
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('modal')) {
        e.target.classList.remove('show');
    }
});
</script>

<?php include 'includes/footer.php'; ?>