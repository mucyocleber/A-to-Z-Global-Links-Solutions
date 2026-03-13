<?php
session_start();
require_once '../../config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

try {
    $pdo = getConnection();
    $pdo->beginTransaction();
    
    // Generate unique application number
    $year = date('Y');
    $stmt = $pdo->prepare("SELECT application_number FROM applications WHERE application_number LIKE ? ORDER BY application_number DESC LIMIT 1");
    $stmt->execute(["ATZ{$year}%"]);
    $lastApp = $stmt->fetch();
    
    if ($lastApp) {
        $lastNumber = intval(substr($lastApp['application_number'], -4));
        $newNumber = $lastNumber + 1;
    } else {
        $newNumber = 1;
    }
    
    $applicationNumber = sprintf("ATZ%s%04d", $year, $newNumber);
    
    // Verify uniqueness
    $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM applications WHERE application_number = ?");
    $checkStmt->execute([$applicationNumber]);
    if ($checkStmt->fetchColumn() > 0) {
        throw new Exception("Application number already exists");
    }
    
    // Get service details
    $serviceStmt = $pdo->prepare("SELECT * FROM services WHERE id = ?");
    $serviceStmt->execute([$_POST['service_id']]);
    $service = $serviceStmt->fetch();
    
    if (!$service) {
        throw new Exception("Service not found");
    }
    
    // Calculate amounts
    $baseAmount = floatval($_POST['base_amount']) ?: $service['base_price'];
    $additionalFees = floatval($_POST['additional_fees']) ?: 0;
    $discountAmount = floatval($_POST['discount_amount']) ?: 0;
    $totalAmount = $baseAmount + $additionalFees - $discountAmount;
    
    $paymentPlan = $_POST['payment_plan'] ?: 'full';
    $initialPaymentAmount = 0;
    $remainingPaymentAmount = 0;
    $initialPaymentPercentage = 0;
    $remainingPaymentPercentage = 0;
    
    if ($paymentPlan === 'partial') {
        $initialPaymentAmount = floatval($_POST['initial_payment_amount']) ?: ($totalAmount * 0.5);
        $remainingPaymentAmount = $totalAmount - $initialPaymentAmount;
        $initialPaymentPercentage = ($initialPaymentAmount / $totalAmount) * 100;
        $remainingPaymentPercentage = ($remainingPaymentAmount / $totalAmount) * 100;
    } else {
        $initialPaymentAmount = $totalAmount;
        $initialPaymentPercentage = 100;
    }
    
    // Insert application
    $insertStmt = $pdo->prepare("
        INSERT INTO applications (
            application_number, application_type, user_id, service_id, status_id,
            destination_country_id, travel_purpose, intended_travel_date, duration_of_stay,
            base_amount, additional_fees, discount_amount, total_amount,
            payment_plan, initial_payment_amount, remaining_payment_amount,
            initial_payment_percentage, remaining_payment_percentage,
            initial_payment_status, final_payment_status, currency, internal_notes
        ) VALUES (
            ?, 'manual', ?, ?, 1,
            ?, ?, ?, ?,
            ?, ?, ?, ?,
            ?, ?, ?,
            ?, ?,
            'pending', ?, ?, ?
        )
    ");
    
    $finalPaymentStatus = ($paymentPlan === 'partial') ? 'pending' : 'not_required';
    
    $insertStmt->execute([
        $applicationNumber,
        $_POST['user_id'],
        $_POST['service_id'],
        $_POST['destination_country_id'] ?: null,
        $_POST['travel_purpose'] ?: null,
        $_POST['intended_travel_date'] ?: null,
        $_POST['duration_of_stay'] ?: null,
        $baseAmount,
        $additionalFees,
        $discountAmount,
        $totalAmount,
        $paymentPlan,
        $initialPaymentAmount,
        $remainingPaymentAmount,
        $initialPaymentPercentage,
        $remainingPaymentPercentage,
        $finalPaymentStatus,
        $service['currency'],
        $_POST['internal_notes'] ?: null
    ]);
    
    $applicationId = $pdo->lastInsertId();
    
    // Create payment installments
    if ($paymentPlan === 'partial') {
        // Initial payment
        $pdo->prepare("
            INSERT INTO payment_installments (
                application_id, installment_number, amount, due_date, status, payment_type
            ) VALUES (?, 1, ?, DATE_ADD(NOW(), INTERVAL 7 DAY), 'pending', 'initial')
        ")->execute([$applicationId, $initialPaymentAmount]);
        
        // Final payment
        $pdo->prepare("
            INSERT INTO payment_installments (
                application_id, installment_number, amount, due_date, status, payment_type
            ) VALUES (?, 2, ?, DATE_ADD(NOW(), INTERVAL 30 DAY), 'pending', 'final')
        ")->execute([$applicationId, $remainingPaymentAmount]);
    } else {
        // Single full payment
        $pdo->prepare("
            INSERT INTO payment_installments (
                application_id, installment_number, amount, due_date, status, payment_type
            ) VALUES (?, 1, ?, DATE_ADD(NOW(), INTERVAL 7 DAY), 'pending', 'full')
        ")->execute([$applicationId, $totalAmount]);
    }
    
    $pdo->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Application created successfully',
        'application_number' => $applicationNumber,
        'application_id' => $applicationId
    ]);
    
} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>