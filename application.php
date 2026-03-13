<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session first
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'config/database.php';

// Handle form submission
if ($_POST) {
    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        // Store form data in session and redirect to login
        $_SESSION['application_data'] = $_POST;
        header('Location: login?redirect=application&action=submit');
        exit;
    }
    
    try {
        $pdo = getConnection();
        $pdo->beginTransaction();
        
        $userId = $_SESSION['user_id'];
        
        // Validate service_id
        if (empty($_POST['service_id'])) {
            throw new Exception("Please select a service");
        }
        
        // Get service details
        $stmt = $pdo->prepare("SELECT * FROM services WHERE id = ? AND is_active = 1");
        $stmt->execute([$_POST['service_id']]);
        $service = $stmt->fetch();
        
        if (!$service) {
            throw new Exception("Selected service not found or inactive");
        }
        
        // Get destination country ID
        $destinationCountryId = null;
        if (!empty($_POST['destination_country'])) {
            $stmt = $pdo->prepare("SELECT id FROM countries WHERE name = ?");
            $stmt->execute([$_POST['destination_country']]);
            $country = $stmt->fetch();
            $destinationCountryId = $country ? $country['id'] : null;
        }
        
        // Generate application number
        $appNumber = 'ATZ' . date('Y') . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
        
        // Create application with online type (user-submitted)
        $stmt = $pdo->prepare("
            INSERT INTO applications (
                application_number, application_type, user_id, service_id, status_id, destination_country_id,
                travel_purpose, intended_travel_date, duration_of_stay, base_amount, total_amount,
                payment_plan, initial_payment_amount, remaining_payment_amount, currency,
                initial_payment_percentage, remaining_payment_percentage,
                initial_payment_status, final_payment_status, refund_status,
                application_data, submitted_at
            ) VALUES (?, 'online', ?, ?, 1, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', ?, 'none', ?, NOW())
        ");
        
        $applicationData = json_encode([
            'additional_info' => $_POST['additional_info'] ?? null
        ]);
        
        // Process payment plan and amounts
        $paymentPlan = $_POST['payment_plan'] ?? 'full';
        $totalAmount = $service['base_price'];
        $currency = $service['currency'] ?? 'USD';
        $initialPaymentAmount = 0;
        $remainingPaymentAmount = 0;
        
        if ($paymentPlan === 'full') {
            $initialPaymentAmount = $totalAmount;
            $remainingPaymentAmount = 0;
            $finalPaymentStatus = 'not_required';
        } else {
            // Handle both 'partial' and 'custom' as partial payments
            if (isset($_POST['is_custom_payment']) && $_POST['is_custom_payment'] === '1') {
                $customAmount = floatval($_POST['custom_amount'] ?? 0);
                if ($customAmount <= 0 || $customAmount >= $totalAmount) {
                    throw new Exception("Custom amount must be between 1 and " . ($totalAmount - 1));
                }
                $initialPaymentAmount = $customAmount;
                $remainingPaymentAmount = $totalAmount - $customAmount;
                // Store as 'partial' in database since enum only supports 'full' and 'partial'
                $paymentPlan = 'partial';
            } else {
                // Standard 50/50 partial payment
                $initialPaymentAmount = $totalAmount / 2;
                $remainingPaymentAmount = $totalAmount / 2;
            }
            $finalPaymentStatus = 'pending';
        }
        
        // Calculate percentages
        $initialPercentage = $totalAmount > 0 ? ($initialPaymentAmount / $totalAmount) * 100 : 0;
        $remainingPercentage = 100 - $initialPercentage;
        
        $stmt->execute([
            $appNumber,
            $userId,
            (int)$_POST['service_id'],
            $destinationCountryId,
            $_POST['travel_purpose'] ?? null,
            $_POST['travel_date'] ?: null,
            $_POST['duration_of_stay'] ?? null,
            $service['base_price'],
            $totalAmount,
            $paymentPlan,
            $initialPaymentAmount,
            $remainingPaymentAmount,
            $currency,
            $initialPercentage,
            $remainingPercentage,
            $finalPaymentStatus,
            $applicationData
        ]);
        
        $applicationId = $pdo->lastInsertId();
        
        // Create payment installments based on plan
        if ($paymentPlan === 'partial') {
            // First installment (initial payment)
            $stmt = $pdo->prepare("
                INSERT INTO payment_installments (application_id, installment_number, amount, status)
                VALUES (?, 1, ?, 'pending')
            ");
            $stmt->execute([$applicationId, $initialPaymentAmount]);
            
            // Second installment (remaining payment)
            $stmt = $pdo->prepare("
                INSERT INTO payment_installments (application_id, installment_number, amount, status)
                VALUES (?, 2, ?, 'pending')
            ");
            $stmt->execute([$applicationId, $remainingPaymentAmount]);
        }
        
        $pdo->commit();
        
        // Clear stored application data
        unset($_SESSION['application_data']);
        
        // Redirect to document upload step
        header("Location: application-documents?app=$applicationId");
        exit;
        
    } catch (Exception $e) {
        $pdo->rollback();
        $error = "Error submitting application: " . $e->getMessage();
        error_log("Application submission error: " . $e->getMessage());
    }
}

// Restore form data if coming back from login
$formData = $_SESSION['application_data'] ?? [];
if (isset($_GET['action']) && $_GET['action'] === 'submit' && !empty($formData) && isset($_SESSION['user_id'])) {
    // Process the stored form data by simulating POST
    $_POST = $formData;
    // The form processing logic at the top will handle this
}

try {
    $pdo = getConnection();
    $services = $pdo->query("
        SELECT s.*, sc.name as category_name, s.currency 
        FROM services s 
        LEFT JOIN service_categories sc ON s.category_id = sc.id 
        WHERE s.is_active = 1 
        ORDER BY sc.sort_order, s.sort_order, s.name
    ")->fetchAll();
    
    $countries = $pdo->query("SELECT * FROM countries WHERE is_active = 1 ORDER BY name")->fetchAll();
    
    // Get service requirements if service selected
    $requirements = [];
    if (isset($_GET['service'])) {
        $stmt = $pdo->prepare("
            SELECT * FROM service_requirements 
            WHERE service_id = ? 
            ORDER BY is_mandatory DESC, sort_order, name
        ");
        $stmt->execute([$_GET['service']]);
        $requirements = $stmt->fetchAll();
    }
    
} catch (Exception $e) {
    echo "Database Error: " . $e->getMessage();
    $services = [];
    $countries = [];
    $requirements = [];
}

$selectedService = null;
if (isset($_GET['service'])) {
    foreach ($services as $service) {
        if ($service['id'] == $_GET['service']) {
            $selectedService = $service;
            break;
        }
    }
}

$pageTitle = 'Apply for Service - A to Z Global Link Solutions';
include 'includes/header.php';
?>

<style>
.main-content {
    margin-top: 70px;
    background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
    min-height: calc(100vh - 70px);
}

.hero-application {
    padding: 4rem 5% 3rem 5%;
    background: linear-gradient(135deg, #0f172a 0%, #1e293b 25%, #1e40af 75%, #3b82f6 100%);
    color: white;
    text-align: center;
    position: relative;
    overflow: hidden;
}

.hero-application::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -20%;
    width: 400px;
    height: 400px;
    background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, transparent 70%);
    border-radius: 50%;
    animation: float 6s ease-in-out infinite;
}

.hero-application::after {
    content: '';
    position: absolute;
    bottom: -30%;
    left: -10%;
    width: 300px;
    height: 300px;
    background: radial-gradient(circle, rgba(255, 255, 255, 0.08) 0%, transparent 70%);
    border-radius: 50%;
    animation: float 8s ease-in-out infinite reverse;
}

@keyframes float {
    0%, 100% { transform: translateY(0px) rotate(0deg); }
    50% { transform: translateY(-20px) rotate(5deg); }
}

.hero-application h1 {
    font-size: clamp(2.5rem, 5vw, 3.5rem);
    font-weight: 900;
    margin-bottom: 1.5rem;
    position: relative;
    z-index: 2;
    text-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
}

.hero-application p {
    font-size: clamp(1.1rem, 2.5vw, 1.4rem);
    max-width: 700px;
    margin: 0 auto;
    opacity: 0.9;
    position: relative;
    z-index: 2;
    line-height: 1.6;
}

.application-section {
    padding: 4rem 5%;
    background: white;
}

.application-container {
    max-width: 1000px;
    margin: 0 auto;
}

.step-indicator {
    display: flex;
    justify-content: center;
    align-items: center;
    margin-bottom: 3rem;
    background: linear-gradient(135deg, rgba(59, 130, 246, 0.05) 0%, rgba(147, 197, 253, 0.08) 100%);
    padding: 2rem;
    border-radius: 24px;
    border: 2px solid rgba(59, 130, 246, 0.1);
}

.step {
    display: flex;
    align-items: center;
    position: relative;
}

.step-number {
    width: 45px;
    height: 45px;
    background: rgba(59, 130, 246, 0.2);
    color: #3b82f6;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 800;
    margin-right: 1rem;
    font-size: 1rem;
    transition: all 0.3s ease;
    border: 2px solid rgba(59, 130, 246, 0.3);
}

.step.active .step-number {
    background: linear-gradient(135deg, #3b82f6, #1d4ed8);
    color: white;
    border-color: #3b82f6;
    box-shadow: 0 8px 25px rgba(59, 130, 246, 0.3);
    transform: scale(1.1);
}

.step-line {
    width: 60px;
    height: 3px;
    background: linear-gradient(90deg, rgba(59, 130, 246, 0.2), rgba(59, 130, 246, 0.1));
    margin: 0 1rem;
    border-radius: 2px;
}

.step-text {
    font-size: 1rem;
    color: #64748b;
    font-weight: 600;
    white-space: nowrap;
}

.step.active .step-text {
    color: #1e40af;
    font-weight: 700;
}

.application-form {
    background: white;
    padding: 3rem;
    border-radius: 24px;
    box-shadow: 0 20px 60px rgba(59, 130, 246, 0.1);
    border: 2px solid rgba(59, 130, 246, 0.1);
    position: relative;
    overflow: hidden;
}

.application-form::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 4px;
    background: linear-gradient(135deg, #3b82f6, #1d4ed8);
}

.form-section {
    margin-bottom: 3rem;
}

.form-section h3 {
    color: #1e40af;
    font-size: 1.5rem;
    font-weight: 800;
    margin-bottom: 2rem;
    padding-bottom: 0.75rem;
    border-bottom: 2px solid rgba(59, 130, 246, 0.1);
    position: relative;
}

.form-section h3::after {
    content: '';
    position: absolute;
    bottom: -2px;
    left: 0;
    width: 60px;
    height: 2px;
    background: linear-gradient(135deg, #3b82f6, #1d4ed8);
}

.service-scroll-wrapper {
    position: relative;
    margin-bottom: 2rem;
}

.scroll-btn {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    width: 48px;
    height: 48px;
    background: linear-gradient(135deg, #3b82f6, #1d4ed8);
    border: none;
    border-radius: 50%;
    color: white;
    font-size: 1.2rem;
    cursor: pointer;
    z-index: 10;
    box-shadow: 0 8px 25px rgba(59, 130, 246, 0.4);
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
}

.scroll-btn:hover {
    background: linear-gradient(135deg, #1d4ed8, #1e40af);
    transform: translateY(-50%) scale(1.1);
    box-shadow: 0 12px 35px rgba(59, 130, 246, 0.5);
}

.scroll-btn:active {
    transform: translateY(-50%) scale(0.95);
}

.scroll-left {
    left: -24px;
}

.scroll-right {
    right: -24px;
}

.service-selection {
    display: flex;
    gap: 1.5rem;
    overflow-x: auto;
    overflow-y: hidden;
    padding: 1rem 0.5rem;
    scroll-behavior: smooth;
    -webkit-overflow-scrolling: touch;
    scrollbar-width: thin;
    scrollbar-color: #3b82f6 #e2e8f0;
}

.service-selection::-webkit-scrollbar {
    height: 8px;
}

.service-selection::-webkit-scrollbar-track {
    background: #e2e8f0;
    border-radius: 10px;
}

.service-selection::-webkit-scrollbar-thumb {
    background: linear-gradient(135deg, #3b82f6, #1d4ed8);
    border-radius: 10px;
}

.service-selection::-webkit-scrollbar-thumb:hover {
    background: linear-gradient(135deg, #1d4ed8, #1e40af);
}

.service-card {
    background: white;
    border: 2px solid rgba(59, 130, 246, 0.15);
    border-radius: 20px;
    padding: 2rem 1.5rem;
    cursor: pointer;
    transition: all 0.4s ease;
    position: relative;
    overflow: hidden;
    display: block;
    min-width: 280px;
    max-width: 280px;
    flex-shrink: 0;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}

.service-card input[type="radio"] {
    display: none;
}

.service-card::before {
    content: '';
    position: absolute;
    top: -50%;
    left: -50%;
    width: 200%;
    height: 200%;
    background: linear-gradient(45deg, transparent, rgba(59, 130, 246, 0.1), transparent);
    transform: rotate(45deg);
    transition: all 0.6s ease;
    opacity: 0;
}

.service-card:hover::before {
    opacity: 1;
    transform: rotate(45deg) translate(50%, 50%);
}

.service-card:hover {
    border-color: #3b82f6;
    background: rgba(59, 130, 246, 0.02);
    transform: translateY(-5px) scale(1.02);
    box-shadow: 0 20px 40px rgba(59, 130, 246, 0.15);
}

.service-card:has(input:checked) {
    border-color: #3b82f6;
    background: rgba(59, 130, 246, 0.05);
    transform: translateY(-5px) scale(1.02);
    box-shadow: 0 20px 40px rgba(59, 130, 246, 0.2);
}

.service-card:has(input:checked)::after {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 4px;
    background: linear-gradient(135deg, #3b82f6, #1d4ed8);
}

.service-checkbox {
    position: absolute;
    top: 15px;
    right: 15px;
    width: 28px;
    height: 28px;
    border: 2px solid #3b82f6;
    border-radius: 50%;
    background: white;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
    z-index: 2;
}

.service-card input[type="radio"]:checked + .service-checkbox {
    background: linear-gradient(135deg, #3b82f6, #1d4ed8);
    box-shadow: 0 4px 15px rgba(59, 130, 246, 0.3);
}

.service-card input[type="radio"]:checked + .service-checkbox i {
    opacity: 1;
    transform: scale(1.2);
}

.service-checkbox i {
    color: white;
    font-size: 14px;
    opacity: 0;
    transition: all 0.3s ease;
}

.service-header {
    text-align: center;
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
}

.service-icon {
    width: 60px;
    height: 60px;
    background: linear-gradient(135deg, #3b82f6, #1d4ed8);
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.5rem;
    margin: 0 auto 1rem auto;
    box-shadow: 0 8px 25px rgba(59, 130, 246, 0.3);
    transition: all 0.3s ease;
}

.service-card:hover .service-icon {
    transform: scale(1.1) rotate(5deg);
}

.service-card h4 {
    color: #1e40af;
    font-size: 1.1rem;
    font-weight: 800;
    margin-bottom: 0.75rem;
    line-height: 1.2;
}

.service-card p {
    color: #64748b;
    font-size: 0.85rem;
    line-height: 1.4;
    margin-bottom: 1rem;
    flex: 1;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    text-overflow: ellipsis;
}

.service-price {
    color: #3b82f6;
    font-weight: 900;
    font-size: 1.1rem;
    text-align: center;
    margin-top: auto;
    padding-top: 0.5rem;
    border-top: 1px solid rgba(59, 130, 246, 0.1);
}

.service-details {
    background: linear-gradient(135deg, rgba(59, 130, 246, 0.05) 0%, rgba(147, 197, 253, 0.08) 100%);
    border-radius: 20px;
    padding: 2.5rem;
    margin-top: 2rem;
    display: none;
    border: 2px solid rgba(59, 130, 246, 0.1);
}

.service-details.show {
    display: block;
    animation: slideDown 0.4s ease-out;
}

@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.service-details h4 {
    color: #1e40af;
    font-size: 1.4rem;
    font-weight: 800;
    margin-bottom: 1.5rem;
}

.service-description {
    background: white;
    padding: 2rem;
    border-radius: 16px;
    margin-bottom: 2rem;
    box-shadow: 0 4px 15px rgba(59, 130, 246, 0.08);
    border-left: 4px solid #3b82f6;
}

.service-description h5 {
    color: #1e40af;
    font-size: 1.2rem;
    font-weight: 700;
    margin-bottom: 1rem;
}

.service-description p {
    color: #374151;
    line-height: 1.7;
    font-size: 1rem;
    margin: 0;
    white-space: pre-line;
}

.details-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.detail-item {
    background: white;
    padding: 1.5rem;
    border-radius: 16px;
    border-left: 4px solid #3b82f6;
    box-shadow: 0 4px 15px rgba(59, 130, 246, 0.08);
    transition: all 0.3s ease;
}

.detail-item:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(59, 130, 246, 0.15);
}

.detail-label {
    font-size: 0.85rem;
    color: #64748b;
    font-weight: 600;
    margin-bottom: 0.5rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.detail-value {
    color: #1e40af;
    font-weight: 700;
    font-size: 1.1rem;
}

.requirements-list {
    background: white;
    padding: 2rem;
    border-radius: 16px;
    margin-top: 1.5rem;
    box-shadow: 0 4px 15px rgba(59, 130, 246, 0.08);
}

.requirements-list h5 {
    color: #1e40af;
    margin-bottom: 1.5rem;
    font-size: 1.2rem;
    font-weight: 700;
}

.requirement-item {
    display: flex;
    align-items: flex-start;
    gap: 1rem;
    padding: 1rem 0;
    border-bottom: 1px solid rgba(59, 130, 246, 0.1);
    transition: all 0.3s ease;
}

.requirement-item:hover {
    background: rgba(59, 130, 246, 0.02);
    border-radius: 8px;
    margin: 0 -0.5rem;
    padding: 1rem 0.5rem;
}

.requirement-item:last-child {
    border-bottom: none;
}

.req-icon {
    width: 24px;
    height: 24px;
    background: linear-gradient(135deg, #3b82f6, #1d4ed8);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 0.75rem;
    flex-shrink: 0;
    margin-top: 0.25rem;
}

.req-text {
    color: #374151;
    font-size: 0.95rem;
    flex: 1;
    line-height: 1.5;
}

.req-badge {
    background: linear-gradient(135deg, #dbeafe, #bfdbfe);
    color: #1e40af;
    padding: 0.25rem 0.75rem;
    border-radius: 12px;
    font-size: 0.75rem;
    font-weight: 600;
    margin-left: auto;
    border: 1px solid rgba(59, 130, 246, 0.2);
}

.req-badge.optional {
    background: linear-gradient(135deg, #d1fae5, #a7f3d0);
    color: #065f46;
    border-color: rgba(16, 185, 129, 0.2);
}

.payment-plan-options {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 1.5rem;
    margin-top: 1rem;
}

.plan-option {
    border: 2px solid rgba(59, 130, 246, 0.15);
    border-radius: 20px;
    padding: 2rem 1.5rem;
    cursor: pointer;
    transition: all 0.4s ease;
    position: relative;
    overflow: hidden;
    background: white;
    min-height: 280px;
    display: flex;
    flex-direction: column;
}

.plan-option::before {
    content: '';
    position: absolute;
    top: -50%;
    left: -50%;
    width: 200%;
    height: 200%;
    background: linear-gradient(45deg, transparent, rgba(59, 130, 246, 0.05), transparent);
    transform: rotate(45deg);
    transition: all 0.6s ease;
    opacity: 0;
}

.plan-option:hover::before {
    opacity: 1;
    transform: rotate(45deg) translate(50%, 50%);
}

.plan-option:hover,
.plan-option.selected {
    border-color: #3b82f6;
    background: rgba(59, 130, 246, 0.02);
    transform: translateY(-5px);
    box-shadow: 0 15px 35px rgba(59, 130, 246, 0.15);
}

.plan-option.selected::after {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 4px;
    background: linear-gradient(135deg, #3b82f6, #1d4ed8);
}

.plan-option input[type="radio"] {
    display: none;
}

.plan-content {
    flex: 1;
    display: flex;
    flex-direction: column;
    text-align: center;
}

.plan-content h4 {
    color: #1e40af;
    font-size: 1.2rem;
    font-weight: 800;
    margin: 0 0 1rem 0;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
}

.plan-content h4 i {
    font-size: 1.1rem;
    color: #3b82f6;
}

.plan-content p {
    color: #64748b;
    font-size: 0.9rem;
    margin: 0 0 1.5rem 0;
    line-height: 1.5;
    flex: 1;
}

.plan-amount {
    color: #059669;
    font-weight: 800;
    font-size: 1.1rem;
    background: linear-gradient(135deg, #d1fae5, #a7f3d0);
    padding: 0.75rem 1rem;
    border-radius: 12px;
    text-align: center;
    border: 1px solid rgba(16, 185, 129, 0.2);
    margin-bottom: 1rem;
}

.plan-benefits {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    margin-top: auto;
}

.benefit-tag {
    background: rgba(59, 130, 246, 0.1);
    color: #1e40af;
    padding: 0.4rem 0.75rem;
    border-radius: 8px;
    font-size: 0.8rem;
    font-weight: 600;
    border: 1px solid rgba(59, 130, 246, 0.2);
}

.custom-payment-input {
    margin: 1rem 0;
}

.input-group {
    display: flex;
    align-items: center;
    background: white;
    border: 2px solid rgba(59, 130, 246, 0.15);
    border-radius: 12px;
    overflow: hidden;
    transition: all 0.3s ease;
}

.input-group:focus-within {
    border-color: #3b82f6;
    box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
}

.custom-amount-input {
    flex: 1;
    padding: 0.875rem 1rem;
    border: none;
    outline: none;
    font-size: 1rem;
    font-weight: 600;
    color: #1e293b;
}

.currency-label {
    padding: 0.875rem 1rem;
    background: rgba(59, 130, 246, 0.1);
    color: #1e40af;
    font-weight: 700;
    font-size: 0.9rem;
    border-left: 1px solid rgba(59, 130, 246, 0.2);
}

.payment-breakdown {
    background: rgba(59, 130, 246, 0.05);
    border-radius: 8px;
    padding: 1rem;
    margin-top: 1rem;
    border: 1px solid rgba(59, 130, 246, 0.1);
}

.breakdown-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.5rem 0;
    font-size: 0.9rem;
}

.breakdown-item:first-child {
    border-bottom: 1px solid rgba(59, 130, 246, 0.1);
    margin-bottom: 0.5rem;
    padding-bottom: 0.75rem;
}

.breakdown-item span:first-child {
    color: #64748b;
    font-weight: 500;
}

.breakdown-item span:nth-child(2) {
    color: #1e40af;
    font-weight: 700;
}

.percentage {
    color: #059669 !important;
    font-size: 0.8rem !important;
    font-weight: 600 !important;
    margin-left: 0.5rem;
}

.form-row {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 2rem;
    margin-bottom: 2rem;
}

.form-group {
    position: relative;
}

.form-group label {
    display: block;
    color: #1e40af;
    font-weight: 700;
    margin-bottom: 0.75rem;
    font-size: 0.95rem;
}

.form-control {
    width: 100%;
    padding: 1rem 1.25rem;
    border: 2px solid rgba(59, 130, 246, 0.15);
    border-radius: 16px;
    font-size: 1rem;
    transition: all 0.3s ease;
    background: white;
    font-weight: 500;
}

.form-control:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
    transform: translateY(-1px);
}

select.form-control {
    cursor: pointer;
}

textarea.form-control {
    resize: vertical;
    min-height: 120px;
    line-height: 1.6;
}

.btn-submit {
    background: linear-gradient(135deg, #3b82f6, #1d4ed8);
    color: white;
    padding: 1.25rem 3rem;
    border: none;
    border-radius: 50px;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.3s ease;
    font-size: 1.1rem;
    width: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.75rem;
    box-shadow: 0 12px 32px rgba(59, 130, 246, 0.3);
    position: relative;
    overflow: hidden;
}

.btn-submit::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
    transition: left 0.6s ease;
}

.btn-submit:hover::before {
    left: 100%;
}

.btn-submit:hover {
    background: linear-gradient(135deg, #1d4ed8, #1e40af);
    transform: translateY(-3px) scale(1.02);
    box-shadow: 0 16px 40px rgba(59, 130, 246, 0.4);
}

.alert {
    padding: 1.25rem 1.5rem;
    border-radius: 16px;
    margin-bottom: 2rem;
    display: flex;
    align-items: center;
    gap: 1rem;
    font-weight: 600;
    border: 2px solid;
}

.alert-info {
    background: linear-gradient(135deg, #dbeafe, #bfdbfe);
    color: #1e40af;
    border-color: rgba(59, 130, 246, 0.3);
}

.alert-success {
    background: linear-gradient(135deg, #d1fae5, #a7f3d0);
    color: #065f46;
    border-color: rgba(16, 185, 129, 0.3);
}

.alert-error {
    background: linear-gradient(135deg, #fee2e2, #fecaca);
    color: #991b1b;
    border-color: rgba(239, 68, 68, 0.3);
}

.req-required {
    color: #ef4444;
    font-weight: bold;
}

.req-icon i.fa-image {
    color: #10b981;
}

.req-icon i.fa-file-pdf {
    color: #ef4444;
}

@media (max-width: 1024px) {
    .service-selection {
        gap: 1.25rem;
        padding: 1rem 0.25rem;
    }
    
    .service-card {
        min-width: 260px;
        max-width: 260px;
    }
    
    .details-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 1.25rem;
    }
    
    .form-row {
        grid-template-columns: 1fr;
        gap: 1.5rem;
    }
}

@media (max-width: 768px) {
    .main-content {
        margin-top: 60px;
        padding-bottom: 100px;
    }
    
    .hero-application {
        padding: 2.5rem 1rem 2rem 1rem;
        margin-bottom: 1.5rem;
    }
    
    .hero-application h1 {
        font-size: 1.8rem;
        margin-bottom: 1rem;
        line-height: 1.2;
    }
    
    .hero-application p {
        font-size: 1rem;
        padding: 0;
        line-height: 1.5;
    }
    
    .application-section {
        padding: 2rem 1rem;
    }
    
    .application-form {
        padding: 1.5rem 1rem;
        border-radius: 20px;
    }
    
    .step-indicator {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 0.5rem;
        padding: 1.5rem 1rem;
        margin-bottom: 2rem;
    }
    
    .step-line {
        display: none;
    }
    
    .step {
        flex-direction: column;
        text-align: center;
        gap: 0.5rem;
    }
    
    .step-number {
        width: 40px;
        height: 40px;
        margin: 0 auto 0.5rem auto;
        font-size: 1rem;
    }
    
    .step-text {
        font-size: 0.8rem;
        white-space: normal;
        line-height: 1.2;
    }
    
    .form-section h3 {
        font-size: 1.3rem;
        text-align: center;
        margin-bottom: 1.5rem;
    }
    
    .scroll-btn {
        width: 40px;
        height: 40px;
        font-size: 1rem;
    }
    
    .scroll-left {
        left: -20px;
    }
    
    .scroll-right {
        right: -20px;
    }
    
    .service-selection {
        gap: 1rem;
        padding: 1rem 0.25rem;
    }
    
    .service-card {
        min-width: 240px;
        max-width: 240px;
        padding: 1.25rem;
        text-align: center;
    }
    
    .service-icon {
        width: 50px;
        height: 50px;
        font-size: 1.3rem;
        margin: 0 auto 1rem auto;
    }
    
    .service-card h4 {
        font-size: 1rem;
        margin-bottom: 0.75rem;
    }
    
    .service-card p {
        font-size: 0.8rem;
        margin-bottom: 1rem;
        -webkit-line-clamp: 3;
    }
    
    .service-price {
        font-size: 1rem;
        margin-top: 0.75rem;
    }
    
    .service-details {
        padding: 1.5rem 1rem;
        margin-top: 1.5rem;
    }
    
    .service-details h4 {
        font-size: 1.2rem;
        text-align: center;
        margin-bottom: 1.25rem;
    }
    
    .details-grid {
        grid-template-columns: 1fr;
        gap: 1rem;
    }
    
    .detail-item {
        padding: 1.25rem;
        text-align: center;
    }
    
    .detail-label {
        font-size: 0.8rem;
        margin-bottom: 0.75rem;
    }
    
    .detail-value {
        font-size: 1rem;
    }
    
    .requirements-list {
        padding: 1.5rem 1rem;
    }
    
    .requirements-list h5 {
        font-size: 1.1rem;
        text-align: center;
        margin-bottom: 1.25rem;
    }
    
    .requirement-item {
        flex-direction: column;
        text-align: center;
        gap: 0.75rem;
        padding: 1rem 0;
    }
    
    .req-icon {
        margin: 0 auto;
    }
    
    .req-text {
        text-align: center;
        font-size: 0.9rem;
    }
    
    .req-badge {
        margin: 0 auto;
        align-self: center;
    }
    
    .form-row {
        grid-template-columns: 1fr;
        gap: 1.5rem;
    }
    
    .form-control {
        padding: 1rem;
        font-size: 0.95rem;
    }
    
    .payment-plan-options {
        grid-template-columns: 1fr;
        gap: 1rem;
    }
    
    .plan-option {
        padding: 1.5rem;
        text-align: center;
        min-height: auto;
    }
    
    .plan-content h4 {
        font-size: 1rem;
        margin-bottom: 0.75rem;
    }
    
    .plan-content p {
        font-size: 0.85rem;
        margin-bottom: 1rem;
    }
    
    .plan-amount {
        font-size: 0.95rem;
        padding: 0.75rem 1rem;
    }
    
    .btn-submit {
        padding: 1.25rem 2rem;
        font-size: 1rem;
        margin-top: 2rem;
    }
}

@media (max-width: 480px) {
    .hero-application {
        padding: 2rem 0.75rem 1.5rem 0.75rem;
    }
    
    .hero-application h1 {
        font-size: 1.6rem;
        line-height: 1.3;
    }
    
    .hero-application p {
        font-size: 0.95rem;
    }
    
    .application-section {
        padding: 1.5rem 0.75rem;
    }
    
    .application-form {
        padding: 1.25rem 0.75rem;
        border-radius: 16px;
    }
    
    .step-indicator {
        padding: 1.25rem 0.75rem;
        gap: 0.25rem;
    }
    
    .step-number {
        width: 35px;
        height: 35px;
        font-size: 0.9rem;
    }
    
    .step-text {
        font-size: 0.75rem;
    }
    
    .form-section h3 {
        font-size: 1.2rem;
    }
    
    .service-card {
        padding: 1rem;
        min-height: 140px;
    }
    
    .service-icon {
        width: 45px;
        height: 45px;
        font-size: 1.2rem;
        margin-bottom: 0.75rem;
    }
    
    .service-card h4 {
        font-size: 0.95rem;
        margin-bottom: 0.5rem;
    }
    
    .service-card p {
        font-size: 0.75rem;
        margin-bottom: 0.75rem;
        -webkit-line-clamp: 2;
    }
    
    .service-price {
        font-size: 0.9rem;
        margin-top: 0.5rem;
    }
    
    .service-details {
        padding: 1.25rem 0.75rem;
    }
    
    .service-details h4 {
        font-size: 1.1rem;
    }
    
    .detail-item {
        padding: 1rem;
    }
    
    .detail-label {
        font-size: 0.75rem;
    }
    
    .detail-value {
        font-size: 0.95rem;
    }
    
    .requirements-list {
        padding: 1.25rem 0.75rem;
    }
    
    .requirements-list h5 {
        font-size: 1rem;
    }
    
    .requirement-item {
        padding: 0.75rem 0;
    }
    
    .req-text {
        font-size: 0.85rem;
    }
    
    .form-control {
        padding: 0.875rem;
        font-size: 0.9rem;
    }
    
    .plan-option {
        padding: 1rem;
    }
    
    .plan-content h4 {
        font-size: 0.95rem;
    }
    
    .plan-content p {
        font-size: 0.8rem;
    }
    
    .plan-amount {
        font-size: 0.9rem;
        padding: 0.625rem 0.875rem;
    }
    
    .btn-submit {
        padding: 1.125rem 1.75rem;
        font-size: 0.95rem;
    }
}
</style>

<main class="main-content">
    <section class="hero-application">
        <h1>Apply for Our Services</h1>
        <p>Start your visa and immigration journey with our expert assistance</p>
    </section>

    <section class="application-section">
        <div class="application-container">
            <?php if (isset($success)): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?= $success ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i> <?= $error ?>
                </div>
            <?php endif; ?>

            <div class="step-indicator">
                <div class="step active">
                    <div class="step-number">1</div>
                    <span class="step-text">Application Details</span>
                </div>
                <div class="step-line"></div>
                <div class="step">
                    <div class="step-number">2</div>
                    <span class="step-text">Upload Documents</span>
                </div>
                <div class="step-line"></div>
                <div class="step">
                    <div class="step-number">3</div>
                    <span class="step-text">Payment</span>
                </div>
            </div>

            <div class="application-form">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <div class="alert alert-info">
                            <strong>Welcome back, <?= htmlspecialchars($_SESSION['user_name']) ?>!</strong> Complete the form below to submit your application.
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info">
                            <strong>Note:</strong> Fill out the application form below. You'll be asked to login or create an account when you submit.
                        </div>
                    <?php endif; ?>

                <form method="POST">
                    <div class="form-section">
                        <h3>Select Service</h3>
                        <div class="service-scroll-wrapper">
                            <button type="button" class="scroll-btn scroll-left" onclick="scrollServices('left')">
                                <i class="fas fa-chevron-left"></i>
                            </button>
                            <div class="service-selection" id="serviceSelection">
                            <?php if (!empty($services)): ?>
                                <?php foreach ($services as $service): ?>
                                    <label class="service-card" for="service_<?= $service['id'] ?>">
                                        <input type="radio" name="service_id" id="service_<?= $service['id'] ?>" value="<?= $service['id'] ?>" required onchange="loadServiceDetails(<?= $service['id'] ?>)" <?= (isset($_GET['service']) && $_GET['service'] == $service['id']) ? 'checked' : '' ?>>
                                        <div class="service-checkbox">
                                            <i class="fas fa-check"></i>
                                        </div>
                                        <div class="service-header">
                                            <div class="service-icon">
                                                <i class="fas fa-passport"></i>
                                            </div>
                                            <h4><?= htmlspecialchars($service['name']) ?></h4>
                                            <p><?= htmlspecialchars(substr($service['short_description'] ?? 'Professional service with expert guidance', 0, 100)) ?>...</p>
                                        </div>
                                        <div class="service-price"><?= number_format($service['base_price']) ?> <?= htmlspecialchars($service['currency'] ?? 'RWF') ?></div>
                                    </label>
                                <?php endforeach; ?>
                            <?php endif; ?>
                            </div>
                            <button type="button" class="scroll-btn scroll-right" onclick="scrollServices('right')">
                                <i class="fas fa-chevron-right"></i>
                            </button>
                        </div>
                        
                        <div id="service-details" class="service-details">
                            <!-- Service details will be loaded here -->
                        </div>
                    </div>

                    <div class="form-section">
                        <h3>Travel Information</h3>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="destination_country">Destination Country</label>
                                <select id="destination_country" name="destination_country" class="form-control">
                                    <option value="">Select Destination</option>
                                    <?php foreach ($countries as $country): ?>
                                        <option value="<?= htmlspecialchars($country['name']) ?>" <?= !empty($formData['destination_country']) && $formData['destination_country'] == $country['name'] ? 'selected' : '' ?>><?= htmlspecialchars($country['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="travel_date">Intended Travel Date</label>
                                <input type="date" id="travel_date" name="travel_date" class="form-control" value="<?= htmlspecialchars($formData['travel_date'] ?? '') ?>">
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="duration_of_stay">Duration of Stay</label>
                                <input type="text" id="duration_of_stay" name="duration_of_stay" class="form-control" placeholder="e.g., 2 weeks, 3 months" value="<?= htmlspecialchars($formData['duration_of_stay'] ?? '') ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="travel_purpose">Purpose of Travel</label>
                            <textarea id="travel_purpose" name="travel_purpose" class="form-control" placeholder="Please describe the purpose of your travel..."><?= htmlspecialchars($formData['travel_purpose'] ?? '') ?></textarea>
                        </div>
                        <div class="form-group">
                            <label for="additional_info">Additional Information</label>
                            <textarea id="additional_info" name="additional_info" class="form-control" placeholder="Any additional information or special requirements..."><?= htmlspecialchars($formData['additional_info'] ?? '') ?></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label>Payment Plan</label>
                            <div class="payment-plan-options">
                                <div class="plan-option" onclick="selectPaymentPlan('full')">
                                    <input type="radio" name="payment_plan" value="full" checked>
                                    <div class="plan-content">
                                        <h4><i class="fas fa-credit-card"></i> Full Payment</h4>
                                        <p>Pay the complete amount upfront and get started immediately</p>
                                        <div class="plan-amount" id="full-amount">100% Now</div>
                                        <div class="plan-benefits">
                                            <span class="benefit-tag">✓ Immediate Processing</span>
                                            <span class="benefit-tag">✓ No Additional Fees</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="plan-option" onclick="selectPaymentPlan('partial')">
                                    <input type="radio" name="payment_plan" value="partial">
                                    <div class="plan-content">
                                        <h4><i class="fas fa-calendar-alt"></i> Partial Payment</h4>
                                        <p>Split payment into two installments for better cash flow</p>
                                        <div class="plan-amount" id="partial-amount">50% + 50%</div>
                                        <div class="plan-benefits">
                                            <span class="benefit-tag">✓ Flexible Payment</span>
                                            <span class="benefit-tag">✓ Pay on Approval</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="plan-option" onclick="selectPaymentPlan('custom')">
                                    <input type="radio" name="payment_plan" value="custom">
                                    <div class="plan-content">
                                        <h4><i class="fas fa-sliders-h"></i> Custom Payment</h4>
                                        <p>Choose your initial payment amount and pay the rest later</p>
                                        <div class="custom-payment-input">
                                            <div class="input-group">
                                                <input type="number" id="custom_amount" name="custom_amount" class="custom-amount-input" placeholder="Enter amount" min="1" oninput="updateCustomPayment()">
                                                <span class="currency-label" id="currency-display">USD</span>
                                            </div>
                                            <div class="payment-breakdown" id="payment-breakdown" style="display: none;">
                                                <div class="breakdown-item">
                                                    <span>Initial Payment:</span>
                                                    <span id="initial-amount">0</span>
                                                    <span class="percentage" id="initial-percentage">(0%)</span>
                                                </div>
                                                <div class="breakdown-item">
                                                    <span>Remaining Payment:</span>
                                                    <span id="remaining-amount">0</span>
                                                    <span class="percentage" id="remaining-percentage">(0%)</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="plan-benefits">
                                            <span class="benefit-tag">✓ Your Choice</span>
                                            <span class="benefit-tag">✓ Flexible Terms</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn-submit">
                        <i class="fas fa-arrow-right"></i>
                        Continue to Documents
                    </button>
                </form>
            </div>
        </div>
    </section>
</main>

<script>
function loadServiceDetails(serviceId) {
    fetch(`get-service-details.php?service=${serviceId}`)
        .then(response => response.json())
        .then(data => {
            const detailsContainer = document.getElementById('service-details');
            if (data.service) {
                const service = data.service;
                window.currentServiceData = service; // Store for payment calculations
                const requirements = JSON.parse(service.requirements_json || '{}');
                
                detailsContainer.innerHTML = `
                    <h4>Service Details</h4>
                    ${service.full_description ? `
                        <div class="service-description">
                            <h5>Description</h5>
                            <p>${service.full_description}</p>
                        </div>
                    ` : ''}
                    <div class="details-grid">
                        <div class="detail-item">
                            <div class="detail-label">Service Name</div>
                            <div class="detail-value">${service.name}</div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Price</div>
                            <div class="detail-value">${parseInt(service.base_price).toLocaleString()} ${service.currency || 'USD'}</div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Processing Time</div>
                            <div class="detail-value">${service.processing_time_description || (service.processing_time_min ? service.processing_time_min + '-' + service.processing_time_max + ' days' : 'Contact us')}</div>
                        </div>
                    </div>
                    ${requirements.documents ? `
                        <div class="requirements-list">
                            <h5>Required Documents</h5>
                            ${requirements.documents.map(req => `
                                <div class="requirement-item">
                                    <div class="req-icon"><i class="fas fa-${req.type === 'image' ? 'image' : 'file-pdf'}"></i></div>
                                    <div class="req-text">
                                        ${req.name} ${req.required ? '<span class="req-required">*</span>' : ''}
                                        <br><small style="color: #64748b;">${req.description} (${req.type.toUpperCase()}, max ${req.maxSize}MB)</small>
                                    </div>
                                    <span class="req-badge ${req.required ? '' : 'optional'}">${req.required ? 'Required' : 'Optional'}</span>
                                </div>
                            `).join('')}
                        </div>
                    ` : ''}
                `;
                
                detailsContainer.classList.add('show');
                updatePaymentPlanAmounts(service.base_price, service.currency || 'USD');
            }
        })
        .catch(error => {
            console.log('Error loading service details:', error);
        });
}

function selectPaymentPlan(plan) {
    document.querySelectorAll('.plan-option').forEach(option => {
        option.classList.remove('selected');
    });
    
    event.currentTarget.classList.add('selected');
    
    // Set the appropriate radio button value
    if (plan === 'custom') {
        // For custom, we'll use 'partial' in the form but track it as custom in UI
        document.querySelector(`input[name="payment_plan"][value="partial"]`).checked = true;
        document.querySelector('.custom-payment-input').style.display = 'block';
        document.getElementById('custom_amount').focus();
        // Add hidden field to track that this is custom
        let customField = document.getElementById('is_custom_payment');
        if (!customField) {
            customField = document.createElement('input');
            customField.type = 'hidden';
            customField.name = 'is_custom_payment';
            customField.id = 'is_custom_payment';
            document.querySelector('form').appendChild(customField);
        }
        customField.value = '1';
    } else {
        document.querySelector(`input[name="payment_plan"][value="${plan}"]`).checked = true;
        document.querySelector('.custom-payment-input').style.display = 'none';
        document.getElementById('payment-breakdown').style.display = 'none';
        // Remove custom field
        const customField = document.getElementById('is_custom_payment');
        if (customField) customField.remove();
    }
}

function updateCustomPayment() {
    const customAmount = parseFloat(document.getElementById('custom_amount').value) || 0;
    const serviceCards = document.querySelectorAll('.service-card input[type="radio"]:checked');
    
    if (serviceCards.length === 0) {
        return;
    }
    
    const selectedServiceId = serviceCards[0].value;
    const selectedService = window.currentServiceData;
    
    if (!selectedService) {
        return;
    }
    
    const totalAmount = parseFloat(selectedService.base_price);
    const currency = selectedService.currency || 'USD';
    
    if (customAmount > 0 && customAmount < totalAmount) {
        const remainingAmount = totalAmount - customAmount;
        const initialPercentage = Math.round((customAmount / totalAmount) * 100);
        const remainingPercentage = Math.round((remainingAmount / totalAmount) * 100);
        
        document.getElementById('initial-amount').textContent = `${customAmount.toLocaleString()} ${currency}`;
        document.getElementById('remaining-amount').textContent = `${remainingAmount.toLocaleString()} ${currency}`;
        document.getElementById('initial-percentage').textContent = `(${initialPercentage}%)`;
        document.getElementById('remaining-percentage').textContent = `(${remainingPercentage}%)`;
        
        document.getElementById('payment-breakdown').style.display = 'block';
    } else {
        document.getElementById('payment-breakdown').style.display = 'none';
    }
}

function updatePaymentPlanAmounts(basePrice, currency) {
    const fullAmount = parseInt(basePrice).toLocaleString();
    const halfAmount = parseInt(basePrice / 2).toLocaleString();
    
    // Update currency display
    document.getElementById('currency-display').textContent = currency;
    
    // Update full payment option
    const fullOption = document.getElementById('full-amount');
    if (fullOption) {
        fullOption.textContent = `${fullAmount} ${currency}`;
    }
    
    // Update partial payment option
    const partialOption = document.getElementById('partial-amount');
    if (partialOption) {
        partialOption.textContent = `${halfAmount} + ${halfAmount} ${currency}`;
    }
    
    // Set max value for custom amount input
    const customInput = document.getElementById('custom_amount');
    if (customInput) {
        customInput.max = basePrice - 1;
        customInput.placeholder = `Min: 1, Max: ${parseInt(basePrice - 1).toLocaleString()}`;
    }
}

function scrollServices(direction) {
    const container = document.getElementById('serviceSelection');
    const scrollAmount = 300;
    
    if (direction === 'left') {
        container.scrollBy({ left: -scrollAmount, behavior: 'smooth' });
    } else {
        container.scrollBy({ left: scrollAmount, behavior: 'smooth' });
    }
}

// Auto-load service details if service is pre-selected from URL
window.addEventListener('DOMContentLoaded', function() {
    const selectedService = document.querySelector('input[name="service_id"]:checked');
    if (selectedService) {
        loadServiceDetails(selectedService.value);
        
        // Scroll to show the selected service card
        setTimeout(function() {
            const selectedCard = selectedService.closest('.service-card');
            if (selectedCard) {
                selectedCard.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
            }
        }, 100);
    }
});
</script>
</script>

<?php include 'includes/footer.php'; ?>