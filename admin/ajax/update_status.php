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

$validStatuses = ['submitted', 'under_review', 'documents_required', 'processing', 'approved', 'rejected', 'completed', 'cancelled'];
if (!in_array($status, $validStatuses)) {
    echo json_encode(['success' => false, 'message' => 'Invalid status']);
    exit;
}

try {
    $pdo = getConnection();
    
    // Get status ID
    $stmt = $pdo->prepare("SELECT id FROM application_statuses WHERE status_code = ?");
    $stmt->execute([$status]);
    $statusId = $stmt->fetchColumn();
    
    if (!$statusId) {
        echo json_encode(['success' => false, 'message' => 'Invalid status code']);
        exit;
    }
    
    // Update application status
    $stmt = $pdo->prepare("UPDATE applications SET status_id = ?, updated_at = NOW() WHERE id = ?");
    $stmt->execute([$statusId, $applicationId]);
    
    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true, 'message' => 'Application status updated successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Application not found or status unchanged']);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error updating application status: ' . $e->getMessage()]);
}
?>