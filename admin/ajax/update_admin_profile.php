<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

require_once '../../config/database.php';

try {
    $pdo = getConnection();
    
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $is_active = intval($_POST['is_active'] ?? 1);
    
    if (empty($username) || empty($email)) {
        echo json_encode(['success' => false, 'error' => 'Username and email are required']);
        exit;
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'error' => 'Invalid email format']);
        exit;
    }
    
    // Check if username or email already exists for other admins
    $stmt = $pdo->prepare("SELECT id FROM admins WHERE (username = ? OR email = ?) AND id != ?");
    $stmt->execute([$username, $email, $_SESSION['admin_id']]);
    
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'error' => 'Username or email already exists']);
        exit;
    }
    
    // Update admin details
    $stmt = $pdo->prepare("UPDATE admins SET username = ?, email = ?, is_active = ? WHERE id = ?");
    $stmt->execute([$username, $email, $is_active, $_SESSION['admin_id']]);
    
    // Update session data
    $_SESSION['admin_username'] = $username;
    $_SESSION['admin_email'] = $email;
    
    echo json_encode([
        'success' => true, 
        'message' => 'Account updated successfully',
        'username' => $username
    ]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Database error occurred']);
}
?>