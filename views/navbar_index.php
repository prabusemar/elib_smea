<?php
// Pastikan config.php sudah di-include di halaman yang memanggil navbar
if (!defined('BASE_URL')) {
    die('BASE_URL tidak didefinisikan. Pastikan config.php di-include');
}
?>

<header>
    <div class="logo-container">
        <img src="<?= BASE_URL ?>/assets/logo/logo-smea.png" alt="Logo Perpus">
        <div class="logo-text">
            <h1>SMEA E-Lib</h1>
            <span>Perpustakaan Digital Modern</span>
        </div>
    </div>

    <nav class="nav-menu">
        <a href="<?= BASE_URL ?>/#home">Beranda</a>
        <a href="<?= BASE_URL ?>/#features">Fitur</a>
        <a href="<?= BASE_URL ?>/#collections">Koleksi</a>
        <a href="<?= BASE_URL ?>/#testimonials">Testimoni</a>
        <a href="<?= BASE_URL ?>/#pricing">Langganan</a>
    </nav>

    <div class="nav-actions">
        <a href="<?= BASE_URL ?>/auth/login.php" class="btn btn-outline">
            <i class="fas fa-sign-in-alt"></i> Masuk
        </a>
        <a href="<?= BASE_URL ?>/auth/register.php" class="btn btn-primary">
            <i class="fas fa-user-plus"></i> Daftar
        </a>
    </div>

    <button class="mobile-menu-btn">
        <i class="fas fa-bars"></i>
    </button>
</header>

<link rel="stylesheet" href="<?= BASE_URL ?>/assets/styles/navbar.css">
<script src="<?= BASE_URL ?>/assets/js/navbar.js"></script>