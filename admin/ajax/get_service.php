<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

require_once '../../config/database.php';

if (!isset($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'Missing service ID']);
    exit;
}

$serviceId = (int)$_GET['id'];

try {
    $pdo = getConnection();
    
    $stmt = $pdo->prepare("
        SELECT s.*, sc.name as category_name, sc.icon as category_icon 
        FROM services s 
        LEFT JOIN service_categories sc ON s.category_id = sc.id 
        WHERE s.id = ?
    ");
    $stmt->execute([$serviceId]);
    $service = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$service) {
        echo json_encode(['success' => false, 'message' => 'Service not found']);
        exit;
    }
    
    echo json_encode(['success' => true, 'service' => $service]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>