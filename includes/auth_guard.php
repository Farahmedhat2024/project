<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function require_login(): void
{
    if (empty($_SESSION['user_id'])) {
        header('Location: /nti/final_project/PROJECT/login.php');
        exit;
    }
}

function require_admin(): void
{
    require_login();

    if (($_SESSION['role'] ?? 'user') !== 'admin') {
        header('Location: /nti/final_project/PROJECT/index.php');
        exit;
    }
}
