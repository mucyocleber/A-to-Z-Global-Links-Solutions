<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit;
}

require_once '../config/database.php';

try {
    $pdo = getConnection();
    $categories = $pdo->query("SELECT * FROM service_categories ORDER BY sort_order, name")->fetchAll();
} catch (Exception $e) {
    $categories = [];
}

$pageTitle = 'Service Categories';
?>

<?php include 'includes/header.php'; ?>
<?php include 'includes/sidebar.php'; ?>

<main class="main-content">
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i>
            <?= $_SESSION['success'] ?>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-error">
            <i class="fas fa-exclamation-triangle"></i>
            <?= $_SESSION['error'] ?>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>
    <div class="content-header">
        <h1 class="page-title">Service Categories</h1>
        <p class="page-subtitle">Manage service categories and organization</p>
    </div>
    
    <div class="control-panel">
        <button class="btn-add" onclick="showAddModal()">
            <i class="fas fa-plus"></i> Add Category
        </button>
    </div>
    
    <div class="categories-grid">
        <?php foreach ($categories as $category): ?>
            <div class="category-card">
                <div class="card-header">
                    <h3><?= htmlspecialchars($category['name']) ?></h3>
                    <span class="status-badge <?= $category['is_active'] ? 'active' : 'inactive' ?>">
                        <?= $category['is_active'] ? 'Active' : 'Inactive' ?>
                    </span>
                </div>
                <div class="card-body">
                    <p><?= htmlspecialchars(substr($category['description'] ?: 'No description available', 0, 120)) ?><?= strlen($category['description'] ?: '') > 120 ? '...' : '' ?></p>
                    <div class="card-meta">
                        <span>Sort Order: <?= $category['sort_order'] ?></span>
                    </div>
                </div>
                <div class="card-actions">
                    <button class="btn-delete" onclick="showDeleteConfirm(<?= $category['id'] ?>, '<?= htmlspecialchars($category['name'], ENT_QUOTES) ?>')">
                        <i class="fas fa-trash"></i> Delete
                    </button>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</main>

<!-- Modern Confirmation Modal -->
<div id="confirmModal" class="confirm-modal">
    <div class="confirm-content">
        <div class="confirm-icon">
            <i class="fas fa-exclamation-triangle"></i>
        </div>
        <h3 class="confirm-title">Delete Category</h3>
        <p class="confirm-message" id="confirmMessage">Are you sure you want to delete this category? This action cannot be undone.</p>
        <div class="confirm-actions">
            <button class="btn-confirm-cancel" onclick="closeConfirmModal()">Cancel</button>
            <button class="btn-confirm-delete" id="confirmDeleteBtn" onclick="confirmDelete()">Delete</button>
        </div>
    </div>
</div>

<!-- Add/Edit Modal -->
<div id="categoryModal" class="modal-overlay">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modalTitle">Add Category</h3>
            <button class="modal-close" onclick="closeModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body">
            <form id="categoryForm" method="POST" action="ajax/save_category.php">
                <input type="hidden" id="category_id" name="category_id">
                <div class="form-group">
                    <label for="name">Name *</label>
                    <input type="text" id="name" name="name" required class="form-control">
                </div>
                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" class="form-control" rows="3"></textarea>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="sort_order">Sort Order</label>
                        <input type="number" id="sort_order" name="sort_order" class="form-control" value="0">
                    </div>
                    <div class="form-group">
                        <label for="is_active">Status</label>
                        <select id="is_active" name="is_active" class="form-control">
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="form-actions">
                    <button type="button" class="btn-cancel" onclick="closeModal()">Cancel</button>
                    <button type="submit" class="btn-submit">Save Category</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.main-content {
    margin-left: 260px;
    margin-top: 70px;
    padding: 2rem;
    background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%);
    min-height: calc(100vh - 70px);
}

.content-header {
    text-align: center;
    margin-bottom: 3rem;
    padding: 2rem;
    background: linear-gradient(135deg, #1e40af 0%, #3b82f6 50%, #60a5fa 100%);
    color: white;
    border-radius: 20px;
    position: relative;
    overflow: hidden;
}

.content-header::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -20%;
    width: 300px;
    height: 300px;
    background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, transparent 70%);
    border-radius: 50%;
    animation: float 6s ease-in-out infinite;
}

@keyframes float {
    0%, 100% { transform: translateY(0px) rotate(0deg); }
    50% { transform: translateY(-15px) rotate(3deg); }
}

.page-title {
    font-size: clamp(1.8rem, 4vw, 2.2rem);
    font-weight: 800;
    margin-bottom: 0.5rem;
    position: relative;
    z-index: 2;
}

.page-subtitle {
    font-size: 1rem;
    opacity: 0.9;
    position: relative;
    z-index: 2;
    margin: 0;
}

.control-panel {
    background: white;
    padding: 2rem;
    border-radius: 16px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    margin-bottom: 2rem;
    text-align: center;
}

.btn-add {
    background: linear-gradient(135deg, #10b981, #059669);
    color: white;
    padding: 1rem 2rem;
    border: none;
    border-radius: 50px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 1rem;
    box-shadow: 0 8px 25px rgba(16, 185, 129, 0.3);
}

.btn-add:hover {
    background: linear-gradient(135deg, #059669, #047857);
    transform: translateY(-2px);
    box-shadow: 0 12px 30px rgba(16, 185, 129, 0.4);
}

.categories-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: 1.5rem;
}

.category-card {
    background: white;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    transition: all 0.3s ease;
    border: 1px solid #e5e7eb;
    height: 280px;
    display: flex;
    flex-direction: column;
}

.category-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 30px rgba(0,0,0,0.12);
}

.card-header {
    padding: 1.5rem;
    background: linear-gradient(135deg, #3b82f6, #1d4ed8);
    color: white;
    display: flex;
    justify-content: space-between;
    align-items: center;
    position: relative;
}

.card-header::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(255, 255, 255, 0.1);
    opacity: 0;
    transition: opacity 0.3s ease;
}

.category-card:hover .card-header::before {
    opacity: 1;
}

.card-header h3 {
    font-size: 1.25rem;
    font-weight: 700;
    margin: 0;
    position: relative;
    z-index: 1;
}

.status-badge {
    padding: 0.375rem 0.875rem;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    position: relative;
    z-index: 1;
}

.status-badge.active {
    background: rgba(16, 185, 129, 0.2);
    color: #ffffff;
    border: 1px solid rgba(255, 255, 255, 0.3);
}

.status-badge.inactive {
    background: rgba(239, 68, 68, 0.2);
    color: #ffffff;
    border: 1px solid rgba(255, 255, 255, 0.3);
}

.card-body {
    padding: 1.5rem;
    flex: 1;
    display: flex;
    flex-direction: column;
}

.card-body p {
    color: #64748b;
    line-height: 1.6;
    margin-bottom: 1rem;
    flex: 1;
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
    text-overflow: ellipsis;
}

.card-meta {
    font-size: 0.875rem;
    color: #9ca3af;
    font-weight: 500;
    padding: 0.5rem 0.75rem;
    background: #f8fafc;
    border-radius: 8px;
    border: 1px solid #e5e7eb;
    margin-top: auto;
}

.card-actions {
    padding: 1rem 1.5rem;
    background: #f8fafc;
    border-top: 1px solid #e5e7eb;
    display: flex;
    justify-content: center;
    margin-top: auto;
}

.btn-delete {
    background: linear-gradient(135deg, #ef4444, #dc2626);
    color: white;
    padding: 0.75rem 1.5rem;
    border: none;
    border-radius: 50px;
    font-size: 0.875rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    box-shadow: 0 4px 15px rgba(239, 68, 68, 0.3);
}

.btn-delete:hover {
    background: linear-gradient(135deg, #dc2626, #b91c1c);
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(239, 68, 68, 0.4);
}

/* Modern Confirmation Modal */
.confirm-modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.6);
    backdrop-filter: blur(8px);
    display: none;
    align-items: center;
    justify-content: center;
    z-index: 3000;
    animation: fadeIn 0.3s ease;
}

.confirm-modal.show {
    display: flex;
}

.confirm-content {
    background: white;
    border-radius: 20px;
    padding: 2.5rem;
    max-width: 400px;
    width: 90%;
    text-align: center;
    box-shadow: 0 25px 50px rgba(0, 0, 0, 0.25);
    animation: slideUp 0.4s ease;
    position: relative;
}

.confirm-icon {
    width: 80px;
    height: 80px;
    background: linear-gradient(135deg, #fee2e2, #fecaca);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1.5rem;
    color: #dc2626;
    font-size: 2rem;
}

.confirm-title {
    font-size: 1.5rem;
    font-weight: 700;
    color: #1e293b;
    margin-bottom: 1rem;
}

.confirm-message {
    color: #64748b;
    line-height: 1.6;
    margin-bottom: 2rem;
    font-size: 1rem;
}

.confirm-actions {
    display: flex;
    gap: 1rem;
    justify-content: center;
}

.btn-confirm-cancel {
    background: #f1f5f9;
    color: #64748b;
    padding: 0.875rem 1.5rem;
    border: none;
    border-radius: 12px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    font-size: 0.95rem;
}

.btn-confirm-cancel:hover {
    background: #e2e8f0;
    transform: translateY(-1px);
}

.btn-confirm-delete {
    background: linear-gradient(135deg, #ef4444, #dc2626);
    color: white;
    padding: 0.875rem 1.5rem;
    border: none;
    border-radius: 12px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    font-size: 0.95rem;
    box-shadow: 0 4px 15px rgba(239, 68, 68, 0.3);
}

.btn-confirm-delete:hover {
    background: linear-gradient(135deg, #dc2626, #b91c1c);
    transform: translateY(-1px);
    box-shadow: 0 6px 20px rgba(239, 68, 68, 0.4);
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

@keyframes slideUp {
    from { transform: translateY(30px); opacity: 0; }
    to { transform: translateY(0); opacity: 1; }
}

/* Add/Edit Modal Styles */
.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.6);
    backdrop-filter: blur(8px);
    display: none;
    align-items: center;
    justify-content: center;
    z-index: 2000;
    animation: fadeIn 0.3s ease;
}

.modal-overlay.show {
    display: flex;
}

.modal-content {
    background: white;
    border-radius: 16px;
    max-width: 500px;
    width: 90%;
    max-height: 90vh;
    overflow-y: auto;
    box-shadow: 0 25px 50px rgba(0, 0, 0, 0.25);
    animation: slideUp 0.4s ease;
}

.modal-header {
    padding: 2rem 2rem 1rem;
    border-bottom: 1px solid #e5e7eb;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.modal-header h3 {
    margin: 0;
    font-size: 1.5rem;
    font-weight: 700;
    color: #1e293b;
}

.modal-close {
    background: none;
    border: none;
    font-size: 1.5rem;
    color: #64748b;
    cursor: pointer;
    padding: 0.5rem;
    border-radius: 8px;
    transition: all 0.3s ease;
}

.modal-close:hover {
    background: #f1f5f9;
    color: #ef4444;
}

.modal-body {
    padding: 1.5rem 2rem 2rem;
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
}

.form-group label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 600;
    color: #374151;
    font-size: 0.875rem;
}

.form-control {
    width: 100%;
    padding: 0.875rem 1rem;
    border: 2px solid #e5e7eb;
    border-radius: 12px;
    font-size: 0.875rem;
    transition: all 0.3s ease;
    background: white;
}

.form-control:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    transform: translateY(-1px);
}

.form-actions {
    display: flex;
    gap: 1rem;
    justify-content: flex-end;
    margin-top: 2rem;
}

.btn-cancel {
    background: #f1f5f9;
    color: #64748b;
    padding: 0.875rem 1.5rem;
    border: none;
    border-radius: 12px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
}

.btn-cancel:hover {
    background: #e2e8f0;
    transform: translateY(-1px);
}

.btn-submit {
    background: linear-gradient(135deg, #3b82f6, #1d4ed8);
    color: white;
    padding: 0.875rem 1.5rem;
    border: none;
    border-radius: 12px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(59, 130, 246, 0.3);
}

.btn-submit:hover {
    background: linear-gradient(135deg, #1d4ed8, #1e40af);
    transform: translateY(-1px);
    box-shadow: 0 6px 20px rgba(59, 130, 246, 0.4);
}

@media (max-width: 768px) {
    .main-content {
        margin-left: 0;
        padding: 1rem;
        padding-bottom: 100px;
    }
    
    .content-header {
        padding: 1.5rem;
        margin-bottom: 2rem;
    }
    
    .control-panel {
        padding: 1.5rem;
        margin-bottom: 1.5rem;
    }
    
    .categories-grid {
        grid-template-columns: 1fr;
        gap: 1rem;
    }
    
    .category-card {
        height: 260px;
    }
    
    .card-header {
        padding: 1.25rem;
        flex-direction: column;
        gap: 0.75rem;
        text-align: center;
    }
    
    .card-body {
        padding: 1.25rem;
    }
    
    .card-actions {
        padding: 1rem 1.25rem;
    }
    
    .form-row {
        grid-template-columns: 1fr;
    }
    
    .confirm-content {
        padding: 2rem;
        margin: 1rem;
    }
    
    .confirm-actions {
        flex-direction: column;
        gap: 0.75rem;
    }
    
    .btn-confirm-cancel,
    .btn-confirm-delete {
        width: 100%;
    }
}

.alert {
    padding: 1rem 1.5rem;
    border-radius: 12px;
    margin-bottom: 2rem;
    display: flex;
    align-items: center;
    gap: 0.75rem;
    font-weight: 600;
    animation: slideDown 0.3s ease;
}

.alert-success {
    background: linear-gradient(135deg, rgba(16, 185, 129, 0.1), rgba(5, 150, 105, 0.15));
    color: #065f46;
    border: 1px solid rgba(16, 185, 129, 0.3);
}

.alert-error {
    background: linear-gradient(135deg, rgba(239, 68, 68, 0.1), rgba(220, 38, 38, 0.15));
    color: #991b1b;
    border: 1px solid rgba(239, 68, 68, 0.3);
}

@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>

<script>
let deleteId = null;

function showAddModal() {
    document.getElementById('modalTitle').textContent = 'Add Category';
    document.getElementById('categoryForm').reset();
    document.getElementById('category_id').value = '';
    document.getElementById('categoryModal').classList.add('show');
}

function closeModal() {
    document.getElementById('categoryModal').classList.remove('show');
}



function showDeleteConfirm(id, name) {
    console.log('showDeleteConfirm called with ID:', id, 'Name:', name);
    deleteId = id;
    document.getElementById('confirmMessage').textContent = `Are you sure you want to delete "${name}"? This action cannot be undone.`;
    document.getElementById('confirmModal').classList.add('show');
}

function closeConfirmModal() {
    document.getElementById('confirmModal').classList.remove('show');
    deleteId = null;
}

function confirmDelete() {
    if (deleteId) {
        // Create a form and submit it
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = 'ajax/delete_category.php';
        form.style.display = 'none';
        
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'id';
        input.value = deleteId;
        
        form.appendChild(input);
        document.body.appendChild(form);
        form.submit();
    }
}

// Close modals when clicking outside
document.getElementById('categoryModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeModal();
    }
});

document.getElementById('confirmModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeConfirmModal();
    }
});
</script>

<?php include 'includes/footer.php'; ?>