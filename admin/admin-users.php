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

// Handle role assignment
if ($_SERVER['REQUEST_METHOD'] === 'POST' && hasPermission('manage_roles')) {
    $action = $_POST['action'] ?? '';
    
    try {
        if ($action === 'assign_role') {
            $role_id = $_POST['role_id'] ?: null;
            $stmt = $pdo->prepare("UPDATE admins SET role_id = ? WHERE id = ?");
            $stmt->execute([$role_id, $_POST['admin_id']]);
            $success = "Role assigned successfully";
            header('Location: admin-users.php?success=role_assigned');
            exit;
        }
        
        if ($action === 'create_admin') {
            // First check what columns exist in admins table
            $columns = $pdo->query("SHOW COLUMNS FROM admins")->fetchAll(PDO::FETCH_COLUMN);
            
            $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $role_id = $_POST['role_id'] ?: null;
            
            // Build INSERT query based on available columns
            if (in_array('password', $columns)) {
                $stmt = $pdo->prepare("INSERT INTO admins (username, email, password, role_id) VALUES (?, ?, ?, ?)");
                $stmt->execute([$_POST['username'], $_POST['email'], $password, $role_id]);
            } elseif (in_array('password_hash', $columns)) {
                $stmt = $pdo->prepare("INSERT INTO admins (username, email, password_hash, role_id) VALUES (?, ?, ?, ?)");
                $stmt->execute([$_POST['username'], $_POST['email'], $password, $role_id]);
            } else {
                // No password column, insert without it
                $stmt = $pdo->prepare("INSERT INTO admins (username, email, role_id) VALUES (?, ?, ?)");
                $stmt->execute([$_POST['username'], $_POST['email'], $role_id]);
            }
            
            $success = "Admin user created successfully";
            header('Location: admin-users.php?success=admin_created');
            exit;
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// Check for success message
if (isset($_GET['success'])) {
    if ($_GET['success'] === 'admin_created') {
        $success = "Admin user created successfully";
    } elseif ($_GET['success'] === 'role_assigned') {
        $success = "Role assigned successfully";
    }
}

// Fetch admins with roles
try {
    $admins = $pdo->query("
        SELECT a.*, ar.role_name, ar.role_slug 
        FROM admins a
        LEFT JOIN admin_roles ar ON a.role_id = ar.id
        ORDER BY a.id
    ")->fetchAll();
} catch (Exception $e) {
    $admins = [];
    $error = "Error fetching admins: " . $e->getMessage();
}

// Fetch all roles
try {
    $roles = $pdo->query("SELECT * FROM admin_roles WHERE is_active = 1 ORDER BY role_name")->fetchAll();
} catch (Exception $e) {
    $roles = [];
    if (!$error) $error = "Error fetching roles: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Users - Admin Panel</title>
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
        @keyframes slideDown { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }
        
        .card { background: white; border-radius: 20px; box-shadow: 0 10px 40px rgba(0,0,0,0.08); margin-bottom: 2rem; overflow: hidden; border: 1px solid rgba(59, 130, 246, 0.1); }
        .card-header { background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%); padding: 1.5rem 2rem; border-bottom: 2px solid #e2e8f0; display: flex; align-items: center; justify-content: space-between; }
        .card-header h3 { color: #1e293b; font-size: 1.25rem; font-weight: 700; display: flex; align-items: center; gap: 0.75rem; }
        .card-header .icon { width: 40px; height: 40px; background: linear-gradient(135deg, #3b82f6, #1e40af); border-radius: 10px; display: flex; align-items: center; justify-content: center; color: white; }
        .card-body { padding: 2rem; }
        
        .form-container { max-width: 700px; }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem; }
        .form-group { display: flex; flex-direction: column; gap: 0.5rem; }
        .form-group.full { grid-column: 1 / -1; }
        .form-label { font-weight: 600; color: #334155; font-size: 0.9rem; display: flex; align-items: center; gap: 0.5rem; }
        .form-label i { color: #3b82f6; }
        .form-control { padding: 0.75rem 1rem; border: 2px solid #e2e8f0; border-radius: 10px; font-size: 0.95rem; transition: all 0.3s; background: #f8fafc; }
        .form-control:focus { outline: none; border-color: #3b82f6; background: white; box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1); }
        .form-control::placeholder { color: #94a3b8; }
        
        .btn { padding: 0.75rem 2rem; border-radius: 10px; border: none; cursor: pointer; font-weight: 600; font-size: 0.95rem; transition: all 0.3s; display: inline-flex; align-items: center; gap: 0.5rem; }
        .btn-primary { background: linear-gradient(135deg, #3b82f6, #1e40af); color: white; box-shadow: 0 4px 15px rgba(59, 130, 246, 0.3); }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(59, 130, 246, 0.4); }
        .btn-sm { padding: 0.5rem 1rem; font-size: 0.85rem; }
        
        .users-grid { display: grid; gap: 1.5rem; }
        .user-card { background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%); border: 2px solid #e2e8f0; border-radius: 16px; padding: 1.5rem; display: flex; align-items: center; gap: 1.5rem; transition: all 0.3s; }
        .user-card:hover { transform: translateY(-4px); box-shadow: 0 10px 30px rgba(59, 130, 246, 0.15); border-color: #3b82f6; }
        .user-avatar { width: 60px; height: 60px; border-radius: 50%; background: linear-gradient(135deg, #3b82f6, #1e40af); display: flex; align-items: center; justify-content: center; color: white; font-size: 1.5rem; font-weight: 700; box-shadow: 0 4px 15px rgba(59, 130, 246, 0.3); }
        .user-info { flex: 1; }
        .user-name { font-size: 1.1rem; font-weight: 700; color: #1e293b; margin-bottom: 0.25rem; }
        .user-email { color: #64748b; font-size: 0.9rem; display: flex; align-items: center; gap: 0.5rem; }
        .user-id { color: #94a3b8; font-size: 0.8rem; font-weight: 600; }
        .user-role { display: flex; align-items: center; gap: 1rem; }
        
        .role-badge { padding: 0.5rem 1rem; border-radius: 25px; font-size: 0.85rem; font-weight: 600; display: inline-flex; align-items: center; gap: 0.5rem; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        .role-super_admin { background: linear-gradient(135deg, #fef3c7, #fde68a); color: #92400e; }
        .role-manager { background: linear-gradient(135deg, #dbeafe, #bfdbfe); color: #1e40af; }
        .role-support_staff { background: linear-gradient(135deg, #d1fae5, #a7f3d0); color: #065f46; }
        .role-accountant { background: linear-gradient(135deg, #fce7f3, #fbcfe8); color: #9f1239; }
        .role-viewer { background: linear-gradient(135deg, #e0e7ff, #c7d2fe); color: #3730a3; }
        .role-none { background: #f1f5f9; color: #64748b; }
        
        .role-form { display: flex; gap: 0.75rem; align-items: center; }
        .role-select { padding: 0.5rem 1rem; border: 2px solid #e2e8f0; border-radius: 10px; font-size: 0.9rem; background: white; cursor: pointer; min-width: 180px; }
        .role-select:focus { outline: none; border-color: #3b82f6; }
        
        .empty-state { text-align: center; padding: 3rem; color: #94a3b8; }
        .empty-state i { font-size: 3rem; margin-bottom: 1rem; opacity: 0.5; }
        
        @media (max-width: 768px) {
            .main-content { margin-left: 0; padding: 1rem; }
            .form-row { grid-template-columns: 1fr; }
            .user-card { flex-direction: column; text-align: center; }
            .role-form { flex-direction: column; width: 100%; }
            .role-select { width: 100%; }
            .page-header .stats { flex-direction: column; gap: 1rem; }
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    <?php include 'includes/sidebar.php'; ?>
    
    <main class="main-content">
        <div class="page-header">
            <h1><i class="fas fa-users-cog"></i> Admin Users Management</h1>
            <p>Manage administrator accounts and assign roles with specific permissions</p>
            <div class="stats">
                <div class="stat-item">
                    <div class="stat-number"><?= count($admins) ?></div>
                    <div class="stat-label">Total Admins</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number"><?= count(array_filter($admins, fn($a) => $a['role_id'])) ?></div>
                    <div class="stat-label">With Roles</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number"><?= count($roles) ?></div>
                    <div class="stat-label">Available Roles</div>
                </div>
            </div>
        </div>
        
        <?php if (isset($error)): ?>
            <div class="alert" style="background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%); color: #991b1b; border-left: 4px solid #ef4444;">
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
                    <span class="icon"><i class="fas fa-user-plus"></i></span>
                    Create New Admin User
                </h3>
            </div>
            <div class="card-body">
                <form method="POST" class="form-container">
                    <input type="hidden" name="action" value="create_admin">
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label"><i class="fas fa-user"></i> Username</label>
                            <input type="text" name="username" placeholder="Enter username" required class="form-control">
                        </div>
                        <div class="form-group">
                            <label class="form-label"><i class="fas fa-envelope"></i> Email Address</label>
                            <input type="email" name="email" placeholder="admin@example.com" required class="form-control">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label"><i class="fas fa-lock"></i> Password</label>
                            <input type="password" name="password" placeholder="Enter secure password" required class="form-control">
                        </div>
                        <div class="form-group">
                            <label class="form-label"><i class="fas fa-shield-alt"></i> Assign Role</label>
                            <select name="role_id" required class="form-control">
                                <option value="">Select Role</option>
                                <?php foreach ($roles as $role): ?>
                                    <option value="<?= $role['id'] ?>"><?= htmlspecialchars($role['role_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-plus-circle"></i> Create Admin User
                    </button>
                </form>
            </div>
        </div>
        <?php endif; ?>
        
        <div class="card">
            <div class="card-header">
                <h3>
                    <span class="icon"><i class="fas fa-users"></i></span>
                    Admin Users (<?= count($admins) ?>)
                </h3>
            </div>
            <div class="card-body">
                <?php if (empty($admins)): ?>
                    <div class="empty-state">
                        <i class="fas fa-user-slash"></i>
                        <p>No admin users found</p>
                    </div>
                <?php else: ?>
                    <div class="users-grid">
                        <?php foreach ($admins as $admin): ?>
                        <div class="user-card">
                            <div class="user-avatar"><?= strtoupper(substr($admin['username'], 0, 2)) ?></div>
                            <div class="user-info">
                                <div class="user-name"><?= htmlspecialchars($admin['username']) ?></div>
                                <div class="user-email">
                                    <i class="fas fa-envelope"></i>
                                    <?= htmlspecialchars($admin['email']) ?>
                                </div>
                                <div class="user-id">ID: #<?= $admin['id'] ?></div>
                            </div>
                            <div class="user-role">
                                <?php if ($admin['role_name']): ?>
                                    <span class="role-badge role-<?= $admin['role_slug'] ?>">
                                        <i class="fas fa-shield-alt"></i>
                                        <?= htmlspecialchars($admin['role_name']) ?>
                                    </span>
                                <?php else: ?>
                                    <span class="role-badge role-none">
                                        <i class="fas fa-user-slash"></i>
                                        No Role Assigned
                                    </span>
                                <?php endif; ?>
                                
                                <?php if (hasPermission('manage_roles')): ?>
                                <form method="POST" class="role-form">
                                    <input type="hidden" name="action" value="assign_role">
                                    <input type="hidden" name="admin_id" value="<?= $admin['id'] ?>">
                                    <select name="role_id" class="role-select">
                                        <option value="">No Role</option>
                                        <?php foreach ($roles as $role): ?>
                                            <option value="<?= $role['id'] ?>" <?= $admin['role_id'] == $role['id'] ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($role['role_name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <button type="submit" class="btn btn-primary btn-sm">
                                        <i class="fas fa-save"></i> Update
                                    </button>
                                </form>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>
</body>
</html>
