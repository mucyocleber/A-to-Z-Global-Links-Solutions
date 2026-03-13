<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

require_once '../../config/database.php';

$input = json_decode(file_get_contents('php://input'), true);
$document_id = $input['document_id'] ?? null;

if (!$document_id) {
    echo json_encode(['success' => false, 'error' => 'Document ID required']);
    exit;
}

try {
    $pdo = getConnection();
    
    // Get document info first
    $stmt = $pdo->prepare("SELECT file_path, stored_filename FROM documents WHERE id = ?");
    $stmt->execute([$document_id]);
    $document = $stmt->fetch();
    
    if (!$document) {
        echo json_encode(['success' => false, 'error' => 'Document not found']);
        exit;
    }
    
    // Delete from database
    $stmt = $pdo->prepare("DELETE FROM documents WHERE id = ?");
    $result = $stmt->execute([$document_id]);
    
    if ($result) {
        // Delete physical file
        $filePath = '../../' . $document['file_path'];
        if (file_exists($filePath)) {
            unlink($filePath);
        }
        
        echo json_encode(['success' => true, 'message' => 'Document deleted successfully']);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to delete document']);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
}
?>