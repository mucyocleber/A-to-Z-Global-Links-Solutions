<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit;
}

require_once '../config/database.php';

try {
    $pdo = getConnection();
    $services = $pdo->query("SELECT s.*, sc.name as category_name, sc.icon as category_icon FROM services s LEFT JOIN service_categories sc ON s.category_id = sc.id ORDER BY s.created_at DESC")->fetchAll();
} catch (Exception $e) {
    $services = [];
}

$pageTitle = 'Services';
?>

<?php include 'includes/header.php'; ?>
<?php include 'includes/sidebar.php'; ?>

<main class="main-content">
    <div class="content-header">
        <h1 class="page-title">Services Management</h1>
        <p class="page-subtitle">Manage visa services and their details</p>
    </div>
    
    <div class="content-body">
        <div class="stats-row">
            <div class="stat-card">
                <div class="stat-icon active">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-info">
                    <div class="stat-number"><?= count(array_filter($services, function($s) { return $s['is_active']; })) ?></div>
                    <div class="stat-label">Active Services</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon total">
                    <i class="fas fa-concierge-bell"></i>
                </div>
                <div class="stat-info">
                    <div class="stat-number"><?= count($services) ?></div>
                    <div class="stat-label">Total Services</div>
                </div>
            </div>
        </div>
        
        <div class="page-actions">
            <button class="btn btn-primary" onclick="addNewService()">
                <i class="fas fa-plus"></i>
                Add New Service
            </button>
        </div>
        
        <div class="services-grid">
            <?php if (empty($services)): ?>
                <div class="empty-state">
                    <i class="fas fa-concierge-bell"></i>
                    <h3>No Services Found</h3>
                    <p>Start by adding your first visa service</p>
                    <button class="btn btn-primary" onclick="addNewService()">Add Service</button>
                </div>
            <?php else: ?>
                <?php foreach ($services as $service): ?>
                    <div class="service-card">
                        <div class="service-thumbnail">
                            <div class="category-icon">
                                <i class="<?= htmlspecialchars($service['category_icon'] ?? 'fas fa-file-alt') ?>"></i>
                            </div>
                            <?php if ($service['is_featured']): ?>
                                <div class="featured-badge">
                                    <i class="fas fa-star"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="service-header">
                            <h3 class="service-title"><?= htmlspecialchars($service['name']) ?></h3>
                            <div class="service-status">
                                <span class="status <?= $service['is_active'] ? 'active' : 'inactive' ?>">
                                    <?= $service['is_active'] ? 'Active' : 'Inactive' ?>
                                </span>
                            </div>
                        </div>
                        
                        <div class="service-content">
                            <p class="service-description"><?= htmlspecialchars(substr($service['short_description'] ?? 'No description available', 0, 120)) ?><?= strlen($service['short_description'] ?? '') > 120 ? '...' : '' ?></p>
                            
                            <div class="service-details">
                                <div class="detail-row">
                                    <div class="detail-item">
                                        <i class="fas fa-dollar-sign"></i>
                                        <span><?= $service['currency'] ?> <?= number_format($service['base_price'] ?? 0, 0) ?></span>
                                    </div>
                                    <div class="detail-item">
                                        <i class="fas fa-clock"></i>
                                        <span><?= htmlspecialchars(substr($service['processing_time_description'] ?? ($service['processing_time_min'] ? $service['processing_time_min'] . '-' . $service['processing_time_max'] . ' days' : 'N/A'), 0, 15)) ?><?= strlen($service['processing_time_description'] ?? '') > 15 ? '...' : '' ?></span>
                                    </div>
                                </div>
                                <div class="detail-item category">
                                    <i class="fas fa-tag"></i>
                                    <span><?= htmlspecialchars(substr($service['category_name'] ?? 'N/A', 0, 20)) ?><?= strlen($service['category_name'] ?? '') > 20 ? '...' : '' ?></span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="service-actions">
                            <button class="btn btn-edit" onclick="editService(<?= $service['id'] ?>)">
                                <i class="fas fa-edit"></i> Edit
                            </button>
                            <div class="action-group">
                                <button class="btn btn-view" onclick="viewService(<?= $service['id'] ?>)">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button class="btn btn-toggle <?= $service['is_active'] ? 'btn-deactivate' : 'btn-activate' ?>" onclick="toggleServiceStatus(<?= $service['id'] ?>, <?= $service['is_active'] ? 0 : 1 ?>)">
                                    <i class="fas <?= $service['is_active'] ? 'fa-pause' : 'fa-play' ?>"></i>
                                </button>
                                <button class="btn btn-delete" onclick="deleteService(<?= $service['id'] ?>)">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</main>

<!-- View Service Modal -->
<div id="viewModal" class="modal-overlay">
    <div class="modal-content large">
        <div class="modal-header">
            <h3>Service Details</h3>
            <button class="modal-close" onclick="closeViewModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body" id="viewModalBody">
            <!-- Service details will be loaded here -->
        </div>
    </div>
</div>

<!-- Edit Service Modal -->
<div id="editModal" class="modal-overlay">
    <div class="modal-content large">
        <div class="modal-header">
            <h3>Edit Service</h3>
            <button class="modal-close" onclick="closeEditModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body" id="editModalBody">
            <!-- Edit form will be loaded here -->
        </div>
    </div>
</div>

<!-- Custom Confirmation Modal -->
<div id="confirmModal" class="modal-overlay">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modalTitle">Confirm Action</h3>
        </div>
        <div class="modal-body">
            <p id="modalMessage">Are you sure you want to perform this action?</p>
        </div>
        <div class="modal-actions">
            <button class="btn btn-cancel" onclick="closeModal()">Cancel</button>
            <button class="btn btn-confirm" id="confirmBtn" onclick="confirmAction()">Confirm</button>
        </div>
    </div>
</div>

<!-- Success/Error Toast -->
<div id="toast" class="toast">
    <div class="toast-content">
        <i id="toastIcon" class="fas fa-check-circle"></i>
        <span id="toastMessage">Action completed successfully</span>
    </div>
</div>

<style>
.main-content {
    margin-left: 260px;
    margin-top: 70px;
    padding: 2rem;
    min-height: calc(100vh - 70px);
    background: #f8fafc;
}

.content-header {
    margin-bottom: 2rem;
}

.page-title {
    font-size: 2rem;
    font-weight: 700;
    color: #1e293b;
    margin-bottom: 0.5rem;
}

.page-subtitle {
    color: #64748b;
    font-size: 1.1rem;
}

.stats-row {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.stat-card {
    background: rgba(255, 255, 255, 0.9);
    backdrop-filter: blur(10px);
    border-radius: 16px;
    padding: 1.5rem;
    display: flex;
    align-items: center;
    gap: 1rem;
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.1);
    border: 1px solid rgba(255, 255, 255, 0.3);
    transition: transform 0.2s;
}

.stat-card:hover {
    transform: translateY(-2px);
}

.stat-icon {
    width: 50px;
    height: 50px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.25rem;
}

.stat-icon.active {
    background: #10b981;
}

.stat-icon.total {
    background: #3b82f6;
}

.stat-number {
    font-size: 1.75rem;
    font-weight: 700;
    color: #1e293b;
}

.stat-label {
    color: #64748b;
    font-size: 0.875rem;
    font-weight: 500;
}

.page-actions {
    margin-bottom: 2rem;
}

.btn {
    padding: 0.75rem 1.5rem;
    border: none;
    border-radius: 12px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.3s;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.9rem;
}

.btn-primary {
    background: #3b82f6;
    color: white;
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
}

.btn-secondary {
    background: #64748b;
    color: white;
}

.btn-success {
    background: #10b981;
    color: white;
}

.btn-warning {
    background: #f59e0b;
    color: white;
}

.btn-sm {
    padding: 0.5rem 1rem;
    font-size: 0.8rem;
}

.btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
}

.services-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: 1.5rem;
}

.service-card {
    background: white;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    transition: all 0.3s ease;
    border: 1px solid #e5e7eb;
    height: 420px;
    display: flex;
    flex-direction: column;
}

.service-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 30px rgba(0,0,0,0.12);
}

.service-thumbnail {
    height: 120px;
    background: linear-gradient(135deg, #3b82f6, #1d4ed8);
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
}

.category-icon {
    font-size: 2.5rem;
    color: white;
    opacity: 0.9;
}

.featured-badge {
    position: absolute;
    top: 12px;
    right: 12px;
    background: #f59e0b;
    color: white;
    width: 28px;
    height: 28px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.75rem;
    box-shadow: 0 2px 8px rgba(245, 158, 11, 0.3);
}

.service-header {
    padding: 1.25rem 1.5rem 0.75rem;
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 1rem;
}

.service-title {
    font-size: 1.1rem;
    font-weight: 700;
    color: #1e293b;
    margin: 0;
    line-height: 1.3;
    flex: 1;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    text-overflow: ellipsis;
}

.service-status .status {
    padding: 0.25rem 0.75rem;
    border-radius: 12px;
    font-size: 0.7rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    white-space: nowrap;
}

.status.active {
    background: #dcfce7;
    color: #166534;
}

.status.inactive {
    background: #fee2e2;
    color: #991b1b;
}

.service-content {
    padding: 0 1.5rem;
    flex: 1;
    display: flex;
    flex-direction: column;
}

.service-description {
    color: #64748b;
    line-height: 1.5;
    margin-bottom: 1rem;
    font-size: 0.875rem;
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
    text-overflow: ellipsis;
    flex: 1;
}

.service-details {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
    margin-bottom: 1rem;
}

.detail-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0.75rem;
}

.detail-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.8rem;
    color: #374151;
    font-weight: 500;
    padding: 0.5rem 0.75rem;
    background: #f8fafc;
    border-radius: 8px;
    border: 1px solid #e5e7eb;
}

.detail-item.category {
    grid-column: 1 / -1;
    background: #f0f9ff;
    border-color: #bae6fd;
    color: #0c4a6e;
}

.detail-item i {
    color: #3b82f6;
    font-size: 0.75rem;
    width: 12px;
    flex-shrink: 0;
}

.service-actions {
    padding: 1rem 1.5rem;
    background: #f8fafc;
    border-top: 1px solid #e5e7eb;
    display: flex;
    gap: 0.75rem;
    margin-top: auto;
}

.btn-edit {
    flex: 1;
    background: #3b82f6;
    color: white;
    padding: 0.625rem 1rem;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    font-size: 0.8rem;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.375rem;
}

.btn-edit:hover {
    background: #1d4ed8;
    transform: translateY(-1px);
}

.action-group {
    display: flex;
    gap: 0.5rem;
}

.btn-view, .btn-toggle, .btn-delete {
    width: 36px;
    height: 36px;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.8rem;
}

.btn-view {
    background: #6366f1;
    color: white;
}

.btn-view:hover {
    background: #4f46e5;
    transform: translateY(-1px);
}

.btn-activate {
    background: #10b981;
    color: white;
}

.btn-activate:hover {
    background: #059669;
    transform: translateY(-1px);
}

.btn-deactivate {
    background: #f59e0b;
    color: white;
}

.btn-deactivate:hover {
    background: #d97706;
    transform: translateY(-1px);
}

.btn-delete {
    background: #ef4444;
    color: white;
}

.btn-delete:hover {
    background: #dc2626;
    transform: translateY(-1px);
}

/* Modal Styles */
.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    backdrop-filter: blur(4px);
    display: none;
    align-items: center;
    justify-content: center;
    z-index: 1000;
    animation: fadeIn 0.3s ease;
    overflow-y: auto;
    padding: 1rem;
}

.modal-overlay.show {
    display: flex;
}

.modal-content {
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(20px);
    border-radius: 16px;
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
    border: 1px solid rgba(255, 255, 255, 0.3);
    max-width: 400px;
    width: 90%;
    animation: slideUp 0.3s ease;
    max-height: 90vh;
    overflow-y: auto;
}

.modal-content.large {
    max-width: 800px;
    width: 95%;
}

.modal-header {
    padding: 1.5rem 1.5rem 1rem;
    border-bottom: 1px solid rgba(226, 232, 240, 0.5);
    display: flex;
    justify-content: space-between;
    align-items: center;
    position: sticky;
    top: 0;
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(20px);
    z-index: 1;
}

.modal-header h3 {
    margin: 0;
    color: #1e293b;
    font-size: 1.25rem;
    font-weight: 600;
}

.modal-close {
    background: none;
    border: none;
    color: #64748b;
    cursor: pointer;
    padding: 0.5rem;
    border-radius: 8px;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
}

.modal-close:hover {
    background: rgba(239, 68, 68, 0.1);
    color: #ef4444;
}

.modal-body {
    padding: 1rem 1.5rem;
}

.modal-body p {
    margin: 0;
    color: #64748b;
    line-height: 1.5;
}

.modal-actions {
    padding: 1rem 1.5rem 1.5rem;
    display: flex;
    gap: 0.75rem;
    justify-content: flex-end;
}

.btn-cancel {
    background: #6b7280;
    color: white;
}

.btn-confirm {
    background: #ef4444;
    color: white;
}

.btn-confirm.activate {
    background: #10b981;
}

/* Toast Styles */
.toast {
    position: fixed;
    top: 20px;
    right: 20px;
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(20px);
    border-radius: 12px;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
    border: 1px solid rgba(255, 255, 255, 0.3);
    padding: 1rem 1.25rem;
    z-index: 1001;
    transform: translateX(400px);
    transition: transform 0.3s ease;
}

.toast.show {
    transform: translateX(0);
}

.toast-content {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.toast.success #toastIcon {
    color: #10b981;
}

.toast.error #toastIcon {
    color: #ef4444;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

@keyframes slideUp {
    from { opacity: 0; transform: translateY(30px); }
    to { opacity: 1; transform: translateY(0); }
}

/* Service View Styles */
.service-view {
    color: #1e293b;
}

.view-header {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-bottom: 2rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid rgba(226, 232, 240, 0.5);
}

.service-icon {
    width: 60px;
    height: 60px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.5rem;
}

.service-info h2 {
    margin: 0 0 0.5rem 0;
    font-size: 1.5rem;
    font-weight: 600;
}

.status-badge {
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
}

.status-badge.active {
    background: linear-gradient(135deg, #d1fae5, #a7f3d0);
    color: #065f46;
}

.status-badge.inactive {
    background: linear-gradient(135deg, #fee2e2, #fecaca);
    color: #991b1b;
}

.info-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1rem;
    margin-bottom: 1.5rem;
}

.info-item {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.info-item label {
    font-size: 0.875rem;
    font-weight: 600;
    color: #64748b;
}

.info-item span {
    font-weight: 500;
}

.description-section {
    margin-bottom: 1.5rem;
}

.description-section label {
    display: block;
    font-size: 0.875rem;
    font-weight: 600;
    color: #64748b;
    margin-bottom: 0.5rem;
}

.description-section p {
    line-height: 1.6;
    margin: 0;
}

.loading, .error {
    text-align: center;
    padding: 2rem;
    color: #64748b;
}

.loading i {
    font-size: 1.5rem;
    margin-right: 0.5rem;
}

.error {
    color: #ef4444;
}

@media (max-width: 768px) {
    .modal-content.large {
        width: 98%;
        margin: 0.5rem;
    }
    
    .info-grid {
        grid-template-columns: 1fr;
    }
    
    .view-header {
        flex-direction: column;
        text-align: center;
    }
}

.empty-state {
    grid-column: 1 / -1;
    text-align: center;
    padding: 4rem 2rem;
    color: #64748b;
    background: rgba(255, 255, 255, 0.9);
    backdrop-filter: blur(10px);
    border-radius: 16px;
    border: 1px solid rgba(255, 255, 255, 0.3);
}

.empty-state i {
    font-size: 3rem;
    margin-bottom: 1rem;
    display: block;
    color: #94a3b8;
}

.empty-state h3 {
    font-size: 1.5rem;
    color: #1e293b;
    margin-bottom: 0.5rem;
}

.empty-state p {
    margin-bottom: 2rem;
}

@media (max-width: 768px) {
    .main-content {
        margin-left: 0;
        padding: 1rem;
    }
    
    .stats-row {
        gap: 1rem;
        margin-bottom: 1.5rem;
    }
    
    .stat-card {
        padding: 1.25rem;
    }
    
    .services-grid {
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 1rem;
    }
    
    .service-card {
        height: 380px;
    }
    
    .service-thumbnail {
        height: 100px;
    }
    
    .category-icon {
        font-size: 2rem;
    }
    
    .service-header {
        padding: 1rem 1.25rem 0.75rem;
        flex-direction: column;
        align-items: flex-start;
        gap: 0.75rem;
    }
    
    .service-title {
        font-size: 1rem;
    }
    
    .service-content {
        padding: 0 1.25rem;
    }
    
    .service-description {
        font-size: 0.8rem;
        -webkit-line-clamp: 2;
    }
    
    .detail-item {
        font-size: 0.75rem;
        padding: 0.5rem 0.625rem;
    }
    
    .service-actions {
        padding: 1rem 1.25rem;
        flex-direction: column;
        gap: 0.75rem;
    }
    
    .action-group {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 0.5rem;
    }
    
    .btn-view, .btn-toggle, .btn-delete {
        width: 100%;
        height: 32px;
    }
    
    .btn-edit {
        height: 36px;
        font-size: 0.8rem;
    }
}

@media (max-width: 480px) {
    .services-grid {
        grid-template-columns: 1fr;
        gap: 1rem;
    }
    
    .service-card {
        height: 360px;
    }
    
    .service-thumbnail {
        height: 80px;
    }
    
    .category-icon {
        font-size: 1.8rem;
    }
}
</style>

<script>
let currentAction = null;

function addNewService() {
    window.location.href = 'add_service.php';
}

function editService(serviceId) {
    window.location.href = `edit_service.php?id=${serviceId}`;
}

function viewService(serviceId) {
    showViewModal(serviceId);
}

function showViewModal(serviceId) {
    document.getElementById('viewModalBody').innerHTML = '<div class="loading"><i class="fas fa-spinner fa-spin"></i> Loading...</div>';
    document.getElementById('viewModal').classList.add('show');
    
    fetch(`ajax/get_service.php?id=${serviceId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const service = data.service;
                document.getElementById('viewModalBody').innerHTML = `
                    <div class="service-view">
                        <div class="view-header">
                            <div class="service-icon">
                                <i class="${service.category_icon || 'fas fa-file-alt'}"></i>
                            </div>
                            <div class="service-info">
                                <h2>${service.name}</h2>
                                <span class="status-badge ${service.is_active ? 'active' : 'inactive'}">
                                    ${service.is_active ? 'Active' : 'Inactive'}
                                </span>
                            </div>
                        </div>
                        
                        <div class="view-content">
                            <div class="info-grid">
                                <div class="info-item">
                                    <label>Category</label>
                                    <span>${service.category_name || 'N/A'}</span>
                                </div>
                                <div class="info-item">
                                    <label>Price</label>
                                    <span>${service.currency} ${parseFloat(service.base_price).toLocaleString()}</span>
                                </div>
                                <div class="info-item">
                                    <label>Processing Time</label>
                                    <span>${service.processing_time_description || service.processing_time_min + '-' + service.processing_time_max + ' days'}</span>
                                </div>
                                <div class="info-item">
                                    <label>Featured</label>
                                    <span>${service.is_featured ? 'Yes' : 'No'}</span>
                                </div>
                            </div>
                            
                            <div class="description-section">
                                <label>Short Description</label>
                                <p>${service.short_description || 'No description available'}</p>
                            </div>
                            
                            ${service.full_description ? `
                                <div class="description-section">
                                    <label>Full Description</label>
                                    <p>${service.full_description}</p>
                                </div>
                            ` : ''}
                            
                            ${service.requirements_summary ? `
                                <div class="description-section">
                                    <label>Requirements</label>
                                    <p>${service.requirements_summary}</p>
                                </div>
                            ` : ''}
                            
                            ${service.terms_conditions ? `
                                <div class="description-section">
                                    <label>Terms & Conditions</label>
                                    <p>${service.terms_conditions}</p>
                                </div>
                            ` : ''}
                        </div>
                    </div>
                `;
            } else {
                document.getElementById('viewModalBody').innerHTML = '<div class="error">Failed to load service details</div>';
            }
        })
        .catch(error => {
            document.getElementById('viewModalBody').innerHTML = '<div class="error">Error loading service details</div>';
        });
}

function showEditModal(serviceId) {
    document.getElementById('editModalBody').innerHTML = '<div class="loading"><i class="fas fa-spinner fa-spin"></i> Loading...</div>';
    document.getElementById('editModal').classList.add('show');
    
    fetch(`ajax/get_service_edit.php?id=${serviceId}`)
        .then(response => response.text())
        .then(html => {
            document.getElementById('editModalBody').innerHTML = html;
        })
        .catch(error => {
            document.getElementById('editModalBody').innerHTML = '<div class="error">Error loading edit form</div>';
        });
}

function closeViewModal() {
    document.getElementById('viewModal').classList.remove('show');
}

function closeEditModal() {
    document.getElementById('editModal').classList.remove('show');
}

function showModal(title, message, action, confirmClass = '') {
    document.getElementById('modalTitle').textContent = title;
    document.getElementById('modalMessage').textContent = message;
    document.getElementById('confirmBtn').className = `btn btn-confirm ${confirmClass}`;
    document.getElementById('confirmModal').classList.add('show');
    currentAction = action;
}

function closeModal() {
    document.getElementById('confirmModal').classList.remove('show');
    currentAction = null;
}

function confirmAction() {
    if (currentAction) {
        currentAction();
        closeModal();
    }
}

function showToast(message, type = 'success') {
    const toast = document.getElementById('toast');
    const icon = document.getElementById('toastIcon');
    const messageEl = document.getElementById('toastMessage');
    
    toast.className = `toast ${type}`;
    icon.className = type === 'success' ? 'fas fa-check-circle' : 'fas fa-exclamation-circle';
    messageEl.textContent = message;
    
    toast.classList.add('show');
    setTimeout(() => {
        toast.classList.remove('show');
    }, 3000);
}

function toggleServiceStatus(serviceId, newStatus) {
    const action = newStatus ? 'activate' : 'deactivate';
    const confirmClass = newStatus ? 'activate' : '';
    
    showModal(
        `${action.charAt(0).toUpperCase() + action.slice(1)} Service`,
        `Are you sure you want to ${action} this service?`,
        () => {
            fetch('ajax/toggle_service_status.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    service_id: serviceId,
                    status: newStatus
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast(`Service ${action}d successfully`);
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showToast(data.message || `Failed to ${action} service`, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast(`An error occurred while ${action}ing the service`, 'error');
            });
        },
        confirmClass
    );
}

function deleteService(serviceId) {
    showModal(
        'Delete Service',
        'Are you sure you want to delete this service? This action cannot be undone.',
        () => {
            fetch('ajax/delete_service.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    service_id: serviceId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast('Service deleted successfully');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showToast(data.message || 'Failed to delete service', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('An error occurred while deleting the service', 'error');
            });
        }
    );
}

// Close modal when clicking outside
document.getElementById('confirmModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeModal();
    }
});

document.getElementById('viewModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeViewModal();
    }
});

document.getElementById('editModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeEditModal();
    }
});

// Make showToast function available globally
window.showToast = showToast;
</script>

<?php include 'includes/footer.php'; ?>