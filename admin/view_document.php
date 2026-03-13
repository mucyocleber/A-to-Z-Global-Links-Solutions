<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit;
}

require_once '../config/database.php';

if (!isset($_GET['id'])) {
    die('Document ID required');
}

$document_id = (int)$_GET['id'];

try {
    $pdo = getConnection();
    $stmt = $pdo->prepare("SELECT * FROM documents WHERE id = ?");
    $stmt->execute([$document_id]);
    $document = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$document) {
        die('Document not found');
    }
    
    $file_path = '../' . $document['file_path'];
    
    if (!file_exists($file_path)) {
        die('File not found');
    }
    
    $mime_type = $document['mime_type'] ?: 'application/octet-stream';
    
    header('Content-Type: ' . $mime_type);
    header('Content-Disposition: inline; filename="' . $document['original_filename'] . '"');
    header('Content-Length: ' . filesize($file_path));
    
    readfile($file_path);
    
} catch (Exception $e) {
    die('Error loading document');
}
?>