<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$appId = $_GET['app_id'] ?? null;
if (!$appId) {
    header('Location: profile.php');
    exit;
}

try {
    $pdo = getConnection();
    $stmt = $pdo->prepare("
        SELECT a.*, s.name as service_name, s.currency 
        FROM applications a 
        LEFT JOIN services s ON a.service_id = s.id 
        WHERE a.id = ? AND a.user_id = ?
    ");
    $stmt->execute([$appId, $_SESSION['user_id']]);
    $app = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$app) {
        header('Location: profile.php');
        exit;
    }
} catch (Exception $e) {
    header('Location: profile.php');
    exit;
}

$pageTitle = 'Request Refund - A to Z Global Link Solutions';
include 'includes/header.php';
?>

<style>
* { margin: 0; padding: 0; box-sizing: border-box; }
body { font-family: 'Inter', sans-serif; background: #f8fafc; }
.container { max-width: 600px; margin: 100px auto 50px; padding: 20px; }
.card { background: white; border-radius: 16px; padding: 40px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
.header { text-align: center; margin-bottom: 30px; }
.header h1 { font-size: 28px; color: #1e293b; margin-bottom: 10px; }
.header p { color: #64748b; }
.app-info { background: #f1f5f9; padding: 20px; border-radius: 12px; margin-bottom: 30px; }
.app-info-item { display: flex; justify-content: space-between; margin-bottom: 10px; }
.app-info-item:last-child { margin-bottom: 0; }
.label { color: #64748b; font-weight: 500; }
.value { color: #1e293b; font-weight: 600; }
.form-group { margin-bottom: 20px; }
.form-group label { display: block; margin-bottom: 8px; color: #334155; font-weight: 600; font-size: 14px; }
.form-group input, .form-group textarea { width: 100%; padding: 12px; border: 2px solid #e2e8f0; border-radius: 8px; font-size: 14px; font-family: inherit; transition: all 0.3s; }
.form-group input:focus, .form-group textarea:focus { outline: none; border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59,130,246,0.1); }
.form-group textarea { min-height: 100px; resize: vertical; }
.form-group small { display: block; margin-top: 5px; color: #64748b; font-size: 12px; }
.btn-group { display: flex; gap: 10px; margin-top: 30px; }
.btn { flex: 1; padding: 14px; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; transition: all 0.3s; font-size: 15px; }
.btn-primary { background: linear-gradient(135deg, #3b82f6, #2563eb); color: white; }
.btn-primary:hover { transform: translateY(-2px); box-shadow: 0 10px 20px rgba(59,130,246,0.3); }
.btn-secondary { background: #e2e8f0; color: #475569; }
.btn-secondary:hover { background: #cbd5e1; }
.alert { padding: 15px; border-radius: 8px; margin-bottom: 20px; display: none; }
.alert.show { display: block; }
.alert-success { background: #d1fae5; color: #065f46; border: 1px solid #6ee7b7; }
.alert-error { background: #fee2e2; color: #991b1b; border: 1px solid #f87171; }
</style>

<div class="container">
    <div class="card">
        <div class="header">
            <h1>Request Refund</h1>
            <p>Submit your refund request for application #<?= htmlspecialchars($app['application_number']) ?></p>
        </div>

        <div class="app-info">
            <div class="app-info-item">
                <span class="label">Service:</span>
                <span class="value"><?= htmlspecialchars($app['service_name']) ?></span>
            </div>
            <div class="app-info-item">
                <span class="label">Application Number:</span>
                <span class="value"><?= htmlspecialchars($app['application_number']) ?></span>
            </div>
            <div class="app-info-item">
                <span class="label">Total Amount:</span>
                <span class="value"><?= number_format($app['total_amount'], 2) ?> <?= $app['currency'] ?></span>
            </div>
        </div>

        <div id="alert" class="alert"></div>

        <form id="refundForm">
            <input type="hidden" name="application_id" value="<?= $appId ?>">
            
            <div class="form-group">
                <label>Refund Amount</label>
                <input type="number" name="refund_amount" value="<?= $app['total_amount'] ?>" max="<?= $app['total_amount'] ?>" step="0.01" required readonly>
                <small>Maximum refundable: <?= number_format($app['total_amount'], 2) ?> <?= $app['currency'] ?></small>
            </div>

            <div class="form-group">
                <label>Reason for Refund</label>
                <textarea name="reason" required placeholder="Please explain why you are requesting a refund..."></textarea>
            </div>

            <div class="form-group">
                <label>Bank Name</label>
                <input type="text" name="bank_name" required placeholder="Enter your bank name">
            </div>

            <div class="form-group">
                <label>Account Number</label>
                <input type="text" name="account_number" required placeholder="Enter your account number">
            </div>

            <div class="form-group">
                <label>Account Holder Name</label>
                <input type="text" name="account_name" required placeholder="Enter account holder name">
            </div>

            <div class="btn-group">
                <button type="button" class="btn btn-secondary" onclick="window.location.href='profile.php'">Cancel</button>
                <button type="submit" class="btn btn-primary">Submit Refund Request</button>
            </div>
        </form>
    </div>
</div>

<script>
document.getElementById('refundForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const btn = this.querySelector('button[type="submit"]');
    const originalText = btn.textContent;
    btn.textContent = 'Submitting...';
    btn.disabled = true;
    
    const formData = new FormData(this);
    
    fetch('process-refund.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        const alert = document.getElementById('alert');
        alert.className = 'alert show ' + (data.success ? 'alert-success' : 'alert-error');
        alert.textContent = data.message;
        
        if (data.success) {
            setTimeout(() => window.location.href = 'profile.php', 2000);
        } else {
            btn.textContent = originalText;
            btn.disabled = false;
        }
    })
    .catch(error => {
        const alert = document.getElementById('alert');
        alert.className = 'alert show alert-error';
        alert.textContent = 'Network error. Please try again.';
        btn.textContent = originalText;
        btn.disabled = false;
    });
});
</script>

<?php include 'includes/footer.php'; ?>
