<?php
// views/header.php
if (!isset($_SESSION['username'])) {
    header("Location: ../../auth/login.php");
    exit;
}

$page_title = $page_title ?? "Perpustakaan Digital";
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title) ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- Di bagian head -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/styles/kategori_admin.css">

</head>

<body>
    <?php include 'sidebar_admin.php'; ?>
    <main class="main-content">