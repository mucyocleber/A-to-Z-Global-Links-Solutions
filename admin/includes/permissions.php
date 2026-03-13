<?php
require_once __DIR__ . '/../../config/database.php';

function hasPermission($permission_slug) {
    if (!isset($_SESSION['admin_id'])) return false;
    
    try {
        $pdo = getConnection();
        $stmt = $pdo->prepare("
            SELECT COUNT(*) 
            FROM admin_role_permissions arp
            JOIN admin_permissions ap ON arp.permission_id = ap.id
            JOIN admins a ON a.role_id = arp.role_id
            WHERE a.id = ? AND ap.permission_slug = ? AND a.role_id IS NOT NULL
        ");
        $stmt->execute([$_SESSION['admin_id'], $permission_slug]);
        return $stmt->fetchColumn() > 0;
    } catch (Exception $e) {
        // If tables don't exist, deny access (change to false)
        return false;
    }
}

function hasAnyPermission($permission_slugs) {
    foreach ($permission_slugs as $slug) {
        if (hasPermission($slug)) return true;
    }
    return false;
}

function requirePermission($permission_slug) {
    if (!hasPermission($permission_slug)) {
        header('Location: dashboard.php?error=access_denied');
        exit;
    }
}

function getAdminRole() {
    if (!isset($_SESSION['admin_id'])) return null;
    
    try {
        $pdo = getConnection();
        $stmt = $pdo->prepare("
            SELECT ar.role_name, ar.role_slug 
            FROM admins a
            JOIN admin_roles ar ON a.role_id = ar.id
            WHERE a.id = ?
        ");
        $stmt->execute([$_SESSION['admin_id']]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        return null;
    }
}

function isSuperAdmin() {
    $role = getAdminRole();
    return $role && $role['role_slug'] === 'super_admin';
}
