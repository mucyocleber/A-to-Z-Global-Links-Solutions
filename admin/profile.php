<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit;
}

require_once '../config/database.php';

try {
    $pdo = getConnection();
    
    // Get current admin details
    $stmt = $pdo->prepare("SELECT * FROM admins WHERE id = ?");
    $stmt->execute([$_SESSION['admin_id']]);
    $admin = $stmt->fetch();
    
    if (!$admin) {
        header('Location: logout.php');
        exit;
    }
    
} catch (Exception $e) {
    $error = "Database error occurred";
}

$pageTitle = 'Profile Settings';
?>

<?php include 'includes/header.php'; ?>
<?php include 'includes/sidebar.php'; ?>

<main class="main-content">
    <div class="content-header">
        <h1 class="page-title">Profile Settings</h1>
        <p class="page-subtitle">Manage your admin account details and security settings</p>
    </div>
    
    <div class="profile-container">
        <div class="profile-card">
            <div class="card-header">
                <div class="profile-avatar-section">
                    <div class="profile-avatar-large">
                        <i class="fas fa-user-circle"></i>
                    </div>
                    <div class="profile-info">
                        <h2><?= htmlspecialchars($admin['username']) ?></h2>
                        <p><?= htmlspecialchars($admin['email']) ?></p>
                        <span class="status-badge <?= $admin['is_active'] ? 'active' : 'inactive' ?>">
                            <?= $admin['is_active'] ? 'Active' : 'Inactive' ?>
                        </span>
                    </div>
                </div>
            </div>
            
            <div class="card-content">
                <div class="tabs">
                    <button class="tab-btn active" onclick="switchTab('account')">
                        <i class="fas fa-user"></i>
                        Account Details
                    </button>
                    <button class="tab-btn" onclick="switchTab('security')">
                        <i class="fas fa-lock"></i>
                        Security
                    </button>
                </div>
                
                <!-- Account Details Tab -->
                <div id="account-tab" class="tab-content active">
                    <form id="accountForm" onsubmit="updateAccount(event)">
                        <div class="form-section">
                            <h3>Basic Information</h3>
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="username">Username *</label>
                                    <input type="text" id="username" name="username" value="<?= htmlspecialchars($admin['username']) ?>" required class="form-control">
                                </div>
                                <div class="form-group">
                                    <label for="email">Email Address *</label>
                                    <input type="email" id="email" name="email" value="<?= htmlspecialchars($admin['email']) ?>" required class="form-control">
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="status">Account Status</label>
                                <select id="status" name="is_active" class="form-control">
                                    <option value="1" <?= $admin['is_active'] ? 'selected' : '' ?>>Active</option>
                                    <option value="0" <?= !$admin['is_active'] ? 'selected' : '' ?>>Inactive</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i>
                                Update Account
                            </button>
                        </div>
                    </form>
                </div>
                
                <!-- Security Tab -->
                <div id="security-tab" class="tab-content">
                    <form id="passwordForm" onsubmit="updatePassword(event)">
                        <div class="form-section">
                            <h3>Change Password</h3>
                            <div class="form-group">
                                <label for="current_password">Current Password *</label>
                                <input type="password" id="current_password" name="current_password" required class="form-control">
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="new_password">New Password *</label>
                                    <input type="password" id="new_password" name="new_password" required class="form-control" minlength="6">
                                </div>
                                <div class="form-group">
                                    <label for="confirm_password">Confirm New Password *</label>
                                    <input type="password" id="confirm_password" name="confirm_password" required class="form-control" minlength="6">
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-key"></i>
                                Update Password
                            </button>
                        </div>
                    </form>
                    
                    <div class="form-section">
                        <h3>Account Information</h3>
                        <div class="info-grid">
                            <div class="info-item">
                                <label>Account Created</label>
                                <span><?= date('F j, Y', strtotime($admin['created_at'])) ?></span>
                            </div>
                            <div class="info-item">
                                <label>Last Login</label>
                                <span><?= $admin['last_login'] ? date('F j, Y g:i A', strtotime($admin['last_login'])) : 'Never' ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<!-- Success Modal -->
<div id="successModal" class="modal-overlay">
    <div class="modal-content">
        <div class="modal-header success">
            <h3>Success</h3>
            <button class="modal-close" onclick="closeSuccessModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body">
            <div class="success-content">
                <i class="fas fa-check-circle"></i>
                <div id="successMessage"></div>
            </div>
            <div class="form-actions">
                <button type="button" class="btn btn-success" onclick="closeSuccessModal()">OK</button>
            </div>
        </div>
    </div>
</div>

<style>
.main-content {
    margin-left: 260px;
    margin-top: 70px;
    padding: 2rem;
    min-height: calc(100vh - 70px);
    background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 50%, #f1f5f9 100%);
}

.content-header {
    background: linear-gradient(135deg, #ffffff, #f8fafc);
    border-radius: 20px;
    padding: 2rem;
    margin-bottom: 2rem;
    box-shadow: 0 4px 20px rgba(59, 130, 246, 0.08);
    border: 1px solid rgba(255, 255, 255, 0.5);
}

.page-title {
    font-size: 2.5rem;
    font-weight: 900;
    color: #1e293b;
    margin-bottom: 0.5rem;
    background: linear-gradient(135deg, #3b82f6, #1d4ed8);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.page-subtitle {
    color: #64748b;
    font-size: 1.2rem;
    font-weight: 500;
}

.profile-container {
    max-width: 800px;
    margin: 0 auto;
}

.profile-card {
    background: white;
    border-radius: 20px;
    box-shadow: 0 8px 25px rgba(59, 130, 246, 0.1);
    border: 1px solid rgba(255, 255, 255, 0.5);
    overflow: hidden;
}

.card-header {
    background: linear-gradient(135deg, #3b82f6, #1d4ed8);
    color: white;
    padding: 2rem;
}

.profile-avatar-section {
    display: flex;
    align-items: center;
    gap: 1.5rem;
}

.profile-avatar-large {
    width: 80px;
    height: 80px;
    border-radius: 16px;
    background: rgba(255, 255, 255, 0.2);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 3rem;
    color: white;
}

.profile-info h2 {
    font-size: 1.8rem;
    font-weight: 700;
    margin-bottom: 0.5rem;
}

.profile-info p {
    font-size: 1rem;
    opacity: 0.9;
    margin-bottom: 0.75rem;
}

.status-badge {
    padding: 0.375rem 0.75rem;
    border-radius: 8px;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.status-badge.active {
    background: rgba(16, 185, 129, 0.2);
    color: #10b981;
}

.status-badge.inactive {
    background: rgba(239, 68, 68, 0.2);
    color: #ef4444;
}

.card-content {
    padding: 2rem;
}

.tabs {
    display: flex;
    gap: 0.5rem;
    margin-bottom: 2rem;
    border-bottom: 2px solid #f1f5f9;
}

.tab-btn {
    padding: 1rem 1.5rem;
    border: none;
    background: none;
    color: #64748b;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    border-radius: 12px 12px 0 0;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.tab-btn:hover {
    background: #f8fafc;
    color: #374151;
}

.tab-btn.active {
    background: #3b82f6;
    color: white;
    box-shadow: 0 4px 15px rgba(59, 130, 246, 0.3);
}

.tab-content {
    display: none;
}

.tab-content.active {
    display: block;
}

.form-section {
    margin-bottom: 2rem;
    padding-bottom: 2rem;
    border-bottom: 1px solid #f1f5f9;
}

.form-section:last-child {
    border-bottom: none;
    margin-bottom: 0;
    padding-bottom: 0;
}

.form-section h3 {
    font-size: 1.25rem;
    font-weight: 700;
    color: #1e293b;
    margin-bottom: 1.5rem;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1.5rem;
    margin-bottom: 1.5rem;
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-group label {
    display: block;
    margin-bottom: 0.75rem;
    color: #374151;
    font-weight: 600;
    font-size: 1rem;
}

.form-control {
    width: 100%;
    padding: 1rem;
    border: 2px solid rgba(59, 130, 246, 0.1);
    border-radius: 12px;
    font-size: 1rem;
    transition: all 0.3s ease;
    background: rgba(255, 255, 255, 0.9);
}

.form-control:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
    transform: translateY(-2px);
}

.form-actions {
    display: flex;
    gap: 1rem;
    justify-content: flex-end;
    margin-top: 2rem;
    padding-top: 2rem;
    border-top: 2px solid rgba(59, 130, 246, 0.1);
}

.btn {
    padding: 1rem 2rem;
    border: none;
    border-radius: 12px;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
}

.btn-primary {
    background: linear-gradient(135deg, #3b82f6, #1d4ed8);
    color: white;
    box-shadow: 0 8px 25px rgba(59, 130, 246, 0.3);
}

.btn-primary:hover {
    transform: translateY(-3px);
    box-shadow: 0 12px 35px rgba(59, 130, 246, 0.4);
}

.btn-success {
    background: linear-gradient(135deg, #10b981, #059669);
    color: white;
    box-shadow: 0 8px 25px rgba(16, 185, 129, 0.3);
}

.btn-success:hover {
    transform: translateY(-3px);
    box-shadow: 0 12px 35px rgba(16, 185, 129, 0.4);
}

.info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1.5rem;
}

.info-item {
    background: #f8fafc;
    padding: 1.5rem;
    border-radius: 12px;
    border: 1px solid #e2e8f0;
}

.info-item label {
    display: block;
    font-size: 0.875rem;
    font-weight: 600;
    color: #6b7280;
    margin-bottom: 0.5rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.info-item span {
    font-size: 1rem;
    font-weight: 600;
    color: #1e293b;
}

/* Modal Styles */
.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.6);
    backdrop-filter: blur(8px);
    display: none;
    align-items: center;
    justify-content: center;
    z-index: 1000;
}

.modal-overlay.show {
    display: flex;
    animation: fadeIn 0.3s ease;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

.modal-content {
    background: white;
    border-radius: 20px;
    box-shadow: 0 25px 50px rgba(0, 0, 0, 0.25);
    max-width: 500px;
    width: 90%;
    overflow: hidden;
    animation: slideUp 0.3s ease;
}

@keyframes slideUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.modal-header {
    padding: 2rem;
    background: linear-gradient(135deg, #3b82f6, #1d4ed8);
    color: white;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.modal-header.success {
    background: linear-gradient(135deg, #10b981, #059669);
}

.modal-header h3 {
    margin: 0;
    font-size: 1.5rem;
    font-weight: 700;
}

.modal-close {
    background: rgba(255, 255, 255, 0.2);
    border: none;
    color: white;
    cursor: pointer;
    padding: 0.75rem;
    border-radius: 8px;
    transition: all 0.3s ease;
    font-size: 1.2rem;
}

.modal-close:hover {
    background: rgba(255, 255, 255, 0.3);
}

.modal-body {
    padding: 2rem;
}

.success-content {
    text-align: center;
    margin-bottom: 2rem;
}

.success-content i {
    font-size: 3rem;
    color: #10b981;
    margin-bottom: 1rem;
}

.success-content div {
    font-size: 1.1rem;
    color: #374151;
}

/* Mobile Responsive */
@media (max-width: 768px) {
    .main-content {
        margin-left: 0;
        padding: 1rem;
        margin-top: 60px;
    }
    
    .content-header {
        padding: 1.5rem;
        text-align: center;
    }
    
    .page-title {
        font-size: 2rem;
    }
    
    .page-subtitle {
        font-size: 1rem;
    }
    
    .profile-avatar-section {
        flex-direction: column;
        text-align: center;
        gap: 1rem;
    }
    
    .card-content {
        padding: 1.5rem;
    }
    
    .form-row {
        grid-template-columns: 1fr;
        gap: 1rem;
    }
    
    .tabs {
        flex-direction: column;
        gap: 0.25rem;
    }
    
    .tab-btn {
        border-radius: 8px;
        justify-content: center;
    }
    
    .form-actions {
        flex-direction: column;
    }
    
    .btn {
        width: 100%;
        justify-content: center;
    }
    
    .info-grid {
        grid-template-columns: 1fr;
        gap: 1rem;
    }
}
</style>

<script>
function switchTab(tabName) {
    // Remove active class from all tabs and buttons
    document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
    document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));
    
    // Add active class to clicked button and corresponding content
    event.target.classList.add('active');
    document.getElementById(tabName + '-tab').classList.add('active');
}

function updateAccount(event) {
    event.preventDefault();
    
    const form = document.getElementById('accountForm');
    const formData = new FormData(form);
    const submitBtn = form.querySelector('button[type="submit"]');
    
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Updating...';
    
    fetch('ajax/update_admin_profile.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showSuccess('Account details updated successfully!');
            // Update session username if changed
            if (data.username) {
                document.querySelector('.admin-name').textContent = data.username;
            }
        } else {
            alert('Error: ' + (data.error || 'Failed to update account'));
        }
    })
    .catch(error => {
        alert('Error updating account');
    })
    .finally(() => {
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fas fa-save"></i> Update Account';
    });
}

function updatePassword(event) {
    event.preventDefault();
    
    const form = document.getElementById('passwordForm');
    const formData = new FormData(form);
    const submitBtn = form.querySelector('button[type="submit"]');
    
    // Check if passwords match
    const newPassword = document.getElementById('new_password').value;
    const confirmPassword = document.getElementById('confirm_password').value;
    
    if (newPassword !== confirmPassword) {
        alert('New passwords do not match');
        return;
    }
    
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Updating...';
    
    fetch('ajax/update_admin_password.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showSuccess('Password updated successfully!');
            form.reset();
        } else {
            alert('Error: ' + (data.error || 'Failed to update password'));
        }
    })
    .catch(error => {
        alert('Error updating password');
    })
    .finally(() => {
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fas fa-key"></i> Update Password';
    });
}

function showSuccess(message) {
    document.getElementById('successMessage').textContent = message;
    document.getElementById('successModal').classList.add('show');
}

function closeSuccessModal() {
    document.getElementById('successModal').classList.remove('show');
}

// Close modal when clicking outside
document.getElementById('successModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeSuccessModal();
    }
});
</script>

<?php include 'includes/footer.php'; ?>