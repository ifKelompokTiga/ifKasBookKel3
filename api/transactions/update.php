<?php
// PUT /api/transactions/update.php
require_once __DIR__ . '/../../api/helpers.php';
allowMethods(['PUT', 'POST']);

$user = requireAuth();
$body = getRequestBody();
$id   = (int)($body['id'] ?? 0);
if (!$id) jsonResponse(false, null, 'ID transaksi wajib.', 422);

$db = getDB();

// Get old transaction
$old = $db->prepare(
    'SELECT * FROM transactions WHERE id = ? AND user_id = ?'
);
$old->execute([$id, $user['id']]);
$oldTx = $old->fetch();
if (!$oldTx) jsonResponse(false, null, 'Transaksi tidak ditemukan.', 404);

$err = validateRequired($body, ['wallet_id', 'type', 'amount', 'date']);
if ($err) jsonResponse(false, null, $err, 422);

$amount      = (float)$body['amount'];
$walletId    = (int)$body['wallet_id'];
$toWalletId  = ($body['type'] === 'transfer') ? (int)($body['to_wallet_id'] ?? 0) : null;
$categoryId  = ($body['type'] !== 'transfer') ? (int)($body['category_id'] ?? 0) : null;

$db->beginTransaction();
try {
    // Reverse old balance effect
    $oldAmt = (float)$oldTx['amount'];
    if ($oldTx['type'] === 'income')   $db->prepare('UPDATE wallets SET balance = balance - ? WHERE id = ?')->execute([$oldAmt, $oldTx['wallet_id']]);
    if ($oldTx['type'] === 'expense')  $db->prepare('UPDATE wallets SET balance = balance + ? WHERE id = ?')->execute([$oldAmt, $oldTx['wallet_id']]);
    if ($oldTx['type'] === 'transfer') {
        $db->prepare('UPDATE wallets SET balance = balance + ? WHERE id = ?')->execute([$oldAmt, $oldTx['wallet_id']]);
        if ($oldTx['to_wallet_id']) $db->prepare('UPDATE wallets SET balance = balance - ? WHERE id = ?')->execute([$oldAmt, $oldTx['to_wallet_id']]);
    }

    // Apply new balance effect
    if ($body['type'] === 'income')   $db->prepare('UPDATE wallets SET balance = balance + ? WHERE id = ?')->execute([$amount, $walletId]);
    if ($body['type'] === 'expense')  $db->prepare('UPDATE wallets SET balance = balance - ? WHERE id = ?')->execute([$amount, $walletId]);
    if ($body['type'] === 'transfer') {
        $db->prepare('UPDATE wallets SET balance = balance - ? WHERE id = ?')->execute([$amount, $walletId]);
        if ($toWalletId) $db->prepare('UPDATE wallets SET balance = balance + ? WHERE id = ?')->execute([$amount, $toWalletId]);
    }

    // Update record
    $upd = $db->prepare(
        'UPDATE transactions SET wallet_id=?, to_wallet_id=?, category_id=?, type=?, amount=?, note=?, date=?
         WHERE id=? AND user_id=?'
    );
    $upd->execute([
        $walletId, $toWalletId, $categoryId ?: null, $body['type'],
        $amount, clean($body['note'] ?? ''), $body['date'], $id, $user['id']
    ]);

    $db->commit();
} catch (Exception $e) {
    $db->rollBack();
    jsonResponse(false, null, 'Gagal memperbarui: ' . $e->getMessage(), 500);
}

logActivity($user['id'], 'transaction_update', "id={$id}");

$ret = $db->prepare(
    'SELECT t.*, w.name AS wallet_name, tw.name AS to_wallet_name,
            c.name AS category_name, c.icon AS category_icon, c.color AS category_color
     FROM transactions t
     LEFT JOIN wallets w ON w.id = t.wallet_id
     LEFT JOIN wallets tw ON tw.id = t.to_wallet_id
     LEFT JOIN categories c ON c.id = t.category_id
     WHERE t.id = ?'
);
$ret->execute([$id]);
$tx = $ret->fetch();
$tx['amount'] = (float)$tx['amount'];
jsonResponse(true, $tx, 'Transaksi berhasil diperbarui.');
