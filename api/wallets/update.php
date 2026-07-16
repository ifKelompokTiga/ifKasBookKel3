<?php
// PUT /api/wallets/update.php
require_once __DIR__ . '/../../api/helpers.php';
allowMethods(['PUT', 'POST']);

$user = requireAuth();
$body = getRequestBody();
$id   = (int)($body['id'] ?? $_GET['id'] ?? 0);

if (!$id) jsonResponse(false, null, 'ID dompet wajib diisi.', 422);
$err = validateRequired($body, ['name', 'type']);
if ($err) jsonResponse(false, null, $err, 422);

$db = getDB();

// Verify ownership
$own = $db->prepare('SELECT id, initial_balance FROM wallets WHERE id = ? AND user_id = ? AND is_active = 1');
$own->execute([$id, $user['id']]);
$wallet = $own->fetch();
if (!$wallet) jsonResponse(false, null, 'Dompet tidak ditemukan.', 404);

$newInit = max(0, (float)($body['initial_balance'] ?? $wallet['initial_balance']));
$diff    = $newInit - (float)$wallet['initial_balance'];

$stmt = $db->prepare(
    'UPDATE wallets SET name=?, type=?, initial_balance=?, balance=balance+?,
     gradient=?, description=? WHERE id=? AND user_id=?'
);
$stmt->execute([
    clean($body['name']), $body['type'], $newInit, $diff,
    clean($body['gradient'] ?? ''), clean($body['description'] ?? ''),
    $id, $user['id']
]);

logActivity($user['id'], 'wallet_update', "id={$id}");

$stmt2 = $db->prepare('SELECT * FROM wallets WHERE id = ?');
$stmt2->execute([$id]);
$w = $stmt2->fetch();
$w['balance'] = (float)$w['balance']; $w['initial_balance'] = (float)$w['initial_balance'];
jsonResponse(true, $w, 'Dompet berhasil diperbarui.');
