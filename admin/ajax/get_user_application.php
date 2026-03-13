<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

require_once '../../config/database.php';

if (!isset($_POST['user_id'])) {
    echo json_encode(['error' => 'Missing user ID']);
    exit;
}

$user_id = (int)$_POST['user_id'];

try {
    $pdo = getConnection();
    
    $stmt = $pdo->prepare("
        SELECT a.id as application_id, a.application_number,
               p.proof_of_payment, p.payment_status
        FROM applications a 
        LEFT JOIN payments p ON a.id = p.application_id
        WHERE a.user_id = ? 
        ORDER BY a.submitted_at DESC 
        LIMIT 1
    ");
    
    $stmt->execute([$user_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result) {
        echo json_encode([
            'success' => true, 
            'application_id' => $result['application_id'],
            'application_number' => $result['application_number'],
            'proof_of_payment' => $result['proof_of_payment'],
            'payment_status' => $result['payment_status']
        ]);
    } else {
        echo json_encode(['error' => 'No application found']);
    }
    
} catch (Exception $e) {
    echo json_encode(['error' => 'Database error']);
}
?>