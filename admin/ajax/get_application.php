<?php
require_once '../../config/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['application_id'])) {
    echo json_encode(['error' => 'Invalid request']);
    exit;
}

$application_id = (int)$_POST['application_id'];

try {
    $pdo = getConnection();
    
    // Get application with related data
    $stmt = $pdo->prepare("
        SELECT a.*, 
               u.first_name, u.last_name, u.email, u.country as user_country, u.date_of_birth,
               s.name as service_name, s.short_description,
               st.status_name,
               c.name as destination_country
        FROM applications a
        LEFT JOIN users u ON a.user_id = u.id
        LEFT JOIN services s ON a.service_id = s.id
        LEFT JOIN application_statuses st ON a.status_id = st.id
        LEFT JOIN countries c ON a.destination_country_id = c.id
        WHERE a.id = ?
    ");
    $stmt->execute([$application_id]);
    $app = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$app) {
        echo json_encode(['error' => 'Application not found']);
        exit;
    }
    
    // Get payment info
    $stmt = $pdo->prepare("
        SELECT p.*, pm.name as payment_method_name
        FROM payments p
        LEFT JOIN payment_methods pm ON p.payment_method_id = pm.id
        WHERE p.application_id = ?
    ");
    $stmt->execute([$application_id]);
    $payment = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Get messages
    $stmt = $pdo->prepare("
        SELECT m.*, 
               CASE 
                   WHEN m.sender_type = 'admin' THEN a.username
                   WHEN m.sender_type = 'user' THEN CONCAT(u2.first_name, ' ', u2.last_name)
               END as sender_name
        FROM application_messages m
        LEFT JOIN admins a ON m.sender_type = 'admin' AND m.sender_id = a.id
        LEFT JOIN users u2 ON m.sender_type = 'user' AND m.sender_id = u2.id
        WHERE m.application_id = ?
        ORDER BY m.created_at ASC
    ");
    $stmt->execute([$application_id]);
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get documents
    $stmt = $pdo->prepare("
        SELECT d.*, sr.name as requirement_name
        FROM documents d
        LEFT JOIN service_requirements sr ON d.requirement_id = sr.id
        WHERE d.application_id = ?
        ORDER BY d.uploaded_at DESC
    ");
    $stmt->execute([$application_id]);
    $documents = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Build HTML without tabs - just details
    $html = '<div class="app-details">';
    
    // User Section
    $html .= '<div class="detail-section">';
    $html .= '<h4><i class="fas fa-user"></i> User Information</h4>';
    $html .= '<div class="detail-grid">';
    $html .= '<div class="detail-item"><span class="label">Name:</span><span class="value">' . htmlspecialchars($app['first_name'] . ' ' . $app['last_name']) . '</span></div>';
    $html .= '<div class="detail-item"><span class="label">Email:</span><span class="value">' . htmlspecialchars($app['email']) . '</span></div>';
    $html .= '<div class="detail-item"><span class="label">Country:</span><span class="value">' . htmlspecialchars($app['user_country'] ?? 'N/A') . '</span></div>';
    $html .= '</div></div>';
    
    // Application Section
    $html .= '<div class="detail-section">';
    $html .= '<h4><i class="fas fa-file-alt"></i> Application Details</h4>';
    $html .= '<div class="detail-grid">';
    $html .= '<div class="detail-item"><span class="label">Application ID:</span><span class="value">' . htmlspecialchars($app['application_number']) . '</span></div>';
    $html .= '<div class="detail-item"><span class="label">Service:</span><span class="value">' . htmlspecialchars($app['service_name']) . '</span></div>';
    $html .= '<div class="detail-item"><span class="label">Status:</span><span class="value status-badge">' . htmlspecialchars($app['status_name']) . '</span></div>';
    $html .= '<div class="detail-item"><span class="label">Amount:</span><span class="value amount">' . number_format($app['total_amount']) . ' RWF</span></div>';
    if ($app['destination_country']) {
        $html .= '<div class="detail-item"><span class="label">Destination:</span><span class="value">' . htmlspecialchars($app['destination_country']) . '</span></div>';
    }
    $html .= '<div class="detail-item"><span class="label">Submitted:</span><span class="value">' . date('M d, Y H:i', strtotime($app['submitted_at'])) . '</span></div>';
    $html .= '</div></div>';
    
    // Payment Section
    if ($payment) {
        $html .= '<div class="detail-section">';
        $html .= '<h4><i class="fas fa-credit-card"></i> Payment Information</h4>';
        $html .= '<div class="detail-grid">';
        $html .= '<div class="detail-item"><span class="label">Amount:</span><span class="value amount">' . number_format($payment['amount']) . ' RWF</span></div>';
        $html .= '<div class="detail-item"><span class="label">Method:</span><span class="value">' . htmlspecialchars($payment['payment_method_name']) . '</span></div>';
        $html .= '<div class="detail-item"><span class="label">Status:</span><span class="value payment-status ' . $payment['payment_status'] . '">' . ucwords(str_replace('_', ' ', $payment['payment_status'])) . '</span></div>';
        if ($payment['transaction_reference']) {
            $html .= '<div class="detail-item"><span class="label">Reference:</span><span class="value">' . htmlspecialchars($payment['transaction_reference']) . '</span></div>';
        }
        if ($payment['proof_of_payment']) {
            $html .= '<div class="detail-item"><span class="label">Proof:</span><span class="value"><button class="btn-view-proof" onclick="window.open(\'view_proof.php?id=' . $payment['id'] . '\', \'_blank\')" title="View Payment Proof"><i class="fas fa-image"></i> View Proof</button></span></div>';
        }
        $html .= '</div></div>';
    }
    
    // Documents Section
    if (!empty($documents)) {
        $html .= '<div class="detail-section">';
        $html .= '<h4><i class="fas fa-folder"></i> Documents (' . count($documents) . ')</h4>';
        $html .= '<div class="documents-list">';
        foreach ($documents as $doc) {
            $html .= '<div class="document-item">';
            $html .= '<div class="doc-info">';
            $html .= '<div class="doc-name">' . htmlspecialchars($doc['original_filename']) . '</div>';
            $html .= '<div class="doc-type">' . htmlspecialchars($doc['requirement_name'] ?? $doc['document_type']) . '</div>';
            $html .= '</div>';
            $html .= '<div class="doc-actions">';
            if ($doc['is_verified']) {
                $html .= '<span class="verified"><i class="fas fa-check-circle"></i></span>';
            } else {
                $html .= '<span class="pending"><i class="fas fa-clock"></i></span>';
            }
            $html .= '<button class="btn-view-doc" onclick="window.open(\'view_document.php?id=' . $doc['id'] . '\', \'_blank\')" title="View Document"><i class="fas fa-eye"></i></button>';
            $html .= '</div>';
            $html .= '</div>';
        }
        $html .= '</div></div>';
    }
    
    $html .= '</div>'; // End app-details
    
    // Add CSS styles
    $html .= '<style>
    .app-details { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; }
    .detail-section { margin-bottom: 2rem; }
    .detail-section h4 { color: #1f2937; font-size: 1.1rem; font-weight: 600; margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem; }
    .detail-section h4 i { color: #3b82f6; }
    .detail-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; }
    .detail-item { display: flex; justify-content: space-between; align-items: center; padding: 0.75rem; background: #f8fafc; border-radius: 8px; }
    .detail-item .label { font-weight: 500; color: #6b7280; }
    .detail-item .value { font-weight: 600; color: #1f2937; }
    .detail-item .value.amount { color: #059669; }
    .status-badge { padding: 0.25rem 0.75rem; border-radius: 12px; font-size: 0.75rem; font-weight: 600; text-transform: uppercase; background: #dbeafe; color: #1e40af; }
    .payment-status.approved { color: #10b981; }
    .payment-status.proof_submitted { color: #f59e0b; }
    .payment-status.under_review { color: #3b82f6; }
    .payment-status.rejected { color: #ef4444; }
    .btn-view-proof { background: #3b82f6; color: white; border: none; padding: 0.5rem 0.75rem; border-radius: 6px; cursor: pointer; font-size: 0.8rem; }
    .btn-view-proof:hover { background: #2563eb; }
    .documents-list { display: flex; flex-direction: column; gap: 0.5rem; }
    .document-item { display: flex; justify-content: space-between; align-items: center; padding: 0.75rem; background: #f8fafc; border-radius: 8px; }
    .doc-info .doc-name { font-weight: 500; color: #1f2937; }
    .doc-info .doc-type { font-size: 0.8rem; color: #6b7280; }
    .doc-actions { display: flex; align-items: center; gap: 0.5rem; }
    .verified { color: #10b981; }
    .pending { color: #f59e0b; }
    .btn-view-doc { background: #6b7280; color: white; border: none; padding: 0.5rem; border-radius: 6px; cursor: pointer; }
    .btn-view-doc:hover { background: #4b5563; }
    @media (max-width: 768px) {
        .detail-grid { grid-template-columns: 1fr; }
        .detail-item { flex-direction: column; align-items: flex-start; gap: 0.25rem; }
    }
    </style>';
    
    echo json_encode(['success' => true, 'html' => $html]);
    
} catch (Exception $e) {
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>