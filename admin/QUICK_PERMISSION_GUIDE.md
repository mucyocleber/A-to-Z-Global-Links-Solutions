# Permission Code Snippets - Copy & Paste

## For applications.php
Add after `require_once '../config/database.php';`:
```php
require_once 'includes/permissions.php';
requirePermission('view_applications');
```

## For users.php
Add after `require_once '../config/database.php';`:
```php
require_once 'includes/permissions.php';
requirePermission('view_users');
```

## For services.php
Add after `require_once '../config/database.php';`:
```php
require_once 'includes/permissions.php';
requirePermission('view_services');
```

## For service-categories.php
Add after `require_once '../config/database.php';`:
```php
require_once 'includes/permissions.php';
requirePermission('view_categories');
```

## For countries.php
Add after `require_once '../config/database.php';`:
```php
require_once 'includes/permissions.php';
requirePermission('view_countries');
```

## For destination-countries.php
Add after `require_once '../config/database.php';`:
```php
require_once 'includes/permissions.php';
requirePermission('view_destinations');
```

## For profile.php
Add after `require_once '../config/database.php';`:
```php
require_once 'includes/permissions.php';
requirePermission('view_dashboard');
```

---

## ✅ Already Done:
- dashboard.php ✓
- roles.php ✓
- admin-users.php ✓

## 🎯 Summary:
Just add these 2 lines after the database connection in each file:
```php
require_once 'includes/permissions.php';
requirePermission('PERMISSION_NAME');
```
