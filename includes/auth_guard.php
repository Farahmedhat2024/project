<?php


if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


function require_login() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit;
    }
}



function require_admin() {
    require_login(); // لازم يكون عامل login الأول
    if (($_SESSION['role'] ?? 'user') !== 'admin') {
        header("Location: index.php");
        exit;
    }
}