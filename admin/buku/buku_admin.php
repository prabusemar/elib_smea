<?php
session_start();
require_once '../../config.php';

// Pastikan hanya admin yang bisa mengakses
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../auth/login.php");
    exit;
}

// Fungsi untuk mendapatkan semua buku
// Fungsi untuk mendapatkan semua buku dengan pagination
function getAllBooks($conn, $filters = [], $limit = 10, $offset = 0)
{
    $query = "SELECT b.*, k.NamaKategori 
              FROM buku b
              LEFT JOIN kategori k ON b.KategoriID = k.KategoriID
              WHERE b.DeletedAt IS NULL";

    $conditions = [];
    $params = [];
    $types = '';

    // Filter Judul
    if (!empty($filters['judul'])) {
        $conditions[] = "b.Judul LIKE ?";
        $params[] = '%' . $filters['judul'] . '%';
        $types .= 's';
    }

    // Filter Penulis
    if (!empty($filters['penulis'])) {
        $conditions[] = "b.Penulis LIKE ?";
        $params[] = '%' . $filters['penulis'] . '%';
        $types .= 's';
    }

    // Filter Kategori
    if (!empty($filters['kategori'])) {
        $conditions[] = "b.KategoriID = ?";
        $params[] = $filters['kategori'];
        $types .= 'i';
    }

    // Filter Tahun
    if (!empty($filters['tahun'])) {
        $conditions[] = "b.TahunTerbit = ?";
        $params[] = $filters['tahun'];
        $types .= 'i';
    }

    // Filter Status
    if (!empty($filters['status'])) {
        $conditions[] = "b.Status = ?";
        $params[] = $filters['status'];
        $types .= 's';
    }

    if (!empty($conditions)) {
        $query .= " AND " . implode(" AND ", $conditions);
    }

    $query .= " ORDER BY b.Judul LIMIT ? OFFSET ?";
    $params[] = $limit;
    $params[] = $offset;
    $types .= 'ii';

    $stmt = mysqli_prepare($conn, $query);
    if (!$stmt) {
        die("Error preparing statement: " . mysqli_error($conn));
    }

    if (!empty($params)) {
        mysqli_stmt_bind_param($stmt, $types, ...$params);
    }

    if (!mysqli_stmt_execute($stmt)) {
        die("Error executing statement: " . mysqli_stmt_error($stmt));
    }

    $result = mysqli_stmt_get_result($stmt);
    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}

// Fungsi untuk mendapatkan total buku
function getTotalBooks($conn, $filters = [])
{
    $query = "SELECT COUNT(*) as total FROM buku b WHERE b.DeletedAt IS NULL";

    $conditions = [];
    $params = [];
    $types = '';

    // Filter Judul
    if (!empty($filters['judul'])) {
        $conditions[] = "b.Judul LIKE ?";
        $params[] = '%' . $filters['judul'] . '%';
        $types .= 's';
    }

    // Filter Penulis
    if (!empty($filters['penulis'])) {
        $conditions[] = "b.Penulis LIKE ?";
        $params[] = '%' . $filters['penulis'] . '%';
        $types .= 's';
    }

    // Filter Kategori
    if (!empty($filters['kategori'])) {
        $conditions[] = "b.KategoriID = ?";
        $params[] = $filters['kategori'];
        $types .= 'i';
    }

    // Filter Tahun
    if (!empty($filters['tahun'])) {
        $conditions[] = "b.TahunTerbit = ?";
        $params[] = $filters['tahun'];
        $types .= 'i';
    }

    // Filter Status
    if (!empty($filters['status'])) {
        $conditions[] = "b.Status = ?";
        $params[] = $filters['status'];
        $types .= 's';
    }

    if (!empty($conditions)) {
        $query .= " AND " . implode(" AND ", $conditions);
    }

    $stmt = mysqli_prepare($conn, $query);
    if (!$stmt) {
        die("Error preparing statement: " . mysqli_error($conn));
    }

    if (!empty($params)) {
        mysqli_stmt_bind_param($stmt, $types, ...$params);
    }

    if (!mysqli_stmt_execute($stmt)) {
        die("Error executing statement: " . mysqli_stmt_error($stmt));
    }

    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    return $row['total'];
}

// Ambil parameter filter
$filters = [
    'judul' => trim($_GET['judul'] ?? ''),
    'penulis' => trim($_GET['penulis'] ?? ''),
    'kategori' => trim($_GET['kategori'] ?? ''),
    'tahun' => trim($_GET['tahun'] ?? ''),
    'status' => trim($_GET['status'] ?? '')
];

// Set pagination parameters
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limit = 5;
$offset = ($page - 1) * $limit;
$totalBooks = getTotalBooks($conn, $filters);
$totalPages = ceil($totalBooks / $limit);

// Ambil data buku dengan filter dan pagination
$books = getAllBooks($conn, $filters, $limit, $offset);

// Ambil data kategori untuk dropdown
$kategori_query = "SELECT * FROM kategori ORDER BY NamaKategori";
$kategori_result = mysqli_query($conn, $kategori_query);
$kategories = mysqli_fetch_all($kategori_result, MYSQLI_ASSOC);

$page_title = "Manajemen Buku - Perpustakaan Digital";
include '../../views/header.php';
?>

<div class="content-wrapper">
    <div class="page-header">
        <h1>Manajemen Buku</h1>
        <div class="header-actions">
            <button class="btn btn-primary" id="addBookBtn">
                <i class="fas fa-plus"></i> Tambah Buku
            </button>
        </div>
    </div>

    <?php include '../../views/alert_messages.php'; ?>

    <!-- Di dalam card-body, setelah alert messages -->
    <form method="GET" action="" class="advanced-filter-form">
        <div class="filter-grid">
            <!-- Judul -->
            <div class="filter-group">
                <div class="input-icon">
                    <i class="fas fa-book"></i>
                    <input type="text" name="judul" class="form-control modern-input"
                        placeholder="Judul Buku"
                        value="<?= htmlspecialchars($_GET['judul'] ?? '') ?>">
                </div>
            </div>

            <!-- Penulis -->
            <div class="filter-group">
                <div class="input-icon">
                    <i class="fas fa-user-edit"></i>
                    <input type="text" name="penulis" class="form-control modern-input"
                        placeholder="Penulis"
                        value="<?= htmlspecialchars($_GET['penulis'] ?? '') ?>">
                </div>
            </div>

            <!-- Kategori -->
            <div class="filter-group">
                <div class="select-wrapper">
                    <i class="fas fa-tag"></i>
                    <select name="kategori" class="form-control modern-select">
                        <option value="">Semua Kategori</option>
                        <?php foreach ($kategories as $kategori): ?>
                            <option value="<?= $kategori['KategoriID'] ?>"
                                <?= ($_GET['kategori'] ?? '') == $kategori['KategoriID'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($kategori['NamaKategori']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <!-- Tahun -->
            <div class="filter-group">
                <div class="input-icon">
                    <i class="fas fa-calendar-alt"></i>
                    <input type="number" name="tahun" class="form-control modern-input"
                        placeholder="Tahun"
                        value="<?= htmlspecialchars($_GET['tahun'] ?? '') ?>">
                </div>
            </div>

            <!-- Status -->
            <div class="filter-group">
                <div class="select-wrapper">
                    <i class="fas fa-info-circle"></i>
                    <select name="status" class="form-control modern-select">
                        <option value="">Status</option>
                        <option value="Free" <?= ($_GET['status'] ?? '') == 'Free' ? 'selected' : '' ?>>Free</option>
                        <option value="Premium" <?= ($_GET['status'] ?? '') == 'Premium' ? 'selected' : '' ?>>Premium</option>
                    </select>
                </div>
            </div>

            <!-- Tombol Aksi -->
            <div class="filter-actions">
                <button type="submit" class="btn btn-filter">
                    <i class="fas fa-filter"></i> Filter
                </button>
                <a href="buku_admin.php" class="btn btn-reset">
                    <i class="fas fa-sync-alt"></i> Reset
                </a>
            </div>
        </div>
    </form>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table" id="booksTable">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Cover</th>
                            <th>Judul</th>
                            <th>Penulis</th>
                            <th>Penerbit</th>
                            <th>Tahun</th>
                            <th>Kategori</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($books)): ?>
                            <?php foreach ($books as $index => $book): ?>
                                <tr>
                                    <td data-label="No"><?= $index + 1 ?></td>
                                    <td data-label="Cover">
                                        <?php if (!empty($book['Cover'])): ?>
                                            <img src="<?= '../../' . htmlspecialchars($book['Cover']) ?>"
                                                alt="Cover Buku" class="book-cover"
                                                onerror="this.src='../../assets/icon/default-book.png'">
                                        <?php else: ?>
                                            <img src="../../assets/icon/default-book.png"
                                                alt="Cover Default" class="book-cover">
                                        <?php endif; ?>
                                    </td>
                                    <td data-label="Judul"><?= htmlspecialchars($book['Judul']) ?></td>
                                    <td data-label="Penulis"><?= htmlspecialchars($book['Penulis']) ?></td>
                                    <td data-label="Penerbit"><?= htmlspecialchars($book['Penerbit']) ?></td>
                                    <td data-label="Tahun"><?= htmlspecialchars($book['TahunTerbit']) ?></td>
                                    <td data-label="Kategori"><?= htmlspecialchars($book['NamaKategori'] ?? '-') ?></td>
                                    <td data-label="Status">
                                        <span class="badge <?= $book['Status'] === 'Tersedia' ? 'bg-success' : ($book['Status'] === 'Dipinjam' ? 'bg-warning' : 'bg-secondary') ?>">
                                            <?= htmlspecialchars($book['Status']) ?>
                                        </span>
                                    </td>
                                    <td data-label="Aksi">
                                        <div class="btn-group">
                                            <button class="btn btn-sm btn-edit edit-book"
                                                data-id="<?= $book['BukuID'] ?>"
                                                data-judul="<?= htmlspecialchars($book['Judul']) ?>"
                                                data-penulis="<?= htmlspecialchars($book['Penulis']) ?>"
                                                data-penerbit="<?= htmlspecialchars($book['Penerbit']) ?>"
                                                data-tahun="<?= htmlspecialchars($book['TahunTerbit']) ?>"
                                                data-isbn="<?= htmlspecialchars($book['ISBN']) ?>"
                                                data-kategori="<?= $book['KategoriID'] ?>"
                                                data-driveurl="<?= htmlspecialchars($book['DriveURL']) ?>"
                                                data-deskripsi="<?= htmlspecialchars($book['Deskripsi']) ?>"
                                                data-halaman="<?= htmlspecialchars($book['JumlahHalaman']) ?>"
                                                data-bahasa="<?= htmlspecialchars($book['Bahasa']) ?>"
                                                data-format="<?= htmlspecialchars($book['FormatEbook']) ?>"
                                                data-ukuran="<?= htmlspecialchars($book['UkuranFile']) ?>"
                                                data-rating="<?= htmlspecialchars($book['Rating']) ?>"
                                                data-status="<?= htmlspecialchars($book['Status']) ?>"
                                                data-cover="<?= htmlspecialchars($book['Cover']) ?>">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-sm btn-delete delete-book"
                                                data-id="<?= $book['BukuID'] ?>"
                                                data-judul="<?= htmlspecialchars($book['Judul']) ?>">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="9" class="text-center">Tidak ada data buku</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <?php if ($totalPages > 1): ?>
                <nav aria-label="Page navigation">
                    <ul class="pagination">
                        <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                            <a class="page-link"
                                href="?judul=<?= urlencode($filters['judul']) ?>&penulis=<?= urlencode($filters['penulis']) ?>&kategori=<?= urlencode($filters['kategori']) ?>&tahun=<?= urlencode($filters['tahun']) ?>&status=<?= urlencode($filters['status']) ?>&page=<?= $page - 1 ?>">
                                Previous
                            </a>
                        </li>
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                                <a class="page-link"
                                    href="?judul=<?= urlencode($filters['judul']) ?>&penulis=<?= urlencode($filters['penulis']) ?>&kategori=<?= urlencode($filters['kategori']) ?>&tahun=<?= urlencode($filters['tahun']) ?>&status=<?= urlencode($filters['status']) ?>&page=<?= $i ?>">
                                    <?= $i ?>
                                </a>
                            </li>
                        <?php endfor; ?>
                        <li class="page-item <?= ($page >= $totalPages) ? 'disabled' : '' ?>">
                            <a class="page-link"
                                href="?judul=<?= urlencode($filters['judul']) ?>&penulis=<?= urlencode($filters['penulis']) ?>&kategori=<?= urlencode($filters['kategori']) ?>&tahun=<?= urlencode($filters['tahun']) ?>&status=<?= urlencode($filters['status']) ?>&page=<?= $page + 1 ?>">
                                Next
                            </a>
                        </li>
                    </ul>
                </nav>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal untuk Edit Buku -->
<div class="modal" id="bookModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title" id="modalTitle">Edit Buku</h3>
            <button class="close-modal">&times;</button>
        </div>
        <form id="bookForm" method="POST" enctype="multipart/form-data" action="buku_handler.php">
            <input type="hidden" name="buku_id" id="buku_id">
            <input type="hidden" name="action" id="formAction" value="add_book">
            <input type="hidden" name="existing_cover" id="existing_cover">

            <!-- Form Row 1 -->
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="judul">Judul Buku *</label>
                    <input type="text" id="judul" name="judul" class="form-control" required>
                </div>
                <div class="form-group col-md-6">
                    <label for="penulis">Penulis *</label>
                    <input type="text" id="penulis" name="penulis" class="form-control" required>
                </div>
            </div>

            <!-- Form Row 2 -->
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="penerbit">Penerbit</label>
                    <input type="text" id="penerbit" name="penerbit" class="form-control">
                </div>
                <div class="form-group col-md-3">
                    <label for="tahun">Tahun Terbit *</label>
                    <input type="number" id="tahun" name="tahun" class="form-control" required min="1900" max="<?= date('Y') ?>">
                </div>
                <div class="form-group col-md-3">
                    <label for="isbn">ISBN</label>
                    <input type="text" id="isbn" name="isbn" class="form-control">
                </div>
            </div>

            <!-- Form Row 3 - Kategori, Bahasa, Status -->
            <div class="form-row">
                <div class="form-group col-md-4">
                    <label for="kategori">Kategori</label>
                    <select id="kategori" name="kategori" class="form-control">
                        <option value="">-- Pilih Kategori --</option>
                        <?php foreach ($kategories as $kategori): ?>
                            <option value="<?= $kategori['KategoriID'] ?>"><?= htmlspecialchars($kategori['NamaKategori']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group col-md-4">
                    <label for="bahasa">Bahasa *</label>
                    <select id="bahasa" name="bahasa" class="form-control" required>
                        <option value="Indonesia">Indonesia</option>
                        <option value="Inggris">Inggris</option>
                        <option value="Arab">Arab</option>
                        <option value="Lainnya">Lainnya</option>
                    </select>
                </div>
                <div class="form-group col-md-4">
                    <label for="status">Status Akses *</label>
                    <select id="status" name="status" class="form-control" required>
                        <option value="Free">Free (Semua user)</option>
                        <option value="Premium">Premium (User premium saja)</option>
                    </select>
                </div>
            </div>

            <!-- Form Row 4 - Halaman, Format, Ukuran, Rating -->
            <div class="form-row">
                <div class="form-group col-md-3">
                    <label for="halaman">Jumlah Halaman</label>
                    <input type="number" id="halaman" name="halaman" class="form-control" min="1">
                </div>
                <div class="form-group col-md-3">
                    <label for="format">Format Ebook</label>
                    <select id="format" name="format" class="form-control">
                        <option value="PDF">PDF</option>
                        <option value="EPUB">EPUB</option>
                        <option value="DOCX">DOCX</option>
                        <option value="Lainnya">Lainnya</option>
                    </select>
                </div>
                <div class="form-group col-md-3">
                    <label for="ukuran">Ukuran File (MB)</label>
                    <input type="number" id="ukuran" name="ukuran" class="form-control" min="0" step="0.01">
                </div>
                <div class="form-group col-md-3">
                    <label for="rating">Rating</label>
                    <select id="rating" name="rating" class="form-control">
                        <?php for ($i = 0; $i <= 5; $i++): ?>
                            <option value="<?= $i ?>"><?= $i ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
            </div>

            <!-- Drive URL -->
            <div class="form-group">
                <label for="driveurl">Google Drive URL *</label>
                <input type="url" id="driveurl" name="driveurl" class="form-control" required placeholder="https://drive.google.com/...">
            </div>

            <!-- Deskripsi -->
            <div class="form-group">
                <label for="deskripsi">Deskripsi</label>
                <textarea id="deskripsi" name="deskripsi" class="form-control" rows="3"></textarea>
            </div>

            <!-- Cover -->
            <div class="form-group">
                <label for="cover">Cover Buku</label>
                <input type="file" id="cover" name="cover" class="form-control" accept="image/*">
                <div class="cover-preview-container mt-2">
                    <img id="coverPreview" src="" alt="Preview Cover" style="max-width: 150px; max-height: 200px; display: none;">
                </div>
                <small class="text-muted">Format: JPG, PNG. Maksimal 2MB</small>
            </div>

            <!-- Modal Footer -->
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary close-modal">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Konfirmasi Hapus -->
<div class="modal" id="confirmModal">
    <div class="modal-content" style="max-width: 500px;">
        <div class="modal-header">
            <h3 class="modal-title">Konfirmasi Hapus</h3>
            <button class="close-modal">&times;</button>
        </div>
        <div class="modal-body">
            <p id="confirmMessage">Apakah Anda yakin ingin menghapus buku ini?</p>
            <p class="text-muted">Catatan: Data akan dihapus secara soft delete dan dapat dipulihkan.</p>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary close-modal">Batal</button>
            <button type="button" class="btn btn-danger" id="confirmDelete">Hapus</button>
        </div>
    </div>
</div>

<!-- Modal untuk Tambah Buku -->
<div class="modal" id="addBookModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Tambah Buku Baru</h3>
            <button class="close-modal">&times;</button>
        </div>
        <form id="addBookForm" method="POST" action="buku_handler.php" enctype="multipart/form-data">
            <input type="hidden" name="action" value="add_book">

            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="add_judul">Judul Buku *</label>
                    <input type="text" id="add_judul" name="judul" class="form-control" required>
                </div>
                <div class="form-group col-md-6">
                    <label for="add_penulis">Penulis *</label>
                    <input type="text" id="add_penulis" name="penulis" class="form-control" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="add_penerbit">Penerbit</label>
                    <input type="text" id="add_penerbit" name="penerbit" class="form-control">
                </div>
                <div class="form-group col-md-3">
                    <label for="add_tahun">Tahun Terbit *</label>
                    <input type="number" id="add_tahun" name="tahun" class="form-control" required min="1900" max="<?= date('Y') ?>">
                </div>
                <div class="form-group col-md-3">
                    <label for="add_isbn">ISBN</label>
                    <input type="text" id="add_isbn" name="isbn" class="form-control">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group col-md-4">
                    <label for="add_kategori">Kategori</label>
                    <select id="add_kategori" name="kategori" class="form-control">
                        <option value="">-- Pilih Kategori --</option>
                        <?php foreach ($kategories as $kategori): ?>
                            <option value="<?= $kategori['KategoriID'] ?>"><?= htmlspecialchars($kategori['NamaKategori']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group col-md-4">
                    <label for="add_status">Status Akses *</label>
                    <select id="add_status" name="status" class="form-control" required>
                        <option value="Free">Free (Semua user)</option>
                        <option value="Premium">Premium (User premium saja)</option>
                    </select>
                </div>
                <div class="form-group col-md-4">
                    <label for="add_bahasa">Bahasa</label>
                    <select id="bahasa" name="bahasa" class="form-control" required>
                        <option value="Indonesia">Indonesia</option>
                        <option value="Inggris">Inggris</option>
                        <option value="Arab">Arab</option>
                        <option value="Lainnya">Lainnya</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label for="add_driveurl">Google Drive URL *</label>
                <input type="url" id="add_driveurl" name="driveurl" class="form-control" required placeholder="https://drive.google.com/...">
                <small class="text-muted">Pastikan file di Google Drive sudah di-share dengan akses "Siapa saja dengan link"</small>
                <button type="button" id="btnGetFileInfo" class="btn btn-sm btn-secondary mt-2">Ambil Info dari Google Drive</button>
                <div id="fileInfoContainer" class="mt-2" style="display: none;">
                    <div class="alert alert-info p-2">
                        <small>
                            <strong>Info File:</strong>
                            <span id="fileInfoText"></span>
                        </small>
                    </div>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group col-md-3">
                    <label for="add_format">Format Ebook</label>
                    <input type="text" id="add_format" name="format" class="form-control" readonly>
                </div>
                <div class="form-group col-md-3">
                    <label for="add_ukuran">Ukuran File (MB)</label>
                    <input type="number" id="add_ukuran" name="ukuran" class="form-control" readonly step="0.01">
                </div>
                <div class="form-group col-md-3">
                    <label for="add_halaman">Jumlah Halaman</label>
                    <input type="number" id="add_halaman" name="halaman" class="form-control" min="1">
                </div>
                <div class="form-group col-md-3">
                    <label for="add_rating">Rating Awal</label>
                    <input type="number" id="add_rating" name="rating" class="form-control" min="0" max="5" value="0" readonly>
                    <small class="text-muted">Rating akan diupdate oleh user</small>
                </div>
            </div>

            <div class="form-group">
                <label for="add_deskripsi">Deskripsi</label>
                <textarea id="add_deskripsi" name="deskripsi" class="form-control" rows="3"></textarea>
            </div>

            <div class="form-group">
                <label for="add_cover">Cover Buku *</label>
                <input type="file" id="add_cover" name="cover" class="form-control" accept="image/*" required>
                <div class="cover-preview-container mt-2">
                    <img id="add_cover_preview" src="" alt="Preview Cover" style="max-width: 150px; max-height: 200px; display: none;">
                </div>
                <small class="text-muted">Format: JPG, PNG. Maksimal 2MB</small>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary close-modal">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Buku</button>
            </div>
        </form>
    </div>
</div>

<?php include '../../views/footer.php'; ?>

<style>
    /* Base Styles */
    :root {
        --primary: #3a0ca3;
        --primary-light: #5f37ef;
        --secondary: #f8f9fa;
        --text: #333;
        --text-light: #6c757d;
        --border: #dee2e6;
        --success: #28a745;
        --warning: #ffc107;
        --danger: #dc3545;
        --white: #fff;
    }

    * {
        box-sizing: border-box;
        margin: 0;
        padding: 0;
    }

    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        color: var(--text);
        line-height: 1.6;
        background-color: #f5f5f5;
    }

    /* Layout Styles */
    .content-wrapper {
        padding: 20px;
        max-width: 1400px;
        margin: 0 auto;
    }

    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
        flex-wrap: wrap;
        gap: 1rem;
    }

    .page-header h1 {
        font-size: 1.8rem;
        color: var(--primary);
        margin: 0;
    }

    .header-actions {
        display: flex;
        gap: 1rem;
    }

    .card {
        background: var(--white);
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        margin-bottom: 2rem;
        overflow: hidden;
    }

    .card-body {
        padding: 1.5rem;
    }

    .user-info .avatar {
        width: 40px;
        height: 40px;
        background-color: #f72585;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        color: black;
        flex-shrink: 0;
    }

    /* Filter Form Styles */
    .advanced-filter-form {
        background: var(--white);
        padding: 1.2rem;
        border-radius: 10px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        margin-bottom: 2rem;
    }

    .filter-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
        gap: 1rem;
        align-items: flex-end;
    }

    .filter-group {
        position: relative;
        width: 100%;
    }

    .filter-actions {
        grid-column: 1 / -1;
        display: flex;
        gap: 0.8rem;
        justify-content: flex-end;
    }

    /* Input and Select Styles */
    .input-icon {
        position: relative;
        width: 100%;
    }

    .input-icon i {
        position: absolute;
        left: 12px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--text-light);
        font-size: 1rem;
        pointer-events: none;
        z-index: 2;
    }

    .form-control {
        width: 100%;
        padding: 0.5rem 1rem;
        border: 1px solid var(--border);
        border-radius: 6px;
        font-size: 0.9rem;
        transition: border-color 0.3s;
    }

    .form-control:focus {
        outline: none;
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(58, 12, 163, 0.1);
    }

    .modern-input {
        padding-left: 2.5em;
        height: 42px;
    }

    .select-wrapper {
        position: relative;
        width: 100%;
    }

    .select-wrapper i {
        position: absolute;
        left: 12px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--text-light);
        font-size: 1rem;
        pointer-events: none;
        z-index: 2;
    }

    .modern-select {
        padding-left: 2.5em;
        height: 42px;
        appearance: none;
        background-color: var(--white);
        cursor: pointer;
    }

    /* Button Styles */
    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0.5rem 1rem;
        border-radius: 6px;
        font-size: 0.9rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        border: none;
        gap: 0.5rem;
        text-decoration: none;
    }

    .btn i {
        font-size: 0.9em;
    }

    .btn-primary {
        background-color: var(--primary);
        color: var(--white);
    }

    .btn-primary:hover {
        background-color: var(--primary-light);
    }

    .btn-secondary {
        background-color: var(--secondary);
        color: var(--text);
    }

    .btn-secondary:hover {
        background-color: #e9ecef;
    }

    .btn-filter {
        background: var(--primary);
        color: var(--white);
        height: 42px;
    }

    .btn-filter:hover {
        background: var(--primary-light);
    }

    .btn-reset {
        background: var(--secondary);
        color: var(--primary);
        height: 42px;
    }

    .btn-reset:hover {
        background: #e9ecef;
    }

    .btn-danger {
        background-color: var(--danger);
        color: var(--white);
    }

    .btn-danger:hover {
        background-color: #c82333;
    }

    .btn-sm {
        padding: 0.3rem 0.6rem;
        font-size: 0.8rem;
    }

    .btn-group {
        display: flex;
        gap: 0.5rem;
    }

    .btn-edit {
        background-color: #e0f2fe;
        color: #0369a1;
    }

    .btn-edit:hover {
        background-color: #bae6fd;
    }

    .btn-delete {
        background-color: #fee2e2;
        color: #b91c1c;
    }

    .btn-delete:hover {
        background-color: #fecaca;
    }

    /* Table Styles */
    .table-responsive {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        margin-bottom: 1rem;
    }

    .table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 1rem;
        background-color: transparent;
    }

    .table th {
        background-color: var(--secondary);
        color: var(--text);
        vertical-align: bottom;
        border-bottom: 2px solid var(--border);
        padding: 12px 10px;
        text-align: left;
        font-weight: 600;
        font-size: 0.85rem;
        white-space: nowrap;
    }

    .table td {
        padding: 12px 10px;
        vertical-align: middle;
        border-top: 1px solid var(--border);
        font-size: 0.85rem;
    }

    .book-cover {
        width: 50px;
        height: 70px;
        object-fit: cover;
        border-radius: 4px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    /* Badge Styles */
    .badge {
        display: inline-block;
        padding: 0.35em 0.65em;
        font-size: 0.75em;
        font-weight: 600;
        line-height: 1;
        text-align: center;
        white-space: nowrap;
        vertical-align: baseline;
        border-radius: 0.25rem;
    }

    .bg-success {
        background-color: var(--success);
        color: var(--white);
    }

    .bg-warning {
        background-color: var(--warning);
        color: var(--text);
    }

    .bg-secondary {
        background-color: var(--text-light);
        color: var(--white);
    }

    /* Pagination Styles */
    .pagination {
        display: flex;
        gap: 0.5rem;
        list-style: none;
        padding: 0;
        margin: 2rem 0;
        justify-content: center;
        flex-wrap: wrap;
    }

    .page-item {
        transition: transform 0.2s ease;
    }

    .page-item:hover {
        transform: translateY(-2px);
    }

    .page-link {
        display: block;
        padding: 0.5rem 0.75rem;
        text-decoration: none;
        border-radius: 6px;
        background: var(--secondary);
        color: var(--primary);
        border: 1px solid var(--border);
        transition: all 0.3s ease;
        font-weight: 500;
        font-size: 0.9rem;
    }

    .page-link:hover {
        background: var(--primary);
        color: var(--white);
        border-color: var(--primary);
    }

    .page-item.active .page-link {
        background: var(--primary);
        border-color: var(--primary);
        color: var(--white);
    }

    .page-item.disabled .page-link {
        background: var(--secondary);
        color: #adb5bd;
        cursor: not-allowed;
        opacity: 0.7;
    }

    /* Modal Styles */
    .modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
        z-index: 1050;
        overflow-y: auto;
        padding: 1rem;
    }

    .modal.show {
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .modal-content {
        background-color: var(--white);
        border-radius: 10px;
        width: 100%;
        max-width: 800px;
        max-height: 90vh;
        overflow-y: auto;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
    }

    .modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1.2rem;
        border-bottom: 1px solid var(--border);
    }

    .modal-title {
        margin: 0;
        font-size: 1.3rem;
        color: var(--primary);
    }

    .close-modal {
        background: none;
        border: none;
        font-size: 1.5rem;
        cursor: pointer;
        color: var(--text-light);
        transition: color 0.3s;
    }

    .close-modal:hover {
        color: var(--text);
    }

    .modal-body {
        padding: 1.2rem;
    }

    .modal-footer {
        padding: 1rem;
        border-top: 1px solid var(--border);
        display: flex;
        justify-content: flex-end;
        gap: 0.5rem;
    }

    /* Form Styles */
    .form-row {
        display: flex;
        flex-wrap: wrap;
        margin: 0 -0.75rem;
    }

    .form-group {
        padding: 0 0.75rem;
        margin-bottom: 1rem;
        width: 100%;
    }

    .form-group label {
        display: block;
        margin-bottom: 0.5rem;
        font-weight: 500;
        font-size: 0.9rem;
    }

    .form-group small {
        display: block;
        margin-top: 0.3rem;
        color: var(--text-light);
        font-size: 0.8rem;
    }

    .col-md-6 {
        flex: 0 0 50%;
        max-width: 50%;
    }

    .col-md-4 {
        flex: 0 0 33.333333%;
        max-width: 33.333333%;
    }

    .col-md-3 {
        flex: 0 0 25%;
        max-width: 25%;
    }

    textarea.form-control {
        min-height: 100px;
        resize: vertical;
    }

    .cover-preview-container {
        margin-top: 0.5rem;
    }

    .cover-preview-container img {
        max-width: 150px;
        max-height: 200px;
        border-radius: 4px;
        border: 1px solid var(--border);
    }

    /* Alert Styles */
    .alert {
        padding: 0.75rem 1.25rem;
        margin-bottom: 1rem;
        border-radius: 6px;
        font-size: 0.9rem;
    }

    .alert-info {
        background-color: #e7f5ff;
        color: #1864ab;
        border: 1px solid #a5d8ff;
    }

    /* Responsive Table */
    @media (max-width: 768px) {
        .table {
            display: block;
        }

        .table thead {
            display: none;
        }

        .table tbody {
            display: block;
            width: 100%;
        }

        .table tr {
            display: block;
            margin-bottom: 1rem;
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 0.5rem;
            background: var(--white);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .table td {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.5rem;
            border: none;
            border-bottom: 1px solid #f1f1f1;
        }

        .table td:last-child {
            border-bottom: none;
        }

        .table td::before {
            content: attr(data-label);
            font-weight: 600;
            color: var(--text);
            margin-right: 1rem;
            flex: 0 0 120px;
        }

        .table td .book-cover {
            width: 40px;
            height: 60px;
        }

        .table td .btn-group {
            justify-content: flex-end;
            flex: 1;
        }

        /* Adjust form layout for mobile */
        .form-row {
            flex-direction: column;
            margin: 0;
        }

        .col-md-6,
        .col-md-4,
        .col-md-3 {
            flex: 0 0 100%;
            max-width: 100%;
            padding: 0;
        }

        .modal-content {
            max-width: 95%;
        }
    }

    /* Small mobile devices */
    @media (max-width: 480px) {
        .content-wrapper {
            padding: 15px;
        }

        .page-header h1 {
            font-size: 1.5rem;
        }

        .advanced-filter-form {
            padding: 1rem;
        }

        .filter-grid {
            gap: 0.8rem;
        }

        .filter-actions {
            flex-direction: column;
            gap: 0.5rem;
        }

        .btn-filter,
        .btn-reset {
            width: 100%;
        }

        .form-control,
        .modern-select {
            height: 38px;
            font-size: 0.85rem;
        }

        .input-icon i,
        .select-wrapper i {
            font-size: 0.9rem;
        }

        .table td::before {
            flex: 0 0 90px;
            font-size: 0.8rem;
        }

        .table td {
            font-size: 0.8rem;
        }

        .btn-group .btn {
            padding: 0.25rem 0.5rem;
        }

        .modal-header {
            padding: 1rem;
        }

        .modal-title {
            font-size: 1.1rem;
        }

        .modal-body,
        .modal-footer {
            padding: 0.8rem;
        }
    }

    .fa-edit, .fa-trash {
        margin: 0 auto;
    }
</style>

<script src="../../assets/js/buku_script.js"></script>