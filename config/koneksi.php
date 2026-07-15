<?php

$host="localhost";
$user="root";
$pass="root";
$db="bukukas";

$koneksi = mysqli_connect("localhost", "root", "root", "bukukas");

if(!$koneksi){

die("Koneksi Database Gagal");

}

?>