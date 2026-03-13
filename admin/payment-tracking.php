<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit;
}

require_once '../config/database.php';

try {
    $pdo = getConnection();
    
    // Get payment methods usage for manual applications
    $stmt = $pdo->query("
        SELECT 
            CASE 
                WHEN p.payment_method_id = 1 THEN 'Cash'
                WHEN p.payment_method_id = 2 THEN 'MTN Mobile Money'
                WHEN p.payment_method_id = 3 THEN 'Bank of Kigali'
                WHEN p.payment_method_id = 4 THEN 'Cryptocurrency'
                ELSE 'Unknown'
            END as payment_method,
            COUNT(*) as usage_count,
            SUM(p.amount) as total_amount,
            AVG(p.amount) as avg_amount
        FROM payments p
        JOIN applications a ON p.application_id = a.id
        WHERE a.application_type = 'manual'
        GROUP BY p.payment_method_id
        ORDER BY usage_count DESC
    ");
    $paymentMethods = $stmt->fetchAll();
    
    // Get total stats
    $totalStmt = $pdo->query("
        SELECT 
            COUNT(*) as total_payments,
            SUM(p.amount) as total_revenue
        FROM payments p
        JOIN applications a ON p.application_id = a.id
        WHERE a.application_type = 'manual'
    ");
    $totalStats = $totalStmt->fetch();
    
} catch (Exception $e) {
    $paymentMethods = [];
    $totalStats = ['total_payments' => 0, 'total_revenue' => 0];
}

$pageTitle = 'Payment Methods Tracking';
include 'includes/header.php';
include 'includes/sidebar.php';
?>

<main class="main-content">
    <div class="content-header">
        <h1>Payment Methods Tracking</h1>
        <p>Manual applications payment methods usage statistics</p>
    </div>
    
    <div class="stats-row">
        <div class="stat-card">
            <div class="stat-number"><?= $totalStats['total_payments'] ?></div>
            <div class="stat-label">Total Payments</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?= number_format($totalStats['total_revenue']) ?></div>
            <div class="stat-label">Total Revenue</div>
        </div>
    </div>
    
    <div class="payment-methods-grid">
        <?php foreach ($paymentMethods as $method): ?>
            <div class="method-card">
                <div class="method-header">
                    <h3><?= htmlspecialchars($method['payment_method']) ?></h3>
                    <div class="usage-count"><?= $method['usage_count'] ?> uses</div>
                </div>
                <div class="method-stats">
                    <div class="stat-item">
                        <span>Total Amount:</span>
                        <span><?= number_format($method['total_amount']) ?></span>
                    </div>
                    <div class="stat-item">
                        <span>Average Amount:</span>
                        <span><?= number_format($method['avg_amount']) ?></span>
                    </div>
                    <div class="stat-item">
                        <span>Usage Percentage:</span>
                        <span><?= round(($method['usage_count'] / $totalStats['total_payments']) * 100, 1) ?>%</span>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</main>

<style>
.main-content {
    margin-left: 250px;
    padding: 2rem;
    background: #f8fafc;
    min-height: 100vh;
}

.content-header {
    margin-bottom: 2rem;
}

.content-header h1 {
    font-size: 1.875rem;
    color: #111827;
    margin-bottom: 0.5rem;
}

.content-header p {
    color: #6b7280;
}

.stats-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.stat-card {
    background: white;
    padding: 1.5rem;
    border-radius: 12px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    text-align: center;
}

.stat-number {
    font-size: 2rem;
    font-weight: 700;
    color: #111827;
}

.stat-label {
    color: #6b7280;
    font-size: 0.875rem;
    margin-top: 0.5rem;
}

.payment-methods-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 1.5rem;
}

.method-card {
    background: white;
    border-radius: 12px;
    padding: 1.5rem;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    border: 1px solid #e5e7eb;
}

.method-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid #f3f4f6;
}

.method-header h3 {
    font-size: 1.125rem;
    font-weight: 600;
    color: #111827;
    margin: 0;
}

.usage-count {
    background: #3b82f6;
    color: white;
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.875rem;
    font-weight: 500;
}

.method-stats {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.stat-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.stat-item span:first-child {
    color: #6b7280;
    font-size: 0.875rem;
}

.stat-item span:last-child {
    font-weight: 600;
    color: #111827;
}

@media (max-width: 768px) {
    .main-content {
        margin-left: 0;
        padding: 1rem;
    }
    
    .payment-methods-grid {
        grid-template-columns: 1fr;
    }
}
</style>