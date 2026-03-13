<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit;
}

require_once '../config/database.php';

// Pagination and search parameters
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limit = 12;
$offset = ($page - 1) * $limit;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$status = isset($_GET['status']) ? $_GET['status'] : 'all';

try {
    $pdo = getConnection();
    
    // Check if users table exists
    $tableExists = false;
    try {
        $pdo->query("SELECT 1 FROM users LIMIT 1");
        $tableExists = true;
    } catch (Exception $e) {
        // Table doesn't exist, create empty arrays
        $users = [];
        $totalUsers = 0;
        $totalPages = 0;
        $totalUsersCount = 0;
        $newTodayCount = 0;
        $activeUsersCount = 0;
    }
    
    if ($tableExists) {
    
    // Build WHERE clause for search and filters
    $whereConditions = [];
    $params = [];
    
    if (!empty($search)) {
        $whereConditions[] = "(CONCAT(u.first_name, ' ', u.last_name) LIKE ? OR u.email LIKE ? OR u.country LIKE ?)";
        $searchParam = "%$search%";
        $params[] = $searchParam;
        $params[] = $searchParam;
        $params[] = $searchParam;
    }
    
    if ($status !== 'all') {
        $whereConditions[] = "u.is_active = ?";
        $params[] = ($status === 'active') ? 1 : 0;
    }
    
    $whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';
    
    // Get total count for pagination
    $countQuery = "SELECT COUNT(*) FROM users u $whereClause";
    $stmt = $pdo->prepare($countQuery);
    $stmt->execute($params);
    $totalUsers = $stmt->fetchColumn();
    $totalPages = ceil($totalUsers / $limit);
    
    // Get users with pagination
    $query = "
        SELECT u.*, 
               CONCAT(u.first_name, ' ', u.last_name) as full_name,
               COUNT(DISTINCT a.id) as application_count,
               COALESCE(SUM(CASE WHEN p.payment_status IN ('completed', 'approved', 'paid') THEN p.amount ELSE 0 END), 0) as total_paid,
               COALESCE(MAX(p.currency), 'RWF') as currency
        FROM users u
        LEFT JOIN applications a ON u.id = a.user_id
        LEFT JOIN payments p ON a.id = p.application_id
        $whereClause
        GROUP BY u.id
        ORDER BY u.created_at DESC
        LIMIT $limit OFFSET $offset
    ";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $users = $stmt->fetchAll();
    
    // Get stats
    $totalUsersCount = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    $newTodayCount = $pdo->query("SELECT COUNT(*) FROM users WHERE DATE(created_at) = CURDATE()")->fetchColumn();
    $activeUsersCount = $pdo->query("SELECT COUNT(*) FROM users WHERE is_active = 1")->fetchColumn();
    
    } // End of tableExists check
    
} catch (Exception $e) {
    $users = [];
    $totalUsers = 0;
    $totalPages = 0;
    $totalUsersCount = 0;
    $newTodayCount = 0;
    $activeUsersCount = 0;
}

$pageTitle = 'Users';
?>

<?php include 'includes/header.php'; ?>
<?php include 'includes/sidebar.php'; ?>

<main class="main-content">
    <!-- Header Section -->
    <div class="content-header">
        <h1 class="page-title">User Management</h1>
        <p class="page-subtitle">Manage registered users and their accounts with advanced controls</p>
    </div>
    
    <!-- Stats Dashboard -->
    <div class="stats-row">
        <div class="stat-card">
            <div class="stat-icon total">
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-info">
                <div class="stat-number"><?= $totalUsersCount ?></div>
                <div class="stat-label">Total Users</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon new">
                <i class="fas fa-user-plus"></i>
            </div>
            <div class="stat-info">
                <div class="stat-number"><?= $newTodayCount ?></div>
                <div class="stat-label">New Today</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon active">
                <i class="fas fa-user-check"></i>
            </div>
            <div class="stat-info">
                <div class="stat-number"><?= $activeUsersCount ?></div>
                <div class="stat-label">Active Users</div>
            </div>
        </div>
    </div>
    
    <!-- Control Panel -->
    <div class="control-panel">
        <div class="page-actions">
            <div class="filters">
                <div class="search-container">
                    <input type="text" id="searchInput" placeholder="🔍 Search users by name or email..." class="search-input" autocomplete="off">
                </div>
                <select id="statusFilter" class="filter-select" onchange="filterUsers()">
                    <option value="all" <?= $status === 'all' ? 'selected' : '' ?>>📊 All Users</option>
                    <option value="active" <?= $status === 'active' ? 'selected' : '' ?>>✅ Active Only</option>
                    <option value="inactive" <?= $status === 'inactive' ? 'selected' : '' ?>>❌ Inactive Only</option>
                </select>
            </div>
            <div class="actions">
                <button class="btn btn-success" onclick="showAddUserModal()">
                    <i class="fas fa-plus"></i>
                    Add New User
                </button>
                <button class="btn btn-primary" onclick="downloadUsersPDF()">
                    <i class="fas fa-download"></i>
                    Download PDF
                </button>
            </div>
        </div>
    </div>
    
    <!-- Users List -->
    <div class="users-container">
        <?php if (empty($users)): ?>
            <div class="empty-state">
                <i class="fas fa-users"></i>
                <h3>No Users Found</h3>
                <p>Registered users will appear here</p>
            </div>
        <?php else: ?>
            <div class="users-table">
                <div class="table-header">
                    <div>User</div>
                    <div>Destination</div>
                    <div>Apps</div>
                    <div>Status</div>
                    <div>Actions</div>
                </div>
                <?php foreach ($users as $user): ?>
                    <div class="table-row">
                        <div class="user-cell">
                            <div class="avatar" onclick="viewUser(<?= $user['id'] ?>)" style="cursor: pointer;" title="Click to view profile">
                                <?php if (!empty($user['profile_picture']) && file_exists("../uploads/profiles/" . $user['profile_picture'])): ?>
                                    <img src="../uploads/profiles/<?= htmlspecialchars($user['profile_picture']) ?>" alt="Profile">
                                <?php else: ?>
                                    <?= strtoupper(substr($user['full_name'], 0, 1)) ?>
                                <?php endif; ?>
                            </div>
                            <div class="user-info">
                                <div class="user-name"><?= htmlspecialchars($user['full_name']) ?></div>
                                <div class="user-email"><?= htmlspecialchars($user['email']) ?></div>
                            </div>
                        </div>
                        <div class="country-cell"><?= htmlspecialchars($user['country']) ?></div>
                        <div class="apps-cell"><?= $user['application_count'] ?></div>
                        <div class="status-cell">
                            <span class="status <?= $user['is_active'] ? 'active' : 'inactive' ?>">
                                <?= $user['is_active'] ? 'Active' : 'Inactive' ?>
                            </span>
                        </div>
                        <div class="action-cell">
                            <button class="btn-view" onclick="viewUser(<?= $user['id'] ?>)" title="View">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button class="btn-edit" onclick="editUser(<?= $user['id'] ?>)" title="Edit">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn-delete" onclick="deleteUser(<?= $user['id'] ?>)" title="Delete">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
        <div class="pagination">
            <div class="pagination-info">
                📄 Showing <?= ($offset + 1) ?> to <?= min($offset + $limit, $totalUsers) ?> of <?= $totalUsers ?> users
            </div>
            <div class="pagination-controls">
                <?php if ($page > 1): ?>
                    <a href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>&status=<?= $status ?>" class="pagination-btn">
                        <i class="fas fa-chevron-left"></i>
                    </a>
                <?php endif; ?>
                
                <?php
                $startPage = max(1, $page - 2);
                $endPage = min($totalPages, $page + 2);
                
                for ($i = $startPage; $i <= $endPage; $i++):
                ?>
                    <a href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&status=<?= $status ?>" 
                       class="pagination-btn <?= $i === $page ? 'active' : '' ?>">
                        <?= $i ?>
                    </a>
                <?php endfor; ?>
                
                <?php if ($page < $totalPages): ?>
                    <a href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>&status=<?= $status ?>" class="pagination-btn">
                        <i class="fas fa-chevron-right"></i>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</main>

<!-- Add User Modal -->
<div id="addUserModal" class="modal-overlay">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Add New User</h3>
            <button class="modal-close" onclick="closeAddUserModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body">
            <form id="addUserForm" onsubmit="addUser(event)" enctype="multipart/form-data">
                <!-- Profile Image Section -->
                <div class="form-group">
                    <label>Profile Picture</label>
                    <div class="profile-upload-section">
                        <div class="current-profile">
                            <div id="add_profile_preview" class="profile-preview">
                                <i class="fas fa-user"></i>
                            </div>
                        </div>
                        <div class="upload-controls">
                            <input type="file" id="add_profile_input" name="profile_image" accept="image/*" class="file-input">
                            <label for="add_profile_input" class="upload-btn">
                                <i class="fas fa-camera"></i> Choose Photo
                            </label>
                        </div>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="first_name">First Name *</label>
                        <input type="text" id="first_name" name="first_name" required class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="last_name">Last Name *</label>
                        <input type="text" id="last_name" name="last_name" required class="form-control">
                    </div>
                </div>
                <div class="form-group">
                    <label for="email">Email *</label>
                    <input type="email" id="email" name="email" required class="form-control">
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="phone">Phone</label>
                        <input type="tel" id="phone" name="phone" class="form-control" placeholder="+250788123456">
                    </div>
                    <div class="form-group">
                        <label for="nationality">Nationality</label>
                        <select id="nationality" name="nationality" class="form-control">
                            <option value="">Select Nationality...</option>
                            <?php
                            try {
                                $countries = $pdo->query("SELECT name FROM countries WHERE is_active = 1 ORDER BY name")->fetchAll();
                                foreach ($countries as $country):
                            ?>
                                <option value="<?= htmlspecialchars($country['name']) ?>"><?= htmlspecialchars($country['name']) ?></option>
                            <?php endforeach; } catch (Exception $e) {} ?>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="country">Country of Destination</label>
                        <select id="country" name="country" class="form-control">
                            <option value="">Select Destination...</option>
                            <?php
                            try {
                                $destinations = $pdo->query("
                                    SELECT c.name 
                                    FROM destination_countries dc 
                                    JOIN countries c ON dc.country_id = c.id 
                                    WHERE dc.is_featured = 1 
                                    ORDER BY c.name
                                ")->fetchAll();
                                foreach ($destinations as $destination):
                            ?>
                                <option value="<?= htmlspecialchars($destination['name']) ?>"><?= htmlspecialchars($destination['name']) ?></option>
                            <?php endforeach; } catch (Exception $e) {} ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="gender">Gender</label>
                        <select id="gender" name="gender" class="form-control">
                            <option value="">Select Gender...</option>
                            <option value="male">Male</option>
                            <option value="female">Female</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="date_of_birth">Date of Birth</label>
                        <input type="date" id="date_of_birth" name="date_of_birth" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="passport_number">Passport Number</label>
                        <input type="text" id="passport_number" name="passport_number" class="form-control" placeholder="Optional">
                    </div>
                </div>
                <div class="form-group">
                    <label for="address">Address</label>
                    <textarea id="address" name="address" class="form-control" rows="2" placeholder="Full address (optional)"></textarea>
                </div>
                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeAddUserModal()">Cancel</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-plus"></i>
                        Add User
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit User Modal -->
<div id="editUserModal" class="modal-overlay">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Edit User</h3>
            <button class="modal-close" onclick="closeEditUserModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body">
            <form id="editUserForm" onsubmit="updateUser(event)" enctype="multipart/form-data">
                <input type="hidden" id="edit_user_id" name="user_id">
                
                <!-- Profile Image Section -->
                <div class="form-group">
                    <label>Profile Picture</label>
                    <div class="profile-upload-section">
                        <div class="current-profile">
                            <div id="current_profile_image" class="profile-preview">
                                <i class="fas fa-user"></i>
                            </div>
                        </div>
                        <div class="upload-controls">
                            <input type="file" id="profile_image" name="profile_image" accept="image/*" class="file-input">
                            <label for="profile_image" class="upload-btn">
                                <i class="fas fa-camera"></i> Change Photo
                            </label>
                            <button type="button" class="remove-btn" onclick="removeProfileImage()">
                                <i class="fas fa-trash"></i> Remove
                            </button>
                        </div>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="edit_first_name">First Name *</label>
                        <input type="text" id="edit_first_name" name="first_name" required class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="edit_last_name">Last Name *</label>
                        <input type="text" id="edit_last_name" name="last_name" required class="form-control">
                    </div>
                </div>
                <div class="form-group">
                    <label for="edit_email">Email *</label>
                    <input type="email" id="edit_email" name="email" required class="form-control">
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="edit_phone">Phone</label>
                        <input type="tel" id="edit_phone" name="phone" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="edit_country">Country of Destination</label>
                        <select id="edit_country" name="country" class="form-control">
                            <option value="">Select Destination...</option>
                            <?php
                            try {
                                $destinations = $pdo->query("
                                    SELECT c.name 
                                    FROM destination_countries dc 
                                    JOIN countries c ON dc.country_id = c.id 
                                    WHERE dc.is_featured = 1 
                                    ORDER BY c.name
                                ")->fetchAll();
                                foreach ($destinations as $destination):
                            ?>
                                <option value="<?= htmlspecialchars($destination['name']) ?>"><?= htmlspecialchars($destination['name']) ?></option>
                            <?php endforeach; } catch (Exception $e) {} ?>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label for="edit_date_of_birth">Date of Birth</label>
                    <input type="date" id="edit_date_of_birth" name="date_of_birth" class="form-control">
                </div>
                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeEditUserModal()">Cancel</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save"></i>
                        Update User
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Confirmation Modal -->
<div id="confirmModal" class="modal-overlay">
    <div class="modal-content" style="max-width: 400px;">
        <div class="modal-header">
            <h3 id="confirmTitle">Confirm Action</h3>
            <button class="modal-close" onclick="closeConfirmModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body">
            <div id="confirmMessage" style="margin-bottom: 1.5rem; text-align: center;"></div>
            <div class="form-actions">
                <button type="button" class="btn btn-secondary" onclick="closeConfirmModal()">Cancel</button>
                <button type="button" id="confirmBtn" class="btn btn-danger">
                    <i class="fas fa-check"></i>
                    Confirm
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Success Modal -->
<div id="successModal" class="modal-overlay">
    <div class="modal-content" style="max-width: 400px;">
        <div class="modal-header" style="background: linear-gradient(135deg, #10b981, #059669);">
            <h3>Success</h3>
            <button class="modal-close" onclick="closeSuccessModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body">
            <div style="text-align: center; padding: 1rem;">
                <i class="fas fa-check-circle" style="font-size: 3rem; color: #10b981; margin-bottom: 1rem;"></i>
                <div id="successMessage" style="font-size: 1.1rem; color: #374151;"></div>
            </div>
            <div class="form-actions">
                <button type="button" class="btn btn-success" onclick="closeSuccessModal()" style="width: 100%;">
                    OK
                </button>
            </div>
        </div>
    </div>
</div>
<div id="userModal" class="modal-overlay">
    <div class="modal-content large">
        <div class="modal-header">
            <h3>User Profile Details</h3>
            <button class="modal-close" onclick="closeUserModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body" id="userModalBody">
            <!-- User details will be loaded here -->
        </div>
    </div>
</div>

<style>
/* Modern Dashboard Layout */
.main-content {
    margin-left: 260px;
    margin-top: 70px;
    padding: 2rem;
    min-height: calc(100vh - 70px);
    background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 50%, #f1f5f9 100%);
}

/* Header Section */
.content-header {
    background: linear-gradient(135deg, #ffffff, #f8fafc);
    border-radius: 20px;
    padding: 2rem;
    margin-bottom: 2rem;
    box-shadow: 0 4px 20px rgba(59, 130, 246, 0.08);
    border: 1px solid rgba(255, 255, 255, 0.5);
}

.page-title {
    font-size: 2.5rem;
    font-weight: 900;
    color: #1e293b;
    margin-bottom: 0.5rem;
    background: linear-gradient(135deg, #3b82f6, #1d4ed8);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.page-subtitle {
    color: #64748b;
    font-size: 1.2rem;
    font-weight: 500;
}

/* Stats Dashboard */
.stats-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 1rem;
    margin-bottom: 2rem;
}

.stat-card {
    background: white;
    border-radius: 8px;
    padding: 1rem;
    display: flex;
    align-items: center;
    gap: 0.75rem;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    border: 1px solid #e5e7eb;
    transition: all 0.2s ease;
}

.stat-card:hover {
    transform: translateY(-1px);
    box-shadow: 0 2px 8px rgba(0,0,0,0.15);
}

.stat-icon {
    width: 36px;
    height: 36px;
    border-radius: 6px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1rem;
    flex-shrink: 0;
}

.stat-icon.total {
    background: #3b82f6;
}

.stat-icon.new {
    background: #10b981;
}

.stat-icon.active {
    background: #8b5cf6;
}

.stat-info {
    flex: 1;
}

.stat-number {
    font-size: 1.5rem;
    font-weight: 700;
    color: #111827;
    margin-bottom: 0.125rem;
    line-height: 1;
}

.stat-label {
    color: #6b7280;
    font-size: 0.75rem;
    font-weight: 500;
}

/* Control Panel */
.control-panel {
    background: linear-gradient(135deg, #ffffff, #f8fafc);
    border-radius: 20px;
    padding: 2rem;
    margin-bottom: 2rem;
    box-shadow: 0 8px 25px rgba(59, 130, 246, 0.1);
    border: 1px solid rgba(255, 255, 255, 0.5);
}

.page-actions {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 2rem;
    flex-wrap: wrap;
}

.filters {
    display: flex;
    gap: 1.5rem;
    align-items: center;
    flex: 1;
}

.search-container {
    position: relative;
    flex: 1;
    max-width: 400px;
}

.search-input {
    width: 100%;
    padding: 1rem 1.5rem;
    border: 2px solid rgba(59, 130, 246, 0.1);
    border-radius: 15px;
    background: rgba(255, 255, 255, 0.9);
    color: #1e293b;
    font-size: 1rem;
    font-weight: 500;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(59, 130, 246, 0.05);
}

.search-input:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1), 0 8px 25px rgba(59, 130, 246, 0.15);
    transform: translateY(-2px);
}

.search-input::placeholder {
    color: #94a3b8;
}

.filter-select {
    padding: 1rem 1.5rem;
    border: 2px solid rgba(59, 130, 246, 0.1);
    border-radius: 15px;
    background: rgba(255, 255, 255, 0.9);
    color: #1e293b;
    font-size: 1rem;
    font-weight: 500;
    cursor: pointer;
    min-width: 180px;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(59, 130, 246, 0.05);
}

.filter-select:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
    transform: translateY(-2px);
}

.actions {
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
}

.btn {
    padding: 1rem 2rem;
    border: none;
    border-radius: 15px;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 0.75rem;
    text-decoration: none;
    position: relative;
    overflow: hidden;
}

.btn::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
    transition: left 0.5s;
}

.btn:hover::before {
    left: 100%;
}

.btn-success {
    background: linear-gradient(135deg, #10b981, #059669);
    color: white;
    box-shadow: 0 8px 25px rgba(16, 185, 129, 0.3);
}

.btn-success:hover {
    transform: translateY(-3px);
    box-shadow: 0 12px 35px rgba(16, 185, 129, 0.4);
}

.btn-primary {
    background: linear-gradient(135deg, #3b82f6, #1d4ed8);
    color: white;
    box-shadow: 0 8px 25px rgba(59, 130, 246, 0.3);
}

.btn-primary:hover {
    transform: translateY(-3px);
    box-shadow: 0 12px 35px rgba(59, 130, 246, 0.4);
}

.btn-secondary {
    background: linear-gradient(135deg, #64748b, #475569);
    color: white;
    box-shadow: 0 8px 25px rgba(100, 116, 139, 0.3);
}

.btn-secondary:hover {
    transform: translateY(-3px);
    box-shadow: 0 12px 35px rgba(100, 116, 139, 0.4);
}

/* Users Table */
.users-container {
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    border: 1px solid #e5e7eb;
    overflow: hidden;
    margin-bottom: 2rem;
}

.users-table {
    width: 100%;
}

.table-header {
    display: grid;
    grid-template-columns: 2fr 1fr 0.5fr 0.8fr 1fr;
    gap: 1rem;
    padding: 1rem 1.5rem;
    background: #f8fafc;
    border-bottom: 1px solid #e5e7eb;
    font-weight: 600;
    color: #374151;
    font-size: 0.875rem;
}

.table-row {
    display: grid;
    grid-template-columns: 2fr 1fr 0.5fr 0.8fr 1fr;
    gap: 1rem;
    padding: 1rem 1.5rem;
    border-bottom: 1px solid #f3f4f6;
    align-items: center;
    transition: background 0.2s;
}

.table-row:hover {
    background: #f9fafb;
}

.table-row:last-child {
    border-bottom: none;
}

.user-cell {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.avatar {
    width: 40px;
    height: 40px;
    border-radius: 8px;
    background: linear-gradient(135deg, #3b82f6, #1d4ed8);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: 600;
    font-size: 0.875rem;
    flex-shrink: 0;
}

.avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 8px;
}

.user-name {
    font-weight: 600;
    color: #111827;
    font-size: 0.875rem;
    margin-bottom: 0.125rem;
}

.user-email {
    font-size: 0.75rem;
    color: #6b7280;
}

.country-cell,
.apps-cell,
.paid-cell {
    font-size: 0.875rem;
    color: #374151;
}

.paid-cell {
    color: #059669;
    font-weight: 600;
}

.status {
    padding: 0.25rem 0.5rem;
    border-radius: 6px;
    font-size: 0.75rem;
    font-weight: 500;
}

.status.active {
    background: #d1fae5;
    color: #065f46;
}

.status.inactive {
    background: #fee2e2;
    color: #991b1b;
}

.btn-view,
.btn-edit,
.btn-delete {
    width: 28px;
    height: 28px;
    border-radius: 4px;
    border: none;
    color: white;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s;
    font-size: 0.7rem;
    margin-right: 0.25rem;
}

.btn-view {
    background: #3b82f6;
}

.btn-view:hover {
    background: #1d4ed8;
}

.btn-edit {
    background: #f59e0b;
}

.btn-edit:hover {
    background: #d97706;
}

.btn-delete {
    background: #ef4444;
}

.btn-delete:hover {
    background: #dc2626;
}

.empty-state {
    text-align: center;
    padding: 3rem 2rem;
    color: #6b7280;
}

.empty-state i {
    font-size: 3rem;
    margin-bottom: 1rem;
    color: #d1d5db;
}

.empty-state h3 {
    font-size: 1.25rem;
    margin-bottom: 0.5rem;
    color: #374151;
}

@media (max-width: 768px) {
    .main-content {
        margin-left: 0;
        padding: 1rem;
        margin-top: 60px;
    }
    
    .stats-row {
        grid-template-columns: repeat(3, 1fr);
        gap: 0.75rem;
        margin-bottom: 1.5rem;
    }
    
    .stat-card {
        padding: 0.75rem;
        gap: 0.5rem;
        flex-direction: column;
        text-align: center;
    }
    
    .stat-icon {
        width: 28px;
        height: 28px;
        font-size: 0.875rem;
    }
    
    .stat-number {
        font-size: 1.25rem;
    }
    
    .stat-label {
        font-size: 0.7rem;
    }
    
    .table-header {
        display: none;
    }
    
    .table-row {
        grid-template-columns: 1fr;
        gap: 0.5rem;
        padding: 1rem;
        border-radius: 8px;
        margin-bottom: 0.75rem;
        background: white;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }
    
    .user-cell {
        grid-column: 1;
        padding-bottom: 0.5rem;
        border-bottom: 1px solid #f3f4f6;
    }
    
    .country-cell,
    .apps-cell,
    .status-cell {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.375rem 0;
        font-size: 0.8rem;
    }
    
    .country-cell::before { content: 'Destination'; font-weight: 600; color: #6b7280; }
    .apps-cell::before { content: 'Apps'; font-weight: 600; color: #6b7280; }
    .status-cell::before { content: 'Status'; font-weight: 600; color: #6b7280; }
    
    .action-cell {
        text-align: right;
        padding-top: 0.5rem;
        border-top: 1px solid #f3f4f6;
    }
}

/* Search Results Dropdown */
.search-results {
    position: absolute;
    top: calc(100% + 8px);
    left: 0;
    right: 0;
    background: white;
    border: 2px solid rgba(59, 130, 246, 0.1);
    border-radius: 15px;
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
    max-height: 400px;
    overflow-y: auto;
    z-index: 1000;
    display: none;
}

.search-results.show {
    display: block;
    animation: slideDown 0.3s ease;
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

.search-result-item {
    padding: 1rem 1.5rem;
    border-bottom: 1px solid #f1f5f9;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 1rem;
    transition: all 0.2s ease;
}

.search-result-item:hover {
    background: linear-gradient(135deg, #f8fafc, #f1f5f9);
}

.search-result-item:last-child {
    border-bottom: none;
}

.search-result-avatar {
    width: 45px;
    height: 45px;
    border-radius: 12px;
    background: linear-gradient(135deg, #3b82f6, #1d4ed8);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: 700;
    font-size: 1rem;
    flex-shrink: 0;
}

.search-result-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 12px;
}

.search-result-info {
    flex: 1;
}

.search-result-info h4 {
    margin: 0 0 0.25rem 0;
    font-size: 1rem;
    font-weight: 600;
    color: #1e293b;
}

.search-result-info p {
    margin: 0;
    font-size: 0.875rem;
    color: #64748b;
}

/* Pagination */
.pagination {
    background: linear-gradient(135deg, #ffffff, #f8fafc);
    border-radius: 20px;
    padding: 2rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    box-shadow: 0 8px 25px rgba(59, 130, 246, 0.1);
    border: 1px solid rgba(255, 255, 255, 0.5);
}

.pagination-info {
    color: #64748b;
    font-size: 1rem;
    font-weight: 500;
}

.pagination-controls {
    display: flex;
    gap: 0.5rem;
    align-items: center;
}

.pagination-btn {
    padding: 0.75rem 1rem;
    border: 2px solid rgba(59, 130, 246, 0.1);
    border-radius: 12px;
    background: rgba(255, 255, 255, 0.9);
    color: #64748b;
    text-decoration: none;
    font-size: 0.9rem;
    font-weight: 600;
    transition: all 0.3s ease;
    min-width: 45px;
    text-align: center;
    display: flex;
    align-items: center;
    justify-content: center;
}

.pagination-btn:hover {
    background: #3b82f6;
    color: white;
    border-color: #3b82f6;
    text-decoration: none;
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(59, 130, 246, 0.3);
}

.pagination-btn.active {
    background: linear-gradient(135deg, #3b82f6, #1d4ed8);
    color: white;
    border-color: #3b82f6;
    box-shadow: 0 8px 25px rgba(59, 130, 246, 0.3);
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: 4rem 2rem;
    background: white;
    border-radius: 16px;
    border: 2px dashed rgba(59, 130, 246, 0.2);
    margin-bottom: 2rem;
}

.empty-state i {
    font-size: 4rem;
    margin-bottom: 1.5rem;
    display: block;
    color: #94a3b8;
}

.empty-state h3 {
    font-size: 1.8rem;
    margin-bottom: 0.5rem;
    color: #1e293b;
    font-weight: 700;
}

.empty-state p {
    color: #64748b;
    font-size: 1.1rem;
}

@media (max-width: 768px) {
    .main-content {
        margin-left: 0;
        padding: 1rem;
        margin-top: 60px;
    }
    
    .list-header {
        display: none;
    }
    
    .user-row {
        grid-template-columns: 1fr;
        gap: 1rem;
        padding: 1rem;
        border-radius: 12px;
        margin-bottom: 1rem;
        background: white;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }
    
    .user-info {
        grid-column: 1;
    }
    
    .user-country,
    .user-applications,
    .user-payment,
    .user-status {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.5rem 0;
        border-top: 1px solid #f1f5f9;
    }
    
    .user-country::before { content: 'Country: '; font-weight: 600; }
    .user-applications::before { content: 'Applications: '; font-weight: 600; }
    .user-payment::before { content: 'Total Paid: '; font-weight: 600; }
    .user-status::before { content: 'Status: '; font-weight: 600; }
    
    .user-actions {
        justify-self: end;
        grid-column: 1;
    }
}

/* Modal Styles */
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
    z-index: 1000;
    overflow-y: auto;
    padding: 2rem;
}

.modal-overlay.show {
    display: flex;
    animation: fadeIn 0.3s ease;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

.modal-content {
    background: white;
    border-radius: 24px;
    box-shadow: 0 25px 50px rgba(0, 0, 0, 0.25);
    max-width: 600px;
    width: 100%;
    max-height: 90vh;
    overflow: hidden;
    animation: slideUp 0.3s ease;
}

@keyframes slideUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.modal-content.large {
    max-width: 800px;
}

.modal-header {
    padding: 2rem;
    background: linear-gradient(135deg, #3b82f6, #1d4ed8);
    color: white;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.modal-header h3 {
    margin: 0;
    font-size: 1.5rem;
    font-weight: 700;
}

.modal-close {
    background: rgba(255, 255, 255, 0.2);
    border: none;
    color: white;
    cursor: pointer;
    padding: 0.75rem;
    border-radius: 12px;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 45px;
    height: 45px;
    font-size: 1.2rem;
}

.modal-close:hover {
    background: rgba(255, 255, 255, 0.3);
    transform: scale(1.1);
}

.modal-body {
    padding: 2rem;
    max-height: 70vh;
    overflow-y: auto;
}

/* Form Styles */
.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1.5rem;
    margin-bottom: 1.5rem;
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-group label {
    display: block;
    margin-bottom: 0.75rem;
    color: #374151;
    font-weight: 600;
    font-size: 1rem;
}

.form-control {
    width: 100%;
    padding: 1rem;
    border: 2px solid rgba(59, 130, 246, 0.1);
    border-radius: 12px;
    font-size: 1rem;
    transition: all 0.3s ease;
    background: rgba(255, 255, 255, 0.9);
}

.form-control:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
    transform: translateY(-2px);
}

.form-actions {
    display: flex;
    gap: 1rem;
    justify-content: flex-end;
    margin-top: 2rem;
    padding-top: 2rem;
    border-top: 2px solid rgba(59, 130, 246, 0.1);
}

/* Loading States */
.loading {
    text-align: center;
    padding: 3rem;
    color: #64748b;
    font-size: 1.1rem;
}

.loading i {
    font-size: 2rem;
    margin-bottom: 1rem;
    display: block;
    color: #3b82f6;
}

.error {
    text-align: center;
    padding: 3rem;
    color: #dc2626;
    font-size: 1.1rem;
    background: rgba(239, 68, 68, 0.05);
    border-radius: 12px;
    border: 1px solid rgba(239, 68, 68, 0.2);
}

/* Profile Upload Section */
.profile-upload-section {
    display: flex;
    align-items: center;
    gap: 1.5rem;
    padding: 1rem;
    background: #f8fafc;
    border-radius: 12px;
    border: 2px dashed rgba(59, 130, 246, 0.2);
}

.current-profile {
    flex-shrink: 0;
}

.profile-preview {
    width: 80px;
    height: 80px;
    border-radius: 12px;
    background: linear-gradient(135deg, #3b82f6, #1d4ed8);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 2rem;
    overflow: hidden;
}

.profile-preview img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 12px;
}

.upload-controls {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.file-input {
    display: none;
}

.upload-btn {
    background: linear-gradient(135deg, #10b981, #059669);
    color: white;
    padding: 0.75rem 1.5rem;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 500;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.875rem;
    border: none;
}

.upload-btn:hover {
    background: linear-gradient(135deg, #059669, #047857);
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(16, 185, 129, 0.3);
}

.remove-btn {
    background: linear-gradient(135deg, #ef4444, #dc2626);
    color: white;
    padding: 0.5rem 1rem;
    border-radius: 6px;
    cursor: pointer;
    font-weight: 500;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.75rem;
    border: none;
}

.remove-btn:hover {
    background: linear-gradient(135deg, #dc2626, #b91c1c);
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(239, 68, 68, 0.3);
}

/* Responsive Design */
@media (max-width: 1200px) {
    .users-grid {
        grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
        gap: 1.5rem;
    }
}

/* Notification Styles */
.notification {
    position: fixed;
    top: 20px;
    right: 20px;
    background: white;
    border-radius: 12px;
    padding: 1rem 1.5rem;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
    display: flex;
    align-items: center;
    gap: 0.75rem;
    z-index: 1000;
    transform: translateX(400px);
    opacity: 0;
    transition: all 0.3s ease;
    min-width: 300px;
}

.notification.show {
    transform: translateX(0);
    opacity: 1;
}

.notification.success {
    border-left: 4px solid #10b981;
    color: #065f46;
}

.notification.success i {
    color: #10b981;
}

.notification.error {
    border-left: 4px solid #ef4444;
    color: #991b1b;
}

.notification.error i {
    color: #ef4444;
}


}

@media (max-width: 1024px) {
    .main-content {
        padding: 1.5rem;
    }
    
    .users-grid {
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 1.25rem;
    }
    
    .user-meta {
        grid-template-columns: 1fr;
        gap: 1rem;
    }
    
    .stat-card {
        padding: 1.5rem;
    }
}

@media (max-width: 768px) {
    .main-content {
        margin-left: 0;
        padding: 1rem;
        margin-top: 60px;
    }
    
    .content-header {
        padding: 1.5rem;
        text-align: center;
        margin-bottom: 1.5rem;
    }
    
    .page-title {
        font-size: 2rem;
        margin-bottom: 0.5rem;
    }
    
    .page-subtitle {
        font-size: 1rem;
    }
    
    .stats-row {
        grid-template-columns: 1fr;
        gap: 1rem;
        margin-bottom: 1.5rem;
    }
    
    .stat-card {
        padding: 1.25rem;
        gap: 1rem;
    }
    
    .stat-icon {
        width: 50px;
        height: 50px;
        font-size: 1.25rem;
    }
    
    .stat-number {
        font-size: 1.75rem;
    }
    
    .stat-label {
        font-size: 0.85rem;
    }
    
    .control-panel {
        padding: 1.5rem;
        margin-bottom: 1.5rem;
    }
    
    .page-actions {
        flex-direction: column;
        gap: 1.5rem;
        align-items: stretch;
    }
    
    .filters {
        flex-direction: column;
        gap: 1rem;
        align-items: stretch;
    }
    
    .search-container {
        max-width: none;
        width: 100%;
    }
    
    .search-input {
        padding: 0.875rem 1rem;
        font-size: 0.9rem;
    }
    
    .filter-select {
        padding: 0.875rem 1rem;
        font-size: 0.9rem;
        min-width: auto;
    }
    
    .actions {
        width: 100%;
    }
    
    .btn {
        width: 100%;
        justify-content: center;
        padding: 0.875rem 1.5rem;
        font-size: 0.9rem;
    }
    
    .users-grid {
        grid-template-columns: 1fr;
        gap: 1.25rem;
    }
    
    .user-card {
        margin: 0;
        border-radius: 16px;
    }
    
    .card-header {
        padding: 1.25rem;
    }
    
    .user-info {
        gap: 1rem;
    }
    
    .user-avatar {
        width: 55px;
        height: 55px;
        font-size: 1.4rem;
        border-radius: 14px;
    }
    
    .user-name {
        font-size: 1.1rem;
    }
    
    .user-email {
        font-size: 0.85rem;
    }
    
    .card-content {
        padding: 1.25rem;
    }
    
    .user-meta {
        grid-template-columns: 1fr;
        gap: 0.875rem;
    }
    
    .info-item {
        padding: 0.75rem;
        border-radius: 8px;
    }
    
    .info-label {
        font-size: 0.8rem;
        margin-bottom: 0.375rem;
    }
    
    .info-value {
        font-size: 0.9rem;
    }
    
    .card-actions {
        padding: 1rem 1.25rem;
    }
    
    .card-actions .btn {
        padding: 0.75rem;
        font-size: 0.85rem;
    }
    
    .pagination {
        flex-direction: column;
        gap: 1.25rem;
        text-align: center;
        padding: 1.25rem;
    }
    
    .pagination-info {
        font-size: 0.85rem;
    }
    
    .pagination-controls {
        justify-content: center;
        flex-wrap: wrap;
        gap: 0.375rem;
    }
    
    .pagination-btn {
        padding: 0.625rem 0.75rem;
        font-size: 0.8rem;
        min-width: 35px;
    }
    
    .form-row {
        grid-template-columns: 1fr;
        gap: 1rem;
    }
    
    .modal-overlay {
        padding: 1rem;
    }
    
    .modal-header {
        padding: 1.25rem;
    }
    
    .modal-header h3 {
        font-size: 1.2rem;
    }
    
    .modal-body {
        padding: 1.25rem;
    }
    
    .form-control {
        padding: 0.75rem;
        font-size: 0.9rem;
    }
    
    .form-actions {
        flex-direction: column;
        gap: 0.75rem;
    }
    
    .form-actions .btn {
        width: 100%;
        padding: 0.875rem;
    }
}

@media (max-width: 480px) {
    .main-content {
        padding: 0.75rem;
    }
    
<script>
// Search functionality
const searchInput = document.getElementById('searchInput');
let searchTimeout;

if (searchInput) {
    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            const query = this.value.trim();
            if (query.length >= 2 || query.length === 0) {
                window.location.href = `?search=${encodeURIComponent(query)}&status=<?= $status ?>`;
            }
        }, 500);
    });
}

function filterUsers() {
    const status = document.getElementById('statusFilter').value;
    const search = document.getElementById('searchInput').value;
    window.location.href = `?search=${encodeURIComponent(search)}&status=${status}`;
}

// Modal functions
function showAddUserModal() {
    document.getElementById('addUserModal').classList.add('show');
}

function closeAddUserModal() {
    document.getElementById('addUserModal').classList.remove('show');
    document.getElementById('addUserForm').reset();
}

function showEditUserModal() {
    document.getElementById('editUserModal').classList.add('show');
}

function closeEditUserModal() {
    document.getElementById('editUserModal').classList.remove('show');
    document.getElementById('editUserForm').reset();
}

function closeConfirmModal() {
    document.getElementById('confirmModal').classList.remove('show');
}

function closeSuccessModal() {
    document.getElementById('successModal').classList.remove('show');
    location.reload();
}

function closeUserModal() {
    document.getElementById('userModal').classList.remove('show');
}

// Add user
function addUser(event) {
    event.preventDefault();
    
    const form = document.getElementById('addUserForm');
    const formData = new FormData(form);
    const submitBtn = form.querySelector('button[type="submit"]');
    
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Adding...';
    
    fetch('add-user.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            closeAddUserModal();
            document.getElementById('successMessage').textContent = `User created successfully! Temporary password: ${data.temp_password}`;
            document.getElementById('successModal').classList.add('show');
        } else {
            alert('Error: ' + (data.error || 'Failed to create user'));
        }
    })
    .catch(error => {
        alert('Error creating user');
    })
    .finally(() => {
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fas fa-plus"></i> Add User';
    });
}

// Edit user
function editUser(userId) {
    fetch(`get-user.php?id=${userId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const user = data.user;
                document.getElementById('edit_user_id').value = user.id;
                document.getElementById('edit_first_name').value = user.first_name;
                document.getElementById('edit_last_name').value = user.last_name;
                document.getElementById('edit_email').value = user.email;
                document.getElementById('edit_phone').value = user.phone || '';
                document.getElementById('edit_country').value = user.country || '';
                document.getElementById('edit_date_of_birth').value = user.date_of_birth || '';
                
                // Update profile image
                const profileImg = document.getElementById('current_profile_image');
                if (user.profile_picture) {
                    profileImg.innerHTML = `<img src="../uploads/profiles/${user.profile_picture}" alt="Profile">`;
                } else {
                    profileImg.innerHTML = '<i class="fas fa-user"></i>';
                }
                
                showEditUserModal();
            } else {
                alert('Error: ' + (data.error || 'Failed to load user'));
            }
        })
        .catch(error => {
            alert('Error loading user data');
        });
}

// Update user
function updateUser(event) {
    event.preventDefault();
    
    const form = document.getElementById('editUserForm');
    const formData = new FormData(form);
    const submitBtn = form.querySelector('button[type="submit"]');
    
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Updating...';
    
    fetch('update-user.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            closeEditUserModal();
            document.getElementById('successMessage').textContent = 'User updated successfully!';
            document.getElementById('successModal').classList.add('show');
        } else {
            alert('Error: ' + (data.error || 'Failed to update user'));
        }
    })
    .catch(error => {
        alert('Error updating user');
    })
    .finally(() => {
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fas fa-save"></i> Update User';
    });
}

// Delete user
function deleteUser(userId) {
    document.getElementById('confirmTitle').textContent = 'Delete User';
    document.getElementById('confirmMessage').textContent = 'Are you sure you want to delete this user? This will also delete all their applications and cannot be undone.';
    document.getElementById('confirmBtn').onclick = () => {
        fetch('delete-user.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({user_id: userId})
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                closeConfirmModal();
                document.getElementById('successMessage').textContent = 'User deleted successfully!';
                document.getElementById('successModal').classList.add('show');
            } else {
                alert('Error: ' + (data.error || 'Failed to delete user'));
            }
        })
        .catch(error => {
            alert('Error deleting user');
        });
    };
    document.getElementById('confirmModal').classList.add('show');
}

// View user details
function viewUser(userId) {
    fetch(`get-user.php?id=${userId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const user = data.user;
                const modalBody = document.getElementById('userModalBody');
                modalBody.innerHTML = `
                    <div style="display: flex; gap: 2rem; margin-bottom: 2rem;">
                        <div style="flex-shrink: 0;">
                            ${user.profile_picture ? 
                                `<img src="../uploads/profiles/${user.profile_picture}" style="width: 120px; height: 120px; border-radius: 12px; object-fit: cover;">` :
                                `<div style="width: 120px; height: 120px; border-radius: 12px; background: linear-gradient(135deg, #3b82f6, #1d4ed8); display: flex; align-items: center; justify-content: center; color: white; font-size: 3rem;">${user.first_name ? user.first_name.charAt(0) : 'U'}</div>`
                            }
                        </div>
                        <div style="flex: 1;">
                            <h2 style="margin: 0 0 0.5rem 0; color: #1e293b;">${user.first_name || ''} ${user.last_name || ''}</h2>
                            <p style="margin: 0 0 1rem 0; color: #64748b; font-size: 1.1rem;">${user.email || 'No email'}</p>
                            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                                <div><strong>Phone:</strong> ${user.phone || 'Not provided'}</div>
                                <div><strong>Nationality:</strong> ${user.nationality || 'Not provided'}</div>
                                <div><strong>Country:</strong> ${user.country || 'Not provided'}</div>
                                <div><strong>Gender:</strong> ${user.gender || 'Not provided'}</div>
                                <div><strong>Date of Birth:</strong> ${user.date_of_birth || 'Not provided'}</div>
                                <div><strong>Passport:</strong> ${user.passport_number || 'Not provided'}</div>
                                <div><strong>Status:</strong> <span style="color: ${user.is_active ? '#10b981' : '#ef4444'}">${user.is_active ? 'Active' : 'Inactive'}</span></div>
                                <div><strong>Applications:</strong> ${user.application_count || 0}</div>
                                <div><strong>Total Paid:</strong> $${parseFloat(user.total_paid || 0).toFixed(2)}</div>
                                <div><strong>Joined:</strong> ${user.created_at ? new Date(user.created_at).toLocaleDateString() : 'Unknown'}</div>
                                <div><strong>Email Verified:</strong> ${user.email_verified ? 'Yes' : 'No'}</div>
                                <div><strong>Last Login:</strong> ${user.last_login ? new Date(user.last_login).toLocaleDateString() : 'Never'}</div>
                            </div>
                            ${user.address ? `<div style="margin-top: 1rem;"><strong>Address:</strong><br>${user.address}</div>` : ''}
                        </div>
                    </div>
                `;
                document.getElementById('userModal').classList.add('show');
            } else {
                alert('Error: ' + (data.error || 'Failed to load user'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error loading user data');
        });
}

// Remove profile image
function removeProfileImage() {
    document.getElementById('profile_image').value = '';
    document.getElementById('current_profile_image').innerHTML = '<i class="fas fa-user"></i>';
}

// Download users PDF
function downloadUsersPDF() {
    window.open('export-users.php', '_blank');
}

// Close modals when clicking outside
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('modal-overlay')) {
        e.target.classList.remove('show');
    }
});
</script>
        margin-bottom: 1rem;
    }
    
    .page-title {
        font-size: 1.75rem;
    }
    
    .page-subtitle {
        font-size: 0.9rem;
    }
    
    .stats-row {
        gap: 0.75rem;
        margin-bottom: 1rem;
    }
    
    .stat-card {
        padding: 1rem;
        gap: 0.75rem;
        flex-direction: column;
        text-align: center;
    }
    
    .stat-icon {
        width: 45px;
        height: 45px;
        font-size: 1.1rem;
        margin: 0 auto;
    }
    
    .stat-number {
        font-size: 1.5rem;
    }
    
    .stat-label {
        font-size: 0.8rem;
    }
    
    .control-panel {
        padding: 1rem;
        margin-bottom: 1rem;
    }
    
    .search-input {
        padding: 0.75rem;
        font-size: 0.85rem;
    }
    
    .filter-select {
        padding: 0.75rem;
        font-size: 0.85rem;
    }
    
    .btn {
        padding: 0.75rem 1rem;
        font-size: 0.85rem;
    }
    
    .users-grid {
        gap: 1rem;
    }
    
    .user-card {
        border-radius: 12px;
    }
    
    .card-header {
        padding: 1rem;
    }
    
    .user-info {
        flex-direction: column;
        text-align: center;
        gap: 0.75rem;
    }
    
    .user-avatar {
        width: 50px;
        height: 50px;
        font-size: 1.2rem;
        border-radius: 12px;
        margin: 0 auto;
    }
    
    .user-name {
        font-size: 1rem;
        margin-bottom: 0.25rem;
    }
    
    .user-email {
        font-size: 0.8rem;
    }
    
    .card-content {
        padding: 1rem;
    }
    
    .user-meta {
        gap: 0.75rem;
    }
    
    .info-item {
        padding: 0.625rem;
        text-align: center;
        border-radius: 6px;
    }
    
    .info-label {
        font-size: 0.75rem;
        margin-bottom: 0.25rem;
    }
    
    .info-value {
        font-size: 0.85rem;
    }
    
    .card-actions {
        padding: 0.875rem 1rem;
    }
    
    .card-actions .btn {
        padding: 0.625rem;
        font-size: 0.8rem;
    }
    
    .pagination {
        padding: 1rem;
        gap: 1rem;
    }
    
    .pagination-info {
        font-size: 0.8rem;
    }
    
    .pagination-btn {
        padding: 0.5rem 0.625rem;
        font-size: 0.75rem;
        min-width: 32px;
    }
    
    .modal-content {
        margin: 0.5rem;
        border-radius: 16px;
    }
    
    .modal-header {
        padding: 1rem;
    }
    
    .modal-header h3 {
        font-size: 1.1rem;
    }
    
    .modal-close {
        width: 35px;
        height: 35px;
        font-size: 1rem;
    }
    
    .modal-body {
        padding: 1rem;
    }
    
    .form-control {
        padding: 0.625rem;
        font-size: 0.85rem;
    }
    
    .empty-state {
        padding: 2rem 1rem;
    }
    
    .empty-state i {
        font-size: 2.5rem;
        margin-bottom: 1rem;
    }
    
    .empty-state h3 {
        font-size: 1.4rem;
    }
    
    .empty-state p {
        font-size: 0.9rem;
    }
}

@media (max-width: 360px) {
    .main-content {
        padding: 0.5rem;
    }
    
    .content-header {
        padding: 0.75rem;
    }
    
    .page-title {
        font-size: 1.5rem;
    }
    
    .control-panel {
        padding: 0.75rem;
    }
    
    .stat-card {
        padding: 0.75rem;
    }
    
    .user-card {
        border-radius: 10px;
    }
    
    .card-header, .card-content, .card-actions {
        padding: 0.75rem;
    }
    
    .user-avatar {
        width: 45px;
        height: 45px;
        font-size: 1.1rem;
    }
    
    .info-item {
        padding: 0.5rem;
    }
}
</style>

<?php include 'includes/footer.php'; ?>

<script>
// Simple search functionality
const searchInput = document.getElementById('searchInput');
const tableRows = document.querySelectorAll('.table-row');

if (searchInput) {
    searchInput.addEventListener('input', function() {
        const query = this.value.toLowerCase().trim();
        
        tableRows.forEach(row => {
            const name = row.querySelector('.user-name')?.textContent?.toLowerCase() || '';
            const email = row.querySelector('.user-email')?.textContent?.toLowerCase() || '';
            
            if (name.includes(query) || email.includes(query)) {
                row.style.display = 'grid';
            } else {
                row.style.display = 'none';
            }
        });
    });
}

function addUser(event) {
    event.preventDefault();
    
    const form = document.getElementById('addUserForm');
    const formData = new FormData(form);
    
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Adding...';
    submitBtn.disabled = true;
    
    fetch('add-user.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            closeAddUserModal();
            showSuccess('User added successfully!');
            setTimeout(() => location.reload(), 1500);
        } else {
            showError(data.error || 'Failed to add user');
        }
    })
    .catch(error => {
        showError('Failed to add user');
    })
    .finally(() => {
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    });
}

function editUser(userId) {
    fetch(`get-user.php?id=${userId}`)
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const user = data.user;
            document.getElementById('edit_user_id').value = user.id;
            document.getElementById('edit_first_name').value = user.first_name || '';
            document.getElementById('edit_last_name').value = user.last_name || '';
            document.getElementById('edit_email').value = user.email || '';
            document.getElementById('edit_phone').value = user.phone || '';
            document.getElementById('edit_country').value = user.country || '';
            document.getElementById('edit_date_of_birth').value = user.date_of_birth || '';
            
            document.getElementById('editUserModal').classList.add('show');
        }
    });
}

function updateUser(event) {
    event.preventDefault();
    
    const form = document.getElementById('editUserForm');
    const formData = new FormData(form);
    
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Updating...';
    submitBtn.disabled = true;
    
    fetch('update-user.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            closeEditUserModal();
            showSuccess('User updated successfully!');
            setTimeout(() => location.reload(), 1500);
        } else {
            showError(data.error || 'Failed to update user');
        }
    })
    .catch(error => {
        showError('Failed to update user');
    })
    .finally(() => {
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    });
}

function deleteUser(userId) {
    showConfirm('Delete User', 'Are you sure you want to delete this user? This action cannot be undone.', () => {
        fetch('delete-user.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ user_id: userId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showSuccess('User deleted successfully!');
                setTimeout(() => location.reload(), 1500);
            } else {
                showError(data.error || 'Failed to delete user');
            }
        })
        .catch(error => {
            showError('Failed to delete user');
        });
    });
}

function showConfirm(title, message, callback) {
    document.getElementById('confirmTitle').textContent = title;
    document.getElementById('confirmMessage').textContent = message;
    document.getElementById('confirmBtn').onclick = () => {
        closeConfirmModal();
        callback();
    };
    document.getElementById('confirmModal').classList.add('show');
}

function showSuccess(message) {
    document.getElementById('successMessage').textContent = message;
    document.getElementById('successModal').classList.add('show');
}

function showError(message) {
    alert('Error: ' + message); // Simple fallback for errors
}

function closeEditUserModal() {
    document.getElementById('editUserModal').classList.remove('show');
}

function closeConfirmModal() {
    document.getElementById('confirmModal').classList.remove('show');
}

function closeSuccessModal() {
    document.getElementById('successModal').classList.remove('show');
}

// Other functions
function filterUsers() {
    const status = document.getElementById('statusFilter').value;
    window.location.href = `?status=${status}`;
}

function downloadUsersPDF() {
    showNotification('Generating users report...', 'success');
    
    const reportWindow = window.open('export-users.php', '_blank');
    
    if (reportWindow) {
        reportWindow.focus();
    } else {
        showNotification('Please allow popups to download the report', 'error');
    }
}

function showNotification(message, type) {
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    notification.innerHTML = `
        <i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-triangle'}"></i>
        <span>${message}</span>
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.classList.add('show');
    }, 100);
    
    setTimeout(() => {
        notification.classList.remove('show');
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}

function viewUser(userId) {
    // Show the user modal
    const modal = document.getElementById('userModal');
    const modalBody = document.getElementById('userModalBody');
    
    if (modal && modalBody) {
        // Show loading
        modalBody.innerHTML = '<div style="text-align: center; padding: 2rem;"><i class="fas fa-spinner fa-spin"></i> Loading...</div>';
        modal.classList.add('show');
        
        // Fetch user details
        fetch(`get-user.php?id=${userId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayUserDetails(data.user);
            } else {
                modalBody.innerHTML = '<div style="text-align: center; padding: 2rem; color: #dc2626;">Error loading user details</div>';
            }
        })
        .catch(error => {
            modalBody.innerHTML = '<div style="text-align: center; padding: 2rem; color: #dc2626;">Failed to load user details</div>';
        });
    }
}

function displayUserDetails(user) {
    const modalBody = document.getElementById('userModalBody');
    const fullName = `${user.first_name || ''} ${user.last_name || ''}`.trim() || 'Unknown User';
    const avatar = user.profile_picture ? 
        `<img src="../uploads/profiles/${user.profile_picture}" style="width: 150px; height: 150px; border-radius: 12px; object-fit: cover; cursor: pointer;" onclick="viewFullImage('../uploads/profiles/${user.profile_picture}')" title="Click to view full size">` :
        `<div style="width: 150px; height: 150px; border-radius: 12px; background: linear-gradient(135deg, #3b82f6, #1d4ed8); display: flex; align-items: center; justify-content: center; color: white; font-size: 3rem; font-weight: 700;">${fullName.charAt(0).toUpperCase()}</div>`;
    
    modalBody.innerHTML = `
        <div style="display: flex; align-items: flex-start; gap: 2rem; margin-bottom: 2rem; padding-bottom: 1.5rem; border-bottom: 1px solid #e5e7eb;">
            <div style="flex-shrink: 0;">
                ${avatar}
            </div>
            <div style="flex: 1;">
                <h3 style="font-size: 1.8rem; font-weight: 700; color: #111827; margin-bottom: 0.5rem;">${fullName}</h3>
                <p style="color: #6b7280; margin: 0 0 1rem 0; font-size: 1.1rem;">${user.email || 'No email'}</p>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                    <div><strong>Phone:</strong> ${user.phone || 'Not provided'}</div>
                    <div><strong>Destination:</strong> ${user.country || 'Not specified'}</div>
                    <div><strong>Status:</strong> <span style="color: ${user.is_active ? '#059669' : '#dc2626'}; font-weight: 600;">${user.is_active ? 'Active' : 'Inactive'}</span></div>
                </div>
            </div>
        </div>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem;">
            <div style="background: #f9fafb; padding: 1rem; border-radius: 8px;">
                <div style="font-size: 0.875rem; font-weight: 600; color: #6b7280; margin-bottom: 0.5rem;">NATIONALITY</div>
                <div style="font-weight: 600; color: #111827;">${user.nationality || 'Not provided'}</div>
            </div>
            <div style="background: #f9fafb; padding: 1rem; border-radius: 8px;">
                <div style="font-size: 0.875rem; font-weight: 600; color: #6b7280; margin-bottom: 0.5rem;">GENDER</div>
                <div style="font-weight: 600; color: #111827;">${user.gender || 'Not provided'}</div>
            </div>
            <div style="background: #f9fafb; padding: 1rem; border-radius: 8px;">
                <div style="font-size: 0.875rem; font-weight: 600; color: #6b7280; margin-bottom: 0.5rem;">DATE OF BIRTH</div>
                <div style="font-weight: 600; color: #111827;">${user.date_of_birth || 'Not provided'}</div>
            </div>
            <div style="background: #f9fafb; padding: 1rem; border-radius: 8px;">
                <div style="font-size: 0.875rem; font-weight: 600; color: #6b7280; margin-bottom: 0.5rem;">PASSPORT</div>
                <div style="font-weight: 600; color: #111827;">${user.passport_number || 'Not provided'}</div>
            </div>
            <div style="background: #f9fafb; padding: 1rem; border-radius: 8px;">
                <div style="font-size: 0.875rem; font-weight: 600; color: #6b7280; margin-bottom: 0.5rem;">MEMBER SINCE</div>
                <div style="font-weight: 600; color: #111827;">${new Date(user.created_at).toLocaleDateString()}</div>
            </div>
            <div style="background: #f9fafb; padding: 1rem; border-radius: 8px;">
                <div style="font-size: 0.875rem; font-weight: 600; color: #6b7280; margin-bottom: 0.5rem;">APPLICATIONS</div>
                <div style="font-weight: 600; color: #111827;">${user.application_count || 0}</div>
            </div>
            <div style="background: #f9fafb; padding: 1rem; border-radius: 8px;">
                <div style="font-size: 0.875rem; font-weight: 600; color: #6b7280; margin-bottom: 0.5rem;">TOTAL PAID</div>
                <div style="font-weight: 600; color: #059669;">${user.currency || 'USD'} ${parseFloat(user.total_paid || 0).toFixed(2)}</div>
            </div>
            ${user.address ? `<div style="background: #f9fafb; padding: 1rem; border-radius: 8px; grid-column: 1 / -1;">
                <div style="font-size: 0.875rem; font-weight: 600; color: #6b7280; margin-bottom: 0.5rem;">ADDRESS</div>
                <div style="font-weight: 600; color: #111827;">${user.address}</div>
            </div>` : ''}
        </div>
    `;
}

function showAddUserModal() {
    const modal = document.getElementById('addUserModal');
    if (modal) {
        modal.classList.add('show');
        const form = document.getElementById('addUserForm');
        if (form) form.reset();
    }
}

function closeAddUserModal() {
    const modal = document.getElementById('addUserModal');
    if (modal) modal.classList.remove('show');
}

function closeUserModal() {
    const modal = document.getElementById('userModal');
    if (modal) modal.classList.remove('show');
}

// Modal click outside to close
const modals = ['userModal', 'addUserModal'];
modals.forEach(modalId => {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.addEventListener('click', function(e) {
            if (e.target === this) {
                this.classList.remove('show');
            }
        });
    }
});
</script>

<script>
// Profile image handling - initialize after DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Add User modal profile image handling
    const addProfileInput = document.getElementById('add_profile_input');
    if (addProfileInput) {
        addProfileInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const preview = document.getElementById('add_profile_preview');
                    preview.innerHTML = `<img src="${e.target.result}" alt="Profile Preview">`;
                };
                reader.readAsDataURL(file);
            }
        });
    }
    
    // Edit User modal profile image handling
    const editProfileInput = document.getElementById('profile_image');
    if (editProfileInput) {
        editProfileInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const preview = document.getElementById('current_profile_image');
                    preview.innerHTML = `<img src="${e.target.result}" alt="Profile Preview">`;
                };
                reader.readAsDataURL(file);
            }
        });
    }
});

function removeProfileImage() {
    document.getElementById('profile_image').value = '';
    const preview = document.getElementById('current_profile_image');
    preview.innerHTML = '<i class="fas fa-user"></i>';
}

// View full size image
function viewFullImage(imageSrc) {
    const overlay = document.createElement('div');
    overlay.style.cssText = `
        position: fixed; top: 0; left: 0; width: 100%; height: 100%;
        background: rgba(0,0,0,0.9); z-index: 9999; display: flex;
        align-items: center; justify-content: center; cursor: pointer;
    `;
    
    const img = document.createElement('img');
    img.src = imageSrc;
    img.style.cssText = `
        max-width: 90%; max-height: 90%; border-radius: 12px;
        box-shadow: 0 25px 50px rgba(0,0,0,0.5);
    `;
    
    overlay.appendChild(img);
    overlay.onclick = () => document.body.removeChild(overlay);
    document.body.appendChild(overlay);
}
</script>