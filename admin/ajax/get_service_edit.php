<?php
session_start();
if (!isset($_SESSION['admin_id'])) exit;

require_once '../../config/database.php';
$serviceId = (int)$_GET['id'];

try {
    $pdo = getConnection();
    $service = $pdo->prepare("SELECT * FROM services WHERE id = ?")->execute([$serviceId]) ? $pdo->fetch() : null;
    $categories = $pdo->query("SELECT * FROM service_categories WHERE is_active = 1 ORDER BY name")->fetchAll();
} catch (Exception $e) {
    echo '<div class="error">Database error</div>';
    exit;
}

if (!$service) {
    echo '<div class="error">Service not found</div>';
    exit;
}
?>

<form id="editForm" class="edit-form">
    <div class="form-grid">
        <div class="form-group">
            <label>Service Name</label>
            <input type="text" name="name" value="<?= htmlspecialchars($service['name']) ?>" required>
        </div>
        
        <div class="form-group">
            <label>Category</label>
            <select name="category_id" required>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= $cat['id'] ?>" <?= $cat['id'] == $service['category_id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($cat['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="form-group">
            <label>Price</label>
            <input type="number" name="base_price" step="0.01" value="<?= $service['base_price'] ?>" required>
        </div>
        
        <div class="form-group">
            <label>Currency</label>
            <select name="currency" required>
                <option value="USD" <?= $service['currency'] == 'USD' ? 'selected' : '' ?>>USD</option>
                <option value="EUR" <?= $service['currency'] == 'EUR' ? 'selected' : '' ?>>EUR</option>
                <option value="RWF" <?= $service['currency'] == 'RWF' ? 'selected' : '' ?>>RWF</option>
            </select>
        </div>
        
        <div class="form-group">
            <label>Min Days</label>
            <input type="number" name="processing_time_min" value="<?= $service['processing_time_min'] ?>" required>
        </div>
        
        <div class="form-group">
            <label>Max Days</label>
            <input type="number" name="processing_time_max" value="<?= $service['processing_time_max'] ?>" required>
        </div>
    </div>
    
    <div class="form-group">
        <label>Short Description</label>
        <textarea name="short_description" rows="3" required><?= htmlspecialchars($service['short_description']) ?></textarea>
    </div>
    
    <div class="form-group">
        <label>Full Description</label>
        <textarea name="full_description" rows="4"><?= htmlspecialchars($service['full_description']) ?></textarea>
    </div>
    
    <div class="form-grid">
        <div class="form-group">
            <label>Sort Order</label>
            <input type="number" name="sort_order" value="<?= $service['sort_order'] ?>">
        </div>
        
        <div class="form-group">
            <label>
                <input type="checkbox" name="is_featured" <?= $service['is_featured'] ? 'checked' : '' ?>>
                Featured Service
            </label>
        </div>
    </div>
    
    <div class="form-actions">
        <button type="button" onclick="closeEditModal()">Cancel</button>
        <button type="submit">Update</button>
    </div>
</form>

<style>
.edit-form { padding: 1rem; }
.form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem; }
.form-group { display: flex; flex-direction: column; }
.form-group label { font-weight: 600; margin-bottom: 0.5rem; color: #374151; }
.form-group input, .form-group select, .form-group textarea { 
    padding: 0.75rem; border: 2px solid #e5e7eb; border-radius: 8px; 
    font-size: 0.9rem; transition: border-color 0.3s;
}
.form-group input:focus, .form-group select:focus, .form-group textarea:focus { 
    outline: none; border-color: #3b82f6; 
}
.form-group textarea { resize: vertical; font-family: inherit; }
.form-actions { display: flex; gap: 1rem; justify-content: flex-end; margin-top: 1.5rem; }
.form-actions button { 
    padding: 0.75rem 1.5rem; border: none; border-radius: 8px; 
    font-weight: 600; cursor: pointer; transition: all 0.3s;
}
.form-actions button[type="button"] { background: #6b7280; color: white; }
.form-actions button[type="submit"] { background: #3b82f6; color: white; }
.form-actions button:hover { transform: translateY(-1px); }
@media (max-width: 768px) { .form-grid { grid-template-columns: 1fr; } }
</style>

<script>
document.getElementById('editForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const data = { id: <?= $serviceId ?> };
    
    for (let [key, value] of formData.entries()) {
        data[key] = value;
    }
    
    data.is_featured = formData.has('is_featured') ? 1 : 0;
    
    const btn = this.querySelector('button[type="submit"]');
    btn.textContent = 'Updating...';
    btn.disabled = true;
    
    fetch('ajax/update_service.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            alert('Service updated successfully');
            closeEditModal();
            location.reload();
        } else {
            alert('Error: ' + result.message);
        }
    })
    .catch(error => {
        alert('Update failed');
        console.error(error);
    })
    .finally(() => {
        btn.textContent = 'Update';
        btn.disabled = false;
    });
});
</script>