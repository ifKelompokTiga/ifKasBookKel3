<?php

require '../config/database.php';

?>

<!DOCTYPE html>

<html>

<head>

<meta charset="UTF-8">

<title>Register Admin</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

</head>

<body class="bg-light">

<div class="container">

<div class="row justify-content-center">

<div class="col-md-5">

<div class="card mt-5 shadow">

<div class="card-body">

<h3 class="text-center">

Register Admin

</h3>

<form action="register_process.php" method="POST">

<div class="mb-3">

<label>Nama</label>

<input
type="text"
name="nama"
class="form-control"
required>

</div>

<div class="mb-3">

<label>Username</label>

<input
type="text"
name="username"
class="form-control"
required>

</div>

<div class="mb-3">

<label>Email</label>

<input
type="email"
name="email"
class="form-control"
required>

</div>

<div class="mb-3">

<label>Password</label>

<input
type="password"
name="password"
class="form-control"
required>

</div>

<button class="btn btn-primary w-100">

Register Admin

</button>

</form>

</div>

</div>

</div>

</div>

</div>

</body>

</html>