<?php
// =====================================================
// BukuKas Universal — API Shared Helpers
// =====================================================

require_once __DIR__ . '/../config/app.php';

/**
 * Send JSON response and exit
 */
function jsonResponse(bool $success, mixed $data = null, string $message = '', int $code = 200): void {
    http_response_code($code);
    echo json_encode([
        'success' => $success,
        'data'    => $data,
        'message' => $message,
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

/**
 * Get JSON request body
 */
function getRequestBody(): array {
    $raw = file_get_contents('php://input');
    return json_decode($raw, true) ?? [];
}

/**
 * Require authenticated user — abort if not logged in
 */
function requireAuth(): array {
    if (empty($_SESSION['user_id'])) {
        jsonResponse(false, null, 'Unauthorized. Silakan login.', 401);
    }
    $db   = getDB();
    $stmt = $db->prepare('SELECT id, name, email, role, is_active, created_at FROM users WHERE id = ?');
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    if (!$user || !$user['is_active']) {
        session_destroy();
        jsonResponse(false, null, 'Akun tidak ditemukan atau dinonaktifkan.', 401);
    }
    return $user;
}

/**
 * Require admin role
 */
function requireAdmin(): array {
    $user = requireAuth();
    if ($user['role'] !== 'admin') {
        jsonResponse(false, null, 'Akses ditolak. Hanya admin yang diizinkan.', 403);
    }
    return $user;
}

/**
 * Validate required fields — returns first error or null
 */
function validateRequired(array $data, array $fields): ?string {
    foreach ($fields as $field) {
        if (!isset($data[$field]) || trim((string)$data[$field]) === '') {
            return "Field '{$field}' wajib diisi.";
        }
    }
    return null;
}

/**
 * Log activity
 */
function logActivity(int $userId, string $action, string $details = ''): void {
    try {
        $db   = getDB();
        $stmt = $db->prepare('INSERT INTO activity_log (user_id, action, details, ip_address) VALUES (?,?,?,?)');
        $stmt->execute([$userId, $action, $details, $_SERVER['REMOTE_ADDR'] ?? null]);
    } catch (Exception $e) {
        // non-fatal, silently ignore
    }
}

/**
 * Sanitize string input
 */
function clean(string $str): string {
    return htmlspecialchars(trim($str), ENT_QUOTES, 'UTF-8');
}

/**
 * Only allow specific HTTP methods — abort otherwise
 */
function allowMethods(array $methods): void {
    if (!in_array($_SERVER['REQUEST_METHOD'], $methods, true)) {
        jsonResponse(false, null, 'Method not allowed.', 405);
    }
}

// Always set API headers
setApiHeaders();
