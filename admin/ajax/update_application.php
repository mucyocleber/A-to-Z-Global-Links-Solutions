<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

require_once '../../config/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$applicationId = $_POST['application_id'] ?? '';

if (empty($applicationId)) {
    echo json_encode(['success' => false, 'message' => 'Application ID is required']);
    exit;
}

try {
    $pdo = getConnection();
    
    // Verify it's a manual application
    $stmt = $pdo->prepare("SELECT application_type FROM applications WHERE id = ?");
    $stmt->execute([$applicationId]);
    $app = $stmt->fetch();
    
    if (!$app || $app['application_type'] !== 'manual') {
        echo json_encode(['success' => false, 'message' => 'Only manual applications can be edited']);
        exit;
    }
    
    // Update application details
    $stmt = $pdo->prepare("
        UPDATE applications SET 
            destination_country_id = ?,
            travel_purpose = ?,
            intended_travel_date = ?,
            duration_of_stay = ?
        WHERE id = ?
    ");
    
    $stmt->execute([
        $_POST['destination_country_id'] ?: null,
        $_POST['travel_purpose'] ?: null,
        $_POST['intended_travel_date'] ?: null,
        $_POST['duration_of_stay'] ?: null,
        $applicationId
    ]);
    
    echo json_encode(['success' => true, 'message' => 'Application updated successfully']);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
