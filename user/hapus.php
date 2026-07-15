<?php

include "../config/koneksi.php";


$id=$_GET['id'];


mysqli_query($koneksi,

"DELETE FROM transaksi 
WHERE id='$id'");


header("location:transaksi.php");


?>
