<?php
session_start();
require_once '../../config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['admin_id'])) {
    echo json_encode([]);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['query'])) {
    echo json_encode([]);
    exit();
}

$query = trim($_POST['query']);

if (strlen($query) < 2) {
    echo json_encode([]);
    exit();
}

try {
    $pdo = getConnection();
    $searchTerm = "%{$query}%";
    
    $stmt = $pdo->prepare("
        SELECT id, first_name, last_name, email, phone 
        FROM users 
        WHERE (first_name LIKE ? OR last_name LIKE ? OR email LIKE ? OR phone LIKE ?)
        AND is_active = 1
        ORDER BY first_name, last_name
        LIMIT 10
    ");
    
    $stmt->execute([$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($users);
    
} catch (Exception $e) {
    echo json_encode([]);
}
?>