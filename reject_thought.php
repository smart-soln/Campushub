<?php
session_start();

// Verify admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin_login.php");
    exit();
}

// Database configuration
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'campushub1';

try {
    $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
    
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    
    $conn->set_charset("utf8mb4");
} catch (Exception $e) {
    die("Database connection error. Please try again later.");
}

// Reject the thought
if (isset($_GET['id'])) {
    $thought_id = intval($_GET['id']);
    
    try {
        $stmt = $conn->prepare("UPDATE student_thoughts SET status = 'rejected', reviewed_by = ?, reviewed_at = NOW() WHERE id = ?");
        $stmt->bind_param("ii", $_SESSION['admin_id'], $thought_id);
        $stmt->execute();
        $stmt->close();
        
        $_SESSION['message'] = "Thought rejected successfully!";
    } catch (Exception $e) {
        $_SESSION['error'] = "Error rejecting thought: " . $e->getMessage();
    }
}

header("Location: admin_dashboard.php");
exit();
?>