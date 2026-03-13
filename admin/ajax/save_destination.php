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
    
    $destination_id = $_POST['destination_id'] ?? null;
    $country_id = $_POST['country_id'] ?? null;
    $flag_emoji = $_POST['flag_emoji'] ?? null;
    $services_offered = $_POST['services_offered'] ?? null;
    $is_featured = $_POST['is_featured'] ?? 1;
    $sort_order = $_POST['sort_order'] ?? 0;
    
    // Validation
    if (empty($country_id) || empty($flag_emoji) || empty($services_offered)) {
        echo json_encode(['success' => false, 'error' => 'All required fields must be filled']);
        exit;
    }
    
    if (empty($destination_id)) {
        // Insert new destination
        $stmt = $pdo->prepare("
            INSERT INTO destination_countries (country_id, flag_emoji, services_offered, is_featured, sort_order) 
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([$country_id, $flag_emoji, $services_offered, $is_featured, $sort_order]);
    } else {
        // Update existing destination
        $stmt = $pdo->prepare("
            UPDATE destination_countries 
            SET country_id = ?, flag_emoji = ?, services_offered = ?, is_featured = ?, sort_order = ? 
            WHERE id = ?
        ");
        $stmt->execute([$country_id, $flag_emoji, $services_offered, $is_featured, $sort_order, $destination_id]);
    }
    
    echo json_encode(['success' => true]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>