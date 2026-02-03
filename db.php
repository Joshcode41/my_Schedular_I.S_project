<?php
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "garage_scheduler_v2";  // NEW DB name

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
