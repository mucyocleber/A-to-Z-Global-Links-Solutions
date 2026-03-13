<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

require_once '../../config/database.php';

try {
    $pdo = getConnection();
    
    if (isset($_POST['country_id']) && !empty($_POST['country_id'])) {
        // Update existing country
        $stmt = $pdo->prepare("UPDATE countries SET name = ?, iso_code_2 = ?, iso_code_3 = ?, phone_code = ?, currency_code = ?, region = ?, visa_required_for_rwanda = ?, is_active = ? WHERE id = ?");
        $stmt->execute([
            $_POST['name'],
            $_POST['iso_code_2'],
            $_POST['iso_code_3'],
            $_POST['phone_code'],
            $_POST['currency_code'],
            $_POST['region'],
            $_POST['visa_required_for_rwanda'],
            $_POST['is_active'],
            $_POST['country_id']
        ]);
    } else {
        // Create new country
        $stmt = $pdo->prepare("INSERT INTO countries (name, iso_code_2, iso_code_3, phone_code, currency_code, region, visa_required_for_rwanda, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $_POST['name'],
            $_POST['iso_code_2'],
            $_POST['iso_code_3'],
            $_POST['phone_code'],
            $_POST['currency_code'],
            $_POST['region'],
            $_POST['visa_required_for_rwanda'],
            $_POST['is_active']
        ]);
    }
    
    echo json_encode(['success' => true]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>