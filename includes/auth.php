<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function isLoggedIn(): bool
{
    return !empty($_SESSION['user']);
}

function getCurrentUser(): ?array
{
    return $_SESSION['user'] ?? null;
}

function requireLogin(): void
{
    if (!isLoggedIn()) {
        header('Location: /nikko/login.php');
        exit;
    }
}

function getUserStorePath(): string
{
    return __DIR__ . '/../data/users.json';
}

function loadUsers(): array
{
    $defaults = [
        'admin' => [
            'password' => password_hash('admin123', PASSWORD_DEFAULT),
            'role' => 'admin',
            'name' => 'Administrator'
        ],
        'user' => [
            'password' => password_hash('user123', PASSWORD_DEFAULT),
            'role' => 'user',
            'name' => 'Pengguna'
        ]
    ];

    $path = getUserStorePath();
    if (!file_exists($path)) {
        return $defaults;
    }

    $data = json_decode(file_get_contents($path), true);
    if (!is_array($data)) {
        return $defaults;
    }

    return array_merge($defaults, $data);
}

function saveUsers(array $users): bool
{
    $path = getUserStorePath();
    $dir = dirname($path);
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }

    return file_put_contents($path, json_encode($users, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) !== false;
}

function loginUser(string $username, string $password): ?array
{
    $accounts = loadUsers();

    if (!isset($accounts[$username])) {
        return null;
    }

    $account = $accounts[$username];
    $hash = $account['password'];

    if (password_verify($password, $hash) === false) {
        return null;
    }

    $_SESSION['user'] = [
        'username' => $username,
        'name' => $account['name'],
        'role' => $account['role']
    ];

    return $_SESSION['user'];
}

function registerUser(string $username, string $password, string $name): ?string
{
    $username = trim($username);
    $name = trim($name);

    if ($username === '' || $password === '' || $name === '') {
        return 'Semua bidang harus diisi.';
    }

    $users = loadUsers();
    if (isset($users[$username])) {
        return 'Username sudah digunakan. Silakan pilih username lain.';
    }

    $users[$username] = [
        'password' => password_hash($password, PASSWORD_DEFAULT),
        'role' => 'user',
        'name' => $name
    ];

    if (!saveUsers($users)) {
        return 'Gagal menyimpan data pengguna. Silakan coba lagi.';
    }

    return null;
}