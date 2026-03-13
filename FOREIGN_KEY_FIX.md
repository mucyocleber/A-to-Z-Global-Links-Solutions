# Foreign Key Constraint Fix

## Problem
Error when deleting applications: 
```
SQLSTATE[23000]: Integrity constraint violation: 1451 
Cannot delete or update a parent row: a foreign key constraint fails
```

## Root Cause
The `payments` table has a foreign key constraint on `application_id` that prevents deletion of applications that have associated payments.

## Solution

Since you don't have database permissions to modify constraints (hosting restriction), we've implemented a **PHP-based workaround** that manually deletes related records in the correct order.

### Files Modified/Created:

1. **`admin/includes/safe_delete.php`** - Helper functions for safe deletion
   - `safeDeleteApplication()` - Deletes application and all related records
   - `safeDeleteUser()` - Deletes user and all their applications

2. **`admin/ajax/delete_application.php`** - Updated to use safe delete

### How It Works:

When deleting an application, the system now:
1. Deletes payment installments first
2. Deletes payments
3. Deletes documents
4. Deletes application messages
5. Deletes status history
6. Deletes refund requests
7. Finally deletes the application

All operations are wrapped in a **transaction** - if any step fails, everything rolls back.

### Usage:

```php
require_once 'includes/safe_delete.php';

// Delete an application
$result = safeDeleteApplication($pdo, $applicationId);
if ($result['success']) {
    echo "Deleted successfully";
} else {
    echo "Error: " . $result['message'];
}

// Delete a user (and all their applications)
$result = safeDeleteUser($pdo, $userId);
```

### Benefits:
✅ No database permission changes needed
✅ Works with hosting restrictions
✅ Transaction-safe (all-or-nothing)
✅ Reusable helper functions
✅ Proper error handling

### Testing:
Try deleting an application that has payments - it should now work without errors!

## Alternative (If You Get Database Access Later):

If you eventually get permission to modify database constraints, run this SQL:

```sql
ALTER TABLE `payments` 
ADD CONSTRAINT `payments_application_fk` 
FOREIGN KEY (`application_id`) REFERENCES `applications` (`id`) 
ON DELETE CASCADE;
```

This would make the database automatically delete related payments when an application is deleted.
