<?php

require '../../config/session.php';

if(!isset($_SESSION['login'])){

header("Location: ../../auth/login.php");

exit();

}

if($_SESSION['role']!="User"){

header("Location: ../../dashboard/admin/");

exit();

}

?>

<!DOCTYPE html>

<html>

<head>

<title>User Dashboard</title>

</head>

<body>

<h1>

Dashboard User

</h1>

<h2>

<?= $_SESSION['nama']; ?>

</h2>

<a href="../../auth/logout.php">

Logout

</a>

</body>

</html>