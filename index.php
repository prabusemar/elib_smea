<?php
$page_title = "Perpustakaan Digital - SMEA E-Lib";
$page_description = "Jelajahi ribuan buku digital dengan akses tanpa batas. Bergabunglah dengan komunitas pembaca kami dan nikmati pengalaman membaca yang tak tertandingi.";
$page_keywords = "perpustakaan digital, buku online, baca buku, ebook, SMEA E-Lib, koleksi buku, langganan buku, komunitas pembaca";
$page_author = "SMEA E-Lib";
include 'config.php';


?>
<!DOCTYPE html>
<html lang="id">

<?php
include 'views/header_index.php';
?>

<body>
    <?php include 'views/navbar_index.php'; ?>

    <!-- Hero Section -->
    <section class="hero" id="home">
        <div class="hero-content fade-in">
            <h1>Jelajahi Dunia Tanpa Batas dengan Ribuan Buku Digital</h1>
            <p>Temukan pengetahuan, petualangan, dan inspirasi dalam genggaman Anda. Akses ribuan buku berkualitas kapan saja, di mana saja.</p>

            <div class="hero-buttons">
                <a href="#collections" class="btn btn-primary">Lihat Koleksi</a>
                <a href="#pricing" class="btn btn-outline" style="background: linear-gradient(90deg, #FFD700 0%, #FFC300 100%); color: #fff; border: none; box-shadow: 0 4px 15px rgba(255, 215, 0, 0.2); font-weight: 700;">
                    <i class="fas fa-crown" style="color: #fff; margin-right: 0.5rem;"></i>Premium Gold
                </a>
            </div>

            <div class="hero-stats">
                <div class="stat-item">
                    <div class="stat-number">10,000+</div>
                    <div class="stat-label">Buku Digital</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number">500+</div>
                    <div class="stat-label">Penulis</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number">50+</div>
                    <div class="stat-label">Kategori</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features" id="features">
        <div class="section-title fade-in">
            <h2>Mengapa Memilih SMEA E-Lib?</h2>
            <p>Kami menyediakan pengalaman membaca digital yang tak tertandingi dengan fitur-fitur unggulan</p>
        </div>

        <div class="features-grid">
            <div class="feature-card fade-in delay-1">
                <div class="feature-icon">
                    <i class="fas fa-book-open"></i>
                </div>
                <h3>Akses Tanpa Batas</h3>
                <p>Baca ribuan buku kapan saja dan di mana saja dengan akses 24/7 melalui perangkat apapun.</p>
            </div>

            <div class="feature-card fade-in delay-2">
                <div class="feature-icon">
                    <i class="fas fa-mobile-alt"></i>
                </div>
                <h3>Baca Offline</h3>
                <p>Download buku favorit Anda dan baca tanpa koneksi internet saat sedang bepergian.</p>
            </div>

            <div class="feature-card fade-in delay-3">
                <div class="feature-icon">
                    <i class="fas fa-bookmark"></i>
                </div>
                <h3>Bookmark & Catatan</h3>
                <p>Simpan halaman favorit dan buat catatan pribadi untuk meningkatkan pengalaman membaca.</p>
            </div>

            <div class="feature-card fade-in delay-4">
                <div class="feature-icon">
                    <i class="fas fa-search"></i>
                </div>
                <h3>Pencarian Canggih</h3>
                <p>Temukan buku yang tepat dengan cepat menggunakan sistem pencarian dan rekomendasi kami.</p>
            </div>

            <div class="feature-card fade-in delay-1">
                <div class="feature-icon">
                    <i class="fas fa-user-friends"></i>
                </div>
                <h3>Komunitas Pembaca</h3>
                <p>Bergabunglah dengan grup diskusi dan berbagi pemikiran dengan pembaca lainnya.</p>
            </div>

            <div class="feature-card fade-in delay-2">
                <div class="feature-icon">
                    <i class="fas fa-chart-line"></i>
                </div>
                <h3>Statistik Membaca</h3>
                <p>Lacak kemajuan membaca Anda dengan fitur statistik dan pencapaian yang menarik.</p>
            </div>
        </div>
    </section>

    <!-- Book Collections -->
    <section class="collections" id="collections">
        <div class="container">
            <div class="collections-header fade-in">
                <h2>Koleksi Buku Terbaru</h2>
                <div class="collection-tabs">
                    <button class="tab-btn active" data-kategori="all">Semua</button>
                    <?php
                    // Ambil kategori dari database
                    $kategori_query = "SELECT KategoriID, NamaKategori FROM kategori ORDER BY NamaKategori";
                    $kategori_result = mysqli_query($conn, $kategori_query);
                    while ($kat = mysqli_fetch_assoc($kategori_result)): ?>
                        <button class="tab-btn" data-kategori="<?= $kat['KategoriID'] ?>">
                            <?= htmlspecialchars($kat['NamaKategori']) ?>
                        </button>
                    <?php endwhile; ?>
                </div>
            </div>

            <div class="books-grid" id="booksGrid">
                <?php
                // Ambil 8 buku terbaru dari database
                $buku_query = "SELECT b.*, k.NamaKategori 
                               FROM buku b
                               LEFT JOIN kategori k ON b.KategoriID = k.KategoriID
                               WHERE b.DeletedAt IS NULL
                               ORDER BY b.BukuID DESC
                               LIMIT 8";
                $buku_result = mysqli_query($conn, $buku_query);
                $delay = 1;
                while ($buku = mysqli_fetch_assoc($buku_result)):
                    // Cover
                    $cover = !empty($buku['Cover']) ? htmlspecialchars($buku['Cover']) : 'assets/icon/default-book.png';
                    // Status badge
                    $isPremium = strtolower($buku['Status']) === 'premium';
                    $badgeClass = $isPremium ? 'book-badge premium' : 'book-badge';
                    $badgeText = $isPremium ? 'Premium' : 'Free';
                    // Rating
                    $rating = is_numeric($buku['Rating']) ? number_format($buku['Rating'], 1) : '-';
                    // Tahun
                    $tahun = htmlspecialchars($buku['TahunTerbit']);
                    // Penulis
                    $penulis = htmlspecialchars($buku['Penulis']);
                    // Judul
                    $judul = htmlspecialchars($buku['Judul']);
                    // Kategori
                    $kategori = htmlspecialchars($buku['NamaKategori']);
                    // Link detail
                    $detail_link = "detail_buku.php?id=" . $buku['BukuID'];
                ?>
                <div class="book-card fade-in delay-<?= $delay ?>" data-kategori="<?= $buku['KategoriID'] ?>" style="display: flex; flex-direction: column; height: 100%;">
                    <img src="<?= $cover ?>" alt="Book Cover" class="book-cover"
                        onerror="this.src='assets/icon/default-book.png'">
                    <span class="<?= $badgeClass ?>"><?= $badgeText ?></span>
                    <div class="book-details" style="flex: 1 1 auto; display: flex; flex-direction: column;">
                        <h3 style="font-size: 1rem; white-space: normal; word-break: break-word;"><?= $judul ?></h3>
                        <p class="author">oleh <?= $penulis ?></p>
                        <div class="book-meta">
                            <span class="book-rating">
                                <i class="fas fa-star"></i> <?= $rating ?>
                            </span>
                            <span><?= $tahun ?></span>
                        </div>
                        <div style="flex:1"></div>
                        <div class="book-actions" style="margin-top: auto;">
                            <a href="<?= $detail_link ?>" class="btn btn-outline"><i class="fas fa-info-circle"></i> Detail</a>
                            <a href="<?= $detail_link ?>" class="btn btn-primary"><i class="fas fa-book-reader"></i> Baca</a>
                        </div>
                    </div>
                </div>
                <?php $delay = $delay < 4 ? $delay + 1 : 1; endwhile; ?>
            </div>
            <script>
            // Simple JS filter for tabs (client-side, for demo only)
            document.querySelectorAll('.collection-tabs .tab-btn').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    document.querySelectorAll('.collection-tabs .tab-btn').forEach(b => b.classList.remove('active'));
                    btn.classList.add('active');
                    var kategori = btn.getAttribute('data-kategori');
                    document.querySelectorAll('#booksGrid .book-card').forEach(function(card) {
                        if (kategori === 'all' || card.getAttribute('data-kategori') === kategori) {
                            card.style.display = 'flex';
                        } else {
                            card.style.display = 'none';
                        }
                    });
                });
            });
            </script>

            <div class="view-all fade-in">
                <a href="semua_buku.php" class="btn btn-outline">Lihat Semua Buku</a>
            </div>
        </div>
    </section>

    <!-- Testimonials -->
    <section class="testimonials" id="testimonials">
        <div class="section-title fade-in">
            <h2>Apa Kata Mereka?</h2>
            <p>Testimoni dari anggota kami yang puas dengan layanan SMEA E-Lib</p>
        </div>

        <div class="testimonials-slider fade-in delay-1">
            <!-- Testimonial 1 -->
            <div class="testimonial-card">
                <p class="testimonial-content">"SMEA E-Lib telah mengubah cara saya membaca. Sekarang saya bisa mengakses ribuan buku dari mana saja. Fitur baca offline sangat membantu saat saya bepergian."</p>
                <div class="testimonial-author">
                    <img src="https://randomuser.me/api/portraits/women/32.jpg" alt="User" class="author-avatar">
                    <div class="author-info">
                        <h4>Diana Sari</h4>
                        <p>Guru & Pembaca Aktif</p>
                    </div>
                </div>
            </div>

            <!-- Testimonial 2 -->
            <div class="testimonial-card">
                <p class="testimonial-content">"Sebagai mahasiswa, koleksi buku akademik di SMEA E-Lib sangat membantu studi saya. Saya bisa menemukan referensi yang sulit didapatkan di perpustakaan fisik."</p>
                <div class="testimonial-author">
                    <img src="https://randomuser.me/api/portraits/men/45.jpg" alt="User" class="author-avatar">
                    <div class="author-info">
                        <h4>Andi Pratama</h4>
                        <p>Mahasiswa Teknik</p>
                    </div>
                </div>
            </div>

            <!-- Testimonial 3 -->
            <div class="testimonial-card">
                <p class="testimonial-content">"Saya suka fitur komunitas pembacanya. Bisa berdiskusi tentang buku yang sedang dibaca dengan orang lain membuat pengalaman membaca lebih menyenangkan."</p>
                <div class="testimonial-author">
                    <img src="https://randomuser.me/api/portraits/women/68.jpg" alt="User" class="author-avatar">
                    <div class="author-info">
                        <h4>Rina Wijaya</h4>
                        <p>Book Blogger</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Pricing Section -->
    <section class="pricing" id="pricing">
        <div class="section-title fade-in">
            <h2>Pilihan Langganan</h2>
            <p>Tingkatkan pengalaman membaca Anda dengan keanggotaan premium</p>
        </div>

        <div class="pricing-grid">
            <!-- Free Plan -->
            <div class="pricing-card fade-in delay-1">
                <h3>Free</h3>
                <div class="price" style="font-size: 3.5rem;">
                    <span style="word-break: break-all; white-space: normal; font-size: 0.75em;">Rp0</span>
                    <span style="font-size: 0.5em;">/bulan</span>
                </div>
                <ul class="pricing-features">
                    <li><i class="fas fa-check"></i> Akses ke 5.000+ buku gratis</li>
                    <li><i class="fas fa-check"></i> Baca online</li>
                    <li><i class="fas fa-check"></i> Bookmark dasar</li>
                    <li><i class="fas fa-check"></i> 1 buku offline</li>
                    <li><i class="fas fa-times"></i> Buku premium</li>
                    <li><i class="fas fa-times"></i> Fitur komunitas</li>
                </ul>
                <a href="auth/register.php" class="btn btn-outline">Daftar Gratis</a>
            </div>

            <!-- Premium Plan -->
            <div class="pricing-card popular fade-in delay-2">
                <div class="popular-badge">Populer</div>
                <h3>Premium</h3>
                <div class="price" style="font-size: 3.5rem;">
                    <span style="word-break: break-all; white-space: normal; font-size: 0.75em;">Rp50.000</span>
                    <span style="font-size: 0.5em;">/bulan</span>
                </div>
                <ul class="pricing-features">
                    <li><i class="fas fa-check"></i> Semua fitur Free</li>
                    <li><i class="fas fa-check"></i> Akses ke 10.000+ buku premium</li>
                    <li><i class="fas fa-check"></i> Download hingga 20 buku offline</li>
                    <li><i class="fas fa-check"></i> Catatan dan highlight</li>
                    <li><i class="fas fa-check"></i> Akses komunitas pembaca</li>
                    <li><i class="fas fa-check"></i> Rekomendasi personal</li>
                </ul>
                <a href="auth/register.php" class="btn btn-primary">Mulai Sekarang</a>
            </div>

            <!-- Annual Plan -->
            <div class="pricing-card fade-in delay-3">
                <h3>Tahunan</h3>
                <div class="price" style="font-size: 3.5rem;">
                    <span style="word-break: break-all; white-space: normal; font-size: 0.75em;">Rp450.000</span>
                    <span style="font-size: 0.5em;">/tahun</span>
                </div>
                <ul class="pricing-features">
                    <li><i class="fas fa-check"></i> Semua fitur Premium</li>
                    <li><i class="fas fa-check"></i> Hemat 25% dibanding bulanan</li>
                    <li><i class="fas fa-check"></i> Download hingga 50 buku offline</li>
                    <li><i class="fas fa-check"></i> Laporan membaca mingguan</li>
                    <li><i class="fas fa-check"></i> Prioritas dukungan</li>
                    <li><i class="fas fa-check"></i> Hadiah buku bulanan</li>
                </ul>
                <a href="auth/register.php" class="btn btn-outline">Pilih Tahunan</a>
            </div>
        </div>
    </section>

    <!-- Newsletter -->
    <section class="newsletter">
        <div class="section-title fade-in">
            <h2>Dapatkan Update Terbaru</h2>
            <p>Berlangganan newsletter kami untuk mendapatkan informasi tentang buku baru, promo, dan acara menarik</p>
        </div>

        <form class="newsletter-form fade-in delay-1">
            <input type="email" placeholder="Alamat email Anda" required>
            <button type="submit" class="btn btn-primary">Berlangganan</button>
        </form>
    </section>

    <?php include 'views/footer_index.php'; ?>

    <script src="<?= BASE_URL ?>/assets/js/index.js"></script>
</body>

</html>