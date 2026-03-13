<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
    $_SESSION['error'] = 'Unauthorized access';
    header('Location: ../service-categories.php');
    exit;
}

require_once '../../config/database.php';

try {
    $pdo = getConnection();
    
    if (isset($_POST['category_id']) && !empty($_POST['category_id'])) {
        // Update existing category
        $stmt = $pdo->prepare("UPDATE service_categories SET name = ?, description = ?, sort_order = ?, is_active = ? WHERE id = ?");
        $stmt->execute([
            $_POST['name'],
            $_POST['description'],
            $_POST['sort_order'],
            $_POST['is_active'],
            $_POST['category_id']
        ]);
        $_SESSION['success'] = 'Category updated successfully!';
    } else {
        // Create new category
        $stmt = $pdo->prepare("INSERT INTO service_categories (name, description, sort_order, is_active) VALUES (?, ?, ?, ?)");
        $stmt->execute([
            $_POST['name'],
            $_POST['description'],
            $_POST['sort_order'],
            $_POST['is_active']
        ]);
        $_SESSION['success'] = 'Category added successfully!';
    }
    
} catch (Exception $e) {
    $_SESSION['error'] = 'Error: ' . $e->getMessage();
}

header('Location: ../service-categories.php');
exit;
?>