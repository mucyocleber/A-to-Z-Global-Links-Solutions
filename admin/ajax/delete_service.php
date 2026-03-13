<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

require_once '../../config/database.php';

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['service_id'])) {
    echo json_encode(['success' => false, 'message' => 'Missing service ID']);
    exit;
}

$serviceId = (int)$input['service_id'];

try {
    $pdo = getConnection();
    
    // Check if service has applications
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM applications WHERE service_id = ?");
    $stmt->execute([$serviceId]);
    $applicationCount = $stmt->fetchColumn();
    
    if ($applicationCount > 0) {
        // Soft delete - set is_active to 0 instead of deleting
        $stmt = $pdo->prepare("UPDATE services SET is_active = 0 WHERE id = ?");
        $result = $stmt->execute([$serviceId]);
        $message = 'Service deactivated (has existing applications)';
    } else {
        // Hard delete if no applications
        $stmt = $pdo->prepare("DELETE FROM services WHERE id = ?");
        $result = $stmt->execute([$serviceId]);
        $message = 'Service deleted successfully';
    }
    
    if ($result && $stmt->rowCount() > 0) {
        echo json_encode(['success' => true, 'message' => $message]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Service not found']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>