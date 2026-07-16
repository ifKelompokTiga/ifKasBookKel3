<?php
// GET /api/auth/me.php
require_once __DIR__ . '/../../api/helpers.php';
allowMethods(['GET']);

if (empty($_SESSION['user_id'])) {
    jsonResponse(false, null, 'Tidak terautentikasi.', 401);
}

$db   = getDB();
$stmt = $db->prepare(
    'SELECT u.id, u.name, u.email, u.role, u.avatar, u.created_at,
            s.theme, s.currency, s.low_balance_alert
     FROM users u
     LEFT JOIN user_settings s ON s.user_id = u.id
     WHERE u.id = ? AND u.is_active = 1'
);
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

if (!$user) {
    session_destroy();
    jsonResponse(false, null, 'Sesi tidak valid.', 401);
}

jsonResponse(true, $user);
