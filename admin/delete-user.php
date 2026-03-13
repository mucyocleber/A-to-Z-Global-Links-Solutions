<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

require_once '../config/database.php';
require_once 'includes/safe_delete.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Invalid request method']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

try {
    $pdo = getConnection();
    $user_id = (int)$input['user_id'];
    
    if (!$user_id) {
        echo json_encode(['error' => 'Missing user ID']);
        exit;
    }
    
    // Use safe delete function
    $result = safeDeleteUser($pdo, $user_id);
    
    if ($result['success']) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['error' => $result['message']]);
    }
    
} catch (Exception $e) {
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>