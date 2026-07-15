<?php


include "../config/koneksi.php";


header(
"Content-type:application/vnd-ms-excel"
);


header(
"Content-Disposition:attachment;filename=laporan_kas.xls"
);



$data=mysqli_query($koneksi,

"SELECT * FROM transaksi");


?>


<table border="1">

<tr>

<th>Tanggal</th>
<th>Jenis</th>
<th>Kategori</th>
<th>Jumlah</th>

</tr>


<?php while($d=mysqli_fetch_assoc($data)){ ?>


<tr>

<td><?=$d['tanggal'];?></td>

<td><?=$d['jenis'];?></td>

<td><?=$d['kategori'];?></td>

<td><?=$d['jumlah'];?></td>

</tr>


<?php } ?>


</table>

<a href="export.php" class="btn">

Export Excel

</a>