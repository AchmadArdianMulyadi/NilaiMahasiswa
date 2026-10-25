<?php
//definisi nilai parameter server
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "nilai_mhsw";
// $dbname = "mydb";

//create connection to database
// $conn = mysqli_connect("localhost", "root", "");
$conn = mysqli_connect($servername,$username,$password,$dbname);

//check connection
if(!$conn){
    die("Conecction failed: " . mysqli_connect_error());
}
// echo "Connected Successfully";
?>