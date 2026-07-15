<?php

require "../config/admin.php";
require "../config/koneksi.php";

include "../config/koneksi.php";

// Validasi Login Admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== "admin") {
    header("location:../login.php");
    exit;
}

// Fungsi helper untuk query agar lebih ringkas
function getTotal($koneksi, $query) {

    $result = mysqli_query($koneksi, $query);

    if(!$result){
        die("Query Error : ".mysqli_error($koneksi));
    }


    $data = mysqli_fetch_assoc($result);


    if(!$data){
        return 0;
    }


    return $data['total'] ?? 0;

}

// Data Dashboard
$total_user   = getTotal($koneksi, "SELECT COUNT(*) AS total FROM users WHERE role='user'");
$total_masuk  = getTotal($koneksi, "SELECT COALESCE(SUM(jumlah),0) AS total FROM transaksi WHERE jenis='Pemasukan'");
$total_keluar = getTotal($koneksi, "SELECT COALESCE(SUM(jumlah),0) AS total FROM transaksi WHERE jenis='Pengeluaran'");

// Data Grafik
$grafik = mysqli_query($koneksi, "SELECT jenis, SUM(jumlah) as total FROM transaksi GROUP BY jenis");
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

<div class="sidebar">

    <h2><i class="bi bi-wallet2"></i> KasBook</h2>

    <a href="dashboard.php">
        <i class="bi bi-speedometer2"></i> Dashboard
    </a>

    <a href="transaksi.php">
        <i class="bi bi-cash-stack"></i> Transaksi
    </a>

    <a href="user.php">
        <i class="bi bi-people"></i> Data User
    </a>

    <a href="export.php">
        <i class="bi bi-file-earmark-excel"></i> Export
    </a>

    <a href="../logout.php">
        <i class="bi bi-box-arrow-right"></i> Logout
    </a>

</div>

<!-- MAIN -->


<div class="main">



<div class="topbar">


<input 
class="search"
placeholder="Cari transaksi...">



<div class="profile">


<div>

Admin

</div>


<div class="avatar">

<?=substr($_SESSION['nama'],0,1);?>

</div>


</div>



</div>





<div class="dashboard-content">


<h1>
Dashboard Keuangan
</h1>


<p>
Ringkasan kondisi keuangan bisnis
</p>




<div class="stats">


<div class="stat-card">

<div>

<p>Total User</p>

<h2>
<?= $total_user ?>
</h2>

</div>

<div class="stat-icon">
<i class="fa fa-users"></i>
</div>

</div>



<div class="stat-card">

<div>

<p>Pemasukan</p>

<h2>
Rp <?=number_format($total_masuk);?>
</h2>

</div>

<div class="stat-icon">
<i class="fa fa-arrow-up"></i>
</div>

</div>



<div class="stat-card">

<div>

<p>Pengeluaran</p>

<h2>
Rp <?=number_format($total_keluar);?>
</h2>

</div>

<div class="stat-icon">
<i class="fa fa-arrow-down"></i>
</div>

</div>


</div>

<div class="chart-container">


<h2>
Grafik Keuangan
</h2>


<canvas id="chartKas"></canvas>


</div>



</div>


</div>


</div>





<script>


new Chart(

document.getElementById('chartKas'),

{


type:'bar',

data:{


labels:[

"Pemasukan",

"Pengeluaran"

],


datasets:[{


label:"Keuangan",

data:[

<?= number_format($total_masuk,0,',','.') ?>

<?= number_format($total_keluar,0,',','.') ?>

]


}]


}


}


);



</script>

<button onclick="darkMode()">



🌙 Dark Mode



</button>



<a href="export.php" class="btn">
📊 Export Excel
</a>

<a href="pdf.php" class="btn">
📄 Cetak PDF
</a>

</body>

</html>