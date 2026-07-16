<?php
// POST /api/transactions/store.php
require_once __DIR__ . '/../../api/helpers.php';
allowMethods(['POST']);

$user = requireAuth();
$body = getRequestBody();

$err = validateRequired($body, ['wallet_id', 'type', 'amount', 'date']);
if ($err) jsonResponse(false, null, $err, 422);

$validTypes = ['income', 'expense', 'transfer'];
if (!in_array($body['type'], $validTypes)) {
    jsonResponse(false, null, 'Jenis transaksi tidak valid.', 422);
}

$amount   = (float)$body['amount'];
if ($amount <= 0) jsonResponse(false, null, 'Nominal harus lebih dari 0.', 422);

$walletId  = (int)$body['wallet_id'];
$toWalletId = ($body['type'] === 'transfer') ? (int)($body['to_wallet_id'] ?? 0) : null;
$categoryId = ($body['type'] !== 'transfer') ? (int)($body['category_id'] ?? 0) : null;
$note       = clean($body['note'] ?? '');
$date       = $body['date'];

$db = getDB();

// Verify wallet ownership
$wStmt = $db->prepare('SELECT id, balance FROM wallets WHERE id = ? AND user_id = ? AND is_active = 1');
$wStmt->execute([$walletId, $user['id']]);
$wallet = $wStmt->fetch();
if (!$wallet) jsonResponse(false, null, 'Dompet tidak ditemukan.', 404);

if ($body['type'] === 'transfer') {
    if (!$toWalletId || $toWalletId === $walletId) {
        jsonResponse(false, null, 'Dompet tujuan harus berbeda dari dompet asal.', 422);
    }
    $tw = $db->prepare('SELECT id FROM wallets WHERE id = ? AND user_id = ? AND is_active = 1');
    $tw->execute([$toWalletId, $user['id']]);
    if (!$tw->fetch()) jsonResponse(false, null, 'Dompet tujuan tidak ditemukan.', 404);
}

// Begin transaction
$db->beginTransaction();
try {
    // Insert transaction record
    $ins = $db->prepare(
        'INSERT INTO transactions (user_id, wallet_id, to_wallet_id, category_id, type, amount, note, date)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
    );
    $ins->execute([$user['id'], $walletId, $toWalletId, $categoryId ?: null, $body['type'], $amount, $note, $date]);
    $txId = (int)$db->lastInsertId();

    // Update wallet balances
    if ($body['type'] === 'income') {
        $db->prepare('UPDATE wallets SET balance = balance + ? WHERE id = ?')->execute([$amount, $walletId]);
    } elseif ($body['type'] === 'expense') {
        $db->prepare('UPDATE wallets SET balance = balance - ? WHERE id = ?')->execute([$amount, $walletId]);
    } elseif ($body['type'] === 'transfer') {
        $db->prepare('UPDATE wallets SET balance = balance - ? WHERE id = ?')->execute([$amount, $walletId]);
        $db->prepare('UPDATE wallets SET balance = balance + ? WHERE id = ?')->execute([$amount, $toWalletId]);
    }

    $db->commit();
} catch (Exception $e) {
    $db->rollBack();
    jsonResponse(false, null, 'Gagal menyimpan transaksi: ' . $e->getMessage(), 500);
}

logActivity($user['id'], 'transaction_create', "id={$txId}, type={$body['type']}, amount={$amount}");

// Return full transaction data
$ret = $db->prepare(
    'SELECT t.*, w.name AS wallet_name, tw.name AS to_wallet_name,
            c.name AS category_name, c.icon AS category_icon, c.color AS category_color
     FROM transactions t
     LEFT JOIN wallets w ON w.id = t.wallet_id
     LEFT JOIN wallets tw ON tw.id = t.to_wallet_id
     LEFT JOIN categories c ON c.id = t.category_id
     WHERE t.id = ?'
);
$ret->execute([$txId]);
$tx = $ret->fetch();
$tx['amount'] = (float)$tx['amount'];

jsonResponse(true, $tx, 'Transaksi berhasil dicatat 🎉', 201);
