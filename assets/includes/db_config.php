<?php 	

$localhost = "127.0.0.1:3310";
$username = "wood-user";
$password = "mU3hW3oW3v";
$dbname = "wood-base";



// db connection
$conn = new mysqli($localhost, $username, $password, $dbname);
mysqli_set_charset($conn,"utf8");
// check connection
if($conn->connect_error) {
  die("Connection Failed : " . $conn->connect_error);
} else {
   
}
