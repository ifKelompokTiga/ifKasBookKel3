<?php
// POST /api/wallets/store.php
require_once __DIR__ . '/../../api/helpers.php';
allowMethods(['POST']);

$user = requireAuth();
$body = getRequestBody();
$err  = validateRequired($body, ['name', 'type']);
if ($err) jsonResponse(false, null, $err, 422);

$validTypes = ['cash', 'bank', 'ewallet', 'savings', 'other'];
if (!in_array($body['type'], $validTypes)) {
    jsonResponse(false, null, 'Jenis dompet tidak valid.', 422);
}

$initBalance = max(0, (float)($body['initial_balance'] ?? 0));
$gradient    = clean($body['gradient'] ?? 'linear-gradient(135deg,#16A34A,#22C55E)');
$desc        = clean($body['description'] ?? '');

$db   = getDB();
$stmt = $db->prepare(
    'INSERT INTO wallets (user_id, name, type, balance, initial_balance, gradient, description)
     VALUES (?, ?, ?, ?, ?, ?, ?)'
);
$stmt->execute([
    $user['id'], clean($body['name']), $body['type'],
    $initBalance, $initBalance, $gradient, $desc
]);
$id = (int)$db->lastInsertId();

logActivity($user['id'], 'wallet_create', "id={$id}, name={$body['name']}");

$stmt2 = $db->prepare('SELECT * FROM wallets WHERE id = ?');
$stmt2->execute([$id]);
$wallet = $stmt2->fetch();
$wallet['balance']         = (float)$wallet['balance'];
$wallet['initial_balance'] = (float)$wallet['initial_balance'];

jsonResponse(true, $wallet, 'Dompet berhasil ditambahkan 🎉', 201);
