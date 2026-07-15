<?php

session_start();

include "../config/koneksi.php";


if(!isset($_SESSION['id'])){

header("location:../login.php");

}


$id_user=$_SESSION['id'];


// total pemasukan

$pemasukan=mysqli_fetch_assoc(mysqli_query($koneksi,

"SELECT SUM(jumlah) AS total 
FROM transaksi 
WHERE user_id='$id_user'
AND jenis='Pemasukan'"));


// total pengeluaran

$pengeluaran=mysqli_fetch_assoc(mysqli_query($koneksi,

"SELECT SUM(jumlah) AS total 
FROM transaksi 
WHERE user_id='$id_user'
AND jenis='Pengeluaran'"));



$masuk=$pemasukan['total'] ?? 0;

$keluar=$pengeluaran['total'] ?? 0;


$saldo=$masuk-$keluar;


?>


<!DOCTYPE html>

<html>

<head>

<title>Dashboard Buku Kas</title>

<link rel="stylesheet" href="../assets/css/style.css">

</head>


<body>


<div class="dashboard">


<h1>
Halo, <?= $_SESSION['nama']; ?>
</h1>


<div class="card-container">


<div class="card">

<h3>
Saldo
</h3>

<p>
Rp <?= number_format($saldo); ?>
</p>

</div>



<div class="card green">

<h3>
Pemasukan
</h3>

<p>
Rp <?= number_format($masuk); ?>
</p>

</div>




<div class="card red">

<h3>
Pengeluaran
</h3>

<p>
Rp <?= number_format($keluar); ?>
</p>

</div>


</div>



<a href="tambah.php" class="btn">
Tambah Transaksi
</a>


<a href="transaksi.php" class="btn">
Riwayat
</a>


<a href="../logout.php" class="btn btn-danger">
Logout
</a>


</div>



</body>

</html>