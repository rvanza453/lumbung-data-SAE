<?php
include_once 'includes/functions.php';
requireLogin();

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
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
        echo "File not found in database";
        exit;
    }
    
    // Check permissions
    if ($_SESSION['role'] !== 'admin' && $file['uploaded_by'] != $_SESSION['user_id']) {
        echo "Permission denied";
        exit;
    }
    
    $filePath = $file['file_path'];
    
    if (!file_exists($filePath)) {
        echo "Physical file not found: " . $filePath;
        exit;
    }
    
    $content = file_get_contents($filePath);
    if ($content === false) {
        echo "Cannot read file";
        exit;
    }
    
    // Try to format JSON
    $json = json_decode($content, true);
    if (json_last_error() === JSON_ERROR_NONE) {
        header('Content-Type: application/json');
        echo json_encode($json, JSON_PRETTY_PRINT);
    } else {
        header('Content-Type: text/plain');
        echo $content;
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>