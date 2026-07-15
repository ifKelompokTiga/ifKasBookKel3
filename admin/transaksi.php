<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include "../config/koneksi.php";


$data=mysqli_query($koneksi,

"SELECT transaksi.*,users.nama

FROM transaksi

JOIN users

ON transaksi.user_id=users.id

ORDER BY transaksi.id DESC");


?>

<?php

$query = mysqli_query($koneksi,

"SELECT * FROM transaksi
ORDER BY id DESC
LIMIT 5");

?>

<div class="card card-dashboard mt-4">

<div class="card-header">

Transaksi Terbaru

</div>

<div class="card-body">

<table class="table table-striped">

<tr>

<th>No</th>

<th>Tanggal</th>

<th>Jenis</th>

<th>Kategori</th>

<th>Jumlah</th>

</tr>

<?php

$no=1;

while($d=mysqli_fetch_assoc($query)):

?>

<tr>

<td><?= $no++ ?></td>

<td><?= htmlspecialchars($d['tanggal']) ?></td>

<td><?= htmlspecialchars($d['jenis']) ?></td>

<td><?= htmlspecialchars($d['kategori']) ?></td>

<td>

Rp <?= number_format($d['jumlah'],0,",",".") ?>

</td>

</tr>

<?php endwhile; ?>

</table>

</div>

</div>

</div>

<?php

include "../template/footer.php";

?>


<!DOCTYPE html>

<html>

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>KasBook</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">

<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

<link rel="stylesheet" href="../assets/css/admin.css">

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
Semua Transaksi
</h1>


<table>


<tr>

<th>No</th>

<th>User</th>

<th>Tanggal</th>

<th>Jenis</th>

<th>Jumlah</th>

<th>Aksi</th>

</tr>



<?php

$no=1;

while($d=mysqli_fetch_assoc($data)){


?>


<tr>


<td><?=$no++;?></td>


<td><?=$d['nama'];?></td>


<td><?=$d['tanggal'];?></td>


<td><?=$d['jenis'];?></td>


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



</div>


</div>


</body>

</html>