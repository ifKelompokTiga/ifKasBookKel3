<?php

session_start();

include "../config/koneksi.php";


if(isset($_POST['simpan'])){


$id=$_SESSION['id'];

$tanggal=$_POST['tanggal'];

$jenis=$_POST['jenis'];

$kategori=$_POST['kategori'];

$keterangan=$_POST['keterangan'];

$jumlah=$_POST['jumlah'];



mysqli_query($koneksi,

"INSERT INTO transaksi

VALUES

(
'',
'$id',
'$tanggal',
'$jenis',
'$kategori',
'$keterangan',
'$jumlah'
)

");


header("location:dashboard.php");


}

?>


<!DOCTYPE html>

<html>

<head>

<title>
Tambah Transaksi
</title>

<link rel="stylesheet" href="../assets/css/style.css">

</head>


<body>


<div class="login-box">


<h2>
Tambah Transaksi
</h2>



<form method="POST">


<input 
type="date"
name="tanggal"
required>



<select name="jenis">


<option>
Pemasukan
</option>


<option>
Pengeluaran
</option>


</select>



<input
name="kategori"
placeholder="Kategori"
required>



<input
name="keterangan"
placeholder="Keterangan"
required>



<input
type="number"
name="jumlah"
placeholder="Jumlah"
required>



<button name="simpan">
Simpan
</button>



</form>


</div>


</body>

</html>