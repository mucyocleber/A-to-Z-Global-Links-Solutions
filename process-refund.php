<?php
session_start();
require_once 'config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

$appId = trim($_POST['application_id'] ?? '');
$amount = trim($_POST['refund_amount'] ?? '');
$reason = trim($_POST['reason'] ?? '');
$bankName = trim($_POST['bank_name'] ?? '');
$accountNumber = trim($_POST['account_number'] ?? '');
$accountName = trim($_POST['account_name'] ?? '');

if (empty($appId) || empty($amount) || empty($reason) || empty($bankName) || empty($accountNumber) || empty($accountName)) {
    echo json_encode(['success' => false, 'message' => 'All fields are required']);
    exit;
}

try {
    $pdo = getConnection();
    $pdo->beginTransaction();
    
    $stmt = $pdo->prepare("SELECT id, total_amount, refund_status FROM applications WHERE id = ? AND user_id = ?");
    $stmt->execute([$appId, $_SESSION['user_id']]);
    $app = $stmt->fetch();
    
    if (!$app) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => 'Application not found']);
        exit;
    }
    
    if ($app['refund_status'] !== 'none') {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => 'Refund already requested for this application']);
        exit;
    }
    
    $stmt = $pdo->prepare("
        INSERT INTO refund_requests (application_id, user_id, refund_amount, reason, bank_name, account_number, account_name, status, requested_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, 'pending', NOW())
    ");
    $stmt->execute([$appId, $_SESSION['user_id'], $amount, $reason, $bankName, $accountNumber, $accountName]);
    
    $stmt = $pdo->prepare("UPDATE applications SET refund_status = 'requested' WHERE id = ?");
    $stmt->execute([$appId]);
    
    $pdo->commit();
    echo json_encode(['success' => true, 'message' => 'Refund request submitted successfully']);
    
} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => 'Database error occurred']);
}
