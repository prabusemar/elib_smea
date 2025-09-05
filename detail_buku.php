<?php
session_start();
include 'config.php';

// Ambil ID buku dari parameter URL
$bukuID = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Query untuk mendapatkan detail buku
$queryBuku = "SELECT b.*, k.NamaKategori 
              FROM buku b
              LEFT JOIN kategori k ON b.KategoriID = k.KategoriID
              WHERE b.BukuID = $bukuID AND b.DeletedAt IS NULL";
$resultBuku = mysqli_query($conn, $queryBuku);
$buku = mysqli_fetch_assoc($resultBuku);

// Jika buku tidak ditemukan
if (!$buku) {
    header("Location: semua_buku.php");
    exit();
}

// Query untuk mendapatkan ulasan buku
$queryUlasan = "SELECT u.*, p.Nama as NamaAnggota, p.FotoProfil
                FROM ulasan u
                JOIN anggota p ON u.MemberID = p.MemberID
                WHERE u.BukuID = $bukuID
                ORDER BY u.TanggalUlas DESC";
$ulasan = mysqli_query($conn, $queryUlasan);
$totalUlasan = mysqli_num_rows($ulasan);
// Hitung rata-rata rating
$avgRating = $buku['Rating'];
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($buku['Judul']) ?> - SMEA E-Lib</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/styles/style.css">
    <style>
        :root {
            --primary: #4361ee;
            --primary-dark: #3a0ca3;
            --secondary: #f72585;
            --accent: #4cc9f0;
            --light: #f8f9fa;
            --dark: #212529;
            --gray: #6c757d;
            --success: #2ecc71;
            --warning: #f39c12;
            --danger: #e74c3c;
            --shadow-sm: 0 1px 3px rgba(0, 0, 0, 0.12), 0 1px 2px rgba(0, 0, 0, 0.24);
            --shadow-md: 0 4px 6px rgba(0, 0, 0, 0.1), 0 1px 3px rgba(0, 0, 0, 0.08);
            --shadow-lg: 0 10px 25px rgba(0, 0, 0, 0.1), 0 5px 10px rgba(0, 0, 0, 0.05);
            --shadow-xl: 0 20px 40px rgba(0, 0, 0, 0.15), 0 10px 10px rgba(0, 0, 0, 0.05);
            --transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
        }

        /* Custom Styles */
        .book-detail-header {
            background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary) 100%);
            padding: 6rem 5% 4rem;
            color: white;
            position: relative;
            overflow: hidden;
        }

        .book-detail-header::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('<?= BASE_URL ?>/assets/patterns/pattern-dots.svg') repeat;
            opacity: 0.5;
            pointer-events: none;
        }

        .book-detail-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem 5% 4rem;
            position: relative;
        }

        .book-detail-grid {
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 3rem;
            align-items: flex-start;
        }

        .book-cover-container {
            position: relative;
            perspective: 1000px;
        }

        .book-cover-large {
            width: 100%;
            height: 500px;
            object-fit: cover;
            border-radius: 12px;
            box-shadow: var(--shadow-xl);
            transform-style: preserve-3d;
            transition: var(--transition);
            position: relative;
            z-index: 2;
            border: 5px solid white;
        }

        .book-cover-container:hover .book-cover-large {
            transform: translateY(-5px) rotateY(5deg);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.3);
        }

        .book-info {
            display: flex;
            flex-direction: column;
            position: relative;
        }

        .book-category {
            color: white;
            background: rgba(255, 255, 255, 0.2);
            padding: 0.5rem 1.2rem;
            border-radius: 50px;
            font-size: 0.9rem;
            font-weight: 600;
            display: inline-block;
            margin-bottom: 1.5rem;
            backdrop-filter: blur(5px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            max-width: max-content;
            white-space: nowrap;
        }

        .book-title {
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
            line-height: 1.2;
            font-weight: 800;
            text-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .book-author {
            font-size: 1.3rem;
            color: rgba(255, 255, 255, 0.9);
            margin-bottom: 1.5rem;
            font-weight: 500;
        }

        .book-author::before {
            content: "✍️ ";
        }

        .book-meta {
            display: flex;
            align-items: center;
            gap: 1.5rem;
            margin-bottom: 2rem;
            flex-wrap: wrap;
        }

        .book-rating {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 1.1rem;
            font-weight: 600;
            background: rgba(0, 0, 0, 0.2);
            padding: 0.5rem 1rem;
            border-radius: 50px;
            backdrop-filter: blur(5px);
        }

        .book-rating i {
            color: var(--warning);
        }

        .book-status {
            padding: 0.5rem 1.2rem;
            border-radius: 50px;
            font-size: 0.9rem;
            font-weight: 600;
            background: <?= $buku['Status'] === 'Premium' ? 'var(--warning)' : 'var(--success)' ?>;
            color: white;
            box-shadow: var(--shadow-sm);
        }

        .book-description {
            line-height: 1.8;
            margin-bottom: 2.5rem;
            color: rgba(255, 255, 255, 0.9);
            font-size: 1.05rem;
        }

        .book-description h3 {
            color: white;
            font-size: 1.3rem;
            margin-bottom: 1rem;
            position: relative;
            display: inline-block;
        }

        .book-description h3::after {
            content: "";
            position: absolute;
            bottom: -5px;
            left: 0;
            width: 50px;
            height: 3px;
            background: var(--accent);
            border-radius: 3px;
        }

        .book-actions {
            display: flex;
            gap: 1rem;
            margin-bottom: 3rem;
            flex-wrap: wrap;
        }

        .btn {
            padding: 0.8rem 1.5rem;
            font-size: 1rem;
            font-weight: 600;
            border-radius: 50px;
            text-decoration: none;
            color: white;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            box-shadow: var(--shadow-sm);
            transition: var(--transition);
        }

        .btn-detail {
            padding: 0.9rem 1.8rem;
            display: flex;
            align-items: center;
            gap: 0.7rem;
            font-weight: 600;
            border-radius: 50px;
            transition: var(--transition);

        }

        .btn-primary {
            background: white;
            color: var(--primary-dark);
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
        }

        .btn-outline {
            background: transparent;
            color: white;
            border: 2px solid white;
        }

        .btn-outline:hover {
            background: rgba(255, 255, 255, 0.1);
            transform: translateY(-3px);
        }

        .section-title {
            font-size: 1.8rem;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #eee;
            position: relative;
            color: var(--dark);
            font-weight: 700;
        }

        .section-title::after {
            content: "";
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 80px;
            height: 3px;
            background: var(--primary);
            border-radius: 3px;
        }

        .section-title i {
            margin-right: 0.8rem;
            color: var(--primary);
        }

        .reviews-container {
            margin-top: 4rem;
            animation: fadeIn 0.8s ease-out;
        }

        .review-card {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: var(--shadow-md);
            transition: var(--transition);
            border-left: 4px solid var(--primary);
        }

        .review-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
        }

        .review-header {
            display: flex;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .review-avatar {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 1.5rem;
            border: 3px solid var(--light);
            box-shadow: var(--shadow-sm);
        }

        .review-user {
            font-weight: 700;
            color: var(--dark);
            font-size: 1.1rem;
        }

        .review-date {
            color: var(--gray);
            font-size: 0.9rem;
            margin-top: 0.3rem;
        }

        .review-rating {
            color: var(--warning);
            margin-bottom: 1rem;
            font-size: 1.1rem;
        }

        .review-content {
            line-height: 1.7;
            color: var(--dark);
            font-size: 1rem;
        }

        .no-reviews {
            text-align: center;
            padding: 3rem;
            color: var(--gray);
            background: #f9f9f9;
            border-radius: 12px;
            box-shadow: var(--shadow-sm);
            border: 1px dashed #ddd;
        }

        .no-reviews i {
            font-size: 3.5rem;
            margin-bottom: 1.5rem;
            color: var(--primary);
            opacity: 0.7;
        }

        .no-reviews h3 {
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
            color: var(--dark);
        }

        .no-reviews p {
            font-size: 1.05rem;
            max-width: 500px;
            margin: 0 auto;
        }

        .floating-shapes {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            z-index: 1;
            pointer-events: none;
        }

        .shape {
            position: absolute;
            opacity: 0.1;
            border-radius: 50%;
            background: white;
        }

        .shape-1 {
            width: 200px;
            height: 200px;
            top: -50px;
            right: -50px;
            background: radial-gradient(circle, white 0%, transparent 70%);
        }

        .shape-2 {
            width: 300px;
            height: 300px;
            bottom: -100px;
            left: -100px;
            background: radial-gradient(circle, var(--accent) 0%, transparent 70%);
        }

        /* Floating animation */
        @keyframes float {

            0%,
            100% {
                transform: translateY(0);
            }

            50% {
                transform: translateY(-20px);
            }
        }

        .floating {
            animation: float 6s ease-in-out infinite;
        }

        /* Responsive styles */
        @media (max-width: 992px) {
            .book-detail-grid {
                grid-template-columns: 1fr;
            }

            .book-cover-large {
                max-width: 350px;
                margin: 0 auto;
            }

            .book-title {
                font-size: 2rem;
                text-align: center;
            }

            .book-author {
                text-align: center;
            }

            .book-meta {
                justify-content: center;
            }

            .book-actions {
                justify-content: center;
            }

            .book-description h3::after {
                left: 50%;
                transform: translateX(-50%);
            }
        }

        @media (max-width: 768px) {
            .book-detail-header {
                padding: 5rem 5% 3rem;
            }

            .book-cover-large {
                height: 400px;
            }

            .book-title {
                font-size: 1.8rem;
            }

            .book-author {
                font-size: 1.1rem;
            }
        }

        @media (max-width: 576px) {
            .book-cover-large {
                height: 350px;
                max-width: 280px;
            }

            .book-actions {
                flex-direction: column;
                align-items: center;
            }

            .btn-detail {
                width: 100%;
                justify-content: center;
            }
        }

        /* Animations */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-delay-1 {
            animation-delay: 0.2s;
        }

        .animate-delay-2 {
            animation-delay: 0.4s;
        }
    </style>
</head>

<body>
    <?php include 'views/navbar_index.php'; ?>

    <div class="book-detail-header">
        <div class="floating-shapes">
            <div class="shape shape-1"></div>
            <div class="shape shape-2"></div>
        </div>

        <div class="book-detail-container">
            <div class="book-detail-grid animate__animated animate__fadeIn">
                <div class="book-cover-container floating">
                    <img src="<?= BASE_URL . '/' . htmlspecialchars($buku['Cover']) ?>"
                        alt="Cover Buku <?= htmlspecialchars($buku['Judul']) ?>"
                        class="book-cover-large animate__animated animate__fadeInLeft"
                        onerror="this.src='<?= BASE_URL ?>/assets/icon/default-book.png'">

                </div>
                <div class="book-info animate__animated animate__fadeIn animate__delay-1s">
                    <span class="book-category"><?= htmlspecialchars($buku['NamaKategori'] ?? 'Umum') ?></span>
                    <h1 class="book-title"><?= htmlspecialchars($buku['Judul']) ?></h1>
                    <p class="book-author"><?= htmlspecialchars($buku['Penulis']) ?></p>

                    <div class="book-meta">
                        <div class="book-rating">
                            <i class="fas fa-star"></i> <?= number_format($avgRating, 1) ?> (<?= $totalUlasan ?> ulasan)
                        </div>
                        <span class="book-status">
                            <i class="fas fa-<?= $buku['Status'] === 'Premium' ? 'lock' : 'lock-open' ?>"></i>
                            <?= $buku['Status'] ?>
                        </span>
                    </div>

                    <div class="book-description">
                        <h3>Deskripsi Buku</h3>
                        <p><?= nl2br(htmlspecialchars($buku['Deskripsi'] ?? 'Tidak ada deskripsi tersedia')) ?></p>
                    </div>

                    <div class="book-actions">
                        <a href="<?= $buku['Status'] === 'Premium' ? '#' : $buku['DriveURL'] ?>"
                            class="btn btn-primary btn-detail"
                            data-status="<?= $buku['Status'] ?>"
                            style="color: var(--primary-dark); border: 2px solid var(--primary-dark); background: #fff; box-shadow: var(--shadow-sm);">
                            <i class="fas fa-book-reader"></i> Baca Sekarang
                        </a>

                        <a href="semua_buku.php" class="btn btn-outline btn-detail"
                            style="color: #fff; border: 2px solid #fff; background: rgba(67,97,238,0.15); box-shadow: var(--shadow-sm);">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                        <button class="btn btn-outline btn-detail" id="shareBtn"
                            style="color: #fff; border: 2px solid #fff; background: rgba(67,97,238,0.15); box-shadow: var(--shadow-sm);">
                            <i class="fas fa-share-alt"></i> Bagikan
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="book-detail-container">
        <div class="reviews-container animate__animated animate__fadeIn animate__delay-2s">
            <h2 class="section-title"><i class="fas fa-comments"></i> Ulasan Pembaca</h2>

            <?php if ($totalUlasan === 0): ?>
                <div class="no-reviews">
                    <i class="fas fa-comment-slash"></i>
                    <h3>Belum ada ulasan</h3>
                    <p>Jadilah yang pertama memberikan ulasan untuk buku ini setelah membacanya!</p>
                    <button class="btn btn-primary" style="margin-top: 1.5rem;" id="addReviewBtn">
                        <i style="margin-top:22px;color: white;" class="fas fa-plus"></i> Tambah Ulasan
                    </button>
                </div>
            <?php else: ?>
                <?php while ($review = mysqli_fetch_assoc($ulasan)): ?>
                    <div class="review-card">
                        <div class="review-header">
                            <img src="<?php
                                        $fotoProfil = !empty($review['FotoProfil']) ? $review['FotoProfil'] : 'assets/profiles/default.jpg';
                                        $fotoPath = ($fotoProfil === 'assets/profiles/default.jpg') ? BASE_URL . '/assets/profiles/default.jpg' : BASE_URL . '/' . htmlspecialchars($fotoProfil);
                                        echo $fotoPath;
                                        ?>"
                                alt="Foto Profil <?= htmlspecialchars($review['NamaAnggota']) ?>"
                                class="review-avatar"
                                onerror="this.src='<?= BASE_URL ?>/assets/profiles/default.jpg'">
                            <div>
                                <div class="review-user"><?= htmlspecialchars($review['NamaAnggota']) ?></div>
                                <div class="review-date"><?= date('d F Y', strtotime($review['TanggalUlas'])) ?></div>
                            </div>
                        </div>
                        <div class="review-rating">
                            <?php
                            // Tampilkan bintang rating sesuai nilai rating (bisa desimal)
                            $rating = floatval($review['Rating']);
                            for ($i = 1; $i <= 5; $i++) {
                                if ($rating >= $i) {
                                    // Full star
                                    echo '<i class="fas fa-star"></i>';
                                } elseif ($rating >= ($i - 0.5)) {
                                    // Half star
                                    echo '<i class="fas fa-star-half-alt"></i>';
                                } else {
                                    // Empty star
                                    echo '<i class="far fa-star"></i>';
                                }
                            }
                            ?>
                            <span style="margin-left: 0.5rem;"><?= $review['Rating'] ?></span>
                        </div>
                        <div class="review-content">
                            <?= nl2br(htmlspecialchars($review['Komentar'])) ?>
                        </div>
                    </div>
                <?php endwhile; ?>

                <button class="btn btn-primary btn-sm" id="addReviewBtn" style="font-size: 1rem;"></button>
                    <i class="fas fa-plus"></i> Tambah Ulasan Anda
                </button>
            <?php endif; ?>
        </div>
    </div>



    <?php include 'views/footer_index.php'; ?>

    <script>
        // Handle "Baca Sekarang" button click
        document.querySelectorAll('.btn-detail').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();

                const bookStatus = btn.getAttribute('data-status'); // Get book status (Premium or Free)
                const isLoggedIn = <?= isset($_SESSION['user_id']) ? 'true' : 'false' ?>; // Check if user is logged in

                if (bookStatus === 'Premium') {
                    // If the book is Premium, show alert for login
                    if (!isLoggedIn) {
                        Swal.fire({
                            title: 'Buku Premium',
                            text: 'Buku ini hanya tersedia untuk anggota berlangganan. Upgrade akun Anda untuk mengakses koleksi premium kami!',
                            icon: 'info',
                            confirmButtonText: 'Pelajari Lebih Lanjut',
                            confirmButtonColor: '#4361ee',
                            showCancelButton: true,
                            cancelButtonText: 'Tutup'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                window.location.href = 'premium.php'; // Redirect to premium page
                            }
                        });
                    } else {
                        // Redirect to the book's content if the user is logged in
                        window.location.href = btn.href;
                    }
                } else if (bookStatus === 'Free') {
                    // If the book is Free, check if the user is logged in
                    if (!isLoggedIn) {
                        Swal.fire({
                            title: 'Login Diperlukan',
                            text: 'Anda harus login terlebih dahulu untuk membaca buku ini.',
                            icon: 'warning',
                            confirmButtonText: 'Login Sekarang',
                            confirmButtonColor: '#4361ee',
                            showCancelButton: true,
                            cancelButtonText: 'Nanti Saja'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                window.location.href = 'login.php?redirect=' + encodeURIComponent(window.location.pathname + window.location.search);
                            }
                        });
                    } else {
                        // Redirect to the book's content if the user is logged in
                        window.location.href = btn.href;
                    }
                }
            });
        });


        // Share button functionality
        document.getElementById('shareBtn')?.addEventListener('click', () => {
            if (navigator.share) {
                navigator.share({
                    title: '<?= htmlspecialchars($buku['Judul']) ?>',
                    text: 'Lihat buku ini di SMEA E-Lib: <?= htmlspecialchars($buku['Judul']) ?> oleh <?= htmlspecialchars($buku['Penulis']) ?>',
                    url: window.location.href
                }).catch(err => {
                    console.log('Error sharing:', err);
                });
            } else {
                // Fallback for browsers that don't support Web Share API
                const shareUrl = `whatsapp://send?text=Baca "${encodeURIComponent('<?= htmlspecialchars($buku['Judul']) ?>')}" oleh ${encodeURIComponent('<?= htmlspecialchars($buku['Penulis']) ?>')} di SMEA E-Lib: ${encodeURIComponent(window.location.href)}`;
                window.open(shareUrl, '_blank');
            }
        });

        // Add review button
        document.getElementById('addReviewBtn')?.addEventListener('click', () => {
            // Check if user is logged in
            const isLoggedIn = <?= isset($_SESSION['user_id']) ? 'true' : 'false' ?>;

            if (!isLoggedIn) {
                Swal.fire({
                    title: 'Login Diperlukan',
                    text: 'Anda harus login terlebih dahulu untuk memberikan ulasan.',
                    icon: 'warning',
                    confirmButtonText: 'Login Sekarang',
                    confirmButtonColor: '#4361ee',
                    showCancelButton: true,
                    cancelButtonText: 'Nanti Saja'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = 'login.php?redirect=' + encodeURIComponent(window.location.pathname + window.location.search);
                    }
                });
            } else {
                // Show review form
                Swal.fire({
                    title: 'Tulis Ulasan Anda',
                    html: `
                        <div class="rating-stars" style="font-size: 2rem; text-align: center; margin: 1rem 0;">
                            <i class="far fa-star" data-rating="1" style="cursor: pointer; margin: 0 0.2rem;"></i>
                            <i class="far fa-star" data-rating="2" style="cursor: pointer; margin: 0 0.2rem;"></i>
                            <i class="far fa-star" data-rating="3" style="cursor: pointer; margin: 0 0.2rem;"></i>
                            <i class="far fa-star" data-rating="4" style="cursor: pointer; margin: 0 0.2rem;"></i>
                            <i class="far fa-star" data-rating="5" style="cursor: pointer; margin: 0 0.2rem;"></i>
                        </div>
                        <input type="hidden" id="rating-value" value="0">
                        <textarea id="review-text" class="swal2-textarea" placeholder="Bagikan pengalaman Anda membaca buku ini..." style="min-height: 150px;"></textarea>
                    `,
                    focusConfirm: false,
                    preConfirm: () => {
                        return {
                            rating: document.getElementById('rating-value').value,
                            review: document.getElementById('review-text').value
                        }
                    },
                    confirmButtonText: 'Kirim Ulasan',
                    showCancelButton: true,
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Submit review via AJAX
                        const formData = new FormData();
                        formData.append('buku_id', <?= $bukuID ?>);
                        formData.append('rating', result.value.rating);
                        formData.append('review', result.value.review);

                        fetch('submit_review.php', {
                                method: 'POST',
                                body: formData
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    Swal.fire({
                                        title: 'Terima Kasih!',
                                        text: 'Ulasan Anda telah berhasil dikirim.',
                                        icon: 'success',
                                        confirmButtonColor: '#4361ee'
                                    }).then(() => {
                                        location.reload();
                                    });
                                } else {
                                    Swal.fire({
                                        title: 'Error',
                                        text: data.message || 'Gagal mengirim ulasan',
                                        icon: 'error',
                                        confirmButtonColor: '#4361ee'
                                    });
                                }
                            })
                            .catch(error => {
                                Swal.fire({
                                    title: 'Error',
                                    text: 'Terjadi kesalahan saat mengirim ulasan',
                                    icon: 'error',
                                    confirmButtonColor: '#4361ee'
                                });
                            });
                    }
                });

                // Star rating interaction
                document.querySelectorAll('.rating-stars i').forEach(star => {
                    star.addEventListener('click', (e) => {
                        const rating = parseInt(e.target.getAttribute('data-rating'));
                        document.getElementById('rating-value').value = rating;

                        // Update star display
                        document.querySelectorAll('.rating-stars i').forEach((s, index) => {
                            if (index < rating) {
                                s.classList.remove('far');
                                s.classList.add('fas');
                            } else {
                                s.classList.remove('fas');
                                s.classList.add('far');
                            }
                        });
                    });

                    star.addEventListener('mouseover', (e) => {
                        const hoverRating = parseInt(e.target.getAttribute('data-rating'));

                        document.querySelectorAll('.rating-stars i').forEach((s, index) => {
                            if (index < hoverRating) {
                                s.classList.remove('far');
                                s.classList.add('fas');
                            } else {
                                s.classList.remove('fas');
                                s.classList.add('far');
                            }
                        });
                    });

                    star.addEventListener('mouseout', () => {
                        const currentRating = parseInt(document.getElementById('rating-value').value);

                        document.querySelectorAll('.rating-stars i').forEach((s, index) => {
                            if (index < currentRating) {
                                s.classList.remove('far');
                                s.classList.add('fas');
                            } else {
                                s.classList.remove('fas');
                                s.classList.add('far');
                            }
                        });
                    });
                });
            }
        });
    </script>

    <!-- SweetAlert2 for beautiful alerts -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</body>

</html>