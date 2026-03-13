<?php
// This would be called when admin approves an application with partial payment
function notifyFinalPayment($applicationId) {
    try {
        $pdo = getConnection();
        
        // Get application and user details
        $stmt = $pdo->prepare("
            SELECT a.*, u.email, u.first_name, s.name as service_name
            FROM applications a
            JOIN users u ON a.user_id = u.id
            JOIN services s ON a.service_id = s.id
            WHERE a.id = ? AND a.payment_plan = 'partial' AND a.initial_payment_status = 'completed'
        ");
        $stmt->execute([$applicationId]);
        $application = $stmt->fetch();
        
        if ($application) {
            // Update final payment due date
            $stmt = $pdo->prepare("
                UPDATE payment_installments 
                SET due_date = DATE_ADD(NOW(), INTERVAL 7 DAY)
                WHERE application_id = ? AND installment_number = 2
            ");
            $stmt->execute([$applicationId]);
            
            // Send notification email (implement your email system here)
            $subject = "Final Payment Required - Application Approved";
            $message = "
                Dear {$application['first_name']},
                
                Great news! Your application #{$application['application_number']} for {$application['service_name']} has been approved.
                
                To complete the process, please make your final payment of " . number_format($application['remaining_payment_amount']) . " RWF within 7 days.
                
                You can make the payment by visiting: " . $_SERVER['HTTP_HOST'] . "/application-payment.php?app={$applicationId}
                
                Thank you for choosing A to Z Global Link Solutions.
            ";
            
            // mail($application['email'], $subject, $message); // Uncomment when email is configured
            
            return true;
        }
        
        return false;
        
    } catch (Exception $e) {
        error_log("Final payment notification error: " . $e->getMessage());
        return false;
    }
}
?>