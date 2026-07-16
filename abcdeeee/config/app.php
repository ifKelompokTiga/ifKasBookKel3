<?php
// =====================================================
// BukuKas Universal — App Configuration
// =====================================================

define('APP_NAME',    'BukuKas Universal');
define('APP_VERSION', '2.0.0');
define('APP_URL',     'http://localhost/kasbuk');
define('SESSION_NAME','bukukas_session');
define('SESSION_LIFETIME', 86400 * 7); // 7 days

// Start session with secure settings
function startAppSession(): void {
    if (session_status() === PHP_SESSION_NONE) {
        session_name(SESSION_NAME);
        session_set_cookie_params([
            'lifetime' => SESSION_LIFETIME,
            'path'     => '/',
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
        session_start();
    }
}

// Set CORS + JSON headers for API endpoints
function setApiHeaders(): void {
    header('Content-Type: application/json; charset=utf-8');
    header('X-Content-Type-Options: nosniff');
}

// Bootstrap: load all configs needed
require_once __DIR__ . '/database.php';
startAppSession();
