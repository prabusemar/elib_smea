<?php
include_once 'config.php';

// Get filter parameters
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$kategori = isset($_GET['kategori']) ? (int)$_GET['kategori'] : 0;
$status = isset($_GET['status']) ? mysqli_real_escape_string($conn, $_GET['status']) : '';

// Get categories for dropdown
$kategories = mysqli_query($conn, "SELECT * FROM kategori ORDER BY NamaKategori");

// Jika ini request AJAX, hanya tampilkan hasil buku
if (isset($_GET['ajax']) && $_GET['ajax'] == 'true') {
    // Query to get books
    $query = "SELECT b.*, k.NamaKategori 
              FROM buku b
              LEFT JOIN kategori k ON b.KategoriID = k.KategoriID
              WHERE b.DeletedAt IS NULL";

    if (!empty($search)) {
        $query .= " AND (b.Judul LIKE '%$search%' OR b.Penulis LIKE '%$search%')";
    }
    if ($kategori > 0) {
        $query .= " AND b.KategoriID = $kategori";
    }
    if (!empty($status)) {
        $query .= " AND b.Status = '$status'";
    }
    $query .= " ORDER BY b.Judul ASC";

    $books = mysqli_query($conn, $query);

    if (mysqli_num_rows($books) === 0): ?>
        <div class="empty-state animate__animated animate__fadeIn">
            <div class="empty-state-icon">
                <i class="fas fa-book-open"></i>
            </div>
            <h3 class="empty-state-title">Tidak Ada Buku Ditemukan</h3>
            <p class="empty-state-text">Kami tidak dapat menemukan buku yang sesuai dengan kriteria pencarian Anda. Coba gunakan kata kunci atau filter yang berbeda.</p>
            <a href="?" class="btn btn-primary" style="padding: 0.75rem 1.5rem;">
                <i class="fas fa-undo"></i> Reset Pencarian
            </a>
        </div>
    <?php else: ?>
        <div class="books-grid">
            <?php
            $animationDelays = ['animate-delay-1', 'animate-delay-2', 'animate-delay-3'];
            $delayIndex = 0;
            while ($book = mysqli_fetch_assoc($books)):
                $delayClass = $animationDelays[$delayIndex % 3];
                $delayIndex++;
            ?>
                <div class="book-card animate__animated animate__fadeInUp <?= $delayClass ?>">
                    <div class="book-cover-container">
                        <img src="<?= BASE_URL . '/' . htmlspecialchars($book['Cover']) ?>"
                            alt="Cover Buku <?= htmlspecialchars($book['Judul']) ?>"
                            class="book-cover"
                            onerror="this.src='<?= BASE_URL ?>/assets/icon/default-book.png'">

                        <span class="book-badge <?= $book['Status'] === 'Premium' ? 'badge-premium' : 'badge-free' ?>">
                            <?= $book['Status'] ?>
                        </span>
                    </div>

                    <div class="book-details">
                        <span class="book-category">
                            <?= htmlspecialchars($book['NamaKategori'] ?? 'Umum') ?>
                        </span>
                        <h3 class="book-title"><?= htmlspecialchars($book['Judul']) ?></h3>
                        <p class="book-author">Oleh <?= htmlspecialchars($book['Penulis']) ?></p>

                        <div class="book-meta">
                            <div class="book-rating">
                                <?php
                                    // Ambil rata-rata rating dari tabel ulasan
                                    $bukuID = (int)$book['BukuID'];
                                    $queryRating = "SELECT COALESCE(AVG(Rating), 0.0) as avg_rating FROM ulasan WHERE BukuID = $bukuID";
                                    $resultRating = mysqli_query($conn, $queryRating);
                                    $ratingData = mysqli_fetch_assoc($resultRating);
                                    $avgRating = number_format($ratingData['avg_rating'], 1);
                                ?>
                                <i class="fas fa-star"></i> <?= $avgRating ?>
                            </div>
                            <small><?= (int)$book['JumlahBaca'] ?> pembaca</small>
                        </div>

                        <div class="book-actions">
                            <a href="detail_buku.php?id=<?= $book['BukuID'] ?>" class="btn-detail">
                                <i class="fas fa-info-circle"></i> Detail
                            </a>
                            <a href="<?= $book['Status'] === 'Premium' ? '#' : $book['DriveURL'] ?>"
                                class="btn-read"
                                <?= $book['Status'] === 'Premium' ? 'data-premium="true"' : '' ?>>
                                <i class="fas fa-book-open"></i> Baca
                            </a>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
<?php endif;
    exit(); // Hentikan eksekusi setelah output untuk AJAX
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Semua Buku - SMEA E-Lib</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/styles/style.css">
    <style>
        :root {
            --primary: #4f46e5;
            --primary-dark: #4338ca;
            --primary-light: #6366f1;
            --secondary: #f59e0b;
            --dark: #1e293b;
            --light: #f8fafc;
            --gray: #94a3b8;
            --success: #10b981;
            --premium-gold: #fbbf24;
            --premium-gradient: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%);
        }

        /* Modern Glass Morphism Effect */
        .glass-card {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-radius: 16px;
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        /* Hero Section Redesign */
        .hero-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 6rem 5% 4rem;
            margin-bottom: 3rem;
            position: relative;
            overflow: hidden;
            min-height: 60vh;
            display: flex;
            align-items: center;
        }

        .hero-section::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320"><path fill="%23ffffff" fill-opacity="0.1" d="M0,128L48,117.3C96,107,192,85,288,112C384,139,480,213,576,218.7C672,224,768,160,864,138.7C960,117,1056,139,1152,149.3C1248,160,1344,160,1392,160L1440,160L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"></path></svg>') no-repeat bottom/cover;
            z-index: 0;
        }

        .hero-content {
            position: relative;
            z-index: 1;
            max-width: 1400px;
            margin: 0 auto;
            width: 100%;
        }

        .hero-title {
            font-size: 3rem;
            font-weight: 800;
            color: white;
            margin-bottom: 1.5rem;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
            line-height: 1.2;
        }

        .hero-subtitle {
            font-size: 1.25rem;
            color: rgba(255, 255, 255, 0.9);
            margin-bottom: 3rem;
            max-width: 600px;
            line-height: 1.6;
            font-weight: 300;
        }

        /* Enhanced Filter Section */
        .filter-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2.5rem;
            border-radius: 24px;
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        }

        .filter-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
        }

        .filter-group {
            position: relative;
        }

        .filter-input {
            width: 100%;
            padding: 1.25rem 1.5rem;
            border: none;
            border-radius: 14px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.95);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            color: var(--dark);
        }

        .filter-input:focus {
            outline: none;
            box-shadow: 0 0 0 3px rgba(255, 255, 255, 0.5);
            background: white;
            transform: translateY(-2px);
        }

        .filter-input::placeholder {
            color: #94a3b8;
        }

        .search-button {
            background: var(--primary);
            color: white;
            border: none;
            padding: 0 2rem;
            height: 100%;
            border-radius: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
            box-shadow: 0 4px 15px rgba(79, 70, 229, 0.3);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .search-button:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(79, 70, 229, 0.4);
        }

        /* Floating decorative elements */
        .floating-books {
            position: absolute;
            right: 5%;
            bottom: 20%;
            z-index: 0;
            opacity: 0.7;
        }

        .floating-books i {
            font-size: 3rem;
            color: white;
            position: absolute;
            text-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        .floating-books i:nth-child(1) {
            top: -50px;
            right: 30px;
            transform: rotate(15deg);
        }

        .floating-books i:nth-child(2) {
            top: 20px;
            right: -20px;
            transform: rotate(-10deg);
        }

        .floating-books i:nth-child(3) {
            top: 70px;
            right: 40px;
            transform: rotate(5deg);
        }

        /* Book Grid Layout */
        .books-container {
            max-width: 1600px;
            margin: 0 auto;
            padding: 0 5% 4rem;
        }

        .books-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 2.5rem;
            margin-top: 3rem;
        }

        /* Book Card Design */
        .book-card {
            background: white;
            border-radius: 16px;
            overflow: hidden;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            position: relative;
            display: flex;
            flex-direction: column;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            border: 1px solid rgba(0, 0, 0, 0.05);
        }

        .book-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }

        .book-cover-container {
            position: relative;
            overflow: hidden;
            height: 380px;
        }

        .book-cover {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
            background-color: #f0f0f0;
            /* Placeholder background */
        }

        .book-card:hover .book-cover {
            transform: scale(1.03);
        }

        .book-badge {
            position: absolute;
            top: 1rem;
            right: 1rem;
            padding: 0.35rem 0.8rem;
            border-radius: 50px;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            z-index: 2;
        }

        .badge-free {
            background: var(--success);
            color: white;
        }

        .badge-premium {
            background: var(--premium-gradient);
            color: #451a03;
        }

        .book-details {
            padding: 1.75rem;
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .book-category {
            color: var(--primary);
            font-size: 0.85rem;
            font-weight: 600;
            margin-bottom: 0.75rem;
            display: inline-block;
            background: rgba(79, 70, 229, 0.1);
            padding: 0.25rem 0.75rem;
            border-radius: 50px;
            max-width: max-content;
            text-wrap: none;
            white-space: nowrap;
        }

        .book-title {
            font-size: 1.25rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            line-height: 1.4;
            color: var(--dark);
            flex: 1;
        }

        .book-author {
            color: var(--gray);
            font-size: 0.95rem;
            margin-bottom: 1rem;
        }

        .book-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 1.5rem;
        }

        .book-rating {
            display: flex;
            align-items: center;
            gap: 0.25rem;
            color: var(--secondary);
            font-weight: 700;
        }

        .book-actions {
            display: flex;
            gap: 1rem;
            margin-top: 1.5rem;
        }

        .btn-detail {
            flex: 1;
            background: white;
            color: var(--primary);
            border: 1px solid var(--primary);
            padding: 0.75rem;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            text-decoration: none;
        }

        .btn-detail:hover {
            background: rgba(79, 70, 229, 0.1);
        }

        .btn-read {
            flex: 1;
            background: var(--primary);
            color: white;
            border: none;
            padding: 0.75rem;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            text-decoration: none;
        }

        .btn-read:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
        }

        /* No Results State */
        .empty-state {
            text-align: center;
            padding: 4rem 0;
            grid-column: 1 / -1;
        }

        .empty-state-icon {
            font-size: 4rem;
            color: var(--primary);
            margin-bottom: 1.5rem;
            opacity: 0.7;
        }

        .empty-state-title {
            font-size: 1.5rem;
            color: var(--dark);
            margin-bottom: 0.5rem;
        }

        .empty-state-text {
            color: var(--gray);
            max-width: 500px;
            margin: 0 auto 1.5rem;
        }

        /* Loading Animation */
        .loading-spinner {
            display: inline-block;
            width: 2rem;
            height: 2rem;
            border: 3px solid rgba(79, 70, 229, 0.3);
            border-radius: 50%;
            border-top-color: var(--primary);
            animation: spin 1s ease-in-out infinite;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        /* Responsive Adjustments */
        @media (max-width: 1024px) {
            .books-grid {
                grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            }

            .hero-title {
                font-size: 2.5rem;
            }

            .floating-books {
                display: none;
            }
        }

        @media (max-width: 768px) {
            .hero-section {
                padding: 5rem 5% 3rem;
                min-height: auto;
            }

            .hero-title {
                font-size: 2.2rem;
            }

            .hero-subtitle {
                font-size: 1.1rem;
            }

            .filter-grid {
                grid-template-columns: 1fr;
            }

            .filter-container {
                padding: 1.5rem;
            }

            .book-cover-container {
                height: 320px;
            }
        }

        @media (max-width: 480px) {
            .hero-title {
                font-size: 1.8rem;
            }

            .filter-input {
                padding: 1rem;
            }

            .book-actions {
                flex-direction: column;
            }
        }

        /* Floating Action Button */
        .fabs {
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            width: 60px;
            height: 60px;
            background: var(--primary);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            box-shadow: 0 10px 25px rgba(79, 70, 229, 0.3);
            z-index: 100;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
        }

        .fabs:hover {
            transform: scale(1.1) translateY(-5px);
            background: var(--primary-dark);
            box-shadow: 0 15px 30px rgba(79, 70, 229, 0.4);
        }

        /* Animation Classes */
        .animate-delay-1 {
            animation-delay: 0.1s;
        }

        .animate-delay-2 {
            animation-delay: 0.2s;
        }

        .animate-delay-3 {
            animation-delay: 0.3s;
        }
    </style>
</head>

<body>
    <?php include 'views/navbar_index.php'; ?>

    <section class="hero-section">
        <div class="hero-content">
            <h1 class="hero-title animate__animated animate__fadeInDown">Jelajahi Dunia Literasi</h1>
            <p class="hero-subtitle animate__animated animate__fadeInDown animate__delay-1s">
                Temukan pengetahuan tanpa batas dalam koleksi buku digital kami. Mulai petualangan membaca Anda hari ini!
            </p>

            <div class="filter-container animate__animated animate__fadeInUp animate__delay-1s">
                <form method="GET" id="searchForm" class="filter-grid">
                    <div class="filter-group">
                        <input type="text" name="search" class="filter-input" placeholder="🔍 Cari judul atau penulis..."
                            value="<?= htmlspecialchars($search) ?>" id="searchInput">
                    </div>
                    <div class="filter-group">
                        <select name="kategori" class="filter-input" id="kategoriSelect">
                            <option value="">📚 Semua Kategori</option>
                            <?php
                            // Reset pointer untuk kategori
                            mysqli_data_seek($kategories, 0);
                            while ($k = mysqli_fetch_assoc($kategories)): ?>
                                <option value="<?= $k['KategoriID'] ?>" <?= $kategori == $k['KategoriID'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($k['NamaKategori']) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="filter-group">
                        <select name="status" class="filter-input" id="statusSelect">
                            <option value="">🏷️ Semua Status</option>
                            <option value="Free" <?= $status == 'Free' ? 'selected' : '' ?>>🌍 Gratis</option>
                            <option value="Premium" <?= $status == 'Premium' ? 'selected' : '' ?>>⭐ Premium</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <button type="submit" class="search-button">
                            <i class="fas fa-search"></i>
                        </button>
                        <style>
                        @media (max-width: 768px) {
                            .search-button {
                                padding: 0 0;
                                width: 100%;
                                height: 56px;
                                font-size: 1.25rem;
                            }
                        }
                        </style></style></style>
                    </div>
                </form>
            </div>

            <div class="floating-books animate__animated animate__fadeInRight animate__delay-2s">
                <i class="fas fa-book"></i>
                <i class="fas fa-book-open"></i>
                <i class="fas fa-bookmark"></i>
            </div>
        </div>
    </section>

    <div class="books-container">
        <div id="books-results">
            <!-- Hasil pencarian akan dimuat di sini via AJAX -->
            <div class="empty-state">
                <div class="empty-state-icon">
                    <div class="loading-spinner"></div>
                </div>
                <h3 class="empty-state-title">Memuat buku...</h3>
            </div>
        </div>
    </div>

    <a href="#" class="fabs animate__animated animate__fadeInUp animate__delay-2s">
        <i class="fas fa-arrow-up"></i>
    </a>

    <?php include 'views/footer_index.php'; ?>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Function untuk load books via AJAX
        function loadBooks() {
            const formData = $('#searchForm').serialize() + '&ajax=true';

            $.ajax({
                url: window.location.pathname,
                type: 'GET',
                data: formData,
                beforeSend: function() {
                    $('#books-results').html(`
                        <div class="empty-state">
                            <div class="empty-state-icon">
                                <div class="loading-spinner"></div>
                            </div>
                            <h3 class="empty-state-title">Memuat buku...</h3>
                        </div>
                    `);
                },
                success: function(response) {
                    $('#books-results').html(response);
                    initializeBookEvents();

                    // Update URL dengan parameter pencarian
                    const params = new URLSearchParams(formData);
                    history.replaceState(null, '', '?' + params.toString());
                },
                error: function() {
                    $('#books-results').html(`
                        <div class="empty-state">
                            <div class="empty-state-icon">
                                <i class="fas fa-exclamation-triangle"></i>
                            </div>
                            <h3 class="empty-state-title">Terjadi kesalahan</h3>
                            <p class="empty-state-text">Silakan coba lagi nanti.</p>
                        </div>
                    `);
                }
            });
        }

        // Function untuk inisialisasi event handlers
        function initializeBookEvents() {
            // Handle premium book click
            $('[data-premium="true"]').on('click', function(e) {
                e.preventDefault();
                Swal.fire({
                    title: 'Buku Premium',
                    html: 'Buku ini hanya tersedia untuk anggota premium. <br><b>Upgrade akun Anda</b> untuk mengakses seluruh koleksi premium kami!',
                    imageUrl: '<?= BASE_URL ?>/assets/icon/premium-badge.png',
                    imageWidth: 100,
                    imageAlt: 'Premium Badge',
                    confirmButtonText: 'Pelajari Lebih Lanjut',
                    confirmButtonColor: '#f59e0b',
                    showCancelButton: true,
                    cancelButtonText: 'Nanti Saja'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = 'premium.php';
                    }
                });
            });

            // Animate cards on scroll
            animateOnScroll();
        }

        // Event handler untuk form submission
        $(document).ready(function() {
            // Load books pertama kali
            loadBooks();

            // Handle form submission
            $('#searchForm').on('submit', function(e) {
                e.preventDefault();
                loadBooks();
            });

            // Handle perubahan pada input filter (real-time search)
            $('#searchForm input, #searchForm select').on('change keyup', function() {
                // Debounce untuk menghindari terlalu banyak request
                clearTimeout($(this).data('timeout'));
                $(this).data('timeout', setTimeout(function() {
                    loadBooks();
                }, 500));
            });

            // Back to top button
            $('.fabs').on('click', function(e) {
                e.preventDefault();
                $('html, body').animate({
                    scrollTop: 0
                }, 800);
                return false;
            });

            // Show/hide fab based on scroll position
            $(window).scroll(function() {
                if ($(this).scrollTop() > 300) {
                    $('.fabs').addClass('animate__fadeIn').removeClass('animate__fadeOut');
                } else {
                    $('.fabs').removeClass('animate__fadeIn').addClass('animate__fadeOut');
                }
            });

            // Animate cards on scroll
            function animateOnScroll() {
                $('.book-card').each(function() {
                    const cardTop = $(this).offset().top;
                    const windowBottom = $(window).scrollTop() + $(window).height();

                    if (cardTop < windowBottom - 100) {
                        $(this).addClass('animate__fadeInUp');
                    }
                });
            }

            $(window).on('scroll', animateOnScroll);
        });
    </script>
</body>

</html>