<?php
include_once 'includes/functions.php';
requireLogin();

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    http_response_code(400);
    echo "Invalid file ID";
    exit;
}

$fileId = (int)$_GET['id'];
$database = new Database();
$conn = $database->getConnection();

try {
    $stmt = $conn->prepare("SELECT * FROM uploads WHERE id = :id");
    $stmt->bindParam(':id', $fileId, PDO::PARAM_INT);
    $stmt->execute();
    $file = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$file) {
        http_response_code(404);
        echo "File not found in database";
        exit;
    }
    
    // Check permissions
    if ($_SESSION['role'] !== 'admin' && $file['uploaded_by'] != $_SESSION['user_id']) {
        http_response_code(403);
        echo "Permission denied";
        exit;
    }
    
    $filePath = $file['file_path'];
    
    if (!file_exists($filePath)) {
        http_response_code(404);
        echo "Physical file not found";
        exit;
    }
    
    // Get file info
    $fileSize = filesize($filePath);
    $fileName = $file['original_filename'];
    
    // Set headers for file download
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . $fileName . '"');
    header('Content-Length: ' . $fileSize);
    header('Content-Transfer-Encoding: binary');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    
    // Log download activity
    try {
        logActivity($conn, $_SESSION['user_id'], 'DOWNLOAD_FILE', 
                   "Downloaded file: " . $fileName . " (" . formatFileSize($fileSize) . ")", 'uploads', $fileId);
    } catch (Exception $e) {
        error_log("Failed to log download activity: " . $e->getMessage());
    }
    
    // Clear any previous output
    if (ob_get_level()) {
        ob_end_clean();
    }
    
    // Read and output file
    readfile($filePath);
    
} catch (Exception $e) {
    http_response_code(500);
    echo "Error: " . $e->getMessage();
}
exit;
?>