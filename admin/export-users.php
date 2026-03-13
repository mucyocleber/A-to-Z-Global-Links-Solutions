<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit;
}

require_once '../config/database.php';

try {
    $pdo = getConnection();
    
    // Get all users with full details
    $query = "
        SELECT u.*, 
               CONCAT(u.first_name, ' ', u.last_name) as full_name,
               COUNT(DISTINCT a.id) as application_count,
               COALESCE(SUM(CASE WHEN a.initial_payment_status = 'completed' THEN a.initial_payment_amount ELSE 0 END), 0) +
               COALESCE(SUM(CASE WHEN a.final_payment_status = 'completed' THEN a.remaining_payment_amount ELSE 0 END), 0) as total_paid
        FROM users u
        LEFT JOIN applications a ON u.id = a.user_id
        GROUP BY u.id
        ORDER BY u.created_at DESC
    ";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $users = $stmt->fetchAll();
    
} catch (Exception $e) {
    $users = [];
}

// Calculate statistics
$totalUsers = count($users);
$activeUsers = count(array_filter($users, function($u) { return $u['is_active']; }));
$newToday = count(array_filter($users, function($u) { return date('Y-m-d', strtotime($u['created_at'])) === date('Y-m-d'); }));
$totalApplications = array_sum(array_column($users, 'application_count'));

// Set headers for HTML content
header('Content-Type: text/html; charset=UTF-8');
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Users Report</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            line-height: 1.6; 
            color: #2d3748; 
            background: #fff;
        }
        
        @page { 
            size: A4; 
            margin: 15mm 20mm; 
        }
        
        .page-container {
            max-width: 210mm;
            margin: 0 auto;
            background: white;
            min-height: 297mm;
        }
        
        .header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 20px 0;
            border-bottom: 3px solid #3b82f6;
            margin-bottom: 30px;
        }
        
        .company-info {
            display: flex;
            align-items: center;
            gap: 20px;
        }
        
        .logo {
            width: 80px;
            height: 80px;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        
        .logo img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .company-details h1 {
            color: #1a202c;
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 5px;
        }
        
        .company-details p {
            color: #4a5568;
            font-size: 14px;
            margin: 2px 0;
        }
        
        .report-info {
            text-align: right;
            color: #4a5568;
        }
        
        .report-info h2 {
            color: #2d3748;
            font-size: 24px;
            margin-bottom: 10px;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin: 30px 0;
        }
        
        .stat-card {
            background: linear-gradient(135deg, #f7fafc 0%, #edf2f7 100%);
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 20px;
            text-align: center;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .stat-number {
            font-size: 32px;
            font-weight: 700;
            color: #2d3748;
            margin-bottom: 5px;
        }
        
        .stat-label {
            font-size: 12px;
            color: #718096;
            text-transform: uppercase;
            font-weight: 600;
            letter-spacing: 0.5px;
        }
        
        .section-title {
            font-size: 20px;
            font-weight: 600;
            color: #2d3748;
            margin: 30px 0 20px 0;
            padding-bottom: 10px;
            border-bottom: 2px solid #e2e8f0;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .user-card {
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            margin-bottom: 20px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            page-break-inside: avoid;
        }
        
        .user-header {
            background: linear-gradient(135deg, #f7fafc 0%, #edf2f7 100%);
            padding: 20px;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .user-title {
            font-size: 18px;
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 5px;
        }
        
        .user-email {
            font-size: 14px;
            color: #718096;
        }
        
        .status-badge {
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .status-active { background: #d1fae5; color: #065f46; }
        .status-inactive { background: #fee2e2; color: #991b1b; }
        
        .user-content {
            padding: 25px;
        }
        
        .details-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-bottom: 25px;
        }
        
        .detail-section {
            background: #f8fafc;
            padding: 20px;
            border-radius: 8px;
            border-left: 4px solid #3b82f6;
        }
        
        .section-header {
            font-size: 16px;
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .detail-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 0;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .detail-row:last-child {
            border-bottom: none;
        }
        
        .detail-label {
            font-weight: 500;
            color: #4a5568;
            font-size: 14px;
        }
        
        .detail-value {
            color: #2d3748;
            font-weight: 500;
            font-size: 14px;
        }
        
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 2px solid #e2e8f0;
            text-align: center;
            color: #718096;
            font-size: 12px;
        }
        
        @media print {
            .no-print { display: none !important; }
            body { margin: 0; }
            .page-container { margin: 0; }
        }
    </style>
</head>
<body>
    <div class="page-container">
        <div class="header">
            <div class="company-info">
                <div class="logo">
                    <img src="../logo/logo.jpg" alt="AtoZ Global Link Logo">
                </div>
                <div class="company-details">
                    <h1>AtoZ Global Link</h1>
                    <p>Visa & Immigration Services</p>
                    <p>Professional Application Management</p>
                    <p>Email: info.atozgloballinksolutions@gmail.com | Phone: 0796597936</p>
                </div>
            </div>
            <div class="report-info">
                <h2>Users Report</h2>
                <p><strong>Generated:</strong> <?= date('F j, Y') ?></p>
                <p><strong>Time:</strong> <?= date('g:i A') ?></p>
                <p><strong>Total Records:</strong> <?= $totalUsers ?></p>
            </div>
        </div>
        
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number"><?= $totalUsers ?></div>
                <div class="stat-label">Total Users</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= $activeUsers ?></div>
                <div class="stat-label">Active Users</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= $newToday ?></div>
                <div class="stat-label">New Today</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= $totalApplications ?></div>
                <div class="stat-label">Total Applications</div>
            </div>
        </div>
        
        <div class="section-title">
            👥 User Details
        </div>
        
        <?php foreach ($users as $user): ?>
            <div class="user-card">
                <div class="user-header">
                    <div>
                        <div class="user-title"><?= htmlspecialchars($user['full_name']) ?></div>
                        <div class="user-email"><?= htmlspecialchars($user['email']) ?></div>
                    </div>
                    <div class="status-badge status-<?= $user['is_active'] ? 'active' : 'inactive' ?>">
                        <?= $user['is_active'] ? 'Active' : 'Inactive' ?>
                    </div>
                </div>
                
                <div class="user-content">
                    <div class="details-grid">
                        <div class="detail-section">
                            <div class="section-header">
                                👤 Personal Information
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Full Name:</span>
                                <span class="detail-value"><?= htmlspecialchars($user['full_name']) ?></span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Email:</span>
                                <span class="detail-value"><?= htmlspecialchars($user['email']) ?></span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Phone:</span>
                                <span class="detail-value"><?= htmlspecialchars($user['phone'] ?? 'N/A') ?></span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Date of Birth:</span>
                                <span class="detail-value"><?= $user['date_of_birth'] ? date('M j, Y', strtotime($user['date_of_birth'])) : 'N/A' ?></span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Gender:</span>
                                <span class="detail-value"><?= ucfirst($user['gender'] ?? 'N/A') ?></span>
                            </div>
                        </div>
                        
                        <div class="detail-section">
                            <div class="section-header">
                                🌍 Location & Activity
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Country:</span>
                                <span class="detail-value"><?= htmlspecialchars($user['country'] ?? 'N/A') ?></span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Nationality:</span>
                                <span class="detail-value"><?= htmlspecialchars($user['nationality'] ?? 'N/A') ?></span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Applications:</span>
                                <span class="detail-value"><?= $user['application_count'] ?></span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Total Paid:</span>
                                <span class="detail-value" style="color: #059669; font-weight: 700;">RWF <?= number_format($user['total_paid'], 2) ?></span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Registered:</span>
                                <span class="detail-value"><?= date('M j, Y g:i A', strtotime($user['created_at'])) ?></span>
                            </div>
                        </div>
                    </div>
                    
                    <?php if ($user['address']): ?>
                    <div class="detail-section" style="margin-top: 20px;">
                        <div class="section-header">
                            📍 Address Information
                        </div>
                        <div style="color: #2d3748; line-height: 1.6;">
                            <?= nl2br(htmlspecialchars($user['address'])) ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($user['passport_number']): ?>
                    <div style="margin-top: 15px; padding: 15px; background: #fffbeb; border: 1px solid #fbbf24; border-radius: 8px;">
                        <strong style="color: #92400e;">Passport Number:</strong> <?= htmlspecialchars($user['passport_number']) ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
        
        <div class="footer">
            <div style="margin-bottom: 10px;">
                <strong>AtoZ Global Link</strong> - Professional Visa & Immigration Services
            </div>
            <div>
                Report generated on <?= date('F j, Y \a\t g:i A') ?> | Confidential Document
            </div>
        </div>
    </div>
    
    <div class="no-print" style="position: fixed; top: 20px; right: 20px; z-index: 1000; display: flex; gap: 10px;">
        <button onclick="window.print()" style="background: linear-gradient(135deg, #3b82f6, #1d4ed8); color: white; border: none; padding: 12px 24px; border-radius: 12px; cursor: pointer; font-weight: 600; box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3); transition: all 0.3s ease;">
            🖨️ Print/Save as PDF
        </button>
        <button onclick="window.close()" style="background: linear-gradient(135deg, #6b7280, #4b5563); color: white; border: none; padding: 12px 24px; border-radius: 12px; cursor: pointer; font-weight: 600; box-shadow: 0 4px 12px rgba(107, 114, 128, 0.3); transition: all 0.3s ease;">
            ❌ Close
        </button>
    </div>
</body>
</html>

<script>
// Auto-print when page loads
window.onload = function() {
    setTimeout(function() {
        window.print();
    }, 1000);
};
</script>
<?php
// End of PHP processing
?>