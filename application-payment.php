<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['user_id']) || !isset($_GET['app'])) {
    header('Location: application.php');
    exit;
}

$applicationId = $_GET['app'];

// Handle payment submission
if ($_POST) {
    try {
        $pdo = getConnection();
        
        // Verify application belongs to user
        $stmt = $pdo->prepare("SELECT * FROM applications WHERE id = ? AND user_id = ?");
        $stmt->execute([$applicationId, $_SESSION['user_id']]);
        $application = $stmt->fetch();
        
        if (!$application) {
            header('Location: application.php');
            exit;
        }
        
        // Determine payment amount and type
        $paymentAmount = $application['total_amount'];
        $paymentType = 'full';
        
        if ($application['payment_plan'] === 'partial') {
            if ($application['initial_payment_status'] === 'pending' && !$application['initial_payment_proof']) {
                $paymentAmount = $application['initial_payment_amount'];
                $paymentType = 'partial_initial';
            } elseif ($application['initial_payment_status'] === 'completed' && $application['final_payment_status'] !== 'completed') {
                $paymentAmount = $application['remaining_payment_amount'];
                $paymentType = 'partial_final';
            }
        }
        
        // Handle file upload
        $proofPath = null;
        if (isset($_FILES['proof_of_payment']) && $_FILES['proof_of_payment']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = 'uploads/payments/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            $extension = pathinfo($_FILES['proof_of_payment']['name'], PATHINFO_EXTENSION);
            $filename = uniqid() . '.' . $extension;
            $proofPath = $uploadDir . $filename;
            
            move_uploaded_file($_FILES['proof_of_payment']['tmp_name'], $proofPath);
        }
        
        // Create payment record
        $stmt = $pdo->prepare("
            INSERT INTO payments (payment_uuid, application_id, user_id, amount, currency, payment_method_id, payment_status, payment_type, is_partial_payment, remaining_amount, proof_of_payment, proof_type, transaction_reference, submitted_at)
            VALUES (UUID(), ?, ?, ?, ?, ?, 'proof_submitted', ?, ?, ?, ?, ?, ?, NOW())
        ");
        
        $isPartialPayment = $application['payment_plan'] === 'partial' ? 1 : 0;
        $remainingAmount = $paymentType === 'partial_initial' ? $application['remaining_payment_amount'] : 0;
        
        $stmt->execute([
            $applicationId,
            $_SESSION['user_id'],
            $paymentAmount,
            $application['currency'] ?? 'USD',
            $_POST['payment_method_id'],
            $paymentType,
            $isPartialPayment,
            $remainingAmount,
            $proofPath,
            $_POST['proof_type'],
            $_POST['transaction_reference']
        ]);
        
        // Update application status
        if ($paymentType === 'partial_initial') {
            $stmt = $pdo->prepare("UPDATE applications SET initial_payment_status = 'pending', payment_method_id = ?, transaction_reference = ?, initial_payment_proof = ?, status_id = 3 WHERE id = ?");
            $stmt->execute([$_POST['payment_method_id'], $_POST['transaction_reference'], $proofPath, $applicationId]);
        } elseif ($paymentType === 'partial_final') {
            $stmt = $pdo->prepare("UPDATE applications SET final_payment_status = 'pending', payment_method_id = ?, final_transaction_reference = ?, final_payment_proof = ? WHERE id = ?");
            $stmt->execute([$_POST['payment_method_id'], $_POST['transaction_reference'], $proofPath, $applicationId]);
        } else {
            $stmt = $pdo->prepare("UPDATE applications SET initial_payment_status = 'pending', final_payment_status = 'not_required', payment_method_id = ?, transaction_reference = ?, payment_proof = ?, status_id = 3 WHERE id = ?");
            $stmt->execute([$_POST['payment_method_id'], $_POST['transaction_reference'], $proofPath, $applicationId]);
        }
        
        $success = true;
        
    } catch (Exception $e) {
        $error = "Error submitting payment: " . $e->getMessage();
    }
}

try {
    $pdo = getConnection();
    
    // Get application details
    $stmt = $pdo->prepare("
        SELECT a.*, s.name as service_name, s.currency as service_currency
        FROM applications a 
        LEFT JOIN services s ON a.service_id = s.id 
        WHERE a.id = ? AND a.user_id = ?
    ");
    $stmt->execute([$applicationId, $_SESSION['user_id']]);
    $application = $stmt->fetch();
    
    if (!$application) {
        header('Location: application.php');
        exit;
    }
    
    // Determine payment details
    $paymentAmount = $application['total_amount'];
    $paymentType = 'full';
    $paymentDescription = 'Full Payment';
    
    if ($application['payment_plan'] === 'partial') {
        if ($application['initial_payment_status'] === 'pending' && !$application['initial_payment_proof']) {
            $paymentAmount = $application['initial_payment_amount'];
            $paymentType = 'partial_initial';
            // Use stored percentage or calculate if null/zero
            $percentage = $application['initial_payment_percentage'];
            if (!$percentage || $percentage == 0) {
                $percentage = $application['total_amount'] > 0 ? ($application['initial_payment_amount'] / $application['total_amount']) * 100 : 0;
            }
            $paymentDescription = "Initial Payment (" . round($percentage) . "%)";
        } elseif ($application['initial_payment_status'] === 'completed' && $application['final_payment_status'] !== 'completed') {
            $paymentAmount = $application['remaining_payment_amount'];
            $paymentType = 'partial_final';
            // Use stored percentage or calculate if null/zero
            $percentage = $application['remaining_payment_percentage'];
            if (!$percentage || $percentage == 0) {
                $percentage = $application['total_amount'] > 0 ? ($application['remaining_payment_amount'] / $application['total_amount']) * 100 : 0;
            }
            $paymentDescription = "Final Payment (" . round($percentage) . "%)";
        }
    }
    
    // Get payment history for partial payments
    $paymentHistory = null;
    if ($application['payment_plan'] === 'partial' && $paymentType === 'partial_final') {
        $stmt = $pdo->prepare("
            SELECT amount, submitted_at 
            FROM payments 
            WHERE application_id = ? AND payment_type = 'partial_initial'
            ORDER BY submitted_at DESC LIMIT 1
        ");
        $stmt->execute([$applicationId]);
        $paymentHistory = $stmt->fetch();
    }
    
} catch (Exception $e) {
    $application = null;
    $paymentAmount = 0;
    $paymentType = 'full';
    $paymentDescription = 'Full Payment';
    $paymentHistory = null;
}

$pageTitle = 'Payment - A to Z Global Link Solutions';
include 'includes/header.php';
?>

<style>
.main-content {
    margin-top: 70px;
    padding: 2rem;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    min-height: calc(100vh - 70px);
}

.payment-container {
    max-width: 1000px;
    margin: 0 auto;
}

.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
    background: rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(10px);
    padding: 1.5rem;
    border-radius: 16px;
    color: white;
}

.page-header h1 {
    font-size: 1.8rem;
    font-weight: 700;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.btn {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.875rem 1.75rem;
    border-radius: 12px;
    font-weight: 600;
    text-decoration: none;
    border: none;
    cursor: pointer;
    transition: all 0.3s ease;
    font-size: 1rem;
}

.btn-secondary {
    background: rgba(255, 255, 255, 0.2);
    color: white;
    border: 2px solid rgba(255, 255, 255, 0.3);
}

.btn-secondary:hover {
    background: rgba(255, 255, 255, 0.3);
    transform: translateY(-2px);
    color: white;
    text-decoration: none;
}

.form-wizard {
    background: white;
    border-radius: 20px;
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
    overflow: hidden;
}

.wizard-steps {
    display: flex;
    background: #f8fafc;
}

.step {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 1.5rem;
    gap: 0.75rem;
    transition: all 0.3s ease;
}

.step.completed {
    background: linear-gradient(135deg, #10b981, #059669);
    color: white;
}

.step.active {
    background: linear-gradient(135deg, #3b82f6, #1d4ed8);
    color: white;
}

.step-number {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.2);
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
}

.step-title {
    font-weight: 600;
    font-size: 0.95rem;
}

.card {
    border-radius: 16px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    overflow: hidden;
    margin-bottom: 2rem;
}

.card-header {
    background: linear-gradient(135deg, #f8fafc, #e2e8f0);
    padding: 1.5rem;
    border-bottom: 1px solid #e2e8f0;
}

.card-header h3 {
    color: #1e293b;
    font-size: 1.25rem;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 0.75rem;
    margin: 0;
}

.card-body {
    padding: 2rem;
}

.price-display {
    background: linear-gradient(135deg, #3b82f6, #1d4ed8);
    color: white;
    padding: 2rem;
    border-radius: 16px;
    margin-bottom: 2rem;
    text-align: center;
}

.service-price {
    font-size: 3rem;
    font-weight: 700;
    margin-bottom: 0.5rem;
}

.currency {
    font-size: 1.5rem;
    opacity: 0.8;
}

.price-label {
    font-size: 1rem;
    opacity: 0.9;
    margin: 0;
}

.summary-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1.5rem;
    margin-top: 2rem;
}

.summary-item {
    background: rgba(255, 255, 255, 0.1);
    padding: 1.25rem;
    border-radius: 12px;
    text-align: center;
    backdrop-filter: blur(10px);
}

.summary-label {
    font-size: 0.75rem;
    opacity: 0.8;
    font-weight: 600;
    margin-bottom: 0.5rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.summary-value {
    font-size: 1rem;
    font-weight: 700;
}

.payment-methods {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.payment-method {
    background: white;
    border: 2px solid #e5e7eb;
    border-radius: 12px;
    padding: 1.5rem;
    cursor: pointer;
    transition: all 0.3s ease;
    position: relative;
}

.payment-method:hover {
    border-color: #3b82f6;
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(59, 130, 246, 0.15);
}

.payment-method.selected {
    border-color: #3b82f6;
    background: #eff6ff;
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(59, 130, 246, 0.15);
}

.method-header {
    display: flex;
    align-items: center;
    gap: 1.25rem;
    margin-bottom: 1.5rem;
}

.method-icon {
    width: 60px;
    height: 60px;
    background: linear-gradient(135deg, #3b82f6, #1d4ed8);
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.5rem;
    box-shadow: 0 8px 25px rgba(59, 130, 246, 0.3);
    transition: all 0.3s ease;
}

.payment-method:hover .method-icon {
    transform: scale(1.1) rotate(5deg);
}

.method-info h4 {
    color: #1e40af;
    font-size: 1.2rem;
    font-weight: 800;
    margin: 0 0 0.5rem 0;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.method-info p {
    color: #64748b;
    font-size: 0.95rem;
    margin: 0;
    font-weight: 500;
}

.method-checkbox {
    width: 28px;
    height: 28px;
    border: 2px solid #d1d5db;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
    background: white;
    margin-left: auto;
}

.payment-method.selected .method-checkbox {
    background: linear-gradient(135deg, #10b981, #059669);
    border-color: #10b981;
    color: white;
    box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3);
}

.method-checkbox i {
    font-size: 0.8rem;
    opacity: 0;
    transition: all 0.3s ease;
}

.payment-method.selected .method-checkbox i {
    opacity: 1;
    transform: scale(1.2);
}

.recommended-badge {
    background: linear-gradient(135deg, #10b981, #059669);
    color: white;
    font-size: 0.7rem;
    padding: 0.3rem 0.6rem;
    border-radius: 12px;
    font-weight: 600;
    box-shadow: 0 2px 8px rgba(16, 185, 129, 0.3);
}

.method-details {
    background: rgba(59, 130, 246, 0.05);
    border-radius: 12px;
    padding: 1.25rem;
    font-family: 'Courier New', monospace;
    font-size: 0.9rem;
    border: 1px solid rgba(59, 130, 246, 0.1);
}

.detail-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 0.75rem;
}

.detail-row:last-child {
    margin-bottom: 0;
}

.detail-label {
    color: #64748b;
    font-weight: 600;
}

.detail-value {
    color: #1e40af;
    font-weight: 700;
}

.form-group {
    display: flex;
    flex-direction: column;
    margin-bottom: 1.5rem;
}

.form-group label {
    font-weight: 600;
    color: #374151;
    margin-bottom: 0.5rem;
    font-size: 0.9rem;
}

.form-control {
    padding: 0.875rem;
    border: 2px solid #e5e7eb;
    border-radius: 12px;
    font-size: 1rem;
    transition: all 0.3s ease;
    background: white;
    font-weight: 500;
}

.form-control:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
    transform: translateY(-1px);
}

.file-upload {
    position: relative;
}

.file-upload input[type="file"] {
    position: absolute;
    opacity: 0;
    width: 100%;
    height: 100%;
    cursor: pointer;
}

.file-upload-label {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.75rem;
    padding: 2rem;
    border: 2px dashed rgba(59, 130, 246, 0.3);
    border-radius: 16px;
    background: rgba(59, 130, 246, 0.02);
    color: #3b82f6;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    min-height: 120px;
}

.file-upload-label:hover {
    border-color: #3b82f6;
    background: rgba(59, 130, 246, 0.05);
    transform: translateY(-2px);
}

.file-upload.has-file .file-upload-label {
    border-color: #10b981;
    background: rgba(16, 185, 129, 0.05);
    color: #059669;
}

.btn-submit {
    background: linear-gradient(135deg, #10b981, #059669);
    color: white;
    padding: 1.25rem 3rem;
    border: none;
    border-radius: 50px;
    font-weight: 700;
    font-size: 1.1rem;
    cursor: pointer;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 0.75rem;
    box-shadow: 0 12px 32px rgba(16, 185, 129, 0.3);
    position: relative;
    overflow: hidden;
}

.btn-submit:hover {
    background: linear-gradient(135deg, #059669, #047857);
    transform: translateY(-3px) scale(1.02);
    box-shadow: 0 16px 40px rgba(16, 185, 129, 0.4);
}

.btn-submit:disabled {
    background: #9ca3af;
    cursor: not-allowed;
    transform: none;
    box-shadow: none;
}

.alert {
    padding: 1.5rem 2rem;
    border-radius: 16px;
    margin-bottom: 2rem;
    display: flex;
    align-items: center;
    gap: 1rem;
    font-weight: 600;
    border: 2px solid;
}

.alert-success {
    background: linear-gradient(135deg, rgba(16, 185, 129, 0.1), rgba(5, 150, 105, 0.15));
    color: #065f46;
    border-color: rgba(16, 185, 129, 0.3);
}

.alert-error {
    background: linear-gradient(135deg, rgba(239, 68, 68, 0.1), rgba(220, 38, 38, 0.15));
    color: #991b1b;
    border-color: rgba(239, 68, 68, 0.3);
}

.alert i {
    font-size: 1.2rem;
}

.success-modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.6);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 10000;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.success-modal-overlay.show {
    opacity: 1;
}

.success-modal {
    background: white;
    padding: 3rem 2.5rem;
    border-radius: 24px;
    text-align: center;
    max-width: 400px;
    margin: 1rem;
    box-shadow: 0 25px 50px rgba(0, 0, 0, 0.3);
    transform: translateY(30px);
    transition: transform 0.4s ease;
}

.success-modal-overlay.show .success-modal {
    transform: translateY(0);
}

.success-icon {
    width: 80px;
    height: 80px;
    background: linear-gradient(135deg, #10b981, #059669);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1.5rem;
    color: white;
    font-size: 2.5rem;
    animation: bounceIn 0.6s ease 0.2s both;
}

.success-modal h3 {
    color: #1e40af;
    margin-bottom: 1rem;
    font-size: 1.5rem;
    font-weight: 700;
}

.success-modal p {
    color: #64748b;
    margin-bottom: 2rem;
    line-height: 1.6;
}

.success-btn {
    background: linear-gradient(135deg, #1e40af, #3b82f6);
    color: white;
    padding: 1rem 2rem;
    border: none;
    border-radius: 16px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin: 0 auto;
    font-size: 1rem;
    text-decoration: none;
}

.success-btn:hover {
    background: linear-gradient(135deg, #1d4ed8, #2563eb);
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(30, 64, 175, 0.3);
    color: white;
    text-decoration: none;
}

@keyframes bounceIn {
    0% { transform: scale(0.3); opacity: 0; }
    50% { transform: scale(1.1); }
    70% { transform: scale(0.9); }
    100% { transform: scale(1); opacity: 1; }
}

@media (max-width: 768px) {
    .main-content {
        margin-top: 60px;
        padding: 1rem;
        padding-bottom: 100px;
    }
    
    .page-header {
        flex-direction: column;
        gap: 1rem;
        text-align: center;
        padding: 1.5rem;
    }
    
    .page-header h1 {
        font-size: 1.5rem;
    }
    
    .wizard-steps {
        flex-direction: column;
        gap: 0;
    }
    
    .step {
        padding: 1rem;
        border-bottom: 1px solid #e5e7eb;
    }
    
    .step:last-child {
        border-bottom: none;
    }
    
    .card-body {
        padding: 1.5rem;
    }
    
    .price-display {
        padding: 1.5rem;
    }
    
    .service-price {
        font-size: 2rem;
        flex-direction: column;
        gap: 0.25rem;
    }
    
    .summary-grid {
        grid-template-columns: 1fr;
        gap: 1rem;
        margin-top: 1.5rem;
    }
    
    .payment-methods {
        grid-template-columns: 1fr;
        gap: 1rem;
    }
    
    .method-header {
        flex-direction: column;
        text-align: center;
        gap: 1rem;
    }
    
    .method-checkbox {
        margin: 0 auto;
    }
    
    .form-group {
        margin-bottom: 1rem;
    }
    
    .btn-submit {
        width: 100%;
        padding: 1rem 2rem;
        font-size: 1rem;
    }
}

@media (max-width: 480px) {
    .main-content {
        padding: 0.75rem;
    }
    
    .page-header {
        padding: 1rem;
        border-radius: 12px;
    }
    
    .page-header h1 {
        font-size: 1.3rem;
    }
    
    .card-body {
        padding: 1rem;
    }
    
    .price-display {
        padding: 1rem;
    }
    
    .service-price {
        font-size: 1.8rem;
    }
    
    .payment-method {
        padding: 1rem;
    }
    
    .method-icon {
        width: 50px;
        height: 50px;
        font-size: 1.3rem;
    }
    
    .method-info h4 {
        font-size: 1rem;
    }
    
    .method-details {
        padding: 1rem;
        font-size: 0.85rem;
    }
    
    .file-upload-label {
        padding: 1.5rem;
        min-height: 100px;
        font-size: 0.9rem;
    }
}
</style>

<main class="main-content">
    <div class="payment-container">
        <div class="page-header">
            <h1><i class="fas fa-credit-card"></i> Complete Payment</h1>
            <a href="application.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back
            </a>
        </div>

        <div class="form-wizard">
            <div class="wizard-steps">
                <div class="step completed">
                    <span class="step-number"><i class="fas fa-check"></i></span>
                    <span class="step-title">Application</span>
                </div>
                <div class="step completed">
                    <span class="step-number"><i class="fas fa-check"></i></span>
                    <span class="step-title">Documents</span>
                </div>
                <div class="step active">
                    <span class="step-number">3</span>
                    <span class="step-title">Payment</span>
                </div>
            </div>

            <?php if (isset($success)): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <div><strong>Success:</strong> Payment proof submitted successfully! Your application is now under review.</div>
                </div>
                <script>
                document.addEventListener('DOMContentLoaded', function() {
                    showSuccessModal();
                });
                
                function showSuccessModal() {
                    const modal = document.createElement('div');
                    modal.className = 'success-modal-overlay';
                    modal.innerHTML = `
                        <div class="success-modal">
                            <div class="success-icon">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <h3>Payment Submitted Successfully!</h3>
                            <p>Your payment proof has been submitted and your application is now under review.</p>
                            <a href="profile.php" class="success-btn">
                                <i class="fas fa-user"></i> Go to Profile
                            </a>
                        </div>
                    `;
                    document.body.appendChild(modal);
                    setTimeout(() => modal.classList.add('show'), 100);
                }
                </script>
            <?php elseif (isset($error)): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-triangle"></i>
                    <div><strong>Error:</strong> <?= $error ?></div>
                </div>
            <?php endif; ?>

            <?php if ($application && !isset($success)): ?>
                <div class="card">
                    <div class="card-header">
                        <h3><i class="fas fa-file-invoice"></i> Payment Summary</h3>
                    </div>
                    <div class="card-body">
                        <div class="price-display">
                            <?php if ($paymentType === 'partial_final' && $paymentHistory): ?>
                                <div class="service-price">
                                    <span class="currency"><?= htmlspecialchars($application['currency'] ?? 'USD') ?></span>
                                    <?= number_format($paymentAmount) ?>
                                </div>
                                <div class="price-label">Final Payment Amount</div>
                                <div class="summary-grid">
                                    <div class="summary-item">
                                        <div class="summary-label">Application Number</div>
                                        <div class="summary-value"><?= htmlspecialchars($application['application_number']) ?></div>
                                    </div>
                                    <div class="summary-item">
                                        <div class="summary-label">Service</div>
                                        <div class="summary-value"><?= htmlspecialchars($application['service_name']) ?></div>
                                    </div>
                                    <div class="summary-item">
                                        <div class="summary-label">First Payment Made</div>
                                        <div class="summary-value"><?= number_format($paymentHistory['amount']) ?> <?= htmlspecialchars($application['currency'] ?? 'USD') ?></div>
                                    </div>
                                    <div class="summary-item">
                                        <div class="summary-label">Payment Date</div>
                                        <div class="summary-value"><?= date('M d, Y', strtotime($paymentHistory['submitted_at'])) ?></div>
                                    </div>
                                </div>
                            <?php else: ?>
                                <div class="service-price">
                                    <span class="currency"><?= htmlspecialchars($application['currency'] ?? 'USD') ?></span>
                                    <?= number_format($paymentAmount) ?>
                                </div>
                                <div class="price-label"><?= $paymentDescription ?></div>
                                <div class="summary-grid">
                                    <div class="summary-item">
                                        <div class="summary-label">Application Number</div>
                                        <div class="summary-value"><?= htmlspecialchars($application['application_number']) ?></div>
                                    </div>
                                    <div class="summary-item">
                                        <div class="summary-label">Service</div>
                                        <div class="summary-value"><?= htmlspecialchars($application['service_name']) ?></div>
                                    </div>
                                    <div class="summary-item">
                                        <div class="summary-label">Payment Type</div>
                                        <div class="summary-value"><?= $paymentDescription ?></div>
                                    </div>
                                    <div class="summary-item">
                                        <div class="summary-label">Status</div>
                                        <div class="summary-value">Awaiting Payment</div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h3><i class="fas fa-wallet"></i> Choose Payment Method</h3>
                    </div>
                    <div class="card-body">
                        <form method="POST" enctype="multipart/form-data" id="paymentForm">
                            <div class="payment-methods">
                                <div class="payment-method" data-method="mobile" onclick="selectPaymentMethod(1, 'mobile')">
                                    <div class="method-header">
                                        <div class="method-icon">
                                            <i class="fas fa-mobile-alt"></i>
                                        </div>
                                        <div class="method-info">
                                            <h4>Mobile Money <span class="recommended-badge">Recommended</span></h4>
                                            <p>Fast & secure mobile payment</p>
                                        </div>
                                        <div class="method-checkbox">
                                            <i class="fas fa-check"></i>
                                        </div>
                                    </div>
                                    <div class="method-details">
                                        <div class="detail-row">
                                            <span class="detail-label">Phone Number:</span>
                                            <span class="detail-value">0785570094</span>
                                        </div>
                                        <div class="detail-row">
                                            <span class="detail-label">Name:</span>
                                            <span class="detail-value">AtoZ Global Link Solutions</span>
                                        </div>
                                    </div>
                                    <input type="radio" name="payment_method_id" value="1" style="display: none;" required>
                                </div>
                                
                                <div class="payment-method" data-method="bank" onclick="selectPaymentMethod(2, 'bank')">
                                    <div class="method-header">
                                        <div class="method-icon">
                                            <i class="fas fa-university"></i>
                                        </div>
                                        <div class="method-info">
                                            <h4>Bank Transfer</h4>
                                            <p>Traditional bank account transfer</p>
                                        </div>
                                        <div class="method-checkbox">
                                            <i class="fas fa-check"></i>
                                        </div>
                                    </div>
                                    <div class="method-details">
                                        <div class="detail-row">
                                            <span class="detail-label">Bank Account:</span>
                                            <span class="detail-value">BK No: 000400030820468</span>
                                        </div>
                                        <div class="detail-row">
                                            <span class="detail-label">Name:</span>
                                            <span class="detail-value">CEO: MUNYANSHONGORE Albert</span>
                                        </div>
                                    </div>
                                    <input type="radio" name="payment_method_id" value="2" style="display: none;" required>
                                </div>
                                
                                <div class="payment-method" data-method="crypto" onclick="selectPaymentMethod(4, 'crypto')">
                                    <div class="method-header">
                                        <div class="method-icon">
                                            <i class="fab fa-bitcoin"></i>
                                        </div>
                                        <div class="method-info">
                                            <h4>Binance Pay</h4>
                                            <p>Fast crypto payment via Binance</p>
                                        </div>
                                        <div class="method-checkbox">
                                            <i class="fas fa-check"></i>
                                        </div>
                                    </div>
                                    <div class="method-details">
                                        <div class="detail-row">
                                            <span class="detail-label">Binance ID:</span>
                                            <span class="detail-value">1164547617</span>
                                        </div>
                                        <div class="detail-row">
                                            <span class="detail-label">Name:</span>
                                            <span class="detail-value">AtoZ Global Link Solutions</span>
                                        </div>
                                    </div>
                                    <input type="radio" name="payment_method_id" value="4" style="display: none;" required>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label>Transaction Reference</label>
                                <input type="text" name="transaction_reference" class="form-control" placeholder="Enter transaction reference" required>
                            </div>
                            
                            <div class="form-group">
                                <label>Payment Proof Type</label>
                                <select name="proof_type" class="form-control" required>
                                    <option value="">Select proof type</option>
                                    <option value="screenshot">Screenshot</option>
                                    <option value="receipt">Receipt</option>
                                    <option value="bank_slip">Bank Slip</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label>Upload Payment Proof</label>
                                <div class="file-upload">
                                    <input type="file" name="proof_of_payment" id="paymentProof" accept="image/*,.pdf" required>
                                    <label for="paymentProof" class="file-upload-label">
                                        <i class="fas fa-cloud-upload-alt"></i>
                                        <span>Upload payment proof (Image or PDF)</span>
                                    </label>
                                </div>
                            </div>
                            
                            <div style="text-align: center; margin-top: 2rem;">
                                <button type="submit" class="btn-submit">
                                    <i class="fas fa-check"></i> Submit Payment Proof
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</main>

<script>
// Payment Method Selection
function selectPaymentMethod(methodId, methodType) {
    // Remove selected class from all methods
    document.querySelectorAll('.payment-method').forEach(method => {
        method.classList.remove('selected');
    });
    
    // Add selected class to clicked method
    event.currentTarget.classList.add('selected');
    document.querySelector(`input[value="${methodId}"]`).checked = true;
}

// File Upload Handler
document.addEventListener('DOMContentLoaded', function() {
    const paymentProofInput = document.getElementById('paymentProof');
    if (paymentProofInput) {
        paymentProofInput.addEventListener('change', function() {
            const fileUpload = this.closest('.file-upload');
            const label = fileUpload.querySelector('.file-upload-label span');
            
            if (this.files && this.files[0]) {
                const file = this.files[0];
                const fileName = file.name;
                const fileSize = (file.size / 1024 / 1024).toFixed(2);
                
                if (file.size > 5 * 1024 * 1024) {
                    alert('File size must be less than 5MB');
                    this.value = '';
                    return;
                }
                
                fileUpload.classList.add('has-file');
                label.innerHTML = `<i class="fas fa-check-circle"></i> ${fileName} (${fileSize}MB)`;
            } else {
                fileUpload.classList.remove('has-file');
                label.innerHTML = 'Upload payment proof (Image or PDF)';
            }
        });
    }
});

// Form Validation
document.getElementById('paymentForm').addEventListener('submit', function(e) {
    const submitBtn = this.querySelector('.btn-submit');
    const paymentMethod = document.querySelector('input[name="payment_method_id"]:checked');
    const transactionRef = document.querySelector('input[name="transaction_reference"]').value;
    const proofType = document.querySelector('select[name="proof_type"]').value;
    const proofFile = document.getElementById('paymentProof').files[0];
    
    if (!paymentMethod) {
        e.preventDefault();
        alert('Please select a payment method');
        return;
    }
    
    if (!transactionRef.trim()) {
        e.preventDefault();
        alert('Please enter transaction reference');
        return;
    }
    
    if (!proofType) {
        e.preventDefault();
        alert('Please select proof type');
        return;
    }
    
    if (!proofFile) {
        e.preventDefault();
        alert('Please upload payment proof');
        return;
    }
    
    // Show loading state
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
});
</script>

<?php include 'includes/footer.php'; ?>