<?php

session_start();

include "config/koneksi.php";

if(isset($_POST['login'])){


$username=$_POST['username'];
$password=$_POST['password'];



$query=mysqli_query($koneksi,

"SELECT * FROM users 
WHERE username='$username'
AND password='$password'");


$data=mysqli_fetch_assoc($query);



if($data){


$_SESSION['id']=$data['id'];

$_SESSION['nama']=$data['nama'];

$_SESSION['role']=$data['role'];



if($data['role']=="admin"){

header("location:admin/dashboard.php");

}



}else{


$error="Username atau password salah";


}



}


?>


<!DOCTYPE html>
<html>

<head>

<title>Login</title>

<link rel="stylesheet" href="assets/css/style.css">

</head>


<body>


<div class="login-box">


<h2>Login</h2>


<?php

if(isset($error)){

echo "<p class='error'>$error</p>";

}

?>


<form method="POST">


<input 
type="text"
name="username"
placeholder="Username"
required>


<input 
type="password"
name="password"
placeholder="Password"
required>



<button name="login">
Masuk
</button>


</form>


</div>



</body>

</html>