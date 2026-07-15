<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include "../config/koneksi.php";


$data=mysqli_query($koneksi,

"SELECT * FROM users
WHERE role='user'");


?>


<!DOCTYPE html>

<html>

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>KasBook</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">

<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

<link rel="stylesheet" href="../assets/css/user.css">

</head>


<body>


<div class="admin-container">


<div class="sidebar">

<h2>
💰 BukuKas
</h2>


<a href="dashboard.php">
Dashboard
</a>


<a href="user.php">
User
</a>


<a href="transaksi.php">
Transaksi
</a>


</div>



<div class="content">


<h1>
Data User
</h1>


<table>


<tr>

<th>No</th>

<th>Nama</th>

<th>Username</th>


</tr>



<?php

$no=1;

while($d=mysqli_fetch_assoc($data)){


?>


<tr>


<td>
<?=$no++;?>
</td>


<td>
<?=$d['nama'];?>
</td>


<td>
<?=$d['username'];?>
</td>


</tr>


<?php } ?>


</table>


</div>


</div>


</body>

</html>