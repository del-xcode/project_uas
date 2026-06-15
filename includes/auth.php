<?php
require_once __DIR__ . '/../config/app.php';

function require_login(): void
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    if (empty($_SESSION['user_id'])) {
        header('Location: ' . app_url('login.php'));
        exit;
    }
}

function require_role(string $role): void
{
    require_login();

    if (($_SESSION['role'] ?? null) !== $role) {
        http_response_code(403);
        exit('Akses ditolak');
    }
}