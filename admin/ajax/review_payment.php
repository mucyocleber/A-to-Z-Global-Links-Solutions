<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

require_once '../../config/database.php';

$input = json_decode(file_get_contents('php://input'), true);
$applicationId = $input['application_id'] ?? null;
$status = $input['status'] ?? null;

if (!$applicationId || !$status) {
    echo json_encode(['success' => false, 'message' => 'Application ID and status required']);
    exit;
}

$validStatuses = ['under_review', 'approved', 'rejected'];
if (!in_array($status, $validStatuses)) {
    echo json_encode(['success' => false, 'message' => 'Invalid status']);
    exit;
}

try {
    $pdo = getConnection();
    
    // Update payment status
    $stmt = $pdo->prepare("UPDATE payments SET payment_status = ?, reviewed_at = NOW(), reviewed_by = ? WHERE application_id = ?");
    $stmt->execute([$status, $_SESSION['admin_id'], $applicationId]);
    
    if ($stmt->rowCount() > 0) {
        // If payment is approved, update application status to processing
        if ($status === 'approved') {
            $statusStmt = $pdo->prepare("SELECT id FROM application_statuses WHERE status_code = 'processing'");
            $statusStmt->execute();
            $statusId = $statusStmt->fetchColumn();
            
            if ($statusId) {
                $pdo->prepare("UPDATE applications SET status_id = ? WHERE id = ?")->execute([$statusId, $applicationId]);
            }
        }
        
        echo json_encode(['success' => true, 'message' => 'Payment status updated successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Payment not found or already updated']);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error updating payment status: ' . $e->getMessage()]);
}
?>