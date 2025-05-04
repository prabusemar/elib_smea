<?php
session_start();
include '../../config.php';

$id = $_GET['id'] ?? 0;

$query = "SELECT * FROM buku WHERE BukuID = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if ($book = mysqli_fetch_assoc($result)) {
    echo json_encode($book);
} else {
    echo json_encode(null);
}
?>