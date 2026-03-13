<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit;
}

require_once '../config/database.php';

$applicationId = $_GET['id'] ?? null;
if (!$applicationId) {
    header('Location: applications.php');
    exit;
}

try {
    $pdo = getConnection();
    
    // Get application details with full joins
    $stmt = $pdo->prepare("
        SELECT a.*, u.first_name, u.last_name, u.email, u.phone, u.date_of_birth, u.country,
               s.name as service_name, s.base_price, s.currency as service_currency,
               st.status_name, c.name as destination_country, pm.name as payment_method_name
        FROM applications a
        JOIN users u ON a.user_id = u.id
        JOIN services s ON a.service_id = s.id
        JOIN application_statuses st ON a.status_id = st.id
        LEFT JOIN countries c ON a.destination_country_id = c.id
        LEFT JOIN payment_methods pm ON a.payment_method_id = pm.id
        WHERE a.id = ?
    ");
    $stmt->execute([$applicationId]);
    $application = $stmt->fetch();
    
    if (!$application) {
        header('Location: applications.php');
        exit;
    }
    
    // Get documents
    $docsStmt = $pdo->prepare("SELECT * FROM documents WHERE application_id = ? ORDER BY uploaded_at DESC");
    $docsStmt->execute([$applicationId]);
    $documents = $docsStmt->fetchAll();
    
} catch (Exception $e) {
    header('Location: applications.php');
    exit;
}

// Set headers for HTML content
header('Content-Type: text/html; charset=UTF-8');
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Application Report - <?= htmlspecialchars($application['application_number']) ?></title>
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
        
        .status-badge {
            display: inline-block;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-top: 10px;
        }
        
        .status-pending { background: #dbeafe; color: #1e40af; }
        .status-approved, .status-completed { background: #d1fae5; color: #065f46; }
        .status-rejected { background: #fee2e2; color: #991b1b; }
        .status-under_review, .status-processing { background: #fef3c7; color: #92400e; }
        .status-submitted { background: #e0e7ff; color: #3730a3; }
        
        .application-card {
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            margin-bottom: 20px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .card-header {
            background: linear-gradient(135deg, #f7fafc 0%, #edf2f7 100%);
            padding: 20px;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .card-title {
            font-size: 20px;
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 5px;
        }
        
        .card-subtitle {
            color: #718096;
            font-size: 14px;
        }
        
        .card-content {
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
        
        .documents-section {
            margin-top: 20px;
            padding: 20px;
            background: #f8fafc;
            border-radius: 8px;
            border-left: 4px solid #8b5cf6;
        }
        
        .document-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .document-item:last-child {
            border-bottom: none;
        }
        
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
                <h2>Application Report</h2>
                <p><strong>Application ID:</strong> <?= htmlspecialchars($application['application_number']) ?></p>
                <p><strong>Generated:</strong> <?= date('F j, Y g:i A') ?></p>
                <div class="status-badge status-<?= strtolower(str_replace(' ', '_', $application['status_name'])) ?>">
                    <?= htmlspecialchars($application['status_name']) ?>
                </div>
            </div>
        </div>
        
        <div class="application-card">
            <div class="card-header">
                <div class="card-title"><?= htmlspecialchars($application['first_name'] . ' ' . $application['last_name']) ?></div>
                <div class="card-subtitle"><?= htmlspecialchars($application['service_name']) ?></div>
            </div>
            
            <div class="card-content">
                <div class="details-grid">
                    <div class="detail-section">
                        <div class="section-header">
                            👤 Personal Information
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Full Name:</span>
                            <span class="detail-value"><?= htmlspecialchars($application['first_name'] . ' ' . $application['last_name']) ?></span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Email:</span>
                            <span class="detail-value"><?= htmlspecialchars($application['email']) ?></span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Phone:</span>
                            <span class="detail-value"><?= htmlspecialchars($application['phone'] ?? 'N/A') ?></span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Date of Birth:</span>
                            <span class="detail-value"><?= $application['date_of_birth'] ? date('M j, Y', strtotime($application['date_of_birth'])) : 'N/A' ?></span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Country:</span>
                            <span class="detail-value"><?= htmlspecialchars($application['country'] ?? 'N/A') ?></span>
                        </div>
                    </div>
                    
                    <div class="detail-section">
                        <div class="section-header">
                            ✈️ Application Details
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Service:</span>
                            <span class="detail-value"><?= htmlspecialchars($application['service_name']) ?></span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Type:</span>
                            <span class="detail-value"><?= ucfirst($application['application_type'] ?? 'online') ?></span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Destination:</span>
                            <span class="detail-value"><?= htmlspecialchars($application['destination_country'] ?? 'N/A') ?></span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Travel Purpose:</span>
                            <span class="detail-value"><?= htmlspecialchars($application['travel_purpose'] ?? 'N/A') ?></span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Travel Date:</span>
                            <span class="detail-value"><?= $application['intended_travel_date'] ? date('M j, Y', strtotime($application['intended_travel_date'])) : 'N/A' ?></span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Duration:</span>
                            <span class="detail-value"><?= htmlspecialchars($application['duration_of_stay'] ?? 'N/A') ?></span>
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
                            <span class="detail-value amount"><?= $application['currency'] ?> <?= number_format($application['total_amount'], 2) ?></span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Payment Plan:</span>
                            <span class="detail-value"><?= ucfirst($application['payment_plan'] ?? 'full') ?></span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Payment Method:</span>
                            <span class="detail-value"><?= htmlspecialchars($application['payment_method_name'] ?? 'N/A') ?></span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Amount Paid:</span>
                            <span class="detail-value amount"><?= $application['currency'] ?> <?= number_format($application['amount_paid_so_far'], 2) ?></span>
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
                                $isPartial = ($application['payment_plan'] ?? 'full') === 'partial';
                                $initialPaid = ($application['initial_payment_status'] ?? 'pending') === 'completed';
                                $finalPaid = ($application['final_payment_status'] ?? 'not_required') === 'completed';
                                
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
                            <span class="detail-value"><?= number_format($application['payment_completion_percentage'], 1) ?>%</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Submitted:</span>
                            <span class="detail-value"><?= date('M j, Y g:i A', strtotime($application['submitted_at'])) ?></span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Last Updated:</span>
                            <span class="detail-value"><?= date('M j, Y g:i A', strtotime($application['last_updated_at'])) ?></span>
                        </div>
                    </div>
                </div>
                
                <?php if (!empty($documents)): ?>
                <div class="documents-section">
                    <div class="section-header">
                        📄 Uploaded Documents (<?= count($documents) ?>)
                    </div>
                    <?php foreach ($documents as $doc): ?>
                        <div class="document-item">
                            <div>
                                <strong><?= htmlspecialchars($doc['document_type']) ?></strong><br>
                                <small><?= htmlspecialchars($doc['original_filename']) ?></small>
                            </div>
                            <div style="text-align: right; font-size: 12px; color: #718096;">
                                <?= number_format($doc['file_size'] / 1024 / 1024, 2) ?> MB<br>
                                <?= date('M j, Y', strtotime($doc['uploaded_at'])) ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
                
                <?php if ($application['internal_notes']): ?>
                <div class="notes-section">
                    <div class="notes-title">
                        📝 Internal Notes
                    </div>
                    <div style="color: #92400e; line-height: 1.6;">
                        <?= nl2br(htmlspecialchars($application['internal_notes'])) ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
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