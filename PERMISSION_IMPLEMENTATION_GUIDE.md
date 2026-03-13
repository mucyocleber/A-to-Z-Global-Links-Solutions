# Complete Permission Implementation Guide

## Quick Start - Add to Every Admin Page

### 1. At the TOP of each page (after session_start):

```php
require_once 'includes/permissions.php';
requirePermission('PERMISSION_SLUG');
```

### 2. Permission Mapping by Page:

| Page | Permission Required |
|------|-------------------|
| `dashboard.php` | `view_dashboard` |
| `applications.php` | `view_applications` |
| `users.php` | `view_users` |
| `services.php` | `view_services` |
| `service-categories.php` | `view_categories` |
| `countries.php` | `view_countries` |
| `destination-countries.php` | `view_destinations` |
| `profile.php` | `view_dashboard` |

## Example Implementation

### applications.php

```php
<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

require_once '../config/database.php';
require_once 'includes/permissions.php';

// Require view permission for the page
requirePermission('view_applications');

// Check permissions for actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'create' && !hasPermission('create_applications')) {
        die('Access denied');
    }
    
    if ($action === 'edit' && !hasPermission('edit_applications')) {
        die('Access denied');
    }
    
    if ($action === 'delete' && !hasPermission('delete_applications')) {
        die('Access denied');
    }
}

// Your existing code...
?>

<!-- In HTML, hide buttons based on permissions -->
<div class="actions">
    <?php if (hasPermission('create_applications')): ?>
        <button class="btn-create">Create New</button>
    <?php endif; ?>
    
    <?php if (hasPermission('edit_applications')): ?>
        <button class="btn-edit">Edit</button>
    <?php endif; ?>
    
    <?php if (hasPermission('delete_applications')): ?>
        <button class="btn-delete">Delete</button>
    <?php endif; ?>
</div>
```

### users.php

```php
<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

require_once '../config/database.php';
require_once 'includes/permissions.php';
requirePermission('view_users');

// Check create permission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !hasPermission('create_users')) {
    die('Access denied');
}
?>
```

### services.php

```php
<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

require_once '../config/database.php';
require_once 'includes/permissions.php';
requirePermission('view_services');
?>
```

## AJAX Endpoints

For AJAX files in `/admin/ajax/` folder:

```php
<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

require_once '../../config/database.php';
require_once '../includes/permissions.php';

// Check permission
if (!hasPermission('delete_applications')) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Access denied']);
    exit;
}

// Your code...
?>
```

## Complete Permission List

### Dashboard
- `view_dashboard`

### Applications
- `view_applications` - View applications list
- `create_applications` - Create new applications
- `edit_applications` - Edit existing applications
- `delete_applications` - Delete applications
- `change_application_status` - Change application status

### Users
- `view_users` - View users list
- `create_users` - Create new users
- `edit_users` - Edit existing users
- `delete_users` - Delete users

### Services
- `view_services` - View services list
- `create_services` - Create new services
- `edit_services` - Edit existing services
- `delete_services` - Delete services

### Categories
- `view_categories` - View categories
- `manage_categories` - Create/Edit/Delete categories

### Countries
- `view_countries` - View countries
- `manage_countries` - Create/Edit/Delete countries

### Destinations
- `view_destinations` - View destinations
- `manage_destinations` - Create/Edit/Delete destinations

### Payments
- `view_payments` - View payments
- `manage_payments` - Manage payments

### Roles
- `view_roles` - View roles
- `manage_roles` - Manage roles and permissions

## Testing

1. Create a test admin with "Viewer" role
2. Login with that account
3. Try to access different pages
4. Verify they can only see what they're allowed to

## Troubleshooting

**Issue:** User can access everything despite restricted role
**Fix:** Make sure `permissions.php` returns `false` on error (not `true`)

**Issue:** User gets "Access denied" on dashboard
**Fix:** Make sure their role has `view_dashboard` permission

**Issue:** Sidebar shows all menu items
**Fix:** Sidebar already has permission checks, make sure user has a role assigned
