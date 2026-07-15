<?php
// verify.php - Verifikasi email setelah registrasi
session_start();
require_once 'config/database.php';

$token = $_GET['token'] ?? '';

if (empty($token)) {
    header("Location: login.php");
    exit();
}

// Cari user dengan token tersebut
$query = "SELECT id, email FROM users WHERE verification_token = '$token' AND is_verified = 0";
$result = mysqli_query($conn, $query);

if (mysqli_num_rows($result) === 1
