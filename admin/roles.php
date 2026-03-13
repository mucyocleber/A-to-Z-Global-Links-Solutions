<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

require_once '../config/database.php';
require_once 'includes/permissions.php';
requirePermission('view_roles');

$pdo = getConnection();
$error = null;

// Handle role creation/update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && hasPermission('manage_roles')) {
    $action = $_POST['action'] ?? '';
    
    try {
        if ($action === 'create_role') {
            $stmt = $pdo->prepare("INSERT INTO admin_roles (role_name, role_slug, description) VALUES (?, ?, ?)");
            $stmt->execute([$_POST['role_name'], $_POST['role_slug'], $_POST['description']]);
            header('Location: roles.php?success=role_created');
            exit;
        }
        
        if ($action === 'update_permissions') {
            $role_id = $_POST['role_id'];
            $permissions = $_POST['permissions'] ?? [];
            
            $pdo->prepare("DELETE FROM admin_role_permissions WHERE role_id = ?")->execute([$role_id]);
            
            $stmt = $pdo->prepare("INSERT INTO admin_role_permissions (role_id, permission_id) VALUES (?, ?)");
            foreach ($permissions as $perm_id) {
                $stmt->execute([$role_id, $perm_id]);
            }
            header('Location: roles.php?success=permissions_updated');
            exit;
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// Check for success message
if (isset($_GET['success'])) {
    if ($_GET['success'] === 'role_created') {
        $success = "Role created successfully";
    } elseif ($_GET['success'] === 'permissions_updated') {
        $success = "Permissions updated successfully";
    }
}

// Fetch roles
try {
    $roles = $pdo->query("SELECT * FROM admin_roles ORDER BY id")->fetchAll();
} catch (Exception $e) {
    $roles = [];
    $error = "RBAC tables not found. Please run the SQL setup file first. <a href='rbac-setup.php' style='color: #1e40af; text-decoration: underline;'>Check Setup Status</a>";
}

// Fetch permissions grouped by module
try {
    $permissions = $pdo->query("SELECT * FROM admin_permissions ORDER BY module, permission_name")->fetchAll();
    $grouped_permissions = [];
    foreach ($permissions as $perm) {
        $grouped_permissions[$perm['module']][] = $perm;
    }
} catch (Exception $e) {
    $grouped_permissions = [];
    if (!$error) $error = "RBAC tables not found. Please run the SQL setup file first. <a href='rbac-setup.php' style='color: #1e40af; text-decoration: underline;'>Check Setup Status</a>";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Roles & Permissions - Admin Panel</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/admin.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; }
        .main-content { margin-left: 280px; margin-top: 75px; padding: 2rem; min-height: calc(100vh - 75px); }
        
        .page-header { background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%); border-radius: 20px; padding: 2rem; margin-bottom: 2rem; box-shadow: 0 10px 40px rgba(59, 130, 246, 0.3); }
        .page-header h1 { color: white; font-size: 2rem; font-weight: 700; display: flex; align-items: center; gap: 1rem; margin-bottom: 0.5rem; }
        .page-header p { color: rgba(255,255,255,0.9); font-size: 1rem; }
        .page-header .stats { display: flex; gap: 2rem; margin-top: 1.5rem; }
        .page-header .stat-item { color: white; }
        .page-header .stat-number { font-size: 1.5rem; font-weight: 700; }
        .page-header .stat-label { font-size: 0.85rem; opacity: 0.9; }
        
        .alert { padding: 1rem 1.5rem; border-radius: 12px; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 1rem; animation: slideDown 0.3s ease; box-shadow: 0 4px 15px rgba(16, 185, 129, 0.2); }
        .alert-success { background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%); color: #065f46; border-left: 4px solid #10b981; }
        .alert-error { background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%); color: #991b1b; border-left: 4px solid #ef4444; }
        @keyframes slideDown { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }
        
        .card { background: white; border-radius: 20px; box-shadow: 0 10px 40px rgba(0,0,0,0.08); margin-bottom: 2rem; overflow: hidden; border: 1px solid rgba(59, 130, 246, 0.1); }
        .card-header { background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%); padding: 1.5rem 2rem; border-bottom: 2px solid #e2e8f0; display: flex; align-items: center; justify-content: space-between; }
        .card-header h3 { color: #1e293b; font-size: 1.25rem; font-weight: 700; display: flex; align-items: center; gap: 0.75rem; }
        .card-header .icon { width: 40px; height: 40px; background: linear-gradient(135deg, #3b82f6, #1e40af); border-radius: 10px; display: flex; align-items: center; justify-content: center; color: white; }
        .card-body { padding: 2rem; }
        
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem; }
        .form-group { display: flex; flex-direction: column; gap: 0.5rem; }
        .form-group.full { grid-column: 1 / -1; }
        .form-label { font-weight: 600; color: #334155; font-size: 0.9rem; display: flex; align-items: center; gap: 0.5rem; }
        .form-label i { color: #3b82f6; }
        .form-control { padding: 0.75rem 1rem; border: 2px solid #e2e8f0; border-radius: 10px; font-size: 0.95rem; transition: all 0.3s; background: #f8fafc; }
        .form-control:focus { outline: none; border-color: #3b82f6; background: white; box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1); }
        textarea.form-control { resize: vertical; min-height: 80px; }
        
        .btn { padding: 0.75rem 2rem; border-radius: 10px; border: none; cursor: pointer; font-weight: 600; font-size: 0.95rem; transition: all 0.3s; display: inline-flex; align-items: center; gap: 0.5rem; }
        .btn-primary { background: linear-gradient(135deg, #3b82f6, #1e40af); color: white; box-shadow: 0 4px 15px rgba(59, 130, 246, 0.3); }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(59, 130, 246, 0.4); }
        .btn-success { background: linear-gradient(135deg, #10b981, #059669); color: white; box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3); }
        .btn-success:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(16, 185, 129, 0.4); }
        
        .role-card { background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%); border: 2px solid #e2e8f0; border-radius: 16px; padding: 2rem; margin-bottom: 1.5rem; transition: all 0.3s; }
        .role-card:hover { transform: translateY(-4px); box-shadow: 0 10px 30px rgba(59, 130, 246, 0.15); border-color: #3b82f6; }
        .role-header { display: flex; align-items: center; gap: 1rem; margin-bottom: 1.5rem; padding-bottom: 1rem; border-bottom: 2px solid #e2e8f0; }
        .role-icon { width: 50px; height: 50px; border-radius: 12px; background: linear-gradient(135deg, #3b82f6, #1e40af); display: flex; align-items: center; justify-content: center; color: white; font-size: 1.5rem; box-shadow: 0 4px 15px rgba(59, 130, 246, 0.3); }
        .role-info { flex: 1; }
        .role-name { font-size: 1.3rem; font-weight: 700; color: #1e293b; margin-bottom: 0.25rem; }
        .role-desc { color: #64748b; font-size: 0.95rem; }
        
        .module-section { margin-bottom: 2rem; }
        .module-title { font-weight: 700; color: #1e293b; font-size: 1.1rem; margin-bottom: 1rem; padding: 0.75rem 1rem; background: linear-gradient(135deg, #dbeafe, #bfdbfe); border-radius: 10px; display: flex; align-items: center; gap: 0.5rem; }
        .module-title i { color: #3b82f6; }
        .permission-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 0.75rem; }
        .permission-item { display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem 1rem; background: white; border: 2px solid #e2e8f0; border-radius: 10px; transition: all 0.3s; cursor: pointer; }
        .permission-item:hover { border-color: #3b82f6; background: #f8fafc; }
        .permission-item input[type="checkbox"] { width: 20px; height: 20px; cursor: pointer; accent-color: #3b82f6; }
        .permission-item label { cursor: pointer; flex: 1; font-size: 0.9rem; color: #334155; font-weight: 500; }
        .permission-check { color: #10b981; font-size: 1.2rem; }
        
        .empty-state { text-align: center; padding: 3rem; color: #94a3b8; }
        .empty-state i { font-size: 3rem; margin-bottom: 1rem; opacity: 0.5; }
        
        @media (max-width: 768px) {
            .main-content { margin-left: 0; padding: 1rem; }
            .form-row { grid-template-columns: 1fr; }
            .permission-grid { grid-template-columns: 1fr; }
            .page-header .stats { flex-direction: column; gap: 1rem; }
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    <?php include 'includes/sidebar.php'; ?>
    
    <main class="main-content">
        <div class="page-header">
            <h1><i class="fas fa-shield-alt"></i> Roles & Permissions Management</h1>
            <p>Create roles and assign granular permissions to control admin access</p>
            <div class="stats">
                <div class="stat-item">
                    <div class="stat-number"><?= count($roles) ?></div>
                    <div class="stat-label">Total Roles</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number"><?= count($grouped_permissions) ?></div>
                    <div class="stat-label">Permission Modules</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number"><?= array_sum(array_map('count', $grouped_permissions)) ?></div>
                    <div class="stat-label">Total Permissions</div>
                </div>
            </div>
        </div>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle" style="font-size: 1.5rem;"></i>
                <span><?= htmlspecialchars($error) ?></span>
            </div>
        <?php endif; ?>
        
        <?php if (isset($success)): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle" style="font-size: 1.5rem;"></i>
                <span><?= htmlspecialchars($success) ?></span>
            </div>
        <?php endif; ?>
        
        <?php if (hasPermission('manage_roles')): ?>
        <div class="card">
            <div class="card-header">
                <h3>
                    <span class="icon"><i class="fas fa-plus-circle"></i></span>
                    Create New Role
                </h3>
            </div>
            <div class="card-body">
                <form method="POST" style="max-width: 700px;">
                    <input type="hidden" name="action" value="create_role">
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label"><i class="fas fa-tag"></i> Role Name</label>
                            <input type="text" name="role_name" placeholder="e.g., Content Manager" required class="form-control">
                        </div>
                        <div class="form-group">
                            <label class="form-label"><i class="fas fa-code"></i> Role Slug</label>
                            <input type="text" name="role_slug" placeholder="e.g., content_manager" required class="form-control">
                        </div>
                    </div>
                    <div class="form-group full">
                        <label class="form-label"><i class="fas fa-align-left"></i> Description</label>
                        <textarea name="description" placeholder="Describe the role and its responsibilities..." class="form-control"></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-plus-circle"></i> Create Role
                    </button>
                </form>
            </div>
        </div>
        <?php endif; ?>
        
        <?php if (empty($roles)): ?>
            <div class="card">
                <div class="card-body">
                    <div class="empty-state">
                        <i class="fas fa-user-shield"></i>
                        <p>No roles found. Create your first role above.</p>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <?php foreach ($roles as $role): ?>
                <?php
                $role_perms = $pdo->prepare("SELECT permission_id FROM admin_role_permissions WHERE role_id = ?");
                $role_perms->execute([$role['id']]);
                $assigned_perms = $role_perms->fetchAll(PDO::FETCH_COLUMN);
                ?>
                <div class="role-card">
                    <div class="role-header">
                        <div class="role-icon"><i class="fas fa-shield-alt"></i></div>
                        <div class="role-info">
                            <div class="role-name"><?= htmlspecialchars($role['role_name']) ?></div>
                            <div class="role-desc"><?= htmlspecialchars($role['description']) ?></div>
                        </div>
                        <div style="color: #64748b; font-size: 0.9rem;">
                            <i class="fas fa-check-circle"></i> <?= count($assigned_perms) ?> permissions
                        </div>
                    </div>
                    
                    <?php if (hasPermission('manage_roles')): ?>
                    <form method="POST">
                        <input type="hidden" name="action" value="update_permissions">
                        <input type="hidden" name="role_id" value="<?= $role['id'] ?>">
                        
                        <?php foreach ($grouped_permissions as $module => $perms): ?>
                            <div class="module-section">
                                <div class="module-title">
                                    <i class="fas fa-folder"></i>
                                    <?= ucwords(str_replace('_', ' ', $module)) ?>
                                </div>
                                <div class="permission-grid">
                                    <?php foreach ($perms as $perm): ?>
                                        <div class="permission-item">
                                            <input type="checkbox" 
                                                   id="perm_<?= $role['id'] ?>_<?= $perm['id'] ?>" 
                                                   name="permissions[]" 
                                                   value="<?= $perm['id'] ?>" 
                                                   <?= in_array($perm['id'], $assigned_perms) ? 'checked' : '' ?>>
                                            <label for="perm_<?= $role['id'] ?>_<?= $perm['id'] ?>">
                                                <?= htmlspecialchars($perm['permission_name']) ?>
                                            </label>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-save"></i> Update Permissions
                        </button>
                    </form>
                    <?php else: ?>
                        <?php foreach ($grouped_permissions as $module => $perms): ?>
                            <?php $module_has_perms = false; ?>
                            <?php foreach ($perms as $perm): ?>
                                <?php if (in_array($perm['id'], $assigned_perms)) { $module_has_perms = true; break; } ?>
                            <?php endforeach; ?>
                            
                            <?php if ($module_has_perms): ?>
                                <div class="module-section">
                                    <div class="module-title">
                                        <i class="fas fa-folder"></i>
                                        <?= ucwords(str_replace('_', ' ', $module)) ?>
                                    </div>
                                    <div class="permission-grid">
                                        <?php foreach ($perms as $perm): ?>
                                            <?php if (in_array($perm['id'], $assigned_perms)): ?>
                                                <div class="permission-item" style="border-color: #10b981; background: #f0fdf4;">
                                                    <i class="fas fa-check-circle permission-check"></i>
                                                    <span style="color: #065f46; font-weight: 600;"><?= htmlspecialchars($perm['permission_name']) ?></span>
                                                </div>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </main>
</body>
</html>
