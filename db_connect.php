<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'campushub1');

// Establish database connection
try {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    
    $conn->set_charset("utf8mb4");
    
} catch (Exception $e) {
    error_log($e->getMessage());
    die("Database connection error. Please try again later.");
}

// Function to safely execute prepared statements
function executeQuery($sql, $params = [], $types = '') {
    global $conn;
    
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    
    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }
    
    return $stmt;
}

// Function to get system settings
function getSetting($key) {
    try {
        $stmt = executeQuery(
            "SELECT setting_value FROM system_settings WHERE setting_key = ?", 
            [$key], 
            's'
        );
        
        $result = $stmt->get_result();
        return $result->num_rows > 0 ? $result->fetch_assoc()['setting_value'] : null;
        
    } catch (Exception $e) {
        error_log($e->getMessage());
        return null;
    }
}

// Function to get department list
function getDepartments() {
    try {
        $stmt = executeQuery("SELECT id, name, code FROM departments ORDER BY name");
        $result = $stmt->get_result();
        $departments = [];
        
        while ($row = $result->fetch_assoc()) {
            $departments[] = $row;
        }
        
        return $departments;
    } catch (Exception $e) {
        error_log($e->getMessage());
        return [];
    }
}
?>