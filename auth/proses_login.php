<?php
session_start();
include '../config.php';

function displayError($message)
{
    $_SESSION['login_error'] = $message;
    header("Location: login.php");
    exit;
}

// Validate input
if (empty($_POST['username']) || empty($_POST['password'])) {
    displayError("Username dan password harus diisi!");
}

$username = trim($_POST['username']);
$password = $_POST['password'];

// Prepare statement with role column (make sure your users table has this column)
$query = "SELECT id, username, password, role FROM users WHERE username = ?";
$stmt = mysqli_prepare($conn, $query);

if (!$stmt) {
    displayError("Terjadi kesalahan sistem. Silakan coba lagi nanti.");
}

mysqli_stmt_bind_param($stmt, "s", $username);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) === 1) {
    $user = mysqli_fetch_assoc($result);

    // Verify password (assuming passwords are hashed)
    if (password_verify($password, $user['password'])) {
        // Regenerate session ID to prevent session fixation
        session_regenerate_id(true);

        // Set session data including role
        $_SESSION = [
            'user_id' => $user['id'],
            'username' => $user['username'],
            'role' => $user['role'],
            'logged_in' => true,
            'last_activity' => time()
        ];

        // Redirect based on role
        switch ($user['role']) {
            case 'admin':
                header("Location: ../admin/dashboard_admin.php");
                break;
            case 'staff':
                header("Location: dashboard_staff.php");
                break;
            case 'user':
                header("Location: dashboard_user.php");
                break;
            default:
                header("Location: dashboard_user.php");
        }
        exit;
    } else {
        displayError("Username atau password salah!");
    }
} else {
    displayError("Username atau password salah!");
}

// Close statement and connection
mysqli_stmt_close($stmt);
mysqli_close($conn);
