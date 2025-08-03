<?php
session_start();
require_once '../config.php';

function redirectWithError($message)
{
    $_SESSION['login_alert'] = [
        'type' => 'error',
        'message' => $message
    ];
    header("Location: login.php");
    exit;
}

// Validasi input
if (empty($_POST['username']) || empty($_POST['password'])) {
    redirectWithError("Username dan password harus diisi!");
}

$username = trim($_POST['username']);
$password = $_POST['password'];

// Query dengan kolom lengkap termasuk data profil
$query = "SELECT 
            u.id, 
            u.username, 
            u.password, 
            u.role, 
            u.full_name,
            u.profile_pic,
            u.email,
            COALESCE(a.AdminID, 0) AS admin_id
          FROM users u
          LEFT JOIN admin a ON u.admin_id = a.AdminID
          WHERE u.username = ?";
$stmt = mysqli_prepare($conn, $query);

if (!$stmt) {
    error_log("MySQL Prepare Error: " . mysqli_error($conn));
    redirectWithError("Terjadi kesalahan sistem. Silakan coba lagi nanti.");
}

mysqli_stmt_bind_param($stmt, "s", $username);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) === 1) {
    $user = mysqli_fetch_assoc($result);

    // Verifikasi password
    if (password_verify($password, $user['password'])) {
        // Regenerasi session ID untuk keamanan
        session_regenerate_id(true);

        // Set data session lengkap
        $_SESSION = [
            'user_id'       => $user['id'], // Pastikan menggunakan 'user_id'
            'username'      => $user['username'],
            'email'         => $user['email'],
            'full_name'     => $user['full_name'],
            'profile_pic'   => $user['profile_pic'] ?? 'default.jpg',
            'role'          => $user['role'],
            'admin_id'      => $user['admin_id'] ?? 0,
            'logged_in'     => true, // Tambahkan status logged_in
            'last_activity' => time()
        ];

        // Redirect berdasarkan role dengan BASE_URL
        $redirect_url = match ($user['role']) {
            'admin'  => BASE_URL . '/admin/dashboard_admin.php',
            'staff'  => BASE_URL . '/staff/dashboard_staff.php',
            default  => BASE_URL . '/user/dashboard.php'
        };

        header("Location: " . $redirect_url);
        exit;
    } else {
        redirectWithError("Username atau password salah!");
    }
} else {
    redirectWithError("Akun tidak ditemukan!");
}

// Tutup koneksi
mysqli_stmt_close($stmt);
mysqli_close($conn);
