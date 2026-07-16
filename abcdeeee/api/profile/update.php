<?php
// PUT /api/profile/update.php
require_once __DIR__ . '/../../api/helpers.php';
allowMethods(['PUT', 'POST']);
$user = requireAuth(); $body = getRequestBody();
$err  = validateRequired($body, ['name', 'email']);
if ($err) jsonResponse(false, null, $err, 422);

$email = strtolower(trim($body['email']));
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) jsonResponse(false, null, 'Format email tidak valid.', 422);

$db = getDB();
// Check email conflict (excluding current user)
$chk = $db->prepare('SELECT id FROM users WHERE email = ? AND id != ?');
$chk->execute([$email, $user['id']]);
if ($chk->fetch()) jsonResponse(false, null, 'Email sudah digunakan akun lain.', 409);

$db->prepare('UPDATE users SET name=?, email=? WHERE id=?')
   ->execute([clean($body['name']), $email, $user['id']]);

// Update settings
if (isset($body['theme'])) {
    $db->prepare('UPDATE user_settings SET theme=?, currency=?, low_balance_alert=? WHERE user_id=?')
       ->execute([$body['theme'], $body['currency']??'IDR', (float)($body['low_balance_alert']??100000), $user['id']]);
}

// Change password
if (!empty($body['new_password'])) {
    if (strlen($body['new_password']) < 6) jsonResponse(false, null, 'Password baru minimal 6 karakter.', 422);
    $hash = password_hash($body['new_password'], PASSWORD_BCRYPT, ['cost'=>12]);
    $db->prepare('UPDATE users SET password=? WHERE id=?')->execute([$hash, $user['id']]);
}

jsonResponse(true, null, 'Profil berhasil disimpan ✓');
