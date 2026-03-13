<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit;
}

require_once '../config/database.php';

try {
    $pdo = getConnection();
    $countries = $pdo->query("SELECT * FROM countries ORDER BY name")->fetchAll();
} catch (Exception $e) {
    $countries = [];
}

$pageTitle = 'Countries';
?>

<?php include 'includes/header.php'; ?>
<?php include 'includes/sidebar.php'; ?>

<main class="main-content">
    <div class="content-header">
        <h1 class="page-title">Countries Management</h1>
        <p class="page-subtitle">Manage countries and their visa requirements</p>
    </div>
    
    <div class="control-panel">
        <div class="search-container">
            <input type="text" id="searchInput" placeholder="🔍 Search countries..." class="search-input">
        </div>
        <button class="btn-add" onclick="showAddModal()">
            <i class="fas fa-plus"></i> Add Country
        </button>
    </div>
    
    <div class="countries-table">
        <table>
            <thead>
                <tr>
                    <th>Country</th>
                    <th>ISO Codes</th>
                    <th>Phone Code</th>
                    <th>Currency</th>
                    <th>Region</th>
                    <th>Visa Required</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($countries as $country): ?>
                    <tr>
                        <td>
                            <div class="country-info">
                                <strong><?= htmlspecialchars($country['name']) ?></strong>
                            </div>
                        </td>
                        <td><?= htmlspecialchars($country['iso_code_2']) ?> / <?= htmlspecialchars($country['iso_code_3']) ?></td>
                        <td><?= htmlspecialchars($country['phone_code']) ?></td>
                        <td><?= htmlspecialchars($country['currency_code']) ?></td>
                        <td><?= htmlspecialchars($country['region']) ?></td>
                        <td>
                            <span class="visa-badge <?= $country['visa_required_for_rwanda'] ? 'required' : 'not-required' ?>">
                                <?= $country['visa_required_for_rwanda'] ? 'Required' : 'Not Required' ?>
                            </span>
                        </td>
                        <td>
                            <span class="status-badge <?= $country['is_active'] ? 'active' : 'inactive' ?>">
                                <?= $country['is_active'] ? 'Active' : 'Inactive' ?>
                            </span>
                        </td>
                        <td>
                            <div class="action-buttons">
                                <button class="btn btn-edit" onclick="editCountry(<?= $country['id'] ?>)">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-delete" onclick="deleteCountry(<?= $country['id'] ?>)">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</main>

<!-- Add/Edit Modal -->
<div id="countryModal" class="modal-overlay">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modalTitle">Add Country</h3>
            <button class="modal-close" onclick="closeModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body">
            <form id="countryForm" onsubmit="saveCountry(event)">
                <input type="hidden" id="country_id" name="country_id">
                <div class="form-row">
                    <div class="form-group">
                        <label for="name">Country Name *</label>
                        <input type="text" id="name" name="name" required class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="region">Region</label>
                        <select id="region" name="region" class="form-control">
                            <option value="Africa">Africa</option>
                            <option value="Asia">Asia</option>
                            <option value="Europe">Europe</option>
                            <option value="North America">North America</option>
                            <option value="South America">South America</option>
                            <option value="Oceania">Oceania</option>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="iso_code_2">ISO Code 2 *</label>
                        <input type="text" id="iso_code_2" name="iso_code_2" required class="form-control" maxlength="2">
                    </div>
                    <div class="form-group">
                        <label for="iso_code_3">ISO Code 3 *</label>
                        <input type="text" id="iso_code_3" name="iso_code_3" required class="form-control" maxlength="3">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="phone_code">Phone Code</label>
                        <input type="text" id="phone_code" name="phone_code" class="form-control" placeholder="+250">
                    </div>
                    <div class="form-group">
                        <label for="currency_code">Currency Code</label>
                        <input type="text" id="currency_code" name="currency_code" class="form-control" maxlength="3" placeholder="RWF">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="visa_required_for_rwanda">Visa Required for Rwanda</label>
                        <select id="visa_required_for_rwanda" name="visa_required_for_rwanda" class="form-control">
                            <option value="1">Yes</option>
                            <option value="0">No</option>
                        </select>
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
                    <button type="submit" class="btn-submit">Save Country</button>
                </div>
            </form>
        </div>
    </div>
</div>

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
    display: flex;
    gap: 1rem;
    align-items: center;
    justify-content: space-between;
}

.search-container {
    flex: 1;
    max-width: 400px;
}

.search-input {
    width: 100%;
    padding: 0.75rem 1rem;
    border: 1px solid #d1d5db;
    border-radius: 8px;
    font-size: 0.875rem;
    background: #f9fafb;
}

.search-input:focus {
    outline: none;
    border-color: #3b82f6;
    background: white;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
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

.countries-table {
    background: white;
    border-radius: 12px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    border: 1px solid #e5e7eb;
    overflow: hidden;
}

table {
    width: 100%;
    border-collapse: collapse;
}

th, td {
    padding: 1rem;
    text-align: left;
    border-bottom: 1px solid #f3f4f6;
}

th {
    background: #f8fafc;
    font-weight: 600;
    color: #374151;
    font-size: 0.875rem;
}

.country-info strong {
    color: #111827;
}

.visa-badge, .status-badge {
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 500;
}

.visa-badge.required {
    background: #fee2e2;
    color: #991b1b;
}

.visa-badge.not-required {
    background: #d1fae5;
    color: #065f46;
}

.status-badge.active {
    background: #d1fae5;
    color: #065f46;
}

.status-badge.inactive {
    background: #fee2e2;
    color: #991b1b;
}

.action-buttons {
    display: flex;
    gap: 0.5rem;
}

.btn {
    padding: 0.5rem;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.2s;
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
    z-index: 2000;
}

.modal-overlay.show {
    display: flex;
}

.modal-content {
    background: white;
    border-radius: 12px;
    max-width: 600px;
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
}

@media (max-width: 768px) {
    .main-content {
        margin-left: 0;
        padding: 1rem;
    }
    
    .control-panel {
        flex-direction: column;
        align-items: stretch;
    }
    
    .countries-table {
        overflow-x: auto;
    }
    
    .form-row {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
// Search functionality
document.getElementById('searchInput').addEventListener('input', function() {
    const query = this.value.toLowerCase();
    const rows = document.querySelectorAll('tbody tr');
    
    rows.forEach(row => {
        const countryName = row.querySelector('.country-info strong').textContent.toLowerCase();
        if (countryName.includes(query)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
});

function showAddModal() {
    document.getElementById('modalTitle').textContent = 'Add Country';
    document.getElementById('countryForm').reset();
    document.getElementById('country_id').value = '';
    document.getElementById('countryModal').classList.add('show');
}

function editCountry(id) {
    fetch(`ajax/get_country.php?id=${id}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('modalTitle').textContent = 'Edit Country';
                document.getElementById('country_id').value = data.country.id;
                document.getElementById('name').value = data.country.name;
                document.getElementById('iso_code_2').value = data.country.iso_code_2;
                document.getElementById('iso_code_3').value = data.country.iso_code_3;
                document.getElementById('phone_code').value = data.country.phone_code || '';
                document.getElementById('currency_code').value = data.country.currency_code || '';
                document.getElementById('region').value = data.country.region || '';
                document.getElementById('visa_required_for_rwanda').value = data.country.visa_required_for_rwanda;
                document.getElementById('is_active').value = data.country.is_active;
                document.getElementById('countryModal').classList.add('show');
            }
        });
}

function closeModal() {
    document.getElementById('countryModal').classList.remove('show');
}

function saveCountry(event) {
    event.preventDefault();
    const formData = new FormData(document.getElementById('countryForm'));
    
    fetch('ajax/save_country.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Error: ' + data.error);
        }
    });
}

function deleteCountry(id) {
    if (confirm('Are you sure you want to delete this country?')) {
        fetch('ajax/delete_country.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({id: id})
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error: ' + data.error);
            }
        });
    }
}
</script>

<?php include 'includes/footer.php'; ?>