<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

require_once '../../config/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$serviceId = $_POST['service_id'] ?? '';

if (empty($serviceId)) {
    echo json_encode(['success' => false, 'message' => 'Service ID is required']);
    exit;
}

try {
    $pdo = getConnection();
    
    $stmt = $pdo->prepare("SELECT * FROM services WHERE id = ? AND is_active = 1");
    $stmt->execute([$serviceId]);
    $service = $stmt->fetch();
    
    if ($service) {
        // Try different possible column names for requirements
        $requirements = null;
        if (isset($service['requirements_json'])) {
            $requirements = $service['requirements_json'];
        } elseif (isset($service['requirements'])) {
            $requirements = $service['requirements'];
        } elseif (isset($service['requirements_summary'])) {
            $requirements = $service['requirements_summary'];
        } elseif (isset($service['document_requirements'])) {
            $requirements = $service['document_requirements'];
        }
        
        if ($requirements) {
            // If it's JSON, decode it
            $decoded = json_decode($requirements, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                // Handle nested structure with documents array
                if (isset($decoded['documents']) && is_array($decoded['documents'])) {
                    $requirementsList = [];
                    foreach ($decoded['documents'] as $doc) {
                        if (isset($doc['name'])) {
                            $requirementsList[] = $doc['name'];
                        }
                    }
                    $requirementsText = implode(', ', $requirementsList);
                } else {
                    // Handle simple array
                    $requirementsList = [];
                    foreach ($decoded as $req) {
                        if (is_string($req)) {
                            $requirementsList[] = $req;
                        } elseif (is_array($req) && isset($req['name'])) {
                            $requirementsList[] = $req['name'];
                        }
                    }
                    $requirementsText = implode(', ', $requirementsList);
                }
            } else {
                $requirementsText = $requirements;
            }
        } else {
            // Default requirements for visa services
            $requirementsText = 'Passport, Photos, Application Form, Supporting Documents';
        }
        
        echo json_encode([
            'success' => true,
            'requirements' => $requirementsText,
            'documents' => isset($decoded['documents']) ? $decoded['documents'] : []
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Service not found'
        ]);
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>