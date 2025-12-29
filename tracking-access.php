<?php
/**
 * Script untuk mencatat aktivitas akses halaman
 * Dipanggil via AJAX dari frontend untuk logging aktivitas
 */
include_once 'includes/functions.php';

// Set content type JSON
header('Content-Type: application/json');

// Cek login
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

// Cek method POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Ambil data JSON
$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['action']) || !isset($input['page'])) {
    echo json_encode(['success' => false, 'message' => 'Missing parameters']);
    exit;
}

$action = sanitizeInput($input['action']);
$page = sanitizeInput($input['page']);
$description = isset($input['description']) ? sanitizeInput($input['description']) : '';

$database = new Database();
$conn = $database->getConnection();

try {
    // Map page ke action yang sesuai
    $actionMap = [
        'monitoring' => 'VIEW_MONITORING',
        'dashboard' => 'ACCESS_DASHBOARD',
        'file-manager' => 'ACCESS_FILE_MANAGER',
        'export-excel' => 'EXPORT_EXCEL',
        'print-data' => 'PRINT_DATA',
        'search' => 'SEARCH_DATA',
        'filter' => 'FILTER_DATA'
    ];
    
    $logAction = isset($actionMap[$action]) ? $actionMap[$action] : strtoupper($action);
    
    // Buat description jika tidak ada
    if (empty($description)) {
        $descriptions = [
            'VIEW_MONITORING' => 'Accessed monitoring page',
            'ACCESS_DASHBOARD' => 'Accessed dashboard page',
            'ACCESS_FILE_MANAGER' => 'Accessed file manager page',
            'EXPORT_EXCEL' => 'Exported data to Excel',
            'PRINT_DATA' => 'Printed monitoring data',
            'SEARCH_DATA' => 'Performed data search',
            'FILTER_DATA' => 'Applied data filters'
        ];
        
        $description = isset($descriptions[$logAction]) ? $descriptions[$logAction] : "Accessed $page";
    }
    
    // Log activity
    logActivity($conn, $_SESSION['user_id'], $logAction, $description);
    
    echo json_encode(['success' => true, 'message' => 'Activity logged']);
    
} catch (Exception $e) {
    error_log("Failed to log access activity: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Logging failed']);
}
?>