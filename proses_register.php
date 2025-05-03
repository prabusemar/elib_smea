<?php
session_start();
include 'config.php';

function displayError($message) {
    $_SESSION['register_error'] = $message;
    header("Location: register.php");
    exit;
}

// Validate input
if (empty($_POST['username']) || empty($_POST['password']) || empty($_POST['confirm_password'])) {
    displayError("Semua field harus diisi!");
}

$username = trim($_POST['username']);
$password = $_POST['password'];
$confirm_password = $_POST['confirm_password'];

// Validate username length
if (strlen($username) < 3) {
    displayError("Username minimal 3 karakter!");
}

// Check password match
if ($password !== $confirm_password) {
    displayError("Password dan konfirmasi password tidak cocok!");
}

// Check password length
if (strlen($password) < 6) {
    displayError("Password minimal 6 karakter!");
}

// Check if username already exists
$check_query = "SELECT id FROM users WHERE username = ?";
$stmt = mysqli_prepare($conn, $check_query);
mysqli_stmt_bind_param($stmt, "s", $username);
mysqli_stmt_execute($stmt);
mysqli_stmt_store_result($stmt);

if (mysqli_stmt_num_rows($stmt) > 0) {
    mysqli_stmt_close($stmt);
    displayError("Username sudah digunakan!");
}

// Hash password
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// Insert new user
$insert_query = "INSERT INTO users (username, password) VALUES (?, ?)";
$stmt = mysqli_prepare($conn, $insert_query);
mysqli_stmt_bind_param($stmt, "ss", $username, $hashed_password);

if (mysqli_stmt_execute($stmt)) {
    // Registration successful
    $_SESSION['register_success'] = "Pendaftaran berhasil! Silakan login.";
    header("Location: login.php");
    exit;
} else {
    displayError("Terjadi kesalahan. Silakan coba lagi.");
}

mysqli_stmt_close($stmt);
mysqli_close($conn);
?>