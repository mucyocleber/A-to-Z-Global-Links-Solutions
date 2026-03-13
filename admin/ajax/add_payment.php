<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

require_once '../../config/database.php';

$application_id = $_POST['application_id'] ?? null;
$payment_type = $_POST['payment_type'] ?? 'additional';
$amount = $_POST['amount'] ?? null;
$payment_method = $_POST['payment_method'] ?? null;
$payment_method_id = $_POST['payment_method_id'] ?? null;
$payment_date = $_POST['payment_date'] ?? null;
$reference = $_POST['reference'] ?? '';
$notes = $_POST['notes'] ?? '';

if (!$application_id || !$amount || !$payment_method_id || !$payment_date) {
    echo json_encode(['success' => false, 'error' => 'Missing required fields']);
    exit;
}

try {
    $pdo = getConnection();
    $pdo->beginTransaction();
    
    // Get user_id from application
    $stmt = $pdo->prepare("SELECT user_id FROM applications WHERE id = ?");
    $stmt->execute([$application_id]);
    $application = $stmt->fetch();
    
    if (!$application) {
        echo json_encode(['success' => false, 'error' => 'Application not found']);
        exit;
    }
    
    $user_id = $application['user_id'];
    
    // Handle file upload if provided (not required for cash payments)
    $proofPath = null;
    $isCashPayment = false;
    
    // Check if it's a cash payment
    if ($payment_method_id) {
        $methodStmt = $pdo->prepare("SELECT name FROM payment_methods WHERE id = ?");
        $methodStmt->execute([$payment_method_id]);
        $method = $methodStmt->fetch();
        if ($method && strtolower($method['name']) === 'cash payment') {
            $isCashPayment = true;
        }
    }
    
    if (!$isCashPayment && isset($_FILES['payment_proof']) && $_FILES['payment_proof']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['payment_proof'];
        
        // Validate file
        $allowedTypes = ['application/pdf', 'image/jpeg', 'image/jpg', 'image/png'];
        if (!in_array($file['type'], $allowedTypes)) {
            echo json_encode(['success' => false, 'error' => 'Invalid file type. Only PDF and images allowed.']);
            exit;
        }
        
        if ($file['size'] > 5 * 1024 * 1024) { // 5MB limit
            echo json_encode(['success' => false, 'error' => 'File too large. Maximum 5MB allowed.']);
            exit;
        }
        
        // Create upload directory
        $uploadDir = '../../uploads/payments/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        // Generate unique filename
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'payment_' . $application_id . '_' . time() . '.' . $extension;
        $filepath = $uploadDir . $filename;
        
        if (move_uploaded_file($file['tmp_name'], $filepath)) {
            $proofPath = 'uploads/payments/' . $filename;
        }
    }
    
    // Insert payment record
    $stmt = $pdo->prepare("
        INSERT INTO payments (
            application_id, user_id, payment_type, amount, proof_of_payment, payment_method_id, payment_status, created_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
    ");
    
    // Set payment status based on payment method
    $paymentStatus = $isCashPayment ? 'approved' : 'proof_submitted';
    
    $result = $stmt->execute([
        $application_id,
        $user_id,
        $payment_type,
        $amount,
        $proofPath,
        $payment_method_id,
        $paymentStatus
    ]);
    
    if ($result) {
        // Update application payment status based on payment type
        if ($payment_type === 'initial' || $payment_type === 'full') {
            $stmt = $pdo->prepare("UPDATE applications SET initial_payment_status = 'completed' WHERE id = ?");
            $stmt->execute([$application_id]);
        } elseif ($payment_type === 'final') {
            $stmt = $pdo->prepare("UPDATE applications SET final_payment_status = 'completed' WHERE id = ?");
            $stmt->execute([$application_id]);
        }
        
        $pdo->commit();
        echo json_encode(['success' => true, 'message' => 'Payment record added successfully']);
    } else {
        $pdo->rollBack();
        if ($proofPath && file_exists('../../' . $proofPath)) {
            unlink('../../' . $proofPath);
        }
        echo json_encode(['success' => false, 'error' => 'Failed to save payment record']);
    }
    
} catch (Exception $e) {
    $pdo->rollBack();
    if ($proofPath && file_exists('../../' . $proofPath)) {
        unlink('../../' . $proofPath);
    }
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
}
?>