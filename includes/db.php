<?php
$host = getenv('NIKKO_DB_HOST') ?: 'localhost';
$dbname = getenv('NIKKO_DB_NAME') ?: 'buku_kas';
$username = getenv('NIKKO_DB_USER') ?: 'root';
$password = getenv('NIKKO_DB_PASSWORD') ?: 'root';
$charset = 'utf8mb4';

$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

try {
    $pdo = new PDO("mysql:host={$host};dbname={$dbname};charset={$charset}", $username, $password, $options);
} catch (PDOException $e) {
    throw new PDOException('Koneksi database gagal: ' . $e->getMessage());
}
