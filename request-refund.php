<?php
session_start();
require_once 'config/database.php';

header('Content-Type: application/json');

// Debug: Log all received data
error_log('POST data: ' . print_r($_POST, true));
error_log('Session user_id: ' . ($_SESSION['user_id'] ?? 'not set'));

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$appId = $_POST['application_id'] ?? null;
$amount = $_POST['amount'] ?? null;
$reason = $_POST['reason'] ?? null;
$bankName = $_POST['bank_name'] ?? null;
$accountNumber = $_POST['account_number'] ?? null;
$accountName = $_POST['account_name'] ?? null;

// Debug: Log individual values
error_log("appId: $appId, amount: $amount, reason: $reason, bankName: $bankName, accountNumber: $accountNumber, accountName: $accountName");

if (!$appId || !$amount || !$reason || !$bankName || !$accountNumber || !$accountName) {
    $missing = [];
    if (!$appId) $missing[] = 'application_id';
    if (!$amount) $missing[] = 'amount';
    if (!$reason) $missing[] = 'reason';
    if (!$bankName) $missing[] = 'bank_name';
    if (!$accountNumber) $missing[] = 'account_number';
    if (!$accountName) $missing[] = 'account_name';
    
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing: ' . implode(', ', $missing)]);
    exit;
}

try {
    $pdo = getConnection();
    
    // Check if application exists and belongs to user
    $stmt = $pdo->prepare("SELECT id, refund_status FROM applications WHERE id = ? AND user_id = ?");
    $stmt->execute([$appId, $_SESSION['user_id']]);
    $app = $stmt->fetch();
    
    if (!$app) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Application not found']);
        exit;
    }
    
    if ($app['refund_status'] !== 'none') {
        http_response_code(409);
        echo json_encode(['success' => false, 'message' => 'Refund already requested']);
        exit;
    }
    
    // Insert refund request
    $stmt = $pdo->prepare("
        INSERT INTO refund_requests (application_id, user_id, refund_amount, reason, bank_name, account_number, account_name)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    
    $stmt->execute([
        $appId,
        $_SESSION['user_id'],
        $amount,
        $reason,
        $bankName,
        $accountNumber,
        $accountName
    ]);
    
    // Update application refund status
    $pdo->prepare("UPDATE applications SET refund_status = 'requested' WHERE id = ?")->execute([$appId]);
    
    echo json_encode(['success' => true, 'message' => 'Refund request submitted successfully']);
    
} catch (Exception $e) {
    error_log('Refund error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error']);
}
?>