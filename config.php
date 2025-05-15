<?php

// Database configuration
$host = "localhost";
$user = "root";
$pass = "";
$db = "library";

// Create connection
$conn = mysqli_connect($host, $user, $pass, $db);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Define base URL - CHANGE THIS TO YOUR ACTUAL BASE URL
define('BASE_URL', 'http://localhost/library');

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

error_log("Accessed: " . $_SERVER['REQUEST_URI']);
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
error_log("Session status: " . json_encode($_SESSION));