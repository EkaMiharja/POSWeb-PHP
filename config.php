<?php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "penjualan_db";

$conn = new mysqli("127.0.0.1", "root", "", "penjualan_db", 3306);

if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}
?>
