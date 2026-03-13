<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

require_once '../../config/database.php';

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['service_id']) || !isset($input['status'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
    exit;
}

$serviceId = (int)$input['service_id'];
$status = (int)$input['status'];

try {
    $pdo = getConnection();
    $stmt = $pdo->prepare("UPDATE services SET is_active = ? WHERE id = ?");
    $result = $stmt->execute([$status, $serviceId]);
    
    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Service status updated successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update service status']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>