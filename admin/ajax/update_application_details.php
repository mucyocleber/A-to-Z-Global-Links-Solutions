<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

require_once '../../config/database.php';

$application_id = $_POST['application_id'] ?? null;
$user_id = $_POST['user_id'] ?? null;

if (!$application_id || !$user_id) {
    echo json_encode(['success' => false, 'error' => 'Missing required parameters']);
    exit;
}

try {
    $pdo = getConnection();
    $pdo->beginTransaction();
    
    // Update application details
    $app_fields = [];
    $app_params = [];
    
    if (isset($_POST['travel_date']) && !empty($_POST['travel_date'])) {
        $app_fields[] = "intended_travel_date = ?";
        $app_params[] = $_POST['travel_date'];
    }
    
    if (isset($_POST['duration_of_stay'])) {
        $app_fields[] = "duration_of_stay = ?";
        $app_params[] = $_POST['duration_of_stay'];
    }
    
    if (isset($_POST['travel_purpose'])) {
        $app_fields[] = "travel_purpose = ?";
        $app_params[] = $_POST['travel_purpose'];
    }
    
    if (!empty($app_fields)) {
        $app_params[] = $application_id;
        $app_query = "UPDATE applications SET " . implode(', ', $app_fields) . " WHERE id = ?";
        $stmt = $pdo->prepare($app_query);
        $stmt->execute($app_params);
    }
    
    // Update user details
    $user_fields = [];
    $user_params = [];
    
    if (isset($_POST['nationality'])) {
        $user_fields[] = "nationality = ?";
        $user_params[] = $_POST['nationality'];
    }
    
    if (isset($_POST['passport_number'])) {
        $user_fields[] = "passport_number = ?";
        $user_params[] = $_POST['passport_number'];
    }
    
    if (isset($_POST['date_of_birth']) && !empty($_POST['date_of_birth'])) {
        $user_fields[] = "date_of_birth = ?";
        $user_params[] = $_POST['date_of_birth'];
    }
    
    if (isset($_POST['phone'])) {
        $user_fields[] = "phone = ?";
        $user_params[] = $_POST['phone'];
    }
    
    if (isset($_POST['address'])) {
        $user_fields[] = "address = ?";
        $user_params[] = $_POST['address'];
    }
    
    if (isset($_POST['emergency_contact_name'])) {
        $user_fields[] = "emergency_contact_name = ?";
        $user_params[] = $_POST['emergency_contact_name'];
    }
    
    if (isset($_POST['emergency_contact_phone'])) {
        $user_fields[] = "emergency_contact_phone = ?";
        $user_params[] = $_POST['emergency_contact_phone'];
    }
    
    if (!empty($user_fields)) {
        $user_params[] = $user_id;
        $user_query = "UPDATE users SET " . implode(', ', $user_fields) . " WHERE id = ?";
        $stmt = $pdo->prepare($user_query);
        $stmt->execute($user_params);
    }
    
    $pdo->commit();
    echo json_encode(['success' => true, 'message' => 'Details updated successfully']);
    
} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
}
?>