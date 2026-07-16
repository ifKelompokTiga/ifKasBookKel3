<?php
// GET /api/transactions/index.php
require_once __DIR__ . '/../../api/helpers.php';
allowMethods(['GET']);

$user = requireAuth();
$db   = getDB();

// Filters from query string
$type       = $_GET['type']        ?? '';
$walletId   = (int)($_GET['wallet_id']   ?? 0);
$categoryId = (int)($_GET['category_id'] ?? 0);
$dateFrom   = $_GET['date_from']   ?? '';
$dateTo     = $_GET['date_to']     ?? '';
$search     = $_GET['search']      ?? '';
$page       = max(1, (int)($_GET['page'] ?? 1));
$perPage    = min(100, max(1, (int)($_GET['per_page'] ?? 20)));

$where  = ['t.user_id = ?'];
$params = [$user['id']];

if ($type && in_array($type, ['income', 'expense', 'transfer'])) {
    $where[] = 't.type = ?'; $params[] = $type;
}
if ($walletId) {
    $where[] = '(t.wallet_id = ? OR t.to_wallet_id = ?)';
    $params[] = $walletId; $params[] = $walletId;
}
if ($categoryId) {
    $where[] = 't.category_id = ?'; $params[] = $categoryId;
}
if ($dateFrom) {
    $where[] = 't.date >= ?'; $params[] = $dateFrom;
}
if ($dateTo) {
    $where[] = 't.date <= ?'; $params[] = $dateTo;
}
if ($search) {
    $where[] = 't.note LIKE ?'; $params[] = '%' . $search . '%';
}

$whereSQL = implode(' AND ', $where);

// Count total
$countStmt = $db->prepare("SELECT COUNT(*) FROM transactions t WHERE {$whereSQL}");
$countStmt->execute($params);
$total = (int)$countStmt->fetchColumn();

// Summary
$sumStmt = $db->prepare(
    "SELECT
        SUM(CASE WHEN t.type='income'  THEN t.amount ELSE 0 END) AS total_income,
        SUM(CASE WHEN t.type='expense' THEN t.amount ELSE 0 END) AS total_expense
     FROM transactions t WHERE {$whereSQL}"
);
$sumStmt->execute($params);
$summary = $sumStmt->fetch();

// Data with pagination
$offset = ($page - 1) * $perPage;
$params[] = $perPage; $params[] = $offset;

$dataStmt = $db->prepare(
    "SELECT t.id, t.wallet_id, t.to_wallet_id, t.category_id, t.type,
            t.amount, t.note, t.date, t.created_at,
            w.name AS wallet_name,
            tw.name AS to_wallet_name,
            c.name AS category_name, c.icon AS category_icon, c.color AS category_color
     FROM transactions t
     LEFT JOIN wallets    w  ON w.id  = t.wallet_id
     LEFT JOIN wallets    tw ON tw.id = t.to_wallet_id
     LEFT JOIN categories c  ON c.id  = t.category_id
     WHERE {$whereSQL}
     ORDER BY t.date DESC, t.created_at DESC
     LIMIT ? OFFSET ?"
);
$dataStmt->execute($params);
$transactions = $dataStmt->fetchAll();

foreach ($transactions as &$tx) {
    $tx['amount'] = (float)$tx['amount'];
}

jsonResponse(true, [
    'transactions'  => $transactions,
    'total'         => $total,
    'page'          => $page,
    'per_page'      => $perPage,
    'total_pages'   => (int)ceil($total / $perPage),
    'total_income'  => (float)($summary['total_income']  ?? 0),
    'total_expense' => (float)($summary['total_expense'] ?? 0),
]);
