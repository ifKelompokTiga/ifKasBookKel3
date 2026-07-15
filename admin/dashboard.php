<?php
session_start();
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
        return 0;
    }

    $data=mysqli_fetch_assoc($result);

    return $data['total'] ?? 0;

}

// Data Dashboard
$user['total']    = getTotal($koneksi, "SELECT COUNT(*) as total FROM users WHERE role='user'");
$masuk['total']   = getTotal($koneksi, "SELECT SUM(jumlah) as total FROM transaksi WHERE jenis='Pemasukan'");
$keluar['total']  = getTotal($koneksi, "SELECT SUM(jumlah) as total FROM transaksi WHERE jenis='Pengeluaran'");

// Data Grafik
$grafik = mysqli_query($koneksi, "SELECT jenis, SUM(jumlah) as total FROM transaksi GROUP BY jenis");
?>

<!DOCTYPE html>

<html>

<head>

<title>
Admin Dashboard
</title>


<link rel="stylesheet"
href="../assets/css/admin.css">


<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">


<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>


</head>


<body>


<div class="admin-wrapper">



<!-- SIDEBAR -->


<div class="sidebar">


<div class="brand">

<i class="fa-solid fa-wallet"></i>

<span>
BukuKas
</span>

</div>



<a class="active">

<i class="fa fa-home"></i>

<span>
Dashboard
</span>

</a>


<a href="user.php">

<i class="fa fa-users"></i>

<span>
User
</span>

</a>


<a href="transaksi.php">

<i class="fa fa-money-bill"></i>

<span>
Transaksi
</span>

</a>



<a href="../logout.php">

<i class="fa fa-right-from-bracket"></i>

<span>
Logout
</span>

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
<?=$total_user;?>
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

<?=$total_masuk;?>,

<?=$total_keluar;?>

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