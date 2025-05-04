<?php
// header.php
require_once __DIR__ . '/../config.php'; // Gunakan __DIR__ untuk path absolut
// Hapus require_once kategori_admin.php karena tidak diperlukan di sini
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($page_title) ? $page_title : "Default Title" ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- Di bagian head -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/styles/kategori_admin.css">

</head>

<body>
    <?php include 'sidebar_admin.php'; ?>
    <main class="main-content">