<?php
session_start();
require_once '../../config.php';

// Cek role admin
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'admin') {
    header("Location: ../../auth/login.php");
    exit;
}

require_once 'kategori_handler.php';
?>

<?php

require_once '../../views/header.php';
$page_title = "Manajemen Kategori"; // Judul untuk halaman ini

?>



<div class="header">
    <h1>Manajemen Kategori</h1>
</div>

<div class="content-container">
    <?php include '../../views/alert_messages.php'; ?>
    <?php include 'kategori_display.php'; ?>
</div>
</main>

<?php include 'modal_kategori.php'; ?>
<?php include '../../views/footer.php'; ?>