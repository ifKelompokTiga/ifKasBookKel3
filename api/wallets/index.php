<?php
// GET/POST /api/wallets/index.php
require_once __DIR__ . '/../../api/helpers.php';
allowMethods(['GET']);

$user = requireAuth();
$db   = getDB();

$stmt = $db->prepare(
    'SELECT id, name, type, balance, initial_balance, gradient, description, created_at
     FROM wallets WHERE user_id = ? AND is_active = 1 ORDER BY created_at ASC'
);
$stmt->execute([$user['id']]);
$wallets = $stmt->fetchAll();

// Cast numeric fields
foreach ($wallets as &$w) {
    $w['balance']         = (float)$w['balance'];
    $w['initial_balance'] = (float)$w['initial_balance'];
}

$total = array_sum(array_column($wallets, 'balance'));
jsonResponse(true, ['wallets' => $wallets, 'total_balance' => $total]);
