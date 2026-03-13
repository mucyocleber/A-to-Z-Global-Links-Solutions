<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit;
}

require_once '../config/database.php';

if ($_POST) {
    try {
        $pdo = getConnection();
        $pdo->beginTransaction();
        
        $slug = strtolower(str_replace(array(' ', '&', '/', '\\'), array('-', 'and', '-', '-'), $_POST['name']));
        $slug = preg_replace('/[^a-z0-9\-]/', '', $slug);
        $slug = preg_replace('/-+/', '-', $slug);
        $slug = trim($slug, '-');
        
        // Check for duplicate slug
        $originalSlug = $slug;
        $counter = 1;
        while (true) {
            $stmt = $pdo->prepare("SELECT id FROM services WHERE slug = ?");
            $stmt->execute([$slug]);
            if (!$stmt->fetch()) break;
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }
        
        // Build requirements JSON
        $requirements = ['documents' => []];
        if (!empty($_POST['doc_names'])) {
            foreach ($_POST['doc_names'] as $index => $name) {
                if (!empty($name)) {
                    $requirements['documents'][] = [
                        'id' => $index + 1,
                        'name' => $name,
                        'type' => $_POST['doc_types'][$index] ?? 'pdf',
                        'required' => isset($_POST['doc_required'][$index]),
                        'description' => $_POST['doc_descriptions'][$index] ?? '',
                        'maxSize' => (int)($_POST['doc_sizes'][$index] ?? 5)
                    ];
                }
            }
        }
        
        $stmt = $pdo->prepare("INSERT INTO services (category_id, name, slug, short_description, full_description, base_price, currency, processing_time_min, processing_time_max, processing_time_description, requirements_json, terms_conditions, is_featured, sort_order, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        $stmt->execute([
            $_POST['category_id'],
            $_POST['name'],
            $slug,
            $_POST['short_description'],
            $_POST['full_description'],
            $_POST['base_price'],
            $_POST['currency'],
            $_POST['processing_time_min'],
            $_POST['processing_time_max'],
            $_POST['processing_time_description'] ?? null,
            json_encode($requirements),
            $_POST['terms_conditions'] ?? null,
            isset($_POST['is_featured']) ? 1 : 0,
            $_POST['sort_order'] ?? 0,
            1
        ]);
        
        $pdo->commit();
        header('Location: services.php?success=1');
        exit;
    } catch (Exception $e) {
        $pdo->rollback();
        $error = $e->getMessage();
    }
}

try {
    $pdo = getConnection();
    $categories = $pdo->query("SELECT * FROM service_categories WHERE is_active = 1 ORDER BY sort_order, name")->fetchAll();
} catch (Exception $e) {
    $categories = [];
}

$pageTitle = 'Add Service';
?>

<?php include 'includes/header.php'; ?>
<?php include 'includes/sidebar.php'; ?>

<main class="main-content">
    <div class="content-header">
        <div class="header-actions">
            <button class="btn-back" onclick="window.location.href='services.php'">
                <i class="fas fa-arrow-left"></i>
            </button>
            <div>
                <h1 class="page-title">Add New Service</h1>
                <p class="page-subtitle">Create a comprehensive visa service with document requirements</p>
            </div>
        </div>
    </div>
    
    <div class="content-body">
        <?php if (isset($error)): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-triangle"></i>
                <span>Error: <?= htmlspecialchars($error) ?></span>
            </div>
        <?php endif; ?>
        
        <form method="POST" class="service-form">
            <div class="form-sections">
                <!-- Basic Information -->
                <div class="form-section">
                    <div class="section-header">
                        <h3><i class="fas fa-info-circle"></i> Basic Information</h3>
                    </div>
                    <div class="section-content">
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label required">Service Name</label>
                                <input type="text" name="name" class="form-input" required placeholder="e.g., Tourist Visa Application">
                            </div>
                            <div class="form-group">
                                <label class="form-label required">Category</label>
                                <select name="category_id" class="form-input" required>
                                    <option value="">Select Category</option>
                                    <?php foreach ($categories as $category): ?>
                                        <option value="<?= $category['id'] ?>"><?= htmlspecialchars($category['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label required">Short Description</label>
                            <textarea name="short_description" class="form-textarea" rows="2" maxlength="500" required placeholder="Brief description for service cards and listings"></textarea>
                            <small class="form-hint">Maximum 500 characters</small>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Full Description</label>
                            <textarea name="full_description" class="form-textarea" rows="4" placeholder="Comprehensive description of the service, what's included, process overview..."></textarea>
                        </div>
                    </div>
                </div>

                <!-- Pricing & Processing -->
                <div class="form-section">
                    <div class="section-header">
                        <h3><i class="fas fa-dollar-sign"></i> Pricing & Processing</h3>
                    </div>
                    <div class="section-content">
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label required">Base Price</label>
                                <input type="number" name="base_price" class="form-input" step="0.01" min="0" required placeholder="0.00">
                            </div>
                            <div class="form-group">
                                <label class="form-label required">Currency</label>
                                <select name="currency" class="form-input" required>
                                    <option value="RWF">RWF - Rwandan Franc</option>
                                    <option value="USD">USD - US Dollar</option>
                                    <option value="EUR">EUR - Euro</option>
                                    <option value="GBP">GBP - British Pound</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label required">Min Processing Days</label>
                                <input type="number" name="processing_time_min" class="form-input" min="1" required placeholder="5">
                            </div>
                            <div class="form-group">
                                <label class="form-label required">Max Processing Days</label>
                                <input type="number" name="processing_time_max" class="form-input" min="1" required placeholder="10">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Processing Time Description</label>
                            <input type="text" name="processing_time_description" class="form-input" placeholder="e.g., 5-10 business days (excluding weekends)">
                        </div>
                    </div>
                </div>

                <!-- Document Requirements -->
                <div class="form-section">
                    <div class="section-header">
                        <h3><i class="fas fa-file-text"></i> Document Requirements</h3>
                    </div>
                    <div class="section-content">
                        <div class="requirements-section">
                            <div class="requirements-header">
                                <h4>Required Documents</h4>
                                <button type="button" class="btn-add-requirement" onclick="addDocument()">
                                    <i class="fas fa-plus"></i> Add Document
                                </button>
                            </div>
                            
                            <div id="documents-container">
                                <div class="document-item">
                                    <div class="doc-row">
                                        <div class="doc-field">
                                            <label>Document Name</label>
                                            <input type="text" name="doc_names[]" placeholder="e.g., Passport Copy" class="form-input">
                                        </div>
                                        <div class="doc-field">
                                            <label>File Type</label>
                                            <select name="doc_types[]" class="form-input">
                                                <option value="pdf">PDF Document</option>
                                                <option value="image">Image (JPG/PNG)</option>
                                            </select>
                                        </div>
                                        <div class="doc-field">
                                            <label>Max Size (MB)</label>
                                            <input type="number" name="doc_sizes[]" class="form-input" value="5" min="1" max="50">
                                        </div>
                                        <div class="doc-checkbox">
                                            <label class="checkbox-label">
                                                <input type="checkbox" name="doc_required[0]" class="form-checkbox" checked>
                                                <span class="checkbox-custom"></span>
                                                <span class="checkbox-text">Required</span>
                                            </label>
                                        </div>
                                        <button type="button" class="btn-remove-doc" onclick="removeDocument(this)">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                    <div class="doc-description">
                                        <label>Description</label>
                                        <input type="text" name="doc_descriptions[]" placeholder="Clear scan of passport bio-data page" class="form-input">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Terms & Settings -->
                <div class="form-section">
                    <div class="section-header">
                        <h3><i class="fas fa-cog"></i> Terms & Settings</h3>
                    </div>
                    <div class="section-content">
                        <div class="form-group">
                            <label class="form-label">Terms & Conditions</label>
                            <textarea name="terms_conditions" class="form-textarea" rows="3" placeholder="Service-specific terms, refund policy, processing conditions..."></textarea>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Sort Order</label>
                                <input type="number" name="sort_order" class="form-input" min="0" value="0" placeholder="0">
                                <small class="form-hint">Lower numbers appear first</small>
                            </div>
                            <div class="form-group">
                                <div class="checkbox-group">
                                    <label class="checkbox-label">
                                        <input type="checkbox" name="is_featured" class="form-checkbox">
                                        <span class="checkbox-custom"></span>
                                        <span class="checkbox-text">Featured Service</span>
                                    </label>
                                    <small class="form-hint">Featured services appear prominently</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="form-actions">
                <button type="button" class="btn btn-secondary" onclick="window.location.href='services.php'">
                    <i class="fas fa-times"></i> Cancel
                </button>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Create Service
                </button>
            </div>
        </form>
    </div>
</main>

<style>
.main-content { margin-left: 260px; margin-top: 70px; padding: 1.5rem; min-height: calc(100vh - 70px); background: #f8fafc; }
.content-header { margin-bottom: 2rem; }
.header-actions { display: flex; align-items: center; gap: 1rem; }
.btn-back { width: 40px; height: 40px; border: none; border-radius: 10px; background: white; color: #64748b; cursor: pointer; transition: all 0.3s; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1); }
.btn-back:hover { background: #3b82f6; color: white; transform: translateY(-2px); }
.page-title { font-size: 1.75rem; font-weight: 700; color: #1e293b; margin: 0; }
.page-subtitle { color: #64748b; font-size: 0.95rem; margin: 0.25rem 0 0 0; }
.service-form { max-width: 900px; }
.form-sections { display: flex; flex-direction: column; gap: 1.5rem; }
.form-section { background: white; border-radius: 16px; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05); overflow: hidden; }
.section-header { background: #f8fafc; padding: 1rem 1.5rem; border-bottom: 1px solid #e2e8f0; }
.section-header h3 { color: #334155; font-size: 1rem; font-weight: 600; margin: 0; display: flex; align-items: center; gap: 0.5rem; }
.section-header i { color: #3b82f6; font-size: 0.9rem; }
.section-content { padding: 1.5rem; }
.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem; }
.form-row:last-child { margin-bottom: 0; }
.form-group { display: flex; flex-direction: column; margin-bottom: 1rem; }
.form-group:last-child { margin-bottom: 0; }
.form-label { color: #374151; font-size: 0.875rem; font-weight: 600; margin-bottom: 0.5rem; }
.form-label.required::after { content: '*'; color: #ef4444; margin-left: 0.25rem; }
.form-input, .form-textarea { padding: 0.75rem; border: 2px solid #e5e7eb; border-radius: 10px; font-size: 0.875rem; transition: all 0.3s; background: #ffffff; }
.form-input:focus, .form-textarea:focus { outline: none; border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1); }
.form-textarea { resize: vertical; min-height: 80px; }
.form-hint { color: #6b7280; font-size: 0.75rem; margin-top: 0.25rem; }
.checkbox-group { display: flex; flex-direction: column; gap: 0.5rem; }
.checkbox-label { display: flex; align-items: center; gap: 0.75rem; cursor: pointer; font-size: 0.875rem; color: #374151; }
.form-checkbox { display: none; }
.checkbox-custom { width: 18px; height: 18px; border: 2px solid #d1d5db; border-radius: 4px; position: relative; transition: all 0.3s; background: #ffffff; }
.form-checkbox:checked + .checkbox-custom { background: #3b82f6; border-color: #3b82f6; }
.form-checkbox:checked + .checkbox-custom::after { content: '✓'; position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); color: white; font-size: 12px; font-weight: bold; }
.requirements-section { margin-top: 1rem; padding: 1rem; background: #f8fafc; border-radius: 12px; border: 1px solid #e2e8f0; }
.requirements-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; }
.requirements-header h4 { color: #1e40af; font-size: 1rem; font-weight: 600; margin: 0; }
.btn-add-requirement { background: #3b82f6; color: white; border: none; padding: 0.5rem 1rem; border-radius: 8px; font-size: 0.8rem; cursor: pointer; display: flex; align-items: center; gap: 0.5rem; transition: all 0.3s; }
.btn-add-requirement:hover { background: #1d4ed8; transform: translateY(-1px); }
.document-item { margin-bottom: 1rem; padding: 1rem; background: white; border-radius: 10px; border: 1px solid #e5e7eb; }
.doc-row { display: grid; grid-template-columns: 2fr 1fr 80px auto auto; gap: 1rem; align-items: end; margin-bottom: 1rem; }
.doc-field { display: flex; flex-direction: column; }
.doc-field label { font-size: 0.8rem; color: #6b7280; margin-bottom: 0.25rem; font-weight: 500; }
.doc-checkbox { display: flex; align-items: center; padding-top: 1.5rem; }
.doc-description { display: flex; flex-direction: column; }
.doc-description label { font-size: 0.8rem; color: #6b7280; margin-bottom: 0.25rem; font-weight: 500; }
.btn-remove-doc { background: #ef4444; color: white; border: none; padding: 0.5rem; border-radius: 6px; cursor: pointer; transition: all 0.3s; display: flex; align-items: center; justify-content: center; width: 36px; height: 36px; }
.btn-remove-doc:hover { background: #dc2626; transform: translateY(-1px); }
.form-actions { display: flex; gap: 1rem; justify-content: flex-end; margin-top: 2rem; padding: 1.5rem; background: white; border-radius: 16px; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05); }
.btn { padding: 0.75rem 1.5rem; border: none; border-radius: 10px; font-weight: 500; cursor: pointer; transition: all 0.3s; font-size: 0.875rem; display: flex; align-items: center; gap: 0.5rem; min-width: 120px; justify-content: center; }
.btn-primary { background: #3b82f6; color: white; box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3); }
.btn-secondary { background: #6b7280; color: white; box-shadow: 0 2px 8px rgba(107, 114, 128, 0.2); }
.btn:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15); }
.alert { padding: 1rem 1.25rem; border-radius: 12px; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.75rem; font-size: 0.875rem; font-weight: 500; }
.alert-error { background: #fef2f2; border: 1px solid #fecaca; color: #dc2626; }
@media (max-width: 768px) { .main-content { margin-left: 0; padding: 1rem; } .form-row { grid-template-columns: 1fr; } .doc-row { grid-template-columns: 1fr; gap: 0.75rem; } .doc-checkbox { padding-top: 0; } .section-content { padding: 1rem; } .form-actions { flex-direction: column; padding: 1rem; } .btn { width: 100%; } }
</style>

<script>
let documentIndex = 1;

function addDocument() {
    const container = document.getElementById('documents-container');
    const newItem = document.createElement('div');
    newItem.className = 'document-item';
    newItem.innerHTML = `
        <div class="doc-row">
            <div class="doc-field">
                <label>Document Name</label>
                <input type="text" name="doc_names[]" placeholder="e.g., Bank Statement" class="form-input">
            </div>
            <div class="doc-field">
                <label>File Type</label>
                <select name="doc_types[]" class="form-input">
                    <option value="pdf">PDF Document</option>
                    <option value="image">Image (JPG/PNG)</option>
                </select>
            </div>
            <div class="doc-field">
                <label>Max Size (MB)</label>
                <input type="number" name="doc_sizes[]" class="form-input" value="5" min="1" max="50">
            </div>
            <div class="doc-checkbox">
                <label class="checkbox-label">
                    <input type="checkbox" name="doc_required[${documentIndex}]" class="form-checkbox" checked>
                    <span class="checkbox-custom"></span>
                    <span class="checkbox-text">Required</span>
                </label>
            </div>
            <button type="button" class="btn-remove-doc" onclick="removeDocument(this)">
                <i class="fas fa-trash"></i>
            </button>
        </div>
        <div class="doc-description">
            <label>Description</label>
            <input type="text" name="doc_descriptions[]" placeholder="Clear description of the document" class="form-input">
        </div>
    `;
    container.appendChild(newItem);
    documentIndex++;
}

function removeDocument(button) {
    button.closest('.document-item').remove();
}
</script>

<?php include 'includes/footer.php'; ?>