<?php
// DELETE /api/transactions/destroy.php
require_once __DIR__ . '/../../api/helpers.php';
allowMethods(['DELETE', 'POST']);

$user = requireAuth();
$body = getRequestBody();
$id   = (int)($body['id'] ?? $_GET['id'] ?? 0);
if (!$id) jsonResponse(false, null, 'ID transaksi wajib.', 422);

$db = getDB();
$stmt = $db->prepare('SELECT * FROM transactions WHERE id = ? AND user_id = ?');
$stmt->execute([$id, $user['id']]);
$tx = $stmt->fetch();
if (!$tx) jsonResponse(false, null, 'Transaksi tidak ditemukan.', 404);

$db->beginTransaction();
try {
    $amount = (float)$tx['amount'];
    // Reverse balance
    if ($tx['type'] === 'income')   $db->prepare('UPDATE wallets SET balance = balance - ? WHERE id = ?')->execute([$amount, $tx['wallet_id']]);
    if ($tx['type'] === 'expense')  $db->prepare('UPDATE wallets SET balance = balance + ? WHERE id = ?')->execute([$amount, $tx['wallet_id']]);
    if ($tx['type'] === 'transfer') {
        $db->prepare('UPDATE wallets SET balance = balance + ? WHERE id = ?')->execute([$amount, $tx['wallet_id']]);
        if ($tx['to_wallet_id']) $db->prepare('UPDATE wallets SET balance = balance - ? WHERE id = ?')->execute([$amount, $tx['to_wallet_id']]);
    }
    $db->prepare('DELETE FROM transactions WHERE id = ? AND user_id = ?')->execute([$id, $user['id']]);
    $db->commit();
} catch (Exception $e) {
    $db->rollBack();
    jsonResponse(false, null, 'Gagal menghapus: ' . $e->getMessage(), 500);
}

logActivity($user['id'], 'transaction_delete', "id={$id}");
jsonResponse(true, null, 'Transaksi berhasil dihapus.');
