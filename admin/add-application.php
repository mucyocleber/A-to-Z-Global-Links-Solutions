<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit;
}

require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    try {
        $pdo = getConnection();
        
        $statusId = $_POST['status_id'] ?? 1;
        $userId = $_POST['user_id'];
        $serviceId = $_POST['service_id'];
        $destinationCountryId = $_POST['destination_country_id'] ?? null;
        $travelPurpose = $_POST['travel_purpose'] ?? '';
        $intendedTravelDate = $_POST['intended_travel_date'] ?? null;
        $durationOfStay = $_POST['duration_of_stay'] ?? '';
        $totalAmount = $_POST['total_amount'];
        $paymentPlan = $_POST['payment_plan'] ?? 'full';
        $currency = $_POST['currency'] ?? 'USD';
        $initialPaymentAmount = $_POST['initial_payment_amount'] ?? 0;
        $remainingPaymentAmount = $_POST['remaining_payment_amount'] ?? 0;
        $paymentMethod = $_POST['payment_method'] ?? '';
        $transactionRef = $_POST['transaction_ref'] ?? '';
        $paymentMethodId = $_POST['payment_method_id'] ?? null;
        $paymentStatus = 'pending';
        
        // Handle file upload (only if not cash payment)
        $paymentProof = null;
        if (isset($_FILES['payment_proof']) && $_FILES['payment_proof']['error'] === 0) {
            $file = $_FILES['payment_proof'];
            
            // Validate file
            $allowedTypes = ['application/pdf', 'image/jpeg', 'image/jpg', 'image/png'];
            if (!in_array($file['type'], $allowedTypes)) {
                echo json_encode(['success' => false, 'message' => 'Invalid file type. Only PDF and images allowed.']);
                exit;
            }
            
            if ($file['size'] > 5 * 1024 * 1024) { // 5MB limit
                echo json_encode(['success' => false, 'message' => 'File too large. Maximum 5MB allowed.']);
                exit;
            }
            
            $uploadDir = '../uploads/payment_proofs/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
            $fileName = uniqid() . '_' . $_FILES['payment_proof']['name'];
            move_uploaded_file($_FILES['payment_proof']['tmp_name'], $uploadDir . $fileName);
            $paymentProof = $fileName;
            $paymentStatus = 'proof_submitted';
        }
        
        // For cash payments, set status to approved and no proof required
        if ($paymentMethod === 'cash' || (isset($paymentMethodId) && $paymentMethodId == 5)) {
            $paymentStatus = 'approved';
            $paymentProof = null; // No proof needed for cash
        }
        
        // Handle document uploads
        $documentsDir = '../uploads/documents/';
        if (!is_dir($documentsDir)) mkdir($documentsDir, 0755, true);
        
        // Generate unique application number
        $stmt = $pdo->prepare("SELECT MAX(CAST(SUBSTRING(application_number, 8) AS UNSIGNED)) as max_num FROM applications WHERE application_number LIKE ?");
        $stmt->execute(['ATZ' . date('Y') . '%']);
        $result = $stmt->fetch();
        $nextNum = ($result['max_num'] ?? 0) + 1;
        $applicationNumber = 'ATZ' . date('Y') . str_pad($nextNum, 4, '0', STR_PAD_LEFT);
        
        // Calculate percentages
        $initialPercentage = $totalAmount > 0 ? ($initialPaymentAmount / $totalAmount) * 100 : 0;
        $remainingPercentage = 100 - $initialPercentage;
        
        // Insert application (manual type for admin-created applications)
        $sql = "INSERT INTO applications (
            application_number, application_type, user_id, service_id, status_id,
            destination_country_id, travel_purpose, intended_travel_date, duration_of_stay,
            base_amount, additional_fees, discount_amount, total_amount,
            payment_plan, initial_payment_amount, remaining_payment_amount,
            initial_payment_percentage, remaining_payment_percentage,
            initial_payment_status, final_payment_status, currency
        ) VALUES (?, 'manual', ?, ?, ?, ?, ?, ?, ?, ?, 0, 0, ?, ?, ?, ?, ?, ?, 'pending', 'not_required', ?)";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $applicationNumber, $userId, $serviceId, $statusId, $destinationCountryId,
            $travelPurpose, $intendedTravelDate, $durationOfStay, $totalAmount, $totalAmount,
            $paymentPlan, $initialPaymentAmount, $remainingPaymentAmount,
            $initialPercentage, $remainingPercentage, $currency
        ]);
        
        $applicationId = $pdo->lastInsertId();
        
        // Update payment method and proof
        if ($paymentProof || $paymentMethodId) {
            $updateSql = "UPDATE applications SET 
                payment_method_id = ?, 
                initial_transaction_reference = ?, 
                payment_status = ?" . 
                ($paymentProof ? ", initial_payment_proof = ?" : "") . 
                " WHERE id = ?";
            $updateStmt = $pdo->prepare($updateSql);
            
            $params = [$paymentMethodId, $transactionRef, $paymentStatus];
            if ($paymentProof) {
                $params[] = 'uploads/payment_proofs/' . $paymentProof;
            }
            $params[] = $applicationId;
            
            $updateStmt->execute($params);
        }
        
        // Handle document uploads
        if (isset($_FILES['documents']) && is_array($_FILES['documents']['name'])) {
            foreach ($_FILES['documents']['name'] as $docId => $fileName) {
                if ($_FILES['documents']['error'][$docId] === 0) {
                    $originalName = $_FILES['documents']['name'][$docId];
                    $tmpName = $_FILES['documents']['tmp_name'][$docId];
                    $fileSize = $_FILES['documents']['size'][$docId];
                    $mimeType = $_FILES['documents']['type'][$docId];
                    
                    $storedName = uniqid() . '.' . pathinfo($originalName, PATHINFO_EXTENSION);
                    $filePath = 'uploads/documents/' . $storedName;
                    
                    if (move_uploaded_file($tmpName, '../' . $filePath)) {
                        // Determine document type based on original filename
                        $docType = 'Document';
                        if (stripos($originalName, 'passport') !== false) {
                            $docType = strpos($mimeType, 'image') !== false ? 'Passport Photo' : 'Passport Copy';
                        }
                        
                        $docStmt = $pdo->prepare(
                            "INSERT INTO documents (application_id, requirement_id, document_type, 
                             original_filename, stored_filename, file_path, file_size, mime_type, uploaded_by) 
                             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
                        );
                        $docStmt->execute([
                            $applicationId, null, $docType, $originalName, 
                            $storedName, $filePath, $fileSize, $mimeType, null
                        ]);
                    }
                }
            }
        }
        
        echo json_encode(['success' => true, 'message' => 'Application created successfully!']);
        exit;
        
    } catch (Exception $e) {
        error_log("Add Application Error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        exit;
    }
}

try {
    $pdo = getConnection();
    
    // Get real services and payment methods from database
    $services = $pdo->query("SELECT id, name, currency, base_price as price FROM services WHERE is_active = 1 ORDER BY name")->fetchAll();
    $countries = $pdo->query("SELECT id, name FROM countries WHERE is_active = 1 ORDER BY name")->fetchAll();
    $statuses = $pdo->query("SELECT id, status_name FROM application_statuses ORDER BY id")->fetchAll();
    $paymentMethods = $pdo->query("SELECT id, name, type FROM payment_methods WHERE is_active = 1 ORDER BY name")->fetchAll();
    
} catch (Exception $e) {
    $services = [];
    $countries = [];
    $statuses = [];
    $paymentMethods = [];
}

$pageTitle = 'Add Application';
?>

<?php include 'includes/header.php'; ?>
<?php include 'includes/sidebar.php'; ?>

<main class="main-content">
    <div class="page-header">
        <h1><i class="fas fa-plus-circle"></i> Create New Application</h1>
        <a href="applications.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </div>
    
    <div class="form-wizard">
        <div class="wizard-steps">
            <div class="step active" data-step="1">
                <span class="step-number">1</span>
                <span class="step-title">Details</span>
            </div>
            <div class="step" data-step="2">
                <span class="step-number">2</span>
                <span class="step-title">Documents</span>
            </div>
            <div class="step" data-step="3">
                <span class="step-number">3</span>
                <span class="step-title">Pricing</span>
            </div>
            <div class="step" data-step="4">
                <span class="step-number">4</span>
                <span class="step-title">Payment</span>
            </div>
        </div>
        
        <form id="applicationForm" enctype="multipart/form-data">
            <!-- Step 1: Details -->
            <div class="step-content active" data-step="1">
                <div class="card">
                    <div class="card-header">
                        <h3><i class="fas fa-user"></i> Application Details</h3>
                    </div>
                    <div class="card-body">
                        <div class="form-grid">
                            <div class="form-group">
                                <label>Select User *</label>
                                <div class="search-select">
                                    <input type="text" id="userSearch" placeholder="Search user by name or email..." autocomplete="off">
                                    <input type="hidden" name="user_id" id="selectedUserId" required>
                                    <div class="search-results" id="userResults"></div>
                                </div>
                                <div class="selected-user" id="selectedUser" style="display: none;"></div>
                            </div>
                            
                            <div class="form-group">
                                <label>Service *</label>
                                <select name="service_id" id="serviceSelect" required>
                                    <option value="">Select Service</option>
                                    <?php if (empty($services)): ?>
                                        <option disabled>No services available</option>
                                    <?php else: ?>
                                        <?php foreach ($services as $service): ?>
                                            <option value="<?= $service['id'] ?>" data-currency="<?= htmlspecialchars($service['currency']) ?>" data-price="<?= htmlspecialchars($service['price']) ?>">
                                                <?= htmlspecialchars($service['name']) ?> (<?= htmlspecialchars($service['currency']) ?> <?= number_format($service['price'], 2) ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label>Destination Country</label>
                                <select name="destination_country_id">
                                    <option value="">Select Country</option>
                                    <?php if (empty($countries)): ?>
                                        <option disabled>No countries available</option>
                                    <?php else: ?>
                                        <?php foreach ($countries as $country): ?>
                                            <option value="<?= $country['id'] ?>"><?= htmlspecialchars($country['name']) ?></option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label>Travel Purpose</label>
                                <input type="text" name="travel_purpose" placeholder="e.g., Tourism, Business, Work">
                            </div>
                            
                            <div class="form-group">
                                <label>Intended Travel Date</label>
                                <input type="date" name="intended_travel_date">
                            </div>
                            
                            <div class="form-group">
                                <label>Duration of Stay</label>
                                <div class="duration-input-group">
                                    <input type="number" name="duration_number" id="durationNumber" placeholder="1" min="1" max="99">
                                    <select name="duration_unit" id="durationUnit">
                                        <option value="weeks">Weeks</option>
                                        <option value="months" selected>Months</option>
                                        <option value="years">Years</option>
                                    </select>
                                </div>
                                <input type="hidden" name="duration_of_stay" id="durationOfStay">
                            </div>
                            
                            <div class="form-group">
                                <label>Application Status *</label>
                                <select name="status_id" required>
                                    <?php if (empty($statuses)): ?>
                                        <option value="1" selected>Draft</option>
                                    <?php else: ?>
                                        <?php foreach ($statuses as $status): ?>
                                            <option value="<?= $status['id'] ?>" <?= $status['id'] == 1 ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($status['status_name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Step 2: Documents -->
            <div class="step-content" data-step="2">
                <div class="card">
                    <div class="card-header">
                        <h3><i class="fas fa-file-alt"></i> Required Documents</h3>
                    </div>
                    <div class="card-body">
                        <div id="documentsContainer">
                            <div class="no-service-selected">
                                <i class="fas fa-file-alt"></i>
                                <h4>Select a Service First</h4>
                                <p>Please select a service in the previous step to view required documents.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Step 3: Pricing -->
            <div class="step-content" data-step="3">
                <div class="card">
                    <div class="card-header">
                        <h3><i class="fas fa-dollar-sign"></i> Pricing & Payment Plan</h3>
                    </div>
                    <div class="card-body">
                        <div class="price-display">
                            <div class="service-price">
                                <span class="currency" id="priceCurrency">USD</span>
                                <span class="amount" id="priceAmount">0.00</span>
                            </div>
                            <div class="price-label">Service Price</div>
                        </div>
                        
                        <input type="hidden" name="total_amount" id="totalAmount">
                        <input type="hidden" name="currency" id="currency">
                        
                        <div class="payment-options">
                            <h4>Payment Plan</h4>
                            <div class="payment-plan-grid">
                                <div class="payment-option">
                                    <input type="radio" name="payment_plan" value="full" id="fullPayment" checked>
                                    <label for="fullPayment">
                                        <i class="fas fa-credit-card"></i>
                                        <span>Full Payment</span>
                                        <small>Pay complete amount now</small>
                                    </label>
                                </div>
                                
                                <div class="payment-option">
                                    <input type="radio" name="payment_plan" value="partial" id="partialPayment">
                                    <label for="partialPayment">
                                        <i class="fas fa-percentage"></i>
                                        <span>Partial Payment</span>
                                        <small>Pay in installments</small>
                                    </label>
                                </div>
                            </div>
                            
                            <div class="partial-options" id="partialOptions" style="display: none;">
                                <div class="partial-grid">
                                    <button type="button" class="partial-btn" data-percent="50">50%</button>
                                    <button type="button" class="partial-btn" data-percent="30">30%</button>
                                    <button type="button" class="partial-btn" data-percent="25">25%</button>
                                    <button type="button" class="partial-btn" data-percent="custom">Custom</button>
                                </div>
                                
                                <div class="custom-amount" id="customAmount" style="display: none;">
                                    <div class="custom-input-wrapper">
                                        <span class="currency-symbol" id="customCurrencySymbol">USD</span>
                                        <input type="number" id="customPaymentAmount" placeholder="0.00" step="0.01" min="0">
                                    </div>
                                    <div class="amount-helper">
                                        <small>Enter initial payment amount</small>
                                    </div>
                                </div>
                                
                                <div class="payment-breakdown" id="paymentBreakdown" style="display: none;">
                                    <div class="breakdown-item">
                                        <span>Initial Payment:</span>
                                        <span id="initialAmount">$0.00</span>
                                        <span class="percentage" id="initialPercent">(0%)</span>
                                    </div>
                                    <div class="breakdown-item">
                                        <span>Remaining:</span>
                                        <span id="remainingAmount">$0.00</span>
                                        <span class="percentage" id="remainingPercent">(0%)</span>
                                    </div>
                                </div>
                                
                                <input type="hidden" name="initial_payment_amount" id="initialPaymentAmount">
                                <input type="hidden" name="remaining_payment_amount" id="remainingPaymentAmount">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Step 4: Payment Method -->
            <div class="step-content" data-step="4">
                <div class="card">
                    <div class="card-header">
                        <h3><i class="fas fa-credit-card"></i> Payment Method</h3>
                    </div>
                    <div class="card-body">
                        <div class="method-grid">
                            <?php if (!empty($paymentMethods)): ?>
                                <?php foreach ($paymentMethods as $method): ?>
                                    <div class="method-option">
                                        <input type="radio" name="payment_method" value="<?= strtolower(str_replace(' ', '_', $method['name'])) ?>" id="method_<?= $method['id'] ?>" data-method-id="<?= $method['id'] ?>" data-method-type="<?= $method['type'] ?>">
                                        <label for="method_<?= $method['id'] ?>">
                                            <i class="fas fa-<?= $method['type'] === 'mobile_money' ? 'mobile-alt' : ($method['type'] === 'bank' ? 'university' : ($method['name'] === 'Cash Payment' ? 'money-bill' : 'credit-card')) ?>"></i>
                                            <span><?= htmlspecialchars($method['name']) ?></span>
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="method-option">
                                    <input type="radio" name="payment_method" value="cash" id="cash" data-method-type="other">
                                    <label for="cash">
                                        <i class="fas fa-money-bill"></i>
                                        <span>Cash Payment</span>
                                    </label>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <input type="hidden" name="payment_method_id" id="paymentMethodId">
                        
                        <div class="payment-details" id="paymentDetails" style="display: none;">
                            <div class="form-group" id="transactionRefGroup">
                                <label>Transaction Reference</label>
                                <input type="text" name="transaction_ref" placeholder="Enter transaction reference">
                            </div>
                            
                            <div class="form-group" id="proofUpload">
                                <label>Payment Proof</label>
                                <div class="file-upload">
                                    <input type="file" name="payment_proof" id="paymentProof" accept="image/*,.pdf">
                                    <label for="paymentProof" class="file-upload-label">
                                        <i class="fas fa-cloud-upload-alt"></i>
                                        <span>Upload payment proof</span>
                                    </label>
                                    <div class="file-info" id="fileInfo" style="display: none;"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="form-navigation">
                <button type="button" class="btn btn-secondary" id="prevBtn" style="display: none;">
                    <i class="fas fa-arrow-left"></i> Previous
                </button>
                <button type="button" class="btn btn-primary" id="nextBtn">
                    Next <i class="fas fa-arrow-right"></i>
                </button>
                <button type="submit" class="btn btn-success" id="submitBtn" style="display: none;">
                    <i class="fas fa-check"></i> Create Application
                </button>
            </div>
        </form>
    </div>
</main>

<style>
.main-content {
    margin-left: 260px;
    margin-top: 70px;
    padding: 2rem;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    min-height: calc(100vh - 70px);
}

.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
    background: rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(10px);
    padding: 1.5rem;
    border-radius: 16px;
    color: white;
}

.form-wizard {
    background: white;
    border-radius: 20px;
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
    overflow: hidden;
}

.wizard-steps {
    display: flex;
    background: #f8fafc;
}

.step {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 1.5rem;
    gap: 0.75rem;
    transition: all 0.3s ease;
}

.step.active {
    background: linear-gradient(135deg, #3b82f6, #1d4ed8);
    color: white;
}

.step-number {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.2);
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
}

.step-content {
    display: none;
    padding: 2rem;
}

.step-content.active {
    display: block;
}

.card {
    border-radius: 16px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    overflow: hidden;
}

.card-header {
    background: linear-gradient(135deg, #f8fafc, #e2e8f0);
    padding: 1.5rem;
    border-bottom: 1px solid #e2e8f0;
}

.card-header h3 {
    color: #1e293b;
    font-size: 1.25rem;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 0.75rem;
    margin: 0;
}

.card-body {
    padding: 2rem;
}

.form-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1.5rem;
}

.form-group {
    display: flex;
    flex-direction: column;
}

.form-group label {
    font-weight: 600;
    color: #374151;
    margin-bottom: 0.5rem;
    font-size: 0.9rem;
}

.form-group input,
.form-group select {
    padding: 0.875rem;
    border: 2px solid #e5e7eb;
    border-radius: 12px;
    font-size: 1rem;
    transition: all 0.3s ease;
}

.form-group input:focus,
.form-group select:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
}

.duration-input-group {
    display: flex;
    gap: 0.5rem;
}

.duration-input-group input {
    flex: 1;
    min-width: 80px;
}

.duration-input-group select {
    flex: 2;
    min-width: 120px;
}

.search-select {
    position: relative;
}

.search-results {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    background: white;
    border: 2px solid #e5e7eb;
    border-top: none;
    border-radius: 0 0 12px 12px;
    max-height: 200px;
    overflow-y: auto;
    z-index: 10;
    display: none;
}

.search-result {
    padding: 0.75rem;
    cursor: pointer;
    border-bottom: 1px solid #f3f4f6;
    transition: background 0.2s;
}

.search-result:hover {
    background: #f8fafc;
}

.selected-user {
    background: #eff6ff;
    border: 2px solid #3b82f6;
    border-radius: 12px;
    padding: 1rem;
    margin-top: 0.5rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.price-display {
    background: linear-gradient(135deg, #3b82f6, #1d4ed8);
    color: white;
    padding: 2rem;
    border-radius: 16px;
    margin-bottom: 2rem;
    text-align: center;
}

.service-price {
    font-size: 3rem;
    font-weight: 700;
    margin-bottom: 0.5rem;
}

.currency {
    font-size: 1.5rem;
    opacity: 0.8;
}

.payment-options h4 {
    color: #1e293b;
    margin-bottom: 1rem;
    font-size: 1.2rem;
}

.payment-plan-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
    margin-bottom: 1.5rem;
}

.payment-option input[type="radio"] {
    display: none;
}

.payment-option label {
    display: block;
    padding: 1.5rem;
    border: 2px solid #e5e7eb;
    border-radius: 12px;
    cursor: pointer;
    transition: all 0.3s ease;
    text-align: center;
}

.payment-option input[type="radio"]:checked + label {
    border-color: #3b82f6;
    background: #eff6ff;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.2);
}

.partial-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 0.75rem;
    margin-bottom: 1rem;
}

.partial-btn {
    padding: 0.75rem;
    border: 2px solid #e5e7eb;
    border-radius: 8px;
    background: white;
    cursor: pointer;
    transition: all 0.3s ease;
    font-weight: 500;
}

.partial-btn:hover,
.partial-btn.active {
    border-color: #3b82f6;
    background: #eff6ff;
    color: #1d4ed8;
}

.payment-breakdown {
    background: #f8fafc;
    border-radius: 12px;
    padding: 1.5rem;
    border: 2px solid #e5e7eb;
}

.custom-input-wrapper {
    position: relative;
    display: flex;
    align-items: center;
    background: white;
    border: 2px solid #e5e7eb;
    border-radius: 12px;
    overflow: hidden;
    transition: all 0.3s ease;
}

.custom-input-wrapper:focus-within {
    border-color: #3b82f6;
    box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
}

.currency-symbol {
    background: #f8fafc;
    padding: 0.875rem 1rem;
    font-weight: 600;
    color: #374151;
    border-right: 2px solid #e5e7eb;
    font-size: 1rem;
}

.custom-input-wrapper input {
    flex: 1;
    border: none;
    padding: 0.875rem 1rem;
    font-size: 1rem;
    outline: none;
    background: transparent;
}

.amount-helper {
    margin-top: 0.5rem;
    text-align: center;
}

.amount-helper small {
    color: #6b7280;
    font-size: 0.875rem;
}

.selected-service-info {
    margin-top: 0.5rem;
}

.service-badge {
    background: #eff6ff;
    color: #1e40af;
    padding: 0.5rem 1rem;
    border-radius: 8px;
    font-size: 0.875rem;
    border: 1px solid #bfdbfe;
}

.requirements-list {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.requirement-item {
    background: white;
    border: 2px solid #e5e7eb;
    border-radius: 16px;
    padding: 1.5rem;
    transition: all 0.3s ease;
}

.requirement-item:hover {
    border-color: #3b82f6;
    box-shadow: 0 4px 20px rgba(59, 130, 246, 0.1);
}

.requirement-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 1rem;
}

.requirement-info h4 {
    margin: 0 0 0.5rem 0;
    color: #1e293b;
    font-size: 1.1rem;
    font-weight: 600;
}

.required-badge {
    background: #fee2e2;
    color: #dc2626;
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
}

.optional-badge {
    background: #f0f9ff;
    color: #0369a1;
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
}

.file-type-info {
    display: flex;
    gap: 0.5rem;
    align-items: center;
}

.file-type {
    background: #f3f4f6;
    color: #374151;
    padding: 0.25rem 0.5rem;
    border-radius: 6px;
    font-size: 0.75rem;
    font-weight: 500;
}

.file-size {
    color: #6b7280;
    font-size: 0.75rem;
}

.upload-area {
    position: relative;
}

.file-input {
    display: none;
}

.upload-label {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 2rem;
    border: 2px dashed #d1d5db;
    border-radius: 12px;
    cursor: pointer;
    transition: all 0.3s ease;
    background: #fafafa;
}

.upload-label:hover {
    border-color: #3b82f6;
    background: #eff6ff;
}

.upload-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    background: #eff6ff;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #3b82f6;
    font-size: 1.5rem;
}

.upload-text {
    display: flex;
    flex-direction: column;
}

.upload-title {
    font-weight: 600;
    color: #374151;
    font-size: 1rem;
}

.upload-subtitle {
    color: #6b7280;
    font-size: 0.875rem;
}

.file-preview {
    margin-top: 1rem;
}

.file-selected {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem;
    background: #f0fdf4;
    border: 1px solid #22c55e;
    border-radius: 8px;
}

.file-selected i {
    color: #22c55e;
    font-size: 1.25rem;
}

.file-name {
    flex: 1;
    font-weight: 500;
    color: #15803d;
}

.file-size {
    color: #16a34a;
    font-size: 0.875rem;
}

.remove-file {
    background: #ef4444;
    color: white;
    border: none;
    width: 24px;
    height: 24px;
    border-radius: 50%;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.75rem;
    transition: all 0.2s ease;
}

.remove-file:hover {
    background: #dc2626;
    transform: scale(1.1);
}

.no-requirements {
    text-align: center;
    padding: 3rem 2rem;
    color: #6b7280;
}

.no-requirements i {
    font-size: 3rem;
    margin-bottom: 1rem;
    color: #9ca3af;
}

.no-requirements h4 {
    margin: 0 0 0.5rem 0;
    color: #374151;
}

.documents-info {
    background: #f0f9ff;
    border: 1px solid #0ea5e9;
    border-radius: 12px;
    padding: 1rem;
    margin-bottom: 1.5rem;
    color: #0c4a6e;
}

.no-service-selected {
    text-align: center;
    padding: 3rem 2rem;
    color: #6b7280;
}

.no-service-selected i {
    font-size: 3rem;
    margin-bottom: 1rem;
    color: #9ca3af;
}

.document-requirement {
    background: white;
    border: 2px solid #e5e7eb;
    border-radius: 12px;
    padding: 1.5rem;
    margin-bottom: 1rem;
    transition: all 0.3s ease;
}

.document-requirement:hover {
    border-color: #3b82f6;
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.1);
}

.document-header {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    margin-bottom: 1rem;
}

.document-icon {
    width: 40px;
    height: 40px;
    border-radius: 8px;
    background: #eff6ff;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #3b82f6;
    font-size: 1.2rem;
}

.document-info h4 {
    margin: 0 0 0.25rem 0;
    color: #1e293b;
    font-size: 1.1rem;
}

.document-info p {
    margin: 0;
    color: #6b7280;
    font-size: 0.9rem;
}

.document-upload {
    position: relative;
}

.document-upload input[type="file"] {
    display: none;
}

.document-upload-label {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.875rem 1rem;
    border: 2px dashed #d1d5db;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.3s ease;
    background: #fafafa;
}

.document-upload-label:hover {
    border-color: #3b82f6;
    background: #eff6ff;
}

.document-upload-label i {
    color: #6b7280;
}

.document-upload-label span {
    color: #374151;
    font-size: 0.9rem;
}

.document-file-info {
    display: none;
    margin-top: 0.5rem;
    padding: 0.5rem;
    background: #f0fdf4;
    border: 1px solid #22c55e;
    border-radius: 6px;
    font-size: 0.85rem;
    color: #15803d;
}

.breakdown-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.5rem 0;
    font-weight: 500;
}

.breakdown-item:first-child {
    border-bottom: 1px solid #e5e7eb;
    margin-bottom: 0.5rem;
    padding-bottom: 1rem;
}

.method-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1rem;
    margin-bottom: 2rem;
}

.method-option input[type="radio"] {
    display: none;
}

.method-option label {
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 1.5rem;
    border: 2px solid #e5e7eb;
    border-radius: 12px;
    cursor: pointer;
    transition: all 0.3s ease;
    text-align: center;
    gap: 0.75rem;
}

.method-option input[type="radio"]:checked + label {
    border-color: #3b82f6;
    background: #eff6ff;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.2);
}

.method-option i {
    font-size: 2rem;
    color: #6b7280;
}

.method-option input[type="radio"]:checked + label i {
    color: #3b82f6;
}

.file-upload input[type="file"] {
    display: none;
}

.file-upload-label {
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 2rem;
    border: 2px dashed #d1d5db;
    border-radius: 12px;
    cursor: pointer;
    transition: all 0.3s ease;
    background: #fafafa;
}

.file-upload-label:hover {
    border-color: #3b82f6;
    background: #eff6ff;
}

.form-navigation {
    display: flex;
    justify-content: space-between;
    padding: 2rem;
    background: #f8fafc;
    border-top: 1px solid #e5e7eb;
}

.btn {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.875rem 1.75rem;
    border-radius: 12px;
    font-weight: 600;
    text-decoration: none;
    border: none;
    cursor: pointer;
    transition: all 0.3s ease;
    font-size: 1rem;
}

.btn-primary {
    background: linear-gradient(135deg, #3b82f6, #1d4ed8);
    color: white;
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(59, 130, 246, 0.4);
}

.btn-secondary {
    background: #6b7280;
    color: white;
}

.btn-success {
    background: linear-gradient(135deg, #10b981, #059669);
    color: white;
    box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
}

.btn-success:disabled {
    opacity: 0.7;
    cursor: not-allowed;
}

.fa-spinner {
    animation: spin 1s linear infinite;
}

@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

.modern-modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: 1000;
    align-items: center;
    justify-content: center;
}

.modern-modal.show {
    display: flex;
}

.modern-modal-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.6);
    backdrop-filter: blur(8px);
}

.modern-modal-content {
    position: relative;
    background: white;
    border-radius: 20px;
    max-width: 450px;
    width: 90%;
    box-shadow: 0 25px 50px rgba(0, 0, 0, 0.25);
    overflow: hidden;
    animation: modalSlideIn 0.3s ease-out;
}

@keyframes modalSlideIn {
    from {
        opacity: 0;
        transform: translateY(-20px) scale(0.95);
    }
    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

.modal-header {
    padding: 2rem 2rem 1rem;
    text-align: center;
    background: linear-gradient(135deg, #f8fafc, #e2e8f0);
}

.modal-icon {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.9);
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1rem;
    font-size: 2rem;
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
}

.modal-header h3 {
    color: #1e293b;
    font-size: 1.5rem;
    font-weight: 700;
    margin: 0;
}

.modal-body {
    padding: 1.5rem 2rem;
    text-align: center;
}

.modal-body p {
    color: #64748b;
    font-size: 1.1rem;
    line-height: 1.6;
    margin: 0;
}

.modal-footer {
    padding: 1.5rem 2rem 2rem;
    text-align: center;
}

.modal-btn {
    min-width: 120px;
    padding: 0.875rem 2rem;
    font-size: 1rem;
    font-weight: 600;
    border-radius: 12px;
    transition: all 0.3s ease;
}

.modal-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(59, 130, 246, 0.4);
}

.modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    backdrop-filter: blur(4px);
    z-index: 1000;
    align-items: center;
    justify-content: center;
}

.modal.show {
    display: flex;
}

.modal-content {
    background: white;
    border-radius: 20px;
    padding: 2rem;
    max-width: 400px;
    width: 90%;
    text-align: center;
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
}

.modal-icon {
    width: 64px;
    height: 64px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1rem;
    font-size: 1.5rem;
}

.modal-icon.success {
    background: #d1fae5;
    color: #059669;
}

.modal-icon.error {
    background: #fee2e2;
    color: #dc2626;
}

.file-info {
    margin-top: 0.5rem;
    padding: 0.5rem;
    background: #f0fdf4;
    border: 1px solid #22c55e;
    border-radius: 6px;
    font-size: 0.85rem;
    color: #15803d;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

@media (max-width: 768px) {
    .main-content {
        margin-left: 0;
        padding: 1rem;
    }
    
    .form-grid {
        grid-template-columns: 1fr;
    }
    
    .payment-plan-grid,
    .method-grid {
        grid-template-columns: 1fr;
    }
    
    .partial-grid {
        grid-template-columns: 1fr 1fr;
    }
}
</style>

<script>
let currentStep = 1;
const totalSteps = 4;

// User search functionality
document.addEventListener('DOMContentLoaded', function() {
    const userSearch = document.getElementById('userSearch');
    const userResults = document.getElementById('userResults');
    
    if (userSearch) {
        userSearch.addEventListener('input', function() {
            const query = this.value.trim();
            
            if (query.length < 2) {
                userResults.style.display = 'none';
                return;
            }
            
            fetch('ajax/search_users.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'query=' + encodeURIComponent(query)
            })
            .then(response => response.json())
            .then(users => {
                if (users.length > 0) {
                    userResults.innerHTML = users.map(user => 
                        `<div class="search-result" onclick="selectUser(${user.id}, '${user.first_name} ${user.last_name}', '${user.email}')">
                            <strong>${user.first_name} ${user.last_name}</strong><br>
                            <small>${user.email}</small>
                        </div>`
                    ).join('');
                    userResults.style.display = 'block';
                } else {
                    userResults.innerHTML = '<div class="search-result">No users found</div>';
                    userResults.style.display = 'block';
                }
            })
            .catch(error => {
                console.error('Search error:', error);
                userResults.style.display = 'none';
            });
        });
        
        document.addEventListener('click', function(e) {
            if (!userSearch.contains(e.target) && !userResults.contains(e.target)) {
                userResults.style.display = 'none';
            }
        });
    }
    
    // Service selection
    const serviceSelect = document.getElementById('serviceSelect');
    if (serviceSelect) {
        serviceSelect.addEventListener('change', function() {
            const option = this.options[this.selectedIndex];
            const price = parseFloat(option.dataset.price) || 0;
            const currency = option.dataset.currency || 'USD';
            const serviceId = this.value;
            
            document.getElementById('priceAmount').textContent = price.toFixed(2);
            document.getElementById('priceCurrency').textContent = currency;
            document.getElementById('totalAmount').value = price;
            document.getElementById('currency').value = currency;
            document.getElementById('customCurrencySymbol').textContent = currency;
            
            // Load service requirements
            loadServiceRequirements(serviceId);
            
            updatePaymentBreakdown();
        });
    }
    
    // Payment plan selection
    document.querySelectorAll('input[name="payment_plan"]').forEach(radio => {
        radio.addEventListener('change', function() {
            const isPartial = this.value === 'partial';
            document.getElementById('partialOptions').style.display = isPartial ? 'block' : 'none';
            
            // Set payment amounts based on plan
            const totalAmount = parseFloat(document.getElementById('totalAmount').value) || 0;
            if (!isPartial) {
                // For full payment, set initial to total and remaining to 0
                document.getElementById('initialPaymentAmount').value = totalAmount;
                document.getElementById('remainingPaymentAmount').value = 0;
            }
            
            updatePaymentBreakdown();
        });
    });
    
    // Partial payment buttons
    document.querySelectorAll('.partial-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.partial-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            
            const percent = this.dataset.percent;
            const customAmount = document.getElementById('customAmount');
            
            if (percent === 'custom') {
                customAmount.style.display = 'block';
            } else {
                customAmount.style.display = 'none';
                const totalAmount = parseFloat(document.getElementById('totalAmount').value) || 0;
                const initialAmount = (totalAmount * parseFloat(percent)) / 100;
                document.getElementById('customPaymentAmount').value = initialAmount.toFixed(2);
                updatePaymentBreakdown();
            }
        });
    });
    
    document.getElementById('customPaymentAmount').addEventListener('input', updatePaymentBreakdown);
    
    // Duration input handling
    const durationNumber = document.getElementById('durationNumber');
    const durationUnit = document.getElementById('durationUnit');
    const durationOfStay = document.getElementById('durationOfStay');
    
    function updateDuration() {
        const number = durationNumber.value;
        const unit = durationUnit.value;
        if (number && unit) {
            durationOfStay.value = `${number} ${unit}`;
        } else {
            durationOfStay.value = '';
        }
    }
    
    if (durationNumber && durationUnit) {
        durationNumber.addEventListener('input', updateDuration);
        durationUnit.addEventListener('change', updateDuration);
    }
    
    // Payment method selection
    document.querySelectorAll('input[name="payment_method"]').forEach(radio => {
        radio.addEventListener('change', function() {
            const details = document.getElementById('paymentDetails');
            const proofUpload = document.getElementById('proofUpload');
            const transactionRefGroup = document.getElementById('transactionRefGroup');
            const paymentMethodId = document.getElementById('paymentMethodId');
            
            // Set payment method ID
            const methodId = this.dataset.methodId || '';
            paymentMethodId.value = methodId;
            
            details.style.display = 'block';
            
            // Hide proof and transaction ref for cash payments
            if (this.value.includes('cash') || this.dataset.methodType === 'other' || methodId == '5') {
                proofUpload.style.display = 'none';
                transactionRefGroup.style.display = 'none';
                // Remove required attribute from payment proof for cash payments
                const proofInput = document.getElementById('paymentProof');
                if (proofInput) {
                    proofInput.removeAttribute('required');
                }
            } else {
                proofUpload.style.display = 'block';
                transactionRefGroup.style.display = 'block';
                // Add required attribute for non-cash payments
                const proofInput = document.getElementById('paymentProof');
                if (proofInput) {
                    proofInput.setAttribute('required', 'required');
                }
            }
        });
    });
    
    // File upload preview
    const paymentProofInput = document.getElementById('paymentProof');
    if (paymentProofInput) {
        paymentProofInput.addEventListener('change', function() {
            const fileInfo = document.getElementById('fileInfo');
            if (this.files.length > 0) {
                const file = this.files[0];
                fileInfo.innerHTML = `<i class="fas fa-file"></i> ${file.name} (${(file.size / 1024 / 1024).toFixed(2)} MB)`;
                fileInfo.style.display = 'block';
            } else {
                fileInfo.style.display = 'none';
            }
        });
    }
    
    // Step navigation
    document.getElementById('nextBtn').addEventListener('click', function() {
        if (validateStep(currentStep)) {
            if (currentStep < totalSteps) {
                currentStep++;
                updateStep();
            }
        }
    });
    
    document.getElementById('prevBtn').addEventListener('click', function() {
        if (currentStep > 1) {
            currentStep--;
            updateStep();
        }
    });
    
    // Form submission
    document.getElementById('applicationForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const submitBtn = document.getElementById('submitBtn');
        const originalHtml = submitBtn.innerHTML;
        
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creating...';
        
        const formData = new FormData(this);
        
        fetch('', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                submitBtn.innerHTML = '<i class="fas fa-check"></i> Success!';
                showModal('success', data.message, () => {
                    window.location.href = 'applications.php';
                });
            } else {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalHtml;
                showModal('error', data.message);
            }
        })
        .catch(error => {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalHtml;
            showModal('error', 'Network error occurred. Please try again.');
        });
    });
});

function removeFile(docId) {
    const fileInput = document.getElementById(`doc_${docId}`);
    const preview = document.getElementById(`preview_${docId}`);
    fileInput.value = '';
    preview.style.display = 'none';
}

function loadServiceRequirements(serviceId) {
    const container = document.getElementById('documentsContainer');
    
    if (!serviceId) {
        container.innerHTML = `
            <div class="no-service-selected">
                <i class="fas fa-file-alt"></i>
                <h4>Select a Service First</h4>
                <p>Please select a service in the previous step to view required documents.</p>
            </div>
        `;
        return;
    }
    
    fetch('ajax/get_service_requirements.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'service_id=' + encodeURIComponent(serviceId)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.documents && data.documents.length > 0) {
            let documentsHtml = '<div class="requirements-list">';
            
            data.documents.forEach((doc, index) => {
                const isRequired = doc.required ? 'required' : 'optional';
                const requiredText = doc.required ? '<span class="required-badge">Required</span>' : '<span class="optional-badge">Optional</span>';
                
                documentsHtml += `
                    <div class="requirement-item">
                        <div class="requirement-header">
                            <div class="requirement-info">
                                <h4>${doc.name}</h4>
                                ${requiredText}
                            </div>
                            <div class="file-type-info">
                                <span class="file-type">${doc.type.toUpperCase()}</span>
                                <span class="file-size">Max ${doc.maxSize}MB</span>
                            </div>
                        </div>
                        <div class="upload-area">
                            <input type="file" name="documents[${doc.id}]" id="doc_${doc.id}" 
                                   accept="${doc.type === 'image' ? 'image/*' : '.pdf'}" 
                                   class="file-input">
                            <label for="doc_${doc.id}" class="upload-label">
                                <div class="upload-icon">
                                    <i class="fas fa-cloud-upload-alt"></i>
                                </div>
                                <div class="upload-text">
                                    <span class="upload-title">Choose ${doc.name}</span>
                                    <span class="upload-subtitle">or drag and drop here</span>
                                </div>
                            </label>
                            <div class="file-preview" id="preview_${doc.id}" style="display: none;"></div>
                        </div>
                    </div>
                `;
            });
            
            documentsHtml += '</div>';
            container.innerHTML = documentsHtml;
            
            // Add file change handlers
            data.documents.forEach(doc => {
                const fileInput = document.getElementById(`doc_${doc.id}`);
                const preview = document.getElementById(`preview_${doc.id}`);
                
                fileInput.addEventListener('change', function() {
                    if (this.files[0]) {
                        const file = this.files[0];
                        preview.innerHTML = `
                            <div class="file-selected">
                                <i class="fas fa-file-${doc.type === 'image' ? 'image' : 'pdf'}"></i>
                                <span class="file-name">${file.name}</span>
                                <span class="file-size">${(file.size / 1024 / 1024).toFixed(2)} MB</span>
                                <button type="button" class="remove-file" onclick="removeFile('${doc.id}')">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        `;
                        preview.style.display = 'block';
                    }
                });
            });
        } else {
            container.innerHTML = `
                <div class="no-requirements">
                    <i class="fas fa-file-alt"></i>
                    <h4>No Specific Requirements</h4>
                    <p>This service has no document requirements specified.</p>
                </div>
            `;
        }
    })
    .catch(error => {
        console.error('Error loading requirements:', error);
        container.innerHTML = `
            <div class="no-service-selected">
                <i class="fas fa-exclamation-triangle"></i>
                <h4>Error Loading Requirements</h4>
                <p>Unable to load document requirements. Please try again.</p>
            </div>
        `;
    });
}

function selectUser(id, name, email) {
    document.getElementById('selectedUserId').value = id;
    document.getElementById('userSearch').value = '';
    document.getElementById('userResults').style.display = 'none';
    document.getElementById('selectedUser').innerHTML = `
        <div><strong>${name}</strong><br><small>${email}</small></div>
        <button type="button" onclick="clearUser()" style="background: #ef4444; color: white; border: none; padding: 0.5rem; border-radius: 6px;">×</button>
    `;
    document.getElementById('selectedUser').style.display = 'flex';
}

function clearUser() {
    document.getElementById('selectedUserId').value = '';
    document.getElementById('selectedUser').style.display = 'none';
}

function updatePaymentBreakdown() {
    const totalAmount = parseFloat(document.getElementById('totalAmount').value) || 0;
    const currency = document.getElementById('currency').value;
    const isPartial = document.querySelector('input[name="payment_plan"]:checked').value === 'partial';
    
    if (!isPartial || totalAmount === 0) {
        document.getElementById('paymentBreakdown').style.display = 'none';
        // For full payment, set values
        if (!isPartial && totalAmount > 0) {
            document.getElementById('initialPaymentAmount').value = totalAmount;
            document.getElementById('remainingPaymentAmount').value = 0;
        }
        return;
    }
    
    const initialAmount = parseFloat(document.getElementById('customPaymentAmount').value) || 0;
    const remainingAmount = totalAmount - initialAmount;
    const initialPercent = (initialAmount / totalAmount) * 100;
    const remainingPercent = 100 - initialPercent;
    
    document.getElementById('initialAmount').textContent = `${currency} ${initialAmount.toFixed(2)}`;
    document.getElementById('remainingAmount').textContent = `${currency} ${remainingAmount.toFixed(2)}`;
    document.getElementById('initialPercent').textContent = `(${initialPercent.toFixed(1)}%)`;
    document.getElementById('remainingPercent').textContent = `(${remainingPercent.toFixed(1)}%)`;
    
    document.getElementById('initialPaymentAmount').value = initialAmount;
    document.getElementById('remainingPaymentAmount').value = remainingAmount;
    
    document.getElementById('paymentBreakdown').style.display = 'block';
}

function updateStep() {
    document.querySelectorAll('.step').forEach((step, index) => {
        step.classList.remove('active');
        if (index + 1 === currentStep) {
            step.classList.add('active');
        }
    });
    
    document.querySelectorAll('.step-content').forEach((content, index) => {
        content.classList.remove('active');
        if (index + 1 === currentStep) {
            content.classList.add('active');
        }
    });
    
    document.getElementById('prevBtn').style.display = currentStep > 1 ? 'block' : 'none';
    document.getElementById('nextBtn').style.display = currentStep < totalSteps ? 'block' : 'none';
    document.getElementById('submitBtn').style.display = currentStep === totalSteps ? 'block' : 'none';
}

function validateStep(step) {
    if (step === 1) {
        const userId = document.getElementById('selectedUserId').value;
        const serviceId = document.getElementById('serviceSelect').value;
        
        if (!userId) {
            showModal('error', 'Please select a user');
            return false;
        }
        if (!serviceId) {
            showModal('error', 'Please select a service');
            return false;
        }
    }
    
    if (step === 2) {
        // Documents step - optional validation
        // Could add document upload validation here if needed
        return true;
    }
    
    if (step === 3) {
        const totalAmount = document.getElementById('totalAmount').value;
        if (!totalAmount || totalAmount <= 0) {
            showModal('error', 'Please select a service with valid pricing');
            return false;
        }
    }
    
    return true;
}

function showModal(type, message, callback) {
    const modal = document.createElement('div');
    modal.className = 'modern-modal show';
    
    const iconClass = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-triangle';
    const iconColor = type === 'success' ? '#10b981' : '#ef4444';
    const title = type === 'success' ? 'Success!' : 'Error';
    const buttonText = type === 'success' ? 'Continue' : 'Try Again';
    
    modal.innerHTML = `
        <div class="modern-modal-overlay"></div>
        <div class="modern-modal-content">
            <div class="modal-header">
                <div class="modal-icon" style="color: ${iconColor}">
                    <i class="fas ${iconClass}"></i>
                </div>
                <h3>${title}</h3>
            </div>
            <div class="modal-body">
                <p>${message}</p>
            </div>
            <div class="modal-footer">
                <button class="btn btn-primary modal-btn" onclick="closeModal(this)">
                    ${buttonText}
                </button>
            </div>
        </div>
    `;
    
    document.body.appendChild(modal);
    
    window.closeModal = function(btn) {
        modal.remove();
        if (callback) callback();
    };
}
</script>

<?php include 'includes/footer.php'; ?>
