<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

require_once '../../config/database.php';

$refund_id = $_POST['refund_id'] ?? null;
$status = $_POST['status'] ?? null;

if (!$refund_id || !$status || !in_array($status, ['approved', 'rejected'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
    exit();
}

try {
    $conn = getConnection();
    $conn->beginTransaction();
    
    // Update refund request status
    $stmt = $conn->prepare("UPDATE refund_requests SET status = ? WHERE id = ?");
    $stmt->execute([$status, $refund_id]);
    
    // Update application refund status
    $stmt = $conn->prepare("
        UPDATE applications 
        SET refund_status = ? 
        WHERE id = (SELECT application_id FROM refund_requests WHERE id = ?)
    ");
    $stmt->execute([$status, $refund_id]);
    
    $conn->commit();
    echo json_encode(['success' => true, 'message' => 'Refund request updated successfully']);
    
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>