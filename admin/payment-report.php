<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

require_once '../config/database.php';
$conn = getConnection();

// Get applications with payment methods
$stmt = $conn->prepare("
    SELECT 
        a.id,
        a.application_number,
        CONCAT(u.first_name, ' ', u.last_name) as user_name,
        s.name as service_name,
        a.total_amount,
        a.currency,
        a.submitted_at,
        GROUP_CONCAT(DISTINCT pm.name ORDER BY p.created_at SEPARATOR ', ') as payment_methods,
        COUNT(p.id) as payment_count,
        SUM(CASE WHEN p.payment_status = 'completed' THEN p.amount ELSE 0 END) as paid_amount
    FROM applications a
    JOIN users u ON a.user_id = u.id
    JOIN services s ON a.service_id = s.id
    LEFT JOIN payments p ON a.id = p.application_id
    LEFT JOIN payment_methods pm ON p.payment_method_id = pm.id
    GROUP BY a.id
    ORDER BY a.submitted_at DESC
");
$stmt->execute();
$applications = $stmt->fetchAll(PDO::FETCH_ASSOC);

include 'includes/header.php';
?>

<style>
.main-content {
    margin-left: 250px;
    margin-top: 80px;
    padding: 2rem;
    background: #f8fafc;
    min-height: calc(100vh - 80px);
}

.page-header {
    background: white;
    padding: 1.5rem;
    border-radius: 12px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    margin-bottom: 2rem;
}

.report-table {
    background: white;
    border-radius: 12px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    overflow: hidden;
}

.table {
    width: 100%;
    border-collapse: collapse;
}

.table th {
    background: #f8fafc;
    padding: 1rem;
    text-align: left;
    font-weight: 600;
    color: #374151;
    border-bottom: 1px solid #e5e7eb;
}

.table td {
    padding: 1rem;
    border-bottom: 1px solid #f3f4f6;
    vertical-align: top;
}

.table tr:hover {
    background: #f9fafb;
}

.payment-methods {
    font-size: 0.875rem;
    color: #059669;
    font-weight: 500;
}

.no-payments {
    color: #9ca3af;
    font-style: italic;
}

.amount {
    font-weight: 600;
    color: #111827;
}

.paid-amount {
    color: #059669;
    font-size: 0.875rem;
}

.payment-count {
    background: #dbeafe;
    color: #1e40af;
    padding: 0.25rem 0.5rem;
    border-radius: 12px;
    font-size: 0.75rem;
    font-weight: 500;
}

@media (max-width: 768px) {
    .main-content {
        margin-left: 0;
        margin-top: 70px;
        padding: 1rem;
    }
    
    .table {
        font-size: 0.875rem;
    }
    
    .table th,
    .table td {
        padding: 0.75rem 0.5rem;
    }
}
</style>

<div class="main-content">
    <div class="page-header">
        <h1>Payment Methods Report</h1>
        <p>Applications and their payment methods used</p>
    </div>

    <div class="report-table">
        <table class="table">
            <thead>
                <tr>
                    <th>Application</th>
                    <th>User</th>
                    <th>Service</th>
                    <th>Amount</th>
                    <th>Payment Methods</th>
                    <th>Payments</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($applications as $app): ?>
                <tr>
                    <td>
                        <a href="view-application.php?id=<?= $app['id'] ?>" style="color: #3b82f6; text-decoration: none;">
                            #<?= $app['id'] ?>
                        </a>
                        <br>
                        <small style="color: #6b7280;"><?= htmlspecialchars($app['application_number']) ?></small>
                    </td>
                    <td><?= htmlspecialchars($app['user_name']) ?></td>
                    <td><?= htmlspecialchars($app['service_name']) ?></td>
                    <td>
                        <div class="amount"><?= $app['currency'] ?> <?= number_format($app['total_amount']) ?></div>
                        <?php if ($app['paid_amount'] > 0): ?>
                            <div class="paid-amount">Paid: <?= $app['currency'] ?> <?= number_format($app['paid_amount']) ?></div>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($app['payment_methods']): ?>
                            <div class="payment-methods"><?= htmlspecialchars($app['payment_methods']) ?></div>
                        <?php else: ?>
                            <div class="no-payments">No payments yet</div>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($app['payment_count'] > 0): ?>
                            <span class="payment-count"><?= $app['payment_count'] ?> payment<?= $app['payment_count'] > 1 ? 's' : '' ?></span>
                        <?php else: ?>
                            <span class="no-payments">0 payments</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div><?= date('M d, Y', strtotime($app['submitted_at'])) ?></div>
                        <small style="color: #6b7280;"><?= date('H:i', strtotime($app['submitted_at'])) ?></small>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'includes/footer.php'; ?>