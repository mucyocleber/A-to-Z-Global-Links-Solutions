<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

require_once '../../config/database.php';

$input = $_POST;

if (!isset($input['application_id']) || !isset($input['message'])) {
    echo json_encode(['error' => 'Missing required fields']);
    exit;
}

$application_id = (int)$input['application_id'];
$message = trim($input['message']);

if (empty($message)) {
    echo json_encode(['error' => 'Message cannot be empty']);
    exit;
}

try {
    $pdo = getConnection();
    
    $stmt = $pdo->prepare("
        INSERT INTO application_messages (application_id, sender_type, sender_id, message) 
        VALUES (?, 'admin', ?, ?)
    ");
    
    $stmt->execute([$application_id, $_SESSION['admin_id'], $message]);
    
    echo json_encode(['success' => true]);
    
} catch (Exception $e) {
    echo json_encode(['error' => 'Failed to send message']);
}
?>