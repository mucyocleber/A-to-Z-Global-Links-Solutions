<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    exit('Unauthorized');
}

require_once '../../config/database.php';

try {
    $pdo = getConnection();
    
    $stmt = $pdo->prepare("
        SELECT u.*, 
               CONCAT(u.first_name, ' ', u.last_name) as full_name,
               COUNT(DISTINCT a.id) as application_count,
               COALESCE(SUM(CASE 
                   WHEN p.payment_status IN ('completed', 'approved', 'paid', 'success')
                   THEN COALESCE(p.amount, 0) 
                   ELSE 0 
               END), 0) as total_paid,
               COALESCE(MAX(CASE 
                   WHEN p.payment_status IN ('completed', 'approved', 'paid', 'success')
                   THEN p.currency 
               END), 'RWF') as currency
        FROM users u
        LEFT JOIN applications a ON u.id = a.user_id
        LEFT JOIN payments p ON a.id = p.application_id
        GROUP BY u.id
        ORDER BY u.created_at DESC
    ");
    
    $stmt->execute();
    $users = $stmt->fetchAll();
    
    header('Content-Type: text/html; charset=utf-8');
    
    echo '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Users Report - A to Z Global Link</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .header { text-align: center; margin-bottom: 30px; }
        .header h1 { color: #1e40af; margin: 0; }
        .header p { color: #64748b; margin: 5px 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; font-size: 12px; }
        th { background-color: #f8fafc; font-weight: bold; }
        .status-active { color: #059669; font-weight: bold; }
        .status-inactive { color: #dc2626; font-weight: bold; }
        .amount { color: #059669; font-weight: bold; }
        .print-btn { 
            background: #3b82f6; 
            color: white; 
            padding: 10px 20px; 
            border: none; 
            border-radius: 5px; 
            cursor: pointer; 
            margin: 10px;
        }
        @media print {
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>A to Z Global Link - Users Report</h1>
        <p>Generated on ' . date('F j, Y \a\t g:i A') . '</p>
        <p>Total Users: ' . count($users) . '</p>
        <button class="print-btn no-print" onclick="window.print()">Print as PDF</button>
    </div>
    
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Full Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Country</th>
                <th>Date of Birth</th>
                <th>Applications</th>
                <th>Total Paid</th>
                <th>Status</th>
                <th>Registered</th>
            </tr>
        </thead>
        <tbody>';
    
    foreach ($users as $user) {
        $status = $user['is_active'] ? 'Active' : 'Inactive';
        $statusClass = $user['is_active'] ? 'status-active' : 'status-inactive';
        $dob = $user['date_of_birth'] ? date('M j, Y', strtotime($user['date_of_birth'])) : 'N/A';
        $registered = date('M j, Y', strtotime($user['created_at']));
        $totalPaid = number_format($user['total_paid']) . ' ' . $user['currency'];
        
        echo '<tr>
                <td>' . $user['id'] . '</td>
                <td>' . htmlspecialchars($user['full_name']) . '</td>
                <td>' . htmlspecialchars($user['email']) . '</td>
                <td>' . htmlspecialchars($user['phone'] ?? 'N/A') . '</td>
                <td>' . htmlspecialchars($user['country'] ?? 'N/A') . '</td>
                <td>' . $dob . '</td>
                <td>' . $user['application_count'] . '</td>
                <td class="amount">' . $totalPaid . '</td>
                <td class="' . $statusClass . '">' . $status . '</td>
                <td>' . $registered . '</td>
            </tr>';
    }
    
    echo '</tbody>
    </table>
</body>
</html>';
    
} catch (Exception $e) {
    echo '<h1>Error generating report</h1><p>' . $e->getMessage() . '</p>';
}
?>