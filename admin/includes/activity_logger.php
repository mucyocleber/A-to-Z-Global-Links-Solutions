<?php
// Activity logging functions

function logActivity($pdo, $applicationId, $activityType, $description, $oldValue = null, $newValue = null, $createdBy = null) {
    try {
        $stmt = $pdo->prepare("
            INSERT INTO application_activities (application_id, activity_type, description, old_value, new_value, created_by)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        return $stmt->execute([$applicationId, $activityType, $description, $oldValue, $newValue, $createdBy]);
    } catch (Exception $e) {
        error_log("Activity logging error: " . $e->getMessage());
        return false;
    }
}

function logStatusChange($pdo, $applicationId, $oldStatus, $newStatus, $adminId = null) {
    $description = "Application status changed from '{$oldStatus}' to '{$newStatus}'";
    return logActivity($pdo, $applicationId, 'status_change', $description, $oldStatus, $newStatus, $adminId);
}

function logPaymentUpdate($pdo, $applicationId, $paymentType, $oldStatus, $newStatus, $adminId = null) {
    $description = ucfirst($paymentType) . " payment status changed from '{$oldStatus}' to '{$newStatus}'";
    return logActivity($pdo, $applicationId, 'payment_update', $description, $oldStatus, $newStatus, $adminId);
}

function logDocumentUpload($pdo, $applicationId, $documentType, $filename, $userId = null) {
    $description = "Document uploaded: {$documentType} ({$filename})";
    return logActivity($pdo, $applicationId, 'document_upload', $description, null, $filename, $userId);
}

function logPaymentProofUpload($pdo, $applicationId, $paymentType, $filename, $adminId = null) {
    $description = ucfirst($paymentType) . " payment proof uploaded: {$filename}";
    return logActivity($pdo, $applicationId, 'payment_proof_uploaded', $description, null, $filename, $adminId);
}

function logNoteAdded($pdo, $applicationId, $notePreview, $adminId = null) {
    $description = "Internal note added: " . substr($notePreview, 0, 100) . (strlen($notePreview) > 100 ? '...' : '');
    return logActivity($pdo, $applicationId, 'note_added', $description, null, null, $adminId);
}

function getApplicationActivities($pdo, $applicationId) {
    try {
        $stmt = $pdo->prepare("
            SELECT aa.*, a.username as admin_username, u.first_name, u.last_name
            FROM application_activities aa
            LEFT JOIN admins a ON aa.created_by = a.id
            LEFT JOIN users u ON aa.created_by = u.id
            WHERE aa.application_id = ?
            ORDER BY aa.created_at DESC
        ");
        $stmt->execute([$applicationId]);
        return $stmt->fetchAll();
    } catch (Exception $e) {
        error_log("Get activities error: " . $e->getMessage());
        return [];
    }
}
?>