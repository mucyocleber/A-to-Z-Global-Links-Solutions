<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit;
}

require_once '../config/database.php';

// Handle form submissions
if ($_POST) {
    try {
        $pdo = getConnection();
        
        if ($_POST['action'] === 'save') {
            $destination_id = $_POST['destination_id'] ?? null;
            $country_id = $_POST['country_id'] ?? null;
            $flag_emoji = $_POST['flag_emoji'] ?? null;
            $services_offered = $_POST['services_offered'] ?? null;
            $is_featured = $_POST['is_featured'] ?? 1;
            $sort_order = $_POST['sort_order'] ?? 0;
            
            if (empty($country_id) || empty($flag_emoji) || empty($services_offered)) {
                $error = 'All required fields must be filled';
            } else {
                if (empty($destination_id)) {
                    // Insert new destination
                    $stmt = $pdo->prepare("
                        INSERT INTO destination_countries (country_id, flag_emoji, services_offered, is_featured, sort_order) 
                        VALUES (?, ?, ?, ?, ?)
                    ");
                    $stmt->execute([$country_id, $flag_emoji, $services_offered, $is_featured, $sort_order]);
                    $success = 'Destination added successfully!';
                } else {
                    // Update existing destination
                    $stmt = $pdo->prepare("
                        UPDATE destination_countries 
                        SET country_id = ?, flag_emoji = ?, services_offered = ?, is_featured = ?, sort_order = ? 
                        WHERE id = ?
                    ");
                    $stmt->execute([$country_id, $flag_emoji, $services_offered, $is_featured, $sort_order, $destination_id]);
                    $success = 'Destination updated successfully!';
                }
            }
        } elseif ($_POST['action'] === 'delete') {
            $id = $_POST['id'] ?? null;
            if ($id) {
                $stmt = $pdo->prepare("DELETE FROM destination_countries WHERE id = ?");
                $stmt->execute([$id]);
                $success = 'Destination deleted successfully!';
            }
        }
    } catch (Exception $e) {
        $error = 'Database error: ' . $e->getMessage();
    }
}

try {
    $pdo = getConnection();
    $destinations = $pdo->query("
        SELECT dc.*, c.name as country_name 
        FROM destination_countries dc 
        JOIN countries c ON dc.country_id = c.id 
        ORDER BY dc.sort_order, c.name
    ")->fetchAll();
    
    $countries = $pdo->query("SELECT id, name FROM countries WHERE is_active = 1 ORDER BY name")->fetchAll();
} catch (Exception $e) {
    $destinations = [];
    $countries = [];
}

$pageTitle = 'Destination Countries';
?>

<?php include 'includes/header.php'; ?>
<?php include 'includes/sidebar.php'; ?>

<main class="main-content">
    <div class="content-header">
        <h1 class="page-title">Destination Countries</h1>
        <p class="page-subtitle">Manage featured destination countries and their services</p>
    </div>
    
    <?php if (isset($success)): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>
    
    <?php if (isset($error)): ?>
        <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    
    <div class="control-panel">
        <button class="btn-add" onclick="showAddModal()">
            <i class="fas fa-plus"></i> Add Destination
        </button>
    </div>
    
    <div class="destinations-grid">
        <?php foreach ($destinations as $destination): ?>
            <div class="destination-card">
                <div class="card-header">
                    <div class="destination-info">
                        <span class="flag"><?= htmlspecialchars($destination['flag_emoji']) ?></span>
                        <h3><?= htmlspecialchars($destination['country_name']) ?></h3>
                    </div>
                    <div class="card-badges">
                        <?php if ($destination['is_featured']): ?>
                            <span class="featured-badge">Featured</span>
                        <?php endif; ?>
                        <span class="sort-badge">Order: <?= $destination['sort_order'] ?></span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="services-offered">
                        <strong>Services:</strong>
                        <p><?= htmlspecialchars($destination['services_offered']) ?></p>
                    </div>
                </div>
                <div class="card-actions">
                    <button class="btn btn-edit" onclick="editDestination(<?= $destination['id'] ?>, '<?= htmlspecialchars($destination['country_name']) ?>', <?= $destination['country_id'] ?>, '<?= htmlspecialchars($destination['flag_emoji']) ?>', '<?= htmlspecialchars($destination['services_offered']) ?>', <?= $destination['is_featured'] ?>, <?= $destination['sort_order'] ?>)">
                        <i class="fas fa-edit"></i> Edit
                    </button>
                    <form method="POST" style="display: inline;" onsubmit="handleDelete(event)">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" value="<?= $destination['id'] ?>">
                        <button type="submit" class="btn btn-delete">
                            <i class="fas fa-trash"></i> Delete
                        </button>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</main>

<!-- Modern Confirmation Modal -->
<div id="confirmModal" class="confirm-overlay">
    <div class="confirm-dialog">
        <div class="confirm-header">
            <div class="confirm-icon-wrapper">
                <i id="confirmIcon" class="fas fa-question-circle"></i>
            </div>
            <h3 id="confirmTitle">Confirm Action</h3>
        </div>
        <div class="confirm-body">
            <p id="confirmMessage">Are you sure you want to proceed?</p>
        </div>
        <div class="confirm-footer">
            <button type="button" class="btn-cancel" onclick="hideConfirm()">Cancel</button>
            <button type="button" class="btn-confirm" id="confirmBtn" onclick="executeConfirm()">Confirm</button>
        </div>
    </div>
</div>

<!-- Add/Edit Modal -->
<div id="destinationModal" class="modal-overlay">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modalTitle">Add Destination</h3>
            <button class="modal-close" onclick="closeModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body">
            <form method="POST" action="" onsubmit="handleSave(event)">
                <input type="hidden" name="action" value="save">
                <input type="hidden" name="destination_id" id="destination_id">
                
                <div class="form-group">
                    <label for="country_id">Country *</label>
                    <select name="country_id" id="country_id" required class="form-control">
                        <option value="">Select Country...</option>
                        <?php foreach ($countries as $country): ?>
                            <option value="<?= $country['id'] ?>"><?= htmlspecialchars($country['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="flag_emoji">Flag Emoji *</label>
                        <input type="text" name="flag_emoji" id="flag_emoji" required class="form-control" placeholder="🇷🇼">
                    </div>
                    <div class="form-group">
                        <label for="sort_order">Sort Order</label>
                        <input type="number" name="sort_order" id="sort_order" class="form-control" value="0">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="services_offered">Services Offered *</label>
                    <textarea name="services_offered" id="services_offered" required class="form-control" rows="3" placeholder="Tourist visas, Work permits, Study visas"></textarea>
                </div>
                
                <div class="form-group">
                    <label for="is_featured">Featured Destination</label>
                    <select name="is_featured" id="is_featured" class="form-control">
                        <option value="1">Yes</option>
                        <option value="0">No</option>
                    </select>
                </div>
                
                <div class="form-actions">
                    <button type="button" class="btn-cancel" onclick="closeModal()">Cancel</button>
                    <button type="submit" class="btn-submit">Save Destination</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
/* Modern Confirmation Modal */
.confirm-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100vw;
    height: 100vh;
    background: rgba(0, 0, 0, 0.7);
    display: none;
    align-items: center;
    justify-content: center;
    z-index: 999999;
    backdrop-filter: blur(4px);
}

.confirm-overlay.active {
    display: flex;
}

.confirm-dialog {
    background: white;
    border-radius: 20px;
    width: 90%;
    max-width: 450px;
    box-shadow: 0 25px 80px rgba(0, 0, 0, 0.4);
    animation: confirmPop 0.3s cubic-bezier(0.68, -0.55, 0.265, 1.55);
    overflow: hidden;
}

@keyframes confirmPop {
    0% {
        opacity: 0;
        transform: scale(0.7) translateY(-50px);
    }
    100% {
        opacity: 1;
        transform: scale(1) translateY(0);
    }
}

.confirm-header {
    padding: 2rem 2rem 1rem;
    text-align: center;
}

.confirm-icon-wrapper {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1rem;
    font-size: 2.5rem;
    background: #fee2e2;
    color: #dc2626;
}

.confirm-icon-wrapper.success {
    background: #d1fae5;
    color: #059669;
}

.confirm-icon-wrapper.warning {
    background: #fef3c7;
    color: #d97706;
}

#confirmTitle {
    font-size: 1.5rem;
    font-weight: 700;
    color: #111827;
    margin: 0;
}

.confirm-body {
    padding: 0 2rem 1.5rem;
    text-align: center;
}

#confirmMessage {
    color: #6b7280;
    font-size: 1rem;
    line-height: 1.6;
    margin: 0;
}

.confirm-footer {
    padding: 1.5rem 2rem 2rem;
    display: flex;
    gap: 1rem;
    justify-content: center;
}

.btn-cancel {
    background: #f3f4f6;
    color: #374151;
    border: none;
    padding: 0.75rem 2rem;
    border-radius: 10px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
    font-size: 0.95rem;
}

.btn-cancel:hover {
    background: #e5e7eb;
    transform: translateY(-1px);
}

.btn-confirm {
    background: #ef4444;
    color: white;
    border: none;
    padding: 0.75rem 2rem;
    border-radius: 10px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
    font-size: 0.95rem;
}

.btn-confirm:hover {
    background: #dc2626;
    transform: translateY(-1px);
}

.btn-confirm.success {
    background: #10b981;
}

.btn-confirm.success:hover {
    background: #059669;
}

.alert {
    padding: 1rem 1.5rem;
    border-radius: 8px;
    margin-bottom: 1.5rem;
    font-weight: 500;
}

.alert-success {
    background: #d1fae5;
    color: #065f46;
    border: 1px solid #a7f3d0;
}

.alert-error {
    background: #fee2e2;
    color: #991b1b;
    border: 1px solid #fca5a5;
}

.main-content {
    margin-left: 250px;
    padding: 2rem;
    background: #f8fafc;
    min-height: 100vh;
}

.content-header {
    margin-bottom: 2rem;
}

.page-title {
    font-size: 1.875rem;
    font-weight: 700;
    color: #111827;
    margin-bottom: 0.5rem;
}

.page-subtitle {
    color: #6b7280;
    font-size: 1rem;
}

.control-panel {
    background: white;
    padding: 1.5rem;
    border-radius: 12px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    border: 1px solid #e5e7eb;
    margin-bottom: 2rem;
}

.btn-add {
    background: #10b981;
    color: white;
    padding: 0.75rem 1.5rem;
    border: none;
    border-radius: 8px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.btn-add:hover {
    background: #059669;
}

.destinations-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(400px, 1fr));
    gap: 1.5rem;
}

.destination-card {
    background: white;
    border-radius: 12px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    border: 1px solid #e5e7eb;
    overflow: hidden;
    transition: all 0.2s;
}

.destination-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

.card-header {
    padding: 1.5rem;
    border-bottom: 1px solid #f3f4f6;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.destination-info {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.flag {
    font-size: 2rem;
}

.destination-info h3 {
    font-size: 1.125rem;
    font-weight: 600;
    color: #111827;
    margin: 0;
}

.card-badges {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    align-items: flex-end;
}

.featured-badge {
    background: #fef3c7;
    color: #92400e;
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 500;
}

.sort-badge {
    background: #e5e7eb;
    color: #6b7280;
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 500;
}

.card-body {
    padding: 1.5rem;
}

.services-offered strong {
    color: #374151;
    font-size: 0.875rem;
}

.services-offered p {
    color: #6b7280;
    margin: 0.5rem 0 0 0;
    line-height: 1.5;
}

.card-actions {
    padding: 1rem 1.5rem;
    border-top: 1px solid #f3f4f6;
    display: flex;
    gap: 0.5rem;
}

.btn {
    padding: 0.5rem 1rem;
    border: none;
    border-radius: 6px;
    font-size: 0.875rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    gap: 0.375rem;
}

.btn-edit {
    background: #f59e0b;
    color: white;
}

.btn-edit:hover {
    background: #d97706;
}

.btn-delete {
    background: #ef4444;
    color: white;
}

.btn-delete:hover {
    background: #dc2626;
}

.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.6);
    display: none;
    align-items: center;
    justify-content: center;
    z-index: 9999;
}

.modal-overlay.show {
    display: flex;
}

.modal-content {
    background: white;
    border-radius: 12px;
    max-width: 500px;
    width: 90%;
    max-height: 90vh;
    overflow-y: auto;
}

.modal-header {
    padding: 1.5rem;
    border-bottom: 1px solid #e5e7eb;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.modal-header h3 {
    margin: 0;
    font-size: 1.25rem;
    font-weight: 600;
    color: #111827;
}

.modal-close {
    background: none;
    border: none;
    font-size: 1.25rem;
    color: #6b7280;
    cursor: pointer;
}

.modal-body {
    padding: 1.5rem;
}

.form-group {
    margin-bottom: 1rem;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
}

.form-group label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 500;
    color: #374151;
}

.form-control {
    width: 100%;
    padding: 0.75rem;
    border: 1px solid #d1d5db;
    border-radius: 8px;
    font-size: 0.875rem;
}

.form-control:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.form-actions {
    display: flex;
    gap: 1rem;
    justify-content: flex-end;
    margin-top: 1.5rem;
}

.btn-cancel {
    background: #f1f5f9;
    color: #64748b;
    padding: 0.75rem 1.5rem;
    border: none;
    border-radius: 8px;
    font-weight: 500;
    cursor: pointer;
}

.btn-submit {
    background: #3b82f6;
    color: white;
    padding: 0.75rem 1.5rem;
    border: none;
    border-radius: 8px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-submit:hover {
    background: #1d4ed8;
    transform: translateY(-1px);
}

.btn-submit:active {
    transform: translateY(0);
}

.btn-submit:disabled {
    background: #9ca3af;
    cursor: not-allowed;
}

@media (max-width: 768px) {
    .main-content {
        margin-left: 0;
        padding: 1rem;
    }
    
    .destinations-grid {
        grid-template-columns: 1fr;
    }
    
    .form-row {
        grid-template-columns: 1fr;
    }
    
    .card-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 1rem;
    }
    
    .card-badges {
        align-items: flex-start;
    }
}
</style>

<script>
let pendingAction = null;

// Simple confirmation system
function showConfirm(title, message, type = 'danger') {
    document.getElementById('confirmTitle').textContent = title;
    document.getElementById('confirmMessage').textContent = message;
    
    const iconWrapper = document.querySelector('.confirm-icon-wrapper');
    const confirmBtn = document.getElementById('confirmBtn');
    
    // Reset classes
    iconWrapper.className = 'confirm-icon-wrapper ' + type;
    confirmBtn.className = 'btn-confirm ' + type;
    
    // Set icons
    const icon = document.getElementById('confirmIcon');
    if (type === 'success') {
        icon.className = 'fas fa-check-circle';
        confirmBtn.textContent = 'Save';
    } else {
        icon.className = 'fas fa-exclamation-triangle';
        confirmBtn.textContent = 'Delete';
    }
    
    document.getElementById('confirmModal').classList.add('active');
}

function hideConfirm() {
    document.getElementById('confirmModal').classList.remove('active');
    pendingAction = null;
}

function executeConfirm() {
    if (pendingAction) {
        pendingAction();
    }
    hideConfirm();
}

// Form handlers
function handleSave(event) {
    event.preventDefault();
    const form = event.target;
    const isEdit = document.getElementById('destination_id').value !== '';
    
    pendingAction = () => {
        const formData = new FormData(form);
        const tempForm = document.createElement('form');
        tempForm.method = 'POST';
        
        // Copy all form data to temp form
        for (let [key, value] of formData.entries()) {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = key;
            input.value = value;
            tempForm.appendChild(input);
        }
        
        document.body.appendChild(tempForm);
        tempForm.submit();
    };
    
    showConfirm(
        isEdit ? 'Update Destination' : 'Save Destination',
        isEdit ? 'Update this destination country?' : 'Save this new destination country?',
        'success'
    );
}

function handleDelete(event) {
    event.preventDefault();
    const form = event.target;
    
    pendingAction = () => {
        const formData = new FormData(form);
        const tempForm = document.createElement('form');
        tempForm.method = 'POST';
        
        // Copy all form data to temp form
        for (let [key, value] of formData.entries()) {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = key;
            input.value = value;
            tempForm.appendChild(input);
        }
        
        document.body.appendChild(tempForm);
        tempForm.submit();
    };
    
    showConfirm(
        'Delete Destination',
        'This will permanently delete this destination. Continue?',
        'danger'
    );
}

function showAddModal() {
    document.getElementById('modalTitle').textContent = 'Add Destination';
    document.getElementById('destination_id').value = '';
    document.getElementById('country_id').value = '';
    document.getElementById('flag_emoji').value = '';
    document.getElementById('services_offered').value = '';
    document.getElementById('is_featured').value = '1';
    document.getElementById('sort_order').value = '0';
    document.getElementById('destinationModal').classList.add('show');
}

function editDestination(id, countryName, countryId, flagEmoji, servicesOffered, isFeatured, sortOrder) {
    document.getElementById('modalTitle').textContent = 'Edit Destination';
    document.getElementById('destination_id').value = id;
    document.getElementById('country_id').value = countryId;
    document.getElementById('flag_emoji').value = flagEmoji;
    document.getElementById('services_offered').value = servicesOffered;
    document.getElementById('is_featured').value = isFeatured;
    document.getElementById('sort_order').value = sortOrder;
    document.getElementById('destinationModal').classList.add('show');
}

function closeModal() {
    document.getElementById('destinationModal').classList.remove('show');
}
</script>

<?php include 'includes/footer.php'; ?>