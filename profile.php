<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_profile') {
    try {
        $pdo = getConnection();
        $stmt = $pdo->prepare("
            UPDATE users SET 
                first_name = ?, last_name = ?, email = ?, phone = ?, 
                date_of_birth = ?, gender = ?, country = ?, nationality = ?, 
                address = ?, city = ?, postal_code = ?, passport_number = ?, 
                passport_expiry = ?, occupation = ?, emergency_contact_name = ?, 
                emergency_contact_phone = ?
            WHERE id = ?
        ");
        
        $result = $stmt->execute([
            $_POST['first_name'], $_POST['last_name'], $_POST['email'], $_POST['phone'] ?: null,
            $_POST['date_of_birth'] ?: null, $_POST['gender'] ?: null, $_POST['country'] ?: null, $_POST['nationality'] ?: null,
            $_POST['address'] ?: null, $_POST['city'] ?: null, $_POST['postal_code'] ?: null, $_POST['passport_number'] ?: null,
            $_POST['passport_expiry'] ?: null, $_POST['occupation'] ?: null, $_POST['emergency_contact_name'] ?: null,
            $_POST['emergency_contact_phone'] ?: null, $_SESSION['user_id']
        ]);
        
        if ($result) {
            $_SESSION['success_message'] = 'Profile updated successfully!';
        }
    } catch (Exception $e) {
        $_SESSION['error_message'] = 'Update failed: ' . $e->getMessage();
    }
    header('Location: profile.php');
    exit;
}

// Get user data and applications
try {
    $pdo = getConnection();
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $stmt = $pdo->prepare("
        SELECT a.*, s.name as service_name, s.currency, s.base_price,
               ast.status_name,
               c.name as destination_country,
               pm.name as payment_method_name,
               COALESCE(a.amount_paid_so_far, 0) as amount_paid_so_far,
               COALESCE(a.payment_completion_percentage, 0) as payment_completion_percentage,
               COALESCE(a.refund_status, 'none') as refund_status
        FROM applications a
        LEFT JOIN services s ON a.service_id = s.id
        LEFT JOIN application_statuses ast ON a.status_id = ast.id
        LEFT JOIN countries c ON a.destination_country_id = c.id
        LEFT JOIN payment_methods pm ON a.payment_method_id = pm.id
        WHERE a.user_id = ?
        ORDER BY a.submitted_at DESC
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $applications = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (Exception $e) {
    $user = null;
    $applications = [];
}

$pageTitle = 'Profile - A to Z Global Link Solutions';
include 'includes/header.php';
?>

<style>
:root {
    --primary: #3b82f6;
    --primary-dark: #1d4ed8;
    --success: #10b981;
    --warning: #f59e0b;
    --danger: #ef4444;
    --gray-50: #f8fafc;
    --gray-100: #f1f5f9;
    --gray-200: #e2e8f0;
    --gray-300: #cbd5e1;
    --gray-400: #94a3b8;
    --gray-500: #64748b;
    --gray-600: #475569;
    --gray-700: #334155;
    --gray-800: #1e293b;
    --gray-900: #0f172a;
}

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
    background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
    color: var(--gray-800);
    line-height: 1.6;
    margin: 0;
    padding: 0;
}

.profile-container {
    max-width: 1400px;
    margin: 0 auto;
    padding: 1rem;
    margin-top: 80px;
}

/* Modern Profile Header */
.profile-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 20px;
    padding: 2rem;
    color: white;
    margin-bottom: 1.5rem;
    box-shadow: 0 20px 40px -12px rgba(0, 0, 0, 0.25);
    position: relative;
    overflow: hidden;
}

.profile-header::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -20%;
    width: 400px;
    height: 400px;
    background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, transparent 70%);
    border-radius: 50%;
    animation: float 6s ease-in-out infinite;
}

@keyframes float {
    0%, 100% { transform: translateY(0px); }
    50% { transform: translateY(-20px); }
}

.header-content {
    display: flex;
    align-items: center;
    gap: 2rem;
    position: relative;
    z-index: 1;
}

.avatar-section {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 1rem;
}

.avatar {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.2);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2.5rem;
    font-weight: 700;
    border: 3px solid rgba(255, 255, 255, 0.3);
    backdrop-filter: blur(10px);
}

.user-info h1 {
    font-size: 2rem;
    font-weight: 800;
    margin-bottom: 0.5rem;
    background: linear-gradient(45deg, #fff, #e2e8f0);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

.user-info p {
    opacity: 0.9;
    font-size: 1.1rem;
    font-weight: 500;
}

.user-meta {
    display: flex;
    gap: 2rem;
    margin-top: 1rem;
    flex-wrap: wrap;
}

.meta-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    opacity: 0.8;
}

.stats {
    display: flex;
    gap: 2rem;
    margin-left: auto;
}

.stat {
    text-align: center;
    padding: 0.75rem;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 12px;
    backdrop-filter: blur(10px);
    min-width: 80px;
}

.stat-number {
    font-size: 2rem;
    font-weight: 800;
    display: block;
    line-height: 1;
}

.stat-label {
    font-size: 0.9rem;
    opacity: 0.8;
    margin-top: 0.5rem;
    font-weight: 500;
}

/* Modern Tabs */
.tabs-container {
    background: white;
    border-radius: 20px;
    box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
    overflow: hidden;
    border: 1px solid var(--gray-200);
}

.tabs-nav {
    display: flex;
    background: var(--gray-50);
    border-bottom: 1px solid var(--gray-200);
    padding: 0.5rem;
    gap: 0.5rem;
}

.tab-btn {
    flex: 1;
    padding: 1rem 1.5rem;
    border: none;
    background: transparent;
    color: var(--gray-600);
    font-weight: 600;
    font-size: 0.95rem;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    border-radius: 12px;
    position: relative;
}

.tab-btn.active {
    background: white;
    color: var(--primary);
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    transform: translateY(-2px);
}

.tab-btn:hover:not(.active) {
    color: var(--primary);
    background: rgba(59, 130, 246, 0.05);
}

.tab-content {
    display: none;
    padding: 2rem;
}

.tab-content.active {
    display: block;
}

/* Form Styling */
.form-section {
    margin-bottom: 2rem;
    background: var(--gray-50);
    border-radius: 16px;
    padding: 1.5rem;
    border: 1px solid var(--gray-200);
}

.section-title {
    font-size: 1.3rem;
    font-weight: 700;
    color: var(--gray-800);
    margin-bottom: 1.25rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding-bottom: 0.75rem;
    border-bottom: 2px solid var(--gray-200);
}

.section-title i {
    color: var(--primary);
    font-size: 1.25rem;
}

.form-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 1.5rem;
    margin-top: 1.5rem;
}

.form-group {
    display: flex;
    flex-direction: column;
}

.form-group label {
    font-weight: 600;
    color: var(--gray-700);
    margin-bottom: 0.75rem;
    font-size: 0.95rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.form-group input,
.form-group select {
    padding: 0.875rem 1rem;
    border: 2px solid var(--gray-300);
    border-radius: 12px;
    font-size: 0.95rem;
    transition: all 0.3s ease;
    background: white;
    font-family: inherit;
}

.form-group input:focus,
.form-group select:focus {
    outline: none;
    border-color: var(--primary);
    box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
    transform: translateY(-2px);
}

.btn-primary {
    background: linear-gradient(135deg, var(--primary), var(--primary-dark));
    color: white;
    padding: 1rem 2.5rem;
    border: none;
    border-radius: 12px;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin: 1.5rem auto 0;
    font-size: 1rem;
    box-shadow: 0 8px 15px -3px rgba(59, 130, 246, 0.3);
}

.btn-primary:hover {
    transform: translateY(-3px);
    box-shadow: 0 20px 25px -5px rgba(59, 130, 246, 0.4);
}

/* Modern Application Cards */
.applications-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
    flex-wrap: wrap;
    gap: 1rem;
}

.applications-title h3 {
    font-size: 2rem;
    font-weight: 800;
    color: var(--gray-800);
    margin-bottom: 0.5rem;
}

.applications-title p {
    color: var(--gray-600);
    font-size: 1.1rem;
}

.applications-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: 1.5rem;
}

.app-card {
    background: white;
    border: 1px solid var(--gray-200);
    border-radius: 16px;
    padding: 1.5rem;
    transition: all 0.4s ease;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    position: relative;
    overflow: hidden;
}

.app-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, var(--primary), var(--success));
}

.app-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
    border-color: var(--primary);
}

.app-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 1.25rem;
    gap: 1rem;
}

.app-title {
    font-size: 1.2rem;
    font-weight: 700;
    color: var(--gray-800);
    margin: 0;
    line-height: 1.3;
}

.status-badge {
    padding: 0.5rem 1rem;
    border-radius: 50px;
    font-size: 0.8rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    border: 2px solid;
    white-space: nowrap;
}

.status-draft { 
    background: linear-gradient(135deg, #f3f4f6, #e5e7eb);
    color: #374151; 
    border-color: #d1d5db;
}

.status-submitted { 
    background: linear-gradient(135deg, #dbeafe, #bfdbfe);
    color: #1e40af; 
    border-color: #93c5fd;
}

.status-under-review { 
    background: linear-gradient(135deg, #fef3c7, #fde68a);
    color: #92400e; 
    border-color: #fcd34d;
}

.status-processing { 
    background: linear-gradient(135deg, #fef3c7, #fde68a);
    color: #92400e; 
    border-color: #fcd34d;
}

.status-approved { 
    background: linear-gradient(135deg, #d1fae5, #a7f3d0);
    color: #065f46; 
    border-color: #6ee7b7;
}

.status-rejected { 
    background: linear-gradient(135deg, #fee2e2, #fecaca);
    color: #991b1b; 
    border-color: #f87171;
}

.status-completed { 
    background: linear-gradient(135deg, #d1fae5, #a7f3d0);
    color: #065f46; 
    border-color: #6ee7b7;
}

.app-details {
    margin-bottom: 1.5rem;
}

.app-detail {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 0.75rem;
    padding: 0.625rem;
    background: var(--gray-50);
    border-radius: 8px;
    border-left: 3px solid var(--primary);
}

.app-detail-label {
    color: var(--gray-600);
    font-weight: 600;
    font-size: 0.9rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.app-detail-value {
    color: var(--gray-800);
    font-weight: 700;
    font-size: 0.95rem;
}

.app-actions {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(100px, 1fr));
    gap: 0.75rem;
}

.btn-action {
    padding: 0.625rem 1rem;
    border: none;
    border-radius: 8px;
    font-size: 0.85rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    text-decoration: none;
    text-align: center;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.375rem;
    position: relative;
    overflow: hidden;
}

.btn-view {
    background: linear-gradient(135deg, var(--primary), var(--primary-dark));
    color: white;
    box-shadow: 0 4px 6px -1px rgba(59, 130, 246, 0.3);
}

.btn-view:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 15px -3px rgba(59, 130, 246, 0.4);
    color: white;
    text-decoration: none;
}

.btn-delete {
    background: linear-gradient(135deg, var(--danger), #dc2626);
    color: white;
    box-shadow: 0 4px 6px -1px rgba(239, 68, 68, 0.3);
}

.btn-delete:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 15px -3px rgba(239, 68, 68, 0.4);
}

.btn-refund {
    background: linear-gradient(135deg, var(--warning), #d97706);
    color: white;
    box-shadow: 0 4px 6px -1px rgba(245, 158, 11, 0.3);
}

.btn-refund:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 15px -3px rgba(245, 158, 11, 0.4);
}

.btn-edit {
    background: linear-gradient(135deg, var(--gray-600), var(--gray-700));
    color: white;
    box-shadow: 0 4px 6px -1px rgba(107, 114, 128, 0.3);
}

.btn-edit:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 15px -3px rgba(107, 114, 128, 0.4);
    color: white;
    text-decoration: none;
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: 4rem 2rem;
    background: white;
    border-radius: 24px;
    border: 2px dashed var(--gray-300);
}

.empty-state-icon {
    width: 120px;
    height: 120px;
    background: linear-gradient(135deg, rgba(59, 130, 246, 0.1), rgba(147, 197, 253, 0.2));
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 2rem;
    color: var(--primary);
    font-size: 3rem;
}

.empty-state h3 {
    color: var(--gray-800);
    margin-bottom: 1rem;
    font-size: 1.5rem;
    font-weight: 700;
}

.empty-state p {
    color: var(--gray-600);
    margin-bottom: 2rem;
    font-size: 1.1rem;
    line-height: 1.6;
}

/* Alerts */
.alert {
    padding: 1.25rem 1.5rem;
    border-radius: 16px;
    margin-bottom: 2rem;
    display: flex;
    align-items: center;
    gap: 0.75rem;
    font-weight: 500;
}

.alert-success {
    background: linear-gradient(135deg, #d1fae5, #a7f3d0);
    color: #065f46;
    border: 1px solid #6ee7b7;
}

.alert-error {
    background: linear-gradient(135deg, #fee2e2, #fecaca);
    color: #991b1b;
    border: 1px solid #f87171;
}

/* Modals */
.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.6);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
    backdrop-filter: blur(4px);
    padding: 1rem;
    overflow-y: auto;
}

.modal-content {
    background: white;
    padding: 2rem;
    border-radius: 16px;
    max-width: 500px;
    width: 100%;
    text-align: center;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
    max-height: 90vh;
    overflow-y: auto;
}

.modal-icon {
    font-size: 3rem;
    margin-bottom: 1rem;
}

.modal-icon.warning { color: var(--warning); }
.modal-icon.danger { color: var(--danger); }

.modal-content h3 {
    font-size: 1.3rem;
    font-weight: 700;
    margin-bottom: 0.75rem;
    color: var(--gray-800);
}

.modal-content p {
    color: var(--gray-600);
    margin-bottom: 1.5rem;
    line-height: 1.5;
    font-size: 1rem;
}

.modal-actions {
    display: flex;
    gap: 0.75rem;
    margin-top: 1.5rem;
}

.btn-cancel, .btn-confirm {
    flex: 1;
    padding: 0.875rem 1.25rem;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    font-size: 0.95rem;
}

.btn-cancel {
    background: var(--gray-200);
    color: var(--gray-700);
}

.btn-cancel:hover {
    background: var(--gray-300);
    transform: translateY(-2px);
}

.btn-confirm {
    background: linear-gradient(135deg, var(--danger), #dc2626);
    color: white;
}

.btn-confirm:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 15px -3px rgba(239, 68, 68, 0.4);
}

/* Refund Modal */
.refund-modal .modal-content {
    max-width: 550px;
    text-align: left;
}

.refund-form {
    margin-top: 1.5rem;
}

.refund-form .form-group {
    margin-bottom: 1.25rem;
}

.refund-form .form-group label {
    font-weight: 600;
    color: var(--gray-700);
    margin-bottom: 0.5rem;
    font-size: 0.9rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.refund-form .form-group input,
.refund-form .form-group textarea {
    width: 100%;
    padding: 0.75rem;
    border: 2px solid var(--gray-300);
    border-radius: 8px;
    font-size: 0.9rem;
    transition: all 0.3s ease;
    background: white;
    font-family: inherit;
}

.refund-form .form-group input:focus,
.refund-form .form-group textarea:focus {
    outline: none;
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.refund-form textarea {
    min-height: 80px;
    resize: vertical;
}

.refund-form small {
    color: var(--gray-500);
    margin-top: 0.25rem;
    display: block;
    font-size: 0.8rem;
}

/* Responsive Design */
@media (max-width: 1024px) {
    .applications-grid {
        grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
    }
    
    .header-content {
        gap: 2rem;
    }
    
    .stats {
        gap: 1.5rem;
    }
}

@media (max-width: 768px) {
    body {
        padding: 0;
    }
    
    .profile-container {
        padding: 0.5rem;
        margin-top: 60px;
    }
    
    .profile-header {
        padding: 1.25rem;
        border-radius: 12px;
        margin-bottom: 1rem;
    }
    
    .header-content {
        flex-direction: column;
        text-align: center;
        gap: 1.25rem;
    }
    
    .avatar {
        width: 70px;
        height: 70px;
        font-size: 1.75rem;
        border-width: 2px;
    }
    
    .user-info h1 {
        font-size: 1.5rem;
        margin-bottom: 0.25rem;
    }
    
    .user-info p {
        font-size: 0.95rem;
    }
    
    .user-meta {
        gap: 0.75rem;
        justify-content: center;
        flex-wrap: wrap;
    }
    
    .meta-item {
        font-size: 0.85rem;
    }
    
    .stats {
        margin-left: 0;
        gap: 1rem;
        width: 100%;
        justify-content: center;
    }
    
    .stat {
        padding: 0.5rem;
        min-width: 60px;
    }
    
    .stat-number {
        font-size: 1.5rem;
    }
    
    .stat-label {
        font-size: 0.75rem;
    }
    
    .tabs-container {
        border-radius: 12px;
    }
    
    .tabs-nav {
        padding: 0.25rem;
        gap: 0.25rem;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        scrollbar-width: none;
        -ms-overflow-style: none;
    }
    
    .tabs-nav::-webkit-scrollbar {
        display: none;
    }
    
    .tab-btn {
        padding: 0.75rem 1rem;
        font-size: 0.85rem;
        white-space: nowrap;
        min-width: 120px;
        flex: none;
        gap: 0.375rem;
    }
    
    .tab-content {
        padding: 1.25rem 1rem;
    }
    
    .form-section {
        padding: 1.25rem;
        margin-bottom: 1.25rem;
        border-radius: 12px;
    }
    
    .section-title {
        font-size: 1.15rem;
        margin-bottom: 1rem;
        gap: 0.375rem;
        padding-bottom: 0.5rem;
    }
    
    .form-grid {
        grid-template-columns: 1fr;
        gap: 1.25rem;
        margin-top: 1.25rem;
    }
    
    .form-group label {
        margin-bottom: 0.5rem;
        font-size: 0.9rem;
    }
    
    .form-group input,
    .form-group select {
        padding: 0.75rem;
        font-size: 0.9rem;
        border-radius: 8px;
    }
    
    .btn-primary {
        padding: 0.875rem 2rem;
        font-size: 0.95rem;
        margin: 1.25rem auto 0;
        border-radius: 8px;
    }
    
    .applications-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 1rem;
        margin-bottom: 1.25rem;
    }
    
    .applications-title h3 {
        font-size: 1.4rem;
    }
    
    .applications-title p {
        font-size: 0.95rem;
    }
    
    .applications-grid {
        grid-template-columns: 1fr;
        gap: 1.25rem;
    }
    
    .app-card {
        padding: 1.25rem;
        border-radius: 12px;
    }
    
    .app-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.75rem;
        margin-bottom: 1rem;
    }
    
    .app-title {
        font-size: 1.1rem;
        line-height: 1.3;
    }
    
    .status-badge {
        align-self: flex-start;
        padding: 0.375rem 0.75rem;
        font-size: 0.7rem;
    }
    
    .app-detail {
        padding: 0.5rem;
        margin-bottom: 0.625rem;
        border-radius: 6px;
        border-left-width: 2px;
    }
    
    .app-detail-label {
        font-size: 0.8rem;
    }
    
    .app-detail-value {
        font-size: 0.85rem;
    }
    
    .app-actions {
        grid-template-columns: 1fr;
        gap: 0.625rem;
    }
    
    .btn-action {
        padding: 0.75rem;
        font-size: 0.8rem;
        border-radius: 6px;
    }
    
    .empty-state {
        padding: 2.5rem 1.25rem;
        border-radius: 12px;
    }
    
    .empty-state-icon {
        width: 60px;
        height: 60px;
        font-size: 1.5rem;
        margin-bottom: 1.25rem;
    }
    
    .empty-state h3 {
        font-size: 1.2rem;
    }
    
    .empty-state p {
        font-size: 0.95rem;
        margin-bottom: 1.25rem;
    }
    
    .modal-content {
        padding: 1.5rem 1rem;
        border-radius: 12px;
        margin: 0.5rem;
        max-width: calc(100vw - 1rem);
        max-height: 95vh;
    }
    
    .modal-icon {
        font-size: 2.5rem;
        margin-bottom: 0.75rem;
    }
    
    .modal-content h3 {
        font-size: 1.2rem;
        margin-bottom: 0.5rem;
    }
    
    .modal-content p {
        font-size: 0.9rem;
        margin-bottom: 1.25rem;
    }
    
    .modal-actions {
        flex-direction: column;
        gap: 0.5rem;
        margin-top: 1.25rem;
    }
    
    .btn-cancel, .btn-confirm {
        padding: 0.75rem 1rem;
        font-size: 0.9rem;
    }
    
    .refund-form {
        margin-top: 1.25rem;
    }
    
    .refund-form .form-group {
        margin-bottom: 1rem;
    }
    
    .refund-form .form-group label {
        font-size: 0.85rem;
        margin-bottom: 0.375rem;
    }
    
    .refund-form .form-group input,
    .refund-form .form-group textarea {
        padding: 0.625rem;
        font-size: 0.85rem;
    }
    
    .refund-form textarea {
        min-height: 60px;
    }
}

@media (max-width: 480px) {
    .profile-container {
        padding: 0.25rem;
    }
    
    .profile-header {
        padding: 1rem;
        margin-bottom: 0.75rem;
    }
    
    .user-info h1 {
        font-size: 1.3rem;
    }
    
    .user-meta {
        flex-direction: column;
        gap: 0.5rem;
    }
    
    .stats {
        gap: 0.75rem;
    }
    
    .stat {
        padding: 0.375rem;
        min-width: 50px;
    }
    
    .stat-number {
        font-size: 1.25rem;
    }
    
    .tab-btn {
        padding: 0.625rem 0.75rem;
        font-size: 0.8rem;
        min-width: 100px;
    }
    
    .tab-content {
        padding: 1rem 0.75rem;
    }
    
    .form-section {
        padding: 1rem;
    }
    
    .section-title {
        font-size: 1.1rem;
    }
    
    .app-card {
        padding: 1rem;
    }
    
    .app-title {
        font-size: 1rem;
    }
    
    .modal-content {
        padding: 1.25rem 1rem;
        margin: 0.5rem;
    }
}
</style>

<div class="profile-container">
    <!-- Modern Profile Header -->
    <div class="profile-header">
        <div class="header-content">
            <div class="avatar-section">
                <div class="avatar">
                    <?= strtoupper(substr($user['first_name'] ?? 'U', 0, 1)) ?>
                </div>
            </div>
            <div class="user-info">
                <h1><?= htmlspecialchars(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '')) ?></h1>
                <p><?= htmlspecialchars($user['email'] ?? '') ?></p>
                <div class="user-meta">
                    <?php if (!empty($user['phone'])): ?>
                    <div class="meta-item">
                        <i class="fas fa-phone"></i>
                        <span><?= htmlspecialchars($user['phone']) ?></span>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($user['country'])): ?>
                    <div class="meta-item">
                        <i class="fas fa-map-marker-alt"></i>
                        <span><?= htmlspecialchars($user['country']) ?></span>
                    </div>
                    <?php endif; ?>
                    <div class="meta-item">
                        <i class="fas fa-calendar"></i>
                        <span>Member since <?= date('M Y', strtotime($user['created_at'] ?? 'now')) ?></span>
                    </div>
                </div>
            </div>
            <div class="stats">
                <div class="stat">
                    <span class="stat-number"><?= count($applications) ?></span>
                    <span class="stat-label">Applications</span>
                </div>
                <div class="stat">
                    <span class="stat-number"><?= count(array_filter($applications, fn($app) => strtolower($app['status_name']) === 'approved')) ?></span>
                    <span class="stat-label">Approved</span>
                </div>
                <div class="stat">
                    <span class="stat-number"><?= count(array_filter($applications, fn($app) => in_array(strtolower($app['status_name']), ['processing', 'under review']))) ?></span>
                    <span class="stat-label">In Progress</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Alerts -->
    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i>
            <?= htmlspecialchars($_SESSION['success_message']) ?>
        </div>
        <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="alert alert-error">
            <i class="fas fa-exclamation-circle"></i>
            <?= htmlspecialchars($_SESSION['error_message']) ?>
        </div>
        <?php unset($_SESSION['error_message']); ?>
    <?php endif; ?>

    <!-- Modern Tabs Container -->
    <div class="tabs-container">
        <div class="tabs-nav">
            <button class="tab-btn active" onclick="switchTab('profile')">
                <i class="fas fa-user-circle"></i>
                Profile Information
            </button>
            <button class="tab-btn" onclick="switchTab('applications')">
                <i class="fas fa-file-alt"></i>
                My Applications
                <span style="background: var(--primary); color: white; padding: 0.25rem 0.5rem; border-radius: 50px; font-size: 0.75rem; margin-left: 0.5rem;"><?= count($applications) ?></span>
            </button>
        </div>

        <!-- Profile Tab -->
        <div id="profile-tab" class="tab-content active">
            <form method="POST">
                <input type="hidden" name="action" value="update_profile">
                
                <!-- Basic Information -->
                <div class="form-section">
                    <h3 class="section-title">
                        <i class="fas fa-user"></i>
                        Basic Information
                    </h3>
                    <div class="form-grid">
                        <div class="form-group">
                            <label><i class="fas fa-user"></i> First Name</label>
                            <input type="text" name="first_name" value="<?= htmlspecialchars($user['first_name'] ?? '') ?>" required>
                        </div>
                        <div class="form-group">
                            <label><i class="fas fa-user"></i> Last Name</label>
                            <input type="text" name="last_name" value="<?= htmlspecialchars($user['last_name'] ?? '') ?>" required>
                        </div>
                        <div class="form-group">
                            <label><i class="fas fa-envelope"></i> Email Address</label>
                            <input type="email" name="email" value="<?= htmlspecialchars($user['email'] ?? '') ?>" required>
                        </div>
                        <div class="form-group">
                            <label><i class="fas fa-phone"></i> Phone Number</label>
                            <input type="tel" name="phone" value="<?= htmlspecialchars($user['phone'] ?? '') ?>" placeholder="+250 xxx xxx xxx">
                        </div>
                        <div class="form-group">
                            <label><i class="fas fa-calendar"></i> Date of Birth</label>
                            <input type="date" name="date_of_birth" value="<?= $user['date_of_birth'] ?? '' ?>">
                        </div>
                        <div class="form-group">
                            <label><i class="fas fa-venus-mars"></i> Gender</label>
                            <select name="gender">
                                <option value="">Select Gender</option>
                                <option value="male" <?= ($user['gender'] ?? '') === 'male' ? 'selected' : '' ?>>Male</option>
                                <option value="female" <?= ($user['gender'] ?? '') === 'female' ? 'selected' : '' ?>>Female</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Location Information -->
                <div class="form-section">
                    <h3 class="section-title">
                        <i class="fas fa-map-marker-alt"></i>
                        Location & Address
                    </h3>
                    <div class="form-grid">
                        <div class="form-group">
                            <label><i class="fas fa-globe"></i> Country</label>
                            <input type="text" name="country" value="<?= htmlspecialchars($user['country'] ?? '') ?>" placeholder="Rwanda">
                        </div>
                        <div class="form-group">
                            <label><i class="fas fa-flag"></i> Nationality</label>
                            <input type="text" name="nationality" value="<?= htmlspecialchars($user['nationality'] ?? '') ?>" placeholder="Rwandan">
                        </div>
                        <div class="form-group">
                            <label><i class="fas fa-home"></i> Address</label>
                            <input type="text" name="address" value="<?= htmlspecialchars($user['address'] ?? '') ?>" placeholder="Street address">
                        </div>
                        <div class="form-group">
                            <label><i class="fas fa-city"></i> City</label>
                            <input type="text" name="city" value="<?= htmlspecialchars($user['city'] ?? '') ?>" placeholder="Kigali">
                        </div>
                        <div class="form-group">
                            <label><i class="fas fa-mail-bulk"></i> Postal Code</label>
                            <input type="text" name="postal_code" value="<?= htmlspecialchars($user['postal_code'] ?? '') ?>" placeholder="00000">
                        </div>
                    </div>
                </div>

                <!-- Travel Documents -->
                <div class="form-section">
                    <h3 class="section-title">
                        <i class="fas fa-passport"></i>
                        Travel Documents
                    </h3>
                    <div class="form-grid">
                        <div class="form-group">
                            <label><i class="fas fa-passport"></i> Passport Number</label>
                            <input type="text" name="passport_number" value="<?= htmlspecialchars($user['passport_number'] ?? '') ?>" placeholder="PC1234567">
                        </div>
                        <div class="form-group">
                            <label><i class="fas fa-calendar-times"></i> Passport Expiry</label>
                            <input type="date" name="passport_expiry" value="<?= $user['passport_expiry'] ?? '' ?>">
                        </div>
                        <div class="form-group">
                            <label><i class="fas fa-briefcase"></i> Occupation</label>
                            <input type="text" name="occupation" value="<?= htmlspecialchars($user['occupation'] ?? '') ?>" placeholder="Software Engineer">
                        </div>
                    </div>
                </div>

                <!-- Emergency Contact -->
                <div class="form-section">
                    <h3 class="section-title">
                        <i class="fas fa-phone-alt"></i>
                        Emergency Contact
                    </h3>
                    <div class="form-grid">
                        <div class="form-group">
                            <label><i class="fas fa-user-friends"></i> Contact Name</label>
                            <input type="text" name="emergency_contact_name" value="<?= htmlspecialchars($user['emergency_contact_name'] ?? '') ?>" placeholder="John Doe">
                        </div>
                        <div class="form-group">
                            <label><i class="fas fa-phone"></i> Contact Phone</label>
                            <input type="tel" name="emergency_contact_phone" value="<?= htmlspecialchars($user['emergency_contact_phone'] ?? '') ?>" placeholder="+250 xxx xxx xxx">
                        </div>
                    </div>
                </div>
                
                <button type="submit" class="btn-primary">
                    <i class="fas fa-save"></i>
                    Save Changes
                </button>
            </form>
        </div>

        <!-- Applications Tab -->
        <div id="applications-tab" class="tab-content">
            <div class="applications-header">
                <div class="applications-title">
                    <h3>My Applications</h3>
                    <p>Track and manage your visa applications</p>
                </div>
                <a href="application" class="btn-primary" style="text-decoration: none;">
                    <i class="fas fa-plus"></i>
                    New Application
                </a>
            </div>

            <?php if (!empty($applications)): ?>
                <div class="applications-grid">
                    <?php foreach ($applications as $app): ?>
                        <div class="app-card">
                            <div class="app-header">
                                <h4 class="app-title"><?= htmlspecialchars($app['service_name']) ?></h4>
                                <span class="status-badge status-<?= strtolower(str_replace(' ', '-', $app['status_name'])) ?>">
                                    <?= htmlspecialchars($app['status_name']) ?>
                                </span>
                            </div>
                            
                            <div class="app-details">
                                <div class="app-detail">
                                    <span class="app-detail-label">
                                        <i class="fas fa-hashtag"></i>
                                        Application Number
                                    </span>
                                    <span class="app-detail-value"><?= htmlspecialchars($app['application_number']) ?></span>
                                </div>
                                
                                <?php if ($app['destination_country']): ?>
                                <div class="app-detail">
                                    <span class="app-detail-label">
                                        <i class="fas fa-map-marker-alt"></i>
                                        Destination
                                    </span>
                                    <span class="app-detail-value"><?= htmlspecialchars($app['destination_country']) ?></span>
                                </div>
                                <?php endif; ?>
                                
                                <div class="app-detail">
                                    <span class="app-detail-label">
                                        <i class="fas fa-calendar"></i>
                                        Submitted
                                    </span>
                                    <span class="app-detail-value"><?= date('M d, Y', strtotime($app['submitted_at'])) ?></span>
                                </div>
                                
                                <div class="app-detail">
                                    <span class="app-detail-label">
                                        <i class="fas fa-dollar-sign"></i>
                                        Amount
                                    </span>
                                    <span class="app-detail-value"><?= number_format($app['total_amount'], 2) ?> <?= $app['currency'] ?></span>
                                </div>
                                
                                <?php if ($app['payment_method_name']): ?>
                                <div class="app-detail">
                                    <span class="app-detail-label">
                                        <i class="fas fa-credit-card"></i>
                                        Payment Method
                                    </span>
                                    <span class="app-detail-value"><?= htmlspecialchars($app['payment_method_name']) ?></span>
                                </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="app-actions">
                                <a href="view-application.php?id=<?= $app['id'] ?>" class="btn-action btn-view">
                                    <i class="fas fa-eye"></i> View Details
                                </a>
                                
                                <?php if (in_array(strtolower($app['status_name']), ['draft', 'submitted'])): ?>
                                    <button onclick="deleteApplication(<?= $app['id'] ?>)" class="btn-action btn-delete">
                                        <i class="fas fa-trash"></i> Delete
                                    </button>
                                <?php endif; ?>
                                
                                <?php if (in_array(strtolower($app['status_name']), ['draft'])): ?>
                                    <a href="edit-application.php?id=<?= $app['id'] ?>" class="btn-action btn-edit">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                <?php endif; ?>
                                
                                <?php if (in_array(strtolower($app['status_name']), ['approved', 'completed']) && ($app['refund_status'] ?? 'none') === 'none' && $app['total_amount'] > 0): ?>
                                    <button onclick="requestRefund(<?= $app['id'] ?>, <?= $app['total_amount'] ?>, '<?= $app['currency'] ?>')" class="btn-action btn-refund">
                                        <i class="fas fa-undo-alt"></i> Request Refund
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <div class="empty-state-icon">
                        <i class="fas fa-file-alt"></i>
                    </div>
                    <h3>No Applications Yet</h3>
                    <p>You haven't submitted any visa applications yet. Start your journey by creating your first application.</p>
                    <a href="application" class="btn-primary" style="text-decoration: none;">
                        <i class="fas fa-plus"></i>
                        Create First Application
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function switchTab(tabName) {
    document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
    document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));
    
    event.target.classList.add('active');
    document.getElementById(tabName + '-tab').classList.add('active');
}

function deleteApplication(appId) {
    const modal = document.createElement('div');
    modal.className = 'modal-overlay';
    modal.innerHTML = `
        <div class="modal-content">
            <div class="modal-icon warning">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <h3>Delete Application</h3>
            <p>Are you sure you want to delete this application? This action cannot be undone and all associated data will be permanently removed.</p>
            <div class="modal-actions">
                <button class="btn-cancel" onclick="closeModal()">Cancel</button>
                <button class="btn-confirm" onclick="confirmDelete(${appId})">Delete Application</button>
            </div>
        </div>
    `;
    document.body.appendChild(modal);
}

function requestRefund(appId, amount, currency) {
    const modal = document.createElement('div');
    modal.className = 'modal-overlay refund-modal';
    modal.innerHTML = `
        <div class="modal-content">
            <div class="modal-icon warning">
                <i class="fas fa-undo-alt"></i>
            </div>
            <h3>Request Refund</h3>
            <p>Submit a refund request for your application. Please provide detailed information about your refund request.</p>
            
            <form class="refund-form" onsubmit="submitRefund(event, ${appId})">
                <div class="form-group">
                    <label><i class="fas fa-dollar-sign"></i> Refund Amount</label>
                    <input type="number" name="amount" value="${amount}" max="${amount}" step="0.01" required readonly>
                    <small style="color: var(--gray-500); margin-top: 0.5rem; display: block;">Maximum refundable amount: ${amount} ${currency}</small>
                </div>
                
                <div class="form-group">
                    <label><i class="fas fa-comment-alt"></i> Reason for Refund</label>
                    <textarea name="reason" required placeholder="Please explain why you are requesting a refund. Be as detailed as possible to help us process your request quickly."></textarea>
                </div>
                
                <div class="form-group">
                    <label><i class="fas fa-university"></i> Bank Name</label>
                    <input type="text" name="bank_name" required placeholder="Enter your bank name">
                </div>
                
                <div class="form-group">
                    <label><i class="fas fa-credit-card"></i> Account Number</label>
                    <input type="text" name="account_number" required placeholder="Enter your account number">
                </div>
                
                <div class="form-group">
                    <label><i class="fas fa-user"></i> Account Holder Name</label>
                    <input type="text" name="account_name" required placeholder="Enter account holder name">
                </div>
                
                <div class="modal-actions">
                    <button type="button" class="btn-cancel" onclick="closeModal()">Cancel</button>
                    <button type="submit" class="btn-confirm" style="background: linear-gradient(135deg, var(--warning), #d97706);">Submit Refund Request</button>
                </div>
            </form>
        </div>
    `;
    document.body.appendChild(modal);
}

function closeModal() {
    const modal = document.querySelector('.modal-overlay');
    if (modal) modal.remove();
}

function confirmDelete(appId) {
    fetch('delete-application.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id: appId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Application deleted successfully', 'success');
            setTimeout(() => location.reload(), 1500);
        } else {
            showToast('Error: ' + data.message, 'error');
        }
    })
    .catch(error => {
        showToast('Error deleting application', 'error');
    });
    closeModal();
}

function submitRefund(event, appId) {
    event.preventDefault();
    const form = event.target;
    const btn = form.querySelector('button[type="submit"]');
    const orig = btn.innerHTML;
    
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Submitting...';
    btn.disabled = true;
    
    // Get values directly from form elements
    const formData = {
        application_id: appId,
        amount: form.querySelector('[name="amount"]').value,
        reason: form.querySelector('[name="reason"]').value,
        bank_name: form.querySelector('[name="bank_name"]').value,
        account_number: form.querySelector('[name="account_number"]').value,
        account_name: form.querySelector('[name="account_name"]').value
    };
    
    console.log('Submitting:', formData);
    
    // Use URLSearchParams for reliable POST
    const params = new URLSearchParams(formData);
    
    fetch('request-refund.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: params
    })
    .then(r => r.json())
    .then(d => {
        console.log('Response:', d);
        showToast(d.message, d.success ? 'success' : 'error');
        if (d.success) {
            closeModal();
            setTimeout(() => location.reload(), 1500);
        } else {
            btn.innerHTML = orig;
            btn.disabled = false;
        }
    })
    .catch(e => {
        console.error('Error:', e);
        showToast('Network error', 'error');
        btn.innerHTML = orig;
        btn.disabled = false;
    });
}

function showToast(message, type = 'success') {
    const toast = document.createElement('div');
    toast.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: ${type === 'success' ? 'linear-gradient(135deg, #10b981, #059669)' : 'linear-gradient(135deg, #ef4444, #dc2626)'};
        color: white;
        padding: 1rem 1.5rem;
        border-radius: 12px;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        z-index: 1001;
        transform: translateX(100%);
        transition: transform 0.3s ease;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-weight: 600;
    `;
    toast.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
        <span>${message}</span>
    `;
    
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.style.transform = 'translateX(0)';
    }, 100);
    
    setTimeout(() => {
        toast.style.transform = 'translateX(100%)';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

document.addEventListener('click', function(e) {
    if (e.target.classList.contains('modal-overlay')) {
        closeModal();
    }
});
</script>

<?php include 'includes/footer.php'; ?>