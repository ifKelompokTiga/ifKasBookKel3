<?php
// DELETE /api/wallets/destroy.php
require_once __DIR__ . '/../../api/helpers.php';
allowMethods(['DELETE', 'POST']);

$user = requireAuth();
$body = getRequestBody();
$id   = (int)($body['id'] ?? $_GET['id'] ?? 0);
if (!$id) jsonResponse(false, null, 'ID dompet wajib diisi.', 422);

$db = getDB();
$own = $db->prepare('SELECT id FROM wallets WHERE id = ? AND user_id = ?');
$own->execute([$id, $user['id']]);
if (!$own->fetch()) jsonResponse(false, null, 'Dompet tidak ditemukan.', 404);

// Soft delete: mark is_active = 0, also delete transactions
$db->prepare('UPDATE wallets SET is_active = 0 WHERE id = ? AND user_id = ?')->execute([$id, $user['id']]);
$db->prepare('DELETE FROM transactions WHERE wallet_id = ? AND user_id = ?')->execute([$id, $user['id']]);

logActivity($user['id'], 'wallet_delete', "id={$id}");
jsonResponse(true, null, 'Dompet berhasil dihapus.');
