<?php
// Prevent multiple session start
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Show errors (for development)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database Credentials
$host = "localhost";
$db   = "medical_ai_pro";
$user = "root";
$pass = "";

// PDO Connection
try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);

    // PDO Settings
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Database Connection Failed: " . $e->getMessage());
}

// MySQLi Connection for legacy code
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_errno) {
    die("MySQLi Connection Failed: " . $conn->connect_error);
}
?>