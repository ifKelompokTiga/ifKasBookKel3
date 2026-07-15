<?php

session_start();

include "../config/koneksi.php";


$id=$_SESSION['id'];


$data=mysqli_query($koneksi,

"SELECT * FROM transaksi
WHERE user_id='$id'
ORDER BY id DESC");


?>


<!DOCTYPE html>

<html>

<head>

<title>
Riwayat Transaksi
</title>

<link rel="stylesheet" href="../assets/css/style.css">

</head>


<body>


<div class="dashboard">


<h1>
Riwayat Transaksi
</h1>


<table>


<tr>

<th>No</th>
<th>Tanggal</th>
<th>Jenis</th>
<th>Kategori</th>
<th>Jumlah</th>
<th>Aksi</th>

</tr>



<?php

$no=1;

while($d=mysqli_fetch_assoc($data)){


?>


<tr>


<td>
<?= $no++; ?>
</td>


<td>
<?= $d['tanggal']; ?>
</td>


<td>
<?= $d['jenis']; ?>
</td>


<td>
<?= $d['kategori']; ?>
</td>


<td>
Rp <?=number_format($d['jumlah']);?>
</td>


<td>

<a href="hapus.php?id=<?=$d['id'];?>">
Hapus
</a>

</td>


</tr>


<?php } ?>


</table>


<a href="dashboard.php" class="btn">
Kembali
</a>


</div>


</body>

</html>