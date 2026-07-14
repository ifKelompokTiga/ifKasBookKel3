<?php

$host="localhost";
$user="root";
$pass="";
$db="db_buku_kas";

$conn=mysqli_connect($host,$user,$pass,$db);

if(!$conn){
    die("Koneksi gagal : ".mysqli_connect_error());
}

date_default_timezone_set("Asia/Jakarta");

?>