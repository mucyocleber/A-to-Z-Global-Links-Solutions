<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

require_once '../config/database.php';

header('Content-Type: application/json');

if (!isset($_GET['id'])) {
    echo json_encode(['error' => 'User ID required']);
    exit;
}

try {
    $pdo = getConnection();
    $user_id = (int)$_GET['id'];
    
    $stmt = $pdo->prepare("
        SELECT u.*, 
               COUNT(DISTINCT a.id) as application_count,
               COALESCE(SUM(CASE WHEN p.payment_status IN ('approved', 'completed') THEN p.amount ELSE 0 END), 0) as total_paid
        FROM users u
        LEFT JOIN applications a ON u.id = a.user_id
        LEFT JOIN payments p ON a.id = p.application_id
        WHERE u.id = ?
        GROUP BY u.id
    ");
    
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        // Try simple query if complex one fails
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user) {
            $user['application_count'] = 0;
            $user['total_paid'] = 0;
        }
    }
    
    if (!$user) {
        echo json_encode(['error' => 'User not found']);
        exit;
    }
    
    echo json_encode([
        'success' => true,
        'user' => $user
    ]);
    
} catch (Exception $e) {
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>