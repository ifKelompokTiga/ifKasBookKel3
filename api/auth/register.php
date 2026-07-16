<?php
// POST /api/auth/register.php
require_once __DIR__ . '/../../api/helpers.php';
allowMethods(['POST']);

$body = getRequestBody();

// Validate
$err = validateRequired($body, ['name', 'email', 'password']);
if ($err) jsonResponse(false, null, $err, 422);

$name     = clean($body['name']);
$email    = strtolower(trim($body['email']));
$password = $body['password'];

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    jsonResponse(false, null, 'Format email tidak valid.', 422);
}
if (strlen($password) < 6) {
    jsonResponse(false, null, 'Password minimal 6 karakter.', 422);
}

$db = getDB();

// Check if email exists
$check = $db->prepare('SELECT id FROM users WHERE email = ?');
$check->execute([$email]);
if ($check->fetch()) {
    jsonResponse(false, null, 'Email sudah terdaftar.', 409);
}

// First user becomes admin
$countStmt = $db->query('SELECT COUNT(*) as cnt FROM users');
$count     = $countStmt->fetch()['cnt'];
$role      = ($count === 0) ? 'admin' : 'user';

// Insert user
$hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
$stmt = $db->prepare(
    'INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)'
);
$stmt->execute([$name, $email, $hash, $role]);
$userId = (int)$db->lastInsertId();

// Create default user_settings
$db->prepare('INSERT INTO user_settings (user_id) VALUES (?)')->execute([$userId]);

// Create default wallet
$db->prepare(
    "INSERT INTO wallets (user_id, name, type, balance, initial_balance, gradient, description)
     VALUES (?, 'Kas Utama', 'cash', 0, 0, 'linear-gradient(135deg,#16A34A,#22C55E)', 'Dompet utama')"
)->execute([$userId]);

// Start session
$_SESSION['user_id'] = $userId;

logActivity($userId, 'register', "email={$email}, role={$role}");

jsonResponse(true, [
    'id'    => $userId,
    'name'  => $name,
    'email' => $email,
    'role'  => $role,
], $role === 'admin' ? 'Selamat datang, Admin! 🎉' : 'Registrasi berhasil! 🎉');
