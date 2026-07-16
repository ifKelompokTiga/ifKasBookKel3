<?php
// POST /api/auth/login.php
require_once __DIR__ . '/../../api/helpers.php';
allowMethods(['POST']);

$body  = getRequestBody();
$err   = validateRequired($body, ['email', 'password']);
if ($err) jsonResponse(false, null, $err, 422);

$email    = strtolower(trim($body['email']));
$password = $body['password'];

$db   = getDB();
$stmt = $db->prepare('SELECT * FROM users WHERE email = ? LIMIT 1');
$stmt->execute([$email]);
$user = $stmt->fetch();

if (!$user || !password_verify($password, $user['password'])) {
    jsonResponse(false, null, 'Email atau password salah.', 401);
}
if (!$user['is_active']) {
    jsonResponse(false, null, 'Akun Anda telah dinonaktifkan. Hubungi admin.', 403);
}

// Regenerate session for security
session_regenerate_id(true);
$_SESSION['user_id'] = $user['id'];

logActivity($user['id'], 'login', "email={$email}");

jsonResponse(true, [
    'id'    => $user['id'],
    'name'  => $user['name'],
    'email' => $user['email'],
    'role'  => $user['role'],
], 'Login berhasil. Selamat datang, ' . $user['name'] . '!');
