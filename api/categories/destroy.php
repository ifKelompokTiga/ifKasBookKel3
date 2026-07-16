<?php
// DELETE /api/categories/destroy.php
require_once __DIR__ . '/../../api/helpers.php';
allowMethods(['DELETE', 'POST']);
$user = requireAuth(); $body = getRequestBody();
$id = (int)($body['id'] ?? $_GET['id'] ?? 0);
if (!$id) jsonResponse(false, null, 'ID wajib.', 422);
$db = getDB();
$own = $db->prepare('SELECT id FROM categories WHERE id = ? AND user_id = ?');
$own->execute([$id, $user['id']]);
if (!$own->fetch()) jsonResponse(false, null, 'Kategori tidak ditemukan.', 404);
$db->prepare('DELETE FROM categories WHERE id = ? AND user_id = ?')->execute([$id, $user['id']]);
jsonResponse(true, null, 'Kategori dihapus.');
