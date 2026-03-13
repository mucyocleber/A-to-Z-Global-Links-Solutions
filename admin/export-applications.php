<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit;
}

require_once '../config/database.php';

try {
    $pdo = getConnection();
    
    // Get all applications with full details
    $query = "
        SELECT a.*, 
               CONCAT(u.first_name, ' ', u.last_name) as full_name, 
               u.email, u.phone, u.country, u.date_of_birth,
               s.name as service_name, s.currency, s.base_price,
               st.status_name,
               c.name as destination_country,
               pm.name as payment_method_name
        FROM applications a 
        JOIN users u ON a.user_id = u.id 
        JOIN services s ON a.service_id = s.id 
        JOIN application_statuses st ON a.status_id = st.id
        LEFT JOIN countries c ON a.destination_country_id = c.id
        LEFT JOIN payment_methods pm ON a.payment_method_id = pm.id
        ORDER BY a.submitted_at DESC
    ";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $applications = $stmt->fetchAll();
    
} catch (Exception $e) {
    $applications = [];
}

// Calculate statistics
$totalApplications = count($applications);
$pendingCount = count(array_filter($applications, function($a) { return in_array($a['status_id'], [1,2,3]); }));
$approvedCount = count(array_filter($applications, function($a) { return $a['status_id'] == 4; }));
$rejectedCount = count(array_filter($applications, function($a) { return $a['status_id'] == 5; }));

// Calculate revenue by currency
$currencies = [];
foreach($applications as $app) {
    $initialPaid = ($app['initial_payment_status'] ?? 'pending') === 'completed';
    $finalPaid = ($app['final_payment_status'] ?? 'not_required') === 'completed';
    $isPartial = ($app['payment_plan'] ?? 'full') === 'partial';
    
    $paidAmount = 0;
    if ($isPartial) {
        if ($initialPaid) $paidAmount += $app['initial_payment_amount'];
        if ($finalPaid) $paidAmount += $app['remaining_payment_amount'];
    } else {
        if ($initialPaid) $paidAmount = $app['total_amount'];
    }
    
    if ($paidAmount > 0) {
        $curr = $app['currency'] ?? 'USD';
        $currencies[$curr] = ($currencies[$curr] ?? 0) + $paidAmount;
    }
}

// Set headers for HTML content
header('Content-Type: text/html; charset=UTF-8');

// Start HTML content
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Applications Report</title>
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
        
        .revenue-section {
            background: linear-gradient(135deg, #ebf8ff 0%, #bee3f8 100%);
            border: 1px solid #90cdf4;
            border-radius: 12px;
            padding: 20px;
            margin: 20px 0;
        }
        
        .revenue-title {
            font-size: 18px;
            font-weight: 600;
            color: #2c5282;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .revenue-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
        }
        
        .revenue-item {
            background: white;
            padding: 15px;
            border-radius: 8px;
            text-align: center;
            border: 1px solid #cbd5e0;
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
        
        .application-card {
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            margin-bottom: 20px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            page-break-inside: avoid;
        }
        
        .app-header {
            background: linear-gradient(135deg, #f7fafc 0%, #edf2f7 100%);
            padding: 20px;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .app-title {
            font-size: 18px;
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 5px;
        }
        
        .app-id {
            font-size: 14px;
            color: #718096;
            font-weight: 500;
        }
        
        .status-badge {
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .status-pending { background: #dbeafe; color: #1e40af; }
        .status-approved, .status-completed { background: #d1fae5; color: #065f46; }
        .status-rejected { background: #fee2e2; color: #991b1b; }
        .status-under_review, .status-processing { background: #fef3c7; color: #92400e; }
        .status-submitted { background: #e0e7ff; color: #3730a3; }
        
        .app-content {
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
        
        .amount {
            color: #059669;
            font-weight: 700;
            font-size: 16px;
        }
        
        .payment-status {
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .payment-completed { background: #d1fae5; color: #065f46; }
        .payment-pending { background: #fee2e2; color: #991b1b; }
        .payment-partial { background: #fef3c7; color: #92400e; }
        
        .notes-section {
            background: #fffbeb;
            border: 1px solid #fbbf24;
            border-radius: 8px;
            padding: 15px;
            margin-top: 20px;
        }
        
        .notes-title {
            font-weight: 600;
            color: #92400e;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 8px;
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
                <h2>Applications Report</h2>
                <p><strong>Generated:</strong> <?= date('F j, Y') ?></p>
                <p><strong>Time:</strong> <?= date('g:i A') ?></p>
                <p><strong>Total Records:</strong> <?= $totalApplications ?></p>
            </div>
        </div>
    
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number"><?= $totalApplications ?></div>
                <div class="stat-label">Total Applications</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= $pendingCount ?></div>
                <div class="stat-label">Pending Review</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= $approvedCount ?></div>
                <div class="stat-label">Approved</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= $rejectedCount ?></div>
                <div class="stat-label">Rejected</div>
            </div>
        </div>
    
        <?php if (!empty($currencies)): ?>
        <div class="revenue-section">
            <div class="revenue-title">
                💰 Revenue Summary (Paid Only)
            </div>
            <div class="revenue-grid">
                <?php foreach($currencies as $curr => $amount): ?>
                    <div class="revenue-item">
                        <div style="font-size: 18px; font-weight: 600; color: #059669;"><?= $curr ?> <?= number_format($amount, 2) ?></div>
                        <div style="font-size: 12px; color: #718096;">Total Received</div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    
        <div class="section-title">
            📄 Application Details
        </div>
        
        <?php foreach ($applications as $app): ?>
            <div class="application-card">
                <div class="app-header">
                    <div>
                        <div class="app-title"><?= htmlspecialchars($app['full_name']) ?></div>
                        <div class="app-id">Application ID: <?= htmlspecialchars($app['application_number']) ?></div>
                    </div>
                    <div class="status-badge status-<?= strtolower(str_replace(' ', '_', $app['status_name'])) ?>">
                        <?= htmlspecialchars($app['status_name']) ?>
                    </div>
                </div>
                
                <div class="app-content">
                    <div class="details-grid">
                        <div class="detail-section">
                            <div class="section-header">
                                👤 Personal Information
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Full Name:</span>
                                <span class="detail-value"><?= htmlspecialchars($app['full_name']) ?></span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Email:</span>
                                <span class="detail-value"><?= htmlspecialchars($app['email']) ?></span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Phone:</span>
                                <span class="detail-value"><?= htmlspecialchars($app['phone'] ?? 'N/A') ?></span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Date of Birth:</span>
                                <span class="detail-value"><?= $app['date_of_birth'] ? date('M j, Y', strtotime($app['date_of_birth'])) : 'N/A' ?></span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Country:</span>
                                <span class="detail-value"><?= htmlspecialchars($app['country'] ?? 'N/A') ?></span>
                            </div>
                        </div>
                        
                        <div class="detail-section">
                            <div class="section-header">
                                ✈️ Application Details
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Service:</span>
                                <span class="detail-value"><?= htmlspecialchars($app['service_name']) ?></span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Type:</span>
                                <span class="detail-value"><?= ucfirst($app['application_type'] ?? 'online') ?></span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Destination:</span>
                                <span class="detail-value"><?= htmlspecialchars($app['destination_country'] ?? 'N/A') ?></span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Travel Purpose:</span>
                                <span class="detail-value"><?= htmlspecialchars($app['travel_purpose'] ?? 'N/A') ?></span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Travel Date:</span>
                                <span class="detail-value"><?= $app['intended_travel_date'] ? date('M j, Y', strtotime($app['intended_travel_date'])) : 'N/A' ?></span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Duration:</span>
                                <span class="detail-value"><?= htmlspecialchars($app['duration_of_stay'] ?? 'N/A') ?></span>
                            </div>
                        </div>
                    </div>
            
                    <div class="details-grid">
                        <div class="detail-section">
                            <div class="section-header">
                                💳 Payment Information
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Total Amount:</span>
                                <span class="detail-value amount"><?= $app['currency'] ?> <?= number_format($app['total_amount'], 2) ?></span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Payment Plan:</span>
                                <span class="detail-value"><?= ucfirst($app['payment_plan'] ?? 'full') ?></span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Payment Method:</span>
                                <span class="detail-value"><?= htmlspecialchars($app['payment_method_name'] ?? 'N/A') ?></span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Amount Paid:</span>
                                <span class="detail-value amount"><?= $app['currency'] ?> <?= number_format($app['amount_paid_so_far'], 2) ?></span>
                            </div>
                        </div>
                        
                        <div class="detail-section">
                            <div class="section-header">
                                📅 Timeline & Status
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Payment Status:</span>
                                <span class="detail-value">
                                    <?php
                                    $isPartial = ($app['payment_plan'] ?? 'full') === 'partial';
                                    $initialPaid = ($app['initial_payment_status'] ?? 'pending') === 'completed';
                                    $finalPaid = ($app['final_payment_status'] ?? 'not_required') === 'completed';
                                    
                                    if ($isPartial) {
                                        if ($initialPaid && $finalPaid) {
                                            echo '<span class="payment-status payment-completed">Fully Paid</span>';
                                        } elseif ($initialPaid) {
                                            echo '<span class="payment-status payment-partial">Partially Paid</span>';
                                        } else {
                                            echo '<span class="payment-status payment-pending">Pending</span>';
                                        }
                                    } else {
                                        echo $initialPaid ? '<span class="payment-status payment-completed">Fully Paid</span>' : '<span class="payment-status payment-pending">Pending</span>';
                                    }
                                    ?>
                                </span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Completion:</span>
                                <span class="detail-value"><?= number_format($app['payment_completion_percentage'], 1) ?>%</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Submitted:</span>
                                <span class="detail-value"><?= date('M j, Y g:i A', strtotime($app['submitted_at'])) ?></span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Last Updated:</span>
                                <span class="detail-value"><?= date('M j, Y g:i A', strtotime($app['last_updated_at'])) ?></span>
                            </div>
                        </div>
                    </div>
            
                    <?php if ($app['internal_notes']): ?>
                    <div class="notes-section">
                        <div class="notes-title">
                            📝 Internal Notes
                        </div>
                        <div style="color: #92400e; line-height: 1.6;">
                            <?= nl2br(htmlspecialchars($app['internal_notes'])) ?>
                        </div>
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