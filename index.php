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
                <a href="#collections" class="btn btn-primary">
                    <i class="fas fa-book-open" style="margin-right: 0.5rem;"></i>Lihat Koleksi
                </a>
                <a href="#pricing" class="btn btn-outline" style="background: linear-gradient(90deg, #FFD700 0%, #FFC300 100%); color: #fff; border: none; box-shadow: 0 4px 15px rgba(255, 215, 0, 0.2); font-weight: 700;">
                    <i class="fas fa-crown" style="color: #fff; margin-right: 0.5rem;"></i>Premium
                </a>
            </div>

            <div class="hero-stats">
                <?php
                // Ambil jumlah buku
                $count_buku = mysqli_query($conn, "SELECT COUNT(*) AS total FROM buku WHERE DeletedAt IS NULL");
                $total_buku = mysqli_fetch_assoc($count_buku)['total'];

                // Ambil jumlah penulis unik
                $count_penulis = mysqli_query($conn, "SELECT COUNT(DISTINCT Penulis) AS total FROM buku WHERE DeletedAt IS NULL");
                $total_penulis = mysqli_fetch_assoc($count_penulis)['total'];

                // Ambil jumlah kategori
                $count_kategori = mysqli_query($conn, "SELECT COUNT(*) AS total FROM kategori");
                $total_kategori = mysqli_fetch_assoc($count_kategori)['total'];
                ?>
                <div class="stat-item">
                    <div class="stat-number"><?= number_format($total_buku) ?></div>
                    <div class="stat-label">Buku Digital</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number"><?= number_format($total_penulis) ?></div>
                    <div class="stat-label">Penulis</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number"><?= number_format($total_kategori) ?></div>
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
                $books_exist = mysqli_num_rows($buku_result) > 0;
                $delay = 1;
                if ($books_exist):
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
                    <?php $delay = $delay < 4 ? $delay + 1 : 1;
                    endwhile;
                else: ?>
                    <div class="no-books-message" style="text-align:center; width:100%; grid-column: 1/-1;">
                        <p>Tidak ada buku yang tersedia pada kategori ini.</p>
                    </div>
                <?php endif; ?>
            </div>
            <script>
                // Simple JS filter for tabs (client-side, for demo only)
                document.querySelectorAll('.collection-tabs .tab-btn').forEach(function(btn) {
                    btn.addEventListener('click', function() {
                        document.querySelectorAll('.collection-tabs .tab-btn').forEach(b => b.classList.remove('active'));
                        btn.classList.add('active');
                        var kategori = btn.getAttribute('data-kategori');
                        var cards = document.querySelectorAll('#booksGrid .book-card');
                        var found = false;
                        cards.forEach(function(card) {
                            if (kategori === 'all' || card.getAttribute('data-kategori') === kategori) {
                                card.style.display = 'flex';
                                found = true;
                            } else {
                                card.style.display = 'none';
                            }
                        });
                        // Handle no books message
                        var noBooksMsg = document.querySelector('#booksGrid .no-books-message');
                        if (!found) {
                            if (!noBooksMsg) {
                                noBooksMsg = document.createElement('div');
                                noBooksMsg.className = 'no-books-message';
                                noBooksMsg.style.textAlign = 'center';
                                noBooksMsg.style.width = '100%';
                                noBooksMsg.style.gridColumn = '1/-1';
                                noBooksMsg.innerHTML = '<p>Tidak ada buku yang tersedia pada kategori ini.</p>';
                                document.getElementById('booksGrid').appendChild(noBooksMsg);
                            }
                            noBooksMsg.style.display = 'block';
                        } else if (noBooksMsg) {
                            noBooksMsg.style.display = 'none';
                        }
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

    <!-- Reading Community Section -->
    <section class="community" id="community">
        <div class="section-title fade-in">
            <h2>Komunitas Pembaca</h2>
            <p>Bergabunglah dengan komunitas pembaca kami dan tingkatkan pengalaman membaca Anda</p>
        </div>

        <div class="community-container">
            <!-- Community Stats -->
            <div class="community-stats fade-in delay-1">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-content">
                        <h3>25,000+</h3>
                        <p>Anggota Aktif</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-comments"></i>
                    </div>
                    <div class="stat-content">
                        <h3>500+</h3>
                        <p>Diskusi Setiap Hari</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-book"></i>
                    </div>
                    <div class="stat-content">
                        <h3>1,200+</h3>
                        <p>Klub Buku</p>
                    </div>
                </div>
            </div>

            <!-- Community Features -->
            <div class="community-features">
                <!-- Feature 1 -->
                <div class="feature-card fade-in delay-2">
                    <div class="feature-icon">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <div class="feature-content">
                        <h3>Acara Rutin</h3>
                        <p>Ikuti acara bulanan seperti bedah buku, temu penulis, dan tantangan membaca.</p>
                        <a href="#" class="feature-link">Lihat Jadwal <i class="fas fa-arrow-right"></i></a>
                    </div>
                </div>

                <!-- Feature 2 -->
                <div class="feature-card fade-in delay-3">
                    <div class="feature-icon">
                        <i class="fas fa-trophy"></i>
                    </div>
                    <div class="feature-content">
                        <h3>Tantangan Membaca</h3>
                        <p>Ikuti tantangan membaca dengan tema berbeda setiap bulan dan dapatkan hadiah.</p>
                        <a href="#" class="feature-link">Lihat Tantangan <i class="fas fa-arrow-right"></i></a>
                    </div>
                </div>

                <!-- Feature 3 -->
                <div class="feature-card fade-in delay-4">
                    <div class="feature-icon">
                        <i class="fas fa-chart-bar"></i>
                    </div>
                    <div class="feature-content">
                        <h3>Statistik Komunitas</h3>
                        <p>Lihat buku paling populer, rating tertinggi, dan rekomendasi dari anggota lain.</p>
                        <a href="#" class="feature-link">Lihat Statistik <i class="fas fa-arrow-right"></i></a>
                    </div>
                </div>
            </div>

            <!-- Discussion Highlights -->
            <div class="discussion-highlights fade-in delay-1">
                <h3>Diskusi Populer</h3>
                <div class="discussion-grid">
                    <!-- Discussion 1 -->
                    <div class="discussion-card">
                        <div class="discussion-header">
                            <img src="https://randomuser.me/api/portraits/women/44.jpg" alt="User" class="user-avatar">
                            <div class="user-info">
                                <h4>Sarah Wijaya</h4>
                                <p>2 hari lalu · Klub Fiksi</p>
                            </div>
                        </div>
                        <div class="discussion-content">
                            <h4>Pendapat tentang akhir cerita Laskar Pelangi?</h4>
                            <p>Saya masih bingung dengan akhir cerita ini. Menurut kalian apa makna di balik akhir cerita yang terbuka ini?</p>
                        </div>
                        <div class="discussion-stats">
                            <span><i class="fas fa-comment"></i> 42 komentar</span>
                            <span><i class="fas fa-heart"></i> 128 suka</span>
                        </div>
                    </div>

                    <!-- Discussion 2 -->
                    <div class="discussion-card">
                        <div class="discussion-header">
                            <img src="https://randomuser.me/api/portraits/men/32.jpg" alt="User" class="user-avatar">
                            <div class="user-info">
                                <h4>Budi Santoso</h4>
                                <p>5 hari lalu · Klub Sains</p>
                            </div>
                        </div>
                        <div class="discussion-content">
                            <h4>Rekomendasi buku sains populer</h4>
                            <p>Saya baru mulai tertarik dengan sains populer. Apa buku favorit kalian di genre ini?</p>
                        </div>
                        <div class="discussion-stats">
                            <span><i class="fas fa-comment"></i> 35 komentar</span>
                            <span><i class="fas fa-heart"></i> 89 suka</span>
                        </div>
                    </div>

                    <!-- Discussion 3 -->
                    <div class="discussion-card">
                        <div class="discussion-header">
                            <img src="https://randomuser.me/api/portraits/women/68.jpg" alt="User" class="user-avatar">
                            <div class="user-info">
                                <h4>Dewi Lestari</h4>
                                <p>1 minggu lalu · Klub Penulis</p>
                            </div>
                        </div>
                        <div class="discussion-content">
                            <h4>Tips mengatasi writer's block</h4>
                            <p>Bagaimana cara kalian mengatasi saat mengalami kebuntuan dalam menulis?</p>
                        </div>
                        <div class="discussion-stats">
                            <span><i class="fas fa-comment"></i> 67 komentar</span>
                            <span><i class="fas fa-heart"></i> 156 suka</span>
                        </div>
                    </div>
                </div>

                <div class="view-all-discussions">
                    <a href="#" class="btn btn-outline">Lihat Semua Diskusi</a>
                </div>
            </div>

            <!-- Book Clubs -->
            <div class="book-clubs fade-in delay-2">
                <h3>Klub Buku Populer</h3>
                <div class="clubs-grid">
                    <!-- Club 1 -->
                    <div class="club-card">
                        <div class="club-cover" style="background-image: url('https://images.unsplash.com/photo-1544947950-fa07a98d237f?ixlib=rb-1.2.1&auto=format&fit=crop&w=500&q=80');">
                            <div class="club-members">
                                <i class="fas fa-users"></i> 1,245 anggota
                            </div>
                        </div>
                        <div class="club-info">
                            <h4>Fiksi Modern</h4>
                            <p>Diskusi buku-buku fiksi kontemporer dari seluruh dunia</p>
                            <div class="club-actions">
                                <a href="#" class="btn btn-small">Bergabung</a>
                                <a href="#" class="btn btn-small btn-outline">Detail</a>
                            </div>
                        </div>
                    </div>

                    <!-- Club 2 -->
                    <div class="club-card">
                        <div class="club-cover" style="background-image: url('https://images.unsplash.com/photo-1506880018603-83d5b814b5a6?ixlib=rb-1.2.1&auto=format&fit=crop&w=500&q=80');">
                            <div class="club-members">
                                <i class="fas fa-users"></i> 892 anggota
                            </div>
                        </div>
                        <div class="club-info">
                            <h4>Sains & Teknologi</h4>
                            <p>Eksplorasi buku sains populer dan perkembangan teknologi</p>
                            <div class="club-actions">
                                <a href="#" class="btn btn-small">Bergabung</a>
                                <a href="#" class="btn btn-small btn-outline">Detail</a>
                            </div>
                        </div>
                    </div>

                    <!-- Club 3 -->
                    <div class="club-card">
                        <div class="club-cover" style="background-image: url('https://images.unsplash.com/photo-1456513080510-7bf3a84b82f8?ixlib=rb-1.2.1&auto=format&fit=crop&w=500&q=80');">
                            <div class="club-members">
                                <i class="fas fa-users"></i> 1,567 anggota
                            </div>
                        </div>
                        <div class="club-info">
                            <h4>Sejarah Dunia</h4>
                            <p>Menelusuri sejarah melalui buku-buku berkualitas</p>
                            <div class="club-actions">
                                <a href="#" class="btn btn-small">Bergabung</a>
                                <a href="#" class="btn btn-small btn-outline">Detail</a>
                            </div>
                        </div>
                    </div>
                </div>
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