<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit;
}

require_once '../config/database.php';

if (!isset($_GET['id'])) {
    die('Payment ID required');
}

$payment_id = (int)$_GET['id'];

try {
    $pdo = getConnection();
    $stmt = $pdo->prepare("SELECT * FROM payments WHERE id = ?");
    $stmt->execute([$payment_id]);
    $payment = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$payment || !$payment['proof_of_payment']) {
        die('Payment proof not found');
    }
    
    $file_path = '../uploads/payments/' . $payment['proof_of_payment'];
    
    if (!file_exists($file_path)) {
        die('File not found');
    }
    
    $mime_type = mime_content_type($file_path) ?: 'image/jpeg';
    
    header('Content-Type: ' . $mime_type);
    header('Content-Disposition: inline; filename="payment_proof_' . $payment['id'] . '"');
    header('Content-Length: ' . filesize($file_path));
    
    readfile($file_path);
    
} catch (Exception $e) {
    die('Error loading payment proof');
}
?>