# RBAC System Installation Guide

## Quick Fix for HTTP 500 Error

The error occurs because the RBAC database tables don't exist yet.

### Installation Steps:

1. **Open phpMyAdmin**
   - Go to: http://localhost/phpmyadmin
   - Select your database

2. **Run the SQL File**
   - Click on "SQL" tab
   - Open file: `database/roles_permissions.sql`
   - Copy all content and paste into SQL box
   - Click "Go"

3. **Verify Installation**
   - Visit: https://atozgloballinksolutions.co.rw/admin/rbac-setup.php
   - Check if all tables show "Exists"

4. **Access Role Management**
   - Roles & Permissions: https://atozgloballinksolutions.co.rw/admin/roles.php
   - Admin Users: https://atozgloballinksolutions.co.rw/admin/admin-users.php

## What Gets Created:

### Tables:
- `admin_roles` - Store roles (Super Admin, Manager, etc.)
- `admin_permissions` - All available permissions
- `admin_role_permissions` - Maps roles to permissions
- `admins.role_id` - Column added to admins table

### Default Roles:
1. **Super Admin** - Full access
2. **Manager** - Manage apps, users, services
3. **Support Staff** - View and update applications
4. **Accountant** - Manage payments
5. **Viewer** - Read-only access

### Features:
✅ Dynamic sidebar based on permissions
✅ Role management from admin panel
✅ Assign roles to admin users
✅ Granular permission control
✅ No hardcoded roles in database

## Usage in Your Pages:

```php
// Protect a page
require_once 'includes/permissions.php';
requirePermission('view_applications');

// Check permission in code
if (hasPermission('delete_applications')) {
    // Show delete button
}

// Check in HTML
<?php if (hasPermission('edit_users')): ?>
    <button>Edit</button>
<?php endif; ?>
```

## Troubleshooting:

If you still get errors after running SQL:
1. Check if SQL executed successfully (no errors in phpMyAdmin)
2. Verify tables exist: `SHOW TABLES LIKE 'admin_%'`
3. Check error logs: `c:\xampp\apache\logs\error.log`
4. Visit setup checker: `/admin/rbac-setup.php`
