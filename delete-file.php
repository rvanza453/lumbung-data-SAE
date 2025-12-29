<?php
include_once 'includes/functions.php';
requireLogin();

header('Content-Type: application/json');

if ($_SESSION['role'] != 'admin') {
    echo json_encode(['success' => false, 'message' => 'Akses ditolak!']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] != 'POST' || !isset($_POST['id']) || !is_numeric($_POST['id'])) {
    echo json_encode(['success' => false, 'message' => 'Request tidak valid!']);
    exit();
}

$fileId = (int)$_POST['id'];

$database = new Database();
$conn = $database->getConnection();

try {
    // Get file information first
    $query = "SELECT file_path FROM uploads WHERE id = :id";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':id', $fileId);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        $file = $stmt->fetch(PDO::FETCH_ASSOC);
        $filePath = $file['file_path'];
        
        // Delete from database
        $deleteQuery = "DELETE FROM uploads WHERE id = :id";
        $deleteStmt = $conn->prepare($deleteQuery);
        $deleteStmt->bindParam(':id', $fileId);
        
        if ($deleteStmt->execute()) {
            // Delete physical file
            if (file_exists($filePath)) {
                unlink($filePath);
            }
            
            // Log file deletion activity
            try {
                logActivity($conn, $_SESSION['user_id'], 'DELETE_FILE', 
                          "Deleted file: " . basename($filePath), 'uploads', $fileId);
            } catch (Exception $e) {
                error_log("Failed to log delete file activity: " . $e->getMessage());
            }
            
            echo json_encode(['success' => true, 'message' => 'File berhasil dihapus!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Gagal menghapus dari database!']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'File tidak ditemukan!']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Error database: ' . $e->getMessage()]);
}
?>