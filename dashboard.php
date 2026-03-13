<?php
ob_start();
session_start();
require_once 'config/database.php';

// Strict authentication check
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Handle profile update - must be before any HTML output
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_profile') {
    try {
        // Server-side validation for required fields
        $required_fields = ['first_name', 'last_name', 'email', 'date_of_birth', 'country'];
        $missing_fields = [];
        
        foreach ($required_fields as $field) {
            if (empty($_POST[$field]) || trim($_POST[$field]) === '') {
                $missing_fields[] = $field;
            }
        }
        
        if (!empty($missing_fields)) {
            $_SESSION['error_message'] = 'Required fields missing';
            $_SESSION['error_details'] = 'Please fill in: ' . implode(', ', array_map('ucwords', str_replace('_', ' ', $missing_fields)));
            header('Location: dashboard.php');
            exit;
        }
        
        // Validate email format
        if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
            $_SESSION['error_message'] = 'Invalid email format';
            $_SESSION['error_details'] = 'Please enter a valid email address (e.g., user@example.com)';
            header('Location: dashboard.php');
            exit;
        }
        
        $pdo = getConnection();
        
        // Handle empty values properly
        $phone = !empty($_POST['phone']) ? $_POST['phone'] : null;
        $gender = !empty($_POST['gender']) ? $_POST['gender'] : null;
        $nationality = !empty($_POST['nationality']) ? $_POST['nationality'] : null;
        $address = !empty($_POST['address']) ? $_POST['address'] : null;
        $city = !empty($_POST['city']) ? $_POST['city'] : null;
        $postal_code = !empty($_POST['postal_code']) ? $_POST['postal_code'] : null;
        $passport_number = !empty($_POST['passport_number']) ? $_POST['passport_number'] : null;
        $passport_expiry = !empty($_POST['passport_expiry']) ? $_POST['passport_expiry'] : null;
        $occupation = !empty($_POST['occupation']) ? $_POST['occupation'] : null;
        $emergency_contact_name = !empty($_POST['emergency_contact_name']) ? $_POST['emergency_contact_name'] : null;
        $emergency_contact_phone = !empty($_POST['emergency_contact_phone']) ? $_POST['emergency_contact_phone'] : null;
        
        $stmt = $pdo->prepare("
            UPDATE users SET 
                first_name = ?, last_name = ?, email = ?, phone = ?, 
                date_of_birth = ?, gender = ?, country = ?, nationality = ?, 
                address = ?, city = ?, postal_code = ?, passport_number = ?, 
                passport_expiry = ?, occupation = ?, emergency_contact_name = ?, 
                emergency_contact_phone = ?
            WHERE id = ?
        ");
        
        $result = $stmt->execute([
            $_POST['first_name'], $_POST['last_name'], $_POST['email'], $phone,
            $_POST['date_of_birth'], $gender, $_POST['country'], $nationality,
            $address, $city, $postal_code, $passport_number,
            $passport_expiry, $occupation, $emergency_contact_name,
            $emergency_contact_phone, $_SESSION['user_id']
        ]);
        
        if ($result) {
            // Verify the update actually happened
            if ($stmt->rowCount() > 0) {
                $_SESSION['success_message'] = 'Profile updated successfully!';
                $_SESSION['success_details'] = 'All changes have been saved to your profile.';
            } else {
                $_SESSION['error_message'] = 'No changes detected';
                $_SESSION['error_details'] = 'The submitted data is identical to your current profile information.';
            }
        } else {
            $_SESSION['error_message'] = 'Database update failed';
            $_SESSION['error_details'] = 'Unable to execute the update query. Please contact support.';
        }
    } catch (Exception $e) {
        error_log('Profile update error: ' . $e->getMessage());
        $_SESSION['error_message'] = 'System error occurred';
        $_SESSION['error_details'] = 'Database connection or query failed. Error logged for review.';
    }
    header('Location: dashboard.php');
    exit;
}

try {
    $pdo = getConnection();
    
    // Get authenticated user data
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? AND is_active = 1");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        session_destroy();
        header('Location: login.php');
        exit;
    }
    
    // Get user applications with complete data
    $stmt = $pdo->prepare("
        SELECT 
            a.*,
            s.name as service_name, 
            s.currency,
            ast.status_name,
            c.name as destination_country,
            COUNT(d.id) as document_count
        FROM applications a
        LEFT JOIN services s ON a.service_id = s.id
        LEFT JOIN application_statuses ast ON a.status_id = ast.id
        LEFT JOIN countries c ON a.destination_country_id = c.id
        LEFT JOIN documents d ON a.id = d.application_id
        WHERE a.user_id = ?
        GROUP BY a.id
        ORDER BY a.submitted_at DESC
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $applications = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Calculate profile completion
    $requiredFields = ['first_name', 'last_name', 'email', 'phone', 'date_of_birth', 'country', 'passport_number'];
    $completedFields = 0;
    foreach ($requiredFields as $field) {
        if (!empty($user[$field])) {
            $completedFields++;
        }
    }
    $profileCompletion = round(($completedFields / count($requiredFields)) * 100);
    
} catch (Exception $e) {
    error_log("Dashboard error: " . $e->getMessage());
    $user = null;
    $applications = [];
    $profileCompletion = 0;
}

$pageTitle = 'Dashboard - A to Z Global Link Solutions';
include 'includes/header.php';
?>

<style>
:root {
    --primary-blue: #2563eb;
    --light-blue: #dbeafe;
    --dark-blue: #1e40af;
    --success-green: #10b981;
    --warning-orange: #f59e0b;
    --danger-red: #ef4444;
    --gray-50: #f9fafb;
    --gray-100: #f3f4f6;
    --gray-200: #e5e7eb;
    --gray-600: #4b5563;
    --gray-800: #1f2937;
    --white: #ffffff;
}

.dashboard-wrapper {
    background: linear-gradient(135deg, var(--light-blue) 0%, var(--white) 50%, var(--gray-50) 100%);
    min-height: 100vh;
    padding-top: 80px;
}

.dashboard-container {
    max-width: 1400px;
    margin: 0 auto;
    padding: 2rem;
}

.dashboard-header {
    background: var(--white);
    border-radius: 16px;
    padding: 2rem;
    margin-bottom: 2rem;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    border: 1px solid var(--gray-200);
}

.user-info {
    display: flex;
    align-items: center;
    gap: 2rem;
    position: relative;
}

.user-avatar {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--primary-blue), var(--dark-blue));
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--white);
    font-size: 2.5rem;
    font-weight: 700;
    position: relative;
    overflow: hidden;
    border: 4px solid var(--white);
    box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
}

.user-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.avatar-upload {
    position: absolute;
    bottom: -5px;
    right: -5px;
    background: var(--success-green);
    border: 3px solid var(--white);
    border-radius: 50%;
    width: 36px;
    height: 36px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.3s ease;
}

.avatar-upload:hover {
    background: #059669;
    transform: scale(1.1);
}

.user-details h1 {
    color: var(--gray-800);
    font-size: 2rem;
    font-weight: 700;
    margin: 0 0 0.5rem 0;
}

.user-details p {
    color: var(--gray-600);
    margin: 0.25rem 0;
}

.profile-completion {
    margin-top: 1rem;
}

.completion-bar {
    background: var(--gray-200);
    height: 8px;
    border-radius: 4px;
    overflow: hidden;
    margin-top: 0.5rem;
}

.completion-fill {
    height: 100%;
    background: linear-gradient(90deg, var(--success-green), #34d399);
    transition: width 0.3s ease;
}

.alert {
    display: flex;
    align-items: flex-start;
    gap: 1rem;
    padding: 1.25rem;
    margin-bottom: 1.5rem;
    border-radius: 12px;
    border-left: 4px solid;
    position: relative;
    animation: slideIn 0.3s ease-out;
}

.alert-success {
    background: linear-gradient(135deg, #d1fae5 0%, #ecfdf5 100%);
    border-left-color: var(--success-green);
    color: #065f46;
}

.alert-error {
    background: linear-gradient(135deg, #fee2e2 0%, #fef2f2 100%);
    border-left-color: var(--danger-red);
    color: #dc2626;
}

.alert-icon {
    font-size: 1.5rem;
    margin-top: 0.125rem;
}

.alert-content h4 {
    margin: 0 0 0.5rem 0;
    font-weight: 600;
    font-size: 1.1rem;
}

.alert-content p {
    margin: 0;
    opacity: 0.8;
    font-size: 0.95rem;
}

.alert-close {
    position: absolute;
    top: 1rem;
    right: 1rem;
    background: none;
    border: none;
    font-size: 1.5rem;
    cursor: pointer;
    opacity: 0.6;
    transition: opacity 0.2s;
}

.alert-close:hover {
    opacity: 1;
}

@keyframes slideIn {
    from {
        transform: translateY(-20px);
        opacity: 0;
    }
    to {
        transform: translateY(0);
        opacity: 1;
    }
}

.dashboard-tabs {
    background: var(--white);
    border-radius: 16px;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    border: 1px solid var(--gray-200);
    overflow: hidden;
}

.tab-navigation {
    display: flex;
    background: var(--gray-50);
    border-bottom: 1px solid var(--gray-200);
}

.tab-button {
    flex: 1;
    padding: 1.25rem 2rem;
    border: none;
    background: transparent;
    color: var(--gray-600);
    font-weight: 600;
    font-size: 1rem;
    cursor: pointer;
    transition: all 0.3s ease;
    position: relative;
}

.tab-button.active {
    color: var(--primary-blue);
    background: var(--white);
}

.tab-button.active::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    height: 3px;
    background: var(--primary-blue);
}

.tab-content {
    display: none;
    padding: 2.5rem;
}

.tab-content.active {
    display: block;
}

.form-section {
    margin-bottom: 2.5rem;
}

.section-title {
    color: var(--gray-800);
    font-size: 1.25rem;
    font-weight: 700;
    margin-bottom: 1.5rem;
    padding-bottom: 0.5rem;
    border-bottom: 2px solid var(--light-blue);
}

.form-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 1.5rem;
}

.form-field {
    display: flex;
    flex-direction: column;
}

.form-field label {
    color: var(--gray-800);
    font-weight: 600;
    margin-bottom: 0.5rem;
    font-size: 0.9rem;
}

.form-field input,
.form-field select {
    padding: 0.875rem 1rem;
    border: 2px solid var(--gray-200);
    border-radius: 8px;
    font-size: 1rem;
    transition: all 0.3s ease;
    background: var(--white);
}

.form-field input:focus,
.form-field select:focus {
    outline: none;
    border-color: var(--primary-blue);
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
}

.form-field input:not(:placeholder-shown) {
    border-color: var(--success-green);
}

.save-button {
    background: linear-gradient(135deg, var(--primary-blue), var(--dark-blue));
    color: var(--white);
    padding: 1rem 2.5rem;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    font-size: 1rem;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
}

.save-button:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(37, 99, 235, 0.4);
}

.form-actions {
    display: flex;
    gap: 1rem;
    margin-top: 2rem;
}

.update-button {
    background: linear-gradient(135deg, var(--success-green), #059669);
    color: var(--white);
    padding: 1rem 2.5rem;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    font-size: 1rem;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
}

.update-button:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(16, 185, 129, 0.4);
}

.delete-button {
    background: linear-gradient(135deg, var(--danger-red), #dc2626);
    color: var(--white);
    padding: 1rem 2.5rem;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    font-size: 1rem;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
}

.delete-button:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(239, 68, 68, 0.4);
}

.application-grid {
    display: grid;
    gap: 1.5rem;
}

.application-card {
    background: var(--white);
    border: 1px solid var(--gray-200);
    border-radius: 12px;
    padding: 1.5rem;
    transition: all 0.3s ease;
}

.application-card:hover {
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
    transform: translateY(-2px);
}

.app-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
}

.app-number {
    font-weight: 700;
    color: var(--primary-blue);
    font-size: 1.1rem;
}

.status-badge {
    padding: 0.375rem 0.875rem;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.status-submitted { background: var(--light-blue); color: var(--primary-blue); }
.status-under-review { background: #fef3c7; color: #d97706; }
.status-processing { background: #e0e7ff; color: #3730a3; }
.status-approved { background: #d1fae5; color: #065f46; }
.status-completed { background: #d1fae5; color: #065f46; }
.status-rejected { background: #fee2e2; color: #dc2626; }

.app-details {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
    margin-top: 1rem;
}

.detail-item {
    display: flex;
    flex-direction: column;
}

.detail-label {
    font-size: 0.8rem;
    color: var(--gray-600);
    font-weight: 500;
    margin-bottom: 0.25rem;
}

.detail-value {
    font-weight: 600;
    color: var(--gray-800);
}

.empty-state {
    text-align: center;
    padding: 3rem;
    color: var(--gray-600);
}

.cta-button {
    background: var(--success-green);
    color: var(--white);
    padding: 0.875rem 2rem;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 600;
    display: inline-block;
    margin-top: 1rem;
    transition: all 0.3s ease;
}

.cta-button:hover {
    background: #059669;
    transform: translateY(-2px);
}

@media (max-width: 768px) {
    .dashboard-container { padding: 1rem; }
    .user-info { flex-direction: column; text-align: center; }
    .form-grid { grid-template-columns: 1fr; }
    .tab-navigation { flex-direction: column; }
}
</style>

<div class="dashboard-wrapper">
    <div class="dashboard-container">
        <!-- Dashboard Header -->
        <div class="dashboard-header">
            <div class="user-info">
                    <div style="position: relative;">
                        <div class="user-avatar" id="userAvatar">
                            <?php if (!empty($user['profile_picture'])): ?>
                                <img src="uploads/profiles/<?= htmlspecialchars($user['profile_picture']) ?>" alt="Profile">
                            <?php else: ?>
                                <?= strtoupper(substr($user['first_name'] ?? 'U', 0, 1)) ?>
                            <?php endif; ?>
                        </div>
                        <div class="avatar-upload">
                            <input type="file" id="profilePictureInput" accept="image/*" style="display: none;" onchange="handleProfileUpload()">
                            <i class="fas fa-camera" style="cursor: pointer;" onclick="document.getElementById('profilePictureInput').click()"></i>
                        </div>
                    </div>
                <div class="user-details">
                    <h1><?= htmlspecialchars(trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '')) ?: 'Welcome') ?></h1>
                    <p><i class="fas fa-envelope"></i> <?= htmlspecialchars($user['email'] ?? '') ?></p>
                    <?php if (!empty($user['phone'])): ?>
                        <p><i class="fas fa-phone"></i> <?= htmlspecialchars($user['phone']) ?></p>
                    <?php endif; ?>
                    <?php if (!empty($user['created_at'])): ?>
                        <p><i class="fas fa-calendar"></i> Member since <?= date('F Y', strtotime($user['created_at'])) ?></p>
                    <?php endif; ?>
                    <div class="profile-completion">
                        <p>Profile Completion: <strong><?= $profileCompletion ?>%</strong></p>
                        <div class="completion-bar">
                            <div class="completion-fill" style="width: <?= $profileCompletion ?>%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Dashboard Tabs -->
        <div class="dashboard-tabs">
            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="alert alert-success">
                    <div class="alert-icon"><i class="fas fa-check-circle"></i></div>
                    <div class="alert-content">
                        <h4><?= htmlspecialchars($_SESSION['success_message']) ?></h4>
                        <p><?= htmlspecialchars($_SESSION['success_details'] ?? '') ?></p>
                    </div>
                    <button class="alert-close" onclick="this.parentElement.remove()">&times;</button>
                </div>
                <?php unset($_SESSION['success_message'], $_SESSION['success_details']); ?>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['error_message'])): ?>
                <div class="alert alert-error">
                    <div class="alert-icon"><i class="fas fa-exclamation-triangle"></i></div>
                    <div class="alert-content">
                        <h4><?= htmlspecialchars($_SESSION['error_message']) ?></h4>
                        <p><?= htmlspecialchars($_SESSION['error_details'] ?? '') ?></p>
                    </div>
                    <button class="alert-close" onclick="this.parentElement.remove()">&times;</button>
                </div>
                <?php unset($_SESSION['error_message'], $_SESSION['error_details']); ?>
            <?php endif; ?>
            <div class="tab-navigation">
                <button class="tab-button active" onclick="switchTab('profile', this)">
                    <i class="fas fa-user"></i> Profile Management
                </button>
                <button class="tab-button" onclick="switchTab('applications', this)">
                    <i class="fas fa-file-alt"></i> My Applications (<?= count($applications) ?>)
                </button>
                <button class="tab-button" onclick="switchTab('settings', this)">
                    <i class="fas fa-cog"></i> Settings
                </button>
                <button class="tab-button" onclick="switchTab('support', this)">
                    <i class="fas fa-headset"></i> Support
                </button>
            </div>

            <!-- Profile Tab -->
            <div id="profile-tab" class="tab-content active">
                <form id="profileForm" method="POST" action="dashboard.php">
                    <input type="hidden" name="action" value="update_profile">
                    <div class="form-section">
                        <h3 class="section-title">Personal Information</h3>
                        <div class="form-grid">
                            <div class="form-field">
                                <label>First Name *</label>
                                <input type="text" name="first_name" value="<?= htmlspecialchars($user['first_name'] ?? '') ?>" required>
                            </div>
                            <div class="form-field">
                                <label>Last Name *</label>
                                <input type="text" name="last_name" value="<?= htmlspecialchars($user['last_name'] ?? '') ?>" required>
                            </div>
                            <div class="form-field">
                                <label>Email Address *</label>
                                <input type="email" name="email" value="<?= htmlspecialchars($user['email'] ?? '') ?>" required>
                            </div>
                            <div class="form-field">
                                <label>Phone Number</label>
                                <input type="tel" name="phone" value="<?= htmlspecialchars($user['phone'] ?? '') ?>" placeholder="+250 xxx xxx xxx">
                            </div>
                            <div class="form-field">
                                <label>Date of Birth *</label>
                                <input type="date" name="date_of_birth" value="<?= $user['date_of_birth'] ?? '' ?>" required>
                            </div>
                            <div class="form-field">
                                <label>Gender</label>
                                <select name="gender">
                                    <option value="">Select Gender</option>
                                    <option value="male" <?= ($user['gender'] ?? '') === 'male' ? 'selected' : '' ?>>Male</option>
                                    <option value="female" <?= ($user['gender'] ?? '') === 'female' ? 'selected' : '' ?>>Female</option>
                                    <option value="other" <?= ($user['gender'] ?? '') === 'other' ? 'selected' : '' ?>>Other</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="form-section">
                        <h3 class="section-title">Address & Location</h3>
                        <div class="form-grid">
                            <div class="form-field">
                                <label>Country of Residence *</label>
                                <input type="text" name="country" value="<?= htmlspecialchars($user['country'] ?? '') ?>" required>
                            </div>
                            <div class="form-field">
                                <label>Nationality</label>
                                <input type="text" name="nationality" value="<?= htmlspecialchars($user['nationality'] ?? '') ?>">
                            </div>
                            <div class="form-field">
                                <label>Street Address</label>
                                <input type="text" name="address" value="<?= htmlspecialchars($user['address'] ?? '') ?>">
                            </div>
                            <div class="form-field">
                                <label>City</label>
                                <input type="text" name="city" value="<?= htmlspecialchars($user['city'] ?? '') ?>">
                            </div>
                            <div class="form-field">
                                <label>Postal Code</label>
                                <input type="text" name="postal_code" value="<?= htmlspecialchars($user['postal_code'] ?? '') ?>">
                            </div>
                        </div>
                    </div>

                    <div class="form-section">
                        <h3 class="section-title">Travel & Professional Information</h3>
                        <div class="form-grid">
                            <div class="form-field">
                                <label>Passport Number</label>
                                <input type="text" name="passport_number" value="<?= htmlspecialchars($user['passport_number'] ?? '') ?>">
                            </div>
                            <div class="form-field">
                                <label>Passport Expiry Date</label>
                                <input type="date" name="passport_expiry" value="<?= $user['passport_expiry'] ?? '' ?>">
                            </div>
                            <div class="form-field">
                                <label>Occupation</label>
                                <input type="text" name="occupation" value="<?= htmlspecialchars($user['occupation'] ?? '') ?>">
                            </div>
                        </div>
                    </div>

                    <div class="form-section">
                        <h3 class="section-title">Emergency Contact</h3>
                        <div class="form-grid">
                            <div class="form-field">
                                <label>Emergency Contact Name</label>
                                <input type="text" name="emergency_contact_name" value="<?= htmlspecialchars($user['emergency_contact_name'] ?? '') ?>">
                            </div>
                            <div class="form-field">
                                <label>Emergency Contact Phone</label>
                                <input type="tel" name="emergency_contact_phone" value="<?= htmlspecialchars($user['emergency_contact_phone'] ?? '') ?>">
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="save-button">
                        <i class="fas fa-save"></i> Save Changes
                    </button>
                </form>
            </div>

            <!-- Applications Tab -->
            <div id="applications-tab" class="tab-content">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
                    <h3 class="section-title" style="margin: 0;">My Applications</h3>
                    <a href="application.php" class="cta-button">
                        <i class="fas fa-plus"></i> New Application
                    </a>
                </div>

                <?php if (!empty($applications)): ?>
                    <div class="application-grid">
                        <?php foreach ($applications as $app): ?>
                            <div class="application-card">
                                <div class="app-header">
                                    <div class="app-number"><?= htmlspecialchars($app['application_number']) ?></div>
                                    <div class="status-badge status-<?= strtolower(str_replace(' ', '-', $app['status_name'])) ?>">
                                        <?= htmlspecialchars($app['status_name']) ?>
                                    </div>
                                </div>
                                <h4 style="margin: 0 0 1rem 0; color: var(--gray-800);"><?= htmlspecialchars($app['service_name']) ?></h4>
                                
                                <div class="app-details">
                                    <?php if ($app['destination_country']): ?>
                                        <div class="detail-item">
                                            <span class="detail-label">Destination</span>
                                            <span class="detail-value"><?= htmlspecialchars($app['destination_country']) ?></span>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <div class="detail-item">
                                        <span class="detail-label">Amount</span>
                                        <span class="detail-value"><?= number_format($app['total_amount'], 2) ?> <?= htmlspecialchars($app['currency']) ?></span>
                                    </div>
                                    
                                    <div class="detail-item">
                                        <span class="detail-label">Submitted</span>
                                        <span class="detail-value"><?= date('M d, Y', strtotime($app['submitted_at'])) ?></span>
                                    </div>
                                    
                                    <div class="detail-item">
                                        <span class="detail-label">Documents</span>
                                        <span class="detail-value"><?= $app['document_count'] ?> uploaded</span>
                                    </div>
                                </div>

                                <?php if ($app['payment_plan'] === 'partial'): ?>
                                    <div style="margin-top: 1rem; padding: 1rem; background: var(--gray-50); border-radius: 8px;">
                                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                                            <div class="detail-item">
                                                <span class="detail-label">Initial Payment</span>
                                                <span class="detail-value"><?= number_format($app['initial_payment_amount'], 2) ?> <?= $app['currency'] ?> 
                                                    <span style="color: <?= $app['initial_payment_status'] === 'completed' ? 'var(--success-green)' : 'var(--warning-orange)' ?>">
                                                        (<?= ucfirst($app['initial_payment_status']) ?>)
                                                    </span>
                                                </span>
                                            </div>
                                            <div class="detail-item">
                                                <span class="detail-label">Remaining</span>
                                                <span class="detail-value"><?= number_format($app['remaining_payment_amount'], 2) ?> <?= $app['currency'] ?>
                                                    <span style="color: <?= $app['final_payment_status'] === 'completed' ? 'var(--success-green)' : 'var(--warning-orange)' ?>">
                                                        (<?= ucfirst($app['final_payment_status']) ?>)
                                                    </span>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-file-alt" style="font-size: 4rem; color: var(--gray-300); margin-bottom: 1rem;"></i>
                        <h3>No Applications Yet</h3>
                        <p>Start your journey with us by submitting your first application.</p>
                        <a href="application.php" class="cta-button">
                            <i class="fas fa-plus"></i> Create First Application
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
function switchTab(tabName, element) {
    document.querySelectorAll('.tab-button').forEach(btn => btn.classList.remove('active'));
    document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));
    
    if (element) {
        element.classList.add('active');
    } else {
        document.querySelector(`[onclick*="${tabName}"]`).classList.add('active');
    }
    document.getElementById(tabName + '-tab').classList.add('active');
}

// Profile picture upload - using direct event binding
function handleProfileUpload() {
    const fileInput = document.getElementById('profilePictureInput');
    const file = fileInput.files[0];
    
    if (!file) {
        console.log('No file selected');
        return;
    }
    
    console.log('File selected:', file.name, file.type, file.size);
    
    // Validate file
    const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
    if (!allowedTypes.includes(file.type)) {
        alert('Please select a valid image file (JPG, PNG, GIF)');
        return;
    }
    
    if (file.size > 5 * 1024 * 1024) {
        alert('File size must be less than 5MB');
        return;
    }
    
    const formData = new FormData();
    formData.append('profile_picture', file);
    
    console.log('Uploading file...');
    
    const avatar = document.getElementById('userAvatar');
    const originalContent = avatar.innerHTML;
    avatar.innerHTML = '<i class="fas fa-spinner fa-spin" style="font-size: 2rem; color: white;"></i>';
    
    fetch('upload_profile.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.text())
    .then(text => {
        console.log('Response:', text);
        try {
            const data = JSON.parse(text);
            if (data.success) {
                avatar.innerHTML = `<img src="uploads/profiles/${data.filename}?t=${Date.now()}" alt="Profile">`;
                alert('Profile picture updated successfully!');
            } else {
                avatar.innerHTML = originalContent;
                alert('Upload failed: ' + (data.error || 'Unknown error'));
            }
        } catch (e) {
            console.error('JSON parse error:', e);
            avatar.innerHTML = originalContent;
            alert('Upload failed: Invalid response');
        }
    })
    .catch(error => {
        console.error('Upload error:', error);
        avatar.innerHTML = originalContent;
        alert('Upload failed: ' + error.message);
    });
    
    fileInput.value = '';
}

// Profile form submission - simple form submit
document.getElementById('profileForm').addEventListener('submit', function(e) {
    const submitBtn = this.querySelector('.save-button');
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
    submitBtn.disabled = true;
});
</script>

</body>
</html>
            <!-- Settings Tab -->
            <div id="settings-tab" class="tab-content">
                <div class="settings-grid">
                    <div class="setting-card">
                        <h4><i class="fas fa-key"></i> Change Password</h4>
                        <p>Update your account password</p>
                        <button class="btn-secondary">Change Password</button>
                    </div>
                    <div class="setting-card">
                        <h4><i class="fas fa-bell"></i> Notifications</h4>
                        <p>Manage email notifications</p>
                        <button class="btn-secondary">Manage Notifications</button>
                    </div>
                    <div class="setting-card">
                        <h4><i class="fas fa-download"></i> Export Data</h4>
                        <p>Download your data</p>
                        <button class="btn-secondary">Export Data</button>
                    </div>
                    <div class="setting-card">
                        <h4><i class="fas fa-trash"></i> Delete Account</h4>
                        <p>Permanently delete account</p>
                        <button class="btn-danger" onclick="alert('Feature coming soon')">Delete Account</button>
                    </div>
                </div>
            </div>

            <!-- Support Tab -->
            <div id="support-tab" class="tab-content">
                <div class="support-options">
                    <div class="support-card">
                        <h4><i class="fas fa-comments"></i> Live Chat</h4>
                        <p>Chat with support team</p>
                        <button class="btn-primary" onclick="alert('Chat feature coming soon')">Start Chat</button>
                    </div>
                    <div class="support-card">
                        <h4><i class="fas fa-envelope"></i> Email Support</h4>
                        <p>Send us an email</p>
                        <button class="btn-secondary" onclick="location.href='mailto:support@atozglobal.com'">Send Email</button>
                    </div>
                    <div class="support-card">
                        <h4><i class="fas fa-phone"></i> Phone Support</h4>
                        <p>Call us directly</p>
                        <button class="btn-secondary" onclick="location.href='tel:+250123456789'">Call Now</button>
                    </div>
                    <div class="support-card">
                        <h4><i class="fas fa-question-circle"></i> FAQ</h4>
                        <p>Common questions</p>
                        <button class="btn-secondary" onclick="alert('FAQ page coming soon')">View FAQ</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.settings-grid, .support-options {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 1.5rem;
    margin-top: 1rem;
}

.setting-card, .support-card {
    background: var(--white);
    border: 1px solid var(--gray-200);
    border-radius: 12px;
    padding: 1.5rem;
    text-align: center;
    transition: all 0.3s ease;
}

.setting-card:hover, .support-card:hover {
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
    transform: translateY(-2px);
}

.setting-card h4, .support-card h4 {
    color: var(--gray-800);
    margin-bottom: 0.5rem;
    font-size: 1.1rem;
}

.setting-card p, .support-card p {
    color: var(--gray-600);
    margin-bottom: 1rem;
    font-size: 0.9rem;
}

.btn-primary {
    background: var(--primary-blue);
    color: white;
    padding: 0.75rem 1.5rem;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 600;
    transition: all 0.3s ease;
}

.btn-secondary {
    background: var(--gray-100);
    color: var(--gray-800);
    padding: 0.75rem 1.5rem;
    border: 1px solid var(--gray-200);
    border-radius: 8px;
    cursor: pointer;
    font-weight: 600;
    transition: all 0.3s ease;
}

.btn-danger {
    background: var(--danger-red);
    color: white;
    padding: 0.75rem 1.5rem;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 600;
    transition: all 0.3s ease;
}

.btn-primary:hover, .btn-secondary:hover, .btn-danger:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
}
</style>