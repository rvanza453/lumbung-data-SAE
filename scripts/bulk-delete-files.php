<?php
include_once '../includes/functions.php';
requireLogin();

header('Content-Type: application/json');

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['fileIds']) || !is_array($input['fileIds'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid file IDs']);
    exit;
}

$fileIds = $input['fileIds'];

if (empty($fileIds)) {
    echo json_encode(['success' => false, 'message' => 'No files selected']);
    exit;
}

$database = new Database();
$conn = $database->getConnection();

try {
    $conn->beginTransaction();
    
    $deletedCount = 0;
    $failedFiles = [];
    
    foreach ($fileIds as $fileId) {
        // Validate file ID
        if (!is_numeric($fileId)) {
            $failedFiles[] = "Invalid file ID: $fileId";
            continue;
        }
        
        // Get file info
        $stmt = $conn->prepare("SELECT * FROM uploads WHERE id = :id");
        $stmt->bindParam(':id', $fileId, PDO::PARAM_INT);
        $stmt->execute();
        $file = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$file) {
            $failedFiles[] = "File not found: ID $fileId";
            continue;
        }
        
        // Check permissions (non-admin users can only delete their own files)
        if ($_SESSION['role'] !== 'admin' && $file['uploaded_by'] != $_SESSION['user_id']) {
            $failedFiles[] = "Permission denied for file: " . $file['original_filename'];
            continue;
        }
        
        // Delete physical file
        $filePath = getAbsolutePath($file['file_path']);
        if (file_exists($filePath)) {
            if (!unlink($filePath)) {
                $failedFiles[] = "Failed to delete physical file: " . $file['original_filename'];
                continue;
            }
        }
        
        // Delete from database
        $deleteStmt = $conn->prepare("DELETE FROM uploads WHERE id = :id");
        $deleteStmt->bindParam(':id', $fileId, PDO::PARAM_INT);
        
        if ($deleteStmt->execute()) {
            $deletedCount++;
            
            // Log activity
            try {
                logActivity($conn, $_SESSION['user_id'], 'BULK_DELETE_FILES', 
                           "Deleted file: " . $file['original_filename'] . " (ID: $fileId)", 'uploads', $fileId);
            } catch (Exception $e) {
                error_log("Failed to log bulk delete activity: " . $e->getMessage());
            }
        } else {
            $failedFiles[] = "Failed to delete database record for: " . $file['original_filename'];
        }
    }
    
    $conn->commit();
    
    // Prepare response
    $response = [
        'success' => true,
        'deletedCount' => $deletedCount,
        'totalSelected' => count($fileIds)
    ];
    
    if (!empty($failedFiles)) {
        $response['warnings'] = $failedFiles;
        $response['message'] = "Deleted $deletedCount files, but some operations failed.";
    } else {
        $response['message'] = "Successfully deleted $deletedCount files.";
    }
    
    echo json_encode($response);
    
} catch (PDOException $e) {
    $conn->rollBack();
    echo json_encode([
        'success' => false, 
        'message' => 'Database error: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    $conn->rollBack();
    echo json_encode([
        'success' => false, 
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>