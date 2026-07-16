<?php
// PUT /api/categories/update.php
require_once __DIR__ . '/../../api/helpers.php';
allowMethods(['PUT', 'POST']);
$user = requireAuth(); $body = getRequestBody();
$id = (int)($body['id'] ?? 0);
if (!$id) jsonResponse(false, null, 'ID wajib.', 422);
$db = getDB();
$own = $db->prepare('SELECT id FROM categories WHERE id = ? AND user_id = ?');
$own->execute([$id, $user['id']]);
if (!$own->fetch()) jsonResponse(false, null, 'Kategori tidak ditemukan.', 404);
$db->prepare('UPDATE categories SET name=?, type=?, icon=?, color=? WHERE id=? AND user_id=?')
   ->execute([clean($body['name']??''), $body['type']??'expense', clean($body['icon']??'📦'), clean($body['color']??'#6B7280'), $id, $user['id']]);
$row = $db->prepare('SELECT * FROM categories WHERE id = ?'); $row->execute([$id]);
jsonResponse(true, $row->fetch(), 'Kategori diperbarui.');
