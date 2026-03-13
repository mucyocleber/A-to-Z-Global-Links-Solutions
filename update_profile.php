<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Session expired']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
    exit;
}

require_once 'config/database.php';

try {
    // Server-side validation for required fields
    $required_fields = ['first_name', 'last_name', 'email', 'date_of_birth', 'country'];
    $missing_fields = [];
    
    foreach ($required_fields as $field) {
        if (empty($_POST[$field]) || trim($_POST[$field]) === '') {
            $missing_fields[] = $field;
        }
    }
    
    if (!empty($missing_fields)) {
        echo json_encode([
            'success' => false, 
            'error' => 'Required fields missing: ' . implode(', ', $missing_fields)
        ]);
        exit;
    }
    
    // Validate email format
    if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'error' => 'Invalid email format']);
        exit;
    }
    
    $pdo = getConnection();
    
    // Handle empty values properly
    $phone = !empty($_POST['phone']) ? $_POST['phone'] : null;
    $gender = !empty($_POST['gender']) ? $_POST['gender'] : null;
    $nationality = !empty($_POST['nationality']) ? $_POST['nationality'] : null;
    $address = !empty($_POST['address']) ? $_POST['address'] : null;
    $city = !empty($_POST['city']) ? $_POST['city'] : null;
    $postal_code = !empty($_POST['postal_code']) ? $_POST['postal_code'] : null;
    $passport_number = !empty($_POST['passport_number']) ? $_POST['passport_number'] : null;
    $passport_expiry = !empty($_POST['passport_expiry']) ? $_POST['passport_expiry'] : null;
    $occupation = !empty($_POST['occupation']) ? $_POST['occupation'] : null;
    $emergency_contact_name = !empty($_POST['emergency_contact_name']) ? $_POST['emergency_contact_name'] : null;
    $emergency_contact_phone = !empty($_POST['emergency_contact_phone']) ? $_POST['emergency_contact_phone'] : null;
    
    $stmt = $pdo->prepare("
        UPDATE users SET 
            first_name = ?, last_name = ?, email = ?, phone = ?, 
            date_of_birth = ?, gender = ?, country = ?, nationality = ?, 
            address = ?, city = ?, postal_code = ?, passport_number = ?, 
            passport_expiry = ?, occupation = ?, emergency_contact_name = ?, 
            emergency_contact_phone = ?
        WHERE id = ?
    ");
    
    $result = $stmt->execute([
        $_POST['first_name'], $_POST['last_name'], $_POST['email'], $phone,
        $_POST['date_of_birth'], $gender, $_POST['country'], $nationality,
        $address, $city, $postal_code, $passport_number,
        $passport_expiry, $occupation, $emergency_contact_name,
        $emergency_contact_phone, $_SESSION['user_id']
    ]);
    
    if ($result) {
        // Verify the update actually happened
        if ($stmt->rowCount() > 0) {
            echo json_encode(['success' => true, 'message' => 'Profile updated successfully']);
        } else {
            echo json_encode(['success' => false, 'error' => 'No changes were made']);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to update profile']);
    }
    
} catch (Exception $e) {
    error_log('Profile update error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Failed to update profile. Please try again.']);
}
?>