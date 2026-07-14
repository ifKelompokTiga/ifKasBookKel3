<?php

require '../../config/session.php';

if(!isset($_SESSION['login'])){

header("Location: ../../auth/login.php");

exit();

}

if($_SESSION['role']!="Admin"){

header("Location: ../../dashboard/user/");

exit();

}

?>

<!DOCTYPE html>

<html>

<head>

<title>Dashboard Admin</title>

</head>

<body>

<h1>

Selamat Datang Admin

</h1>

<h2>

<?= $_SESSION['nama']; ?>

</h2>

<a href="../../auth/logout.php">

Logout

</a>

</body>

</html>