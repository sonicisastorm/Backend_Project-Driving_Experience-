<?php
/**
 * Database Configuration File
 * Replace these values with your Alwaysdata database credentials
 */

define('DB_HOST', 'mysql-youraccount.alwaysdata.net');
define('DB_NAME', 'youraccount_driving');
define('DB_USER', 'youraccount_student');  
define('DB_PASS', 'your_password_here');

// Create database connection using mysqli
function getDatabaseConnection() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    // Set charset to utf8mb4 for proper character handling
    $conn->set_charset("utf8mb4");
    
    return $conn;
}

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Set timezone
date_default_timezone_set('Europe/Paris');

// Include security helpers for data anonymization
require_once __DIR__ . '/security_helpers.php';
?>