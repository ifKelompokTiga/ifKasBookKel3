<?php
$host = 'localhost';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host={$host};charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);

    $sql = file_get_contents(__DIR__ . '/setup_database.sql');
    $pdo->exec($sql);

    echo "Database berhasil dibuat.";
} catch (PDOException $e) {
    echo 'Gagal membuat database: ' . $e->getMessage();
}