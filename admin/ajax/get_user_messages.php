<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

require_once '../../config/database.php';

if (!isset($_POST['user_id'])) {
    echo json_encode(['error' => 'Missing user ID']);
    exit;
}

$user_id = (int)$_POST['user_id'];

try {
    $pdo = getConnection();
    
    // Get all messages for this user across all their applications
    $stmt = $pdo->prepare("
        SELECT 
            am.id, 
            am.message, 
            am.sender_type, 
            am.sender_id, 
            am.created_at,
            a.application_number,
            u.first_name,
            u.last_name,
            ad.username as admin_name
        FROM application_messages am
        JOIN applications a ON am.application_id = a.id
        JOIN users u ON a.user_id = u.id
        LEFT JOIN admins ad ON am.sender_type = 'admin' AND am.sender_id = ad.id
        WHERE a.user_id = ?
        ORDER BY am.created_at ASC
    ");
    
    $stmt->execute([$user_id]);
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Mark user messages as read
    $pdo->prepare("
        UPDATE application_messages am
        JOIN applications a ON am.application_id = a.id
        SET am.is_read = 1 
        WHERE a.user_id = ? AND am.sender_type = 'user'
    ")->execute([$user_id]);
    
    echo json_encode(['success' => true, 'messages' => $messages]);
    
} catch (Exception $e) {
    echo json_encode(['error' => 'Failed to load messages: ' . $e->getMessage()]);
}
?>