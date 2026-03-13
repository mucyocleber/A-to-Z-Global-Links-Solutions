<?php
header('Content-Type: application/json');
require_once 'config/database.php';

if (!isset($_GET['service']) || !is_numeric($_GET['service'])) {
    echo json_encode(['error' => 'Invalid service ID']);
    exit;
}

try {
    $pdo = getConnection();
    $stmt = $pdo->prepare("
        SELECT s.*, sc.name as category_name 
        FROM services s 
        LEFT JOIN service_categories sc ON s.category_id = sc.id 
        WHERE s.id = ? AND s.is_active = 1
    ");
    $stmt->execute([$_GET['service']]);
    $service = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($service) {
        echo json_encode(['service' => $service]);
    } else {
        echo json_encode(['error' => 'Service not found']);
    }
} catch (Exception $e) {
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>