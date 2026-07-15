<?php

$host = "localhost";
$user = "root";
$pass = "root";
$db   = "bukukas";


$koneksi = mysqli_connect(
    $host,
    $user,
    $pass,
    $db
);


if(!$koneksi){

    die("Database gagal terkoneksi : ".mysqli_connect_error());

}

?>