<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

require_once '../../config/database.php';

try {
    $pdo = getConnection();
    
    $stmt = $pdo->prepare("
        SELECT DISTINCT
            u.id as user_id,
            u.first_name,
            u.last_name,
            u.email,
            (SELECT COUNT(*) FROM application_messages am 
             JOIN applications a ON am.application_id = a.id
             WHERE a.user_id = u.id AND am.sender_type = 'user' AND am.is_read = 0) as unread_count,
            (SELECT MAX(am.created_at) FROM application_messages am 
             JOIN applications a ON am.application_id = a.id
             WHERE a.user_id = u.id) as last_message_time,
            (SELECT am.message FROM application_messages am 
             JOIN applications a ON am.application_id = a.id
             WHERE a.user_id = u.id 
             ORDER BY am.created_at DESC LIMIT 1) as last_message
        FROM users u
        WHERE EXISTS (SELECT 1 FROM applications a WHERE a.user_id = u.id)
        ORDER BY last_message_time DESC, u.created_at DESC
    ");
    
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Format the data for better display
    foreach ($users as &$user) {
        if ($user['last_message_time']) {
            $time = new DateTime($user['last_message_time']);
            $now = new DateTime();
            $diff = $now->diff($time);
            
            if ($diff->days == 0) {
                $user['formatted_time'] = $time->format('H:i');
            } elseif ($diff->days == 1) {
                $user['formatted_time'] = 'Yesterday';
            } elseif ($diff->days < 7) {
                $user['formatted_time'] = $time->format('D');
            } else {
                $user['formatted_time'] = $time->format('M j');
            }
        } else {
            $user['formatted_time'] = '';
        }
        
        // Truncate last message if too long
        if ($user['last_message'] && strlen($user['last_message']) > 50) {
            $user['last_message'] = substr($user['last_message'], 0, 50) . '...';
        }
    }
    
    echo json_encode(['success' => true, 'users' => $users]);
    
} catch (Exception $e) {
    echo json_encode(['error' => 'Failed to load users']);
}
?>