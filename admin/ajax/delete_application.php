<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

require_once '../../config/database.php';
require_once '../includes/safe_delete.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$applicationId = $_POST['application_id'] ?? '';

if (empty($applicationId)) {
    echo json_encode(['success' => false, 'message' => 'Missing application ID']);
    exit;
}

try {
    $pdo = getConnection();
    
    // Check if application exists
    $checkStmt = $pdo->prepare("SELECT id FROM applications WHERE id = ?");
    $checkStmt->execute([$applicationId]);
    
    if (!$checkStmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Application not found']);
        exit;
    }
    
    // Use safe delete function
    $result = safeDeleteApplication($pdo, $applicationId);
    echo json_encode($result);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>