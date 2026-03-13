<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

require_once '../../config/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Invalid request method']);
    exit;
}

try {
    $pdo = getConnection();
    
    $application_id = (int)$_POST['application_id'];
    $status_id = (int)$_POST['status_id'];
    
    if (!$application_id || !$status_id) {
        echo json_encode(['error' => 'Missing required fields']);
        exit;
    }
    
    $stmt = $pdo->prepare("UPDATE applications SET status_id = ? WHERE id = ?");
    $stmt->execute([$status_id, $application_id]);
    
    echo json_encode(['success' => true]);
    
} catch (Exception $e) {
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>