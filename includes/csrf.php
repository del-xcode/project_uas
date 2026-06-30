<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

/**
 * Generate CSRF token and store it in session if not present.
 */
function generate_csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Validate given token against token stored in session.
 */
function validate_csrf_token(?string $token): bool
{
    if (empty($_SESSION['csrf_token'])) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], (string)$token);
}

/**
 * Helper to print hidden CSRF input field.
 */
function csrf_input(): string
{
    $token = generate_csrf_token();
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token) . '">';
}

/**
 * Validate CSRF token for POST requests and stop execution if invalid.
 */
function require_csrf(): void
{
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $token = $_POST['csrf_token'] ?? null;
        if (!validate_csrf_token($token)) {
            http_response_code(403);
            exit('Error: Permintaan tidak valid (CSRF Token Mismatch).');
        }
    }
}
