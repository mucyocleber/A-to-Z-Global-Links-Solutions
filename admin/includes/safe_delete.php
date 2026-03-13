<?php
/**
 * Safe Application Deletion Helper
 * Manually deletes all related records before deleting the application
 * Use this when database CASCADE constraints are not available
 */

function safeDeleteApplication($pdo, $applicationId) {
    try {
        $pdo->beginTransaction();
        
        // Delete in correct order to avoid foreign key violations
        
        // 1. Payment installments (depends on payments and applications)
        $pdo->prepare("DELETE FROM payment_installments WHERE application_id = ?")->execute([$applicationId]);
        
        // 2. Payments (depends on applications)
        $pdo->prepare("DELETE FROM payments WHERE application_id = ?")->execute([$applicationId]);
        
        // 3. Documents (depends on applications)
        $pdo->prepare("DELETE FROM documents WHERE application_id = ?")->execute([$applicationId]);
        
        // 4. Application messages (depends on applications)
        $pdo->prepare("DELETE FROM application_messages WHERE application_id = ?")->execute([$applicationId]);
        
        // 5. Status history (depends on applications)
        $pdo->prepare("DELETE FROM application_status_history WHERE application_id = ?")->execute([$applicationId]);
        
        // 6. Refund requests (depends on applications)
        $pdo->prepare("DELETE FROM refund_requests WHERE application_id = ?")->execute([$applicationId]);
        
        // 7. Finally delete the application itself
        $pdo->prepare("DELETE FROM applications WHERE id = ?")->execute([$applicationId]);
        
        $pdo->commit();
        return ['success' => true, 'message' => 'Application deleted successfully'];
        
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
    }
}

/**
 * Safe User Deletion Helper
 */
function safeDeleteUser($pdo, $userId) {
    try {
        $pdo->beginTransaction();
        
        // Get all applications for this user
        $apps = $pdo->prepare("SELECT id FROM applications WHERE user_id = ?");
        $apps->execute([$userId]);
        $applicationIds = $apps->fetchAll(PDO::FETCH_COLUMN);
        
        // Delete each application's related records
        foreach ($applicationIds as $appId) {
            // Payment installments
            $pdo->prepare("DELETE FROM payment_installments WHERE application_id = ?")->execute([$appId]);
            // Payments
            $pdo->prepare("DELETE FROM payments WHERE application_id = ?")->execute([$appId]);
            // Documents
            $pdo->prepare("DELETE FROM documents WHERE application_id = ?")->execute([$appId]);
            // Application messages
            $pdo->prepare("DELETE FROM application_messages WHERE application_id = ?")->execute([$appId]);
            // Status history
            $pdo->prepare("DELETE FROM application_status_history WHERE application_id = ?")->execute([$appId]);
            // Refund requests
            $pdo->prepare("DELETE FROM refund_requests WHERE application_id = ?")->execute([$appId]);
        }
        
        // Delete all applications
        $pdo->prepare("DELETE FROM applications WHERE user_id = ?")->execute([$userId]);
        
        // Delete payments by user_id (not linked to applications)
        $pdo->prepare("DELETE FROM payments WHERE user_id = ?")->execute([$userId]);
        
        // Delete refund requests by user_id
        $pdo->prepare("DELETE FROM refund_requests WHERE user_id = ?")->execute([$userId]);
        
        // Delete user's chat messages
        $pdo->prepare("DELETE FROM chat_messages WHERE user_id = ?")->execute([$userId]);
        
        // Finally delete the user
        $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$userId]);
        
        $pdo->commit();
        return ['success' => true, 'message' => 'User deleted successfully'];
        
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
    }
}
