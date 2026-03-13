<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

require_once '../../config/database.php';

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['application_id']) || !isset($input['payment_type'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
    exit;
}

try {
    $pdo = getConnection();
    $pdo->beginTransaction();
    
    $applicationId = $input['application_id'];
    $paymentType = $input['payment_type']; // 'initial' or 'final'
    
    // Get application details
    $stmt = $pdo->prepare("SELECT * FROM applications WHERE id = ?");
    $stmt->execute([$applicationId]);
    $application = $stmt->fetch();
    
    if (!$application) {
        throw new Exception('Application not found');
    }
    
    if ($paymentType === 'initial') {
        // Approve initial payment
        $stmt = $pdo->prepare("UPDATE applications SET initial_payment_status = 'completed', status_id = 3 WHERE id = ?");
        $stmt->execute([$applicationId]);
        
        // Update payment record if exists
        $stmt = $pdo->prepare("UPDATE payments SET payment_status = 'completed' WHERE application_id = ? AND payment_type IN ('full', 'partial_initial')");
        $stmt->execute([$applicationId]);
        
    } elseif ($paymentType === 'final') {
        // Approve final payment
        $stmt = $pdo->prepare("UPDATE applications SET final_payment_status = 'completed' WHERE id = ?");
        $stmt->execute([$applicationId]);
        
        // Update payment record if exists
        $stmt = $pdo->prepare("UPDATE payments SET payment_status = 'completed' WHERE application_id = ? AND payment_type = 'partial_final'");
        $stmt->execute([$applicationId]);
    }
    
    $pdo->commit();
    echo json_encode(['success' => true, 'message' => 'Payment approved successfully']);
    
} catch (Exception $e) {
    $pdo->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>