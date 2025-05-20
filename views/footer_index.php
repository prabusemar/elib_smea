<?php
// Pastikan config.php sudah di-include di halaman yang memanggil navbar
if (!defined('BASE_URL')) {
    die('BASE_URL tidak didefinisikan. Pastikan config.php di-include');
}
?>

<!-- Footer -->
<footer>
    <div class="footer-grid">
        <div class="footer-logo">
            <img src="https://cdn-icons-png.flaticon.com/512/3565/3565418.png" alt="Logo Perpus">
            <p>SMEA E-Lib adalah perpustakaan digital modern yang menyediakan akses ke ribuan buku berkualitas untuk semua kalangan.</p>
            <div class="social-links">
                <a href="#"><i class="fab fa-facebook-f"></i></a>
                <a href="#"><i class="fab fa-twitter"></i></a>
                <a href="#"><i class="fab fa-instagram"></i></a>
                <a href="#"><i class="fab fa-youtube"></i></a>
            </div>
        </div>

        <div class="footer-links">
            <h3>Tautan Cepat</h3>
            <ul>
                <li><a href="#home">Beranda</a></li>
                <li><a href="#features">Fitur</a></li>
                <li><a href="#collections">Koleksi</a></li>
                <li><a href="#testimonials">Testimoni</a></li>
                <li><a href="#pricing">Langganan</a></li>
            </ul>
        </div>

        <div class="footer-links">
            <h3>Kategori</h3>
            <ul>
                <li><a href="#">Fiksi</a></li>
                <li><a href="#">Non-Fiksi</a></li>
                <li><a href="#">Sains & Teknologi</a></li>
                <li><a href="#">Bisnis & Ekonomi</a></li>
                <li><a href="#">Kesehatan</a></li>
            </ul>
        </div>

        <div class="footer-contact">
            <h3>Kontak Kami</h3>
            <p><i class="fas fa-map-marker-alt"></i> Jl. Pendidikan No. 123, Jakarta</p>
            <p><i class="fas fa-phone-alt"></i> (021) 1234-5678</p>
            <p><i class="fas fa-envelope"></i> info@smea-elib.com</p>
            <p><i class="fas fa-clock"></i> Senin-Jumat: 08.00-17.00</p>
        </div>
    </div>

    <div class="footer-bottom">
        <p>&copy; 2023 SMEA E-Lib. All rights reserved. | <a href="#">Kebijakan Privasi</a> | <a href="#">Syarat & Ketentuan</a></p>
    </div>
</footer>

<link rel="stylesheet" href="<?= BASE_URL ?>/assets/styles/footer.css">