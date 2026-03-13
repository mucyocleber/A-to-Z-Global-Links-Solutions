<?php
/**
 * PERMISSION IMPLEMENTATION GUIDE
 * Copy this template to add permissions to any admin page
 */

// ============================================
// STEP 1: Add at the TOP of every admin page
// ============================================
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

require_once '../config/database.php';
require_once 'includes/permissions.php';

// ============================================
// STEP 2: Require permission for the page
// ============================================

// For Applications page:
// requirePermission('view_applications');

// For Users page:
// requirePermission('view_users');

// For Services page:
// requirePermission('view_services');

// For Categories page:
// requirePermission('view_categories');

// For Countries page:
// requirePermission('view_countries');

// For Destinations page:
// requirePermission('view_destinations');

// For Dashboard (usually everyone can access):
// requirePermission('view_dashboard');

// ============================================
// STEP 3: Check permissions for actions
// ============================================

// Example for form submissions:
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    // Check create permission
    if ($action === 'create' && !hasPermission('create_applications')) {
        die('Access denied: You do not have permission to create applications');
    }
    
    // Check edit permission
    if ($action === 'edit' && !hasPermission('edit_applications')) {
        die('Access denied: You do not have permission to edit applications');
    }
    
    // Check delete permission
    if ($action === 'delete' && !hasPermission('delete_applications')) {
        die('Access denied: You do not have permission to delete applications');
    }
}

// ============================================
// STEP 4: Hide UI elements based on permissions
// ============================================
?>

<!-- Example: Hide create button if no permission -->
<?php if (hasPermission('create_applications')): ?>
    <button class="btn-create">Create New Application</button>
<?php endif; ?>

<!-- Example: Hide edit button if no permission -->
<?php if (hasPermission('edit_applications')): ?>
    <button class="btn-edit">Edit</button>
<?php endif; ?>

<!-- Example: Hide delete button if no permission -->
<?php if (hasPermission('delete_applications')): ?>
    <button class="btn-delete">Delete</button>
<?php endif; ?>

<!-- Example: Show read-only view if no edit permission -->
<?php if (hasPermission('edit_applications')): ?>
    <input type="text" name="title" value="<?= $data['title'] ?>">
<?php else: ?>
    <span><?= $data['title'] ?></span>
<?php endif; ?>

<?php
// ============================================
// PERMISSION REFERENCE
// ============================================

/*
DASHBOARD:
- view_dashboard

APPLICATIONS:
- view_applications
- create_applications
- edit_applications
- delete_applications
- change_application_status

USERS:
- view_users
- create_users
- edit_users
- delete_users

SERVICES:
- view_services
- create_services
- edit_services
- delete_services

CATEGORIES:
- view_categories
- manage_categories

COUNTRIES:
- view_countries
- manage_countries

DESTINATIONS:
- view_destinations
- manage_destinations

PAYMENTS:
- view_payments
- manage_payments

ROLES:
- view_roles
- manage_roles
*/
?>
