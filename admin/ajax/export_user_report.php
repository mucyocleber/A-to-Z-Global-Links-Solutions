<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../index.php');
    exit;
}

require_once '../../config/database.php';

$application_id = $_GET['application_id'] ?? null;
if (!$application_id) {
    header('Location: ../applications.php');
    exit;
}

$pdo = getConnection();

// Get application details
$stmt = $pdo->prepare("
    SELECT a.*, s.name as service_name, s.currency,
           u.first_name, u.last_name, u.email, u.phone, u.profile_picture,
           u.date_of_birth, u.nationality, u.passport_number, u.address,
           u.emergency_contact_name, u.emergency_contact_phone,
           ast.status_name as status,
           c.name as country_name
    FROM applications a 
    JOIN services s ON a.service_id = s.id 
    JOIN users u ON a.user_id = u.id 
    LEFT JOIN application_statuses ast ON a.status_id = ast.id
    LEFT JOIN countries c ON a.destination_country_id = c.id
    WHERE a.id = ?
");
$stmt->execute([$application_id]);
$application = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$application) {
    header('Location: ../applications.php');
    exit;
}

// Get payments
$stmt = $pdo->prepare("SELECT * FROM payments WHERE application_id = ? ORDER BY created_at DESC");
$stmt->execute([$application_id]);
$payments = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Set headers for HTML to PDF conversion
header('Content-Type: text/html; charset=UTF-8');
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Application Report - <?= htmlspecialchars($application['application_number']) ?></title>
    <style>
        @media print { body { margin: 0; } .no-print { display: none; } }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f8fafc; color: #1f2937; }
        .container { max-width: 800px; margin: 0 auto; background: white; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .header { background: linear-gradient(135deg, #1e40af, #3b82f6); color: white; padding: 2rem; text-align: center; }
        .company-logo { width: 60px; height: 60px; margin: 0 auto 1rem; }
        .company-logo img { width: 100%; height: 100%; object-fit: contain; }
        .company-name { font-size: 28px; font-weight: 700; margin-bottom: 0.5rem; }
        .report-title { font-size: 18px; opacity: 0.9; margin-bottom: 0.5rem; }
        .report-meta { font-size: 14px; opacity: 0.8; }
        .content { padding: 2rem; }
        .client-header { display: flex; align-items: center; gap: 2rem; margin-bottom: 2rem; padding: 1.5rem; background: #f8fafc; border-radius: 12px; border-left: 4px solid #3b82f6; }
        .profile-image { width: 120px; height: 120px; border-radius: 8px; object-fit: cover; border: 3px solid #e5e7eb; }
        .profile-placeholder { width: 120px; height: 120px; border-radius: 8px; background: linear-gradient(135deg, #e5e7eb, #d1d5db); display: flex; align-items: center; justify-content: center; font-size: 48px; color: #9ca3af; }
        .client-info h2 { font-size: 24px; color: #1f2937; margin-bottom: 0.5rem; }
        .client-info .app-number { font-size: 14px; color: #6b7280; font-weight: 500; }
        .status-badge { display: inline-block; padding: 6px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; text-transform: uppercase; margin-top: 0.5rem; }
        .status-pending { background: #fef3c7; color: #92400e; }
        .status-approved { background: #d1fae5; color: #065f46; }
        .status-rejected { background: #fee2e2; color: #991b1b; }
        .status-submitted { background: #dbeafe; color: #1e40af; }
        .section { margin-bottom: 2rem; }
        .section-title { font-size: 18px; font-weight: 600; color: #1f2937; margin-bottom: 1rem; padding-bottom: 0.5rem; border-bottom: 2px solid #e5e7eb; display: flex; align-items: center; gap: 0.5rem; }
        .section-icon { width: 20px; height: 20px; color: #3b82f6; }
        .info-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1rem; }
        .info-card { background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 8px; padding: 1rem; }
        .info-label { font-size: 12px; font-weight: 600; color: #6b7280; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 0.25rem; }
        .info-value { font-size: 14px; color: #1f2937; font-weight: 500; }
        .highlight-card { background: linear-gradient(135deg, #eff6ff, #dbeafe); border-color: #3b82f6; }
        .amount { font-size: 18px; font-weight: 700; color: #059669; }
        .payments-section { background: #f9fafb; border-radius: 12px; padding: 1.5rem; }
        .payments-table { width: 100%; border-collapse: collapse; margin-top: 1rem; }
        .payments-table th { background: #374151; color: white; padding: 12px; text-align: left; font-size: 12px; font-weight: 600; }
        .payments-table td { padding: 12px; border-bottom: 1px solid #e5e7eb; font-size: 13px; }
        .payments-table tr:hover { background: #f3f4f6; }
        .footer { background: #1f2937; color: white; padding: 1.5rem; text-align: center; }
        .footer-content { font-size: 12px; line-height: 1.6; }
        .footer-logo { font-size: 16px; font-weight: 600; margin-bottom: 0.5rem; }
        .no-print { position: fixed; top: 20px; right: 20px; z-index: 1000; }
        .print-btn { background: #3b82f6; color: white; padding: 12px 24px; border: none; border-radius: 8px; cursor: pointer; font-weight: 500; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .print-btn:hover { background: #2563eb; }
    </style>
</head>
<body>
    <div class="no-print">
        <button class="print-btn" onclick="window.print()">
            <i class="fas fa-print"></i> Print Report
        </button>
    </div>
    
    <div class="container">
        <div class="header">
            <div class="company-name">A to Z Global Link Solutions</div>
            <div class="report-title">Client Application Report</div>
            <div class="report-meta">Generated on <?= date('F j, Y \a\t g:i A') ?></div>
        </div>
        
        <div class="content">
            <!-- Client Header -->
            <div class="client-header">
                <?php if ($application['profile_picture']): ?>
                    <img src="../../uploads/profiles/<?= $application['profile_picture'] ?>" class="profile-image" alt="Profile">
                <?php else: ?>
                    <div class="profile-placeholder">
                        <i class="fas fa-user"></i>
                    </div>
                <?php endif; ?>
                <div class="client-info">
                    <h2><?= htmlspecialchars($application['first_name'] . ' ' . $application['last_name']) ?></h2>
                    <div class="app-number">Application #<?= htmlspecialchars($application['application_number']) ?></div>
                    <span class="status-badge status-<?= strtolower($application['status'] ?? 'pending') ?>">
                        <?= ucfirst($application['status'] ?? 'Pending') ?>
                    </span>
                </div>
            </div>
            
            <!-- Application Details -->
            <div class="section">
                <div class="section-title">
                    <svg class="section-icon" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"/>
                        <path fill-rule="evenodd" d="M4 5a2 2 0 012-2v1a1 1 0 001 1h6a1 1 0 001-1V3a2 2 0 012 2v6a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3z" clip-rule="evenodd"/>
                    </svg>
                    Application Information
                </div>
                <div class="info-grid">
                    <div class="info-card highlight-card">
                        <div class="info-label">Service Requested</div>
                        <div class="info-value"><?= htmlspecialchars($application['service_name']) ?></div>
                    </div>
                    <div class="info-card highlight-card">
                        <div class="info-label">Total Amount</div>
                        <div class="info-value amount"><?= $application['currency'] ?> <?= number_format($application['total_amount']) ?></div>
                    </div>
                    <div class="info-card">
                        <div class="info-label">Destination</div>
                        <div class="info-value"><?= htmlspecialchars($application['country_name'] ?? 'Not specified') ?></div>
                    </div>
                    <div class="info-card">
                        <div class="info-label">Travel Date</div>
                        <div class="info-value"><?= $application['intended_travel_date'] ? date('M j, Y', strtotime($application['intended_travel_date'])) : 'Not specified' ?></div>
                    </div>
                    <div class="info-card">
                        <div class="info-label">Duration</div>
                        <div class="info-value"><?= htmlspecialchars($application['duration_of_stay'] ?? 'Not specified') ?></div>
                    </div>
                    <div class="info-card">
                        <div class="info-label">Payment Plan</div>
                        <div class="info-value"><?= ucfirst($application['payment_plan']) ?> Payment</div>
                    </div>
                    <div class="info-card">
                        <div class="info-label">Application Type</div>
                        <div class="info-value"><?= ucfirst($application['application_type'] ?? 'online') ?></div>
                    </div>
                    <div class="info-card">
                        <div class="info-label">Submitted Date</div>
                        <div class="info-value"><?= date('M j, Y', strtotime($application['submitted_at'])) ?></div>
                    </div>
                </div>
                <?php if ($application['travel_purpose']): ?>
                <div class="info-card" style="margin-top: 1rem;">
                    <div class="info-label">Purpose of Travel</div>
                    <div class="info-value"><?= htmlspecialchars($application['travel_purpose']) ?></div>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- Personal Information -->
            <div class="section">
                <div class="section-title">
                    <svg class="section-icon" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
                    </svg>
                    Personal Information
                </div>
                <div class="info-grid">
                    <div class="info-card">
                        <div class="info-label">Email Address</div>
                        <div class="info-value"><?= htmlspecialchars($application['email']) ?></div>
                    </div>
                    <div class="info-card">
                        <div class="info-label">Phone Number</div>
                        <div class="info-value"><?= htmlspecialchars($application['phone']) ?></div>
                    </div>
                    <div class="info-card">
                        <div class="info-label">Nationality</div>
                        <div class="info-value"><?= htmlspecialchars($application['nationality'] ?? 'Not specified') ?></div>
                    </div>
                    <div class="info-card">
                        <div class="info-label">Date of Birth</div>
                        <div class="info-value"><?= $application['date_of_birth'] ? date('M j, Y', strtotime($application['date_of_birth'])) : 'Not specified' ?></div>
                    </div>
                    <div class="info-card">
                        <div class="info-label">Passport Number</div>
                        <div class="info-value"><?= htmlspecialchars($application['passport_number'] ?? 'Not specified') ?></div>
                    </div>
                    <div class="info-card">
                        <div class="info-label">Emergency Contact</div>
                        <div class="info-value"><?= $application['emergency_contact_name'] ? htmlspecialchars($application['emergency_contact_name']) . '<br><small>' . htmlspecialchars($application['emergency_contact_phone']) . '</small>' : 'Not specified' ?></div>
                    </div>
                </div>
                <?php if ($application['address']): ?>
                <div class="info-card" style="margin-top: 1rem;">
                    <div class="info-label">Residential Address</div>
                    <div class="info-value"><?= htmlspecialchars($application['address']) ?></div>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- Payment Information -->
            <?php if (!empty($payments)): ?>
            <div class="section">
                <div class="section-title">
                    <svg class="section-icon" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M4 4a2 2 0 00-2 2v1h16V6a2 2 0 00-2-2H4z"/>
                        <path fill-rule="evenodd" d="M18 9H2v5a2 2 0 002 2h12a2 2 0 002-2V9zM4 13a1 1 0 011-1h1a1 1 0 110 2H5a1 1 0 01-1-1zm5-1a1 1 0 100 2h1a1 1 0 100-2H9z" clip-rule="evenodd"/>
                    </svg>
                    Payment History
                </div>
                <div class="payments-section">
                    <table class="payments-table">
                        <thead>
                            <tr>
                                <th>Payment Date</th>
                                <th>Payment Type</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Reference</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($payments as $payment): ?>
                            <tr>
                                <td><?= date('M j, Y', strtotime($payment['created_at'])) ?></td>
                                <td><?= ucfirst(str_replace('_', ' ', $payment['payment_type'] ?? 'Payment')) ?></td>
                                <td class="amount"><?= $application['currency'] ?> <?= number_format($payment['amount']) ?></td>
                                <td>
                                    <span class="status-badge status-<?= strtolower($payment['payment_status'] ?? 'pending') ?>">
                                        <?= ucfirst($payment['payment_status'] ?? 'pending') ?>
                                    </span>
                                </td>
                                <td><?= htmlspecialchars($payment['transaction_reference'] ?? '-') ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endif; ?>
        </div>
        
        <div class="footer">
            <div class="footer-content">
                <div class="footer-logo">A to Z Global Link Solutions</div>
                <div>Kabuga Center, Kicukiro, Kigali, Rwanda | Phone: +250 796 597 936</div>
                <div>Email: info@atozglobal.rw | Website: www.atozglobal.rw</div>
                <div style="margin-top: 0.5rem; opacity: 0.8;">This is a computer-generated report. No signature required.</div>
            </div>
        </div>
    </div>
</body>
</html>