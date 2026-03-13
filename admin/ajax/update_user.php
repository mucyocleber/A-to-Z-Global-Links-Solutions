<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

require_once '../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
    exit;
}

try {
    $pdo = getConnection();
    
    $userId = $_POST['user_id'] ?? null;
    if (!$userId) {
        echo json_encode(['success' => false, 'error' => 'User ID required']);
        exit;
    }
    
    // Handle profile image upload
    $profileImage = null;
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../../uploads/profiles/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $fileExtension = strtolower(pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION));
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
        
        if (in_array($fileExtension, $allowedExtensions)) {
            $profileImage = 'profile_' . $userId . '_' . time() . '.' . $fileExtension;
            $uploadPath = $uploadDir . $profileImage;
            
            if (!move_uploaded_file($_FILES['profile_image']['tmp_name'], $uploadPath)) {
                echo json_encode(['success' => false, 'error' => 'Failed to upload image']);
                exit;
            }
        }
    }
    
    // Update user data
    $updateFields = [
        'first_name = ?',
        'last_name = ?',
        'email = ?',
        'phone = ?',
        'country = ?',
        'date_of_birth = ?'
    ];
    
    $params = [
        $_POST['first_name'],
        $_POST['last_name'],
        $_POST['email'],
        $_POST['phone'] ?: null,
        $_POST['country'] ?: null,
        $_POST['date_of_birth'] ?: null
    ];
    
    if ($profileImage) {
        $updateFields[] = 'profile_picture = ?';
        $params[] = $profileImage;
    }
    
    $params[] = $userId;
    
    $sql = "UPDATE users SET " . implode(', ', $updateFields) . " WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    
    echo json_encode(['success' => true, 'message' => 'User updated successfully']);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
}
?>