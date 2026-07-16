<?php
// GET/POST /api/admin/users.php
require_once __DIR__ . '/../../api/helpers.php';

$admin = requireAdmin();
$db    = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $search  = $_GET['search'] ?? '';
    $role    = $_GET['role']   ?? '';
    $page    = max(1, (int)($_GET['page'] ?? 1));
    $perPage = min(50, (int)($_GET['per_page'] ?? 20));

    $where  = ['1=1']; $params = [];
    if ($search) { $where[] = '(u.name LIKE ? OR u.email LIKE ?)'; $params[] = "%$search%"; $params[] = "%$search%"; }
    if ($role && in_array($role, ['admin','user'])) { $where[] = 'u.role = ?'; $params[] = $role; }

    $sql = 'SELECT u.id, u.name, u.email, u.role, u.is_active, u.created_at,
                   COUNT(t.id) AS tx_count,
                   (SELECT SUM(balance) FROM wallets w WHERE w.user_id=u.id AND w.is_active=1) AS total_balance
            FROM users u
            LEFT JOIN transactions t ON t.user_id = u.id
            WHERE ' . implode(' AND ', $where) . '
            GROUP BY u.id ORDER BY u.created_at DESC
            LIMIT ? OFFSET ?';

    $countSql = 'SELECT COUNT(*) FROM users u WHERE ' . implode(' AND ', $where);
    $cStmt = $db->prepare($countSql); $cStmt->execute($params);
    $total = (int)$cStmt->fetchColumn();

    $params[] = $perPage; $params[] = ($page-1)*$perPage;
    $stmt = $db->prepare($sql); $stmt->execute($params);
    $users = $stmt->fetchAll();
    foreach ($users as &$u) {
        $u['tx_count']     = (int)$u['tx_count'];
        $u['total_balance']= (float)($u['total_balance'] ?? 0);
        $u['is_active']    = (bool)$u['is_active'];
    }
    jsonResponse(true, ['users' => $users, 'total' => $total, 'page' => $page, 'per_page' => $perPage]);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    allowMethods(['POST']);
    $body   = getRequestBody();
    $userId = (int)($body['user_id'] ?? 0);
    $action = $body['action'] ?? '';

    if (!$userId) jsonResponse(false, null, 'user_id wajib.', 422);
    if ($userId === $admin['id'] && $action === 'deactivate') {
        jsonResponse(false, null, 'Tidak bisa menonaktifkan akun sendiri.', 403);
    }

    if ($action === 'activate') {
        $db->prepare('UPDATE users SET is_active=1 WHERE id=?')->execute([$userId]);
        logActivity($admin['id'], 'admin_activate_user', "target={$userId}");
        jsonResponse(true, null, 'Akun diaktifkan.');
    } elseif ($action === 'deactivate') {
        $db->prepare('UPDATE users SET is_active=0 WHERE id=?')->execute([$userId]);
        logActivity($admin['id'], 'admin_deactivate_user', "target={$userId}");
        jsonResponse(true, null, 'Akun dinonaktifkan.');
    } elseif ($action === 'make_admin') {
        $db->prepare('UPDATE users SET role=? WHERE id=?')->execute(['admin', $userId]);
        logActivity($admin['id'], 'admin_set_role', "target={$userId}, role=admin");
        jsonResponse(true, null, 'Role diubah menjadi Admin.');
    } elseif ($action === 'make_user') {
        $db->prepare('UPDATE users SET role=? WHERE id=?')->execute(['user', $userId]);
        logActivity($admin['id'], 'admin_set_role', "target={$userId}, role=user");
        jsonResponse(true, null, 'Role diubah menjadi User.');
    } elseif ($action === 'delete') {
        if ($userId === $admin['id']) jsonResponse(false, null, 'Tidak bisa menghapus diri sendiri.', 403);
        $db->prepare('DELETE FROM users WHERE id=?')->execute([$userId]);
        logActivity($admin['id'], 'admin_delete_user', "target={$userId}");
        jsonResponse(true, null, 'Akun dihapus permanen.');
    } else {
        jsonResponse(false, null, 'Action tidak dikenali.', 422);
    }
}

jsonResponse(false, null, 'Method not allowed.', 405);
