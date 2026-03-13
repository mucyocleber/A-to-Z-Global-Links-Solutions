<?php
/**
 * EXAMPLE: How to protect your existing pages with permissions
 * 
 * Add these lines at the top of your existing admin pages
 */

// Example 1: applications.php
require_once 'includes/config.php';
require_once 'includes/permissions.php';
requirePermission('view_applications'); // This will redirect if user doesn't have permission

// Then in your page, check for specific actions:
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'delete' && !hasPermission('delete_applications')) {
        die('Access denied');
    }
    
    if ($action === 'update' && !hasPermission('edit_applications')) {
        die('Access denied');
    }
    
    if ($action === 'change_status' && !hasPermission('change_application_status')) {
        die('Access denied');
    }
}

// In your HTML, conditionally show buttons:
?>
<div class="actions">
    <?php if (hasPermission('edit_applications')): ?>
        <button class="btn-edit">Edit</button>
    <?php endif; ?>
    
    <?php if (hasPermission('delete_applications')): ?>
        <button class="btn-delete">Delete</button>
    <?php endif; ?>
    
    <?php if (hasPermission('change_application_status')): ?>
        <select name="status">
            <!-- status options -->
        </select>
    <?php endif; ?>
</div>

<?php
// Example 2: users.php
require_once 'includes/config.php';
require_once 'includes/permissions.php';
requirePermission('view_users');

// Example 3: services.php
require_once 'includes/config.php';
require_once 'includes/permissions.php';
requirePermission('view_services');

// Example 4: payments.php
require_once 'includes/config.php';
require_once 'includes/permissions.php';
requirePermission('view_payments');

/**
 * PERMISSION SLUGS REFERENCE:
 * 
 * Dashboard:
 * - view_dashboard
 * 
 * Applications:
 * - view_applications
 * - create_applications
 * - edit_applications
 * - delete_applications
 * - change_application_status
 * 
 * Users:
 * - view_users
 * - create_users
 * - edit_users
 * - delete_users
 * 
 * Services:
 * - view_services
 * - create_services
 * - edit_services
 * - delete_services
 * 
 * Categories:
 * - view_categories
 * - manage_categories
 * 
 * Countries:
 * - view_countries
 * - manage_countries
 * 
 * Destinations:
 * - view_destinations
 * - manage_destinations
 * 
 * Payments:
 * - view_payments
 * - manage_payments
 * 
 * Roles:
 * - view_roles
 * - manage_roles
 */
