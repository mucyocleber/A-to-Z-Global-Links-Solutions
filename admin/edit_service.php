<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit;
}

require_once '../config/database.php';

$serviceId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$message = '';

// Handle form submission
if ($_POST) {
    try {
        $pdo = getConnection();
        
        $stmt = $pdo->prepare("UPDATE services SET 
            name = ?, category_id = ?, base_price = ?, currency = ?, 
            processing_time_min = ?, processing_time_max = ?, 
            short_description = ?, full_description = ?, 
            is_featured = ?, sort_order = ? 
            WHERE id = ?");
        
        $result = $stmt->execute([
            $_POST['name'],
            $_POST['category_id'],
            $_POST['base_price'],
            $_POST['currency'],
            $_POST['processing_time_min'],
            $_POST['processing_time_max'],
            $_POST['short_description'],
            $_POST['full_description'] ?? '',
            isset($_POST['is_featured']) ? 1 : 0,
            $_POST['sort_order'] ?? 0,
            $serviceId
        ]);
        
        if ($result) {
            $message = '<div class="success">Service updated successfully!</div>';
        } else {
            $message = '<div class="error">Failed to update service.</div>';
        }
    } catch (Exception $e) {
        $message = '<div class="error">Error: ' . $e->getMessage() . '</div>';
    }
}

// Get service data
try {
    $pdo = getConnection();
    
    $stmt = $pdo->prepare("SELECT * FROM services WHERE id = ?");
    $stmt->execute([$serviceId]);
    $service = $stmt->fetch();
    
    if (!$service) {
        header('Location: services.php');
        exit;
    }
    
    $categories = $pdo->query("SELECT * FROM service_categories WHERE is_active = 1 ORDER BY name")->fetchAll();
} catch (Exception $e) {
    $message = '<div class="error">Database error: ' . $e->getMessage() . '</div>';
    $service = null;
    $categories = [];
}

$pageTitle = 'Edit Service';
?>

<?php include 'includes/header.php'; ?>
<?php include 'includes/sidebar.php'; ?>

<main class="main-content">
    <div class="content-header">
        <h1>Edit Service</h1>
        <a href="services.php" class="btn-back">← Back to Services</a>
    </div>
    
    <?= $message ?>
    
    <?php if ($service): ?>
    <div class="edit-container">
        <form method="POST" class="edit-form">
            <div class="form-grid">
                <div class="form-group">
                    <label>Service Name *</label>
                    <input type="text" name="name" value="<?= htmlspecialchars($service['name']) ?>" required>
                </div>
                
                <div class="form-group">
                    <label>Category *</label>
                    <select name="category_id" required>
                        <option value="">Select Category</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>" <?= $cat['id'] == $service['category_id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cat['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Base Price *</label>
                    <input type="number" name="base_price" step="0.01" min="0" value="<?= $service['base_price'] ?>" required>
                </div>
                
                <div class="form-group">
                    <label>Currency *</label>
                    <select name="currency" required>
                        <option value="USD" <?= $service['currency'] == 'USD' ? 'selected' : '' ?>>USD</option>
                        <option value="EUR" <?= $service['currency'] == 'EUR' ? 'selected' : '' ?>>EUR</option>
                        <option value="RWF" <?= $service['currency'] == 'RWF' ? 'selected' : '' ?>>RWF</option>
                        <option value="GBP" <?= $service['currency'] == 'GBP' ? 'selected' : '' ?>>GBP</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Min Processing Days *</label>
                    <input type="number" name="processing_time_min" min="1" value="<?= $service['processing_time_min'] ?>" required>
                </div>
                
                <div class="form-group">
                    <label>Max Processing Days *</label>
                    <input type="number" name="processing_time_max" min="1" value="<?= $service['processing_time_max'] ?>" required>
                </div>
            </div>
            
            <div class="form-group">
                <label>Short Description *</label>
                <textarea name="short_description" rows="3" required><?= htmlspecialchars($service['short_description']) ?></textarea>
            </div>
            
            <div class="form-group">
                <label>Full Description</label>
                <textarea name="full_description" rows="5"><?= htmlspecialchars($service['full_description']) ?></textarea>
            </div>
            
            <div class="form-grid">
                <div class="form-group">
                    <label>Sort Order</label>
                    <input type="number" name="sort_order" min="0" value="<?= $service['sort_order'] ?>">
                </div>
                
                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="is_featured" <?= $service['is_featured'] ? 'checked' : '' ?>>
                        Featured Service
                    </label>
                </div>
            </div>
            
            <div class="form-actions">
                <a href="services.php" class="btn-cancel">Cancel</a>
                <button type="submit" class="btn-save">Update Service</button>
            </div>
        </form>
    </div>
    <?php endif; ?>
</main>

<style>
.main-content {
    margin-left: 260px;
    margin-top: 70px;
    padding: 2rem;
    min-height: calc(100vh - 70px);
    background: #f8fafc;
}

.content-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
}

.content-header h1 {
    font-size: 2rem;
    font-weight: 700;
    color: #1e293b;
    margin: 0;
}

.btn-back {
    background: #6b7280;
    color: white;
    padding: 0.75rem 1.5rem;
    text-decoration: none;
    border-radius: 8px;
    font-weight: 600;
    transition: all 0.3s;
}

.btn-back:hover {
    background: #4b5563;
    transform: translateY(-1px);
}

.success {
    background: #d1fae5;
    color: #065f46;
    padding: 1rem;
    border-radius: 8px;
    margin-bottom: 2rem;
    border: 1px solid #a7f3d0;
}

.error {
    background: #fee2e2;
    color: #991b1b;
    padding: 1rem;
    border-radius: 8px;
    margin-bottom: 2rem;
    border: 1px solid #fecaca;
}

.edit-container {
    background: white;
    border-radius: 16px;
    padding: 2rem;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1.5rem;
    margin-bottom: 1.5rem;
}

.form-group {
    display: flex;
    flex-direction: column;
}

.form-group label {
    font-weight: 600;
    color: #374151;
    margin-bottom: 0.5rem;
    font-size: 0.9rem;
}

.form-group input,
.form-group select,
.form-group textarea {
    padding: 0.75rem;
    border: 2px solid #e5e7eb;
    border-radius: 8px;
    font-size: 0.9rem;
    transition: border-color 0.3s;
    font-family: inherit;
}

.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.form-group textarea {
    resize: vertical;
}

.checkbox-label {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    cursor: pointer;
    margin-top: 1.5rem;
}

.checkbox-label input[type="checkbox"] {
    width: 18px;
    height: 18px;
    accent-color: #3b82f6;
}

.form-actions {
    display: flex;
    gap: 1rem;
    justify-content: flex-end;
    margin-top: 2rem;
    padding-top: 2rem;
    border-top: 1px solid #e5e7eb;
}

.btn-cancel,
.btn-save {
    padding: 0.75rem 2rem;
    border-radius: 8px;
    font-weight: 600;
    text-decoration: none;
    cursor: pointer;
    transition: all 0.3s;
    border: none;
    font-size: 0.9rem;
}

.btn-cancel {
    background: #6b7280;
    color: white;
}

.btn-cancel:hover {
    background: #4b5563;
    transform: translateY(-1px);
}

.btn-save {
    background: #3b82f6;
    color: white;
}

.btn-save:hover {
    background: #1d4ed8;
    transform: translateY(-1px);
}

@media (max-width: 768px) {
    .main-content {
        margin-left: 0;
        padding: 1rem;
    }
    
    .content-header {
        flex-direction: column;
        gap: 1rem;
        align-items: flex-start;
    }
    
    .form-grid {
        grid-template-columns: 1fr;
        gap: 1rem;
    }
    
    .form-actions {
        flex-direction: column;
    }
    
    .edit-container {
        padding: 1.5rem;
    }
}
</style>

<?php include 'includes/footer.php'; ?>