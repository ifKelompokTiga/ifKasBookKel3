<?php
// GET /api/categories/index.php
require_once __DIR__ . '/../../api/helpers.php';
allowMethods(['GET']);
$user = requireAuth();
$db   = getDB();

// Return global categories + user's custom categories
$stmt = $db->prepare(
    'SELECT id, user_id, name, type, icon, color
     FROM categories
     WHERE user_id IS NULL OR user_id = ?
     ORDER BY user_id IS NULL DESC, type, name'
);
$stmt->execute([$user['id']]);
jsonResponse(true, $stmt->fetchAll());
