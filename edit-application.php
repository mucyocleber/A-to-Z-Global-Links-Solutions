<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$applicationId = $_GET['id'] ?? null;
if (!$applicationId) {
    header('Location: dashboard.php');
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo = getConnection();
        
        $stmt = $pdo->prepare("
            UPDATE applications SET 
                destination_country_id = (SELECT id FROM countries WHERE name = ?),
                travel_purpose = ?, 
                intended_travel_date = ?, 
                duration_of_stay = ?
            WHERE id = ? AND user_id = ?
        ");
        
        $stmt->execute([
            $_POST['destination_country'],
            $_POST['travel_purpose'],
            $_POST['travel_date'] ?: null,
            $_POST['duration_of_stay'],
            $applicationId,
            $_SESSION['user_id']
        ]);
        
        $success = "Application updated successfully!";
        
    } catch (Exception $e) {
        $error = "Error updating application: " . $e->getMessage();
    }
}

try {
    $pdo = getConnection();
    
    // Get application details
    $stmt = $pdo->prepare("
        SELECT a.*, s.name as service_name, c.name as destination_country
        FROM applications a
        LEFT JOIN services s ON a.service_id = s.id
        LEFT JOIN countries c ON a.destination_country_id = c.id
        WHERE a.id = ? AND a.user_id = ?
    ");
    $stmt->execute([$applicationId, $_SESSION['user_id']]);
    $application = $stmt->fetch();
    
    if (!$application) {
        header('Location: dashboard.php');
        exit;
    }
    
    // Get countries
    $countries = $pdo->query("SELECT * FROM countries WHERE is_active = 1 ORDER BY name")->fetchAll();
    
} catch (Exception $e) {
    $error = "Error loading application: " . $e->getMessage();
    error_log("Edit application error: " . $e->getMessage());
    $application = null;
    $countries = [];
}

$pageTitle = 'Edit Application - A to Z Global Link Solutions';
include 'includes/header.php';
?>

<style>
.main-content { margin-top: 70px; padding: 2rem 5%; background: linear-gradient(135deg, rgba(59, 130, 246, 0.05) 0%, rgba(147, 197, 253, 0.1) 50%, rgba(219, 234, 254, 0.05) 100%); min-height: 100vh; }
.edit-container { max-width: 800px; margin: 0 auto; }
.edit-header { text-align: center; margin-bottom: 2rem; }
.edit-header h1 { color: #1e40af; font-size: 2rem; font-weight: 700; margin-bottom: 0.5rem; }
.edit-header p { color: #64748b; }
.edit-form { background: white; padding: 2rem; border-radius: 20px; box-shadow: 0 8px 32px rgba(59, 130, 246, 0.1); }
.form-group { margin-bottom: 1.5rem; }
.form-group label { display: block; color: #374151; font-weight: 600; margin-bottom: 0.5rem; }
.form-control { width: 100%; padding: 0.875rem 1rem; border: 2px solid #e5e7eb; border-radius: 12px; font-size: 1rem; transition: all 0.3s; }
.form-control:focus { outline: none; border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1); }
.btn-update { background: linear-gradient(135deg, #3b82f6, #1d4ed8); color: white; padding: 0.875rem 2rem; border: none; border-radius: 12px; font-weight: 600; cursor: pointer; transition: all 0.3s; }
.btn-update:hover { background: linear-gradient(135deg, #1d4ed8, #1e40af); transform: translateY(-2px); }
.btn-back { background: #6b7280; color: white; padding: 0.875rem 2rem; border: none; border-radius: 12px; font-weight: 600; text-decoration: none; display: inline-block; margin-right: 1rem; }
.alert { padding: 1rem; border-radius: 12px; margin-bottom: 1rem; }
.alert-success { background: #d1fae5; color: #065f46; border: 1px solid #34d399; }
.alert-error { background: #fee2e2; color: #991b1b; border: 1px solid #f87171; }
</style>

<main class="main-content">
    <div class="edit-container">
        <div class="edit-header">
            <h1>Edit Application</h1>
            <p>Application #<?= htmlspecialchars($application['application_number']) ?></p>
        </div>
        
        <?php if (isset($success)): ?>
            <div class="alert alert-success"><?= $success ?></div>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-error"><?= $error ?></div>
        <?php endif; ?>
        
        <?php if ($application): ?>
            <form method="POST">
                <div class="form-group">
                    <label>Service</label>
                    <input type="text" class="form-control" value="<?= htmlspecialchars($application['service_name']) ?>" readonly>
                </div>
                
                <div class="form-group">
                    <label for="destination_country">Destination Country</label>
                    <select id="destination_country" name="destination_country" class="form-control">
                        <option value="">Select Destination</option>
                        <?php foreach ($countries as $country): ?>
                            <option value="<?= htmlspecialchars($country['name']) ?>" <?= $application['destination_country'] === $country['name'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($country['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="travel_date">Intended Travel Date</label>
                    <input type="date" id="travel_date" name="travel_date" class="form-control" value="<?= $application['intended_travel_date'] ?>">
                </div>
                
                <div class="form-group">
                    <label for="duration_of_stay">Duration of Stay</label>
                    <input type="text" id="duration_of_stay" name="duration_of_stay" class="form-control" value="<?= htmlspecialchars($application['duration_of_stay']) ?>" placeholder="e.g., 2 weeks, 3 months">
                </div>
                
                <div class="form-group">
                    <label for="travel_purpose">Purpose of Travel</label>
                    <textarea id="travel_purpose" name="travel_purpose" class="form-control" rows="4"><?= htmlspecialchars($application['travel_purpose']) ?></textarea>
                </div>
                
                <div style="text-align: center; margin-top: 2rem;">
                    <a href="dashboard.php" class="btn-back">Back to Dashboard</a>
                    <button type="submit" class="btn-update">Update Application</button>
                </div>
            </form>
        </div>
        <?php else: ?>
            <div class="alert alert-error">
                <strong>Application not found or access denied.</strong>
                <br><a href="dashboard.php" style="color: #991b1b; text-decoration: underline;">Return to Dashboard</a>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php include 'includes/footer.php'; ?>