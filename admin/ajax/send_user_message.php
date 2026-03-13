<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

require_once '../../config/database.php';

if (!isset($_POST['user_id']) || !isset($_POST['message'])) {
    echo json_encode(['error' => 'Missing required fields']);
    exit;
}

$user_id = (int)$_POST['user_id'];
$message = trim($_POST['message']);

if (empty($message)) {
    echo json_encode(['error' => 'Message cannot be empty']);
    exit;
}

try {
    $pdo = getConnection();
    
    // Get the user's most recent application to attach the message to
    $stmt = $pdo->prepare("
        SELECT id FROM applications 
        WHERE user_id = ? 
        ORDER BY submitted_at DESC 
        LIMIT 1
    ");
    $stmt->execute([$user_id]);
    $application = $stmt->fetch();
    
    if (!$application) {
        echo json_encode(['error' => 'No application found for this user']);
        exit;
    }
    
    // Insert the message
    $stmt = $pdo->prepare("
        INSERT INTO application_messages (application_id, sender_type, sender_id, message) 
        VALUES (?, 'admin', ?, ?)
    ");
    
    $stmt->execute([$application['id'], $_SESSION['admin_id'], $message]);
    
    echo json_encode(['success' => true]);
    
} catch (Exception $e) {
    echo json_encode(['error' => 'Failed to send message: ' . $e->getMessage()]);
}
?>