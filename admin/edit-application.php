<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

$pdo = getConnection();

$applicationId = $_GET['id'] ?? null;
if (!$applicationId) {
    header('Location: applications.php');
    exit();
}

// Get application details
$stmt = $pdo->prepare("
    SELECT a.*, u.first_name, u.last_name, u.email,
           s.service_name, st.status_name
    FROM applications a
    JOIN users u ON a.user_id = u.id
    JOIN services s ON a.service_id = s.id
    JOIN application_statuses st ON a.status_id = st.id
    WHERE a.id = ?
");
$stmt->execute([$applicationId]);
$application = $stmt->fetch();

if (!$application) {
    header('Location: applications.php');
    exit();
}

// Get statuses for dropdown
$statuses_stmt = $pdo->query("SELECT * FROM application_statuses ORDER BY id");
$statuses = $statuses_stmt->fetchAll();

// Get destination countries
$countries_stmt = $pdo->query("SELECT * FROM destination_countries ORDER BY country_name");
$countries = $countries_stmt->fetchAll();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $updateStmt = $pdo->prepare("
            UPDATE applications SET
                status_id = ?,
                destination_country_id = ?,
                travel_purpose = ?,
                intended_travel_date = ?,
                duration_of_stay = ?,
                additional_fees = ?,
                discount_amount = ?,
                total_amount = base_amount + ? - ?,
                internal_notes = ?,
                last_updated_at = NOW()
            WHERE id = ?
        ");
        
        $additionalFees = floatval($_POST['additional_fees']) ?: 0;
        $discountAmount = floatval($_POST['discount_amount']) ?: 0;
        
        $updateStmt->execute([
            $_POST['status_id'],
            $_POST['destination_country_id'] ?: null,
            $_POST['travel_purpose'] ?: null,
            $_POST['intended_travel_date'] ?: null,
            $_POST['duration_of_stay'] ?: null,
            $additionalFees,
            $discountAmount,
            $additionalFees,
            $discountAmount,
            $_POST['internal_notes'] ?: null,
            $applicationId
        ]);
        
        $success = "Application updated successfully!";
        
        // Refresh application data
        $stmt->execute([$applicationId]);
        $application = $stmt->fetch();
        
    } catch (Exception $e) {
        $error = "Error updating application: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Application - <?= htmlspecialchars($application['application_number']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container-fluid">
        <div class="row">
            <?php include 'includes/sidebar.php'; ?>
            
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Edit Application - <?= htmlspecialchars($application['application_number']) ?></h1>
                    <a href="applications.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Applications
                    </a>
                </div>

                <?php if (isset($success)): ?>
                <div class="alert alert-success"><?= $success ?></div>
                <?php endif; ?>

                <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?= $error ?></div>
                <?php endif; ?>

                <div class="row">
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Application Details</h5>
                            </div>
                            <div class="card-body">
                                <form method="POST">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Application Number</label>
                                                <input type="text" class="form-control" value="<?= htmlspecialchars($application['application_number']) ?>" readonly>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Status</label>
                                                <select class="form-select" name="status_id" required>
                                                    <?php foreach ($statuses as $status): ?>
                                                    <option value="<?= $status['id'] ?>" <?= $status['id'] == $application['status_id'] ? 'selected' : '' ?>>
                                                        <?= htmlspecialchars($status['status_name']) ?>
                                                    </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">User</label>
                                                <input type="text" class="form-control" value="<?= htmlspecialchars($application['first_name'] . ' ' . $application['last_name'] . ' (' . $application['email'] . ')') ?>" readonly>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Service</label>
                                                <input type="text" class="form-control" value="<?= htmlspecialchars($application['service_name']) ?>" readonly>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Destination Country</label>
                                                <select class="form-select" name="destination_country_id">
                                                    <option value="">Select Country</option>
                                                    <?php foreach ($countries as $country): ?>
                                                    <option value="<?= $country['id'] ?>" <?= $country['id'] == $application['destination_country_id'] ? 'selected' : '' ?>>
                                                        <?= htmlspecialchars($country['country_name']) ?>
                                                    </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Travel Purpose</label>
                                                <input type="text" class="form-control" name="travel_purpose" value="<?= htmlspecialchars($application['travel_purpose'] ?? '') ?>">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Intended Travel Date</label>
                                                <input type="date" class="form-control" name="intended_travel_date" value="<?= $application['intended_travel_date'] ?>">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Duration of Stay</label>
                                                <input type="text" class="form-control" name="duration_of_stay" value="<?= htmlspecialchars($application['duration_of_stay'] ?? '') ?>">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label class="form-label">Base Amount</label>
                                                <input type="number" class="form-control" value="<?= $application['base_amount'] ?>" readonly>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label class="form-label">Additional Fees</label>
                                                <input type="number" class="form-control" name="additional_fees" step="0.01" value="<?= $application['additional_fees'] ?>">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label class="form-label">Discount</label>
                                                <input type="number" class="form-control" name="discount_amount" step="0.01" value="<?= $application['discount_amount'] ?>">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Internal Notes</label>
                                        <textarea class="form-control" name="internal_notes" rows="4"><?= htmlspecialchars($application['internal_notes'] ?? '') ?></textarea>
                                    </div>

                                    <div class="d-flex justify-content-end">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save"></i> Update Application
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Current Payment Info</h5>
                            </div>
                            <div class="card-body">
                                <p><strong>Total Amount:</strong> <?= $application['currency'] ?> <?= number_format($application['total_amount'], 2) ?></p>
                                <p><strong>Payment Plan:</strong> <?= ucfirst($application['payment_plan']) ?></p>
                                <p><strong>Amount Paid:</strong> <?= $application['currency'] ?> <?= number_format($application['amount_paid_so_far'], 2) ?></p>
                                <p><strong>Completion:</strong> <?= number_format($application['payment_completion_percentage'], 1) ?>%</p>
                                
                                <hr>
                                
                                <p><strong>Submitted:</strong> <?= date('M j, Y g:i A', strtotime($application['submitted_at'])) ?></p>
                                <p><strong>Last Updated:</strong> <?= date('M j, Y g:i A', strtotime($application['last_updated_at'])) ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>