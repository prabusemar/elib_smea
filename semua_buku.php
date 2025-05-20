<?php
include 'config.php';

// Ambil parameter filter
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$kategori = isset($_GET['kategori']) ? (int)$_GET['kategori'] : 0;
$status = isset($_GET['status']) ? mysqli_real_escape_string($conn, $_GET['status']) : '';

// Query untuk mendapatkan buku
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

// Ambil kategori untuk dropdown
$kategories = mysqli_query($conn, "SELECT * FROM kategori ORDER BY NamaKategori");
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Semua Buku - SMEA E-Lib</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <?php include 'config.php'; ?>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/styles/style.css">
    <style>
        /* Custom Styles */
        .book-grid-header {
            background: linear-gradient(135deg, #3a0ca3 0%, #4361ee 100%);
            padding: 4rem 5%;
            margin-bottom: 3rem;
        }

        .filter-container {
            max-width: 1200px;
            margin: 0 auto;
            background: rgba(255, 255, 255, 0.95);
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        }

        .filter-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1rem;
        }

        .filter-group {
            position: relative;
        }

        .filter-input {
            width: 100%;
            padding: 0.8rem 1.5rem;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .filter-input:focus {
            border-color: #3a0ca3;
            box-shadow: 0 0 0 3px rgba(58, 12, 163, 0.1);
        }

        .books-grid {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 5% 4rem;
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 2rem;
        }

        .book-card {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            position: relative;
            display: flex;
            flex-direction: column;
        }

        .book-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
        }

        .book-cover {
            width: 100%;
            height: 380px;
            object-fit: cover;
            border-bottom: 3px solid #3a0ca3;
        }

        .book-details {
            padding: 1.5rem;
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .book-category {
            color: #3a0ca3;
            font-size: 0.85rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .book-title {
            font-size: 1.1rem;
            margin-bottom: 0.5rem;
            line-height: 1.3;
            flex: 1;
        }

        .book-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 1rem;
        }

        .book-rating {
            color: #f39c12;
            font-weight: 600;
        }

        @media (max-width: 768px) {
            .filter-container {
                padding: 1.5rem;
            }

            .book-cover {
                height: 300px;
            }
        }
    </style>
</head>

<body>
    <?php include 'views/navbar_index.php'; ?>

    <div class="book-grid-header">
        <div class="filter-container">
            <h1 style="color: #3a0ca3; margin-bottom: 1.5rem;">Jelajahi Koleksi Buku</h1>
            <form method="GET" class="filter-grid">
                <div class="filter-group">
                    <input type="text" name="search" class="filter-input" placeholder="Cari judul atau penulis..."
                        value="<?= htmlspecialchars($search) ?>">
                </div>
                <div class="filter-group">
                    <select name="kategori" class="filter-input">
                        <option value="">Semua Kategori</option>
                        <?php while ($k = mysqli_fetch_assoc($kategories)): ?>
                            <option value="<?= $k['KategoriID'] ?>" <?= $kategori == $k['KategoriID'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($k['NamaKategori']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="filter-group">
                    <select name="status" class="filter-input">
                        <option value="">Semua Status</option>
                        <option value="Free" <?= $status == 'Free' ? 'selected' : '' ?>>Gratis</option>
                        <option value="Premium" <?= $status == 'Premium' ? 'selected' : '' ?>>Premium</option>
                    </select>
                </div>
                <div class="filter-group" style="grid-column: span 2; display: flex; align-items: center; gap: 1rem;">
                    <button type="submit" class="btn btn-primary" style="padding: 0.8rem 1.5rem; display: flex; align-items: center; gap: 0.5rem;">
                        <i class="fas fa-search"></i> Cari
                    </button>
                </div>
            </form>
        </div>
    </div>

    <?php if (mysqli_num_rows($books) === 0): ?>
        <div style="text-align:center; padding:3rem 0; color:#888;">
            <i class="fas fa-book-open" style="font-size:3rem; color:#3a0ca3; margin-bottom:1rem;"></i>
            <h2>Tidak ada buku ditemukan</h2>
            <p>Coba gunakan kata kunci atau filter lain.</p>
        </div>
    <?php else: ?>
        <div class="books-grid">
            <?php while ($book = mysqli_fetch_assoc($books)): ?>
                <div class="book-card">
                    <img src="<?= BASE_URL . '/' . htmlspecialchars($book['Cover']) ?>"
                        alt="Cover Buku"
                        class="book-cover"
                        onerror="this.src='<?= BASE_URL ?>/assets/icon/default-book.png'">

                    <div class="book-details">
                        <span class="book-category">
                            <?= htmlspecialchars($book['NamaKategori'] ?? 'Umum') ?>
                        </span>
                        <h3 class="book-title"><?= htmlspecialchars($book['Judul']) ?></h3>
                        <p class="author"><?= htmlspecialchars($book['Penulis']) ?></p>

                        <div class="book-meta">
                            <div class="book-rating">
                                <i class="fas fa-star"></i> <?= number_format($book['Rating'], 1) ?>
                            </div>
                            <span class="book-badge <?= $book['Status'] === 'Premium' ? 'premium' : '' ?>">
                                <?= $book['Status'] ?>
                            </span>
                        </div>

                        <div class="book-actions" style="margin-top: 1.5rem;">
                            <a href="#" class="btn btn-outline" style="flex: 1;">
                                <i class="fas fa-info-circle"></i> Detail
                            </a>
                            <a href="<?= $book['Status'] === 'Premium' ? '#' : $book['DriveURL'] ?>"
                                class="btn btn-primary" style="flex: 1;"
                                <?= $book['Status'] === 'Premium' ? 'data-premium="true"' : '' ?>>
                                <i class="fas fa-book-reader"></i> Baca
                            </a>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php endif; ?>

    <?php include 'views/footer_index.php'; ?>

    <script>
        // Handle premium book click
        document.querySelectorAll('[data-premium="true"]').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                alert('Buku premium hanya tersedia untuk anggota berlangganan. Silakan upgrade akun Anda!');
            });
        });
    </script>
</body>

</html>