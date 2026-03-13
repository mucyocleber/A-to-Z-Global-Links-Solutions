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
    
    // Validate required fields
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $nationality = trim($_POST['nationality'] ?? '');
    $country = trim($_POST['country'] ?? '');
    $gender = trim($_POST['gender'] ?? '');
    $date_of_birth = !empty($_POST['date_of_birth']) ? $_POST['date_of_birth'] : null;
    $passport_number = trim($_POST['passport_number'] ?? '');
    $address = trim($_POST['address'] ?? '');
    
    if (empty($first_name) || empty($last_name) || empty($email)) {
        echo json_encode(['error' => 'First name, last name, and email are required']);
        exit;
    }
    
    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['error' => 'Invalid email format']);
        exit;
    }
    
    // Check if email already exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        echo json_encode(['error' => 'Email already exists']);
        exit;
    }
    
    // Generate a temporary password
    $temp_password = bin2hex(random_bytes(8));
    $password_hash = password_hash($temp_password, PASSWORD_DEFAULT);
    
    // Handle profile image upload
    $profile_picture = null;
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../uploads/profiles/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        $file_extension = strtolower(pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION));
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
        
        if (in_array($file_extension, $allowed_extensions)) {
            $profile_picture = 'profile_' . time() . '_' . rand(1000, 9999) . '.' . $file_extension;
            $upload_path = $upload_dir . $profile_picture;
            
            if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $upload_path)) {
                // Image uploaded successfully
            } else {
                $profile_picture = null;
            }
        }
    }
    
    // Insert user
    $stmt = $pdo->prepare("
        INSERT INTO users (
            first_name, last_name, email, phone, nationality, country, 
            gender, date_of_birth, passport_number, address, profile_picture, 
            password_hash, is_active, email_verified, created_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, 1, NOW())
    ");
    
    $stmt->execute([
        $first_name,
        $last_name,
        $email,
        $phone ?: null,
        $nationality ?: null,
        $country ?: null,
        $gender ?: null,
        $date_of_birth,
        $passport_number ?: null,
        $address ?: null,
        $profile_picture,
        $password_hash
    ]);
    
    $user_id = $pdo->lastInsertId();
    
    echo json_encode([
        'success' => true,
        'message' => 'User created successfully',
        'user_id' => $user_id,
        'temp_password' => $temp_password
    ]);
    
} catch (Exception $e) {
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>