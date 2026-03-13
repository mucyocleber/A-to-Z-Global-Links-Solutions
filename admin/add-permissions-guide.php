<?php
/**
 * AUTO-ADD PERMISSIONS TO ADMIN PAGES
 * This script adds permission checks to all admin pages
 */

$pages_permissions = [
    'dashboard.php' => 'view_dashboard',
    'applications.php' => 'view_applications',
    'users.php' => 'view_users',
    'services.php' => 'view_services',
    'service-categories.php' => 'view_categories',
    'countries.php' => 'view_countries',
    'destination-countries.php' => 'view_destinations',
    'profile.php' => 'view_dashboard', // Everyone can view their profile
];

echo "<h2>Pages that need permission checks:</h2>";
echo "<table border='1' cellpadding='10'>";
echo "<tr><th>Page</th><th>Required Permission</th><th>Code to Add</th></tr>";

foreach ($pages_permissions as $page => $permission) {
    $file_path = __DIR__ . '/' . $page;
    $exists = file_exists($file_path) ? '✅' : '❌';
    
    $code = "require_once 'includes/permissions.php';\nrequirePermission('$permission');";
    
    echo "<tr>";
    echo "<td>$exists $page</td>";
    echo "<td><code>$permission</code></td>";
    echo "<td><pre>" . htmlspecialchars($code) . "</pre></td>";
    echo "</tr>";
}

echo "</table>";

echo "<h2>Instructions:</h2>";
echo "<ol>";
echo "<li>Open each page listed above</li>";
echo "<li>Add the code shown after the session_start() and database connection</li>";
echo "<li>Test by logging in with different roles</li>";
echo "</ol>";

echo "<h2>Quick Copy-Paste Code:</h2>";
echo "<pre>";
echo htmlspecialchars("
// Add this after session_start() and database connection
require_once 'includes/permissions.php';
requirePermission('PERMISSION_NAME_HERE');
");
echo "</pre>";
?>
