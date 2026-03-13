<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

require_once '../config/database.php';
$pdo = getConnection();

// Check if RBAC tables exist
$tables_needed = ['admin_roles', 'admin_permissions', 'admin_role_permissions'];
$tables_exist = [];
$tables_missing = [];

foreach ($tables_needed as $table) {
    try {
        $pdo->query("SELECT 1 FROM $table LIMIT 1");
        $tables_exist[] = $table;
    } catch (Exception $e) {
        $tables_missing[] = $table;
    }
}

$all_tables_exist = empty($tables_missing);

// Check if role_id column exists in admins table
$role_column_exists = false;
try {
    $pdo->query("SELECT role_id FROM admins LIMIT 1");
    $role_column_exists = true;
} catch (Exception $e) {
    $role_column_exists = false;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RBAC Setup Status</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #f8fafc; padding: 2rem; }
        .container { max-width: 800px; margin: 0 auto; }
        .card { background: white; border-radius: 12px; padding: 2rem; box-shadow: 0 2px 8px rgba(0,0,0,0.1); margin-bottom: 1.5rem; }
        h1 { color: #1e293b; margin-bottom: 0.5rem; }
        .status { display: flex; align-items: center; gap: 0.5rem; padding: 1rem; border-radius: 8px; margin: 1rem 0; }
        .status.success { background: #d1fae5; color: #065f46; }
        .status.error { background: #fee2e2; color: #991b1b; }
        .status.warning { background: #fef3c7; color: #92400e; }
        .table { width: 100%; border-collapse: collapse; margin: 1rem 0; }
        .table th { background: #f8fafc; padding: 0.75rem; text-align: left; font-weight: 600; border-bottom: 2px solid #e2e8f0; }
        .table td { padding: 0.75rem; border-bottom: 1px solid #e2e8f0; }
        .badge { padding: 0.25rem 0.75rem; border-radius: 20px; font-size: 0.85rem; font-weight: 500; }
        .badge.success { background: #d1fae5; color: #065f46; }
        .badge.error { background: #fee2e2; color: #991b1b; }
        .btn { display: inline-block; padding: 0.75rem 1.5rem; background: #3b82f6; color: white; text-decoration: none; border-radius: 8px; font-weight: 500; margin-top: 1rem; }
        .btn:hover { background: #2563eb; }
        .code-block { background: #1e293b; color: #e2e8f0; padding: 1rem; border-radius: 8px; overflow-x: auto; margin: 1rem 0; font-family: 'Courier New', monospace; font-size: 0.9rem; }
        .instructions { background: #f1f5f9; padding: 1rem; border-radius: 8px; margin: 1rem 0; }
        .instructions ol { margin-left: 1.5rem; }
        .instructions li { margin: 0.5rem 0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <h1><i class="fas fa-shield-alt"></i> RBAC System Setup Status</h1>
            <p style="color: #64748b; margin-top: 0.5rem;">Role-Based Access Control System Installation Check</p>
            
            <?php if ($all_tables_exist && $role_column_exists): ?>
                <div class="status success">
                    <i class="fas fa-check-circle"></i>
                    <strong>All RBAC tables are installed and ready!</strong>
                </div>
                <p>You can now access:</p>
                <ul style="margin: 1rem 0 1rem 2rem;">
                    <li><a href="roles.php">Roles & Permissions Management</a></li>
                    <li><a href="admin-users.php">Admin Users Management</a></li>
                </ul>
            <?php else: ?>
                <div class="status error">
                    <i class="fas fa-exclamation-circle"></i>
                    <strong>RBAC tables are not installed yet</strong>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="card">
            <h2 style="margin-bottom: 1rem;">Database Tables Status</h2>
            <table class="table">
                <thead>
                    <tr>
                        <th>Table Name</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>admins.role_id column</td>
                        <td>
                            <?php if ($role_column_exists): ?>
                                <span class="badge success"><i class="fas fa-check"></i> Exists</span>
                            <?php else: ?>
                                <span class="badge error"><i class="fas fa-times"></i> Missing</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php foreach ($tables_needed as $table): ?>
                        <tr>
                            <td><?= htmlspecialchars($table) ?></td>
                            <td>
                                <?php if (in_array($table, $tables_exist)): ?>
                                    <span class="badge success"><i class="fas fa-check"></i> Exists</span>
                                <?php else: ?>
                                    <span class="badge error"><i class="fas fa-times"></i> Missing</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <?php if (!$all_tables_exist || !$role_column_exists): ?>
        <div class="card">
            <h2 style="margin-bottom: 1rem;"><i class="fas fa-wrench"></i> Installation Instructions</h2>
            
            <div class="instructions">
                <h3 style="margin-bottom: 0.5rem;">Follow these steps:</h3>
                <ol>
                    <li>Open <strong>phpMyAdmin</strong> or your MySQL client</li>
                    <li>Select your database: <strong><?= $pdo->query("SELECT DATABASE()")->fetchColumn() ?></strong></li>
                    <li>Go to the <strong>SQL</strong> tab</li>
                    <li>Copy and paste the SQL code from: <code>database/roles_permissions.sql</code></li>
                    <li>Click <strong>Go</strong> to execute</li>
                    <li>Refresh this page to verify installation</li>
                </ol>
            </div>
            
            <p style="margin-top: 1rem;"><strong>SQL File Location:</strong></p>
            <div class="code-block">
                c:\xampp\htdocs\atoz\database\roles_permissions.sql
            </div>
            
            <p style="margin-top: 1rem; color: #64748b;">
                <i class="fas fa-info-circle"></i> 
                The SQL file will create all necessary tables and insert default roles with permissions.
            </p>
        </div>
        <?php endif; ?>
        
        <div class="card">
            <a href="dashboard.php" class="btn"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
        </div>
    </div>
</body>
</html>
