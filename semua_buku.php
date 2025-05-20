<?php
include 'config.php';

// Get filter parameters
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$kategori = isset($_GET['kategori']) ? (int)$_GET['kategori'] : 0;
$status = isset($_GET['status']) ? mysqli_real_escape_string($conn, $_GET['status']) : '';

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

// Get categories for dropdown
$kategories = mysqli_query($conn, "SELECT * FROM kategori ORDER BY NamaKategori");
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Semua Buku - SMEA E-Lib</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <?php include 'config.php'; ?>
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

        /* Hero Section with Parallax Effect */
        .hero-section {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            padding: 6rem 5% 4rem;
            margin-bottom: 3rem;
            position: relative;
            overflow: hidden;
        }

        .hero-section::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('<?= BASE_URL ?>/assets/patterns/circuit-board.svg') center/cover;
            opacity: 0.05;
            z-index: 0;
        }

        .hero-content {
            position: relative;
            z-index: 1;
            max-width: 1400px;
            margin: 0 auto;
        }

        .hero-title {
            font-size: 2.5rem;
            font-weight: 800;
            color: white;
            margin-bottom: 1rem;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .hero-subtitle {
            font-size: 1.1rem;
            color: rgba(255, 255, 255, 0.9);
            margin-bottom: 2rem;
            max-width: 600px;
        }

        /* Enhanced Filter Section */
        .filter-container {
            max-width: 1200px;
            margin: 2rem auto 0;
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
            padding: 1rem 1.5rem;
            border: none;
            border-radius: 12px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.9);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        }

        .filter-input:focus {
            outline: none;
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.3);
            background: white;
        }

        .search-button {
            background: white;
            color: var(--primary);
            border: none;
            padding: 0 2rem;
            height: 100%;
            border-radius: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .search-button:hover {
            background: var(--primary-light);
            color: white;
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
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

        /* Responsive Adjustments */
        @media (max-width: 1024px) {
            .books-grid {
                grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            }
        }

        @media (max-width: 768px) {
            .hero-section {
                padding: 5rem 5% 3rem;
            }

            .hero-title {
                font-size: 2rem;
            }

            .filter-grid {
                grid-template-columns: 1fr;
            }

            .book-cover-container {
                height: 320px;
            }
        }

        @media (max-width: 480px) {
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

            <div class="filter-container glass-card animate__animated animate__fadeInUp animate__delay-1s">
                <form method="GET" class="filter-grid">
                    <div class="filter-group">
                        <input type="text" name="search" class="filter-input" placeholder="🔍 Cari judul atau penulis..."
                            value="<?= htmlspecialchars($search) ?>">
                    </div>
                    <div class="filter-group">
                        <select name="kategori" class="filter-input">
                            <option value="">📚 Semua Kategori</option>
                            <?php while ($k = mysqli_fetch_assoc($kategories)): ?>
                                <option value="<?= $k['KategoriID'] ?>" <?= $kategori == $k['KategoriID'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($k['NamaKategori']) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="filter-group">
                        <select name="status" class="filter-input">
                            <option value="">🏷️ Semua Status</option>
                            <option value="Free" <?= $status == 'Free' ? 'selected' : '' ?>&#127758; Gratis</option>
                            <option value="Premium" <?= $status == 'Premium' ? 'selected' : '' ?>&#11088; Premium</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <button type="submit" class="search-button">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </section>

    <div class="books-container">
        <?php if (mysqli_num_rows($books) === 0): ?>
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
                                    <i class="fas fa-star"></i> <?= number_format($book['Rating'], 1) ?>
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
        <?php endif; ?>
    </div>

    <a href="#" class="fabs animate__animated animate__fadeInUp animate__delay-2s">
        <i class="fas fa-arrow-up"></i>
    </a>

    <?php include 'views/footer_index.php'; ?>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script>
        // Handle premium book click
        $(document).ready(function() {
            $('[data-premium="true"]').on('click', function(e) {
                e.preventDefault();
                Swal.fire({
                    title: 'Buku Premium',
                    html: 'Buku ini hanya tersedia untuk anggota premium. <br><b>Upgrade akun Anda</b> untuk mengakses seluruh koleksi premium kami!',
                    icon: 'info',
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

            // Run once on load and then on scroll
            animateOnScroll();
            $(window).on('scroll', animateOnScroll);
        });
    </script>
</body>

</html>