<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

require_once '../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo = getConnection();
        
        $applicationId = $_POST['application_id'] ?? null;
        
        if (!$applicationId) {
            echo json_encode(['success' => false, 'message' => 'Application ID is required']);
            exit;
        }
        
        // Handle file upload
        if (!isset($_FILES['payment_proof']) || $_FILES['payment_proof']['error'] !== 0) {
            echo json_encode(['success' => false, 'message' => 'Please select a valid file']);
            exit;
        }
        
        $uploadDir = '../../uploads/payments/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $file = $_FILES['payment_proof'];
        $fileName = uniqid() . '.' . pathinfo($file['name'], PATHINFO_EXTENSION);
        $filePath = $uploadDir . $fileName;
        
        if (move_uploaded_file($file['tmp_name'], $filePath)) {
            // Get application details
            $stmt = $pdo->prepare("SELECT payment_plan, initial_payment_status, final_payment_status, initial_payment_proof, final_payment_proof FROM applications WHERE id = ?");
            $stmt->execute([$applicationId]);
            $app = $stmt->fetch();
            
            if ($app) {
                // For partial payments, check if initial payment is already completed
                if ($app['payment_plan'] === 'partial') {
                    if ($app['initial_payment_status'] === 'completed' && $app['final_payment_status'] === 'not_required') {
                        // This is for final payment
                        $updateStmt = $pdo->prepare("
                            UPDATE applications SET 
                            final_payment_proof = ?, 
                            payment_status = 'proof_submitted',
                            final_payment_status = 'pending'
                            WHERE id = ?
                        ");
                        $updateStmt->execute(['uploads/payments/' . $fileName, $applicationId]);
                    } else {
                        // This is for initial payment
                        $updateStmt = $pdo->prepare("
                            UPDATE applications SET 
                            initial_payment_proof = ?, 
                            payment_status = 'proof_submitted',
                            initial_payment_status = 'pending'
                            WHERE id = ?
                        ");
                        $updateStmt->execute(['uploads/payments/' . $fileName, $applicationId]);
                    }
                } else {
                    // Full payment - always goes to initial_payment_proof
                    $updateStmt = $pdo->prepare("
                        UPDATE applications SET 
                        initial_payment_proof = ?, 
                        payment_status = 'proof_submitted',
                        initial_payment_status = 'pending',
                        final_payment_status = 'not_required'
                        WHERE id = ?
                    ");
                    $updateStmt->execute(['uploads/payments/' . $fileName, $applicationId]);
                }
            }
            
            echo json_encode(['success' => true, 'message' => 'Payment proof uploaded successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to upload file']);
        }
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>