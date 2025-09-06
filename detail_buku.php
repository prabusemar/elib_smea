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

// Cek apakah user sudah login
$isLoggedIn = isset($_SESSION['user_id']);
$userID = $isLoggedIn ? $_SESSION['user_id'] : 0;
$memberID = 0;

// Get the correct MemberID from anggota table if user is logged in
if ($isLoggedIn) {
    // First get user email from users table
    $queryUser = "SELECT email FROM users WHERE id = $userID AND is_deleted = 0";
    $resultUser = mysqli_query($conn, $queryUser);

    if ($resultUser && mysqli_num_rows($resultUser) > 0) {
        $userData = mysqli_fetch_assoc($resultUser);
        $userEmail = $userData['email'];

        // Now get MemberID from anggota table using email - HANYA yang tidak dihapus
        $queryAnggota = "SELECT MemberID FROM anggota WHERE Email = '$userEmail' AND is_deleted = 0";
        $resultAnggota = mysqli_query($conn, $queryAnggota);

        if ($resultAnggota && mysqli_num_rows($resultAnggota) > 0) {
            $anggotaData = mysqli_fetch_assoc($resultAnggota);
            $memberID = $anggotaData['MemberID'];
        } else {
            // Jika anggota tidak ditemukan atau dihapus, coba buat record baru
            $queryUserData = "SELECT full_name, email, password FROM users WHERE id = $userID AND is_deleted = 0";
            $resultUserData = mysqli_query($conn, $queryUserData);

            if ($resultUserData && mysqli_num_rows($resultUserData) > 0) {
                $userFullData = mysqli_fetch_assoc($resultUserData);

                $queryCreateAnggota = "INSERT INTO anggota (Nama, Email, Password, TanggalBergabung, Status, JenisAkun) 
                                      VALUES ('" . mysqli_real_escape_string($conn, $userFullData['full_name']) . "', 
                                              '" . mysqli_real_escape_string($conn, $userFullData['email']) . "', 
                                              '" . mysqli_real_escape_string($conn, $userFullData['password']) . "', 
                                              CURDATE(), 'Active', 'Free')";

                if (mysqli_query($conn, $queryCreateAnggota)) {
                    $memberID = mysqli_insert_id($conn);
                }
            }
        }
    }
}

// Cek apakah buku sudah difavoritkan oleh user
$isFavorited = false;
if ($isLoggedIn && $memberID > 0) {
    $queryFavorit = "SELECT * FROM favorit WHERE MemberID = $memberID AND BukuID = $bukuID";
    $resultFavorit = mysqli_query($conn, $queryFavorit);
    $isFavorited = mysqli_num_rows($resultFavorit) > 0;
}

// Handle add/remove favorite
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['toggle_favorite'])) {
    if (!$isLoggedIn) {
        $_SESSION['toast'] = [
            'type' => 'error',
            'message' => "Anda harus login terlebih dahulu untuk menambahkan ke favorit"
        ];
        header("Location: login.php?redirect=" . urlencode($_SERVER['REQUEST_URI']));
        exit();
    }

    if ($memberID === 0) {
        $_SESSION['toast'] = [
            'type' => 'error',
            'message' => "Akun anggota tidak ditemukan. Silakan logout dan login kembali."
        ];
        header("Location: " . $_SERVER['REQUEST_URI']);
        exit();
    }

    if ($isFavorited) {
        // Remove from favorites
        $queryDelete = "DELETE FROM favorit WHERE MemberID = $memberID AND BukuID = $bukuID";
        if (mysqli_query($conn, $queryDelete)) {
            $isFavorited = false;
            $_SESSION['toast'] = [
                'type' => 'success',
                'message' => "Buku dihapus dari favorit"
            ];
        } else {
            $_SESSION['toast'] = [
                'type' => 'error',
                'message' => "Gagal menghapus dari favorit: " . mysqli_error($conn)
            ];
        }
    } else {
        // Add to favorites
        $queryInsert = "INSERT INTO favorit (MemberID, BukuID, TanggalDitambahkan) VALUES ($memberID, $bukuID, NOW())";
        if (mysqli_query($conn, $queryInsert)) {
            $isFavorited = true;
            $_SESSION['toast'] = [
                'type' => 'success',
                'message' => "Buku ditambahkan ke favorit"
            ];
        } else {
            $_SESSION['toast'] = [
                'type' => 'error',
                'message' => "Gagal menambahkan ke favorit: " . mysqli_error($conn)
            ];
        }
    }

    // Redirect to avoid form resubmission
    header("Location: " . $_SERVER['REQUEST_URI']);
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

        /* Toast Notification Styles */
        .toast-container {
            position: fixed;
            top: 100px;
            right: 20px;
            z-index: 9999;
            display: flex;
            flex-direction: column;
            gap: 10px;
            max-width: 350px;
        }

        .toast {
            padding: 16px 20px;
            border-radius: 12px;
            box-shadow: var(--shadow-xl);
            display: flex;
            align-items: center;
            gap: 12px;
            animation: slideInRight 0.3s ease-out, fadeOut 0.5s ease-in 4.5s forwards;
            transform: translateX(400px);
            opacity: 0;
            transition: transform 0.3s ease-out, opacity 0.3s ease-out;
        }

        .toast.show {
            transform: translateX(0);
            opacity: 1;
        }

        .toast.hide {
            transform: translateX(400px);
            opacity: 0;
        }

        .toast-success {
            background: linear-gradient(135deg, #2ecc71 0%, #27ae60 100%);
            color: white;
            border-left: 4px solid #27ae60;
        }

        .toast-error {
            background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
            color: white;
            border-left: 4px solid #c0392b;
        }

        .toast-icon {
            font-size: 1.5rem;
            flex-shrink: 0;
        }

        .toast-content {
            flex: 1;
        }

        .toast-title {
            font-weight: 600;
            margin-bottom: 4px;
            font-size: 1rem;
        }

        .toast-message {
            font-size: 0.9rem;
            opacity: 0.9;
        }

        .toast-close {
            background: none;
            border: none;
            color: white;
            font-size: 1.2rem;
            cursor: pointer;
            opacity: 0.7;
            transition: opacity 0.2s;
            padding: 0;
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
        }

        .toast-close:hover {
            opacity: 1;
            background: rgba(255, 255, 255, 0.1);
        }

        @keyframes slideInRight {
            from {
                transform: translateX(400px);
                opacity: 0;
            }

            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        @keyframes fadeOut {
            from {
                opacity: 1;
            }

            to {
                opacity: 0;
            }
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

        /* Favorite Button Styles */
        .favorite-btn {
            position: absolute;
            top: 20px;
            right: 20px;
            background: rgba(255, 255, 255, 0.95);
            border: none;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: var(--shadow-lg);
            transition: var(--transition);
            z-index: 3;
        }

        .favorite-btn:hover {
            transform: scale(1.1);
            background: white;
        }

        .favorite-btn.active {
            background: var(--secondary);
        }

        .favorite-btn.active i {
            color: white;
        }

        .favorite-btn i {
            font-size: 1.5rem;
            color: var(--secondary);
            transition: var(--transition);
        }

        .favorite-btn:hover i {
            color: var(--secondary);
        }

        .favorite-btn.active:hover i {
            color: white;
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
            font-size: 1.0rem;
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

            .favorite-btn {
                width: 45px;
                height: 45px;
                top: 15px;
                right: 15px;
            }

            .favorite-btn i {
                font-size: 1.3rem;
            }

            .toast-container {
                top: 80px;
                right: 10px;
                left: 10px;
                max-width: none;
            }

            .toast {
                max-width: 100%;
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

            .favorite-btn {
                width: 40px;
                height: 40px;
                top: 10px;
                right: 10px;
            }

            .favorite-btn i {
                font-size: 1.2rem;
            }

            /* Reposition favorite button on mobile */
            .book-cover-container {
                display: flex;
                justify-content: center;
            }

            .book-cover-container .favorite-btn {
                position: relative;
                top: 0;
                right: 0;
                margin-top: 15px;
                align-self: center;
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

        /* Heart beat animation */
        @keyframes heartBeat {
            0% {
                transform: scale(1);
            }

            25% {
                transform: scale(1.3);
            }

            50% {
                transform: scale(1);
            }

            75% {
                transform: scale(1.3);
            }

            100% {
                transform: scale(1);
            }
        }

        .heart-beat {
            animation: heartBeat 0.8s ease-in-out;
        }
    </style>
</head>

<body>
    <?php include 'views/navbar_index.php'; ?>

    <!-- Toast Notification Container -->
    <div class="toast-container" id="toastContainer"></div>

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

                    <!-- Tombol Favorit -->
                    <form method="POST" class="favorite-form">
                        <input type="hidden" name="toggle_favorite" value="1">
                        <button type="submit" class="favorite-btn <?= $isFavorited ? 'active' : '' ?>"
                            id="favoriteButton">
                            <i class="<?= $isFavorited ? 'fas' : 'far' ?> fa-heart"></i>
                        </button>
                    </form>
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
                        <i class="fas fa-plus" style="margin-right: 8px;"></i> Tambah Ulasan
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

                <button class="btn btn-primary" id="addReviewBtn" style="margin-top: 1rem;">
                    <i class="fas fa-plus" style="margin-right: 8px;"></i> Tambah Ulasan Anda
                </button>
            <?php endif; ?>
        </div>
    </div>

    <?php include 'views/footer_index.php'; ?>

    <!-- SweetAlert2 for beautiful alerts -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        // Toast notification system
        function showToast(type, message) {
            const toastContainer = document.getElementById('toastContainer');
            const toast = document.createElement('div');
            toast.className = `toast toast-${type}`;

            const icons = {
                success: 'fa-check-circle',
                error: 'fa-exclamation-circle'
            };

            const titles = {
                success: 'Sukses',
                error: 'Error'
            };

            toast.innerHTML = `
                <i class="toast-icon fas ${icons[type]}"></i>
                <div class="toast-content">
                    <div class="toast-title">${titles[type]}</div>
                    <div class="toast-message">${message}</div>
                </div>
                <button class="toast-close" onclick="this.parentElement.remove()">
                    <i class="fas fa-times"></i>
                </button>
            `;

            toastContainer.appendChild(toast);

            // Show toast with animation
            setTimeout(() => {
                toast.classList.add('show');
            }, 10);

            // Auto remove after 5 seconds
            setTimeout(() => {
                toast.classList.remove('show');
                toast.classList.add('hide');
                setTimeout(() => {
                    if (toast.parentNode) {
                        toast.parentNode.removeChild(toast);
                    }
                }, 300);
            }, 5000);
        }

        // Check for toast message from PHP session
        <?php if (isset($_SESSION['toast'])): ?>
            document.addEventListener('DOMContentLoaded', function() {
                showToast('<?= $_SESSION['toast']['type'] ?>', '<?= addslashes($_SESSION['toast']['message']) ?>');
            });
            <?php unset($_SESSION['toast']); ?>
        <?php endif; ?>

        // Handle "Baca Sekarang" button click
        document.querySelectorAll('.btn-detail').forEach(btn => {
            if (btn.getAttribute('data-status')) {
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
            }
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

        // Favorite button functionality
        const favoriteButton = document.getElementById('favoriteButton');
        if (favoriteButton) {
            favoriteButton.addEventListener('click', function(e) {
                const isLoggedIn = <?= isset($_SESSION['user_id']) ? 'true' : 'false' ?>;

                if (!isLoggedIn) {
                    e.preventDefault();
                    Swal.fire({
                        title: 'Login Diperlukan',
                        text: 'Anda harus login terlebih dahulu untuk menambahkan ke favorit.',
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
                    // Add heart beat animation
                    const heartIcon = this.querySelector('i');
                    heartIcon.classList.add('heart-beat');

                    // Remove animation class after animation completes
                    setTimeout(() => {
                        heartIcon.classList.remove('heart-beat');
                    }, 800);
                }
            });
        }

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
                                    showToast('success', 'Ulasan Anda telah berhasil dikirim.');
                                    setTimeout(() => {
                                        location.reload();
                                    }, 2000);
                                } else {
                                    showToast('error', data.message || 'Gagal mengirim ulasan');
                                }
                            })
                            .catch(error => {
                                showToast('error', 'Terjadi kesalahan saat mengirim ulasan');
                            });
                    }
                });

                // Star rating interaction
                setTimeout(() => {
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
                }, 100);
            }
        });

        // Reposition favorite button on mobile
        function handleMobileLayout() {
            const favoriteBtn = document.getElementById('favoriteButton');
            const bookCoverContainer = document.querySelector('.book-cover-container');

            if (window.innerWidth <= 576) {
                // Mobile view
                if (favoriteBtn && bookCoverContainer) {
                    favoriteBtn.style.position = 'relative';
                    favoriteBtn.style.top = '0';
                    favoriteBtn.style.right = '0';
                    favoriteBtn.style.marginTop = '15px';
                    favoriteBtn.style.alignSelf = 'center';

                    // Create a container for the button if it doesn't exist
                    if (!document.querySelector('.favorite-btn-container')) {
                        const container = document.createElement('div');
                        container.className = 'favorite-btn-container';
                        container.style.display = 'flex';
                        container.style.justifyContent = 'center';
                        container.style.width = '100%';
                        container.appendChild(favoriteBtn);
                        bookCoverContainer.appendChild(container);
                    }
                }
            } else {
                // Desktop view
                if (favoriteBtn && bookCoverContainer) {
                    favoriteBtn.style.position = 'absolute';
                    favoriteBtn.style.top = '20px';
                    favoriteBtn.style.right = '20px';
                    favoriteBtn.style.marginTop = '0';

                    // Remove the container if it exists
                    const container = document.querySelector('.favorite-btn-container');
                    if (container) {
                        container.remove();
                        bookCoverContainer.appendChild(favoriteBtn);
                    }
                }
            }
        }

        // Initial call and resize listener
        handleMobileLayout();
        window.addEventListener('resize', handleMobileLayout);
    </script>
</body>

</html>