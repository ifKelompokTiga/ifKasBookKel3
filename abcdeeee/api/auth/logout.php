<?php
// POST /api/auth/logout.php
require_once __DIR__ . '/../../api/helpers.php';
allowMethods(['POST']);

if (!empty($_SESSION['user_id'])) {
    logActivity($_SESSION['user_id'], 'logout', '');
}

$_SESSION = [];
if (ini_get('session.use_cookies')) {
    $p = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
}
session_destroy();

jsonResponse(true, null, 'Berhasil keluar.');
