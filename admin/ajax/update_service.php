<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

require_once '../../config/database.php';

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
    exit;
}

try {
    $pdo = getConnection();
    
    $stmt = $pdo->prepare("UPDATE services SET 
        name = ?, category_id = ?, base_price = ?, currency = ?, 
        processing_time_min = ?, processing_time_max = ?, 
        short_description = ?, full_description = ?, 
        is_featured = ?, sort_order = ? 
        WHERE id = ?");
    
    $result = $stmt->execute([
        $data['name'],
        $data['category_id'],
        $data['base_price'],
        $data['currency'],
        $data['processing_time_min'],
        $data['processing_time_max'],
        $data['short_description'],
        $data['full_description'] ?? null,
        $data['is_featured'] ?? 0,
        $data['sort_order'] ?? 0,
        $data['id']
    ]);
    
    if ($result) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Update failed']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>