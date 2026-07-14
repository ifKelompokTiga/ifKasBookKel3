<?php

require_once '../config/database.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER["REQUEST_METHOD"] != "POST") {
    header("Location: login.php");
    exit();
}

$username = trim($_POST['username']);
$password = $_POST['password'];
$remember = isset($_POST['remember']);

// Cek input kosong
if (empty($username) || empty($password)) {
    $_SESSION['error'] = "Username dan Password wajib diisi.";
    header("Location: login.php");
    exit();
}

// Login menggunakan username ATAU email
$sql = "SELECT * FROM users
        WHERE username = ?
        OR email = ?
        LIMIT 1";

$stmt = mysqli_prepare($conn, $sql);

mysqli_stmt_bind_param($stmt, "ss", $username, $username);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

if ($user = mysqli_fetch_assoc($result)) {

    // cek status akun
    if ($user['status'] != "Aktif") {

        $_SESSION['error'] = "Akun tidak aktif.";

        header("Location: login.php");

        exit();

    }

    // cek password
    if (password_verify($password, $user['password'])) {

        session_regenerate_id(true);

        $_SESSION['login'] = true;

        $_SESSION['id'] = $user['id'];

        $_SESSION['nama'] = $user['nama'];

        $_SESSION['username'] = $user['username'];

        $_SESSION['email'] = $user['email'];

        $_SESSION['role'] = $user['role'];

        $_SESSION['foto'] = $user['foto'];

        $_SESSION['LAST_ACTIVITY'] = time();

        // update last login
        $update = mysqli_prepare($conn,
            "UPDATE users
             SET last_login = NOW()
             WHERE id=?");

        mysqli_stmt_bind_param($update, "i", $user['id']);

        mysqli_stmt_execute($update);

        // Remember Me
        if ($remember) {

            $token = bin2hex(random_bytes(32));

            $stmtToken = mysqli_prepare($conn,
            "UPDATE users
             SET remember_token=?
             WHERE id=?");

            mysqli_stmt_bind_param(
                $stmtToken,
                "si",
                $token,
                $user['id']
            );

            mysqli_stmt_execute($stmtToken);

            setcookie(

                "remember_token",

                $token,

                time()+86400*30,

                "/",

                "",

                false,

                true

            );

        }

        // redirect berdasarkan role

        if($user['role']=="Admin"){

            header("Location: ../dashboard/admin/index.php");

        }else{

            header("Location: ../dashboard/user/index.php");

        }

        exit();

    }

}

$_SESSION['error']="Username atau Password salah.";

header("Location: login.php");

exit();