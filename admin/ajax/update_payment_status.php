<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

require_once '../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$applicationId = $_POST['application_id'] ?? null;
$paymentType = $_POST['payment_type'] ?? null; // 'initial' or 'final'
$status = $_POST['status'] ?? null; // 'completed' or 'failed'

if (!$applicationId || !$paymentType || !$status) {
    echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
    exit;
}

try {
    $pdo = getConnection();
    
    // Get application details
    $stmt = $pdo->prepare("SELECT application_type, payment_plan, total_amount, initial_payment_amount, remaining_payment_amount FROM applications WHERE id = ?");
    $stmt->execute([$applicationId]);
    $application = $stmt->fetch();
    
    if (!$application) {
        echo json_encode(['success' => false, 'message' => 'Application not found']);
        exit;
    }
    
    // Update payment status based on type
    if ($paymentType === 'initial') {
        $stmt = $pdo->prepare("UPDATE applications SET initial_payment_status = ? WHERE id = ?");
        $stmt->execute([$status, $applicationId]);
        
        // If completed, update amount paid
        if ($status === 'completed') {
            $amountPaid = ($application['payment_plan'] === 'full') ? $application['total_amount'] : $application['initial_payment_amount'];
            $percentage = ($amountPaid / $application['total_amount']) * 100;
            
            $stmt = $pdo->prepare("UPDATE applications SET amount_paid_so_far = ?, payment_completion_percentage = ? WHERE id = ?");
            $stmt->execute([$amountPaid, $percentage, $applicationId]);
        }
    } elseif ($paymentType === 'final') {
        $stmt = $pdo->prepare("UPDATE applications SET final_payment_status = ? WHERE id = ?");
        $stmt->execute([$status, $applicationId]);
        
        // If completed, update amount paid to full
        if ($status === 'completed') {
            $stmt = $pdo->prepare("UPDATE applications SET amount_paid_so_far = ?, payment_completion_percentage = 100.00 WHERE id = ?");
            $stmt->execute([$application['total_amount'], $applicationId]);
        }
    }
    
    echo json_encode(['success' => true, 'message' => 'Payment status updated successfully']);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>