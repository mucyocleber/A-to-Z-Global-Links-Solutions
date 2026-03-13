<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'config/database.php';

if (!isset($_SESSION['user_id']) || !isset($_GET['app'])) {
    header('Location: application.php');
    exit;
}

$applicationId = $_GET['app'];

// Handle document upload
if ($_POST && isset($_FILES['documents'])) {
    try {
        $pdo = getConnection();
        
        // Verify application belongs to user
        $stmt = $pdo->prepare("SELECT * FROM applications WHERE id = ? AND user_id = ?");
        $stmt->execute([$applicationId, $_SESSION['user_id']]);
        $application = $stmt->fetch();
        
        if (!$application) {
            header('Location: application.php');
            exit;
        }
        
        // Get service with requirements
        $stmt = $pdo->prepare("SELECT requirements_json FROM services WHERE id = ?");
        $stmt->execute([$application['service_id']]);
        $service = $stmt->fetch();
        $requirements = json_decode($service['requirements_json'] ?? '{}', true);
        
        $uploadDir = 'uploads/documents/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        foreach ($_FILES['documents']['name'] as $key => $filename) {
            if ($_FILES['documents']['error'][$key] === UPLOAD_ERR_OK) {
                $originalName = $filename;
                $extension = pathinfo($originalName, PATHINFO_EXTENSION);
                $storedName = uniqid() . '.' . $extension;
                $filePath = $uploadDir . $storedName;
                
                if (move_uploaded_file($_FILES['documents']['tmp_name'][$key], $filePath)) {
        // Create payment record
        $stmt = $pdo->prepare("
            INSERT INTO documents (document_uuid, application_id, document_type, original_filename, stored_filename, file_path, file_size, mime_type, uploaded_by)
            VALUES (UUID(), ?, ?, ?, ?, ?, ?, ?, ?)
        ");
                    $stmt->execute([
                        $applicationId,
                        $_POST['document_types'][$key],
                        $originalName,
                        $storedName,
                        $filePath,
                        $_FILES['documents']['size'][$key],
                        $_FILES['documents']['type'][$key],
                        $_SESSION['user_id']
                    ]);
                }
            }
        }
        
        // Update application status to submitted
        $stmt = $pdo->prepare("UPDATE applications SET status_id = 2 WHERE id = ?");
        $stmt->execute([$applicationId]);
        
        // Redirect to payment
        header("Location: application-payment.php?app=$applicationId");
        exit;
        
    } catch (Exception $e) {
        $error = "Error uploading documents. Please try again.";
    }
}

try {
    $pdo = getConnection();
    
    // Get application details
    $stmt = $pdo->prepare("
        SELECT a.*, s.name as service_name, s.requirements_json 
        FROM applications a 
        LEFT JOIN services s ON a.service_id = s.id 
        WHERE a.id = ? AND a.user_id = ?
    ");
    $stmt->execute([$applicationId, $_SESSION['user_id']]);
    $application = $stmt->fetch();
    
    if (!$application) {
        header('Location: application.php');
        exit;
    }
    
    // Parse requirements from JSON
    $requirements = json_decode($application['requirements_json'] ?? '{}', true);
    $documents = $requirements['documents'] ?? [];
    
} catch (Exception $e) {
    $application = null;
    $documents = [];
}

$pageTitle = 'Upload Documents - A to Z Global Link Solutions';
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
    background: white;
    padding: 2.5rem 2rem;
    border-radius: 24px;
    box-shadow: 0 10px 40px rgba(59, 130, 246, 0.1);
    border: 2px solid rgba(59, 130, 246, 0.1);
}

.step {
    display: flex;
    align-items: center;
    position: relative;
}

.step-number {
    width: 50px;
    height: 50px;
    background: rgba(59, 130, 246, 0.2);
    color: #3b82f6;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 800;
    margin-right: 1rem;
    font-size: 1.1rem;
    transition: all 0.4s ease;
    border: 3px solid rgba(59, 130, 246, 0.3);
}

.step.active .step-number,
.step.completed .step-number {
    background: linear-gradient(135deg, #3b82f6, #1d4ed8);
    color: white;
    border-color: #3b82f6;
    box-shadow: 0 8px 25px rgba(59, 130, 246, 0.3);
    transform: scale(1.1);
}

.step.completed .step-number {
    background: linear-gradient(135deg, #10b981, #059669);
    border-color: #10b981;
    box-shadow: 0 8px 25px rgba(16, 185, 129, 0.3);
}

.step-line {
    width: 60px;
    height: 4px;
    background: rgba(59, 130, 246, 0.2);
    margin: 0 1rem;
    border-radius: 2px;
}

.step.completed .step-line {
    background: linear-gradient(90deg, #10b981, #059669);
}

.step-text {
    font-size: 1rem;
    color: #64748b;
    font-weight: 600;
    white-space: nowrap;
}

.step.active .step-text,
.step.completed .step-text {
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

.app-info {
    background: linear-gradient(135deg, rgba(59, 130, 246, 0.05) 0%, rgba(147, 197, 253, 0.08) 100%);
    padding: 2rem;
    border-radius: 20px;
    margin-bottom: 2.5rem;
    border: 2px solid rgba(59, 130, 246, 0.1);
    position: relative;
}

.app-info::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 3px;
    background: linear-gradient(135deg, #3b82f6, #1d4ed8);
    border-radius: 2px;
}

.app-info h3 {
    color: #1e40af;
    margin-bottom: 0.75rem;
    font-size: 1.4rem;
    font-weight: 800;
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.app-info p {
    color: #3b82f6;
    margin: 0;
    font-weight: 600;
    font-size: 1.1rem;
}

.requirements-section {
    margin-bottom: 3rem;
}

.requirements-section h3 {
    color: #1e40af;
    font-size: 1.5rem;
    font-weight: 800;
    margin-bottom: 2rem;
    padding-bottom: 0.75rem;
    border-bottom: 2px solid rgba(59, 130, 246, 0.1);
    position: relative;
}

.requirements-section h3::after {
    content: '';
    position: absolute;
    bottom: -2px;
    left: 0;
    width: 60px;
    height: 2px;
    background: linear-gradient(135deg, #3b82f6, #1d4ed8);
}

.requirement-item {
    display: flex;
    align-items: center;
    gap: 1.25rem;
    padding: 1.75rem;
    background: white;
    border: 2px solid rgba(59, 130, 246, 0.1);
    border-radius: 16px;
    margin-bottom: 1.25rem;
    transition: all 0.4s ease;
    position: relative;
    overflow: hidden;
}

.requirement-item::before {
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

.requirement-item:hover::before {
    opacity: 1;
    transform: rotate(45deg) translate(50%, 50%);
}

.requirement-item:hover {
    border-color: #3b82f6;
    box-shadow: 0 10px 30px rgba(59, 130, 246, 0.15);
    transform: translateY(-3px);
}

.req-icon {
    width: 50px;
    height: 50px;
    background: linear-gradient(135deg, #3b82f6, #1d4ed8);
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.4rem;
    flex-shrink: 0;
    box-shadow: 0 8px 25px rgba(59, 130, 246, 0.3);
    transition: all 0.3s ease;
}

.requirement-item:hover .req-icon {
    transform: scale(1.1) rotate(5deg);
}

.req-info {
    flex: 1;
}

.req-info h4 {
    color: #1e40af;
    margin-bottom: 0.5rem;
    font-size: 1.1rem;
    font-weight: 700;
}

.req-info p {
    color: #64748b;
    margin: 0;
    font-size: 0.95rem;
    line-height: 1.5;
    font-weight: 500;
}

.req-badge {
    padding: 0.4rem 1rem;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    border: 2px solid;
}

.req-badge.required {
    background: linear-gradient(135deg, #fee2e2, #fecaca);
    color: #991b1b;
    border-color: rgba(239, 68, 68, 0.3);
}

.req-badge.optional {
    background: linear-gradient(135deg, #d1fae5, #a7f3d0);
    color: #065f46;
    border-color: rgba(16, 185, 129, 0.3);
}

.upload-section {
    margin-bottom: 3rem;
}

.upload-section h3 {
    color: #1e40af;
    font-size: 1.5rem;
    font-weight: 800;
    margin-bottom: 2rem;
    padding-bottom: 0.75rem;
    border-bottom: 2px solid rgba(59, 130, 246, 0.1);
    position: relative;
}

.upload-section h3::after {
    content: '';
    position: absolute;
    bottom: -2px;
    left: 0;
    width: 60px;
    height: 2px;
    background: linear-gradient(135deg, #3b82f6, #1d4ed8);
}

.upload-item {
    display: grid;
    grid-template-columns: 1fr 2fr auto;
    gap: 1.5rem;
    align-items: center;
    margin-bottom: 1.5rem;
    padding: 2rem;
    border: 2px dashed rgba(59, 130, 246, 0.3);
    border-radius: 16px;
    background: rgba(59, 130, 246, 0.02);
    transition: all 0.4s ease;
    position: relative;
    overflow: hidden;
}

.upload-item::before {
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

.upload-item:hover::before {
    opacity: 1;
    transform: rotate(45deg) translate(50%, 50%);
}

.upload-item:hover {
    border-color: #3b82f6;
    background: rgba(59, 130, 246, 0.05);
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(59, 130, 246, 0.15);
}

.upload-item select {
    padding: 1rem 1.25rem;
    border: 2px solid rgba(59, 130, 246, 0.15);
    border-radius: 12px;
    font-size: 1rem;
    background: white;
    transition: all 0.3s ease;
    font-weight: 500;
    cursor: pointer;
}

.upload-item select:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
    transform: translateY(-1px);
}

.upload-item input[type="file"] {
    padding: 1rem 1.25rem;
    border: 2px solid rgba(59, 130, 246, 0.15);
    border-radius: 12px;
    background: white;
    transition: all 0.3s ease;
    font-weight: 500;
    cursor: pointer;
}

.upload-item input[type="file"]:hover {
    border-color: #3b82f6;
    transform: translateY(-1px);
}

.btn-remove {
    background: linear-gradient(135deg, #ef4444, #dc2626);
    color: white;
    border: none;
    padding: 0.75rem;
    border-radius: 12px;
    cursor: pointer;
    transition: all 0.3s ease;
    width: 45px;
    height: 45px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.1rem;
    box-shadow: 0 4px 15px rgba(239, 68, 68, 0.3);
}

.btn-remove:hover {
    background: linear-gradient(135deg, #dc2626, #b91c1c);
    transform: scale(1.1);
    box-shadow: 0 6px 20px rgba(239, 68, 68, 0.4);
}

.btn-add-upload {
    background: rgba(59, 130, 246, 0.1);
    color: #3b82f6;
    padding: 1rem 2rem;
    border: 2px solid rgba(59, 130, 246, 0.2);
    border-radius: 16px;
    cursor: pointer;
    transition: all 0.3s ease;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 0.75rem;
    margin-bottom: 2.5rem;
    font-size: 1rem;
    position: relative;
    overflow: hidden;
}

.btn-add-upload::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
    transition: left 0.6s ease;
}

.btn-add-upload:hover::before {
    left: 100%;
}

.btn-add-upload:hover {
    background: rgba(59, 130, 246, 0.15);
    border-color: #3b82f6;
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(59, 130, 246, 0.2);
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
    padding: 1.5rem 2rem;
    border-radius: 16px;
    margin-bottom: 2rem;
    display: flex;
    align-items: center;
    gap: 1rem;
    font-weight: 600;
    border: 2px solid;
}

.alert-error {
    background: linear-gradient(135deg, rgba(239, 68, 68, 0.1), rgba(220, 38, 38, 0.15));
    color: #991b1b;
    border-color: rgba(239, 68, 68, 0.3);
}

.alert i {
    font-size: 1.2rem;
}

@media (max-width: 1024px) {
    .upload-item {
        grid-template-columns: 1fr 1fr auto;
        gap: 1.25rem;
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
    
    .app-info {
        padding: 1.25rem;
        margin-bottom: 2rem;
    }
    
    .app-info h3 {
        font-size: 1.2rem;
        text-align: center;
        margin-bottom: 1rem;
    }
    
    .app-info p {
        text-align: center;
        font-size: 1rem;
    }
    
    .requirements-section h3,
    .upload-section h3 {
        font-size: 1.3rem;
        margin-bottom: 1.5rem;
        text-align: center;
    }
    
    .requirement-item {
        padding: 1.25rem;
        gap: 1rem;
        text-align: center;
        align-items: center;
    }
    
    .req-info {
        text-align: center;
    }
    
    .req-badge {
        align-self: center;
    }
    
    .upload-item {
        display: flex;
        flex-direction: column;
        gap: 1rem;
        padding: 1.25rem;
        text-align: center;
    }
    
    .upload-item select,
    .upload-item input[type="file"] {
        width: 100%;
        padding: 1rem;
        font-size: 0.95rem;
    }
    
    .btn-remove {
        align-self: center;
        width: 50px;
        height: 50px;
    }
    
    .btn-add-upload {
        padding: 1rem 1.5rem;
        font-size: 0.95rem;
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
    
    .app-info {
        padding: 1rem;
    }
    
    .app-info h3 {
        font-size: 1.1rem;
    }
    
    .app-info p {
        font-size: 0.95rem;
    }
    
    .requirements-section h3,
    .upload-section h3 {
        font-size: 1.2rem;
    }
    
    .requirement-item {
        padding: 1rem;
    }
    
    .req-icon {
        width: 40px;
        height: 40px;
        font-size: 1.1rem;
    }
    
    .req-info h4 {
        font-size: 1rem;
    }
    
    .req-info p {
        font-size: 0.9rem;
    }
    
    .upload-item {
        padding: 1rem;
    }
    
    .upload-item select,
    .upload-item input[type="file"] {
        padding: 0.875rem;
        font-size: 0.9rem;
    }
    
    .btn-remove {
        width: 45px;
        height: 45px;
    }
    
    .btn-add-upload {
        padding: 0.875rem 1.25rem;
        font-size: 0.9rem;
    }
    
    .btn-submit {
        padding: 1.125rem 1.75rem;
        font-size: 0.95rem;
    }
}
</style>

<main class="main-content">
    <section class="hero-application">
        <h1>Upload Required Documents</h1>
        <p>Please upload all required documents for your visa application</p>
    </section>

    <section class="application-section">
        <div class="application-container">
            <?php if (isset($error)): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i> <?= $error ?>
                </div>
            <?php endif; ?>

            <div class="step-indicator">
                <div class="step completed">
                    <div class="step-number">1</div>
                    <span class="step-text">Application Details</span>
                </div>
                <div class="step-line"></div>
                <div class="step active">
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
                <?php if ($application): ?>
                    <div class="app-info">
                        <h3>Application: <?= htmlspecialchars($application['application_number']) ?></h3>
                        <p>Service: <?= htmlspecialchars($application['service_name']) ?></p>
                    </div>
                <?php endif; ?>

                <div class="requirements-section">
                    <h3>Required Documents</h3>
                    <?php if (!empty($documents)): ?>
                        <?php foreach ($documents as $doc): ?>
                            <div class="requirement-item">
                                <div class="req-icon">
                                    <i class="fas fa-<?= $doc['type'] === 'image' ? 'image' : 'file-pdf' ?>"></i>
                                </div>
                                <div class="req-info">
                                    <h4><?= htmlspecialchars($doc['name']) ?></h4>
                                    <p><?= htmlspecialchars($doc['description']) ?> (<?= strtoupper($doc['type']) ?>, max <?= $doc['maxSize'] ?>MB)</p>
                                </div>
                                <span class="req-badge <?= $doc['required'] ? 'required' : 'optional' ?>">
                                    <?= $doc['required'] ? 'Required' : 'Optional' ?>
                                </span>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="requirement-item">
                            <div class="req-icon"><i class="fas fa-file"></i></div>
                            <div class="req-info">
                                <h4>Standard Documents</h4>
                                <p>Please upload your required documents for this service</p>
                            </div>
                            <span class="req-badge required">Required</span>
                        </div>
                    <?php endif; ?>
                </div>

                <form method="POST" enctype="multipart/form-data">
                    <div class="upload-section">
                        <h3>Upload Documents</h3>
                        <div id="upload-container">
                            <div class="upload-item">
                                <select name="document_types[]" required>
                                    <option value="">Select Document Type</option>
                                    <?php if (!empty($documents)): ?>
                                        <?php foreach ($documents as $doc): ?>
                                            <option value="<?= htmlspecialchars($doc['name']) ?>" data-required="<?= $doc['required'] ? '1' : '0' ?>">
                                                <?= htmlspecialchars($doc['name']) ?><?= $doc['required'] ? ' *' : '' ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <option value="Document">Document</option>
                                    <?php endif; ?>
                                </select>
                                <input type="file" name="documents[]" accept=".pdf,.jpg,.jpeg,.png" required>
                            </div>
                        </div>
                        <button type="button" class="btn-add-upload" onclick="addUploadField()">
                            <i class="fas fa-plus"></i> Add Another Document
                        </button>
                    </div>

                    <button type="submit" class="btn-submit">
                        <i class="fas fa-arrow-right"></i>
                        Continue to Payment
                    </button>
                </form>
            </div>
        </div>
    </section>
</main>

<script>
const documents = <?= json_encode($documents) ?>;

function addUploadField() {
    const container = document.getElementById('upload-container');
    const newField = document.createElement('div');
    newField.className = 'upload-item';
    
    let optionsHtml = '<option value="">Select Document Type</option>';
    if (documents && documents.length > 0) {
        documents.forEach(doc => {
            const required = doc.required ? ' *' : '';
            optionsHtml += `<option value="${doc.name}" data-required="${doc.required ? '1' : '0'}">${doc.name}${required}</option>`;
        });
    } else {
        optionsHtml += '<option value="Document">Document</option>';
    }
    
    newField.innerHTML = `
        <select name="document_types[]" required>
            ${optionsHtml}
        </select>
        <input type="file" name="documents[]" accept=".pdf,.jpg,.jpeg,.png" required>
        <button type="button" class="btn-remove" onclick="this.parentElement.remove()">
            <i class="fas fa-trash"></i>
        </button>
    `;
    container.appendChild(newField);
}
</script>

<?php include 'includes/footer.php'; ?>