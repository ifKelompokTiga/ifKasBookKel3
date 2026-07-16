<?php
// GET /api/admin/stats.php
require_once __DIR__ . '/../../api/helpers.php';
allowMethods(['GET']);
$admin = requireAdmin();
$db    = getDB();

$users        = $db->query('SELECT COUNT(*) FROM users')->fetchColumn();
$activeUsers  = $db->query("SELECT COUNT(*) FROM users WHERE is_active=1")->fetchColumn();
$wallets      = $db->query('SELECT COUNT(*) FROM wallets WHERE is_active=1')->fetchColumn();
$txCount      = $db->query('SELECT COUNT(*) FROM transactions')->fetchColumn();

$txSums = $db->query(
    "SELECT SUM(CASE WHEN type='income'  THEN amount ELSE 0 END) AS income,
            SUM(CASE WHEN type='expense' THEN amount ELSE 0 END) AS expense
     FROM transactions"
)->fetch();

$totalBalance = $db->query('SELECT SUM(balance) FROM wallets WHERE is_active=1')->fetchColumn();

// New users this month
$newUsersMonth = $db->query(
    "SELECT COUNT(*) FROM users WHERE MONTH(created_at)=MONTH(NOW()) AND YEAR(created_at)=YEAR(NOW())"
)->fetchColumn();

// Recent activity
$recentActivity = $db->query(
    "SELECT l.*, u.name AS user_name
     FROM activity_log l
     LEFT JOIN users u ON u.id = l.user_id
     ORDER BY l.created_at DESC LIMIT 20"
)->fetchAll();

// Transactions per day (last 30 days)
$daily = $db->query(
    "SELECT date, COUNT(*) AS count,
            SUM(CASE WHEN type='income' THEN amount ELSE 0 END) AS income,
            SUM(CASE WHEN type='expense' THEN amount ELSE 0 END) AS expense
     FROM transactions
     WHERE date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
     GROUP BY date ORDER BY date ASC"
)->fetchAll();

// Top users by transaction count
$topUsers = $db->query(
    "SELECT u.id, u.name, u.email, u.role, COUNT(t.id) AS tx_count
     FROM users u
     LEFT JOIN transactions t ON t.user_id = u.id
     GROUP BY u.id ORDER BY tx_count DESC LIMIT 10"
)->fetchAll();

jsonResponse(true, [
    'users'          => (int)$users,
    'active_users'   => (int)$activeUsers,
    'wallets'        => (int)$wallets,
    'tx_count'       => (int)$txCount,
    'total_income'   => (float)($txSums['income'] ?? 0),
    'total_expense'  => (float)($txSums['expense'] ?? 0),
    'total_balance'  => (float)($totalBalance ?? 0),
    'new_users_month'=> (int)$newUsersMonth,
    'recent_activity'=> $recentActivity,
    'daily_stats'    => $daily,
    'top_users'      => $topUsers,
]);
