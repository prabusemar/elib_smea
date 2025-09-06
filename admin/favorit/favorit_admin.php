<?php
session_start();
require_once '../../config.php';

// Aktifkan error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Validasi session admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

// Fungsi untuk redirect yang aman
function safe_redirect($url)
{
    if (headers_sent()) {
        die("Redirect failed. Please <a href='$url'>click here</a> to continue.");
    }
    header("Location: $url");
    exit;
}

// Get admin data
$adminId = $_SESSION['user_id'];
$sql = "SELECT u.*, a.Bio, a.NoTelepon 
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

// Ambil data buku favorit admin
$favorites = [];
$hasFavorites = false;

$sqlFavorites = "SELECT f.*, b.Judul, b.Penulis, b.Cover, b.Rating, b.Status, k.NamaKategori 
                 FROM favorit f 
                 JOIN buku b ON f.BukuID = b.BukuID 
                 LEFT JOIN kategori k ON b.KategoriID = k.KategoriID 
                 WHERE f.MemberID = ? 
                 ORDER BY f.TanggalDitambahkan DESC";
$stmtFavorites = mysqli_prepare($conn, $sqlFavorites);

if ($stmtFavorites) {
    mysqli_stmt_bind_param($stmtFavorites, "i", $adminId);
    if (mysqli_stmt_execute($stmtFavorites)) {
        $resultFavorites = mysqli_stmt_get_result($stmtFavorites);
        $favorites = mysqli_fetch_all($resultFavorites, MYSQLI_ASSOC);
        $hasFavorites = count($favorites) > 0;
    }
}

// Handle remove from favorites
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['remove_favorite'])) {
    $bukuID = intval($_POST['buku_id']);

    $sqlDelete = "DELETE FROM favorit WHERE MemberID = ? AND BukuID = ?";
    $stmtDelete = mysqli_prepare($conn, $sqlDelete);

    if ($stmtDelete) {
        mysqli_stmt_bind_param($stmtDelete, "ii", $adminId, $bukuID);
        if (mysqli_stmt_execute($stmtDelete)) {
            $_SESSION['success'] = "Buku berhasil dihapus dari favorit";
            safe_redirect("admin_favorites.php");
        } else {
            $_SESSION['error'] = "Gagal menghapus dari favorit: " . mysqli_error($conn);
        }
    } else {
        $_SESSION['error'] = "Terjadi kesalahan sistem";
    }
}

$page_title = "Buku Favorit - Perpustakaan Digital";
include '../../views/header.php';
?>

<div class="favorites-container">
    <div class="page-header">
        <div>
            <h1 class="page-title">Buku Favorit</h1>
            <p class="page-subtitle">Koleksi buku yang telah Anda tandai sebagai favorit</p>
        </div>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success">
            <?= $_SESSION['success'] ?>
            <button type="button" class="close-alert">&times;</button>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-error">
            <?= is_array($_SESSION['error']) ? implode('<br>', $_SESSION['error']) : $_SESSION['error'] ?>
            <button type="button" class="close-alert">&times;</button>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <div class="filter-controls">
        <select class="filter-select" id="categoryFilter">
            <option value="">Semua Kategori</option>
            <?php
            // Ambil kategori dari database
            $sqlCategories = "SELECT * FROM kategori ORDER BY NamaKategori";
            $resultCategories = mysqli_query($conn, $sqlCategories);
            if ($resultCategories && mysqli_num_rows($resultCategories) > 0) {
                while ($category = mysqli_fetch_assoc($resultCategories)) {
                    echo "<option value=\"" . htmlspecialchars($category['NamaKategori']) . "\">" .
                        htmlspecialchars($category['NamaKategori']) . "</option>";
                }
            }
            ?>
        </select>

        <select class="filter-select" id="sortFilter">
            <option value="newest">Urutkan Terbaru</option>
            <option value="oldest">Urutkan Terlama</option>
            <option value="rating">Rating Tertinggi</option>
            <option value="title">Judul A-Z</option>
        </select>

        <div class="search-box">
            <i class="fas fa-search search-icon"></i>
            <input type="text" class="search-input" id="searchInput" placeholder="Cari buku favorit...">
        </div>
    </div>

    <!-- State ketika ada buku favorit -->
    <div class="favorites-grid" id="favorites-grid">
        <?php if ($hasFavorites): ?>
            <?php foreach ($favorites as $favorite): ?>
                <div class="favorite-card" data-category="<?= htmlspecialchars($favorite['NamaKategori'] ?? 'Uncategorized') ?>"
                    data-rating="<?= $favorite['Rating'] ?>" data-title="<?= htmlspecialchars($favorite['Judul']) ?>">
                    <div class="book-cover">
                        <img src="../<?= htmlspecialchars($favorite['Cover'] ?? 'uploads/covers/default.jpg') ?>"
                            alt="<?= htmlspecialchars($favorite['Judul']) ?>"
                            onerror="this.src='https://via.placeholder.com/300x400/6c757d/ffffff?text=No+Cover'">
                        <span class="book-category"><?= htmlspecialchars($favorite['NamaKategori'] ?? 'Uncategorized') ?></span>
                        <form method="POST" class="favorite-form">
                            <input type="hidden" name="buku_id" value="<?= $favorite['BukuID'] ?>">
                            <button type="submit" name="remove_favorite" class="favorite-badge active"
                                onclick="return confirm('Hapus buku ini dari favorit?')">
                                <i class="fas fa-heart"></i>
                            </button>
                        </form>
                    </div>
                    <div class="book-info">
                        <h3 class="book-title"><?= htmlspecialchars($favorite['Judul']) ?></h3>
                        <p class="book-author"><?= htmlspecialchars($favorite['Penulis']) ?></p>
                        <div class="book-meta">
                            <div class="book-rating">
                                <i class="fas fa-star"></i>
                                <span><?= number_format($favorite['Rating'], 1) ?></span>
                            </div>
                            <span class="book-status status-<?= strtolower($favorite['Status']) ?>">
                                <?= $favorite['Status'] ?>
                            </span>
                        </div>
                        <div class="card-actions">
                            <a href="../buku/detail.php?id=<?= $favorite['BukuID'] ?>" class="action-btn btn-read">
                                Baca Buku
                            </a>
                            <a href="../buku/detail.php?id=<?= $favorite['BukuID'] ?>" class="action-btn btn-details">
                                Detail
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <!-- State ketika tidak ada buku favorit -->
            <div class="empty-state" id="empty-state">
                <div class="empty-icon">
                    <i class="fas fa-heart"></i>
                </div>
                <h3 class="empty-title">Belum Ada Buku Favorit</h3>
                <p class="empty-desc">Anda belum menambahkan buku apapun ke favorit. Jelajahi katalog buku kami dan temukan bacaan menarik untuk ditambahkan ke koleksi favorit Anda.</p>
                <a href="../buku/" class="explore-btn">
                    <i class="fas fa-book-open"></i> Jelajahi Buku
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
    /* Base Styles */
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
        --danger: #dc3545;
        --danger-light: #f8d7da;
        --success: #28a745;
        --success-light: #d4edda;
        --warning: #ffc107;
        --warning-light: #fff3cd;
        --border-radius: 8px;
        --box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
        --transition: all 0.3s ease;
    }

    * {
        box-sizing: border-box;
        margin: 0;
        padding: 0;
    }

    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        line-height: 1.6;
        color: var(--dark);
        background-color: var(--gray-lighter);
    }

    a {
        text-decoration: none;
        color: inherit;
    }

    .favorites-container {
        max-width: 1200px;
        margin: 0 auto;
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
    }

    .page-subtitle {
        color: var(--gray);
        margin-top: 0.5rem;
    }

    .filter-controls {
        display: flex;
        gap: 1rem;
        margin-bottom: 2rem;
    }

    .filter-select {
        padding: 0.75rem 1rem;
        border: 1px solid var(--gray-light);
        border-radius: var(--border-radius);
        background-color: white;
        font-size: 0.9rem;
        min-width: 180px;
    }

    .search-box {
        position: relative;
        flex: 1;
        max-width: 400px;
    }

    .search-input {
        width: 100%;
        padding: 0.75rem 1rem 0.75rem 3rem;
        border: 1px solid var(--gray-light);
        border-radius: var(--border-radius);
        font-size: 0.9rem;
    }

    .search-icon {
        position: absolute;
        left: 1rem;
        top: 50%;
        transform: translateY(-50%);
        color: var(--gray);
    }

    /* Favorites Grid */
    .favorites-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 2rem;
        margin-bottom: 2rem;
    }

    .favorite-card {
        background: white;
        border-radius: var(--border-radius);
        overflow: hidden;
        box-shadow: var(--box-shadow);
        transition: var(--transition);
        position: relative;
    }

    .favorite-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.12);
    }

    .book-cover {
        height: 200px;
        overflow: hidden;
        position: relative;
    }

    .book-cover img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: var(--transition);
    }

    .favorite-card:hover .book-cover img {
        transform: scale(1.05);
    }

    .book-category {
        position: absolute;
        top: 1rem;
        left: 1rem;
        background: var(--primary);
        color: white;
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 500;
    }

    .favorite-form {
        position: absolute;
        top: 1rem;
        right: 1rem;
    }

    .favorite-badge {
        background: rgba(255, 255, 255, 0.9);
        color: var(--secondary);
        width: 36px;
        height: 36px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        cursor: pointer;
        border: none;
        transition: var(--transition);
    }

    .favorite-badge.active {
        background: var(--secondary);
        color: white;
    }

    .favorite-badge:hover {
        transform: scale(1.1);
    }

    .book-info {
        padding: 1.5rem;
    }

    .book-title {
        font-size: 1.1rem;
        font-weight: 600;
        margin-bottom: 0.5rem;
        color: var(--dark);
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .book-author {
        color: var(--gray);
        font-size: 0.9rem;
        margin-bottom: 1rem;
    }

    .book-meta {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 1rem;
        padding-top: 1rem;
        border-top: 1px solid var(--gray-light);
    }

    .book-rating {
        display: flex;
        align-items: center;
        color: var(--warning);
        font-weight: 500;
    }

    .book-status {
        font-size: 0.8rem;
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
        font-weight: 500;
    }

    .status-free {
        background: var(--success-light);
        color: var(--success);
    }

    .status-premium {
        background: var(--primary-lighter);
        color: var(--primary);
    }

    .card-actions {
        display: flex;
        justify-content: space-between;
        margin-top: 1rem;
    }

    .action-btn {
        padding: 0.5rem 1rem;
        border-radius: var(--border-radius);
        font-size: 0.85rem;
        font-weight: 500;
        cursor: pointer;
        transition: var(--transition);
        text-decoration: none;
        text-align: center;
    }

    .btn-read {
        background: var(--primary);
        color: white;
        flex: 1;
        margin-right: 0.5rem;
    }

    .btn-read:hover {
        background: var(--primary-light);
    }

    .btn-details {
        background: var(--gray-lighter);
        color: var(--dark);
    }

    .btn-details:hover {
        background: var(--gray-light);
    }

    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 4rem 2rem;
        background: white;
        border-radius: var(--border-radius);
        box-shadow: var(--box-shadow);
        margin: 2rem 0;
        grid-column: 1 / -1;
    }

    .empty-icon {
        font-size: 4rem;
        color: var(--gray-light);
        margin-bottom: 1.5rem;
    }

    .empty-title {
        font-size: 1.5rem;
        color: var(--dark);
        margin-bottom: 1rem;
    }

    .empty-desc {
        color: var(--gray);
        max-width: 500px;
        margin: 0 auto 2rem;
    }

    .explore-btn {
        display: inline-flex;
        align-items: center;
        padding: 0.75rem 1.5rem;
        background: var(--primary);
        color: white;
        border-radius: var(--border-radius);
        font-weight: 500;
        transition: var(--transition);
        text-decoration: none;
    }

    .explore-btn:hover {
        background: var(--primary-light);
        transform: translateY(-2px);
    }

    .explore-btn i {
        margin-right: 0.5rem;
    }

    /* Alerts */
    .alert {
        position: relative;
        padding: 1rem;
        margin-bottom: 1.5rem;
        border-radius: var(--border-radius);
    }

    .alert-success {
        background-color: var(--success-light);
        color: var(--success);
        border: 1px solid rgba(40, 167, 69, 0.2);
    }

    .alert-error {
        background-color: var(--danger-light);
        color: var(--danger);
        border: 1px solid rgba(220, 53, 69, 0.2);
    }

    .close-alert {
        position: absolute;
        top: 0.5rem;
        right: 0.5rem;
        background: none;
        border: none;
        font-size: 1.25rem;
        cursor: pointer;
        color: inherit;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .filter-controls {
            flex-direction: column;
        }

        .search-box {
            max-width: 100%;
        }

        .favorites-grid {
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 1.5rem;
        }

        .page-header {
            flex-direction: column;
            align-items: flex-start;
        }

        .page-title {
            margin-bottom: 1rem;
        }
    }

    @media (max-width: 576px) {
        .favorites-grid {
            grid-template-columns: 1fr;
        }

        .filter-select {
            min-width: 100%;
        }

        body {
            padding: 10px;
        }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Filter functionality
        const categoryFilter = document.getElementById('categoryFilter');
        const sortFilter = document.getElementById('sortFilter');
        const searchInput = document.getElementById('searchInput');
        const favoriteCards = document.querySelectorAll('.favorite-card');

        function filterFavorites() {
            const categoryValue = categoryFilter.value.toLowerCase();
            const searchValue = searchInput.value.toLowerCase();
            const sortValue = sortFilter.value;

            let visibleCards = [];

            favoriteCards.forEach(card => {
                const category = card.getAttribute('data-category').toLowerCase();
                const title = card.getAttribute('data-title').toLowerCase();
                const rating = parseFloat(card.getAttribute('data-rating'));

                const categoryMatch = !categoryValue || category === categoryValue;
                const searchMatch = !searchValue || title.includes(searchValue);

                if (categoryMatch && searchMatch) {
                    card.style.display = 'block';
                    visibleCards.push({
                        card,
                        rating,
                        title
                    });
                } else {
                    card.style.display = 'none';
                }
            });

            // Sort functionality
            if (sortValue === 'newest') {
                // Default order (already sorted by newest in SQL)
            } else if (sortValue === 'oldest') {
                visibleCards.reverse();
            } else if (sortValue === 'rating') {
                visibleCards.sort((a, b) => b.rating - a.rating);
            } else if (sortValue === 'title') {
                visibleCards.sort((a, b) => a.title.localeCompare(b.title));
            }

            // Reorder cards in DOM
            const grid = document.getElementById('favorites-grid');
            visibleCards.forEach(({
                card
            }) => {
                grid.appendChild(card);
            });
        }

        categoryFilter.addEventListener('change', filterFavorites);
        sortFilter.addEventListener('change', filterFavorites);
        searchInput.addEventListener('input', filterFavorites);

        // Close alert buttons
        document.querySelectorAll('.close-alert').forEach(button => {
            button.addEventListener('click', function() {
                this.parentElement.style.display = 'none';
            });
        });
    });
</script>

<?php include '../../views/footer.php'; ?>