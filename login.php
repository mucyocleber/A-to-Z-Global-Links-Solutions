<?php
session_start();
require_once 'config/database.php';

if ($_POST) {
    try {
        $pdo = getConnection();
        $stmt = $pdo->prepare("SELECT id, first_name, last_name, password_hash FROM users WHERE email = ?");
        $stmt->execute([$_POST['email']]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($_POST['password'], $user['password_hash'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
            
            $redirect = $_GET['redirect'] ?? 'profile';
            if (isset($_GET['action']) && $_GET['action'] === 'submit') {
                $redirect .= '?action=submit';
            }
            header("Location: $redirect");
            exit;
        } else {
            if (!$user) {
                $show_error_popup = true;
                $error_type = 'email';
            } else {
                $show_error_popup = true;
                $error_type = 'password';
            }
        }
    } catch (Exception $e) {
        $error = "Login failed. Please try again.";
    }
}

$pageTitle = 'Login - A to Z Global Link Solutions';
include 'includes/header.php';
?>

<main style="margin-top: 70px; min-height: calc(100vh - 70px); display: flex; background: white;">
    <div style="display: grid; grid-template-columns: 1fr 1fr; width: 100%; min-height: calc(100vh - 70px);">
        <div style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 25%, #1e40af 75%, #3b82f6 100%); display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 3rem; position: relative; overflow: hidden;">
            <div style="text-align: center; color: white; position: relative; z-index: 2;">
                <div style="width: 120px; height: 120px; margin: 0 auto 2rem; border-radius: 24px; overflow: hidden; box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);">
                    <img src="logo/logo.jpg" alt="A to Z Global Link" style="width: 100%; height: 100%; object-fit: cover;">
                </div>
                <h1 style="font-size: 2.5rem; font-weight: 900; margin-bottom: 1rem; line-height: 1.2;">A to Z Global Link Solutions</h1>
                <p style="font-size: 1.2rem; opacity: 0.9; margin-bottom: 2rem; line-height: 1.6;">Your Gateway to Global Opportunities</p>
            </div>
        </div>
        
        <div style="display: flex; align-items: center; justify-content: center; padding: 3rem; background: #f8fafc;">
            <div style="width: 100%; max-width: 400px; background: white; padding: 3rem 2.5rem; border-radius: 24px; box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1); border: 1px solid #e2e8f0;">
                <div style="text-align: center; margin-bottom: 2.5rem;">
                    <h1 style="color: #1e40af; margin-bottom: 0.75rem; font-size: 1.8rem; font-weight: 800;">Welcome Back</h1>
                    <p style="color: #64748b; font-size: 0.95rem; font-weight: 500;">Sign in to your account to continue</p>
                </div>
                
                <?php if (isset($show_error_popup)): ?>
                    <div id="errorPopup" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.7); backdrop-filter: blur(12px); display: flex; align-items: center; justify-content: center; z-index: 10000;">
                        <div style="background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%); padding: 3.5rem 3rem; border-radius: 32px; text-align: center; max-width: 480px; width: 90%; margin: 1rem; box-shadow: 0 32px 64px rgba(0, 0, 0, 0.3); position: relative; overflow: hidden;">
                            <div style="position: absolute; top: 0; left: 0; right: 0; height: 4px; background: linear-gradient(90deg, #ef4444, #f97316, #eab308);"></div>
                            <div style="width: 100px; height: 100px; background: linear-gradient(135deg, #fef2f2, #fee2e2); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 2rem; color: #dc2626; font-size: 2.5rem; box-shadow: 0 8px 32px rgba(220, 38, 38, 0.2);">
                                <i class="fas fa-exclamation-triangle"></i>
                            </div>
                            <?php if ($error_type === 'email'): ?>
                                <h2 style="color: #1e293b; margin-bottom: 1.5rem; font-size: 1.75rem; font-weight: 800;">Email Not Found</h2>
                                <p style="color: #64748b; margin-bottom: 1.25rem; line-height: 1.7; font-size: 1.05rem; font-weight: 500;">The email address you entered is not registered in our system.</p>
                                <p style="color: #64748b; margin-bottom: 1.25rem; line-height: 1.7; font-size: 1.05rem; font-weight: 500;">Please check your email or create a new account.</p>
                            <?php else: ?>
                                <h2 style="color: #1e293b; margin-bottom: 1.5rem; font-size: 1.75rem; font-weight: 800;">Incorrect Password</h2>
                                <p style="color: #64748b; margin-bottom: 1.25rem; line-height: 1.7; font-size: 1.05rem; font-weight: 500;">The password you entered is incorrect.</p>
                                <p style="color: #64748b; margin-bottom: 1.25rem; line-height: 1.7; font-size: 1.05rem; font-weight: 500;">Please try again or reset your password if you've forgotten it.</p>
                            <?php endif; ?>
                            <div style="display: flex; gap: 1.25rem; justify-content: center; margin-top: 2.5rem;">
                                <button onclick="closeErrorPopup()" style="background: linear-gradient(135deg, #f1f5f9, #e2e8f0); color: #475569; padding: 1rem 2rem; border: 2px solid #e2e8f0; border-radius: 16px; font-weight: 700; cursor: pointer; font-size: 1rem;">Try Again</button>
                                <?php if ($error_type === 'email'): ?>
                                    <button onclick="goToRegister()" style="background: linear-gradient(135deg, #1e40af, #3b82f6, #60a5fa); color: white; padding: 1rem 2rem; border: none; border-radius: 16px; font-weight: 700; cursor: pointer; font-size: 1rem; display: inline-flex; align-items: center; gap: 0.75rem;">
                                        <i class="fas fa-user-plus"></i>
                                        Create Account
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
                
                <form method="POST">
                    <div style="margin-bottom: 1.75rem;">
                        <label for="email" style="display: block; color: #1e40af; font-weight: 700; margin-bottom: 0.75rem; font-size: 0.95rem;">Email Address</label>
                        <div style="position: relative;">
                            <i class="fas fa-envelope" style="position: absolute; left: 1.25rem; top: 50%; transform: translateY(-50%); color: #64748b; font-size: 1.2rem; z-index: 2;"></i>
                            <input type="email" id="email" name="email" placeholder="Enter your email" required style="width: 100%; padding: 1.25rem 3.5rem; border: 2px solid #e2e8f0; border-radius: 20px; font-size: 1rem; transition: all 0.3s ease; background: rgba(248, 250, 252, 0.8); font-weight: 500;">
                        </div>
                    </div>
                    
                    <div style="margin-bottom: 1.75rem;">
                        <label for="password" style="display: block; color: #1e40af; font-weight: 700; margin-bottom: 0.75rem; font-size: 0.95rem;">Password</label>
                        <div style="position: relative;">
                            <i class="fas fa-lock" style="position: absolute; left: 1.25rem; top: 50%; transform: translateY(-50%); color: #64748b; font-size: 1.2rem; z-index: 2;"></i>
                            <input type="password" id="password" name="password" placeholder="Enter your password" required style="width: 100%; padding: 1.25rem 3.5rem; border: 2px solid #e2e8f0; border-radius: 20px; font-size: 1rem; transition: all 0.3s ease; background: rgba(248, 250, 252, 0.8); font-weight: 500;">
                        </div>
                    </div>
                    
                    <button type="submit" style="background: linear-gradient(135deg, #1e40af, #3b82f6); color: white; padding: 1.25rem 2rem; border: none; border-radius: 20px; font-weight: 700; cursor: pointer; transition: all 0.3s ease; font-size: 1.1rem; width: 100%; display: flex; align-items: center; justify-content: center; gap: 0.75rem; box-shadow: 0 12px 32px rgba(30, 64, 175, 0.4);">
                        <i class="fas fa-sign-in-alt"></i>
                        <span>Sign In</span>
                    </button>
                </form>
                
                <div style="display: flex; align-items: center; margin: 2rem 0;">
                    <div style="flex: 1; height: 2px; background: linear-gradient(90deg, transparent, #e2e8f0, transparent);"></div>
                    <span style="padding: 0 1.5rem; color: #64748b; font-size: 0.9rem; font-weight: 600; background: white;">or</span>
                    <div style="flex: 1; height: 2px; background: linear-gradient(90deg, transparent, #e2e8f0, transparent);"></div>
                </div>
                
                <div style="text-align: center; margin-top: 2rem;">
                    <p style="color: #64748b; margin-bottom: 1rem; font-weight: 500;">Don't have an account?</p>
                    <a href="register<?= isset($_GET['redirect']) ? '?redirect=' . urlencode($_GET['redirect']) . (isset($_GET['action']) ? '&action=' . $_GET['action'] : '') : '' ?>" style="color: #1e40af; text-decoration: none; font-weight: 700; padding: 1rem 2rem; border: 2px solid #e2e8f0; border-radius: 16px; display: inline-flex; align-items: center; gap: 0.5rem; transition: all 0.3s ease; background: rgba(248, 250, 252, 0.5);">
                        <i class="fas fa-user-plus"></i> Create Account
                    </a>
                </div>
            </div>
        </div>
    </div>
</main>

<style>
@media (max-width: 768px) {
    main {
        margin-top: 60px !important;
        min-height: calc(100vh - 160px) !important;
    }
    
    main > div {
        grid-template-columns: 1fr !important;
        min-height: calc(100vh - 160px) !important;
    }
    
    main > div > div:first-child {
        padding: 2rem !important;
        min-height: 300px !important;
    }
    
    main > div > div:first-child h1 {
        font-size: 1.8rem !important;
    }
    
    main > div > div:first-child p {
        font-size: 1rem !important;
        margin-bottom: 1.5rem !important;
    }
    
    main > div > div:first-child > div > div:first-child {
        width: 80px !important;
        height: 80px !important;
        margin-bottom: 1.5rem !important;
    }
    
    main > div > div:last-child {
        padding: 2rem 1.5rem !important;
    }
    
    main > div > div:last-child > div {
        padding: 2rem 1.5rem !important;
    }
}
</style>

<script>
function closeErrorPopup() {
    document.getElementById('errorPopup').style.display = 'none';
}

function goToRegister() {
    window.location.href = 'register.php';
}
</script>

<?php include 'includes/footer.php'; ?>