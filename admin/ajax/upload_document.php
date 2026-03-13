<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

require_once '../../config/database.php';

$application_id = $_POST['application_id'] ?? null;
$document_type = $_POST['document_type'] ?? 'Document';

if (!$application_id || !isset($_FILES['document'])) {
    echo json_encode(['success' => false, 'error' => 'Missing required parameters']);
    exit;
}

try {
    $file = $_FILES['document'];
    
    // Validate file
    $allowedTypes = [
        'application/pdf',
        'image/jpeg', 'image/jpg', 'image/png',
        'image/pjpeg', 'image/x-png'
    ];
    
    $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowedExtensions = ['pdf', 'jpg', 'jpeg', 'png'];
    
    // Check both MIME type and file extension
    if (!in_array($file['type'], $allowedTypes) && !in_array($fileExtension, $allowedExtensions)) {
        echo json_encode(['success' => false, 'error' => 'Invalid file type. Only PDF and images (JPG, PNG) allowed.']);
        exit;
    }
    
    if ($file['size'] > 5 * 1024 * 1024) { // 5MB limit
        echo json_encode(['success' => false, 'error' => 'File too large. Maximum 5MB allowed.']);
        exit;
    }
    
    // Create upload directory if it doesn't exist
    $uploadDir = '../../uploads/documents/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    // Generate unique filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = 'doc_' . $application_id . '_' . time() . '.' . $extension;
    $filepath = $uploadDir . $filename;
    
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        $pdo = getConnection();
        
        $stmt = $pdo->prepare("
            INSERT INTO documents (application_id, document_type, original_filename, stored_filename, file_path, file_size, mime_type, uploaded_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        
        $result = $stmt->execute([
            $application_id,
            $document_type,
            $file['name'],
            $filename,
            'uploads/documents/' . $filename,
            $file['size'],
            $file['type']
        ]);
        
        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Document uploaded successfully']);
        } else {
            unlink($filepath); // Delete file if database insert fails
            echo json_encode(['success' => false, 'error' => 'Failed to save document record']);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to upload file']);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
}
?>