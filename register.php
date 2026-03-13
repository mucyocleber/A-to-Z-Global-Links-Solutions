<?php
// Start session first
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'config/database.php';

if ($_POST) {
    try {
        $pdo = getConnection();
        
        // Check if email already exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$_POST['email']]);
        if ($stmt->fetch()) {
            $show_error_popup = true;
        } else {
            // Insert new user
            $stmt = $pdo->prepare("
                INSERT INTO users (user_uuid, first_name, last_name, email, password_hash, country, date_of_birth) 
                VALUES (UUID(), ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $_POST['first_name'],
                $_POST['last_name'],
                $_POST['email'],
                password_hash($_POST['password'], PASSWORD_DEFAULT),
                $_POST['country'],
                $_POST['date_of_birth']
            ]);
            
            // Auto-login the new user
            $_SESSION['user_id'] = $pdo->lastInsertId();
            $_SESSION['user_name'] = $_POST['first_name'] . ' ' . $_POST['last_name'];
            
            // Check if there's a redirect after registration
            if (isset($_GET['redirect']) && isset($_GET['action'])) {
                $redirect = $_GET['redirect'] . '?action=' . $_GET['action'];
                header("Location: $redirect");
                exit;
            }
            
            // Set success flag for popup
            $registration_success = true;
            $user_name = $_POST['first_name'];
        }
    } catch (Exception $e) {
        $error = "Registration failed. Please try again.";
        error_log("Registration error: " . $e->getMessage());
    }
}

try {
    $pdo = getConnection();
    $countries = $pdo->query("SELECT name FROM countries WHERE is_active = 1 ORDER BY name")->fetchAll();
} catch (Exception $e) {
    $countries = [];
}

$pageTitle = 'Register - A to Z Global Link Solutions';
include 'includes/header.php';
?>

<style>
.main-content {
    margin-top: 70px;
    min-height: calc(100vh - 70px);
    display: flex;
    background: white;
}

.register-container {
    display: grid;
    grid-template-columns: 1fr 1fr;
    width: 100%;
    min-height: calc(100vh - 70px);
}

.register-left {
    background: linear-gradient(135deg, #0f172a 0%, #1e293b 25%, #1e40af 75%, #3b82f6 100%);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 3rem;
    position: relative;
    overflow: hidden;
}

.register-left::before {
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

.register-left::after {
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

.brand-content {
    text-align: center;
    color: white;
    position: relative;
    z-index: 2;
}

.brand-logo {
    width: 120px;
    height: 120px;
    margin: 0 auto 2rem;
    border-radius: 24px;
    overflow: hidden;
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
    animation: logoFloat 3s ease-in-out infinite;
}

.brand-logo img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

@keyframes logoFloat {
    0%, 100% { transform: translateY(0px); }
    50% { transform: translateY(-10px); }
}

.brand-title {
    font-size: 2.5rem;
    font-weight: 900;
    margin-bottom: 1rem;
    line-height: 1.2;
}

.brand-subtitle {
    font-size: 1.2rem;
    opacity: 0.9;
    margin-bottom: 2rem;
    line-height: 1.6;
}

.brand-features {
    display: flex;
    flex-direction: column;
    gap: 1rem;
    margin-top: 2rem;
}

.brand-feature {
    display: flex;
    align-items: center;
    gap: 1rem;
    opacity: 0.9;
}

.brand-feature i {
    width: 20px;
    color: #60a5fa;
    font-size: 1.2rem;
}

.register-right {
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 3rem;
    background: #f8fafc;
}

.register-form {
    width: 100%;
    max-width: 480px;
    background: white;
    padding: 3rem 2.5rem;
    border-radius: 24px;
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
    border: 1px solid #e2e8f0;
    animation: slideUp 0.8s ease-out;
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

/* Error Popup Styles */
.error-popup {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.7);
    backdrop-filter: blur(12px);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 10000;
    animation: fadeIn 0.4s ease;
}

.error-popup .popup-content {
    background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
    padding: 3.5rem 3rem;
    border-radius: 32px;
    text-align: center;
    max-width: 480px;
    width: 90%;
    margin: 1rem;
    box-shadow: 0 32px 64px rgba(0, 0, 0, 0.3), 0 0 0 1px rgba(255, 255, 255, 0.1);
    animation: slideUp 0.5s cubic-bezier(0.34, 1.56, 0.64, 1);
    position: relative;
    overflow: hidden;
}

.error-popup .popup-content::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, #ef4444, #f97316, #eab308);
}

.error-popup .popup-icon {
    width: 100px;
    height: 100px;
    background: linear-gradient(135deg, #fef2f2, #fee2e2);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 2rem;
    color: #dc2626;
    font-size: 2.5rem;
    box-shadow: 0 8px 32px rgba(220, 38, 38, 0.2);
    animation: bounce 0.6s ease;
}

@keyframes bounce {
    0%, 20%, 53%, 80%, 100% { transform: translate3d(0,0,0); }
    40%, 43% { transform: translate3d(0, -8px, 0); }
    70% { transform: translate3d(0, -4px, 0); }
    90% { transform: translate3d(0, -2px, 0); }
}

.error-popup .popup-content h2 {
    color: #1e293b;
    margin-bottom: 1.5rem;
    font-size: 1.75rem;
    font-weight: 800;
    background: linear-gradient(135deg, #1e293b, #475569);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.error-popup .popup-content p {
    color: #64748b;
    margin-bottom: 1.25rem;
    line-height: 1.7;
    font-size: 1.05rem;
    font-weight: 500;
}

.popup-actions {
    display: flex;
    gap: 1.25rem;
    justify-content: center;
    margin-top: 2.5rem;
}

.popup-btn-secondary {
    background: linear-gradient(135deg, #f1f5f9, #e2e8f0);
    color: #475569;
    padding: 1rem 2rem;
    border: 2px solid #e2e8f0;
    border-radius: 16px;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    font-size: 1rem;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.popup-btn-secondary:hover {
    background: linear-gradient(135deg, #e2e8f0, #cbd5e1);
    border-color: #cbd5e1;
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
}

.popup-btn-primary {
    background: linear-gradient(135deg, #1e40af, #3b82f6, #60a5fa);
    color: white;
    padding: 1rem 2rem;
    border: none;
    border-radius: 16px;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    font-size: 1rem;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 0.75rem;
    box-shadow: 0 8px 25px rgba(30, 64, 175, 0.4);
    position: relative;
    overflow: hidden;
}

.popup-btn-primary::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
    transition: left 0.6s ease;
}

.popup-btn-primary:hover::before {
    left: 100%;
}

.popup-btn-primary:hover {
    background: linear-gradient(135deg, #1d4ed8, #2563eb, #3b82f6);
    transform: translateY(-2px) scale(1.02);
    box-shadow: 0 12px 35px rgba(30, 64, 175, 0.5);
    text-decoration: none;
    color: white;
}

.form-header {
    text-align: center;
    margin-bottom: 2rem;
}

.form-header h1 {
    color: #1e40af;
    margin-bottom: 0.5rem;
    font-size: 1.75rem;
    font-weight: 700;
}

.form-header p {
    color: #64748b;
    font-size: 0.95rem;
}

.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
.form-group { margin-bottom: 1.5rem; position: relative; }
.form-group label { display: block; color: #1e40af; font-weight: 600; margin-bottom: 0.5rem; font-size: 0.9rem; }
.input-wrapper { position: relative; }
.input-icon { position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: #64748b; font-size: 1.1rem; }
.form-control { width: 100%; padding: 1rem 3rem; border: 2px solid #e2e8f0; border-radius: 16px; font-size: 1rem; transition: all 0.3s; background: rgba(248, 250, 252, 0.8); }
.form-control:focus { outline: none; border-color: #3b82f6; background: white; box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1); }
.form-control::placeholder { color: #94a3b8; }

.btn-register { background: linear-gradient(135deg, #1e40af, #3b82f6); color: white; padding: 1rem 2rem; border: none; border-radius: 16px; font-weight: 600; cursor: pointer; transition: all 0.3s; font-size: 1rem; width: 100%; display: flex; align-items: center; justify-content: center; gap: 0.5rem; box-shadow: 0 8px 25px rgba(30, 64, 175, 0.3); }
.btn-register:hover { background: linear-gradient(135deg, #1d4ed8, #2563eb); transform: translateY(-2px); box-shadow: 0 12px 35px rgba(30, 64, 175, 0.4); }
.btn-register:active { transform: translateY(0); }

.alert-error { background: linear-gradient(135deg, #fef2f2, #fee2e2); color: #dc2626; border: 1px solid #fca5a5; padding: 1rem 1.25rem; border-radius: 16px; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.75rem; font-size: 0.9rem; }
.alert-error i { color: #ef4444; }
.alert-success { background: linear-gradient(135deg, #f0fdf4, #dcfce7); color: #16a34a; border: 1px solid #86efac; padding: 1rem 1.25rem; border-radius: 16px; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.75rem; font-size: 0.9rem; }
.alert-success i { color: #22c55e; }

.divider { display: flex; align-items: center; margin: 1.5rem 0; }
.divider::before, .divider::after { content: ''; flex: 1; height: 1px; background: #e2e8f0; }
.divider span { padding: 0 1rem; color: #64748b; font-size: 0.85rem; }

.login-link { text-align: center; margin-top: 1.5rem; }
.login-link p { color: #64748b; margin-bottom: 0.75rem; }
.login-link a { color: #1e40af; text-decoration: none; font-weight: 600; padding: 0.75rem 1.5rem; border: 2px solid #e2e8f0; border-radius: 12px; display: inline-block; transition: all 0.3s; }
.login-link a:hover { background: #f8fafc; border-color: #3b82f6; color: #3b82f6; }

@media (max-width: 768px) {
    .main-content {
        margin-top: 60px;
        min-height: calc(100vh - 160px);
    }
    
    .register-container {
        grid-template-columns: 1fr;
        min-height: calc(100vh - 160px);
    }
    
    .register-left {
        padding: 2rem;
        min-height: 300px;
    }
    
    .brand-logo {
        width: 80px;
        height: 80px;
        margin-bottom: 1.5rem;
    }
    
    .brand-title {
        font-size: 1.8rem;
    }
    
    .brand-subtitle {
        font-size: 1rem;
        margin-bottom: 1.5rem;
    }
    
    .brand-features {
        display: none;
    }
    
    .register-right {
        padding: 2rem 1.5rem;
    }
    
    .register-form {
        padding: 2rem 1.5rem;
    }
    
    .form-row {
        grid-template-columns: 1fr;
    }
}
</style>

<main class="main-content">
    <div class="register-container">
        <div class="register-left">
            <div class="brand-content">
                <div class="brand-logo">
                    <img src="logo/logo.jpg" alt="A to Z Global Link">
                </div>
                <h1 class="brand-title">A to Z Global Link Solutions</h1>
                <p class="brand-subtitle">Your Gateway to Global Opportunities</p>
                
                <div class="brand-features">
                    <div class="brand-feature">
                        <i class="fas fa-shield-check"></i>
                        <span>Trusted by thousands of clients</span>
                    </div>
                    <div class="brand-feature">
                        <i class="fas fa-globe"></i>
                        <span>Worldwide visa services</span>
                    </div>
                    <div class="brand-feature">
                        <i class="fas fa-clock"></i>
                        <span>Fast & reliable processing</span>
                    </div>
                    <div class="brand-feature">
                        <i class="fas fa-headset"></i>
                        <span>24/7 expert support</span>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="register-right">
            <div class="register-form">
                <div class="form-header">
                    <h1>Create Account</h1>
                    <p>Join us to start your visa application journey</p>
                </div>
                
                <?php if (isset($show_error_popup)): ?>
                    <div id="errorPopup" class="error-popup">
                        <div class="popup-content">
                            <div class="popup-icon">
                                <i class="fas fa-exclamation-triangle"></i>
                            </div>
                            <h2>Email Already Registered</h2>
                            <p>This email address is already associated with an account.</p>
                            <p>Please use the login page to access your existing account.</p>
                            <div class="popup-actions">
                                <button onclick="closeErrorPopup()" class="popup-btn-secondary">Try Again</button>
                                <button onclick="goToLogin()" class="popup-btn-primary">
                                    <i class="fas fa-sign-in-alt"></i>
                                    Go to Login
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
                
                <form method="POST">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="first_name">First Name *</label>
                            <div class="input-wrapper">
                                <i class="fas fa-user input-icon"></i>
                                <input type="text" id="first_name" name="first_name" class="form-control" placeholder="First name" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="last_name">Last Name *</label>
                            <div class="input-wrapper">
                                <i class="fas fa-user input-icon"></i>
                                <input type="text" id="last_name" name="last_name" class="form-control" placeholder="Last name" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email Address *</label>
                        <div class="input-wrapper">
                            <i class="fas fa-envelope input-icon"></i>
                            <input type="email" id="email" name="email" class="form-control" placeholder="Enter your email" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Password *</label>
                        <div class="input-wrapper">
                            <i class="fas fa-lock input-icon"></i>
                            <input type="password" id="password" name="password" class="form-control" placeholder="Create password (min 6 chars)" required minlength="6">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="country">Country *</label>
                            <div class="input-wrapper">
                                <i class="fas fa-globe input-icon"></i>
                                <select id="country" name="country" class="form-control" required>
                                    <option value="">Select Country</option>
                                    <?php foreach ($countries as $country): ?>
                                        <option value="<?= htmlspecialchars($country['name']) ?>"><?= htmlspecialchars($country['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="date_of_birth">Date of Birth *</label>
                            <div class="input-wrapper">
                                <i class="fas fa-calendar input-icon"></i>
                                <input type="date" id="date_of_birth" name="date_of_birth" class="form-control" required>
                            </div>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn-register">
                        <i class="fas fa-user-plus"></i>
                        <span>Create Account</span>
                    </button>
                </form>
                
                <div class="divider">
                    <span>or</span>
                </div>
                
                <div class="login-link">
                    <p>Already have an account?</p>
                    <a href="login<?= isset($_GET['redirect']) ? '?redirect=' . urlencode($_GET['redirect']) . (isset($_GET['action']) ? '&action=' . $_GET['action'] : '') : '' ?>">
                        <i class="fas fa-sign-in-alt"></i> Sign In
                    </a>
                </div>
            </div>
        </div>
    </div>
</main>

<!-- Success Popup -->
<?php if (isset($registration_success)): ?>
<div id="successPopup" class="success-popup">
    <div class="popup-content">
        <div class="popup-icon">
            <i class="fas fa-check-circle"></i>
        </div>
        <h2>Welcome to A to Z Global Link!</h2>
        <p>Thank you, <strong><?= htmlspecialchars($user_name) ?></strong>!</p>
        <p>Your account has been created successfully. Please complete your profile details in the dashboard to get started with your visa applications.</p>
        <button onclick="goToDashboard()" class="popup-btn">
            <i class="fas fa-user-cog"></i>
            Complete Profile
        </button>
    </div>
</div>

<style>
.success-popup {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 10000;
    animation: fadeIn 0.3s ease;
}

.popup-content {
    background: white;
    padding: 3rem 2rem;
    border-radius: 24px;
    text-align: center;
    max-width: 400px;
    margin: 1rem;
    box-shadow: 0 25px 50px rgba(0, 0, 0, 0.2);
    animation: slideUp 0.3s ease;
}

.popup-icon {
    width: 80px;
    height: 80px;
    background: linear-gradient(135deg, #10b981, #059669);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1.5rem;
    color: white;
    font-size: 2rem;
}

.popup-content h2 {
    color: #1e40af;
    margin-bottom: 1rem;
    font-size: 1.5rem;
}

.popup-content p {
    color: #64748b;
    margin-bottom: 1rem;
    line-height: 1.6;
}

.popup-btn {
    background: linear-gradient(135deg, #1e40af, #3b82f6);
    color: white;
    padding: 1rem 2rem;
    border: none;
    border-radius: 16px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin: 1.5rem auto 0;
}

.popup-btn:hover {
    background: linear-gradient(135deg, #1d4ed8, #2563eb);
    transform: translateY(-2px);
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}


</style>

<?php endif; ?>

<script>
function goToDashboard() {
    window.location.href = 'profile';
}

function closeErrorPopup() {
    document.getElementById('errorPopup').style.display = 'none';
}

function goToLogin() {
    window.location.href = 'login.php';
}
</script>

<?php include 'includes/footer.php'; ?>