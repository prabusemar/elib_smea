<?php
session_start();
require_once '../../config.php';

// Validasi session admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

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
$queryRating = "SELECT COALESCE(AVG(Rating), 0.0) as avg_rating FROM ulasan WHERE BukuID = $bukuID";
$resultRating = mysqli_query($conn, $queryRating);
$ratingData = mysqli_fetch_assoc($resultRating);
$avgRating = number_format($ratingData['avg_rating'], 1);

// Get admin data
$adminId = $_SESSION['user_id'];
$sql = "SELECT u.*, a.Bio, a.NoTelepon, a.AdminID
        FROM users u
        JOIN admin a ON u.admin_id = a.AdminID
        WHERE u.id = ? AND u.role = 'admin'";
$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {
    die("Error prepare statement: " . mysqli_error($conn));
}

mysqli_stmt_bind_param($stmt, "i", $adminId);
if (!mysqli_stmt_execute($stmt)) {
    die("Error execute statement: " . mysqli_stmt_error($stmt));
}

$result = mysqli_stmt_get_result($stmt);
$admin = mysqli_fetch_assoc($result);

if (!$admin) {
    die("Error: Data admin tidak ditemukan di database untuk ID: " . htmlspecialchars($adminId));
}

$page_title = "Detail Buku - Admin Perpustakaan Digital";
include '../../views/header.php';
?>

<div class="admin-book-detail-container">
    <div class="page-header">
        <div>
            <h1 class="page-title">Detail Buku</h1>
            <p class="page-subtitle">Kelola informasi dan data buku</p>
        </div>
        <div class="header-actions">
            <a href="edit.php?id=<?= $bukuID ?>" class="btn btn-primary">
                <i class="fas fa-edit"></i> Edit Buku
            </a>
            <a href="<?= isset($_SERVER['HTTP_REFERER']) ? htmlspecialchars($_SERVER['HTTP_REFERER']) : 'semua_buku.php' ?>" class="btn btn-outline">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>
    </div>

    <div class="book-detail-content">
        <div class="book-detail-grid">
            <div class="book-cover-container">
                <img src="<?= '../../' . htmlspecialchars($buku['Cover']) ?>"
                    alt="Cover Buku <?= htmlspecialchars($buku['Judul']) ?>"
                    class="book-cover-large"
                    onerror="this.src='<?= BASE_URL ?>/assets/icon/default-book.png'">
            </div>
            <div class="book-info">
                <span class="book-category"><?= htmlspecialchars($buku['NamaKategori'] ?? 'Umum') ?></span>
                <h1 class="book-title"><?= htmlspecialchars($buku['Judul']) ?></h1>
                <p class="book-author"><?= htmlspecialchars($buku['Penulis']) ?></p>

                <div class="book-meta">
                    <div class="book-rating">
                        <i class="fas fa-star"></i> <?= number_format($avgRating, 1) ?> (<?= $totalUlasan ?> ulasan)
                    </div>
                    <span class="book-status status-<?= strtolower($buku['Status']) ?>">
                        <i class="fas fa-<?= $buku['Status'] === 'Premium' ? 'crown' : 'lock-open' ?>"></i>
                        <?= $buku['Status'] ?>
                    </span>
                    <span class="book-id">
                        <i class="fas fa-hashtag"></i> ID: <?= $bukuID ?>
                    </span>
                </div>

                <div class="book-description">
                    <h3>Deskripsi Buku</h3>
                    <p><?= nl2br(htmlspecialchars($buku['Deskripsi'] ?? 'Tidak ada deskripsi tersedia')) ?></p>
                </div>

                <div class="book-details-grid">
                    <div class="detail-item">
                        <span class="detail-label">Penerbit</span>
                        <span class="detail-value"><?= htmlspecialchars($buku['Penerbit'] ?? '-') ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Tahun Terbit</span>
                        <span class="detail-value"><?= htmlspecialchars($buku['TahunTerbit'] ?? '-') ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">ISBN</span>
                        <span class="detail-value"><?= htmlspecialchars($buku['ISBN'] ?? '-') ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Jumlah Halaman</span>
                        <span class="detail-value"><?= htmlspecialchars($buku['JumlahHalaman'] ?? '-') ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Bahasa</span>
                        <span class="detail-value"><?= htmlspecialchars($buku['Bahasa'] ?? '-') ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Tanggal Ditambahkan</span>
                        <span class="detail-value"><?= date('d F Y', strtotime($buku['CreatedAt'])) ?></span>
                    </div>
                </div>

                <div class="book-actions">
                    <a href="<?= $buku['DriveURL'] ?>" target="_blank" class="btn btn-primary">
                        <i class="fas fa-external-link-alt"></i> Lihat di Google Drive
                    </a>
                    <button class="btn btn-outline" id="copyUrlBtn">
                        <i class="fas fa-link"></i> Salin URL
                    </button>
                    <a href="../laporan/buku.php?id=<?= $bukuID ?>" class="btn btn-outline">
                        <i class="fas fa-chart-bar"></i> Lihat Laporan
                    </a>
                </div>
            </div>
        </div>

        <div class="book-stats-section">
            <h2 class="section-title"><i class="fas fa-chart-line"></i> Statistik Buku</h2>
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-eye"></i>
                    </div>
                    <div class="stat-content">
                        <h3>1,245</h3>
                        <p>Total Dilihat</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-book-open"></i>
                    </div>
                    <div class="stat-content">
                        <h3>568</h3>
                        <p>Total Dibaca</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-heart"></i>
                    </div>
                    <div class="stat-content">
                        <h3>189</h3>
                        <p>Dalam Favorit</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-bookmark"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?= $totalUlasan ?></h3>
                        <p>Ulasan</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="reviews-section">
            <div class="section-header">
                <h2 class="section-title"><i class="fas fa-comments"></i> Ulasan Pembaca</h2>
                <span class="section-badge"><?= $totalUlasan ?> ulasan</span>
            </div>

            <?php if ($totalUlasan === 0): ?>
                <div class="empty-state">
                    <div class="empty-icon">
                        <i class="fas fa-comment-slash"></i>
                    </div>
                    <h3>Belum ada ulasan</h3>
                    <p>Buku ini belum menerima ulasan dari pembaca.</p>
                </div>
            <?php else: ?>
                <div class="reviews-container">
                    <?php while ($review = mysqli_fetch_assoc($ulasan)): ?>
                        <div class="review-card">
                            <div class="review-header">
                                <img src="<?php
                                            $fotoProfil = !empty($review['FotoProfil']) ? $review['FotoProfil'] : '../../assets/profiles/default.jpg';
                                            $fotoPath = ($fotoProfil === '../../assets/profiles/default.jpg') ? BASE_URL . '/assets/profiles/default.jpg' : '../../' . htmlspecialchars($fotoProfil);
                                            echo $fotoPath;
                                            ?>"
                                    alt="Foto Profil <?= htmlspecialchars($review['NamaAnggota']) ?>"
                                    class="review-avatar"
                                    onerror="this.src='<?= BASE_URL ?>/assets/profiles/default.jpg'">
                                <div>
                                    <div class="review-user"><?= htmlspecialchars($review['NamaAnggota']) ?></div>
                                    <div class="review-date"><?= date('d F Y', strtotime($review['TanggalUlas'])) ?></div>
                                </div>
                                <div class="review-rating">
                                    <?php
                                    $rating = floatval($review['Rating']);
                                    for ($i = 1; $i <= 5; $i++) {
                                        if ($rating >= $i) {
                                            echo '<i class="fas fa-star"></i>';
                                        } elseif ($rating >= ($i - 0.5)) {
                                            echo '<i class="fas fa-star-half-alt"></i>';
                                        } else {
                                            echo '<i class="far fa-star"></i>';
                                        }
                                    }
                                    ?>
                                    <span><?= $review['Rating'] ?></span>
                                </div>
                            </div>
                            <div class="review-content">
                                <?= nl2br(htmlspecialchars($review['Komentar'])) ?>
                            </div>
                            <div class="review-actions">
                                <button class="action-btn" onclick="replyToReview(<?= $review['UlasanID'] ?>)">
                                    <i class="fas fa-reply"></i> Balas
                                </button>
                                <button class="action-btn text-danger" onclick="deleteReview(<?= $review['UlasanID'] ?>)">
                                    <i class="fas fa-trash"></i> Hapus
                                </button>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
    :root {
        --primary: #3a0ca3;
        --primary-light: #4361ee;
        --primary-lighter: #f0f2ff;
        --secondary: #f72585;
        --light: #f8f9fa;
        --dark: #212529;
        --gray: #6c757d;
        --gray-light: #e9ecef;
        --gray-lighter: #f8f9fa;
        --success: #28a745;
        --warning: #ffc107;
        --danger: #dc3545;
        --border-radius: 8px;
        --box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
        --box-shadow-lg: 0 10px 30px rgba(0, 0, 0, 0.12);
        --transition: all 0.3s ease;
    }

    .admin-book-detail-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 2rem;
    }

    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 2rem;
        padding-bottom: 1rem;
        border-bottom: 1px solid var(--gray-light);
    }

    .page-title {
        font-size: 2rem;
        color: var(--primary);
        margin-bottom: 0.5rem;
    }

    .page-subtitle {
        color: var(--gray);
    }

    .header-actions {
        display: flex;
        gap: 1rem;
    }

    .btn {
        display: inline-flex;
        align-items: center;
        padding: 0.75rem 1.5rem;
        border-radius: var(--border-radius);
        font-weight: 500;
        transition: var(--transition);
        text-decoration: none;
        border: none;
        cursor: pointer;
        gap: 0.5rem;
    }

    .btn-primary {
        background: var(--primary);
        color: white;
    }

    .btn-primary:hover {
        background: var(--primary-light);
        transform: translateY(-2px);
    }

    .btn-outline {
        background: transparent;
        color: var(--primary);
        border: 1px solid var(--primary);
    }

    .btn-outline:hover {
        background: var(--primary-lighter);
        transform: translateY(-2px);
    }

    .book-detail-content {
        background: white;
        border-radius: var(--border-radius);
        box-shadow: var(--box-shadow);
        overflow: hidden;
    }

    .book-detail-grid {
        display: grid;
        grid-template-columns: 1fr 2fr;
        gap: 2rem;
        padding: 2rem;
    }

    .book-cover-container {
        position: relative;
    }

    .book-cover-large {
        width: 100%;
        height: 400px;
        object-fit: cover;
        border-radius: var(--border-radius);
        box-shadow: var(--box-shadow-lg);
    }

    .book-info {
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
    }

    .book-category {
        background: var(--primary-lighter);
        color: var(--primary);
        padding: 0.5rem 1rem;
        border-radius: 50px;
        font-size: 0.9rem;
        font-weight: 600;
        display: inline-block;
        width: fit-content;
    }

    .book-title {
        font-size: 2.2rem;
        color: var(--dark);
        line-height: 1.2;
        margin: 0;
    }

    .book-author {
        font-size: 1.2rem;
        color: var(--gray);
        margin: 0;
    }

    .book-meta {
        display: flex;
        gap: 1rem;
        flex-wrap: wrap;
        align-items: center;
    }

    .book-rating {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        background: var(--warning-light);
        color: var(--dark);
        padding: 0.5rem 1rem;
        border-radius: 50px;
        font-weight: 500;
    }

    .book-rating i {
        color: var(--warning);
    }

    .book-status {
        padding: 0.5rem 1rem;
        border-radius: 50px;
        font-size: 0.9rem;
        font-weight: 600;
    }

    .status-premium {
        background: var(--warning-light);
        color: var(--warning-dark);
    }

    .status-free {
        background: var(--success-light);
        color: var(--success);
    }

    .book-id {
        background: var(--gray-lighter);
        color: var(--gray);
        padding: 0.5rem 1rem;
        border-radius: 50px;
        font-size: 0.9rem;
    }

    .book-description h3 {
        font-size: 1.2rem;
        color: var(--dark);
        margin-bottom: 1rem;
        padding-bottom: 0.5rem;
        border-bottom: 2px solid var(--primary-lighter);
    }

    .book-description p {
        line-height: 1.6;
        color: var(--gray);
    }

    .book-details-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 1rem;
    }

    .detail-item {
        display: flex;
        flex-direction: column;
        padding: 1rem;
        background: var(--gray-lighter);
        border-radius: var(--border-radius);
    }

    .detail-label {
        font-size: 0.9rem;
        color: var(--gray);
        margin-bottom: 0.5rem;
    }

    .detail-value {
        font-weight: 500;
        color: var(--dark);
    }

    .book-actions {
        display: flex;
        gap: 1rem;
        flex-wrap: wrap;
    }

    .book-stats-section {
        padding: 2rem;
        border-top: 1px solid var(--gray-light);
        border-bottom: 1px solid var(--gray-light);
        background: var(--gray-lighter);
    }

    .section-title {
        font-size: 1.5rem;
        color: var(--dark);
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 1.5rem;
    }

    .stat-card {
        background: white;
        padding: 1.5rem;
        border-radius: var(--border-radius);
        box-shadow: var(--box-shadow);
        display: flex;
        align-items: center;
        gap: 1rem;
        transition: var(--transition);
    }

    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: var(--box-shadow-lg);
    }

    .stat-icon {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        background: var(--primary-lighter);
        color: var(--primary);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
    }

    .stat-content h3 {
        font-size: 1.8rem;
        color: var(--primary);
        margin: 0;
    }

    .stat-content p {
        color: var(--gray);
        margin: 0;
    }

    .reviews-section {
        padding: 2rem;
    }

    .section-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 2rem;
    }

    .section-badge {
        background: var(--primary);
        color: white;
        padding: 0.5rem 1rem;
        border-radius: 50px;
        font-weight: 500;
    }

    .reviews-container {
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
    }

    .review-card {
        background: white;
        border-radius: var(--border-radius);
        box-shadow: var(--box-shadow);
        padding: 1.5rem;
        transition: var(--transition);
        border-left: 4px solid var(--primary);
    }

    .review-card:hover {
        box-shadow: var(--box-shadow-lg);
    }

    .review-header {
        display: flex;
        align-items: center;
        margin-bottom: 1rem;
        gap: 1rem;
    }

    .review-avatar {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        object-fit: cover;
    }

    .review-user {
        font-weight: 600;
        color: var(--dark);
    }

    .review-date {
        font-size: 0.9rem;
        color: var(--gray);
    }

    .review-rating {
        margin-left: auto;
        color: var(--warning);
        font-weight: 500;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .review-content {
        line-height: 1.6;
        color: var(--dark);
        margin-bottom: 1rem;
    }

    .review-actions {
        display: flex;
        gap: 1rem;
    }

    .action-btn {
        background: none;
        border: none;
        color: var(--gray);
        cursor: pointer;
        font-size: 0.9rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem;
        border-radius: var(--border-radius);
        transition: var(--transition);
    }

    .action-btn:hover {
        background: var(--gray-lighter);
    }

    .text-danger {
        color: var(--danger);
    }

    .text-danger:hover {
        color: white;
        background: var(--danger);
    }

    .empty-state {
        text-align: center;
        padding: 3rem;
        background: white;
        border-radius: var(--border-radius);
        box-shadow: var(--box-shadow);
    }

    .empty-icon {
        font-size: 3rem;
        color: var(--gray-light);
        margin-bottom: 1rem;
    }

    .empty-state h3 {
        color: var(--dark);
        margin-bottom: 0.5rem;
    }

    .empty-state p {
        color: var(--gray);
    }

    /* Responsive styles */
    @media (max-width: 992px) {
        .book-detail-grid {
            grid-template-columns: 1fr;
        }

        .stats-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 768px) {
        .admin-book-detail-container {
            padding: 1rem;
        }

        .page-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 1rem;
        }

        .header-actions {
            width: 100%;
            justify-content: flex-end;
        }

        .book-details-grid {
            grid-template-columns: 1fr;
        }

        .book-actions {
            flex-direction: column;
        }

        .stats-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 576px) {
        .book-meta {
            flex-direction: column;
            align-items: flex-start;
        }

        .review-header {
            flex-direction: column;
            align-items: flex-start;
        }

        .review-rating {
            margin-left: 0;
        }
    }

    .fa-eye,
    .fa-book-open,
    .fa-heart,
    .fa-bookmark {
        margin: 0 auto;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Copy URL functionality
        const copyUrlBtn = document.getElementById('copyUrlBtn');
        if (copyUrlBtn) {
            copyUrlBtn.addEventListener('click', function() {
                const url = window.location.href;
                navigator.clipboard.writeText(url).then(() => {
                    // Show success message
                    const originalText = this.innerHTML;
                    this.innerHTML = '<i class="fas fa-check"></i> URL Disalin!';

                    setTimeout(() => {
                        this.innerHTML = originalText;
                    }, 2000);
                }).catch(err => {
                    console.error('Failed to copy: ', err);
                });
            });
        }
    });

    function replyToReview(reviewId) {
        alert('Fitur balas ulasan dengan ID ' + reviewId + ' akan segera tersedia.');
        // Implementasi fungsi balas ulasan di sini
    }

    function deleteReview(reviewId) {
        if (confirm('Apakah Anda yakin ingin menghapus ulasan ini?')) {
            // Implementasi penghapusan ulasan dengan AJAX atau form submission
            alert('Ulasan dengan ID ' + reviewId + ' akan dihapus.');
            // window.location.href = 'delete_review.php?id=' + reviewId;
        }
    }
</script>

<?php include '../../views/footer.php'; ?>