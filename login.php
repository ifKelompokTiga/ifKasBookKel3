<?php
session_start();

require_once "config/koneksi.php";

if (isset($_SESSION['id'])) {
    if ($_SESSION['role'] == "admin") {
        header("Location: admin/dashboard.php");
        exit;
    }
}

$error = "";

if (isset($_POST['login'])) {

    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if ($username == "" || $password == "") {

        $error = "Username dan Password wajib diisi.";

    } else {

        $stmt = mysqli_prepare(
            $koneksi,
            "SELECT * FROM users WHERE username=? LIMIT 1"
        );

        mysqli_stmt_bind_param($stmt, "s", $username);
        mysqli_stmt_execute($stmt);

        $result = mysqli_stmt_get_result($stmt);

        if ($user = mysqli_fetch_assoc($result)) {

            $loginBerhasil = false;

            // Support password_hash()
            if (password_verify($password, $user['password'])) {
                $loginBerhasil = true;
            }

            // Support database lama yang masih plaintext
            elseif ($password === $user['password']) {

                $loginBerhasil = true;

                // Upgrade password otomatis
                $hash = password_hash($password, PASSWORD_DEFAULT);

                $update = mysqli_prepare(
                    $koneksi,
                    "UPDATE users SET password=? WHERE id=?"
                );

                mysqli_stmt_bind_param(
                    $update,
                    "si",
                    $hash,
                    $user['id']
                );

                mysqli_stmt_execute($update);
            }

            if ($loginBerhasil) {

                session_regenerate_id(true);

                $_SESSION['id'] = $user['id'];
                $_SESSION['nama'] = $user['nama'];
                $_SESSION['role'] = $user['role'];

                if ($user['role'] == "admin") {
                    header("Location: admin/dashboard.php");
                } else {
                    header("Location: index.php");
                }

                exit;
            }

            $error = "Password salah.";

        } else {

            $error = "Username tidak ditemukan.";

        }

    }

}
?>

<!DOCTYPE html>
<html lang="id">
<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Login</title>

<link rel="stylesheet" href="assets/css/style.css">

</head>

<body>

<div class="login-box">

<h2>Login</h2>

<?php if ($error != "") : ?>
<p class="error"><?php echo htmlspecialchars($error); ?></p>
<?php endif; ?>

<form method="POST">

<input
type="text"
name="username"
placeholder="Username"
required>

<input
type="password"
name="password"
placeholder="Password"
required>

<button type="submit" name="login">
Masuk
</button>

</form>

</div>

</body>
</html>