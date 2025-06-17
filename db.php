<?php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "vitamin_kasir"; // <-- sudah diganti dari thriftshop ke vitamin_kasir

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}
?>
