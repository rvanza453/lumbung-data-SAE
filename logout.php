<?php
session_start();
include_once 'includes/functions.php';

if (isset($_SESSION['user_id'])) {
    $database = new Database();
    $conn = $database->getConnection();
    
    $user_id = $_SESSION['user_id'];
    $username = $_SESSION['username'] ?? 'unknown';
    
    // Log logout activity
    try {
        logActivity($conn, $user_id, 'LOGOUT', 'User logged out');
    } catch (Exception $e) {
        // Handle silently
    }
    
    // Destroy session
    session_destroy();
}

// Redirect to login
header('Location: login.php');
exit();
?>