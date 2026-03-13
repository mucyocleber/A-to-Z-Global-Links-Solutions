<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

require_once '../config/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Invalid request method']);
    exit;
}

try {
    $pdo = getConnection();
    
    $user_id = (int)$_POST['user_id'];
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $country = trim($_POST['country'] ?? '');
    $date_of_birth = !empty($_POST['date_of_birth']) ? $_POST['date_of_birth'] : null;
    
    if (!$user_id || empty($first_name) || empty($last_name) || empty($email)) {
        echo json_encode(['error' => 'Missing required fields']);
        exit;
    }
    
    // Check if email exists for other users
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
    $stmt->execute([$email, $user_id]);
    if ($stmt->fetch()) {
        echo json_encode(['error' => 'Email already exists']);
        exit;
    }
    
    // Handle profile image upload
    $profile_picture = null;
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../uploads/profiles/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        $file_extension = strtolower(pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION));
        $allowed_extensions = ['jpg', 'jpeg', 'png'];
        
        if (in_array($file_extension, $allowed_extensions)) {
            $profile_picture = uniqid() . '.' . $file_extension;
            $upload_path = $upload_dir . $profile_picture;
            
            if (!move_uploaded_file($_FILES['profile_image']['tmp_name'], $upload_path)) {
                echo json_encode(['error' => 'Failed to upload profile image']);
                exit;
            }
        } else {
            echo json_encode(['error' => 'Invalid image format']);
            exit;
        }
    }
    
    // Update user
    if ($profile_picture) {
        $stmt = $pdo->prepare("
            UPDATE users SET 
                first_name = ?, last_name = ?, email = ?, phone = ?, 
                country = ?, date_of_birth = ?, profile_picture = ?
            WHERE id = ?
        ");
        $stmt->execute([
            $first_name, $last_name, $email, $phone, 
            $country, $date_of_birth, $profile_picture, $user_id
        ]);
    } else {
        $stmt = $pdo->prepare("
            UPDATE users SET 
                first_name = ?, last_name = ?, email = ?, phone = ?, 
                country = ?, date_of_birth = ?
            WHERE id = ?
        ");
        $stmt->execute([
            $first_name, $last_name, $email, $phone, 
            $country, $date_of_birth, $user_id
        ]);
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'User updated successfully'
    ]);
    
} catch (Exception $e) {
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>