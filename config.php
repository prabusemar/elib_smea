<?php

// Database configuration
// These settings are now fetched from environment variables.
// You need to set these in your server configuration (e.g., Apache, Nginx, .htaccess, or a .env file).
$host = getenv('DB_HOST') ?: "localhost"; // Fallback to localhost if not set
$user = getenv('DB_USER') ?: "root";    // Fallback to root if not set
$pass = getenv('DB_PASS') ?: "";      // Fallback to empty string if not set
$db = getenv('DB_NAME') ?: "library";  // Fallback to library if not set

// Create connection
$conn = mysqli_connect($host, $user, $pass, $db);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Define base URL - CHANGE THIS TO YOUR ACTUAL BASE URL
define('BASE_URL', 'http://localhost/library');

// Define ROOT_PATH for consistent file includes
// __DIR__ is the directory of the current file (config.php).
// Since config.php is in the project root, __DIR__ is the project root.
define('ROOT_PATH', __DIR__);

/*
// Example of using ROOT_PATH for includes:
// require_once ROOT_PATH . '/views/header.php';
// require_once ROOT_PATH . '/admin/buku/buku_handler.php'; 
// require_once ROOT_PATH . '/lib/Database.php';
*/

// Error reporting
// Set APP_ENV to 'development' in your server configuration to enable display_errors.
if (getenv('APP_ENV') === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1); // It's good practice to log errors to a file in production
    // ini_set('error_log', '/path/to/your/php-error.log'); // Optionally specify error log file
}

error_log("Accessed: " . $_SERVER['REQUEST_URI']);
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// CSRF Token Functions
function generate_csrf_token($timeout = 3600) { // Default timeout 1 hour
    if (empty($_SESSION['csrf_token']) ||
        (isset($_SESSION['csrf_token_time']) && (time() - $_SESSION['csrf_token_time']) > $timeout)
    ) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        $_SESSION['csrf_token_time'] = time();
    }
}

function get_csrf_token() {
    return $_SESSION['csrf_token'] ?? null;
}

function verify_csrf_token($token_from_form, $timeout = 3600) {
    if (isset($_SESSION['csrf_token']) && isset($_SESSION['csrf_token_time']) &&
        hash_equals($_SESSION['csrf_token'], $token_from_form)) {
        if ((time() - $_SESSION['csrf_token_time']) <= $timeout) {
            // Token is valid and not expired
            unset($_SESSION['csrf_token']); // Unset old token
            unset($_SESSION['csrf_token_time']);
            generate_csrf_token(); // Regenerate new token for next request
            return true;
        }
    }
    // Token is invalid or expired
    unset($_SESSION['csrf_token']); // Invalidate token on failure as well
    unset($_SESSION['csrf_token_time']);
    return false;
}

error_log("Session status: " . json_encode($_SESSION));