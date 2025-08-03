<?php
session_start();
include '../config.php';

function redirectWithMessage($isSuccess, $message)
{
    $_SESSION['register_alert'] = [
        'type' => $isSuccess ? 'success' : 'error',
        'message' => $message
    ];
    header("Location: " . ($isSuccess ? "login.php" : "register.php"));
    exit;
}

// Validate all fields
$required_fields = ['full_name', 'username', 'email', 'password', 'confirm_password'];
foreach ($required_fields as $field) {
    if (empty($_POST[$field])) {
        redirectWithMessage(false, "Semua field harus diisi!");
    }
}

$full_name = trim($_POST['full_name']);
$username = trim($_POST['username']);
$email = trim($_POST['email']);
$password = $_POST['password'];
$confirm_password = $_POST['confirm_password'];

// Input validation
if (strlen($full_name) < 3) redirectWithMessage(false, "Nama lengkap minimal 3 karakter!");
if (strlen($username) < 3) redirectWithMessage(false, "Username minimal 3 karakter!");
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) redirectWithMessage(false, "Format email tidak valid!");
if (strlen($password) < 6) redirectWithMessage(false, "Password minimal 6 karakter!");
if ($password !== $confirm_password) redirectWithMessage(false, "Password tidak cocok!");

// Check for existing username or email
$check_stmt = $conn->prepare("SELECT 
    SUM(username = ?) as username_exists, 
    SUM(email = ?) as email_exists 
    FROM users");
$check_stmt->bind_param("ss", $username, $email);
$check_stmt->execute();
$result = $check_stmt->get_result()->fetch_assoc();

if ($result['username_exists'] > 0) {
    redirectWithMessage(false, "Username sudah digunakan!");
}

if ($result['email_exists'] > 0) {
    redirectWithMessage(false, "Email sudah terdaftar!");
}

// Hash password
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// Start transaction
$conn->begin_transaction();

try {
    // Insert into users table
    $user_stmt = $conn->prepare("INSERT INTO users (username, email, full_name, password, role) VALUES (?, ?, ?, ?, 'member')");
    $user_stmt->bind_param("ssss", $username, $email, $full_name, $hashed_password);

    if (!$user_stmt->execute()) {
        throw new Exception("Gagal membuat akun pengguna");
    }

    // Try to call stored procedure (optional)
    try {
        $proc_stmt = $conn->prepare("CALL register_member(?, ?, ?)");
        $proc_stmt->bind_param("sss", $username, $email, $hashed_password);
        $proc_stmt->execute();
    } catch (Exception $e) {
        error_log("Procedure error: " . $e->getMessage());
    }

    $conn->commit();
    redirectWithMessage(true, "🎉 Pendaftaran berhasil! Silakan login");
} catch (Exception $e) {
    $conn->rollback();
    error_log("Registration error: " . $e->getMessage());
    redirectWithMessage(false, "Terjadi kesalahan saat pendaftaran. Silakan coba lagi.");
}
