<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

require_once '../../config/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$applicationId = $_POST['application_id'] ?? '';
$statusId = $_POST['status_id'] ?? '';

if (empty($applicationId) || empty($statusId)) {
    echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
    exit;
}

try {
    $pdo = getConnection();
    
    // Get current status for logging
    $currentStmt = $pdo->prepare("SELECT st.status_name FROM applications a JOIN application_statuses st ON a.status_id = st.id WHERE a.id = ?");
    $currentStmt->execute([$applicationId]);
    $currentStatus = $currentStmt->fetchColumn();
    
    // Get new status name
    $newStmt = $pdo->prepare("SELECT status_name FROM application_statuses WHERE id = ?");
    $newStmt->execute([$statusId]);
    $newStatus = $newStmt->fetchColumn();
    
    if (!$newStatus) {
        echo json_encode(['success' => false, 'message' => 'Invalid status ID']);
        exit;
    }
    
    // Update application status
    $stmt = $pdo->prepare("UPDATE applications SET status_id = ?, last_updated_at = NOW() WHERE id = ?");
    $stmt->execute([$statusId, $applicationId]);
    
    echo json_encode(['success' => true, 'message' => 'Status updated successfully']);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>