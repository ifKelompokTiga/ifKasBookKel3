<?php
// POST /api/categories/store.php
require_once __DIR__ . '/../../api/helpers.php';
allowMethods(['POST']);
$user = requireAuth();
$body = getRequestBody();
$err  = validateRequired($body, ['name', 'type']);
if ($err) jsonResponse(false, null, $err, 422);

$db   = getDB();
$stmt = $db->prepare(
    'INSERT INTO categories (user_id, name, type, icon, color) VALUES (?,?,?,?,?)'
);
$stmt->execute([
    $user['id'], clean($body['name']), $body['type'],
    clean($body['icon'] ?? '📦'), clean($body['color'] ?? '#6B7280')
]);
$id = $db->lastInsertId();
$row = $db->prepare('SELECT * FROM categories WHERE id = ?');
$row->execute([$id]);
jsonResponse(true, $row->fetch(), 'Kategori ditambahkan 🎉', 201);
