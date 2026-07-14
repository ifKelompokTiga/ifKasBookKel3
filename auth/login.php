<?php

session_start();

if(isset($_SESSION['login'])){

header("Location: ../dashboard/admin/");

exit();

}

?>

<!DOCTYPE html>

<html lang="id">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1">

<title>Login | Buku Kas Digital</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" rel="stylesheet">

<link href="../assets/css/login.css" rel="stylesheet">

</head>

<body>

<?php

if(isset($_SESSION['error'])){

?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>

Swal.fire({

icon:'error',

title:'Login Gagal',

text:'<?= $_SESSION['error']; ?>'

});

</script>

<?php

unset($_SESSION['error']);

}

?>

<div class="bg-circle c1"></div>

<div class="bg-circle c2"></div>

<div class="login-card">

<div class="left">

<h1>Buku Kas Digital</h1>

<p>

Kelola pemasukan, pengeluaran, target tabungan,

dan laporan keuangan dengan mudah.

</p>

</div>

<div class="right">

<h2 class="mb-4 text-center">

Login

</h2>

<form

action="login_process.php"

method="POST">

<div class="mb-3">

<label>Username / Email</label>

<input

type="text"

name="username"

class="form-control"

required>

</div>

<div class="mb-3">

<label>Password</label>

<div class="password-box">

<input

type="password"

name="password"

id="password"

class="form-control"

required>

<i

class="fa-solid fa-eye"

id="togglePassword">

</i>

</div>

</div>

<div class="mb-3 form-check">

<input

class="form-check-input"

type="checkbox"

name="remember"

id="remember">

<label class="form-check-label">

Remember Me

</label>

</div>

<button

class="btn btn-login w-100">

Login

</button>

<div class="text-center mt-4">

<a href="forgot-password.php">

Lupa Password?

</a>

</div>

</form>

</div>

</div>

<script>

const toggle=document.getElementById("togglePassword");

const pass=document.getElementById("password");

toggle.onclick=function(){

const type=pass.getAttribute("type")==="password"?"text":"password";

pass.setAttribute("type",type);

this.classList.toggle("fa-eye-slash");

}

</script>

</body>

</html>