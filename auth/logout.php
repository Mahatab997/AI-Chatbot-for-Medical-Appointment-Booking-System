<?php
// logout.php - end user session and redirect to homepage
include "../config/database.php";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/* Clear session data */
$_SESSION = array();

/* Remove session cookie */
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

/* Destroy session */
session_destroy();

/* Clear remember-me cookie */
if (isset($_COOKIE['user_email'])) {
    setcookie('user_email', '', time() - 3600, "/");
}

/* FINAL REDIRECT → HOME PAGE */
header("Location: ../index.php");
exit();
?>