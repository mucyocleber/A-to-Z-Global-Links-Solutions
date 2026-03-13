<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

require_once '../../config/database.php';

$input = $_POST;

if (!isset($input['application_id'])) {
    echo json_encode(['error' => 'Missing application ID']);
    exit;
}

$application_id = (int)$input['application_id'];

try {
    $pdo = getConnection();
    
    $stmt = $pdo->prepare("
        SELECT m.id, m.message, m.sender_type, m.sender_id, m.created_at
        FROM application_messages m
        WHERE m.application_id = ?
        ORDER BY m.created_at ASC
    ");
    
    $stmt->execute([$application_id]);
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Mark user messages as read
    $pdo->prepare("UPDATE application_messages SET is_read = 1 WHERE application_id = ? AND sender_type = 'user'")->execute([$application_id]);
    
    $html = '';
    if (!empty($messages)) {
        foreach ($messages as $msg) {
            $isAdmin = $msg['sender_type'] === 'admin';
            $html .= '<div class="message ' . ($isAdmin ? 'admin' : 'user') . '">';
            $html .= '<div class="message-content">' . nl2br(htmlspecialchars($msg['message'])) . '</div>';
            $html .= '<div class="message-time">' . date('M d, H:i', strtotime($msg['created_at'])) . '</div>';
            $html .= '</div>';
        }
    } else {
        $html = '<div class="no-messages">💬 No messages yet. Start the conversation!</div>';
    }
    
    echo json_encode(['success' => true, 'html' => $html, 'count' => count($messages)]);
    
} catch (Exception $e) {
    echo json_encode(['error' => 'Failed to load messages: ' . $e->getMessage(), 'debug' => $application_id]);
}
?>