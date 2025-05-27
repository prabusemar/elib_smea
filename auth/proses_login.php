<?php
session_start(); // Ensure session is started before using $_SESSION
include '../config.php';

// CSRF Token Verification
$submitted_token = $_POST['csrf_token'] ?? '';
if (!verify_csrf_token($submitted_token)) {
    // Before calling displayError, ensure the session is truly active
    // as verify_csrf_token might unset session variables on failure.
    // displayError itself relies on $_SESSION.
    // A simple way is to restart session if not active, though config.php should handle this.
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $_SESSION['login_error'] = "Invalid or expired request. Please try again.";
    header("Location: login.php");
    exit;
}

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
