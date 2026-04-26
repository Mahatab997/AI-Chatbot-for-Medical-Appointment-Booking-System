<?php
include "../config/database.php";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != "patient") {
    header("Location: ../auth/login.php");
    exit();
}

$uid = $_SESSION['user_id'];

if (!isset($_GET['id'])) {
    die("Invalid request");
}

$appointment_id = (int)$_GET['id'];

// Make sure this appointment belongs to the logged-in patient
$stmt = $pdo->prepare("UPDATE appointments SET status='cancelled' WHERE id=? AND user_id=?");
$stmt->execute([$appointment_id, $uid]);

header("Location: my_appointments.php");
exit();