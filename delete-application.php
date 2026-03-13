<?php
session_start();
require_once 'config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$applicationId = $input['id'] ?? null;

if (!$applicationId) {
    echo json_encode(['success' => false, 'message' => 'Application ID is required']);
    exit;
}

try {
    $pdo = getConnection();
    
    // Check if application belongs to user and can be deleted
    $stmt = $pdo->prepare("SELECT ast.status_name FROM applications a LEFT JOIN application_statuses ast ON a.status_id = ast.id WHERE a.id = ? AND a.user_id = ?");
    $stmt->execute([$applicationId, $_SESSION['user_id']]);
    $application = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$application) {
        echo json_encode(['success' => false, 'message' => 'Application not found']);
        exit;
    }
    
    // Only allow deletion of draft or submitted applications
    if (!in_array(strtolower($application['status_name']), ['draft', 'submitted'])) {
        echo json_encode(['success' => false, 'message' => 'Cannot delete application in current status']);
        exit;
    }
    
    // Delete related documents first
    $stmt = $pdo->prepare("DELETE FROM documents WHERE application_id = ?");
    $stmt->execute([$applicationId]);
    
    // Delete the application
    $stmt = $pdo->prepare("DELETE FROM applications WHERE id = ? AND user_id = ?");
    $result = $stmt->execute([$applicationId, $_SESSION['user_id']]);
    
    if ($result && $stmt->rowCount() > 0) {
        echo json_encode(['success' => true, 'message' => 'Application deleted successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to delete application']);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>