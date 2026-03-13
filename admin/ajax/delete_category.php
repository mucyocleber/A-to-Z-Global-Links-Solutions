<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
    header('Location: ../index.php');
    exit;
}

require_once '../../config/database.php';

if ($_POST && isset($_POST['id'])) {
    $id = $_POST['id'];
    
    try {
        $pdo = getConnection();
        
        // Check if category has services
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM services WHERE category_id = ?");
        $stmt->execute([$id]);
        $serviceCount = $stmt->fetchColumn();
        
        if ($serviceCount > 0) {
            $_SESSION['error'] = 'Cannot delete category with existing services';
        } else {
            $stmt = $pdo->prepare("DELETE FROM service_categories WHERE id = ?");
            $stmt->execute([$id]);
            $_SESSION['success'] = 'Category deleted successfully';
        }
        
    } catch (Exception $e) {
        $_SESSION['error'] = 'Error deleting category: ' . $e->getMessage();
    }
}

header('Location: ../service-categories.php');
exit;
?>