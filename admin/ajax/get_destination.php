<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

require_once '../../config/database.php';

try {
    $pdo = getConnection();
    $id = $_GET['id'] ?? null;
    
    if (empty($id)) {
        echo json_encode(['success' => false, 'error' => 'ID is required']);
        exit;
    }
    
    $stmt = $pdo->prepare("
        SELECT dc.*, c.name as country_name 
        FROM destination_countries dc 
        JOIN countries c ON dc.country_id = c.id 
        WHERE dc.id = ?
    ");
    $stmt->execute([$id]);
    $destination = $stmt->fetch();
    
    if (!$destination) {
        echo json_encode(['success' => false, 'error' => 'Destination not found']);
        exit;
    }
    
    echo json_encode(['success' => true, 'destination' => $destination]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>