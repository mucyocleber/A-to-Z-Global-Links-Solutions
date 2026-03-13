<?php
require_once 'config/database.php';

header('Content-Type: application/json');

if (!isset($_GET['service'])) {
    echo json_encode([]);
    exit;
}

try {
    $pdo = getConnection();
    $stmt = $pdo->prepare("
        SELECT * FROM service_requirements 
        WHERE service_id = ? 
        ORDER BY is_mandatory DESC, sort_order, name
    ");
    $stmt->execute([$_GET['service']]);
    $requirements = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($requirements);
} catch (Exception $e) {
    echo json_encode([]);
}
?>