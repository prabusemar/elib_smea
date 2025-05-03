<?php
$host = "localhost";
$user = "root";
$pass = ""; // sesuaikan dengan password MySQL kamu
$db   = "library"; // nama database

// Membuat koneksi
$conn = mysqli_connect($host, $user, $pass, $db);

// Cek koneksi
if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}
