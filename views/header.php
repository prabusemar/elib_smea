<?php
include '../config.php';
require_once '../admin/kategori/kategori_admin.php';
?>
<!-- header.php -->
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($page_title) ? $page_title : "Default Title" ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="../assets/styles/kategori_admin.css">
    <script src="../assets/js/kategori_script.js"></script>
</head>

<body>
    <?php include 'sidebar_admin.php'; ?>
    <main class="main-content">