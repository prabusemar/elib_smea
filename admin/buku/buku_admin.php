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
            <div class="filter-group">
                <div class="input-icon">
                    <i class="fas fa-book"></i>
                    <input type="text" name="judul" class="form-control modern-input"
                        placeholder="Cari Judul"
                        value="<?= htmlspecialchars($_GET['judul'] ?? '') ?>">
                </div>
            </div>

            <div class="filter-group">
                <div class="input-icon">
                    <i class="fas fa-user-edit"></i>
                    <input type="text" name="penulis" class="form-control modern-input"
                        placeholder="Nama Penulis"
                        style="padding-left: calc(2.2em + 10px);"
                        value="<?= htmlspecialchars($_GET['penulis'] ?? '') ?>">
                </div>
            </div>

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

            <div class="filter-group">
                <div class="input-icon">
                    <i class="fas fa-calendar-alt"></i>
                    <input type="number" name="tahun" class="form-control modern-input"
                        placeholder="Tahun Terbit"
                        value="<?= htmlspecialchars($_GET['tahun'] ?? '') ?>">
                </div>
            </div>

            <div class="filter-group">
                <div class="select-wrapper">
                    <i class="fas fa-info-circle"></i>
                    <select name="status" class="form-control modern-select">
                        <option value="">Status Buku</option>
                        <option value="Free" <?= ($_GET['status'] ?? '') == 'Free' ? 'selected' : '' ?>>Free</option>
                        <option value="Premium" <?= ($_GET['status'] ?? '') == 'Premium' ? 'selected' : '' ?>>Premium</option>
                    </select>
                </div>
            </div>

            <div class="filter-actions">
                <button type="submit" class="btn btn-filter">
                    <i class="fas fa-filter"></i> Terapkan Filter
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
    .book-cover {
        width: 50px;
        height: 70px;
        object-fit: cover;
        border-radius: 4px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .badge {
        display: inline-block;
        padding: 0.35em 0.65em;
        font-size: 0.75em;
        font-weight: 700;
        line-height: 1;
        text-align: center;
        white-space: nowrap;
        vertical-align: baseline;
        border-radius: 0.25rem;
    }

    .bg-success {
        background-color: #28a745;
        color: white;
    }

    .bg-warning {
        background-color: #ffc107;
        color: #212529;
    }

    .bg-secondary {
        background-color: #6c757d;
        color: white;
    }

    .btn-group {
        display: flex;
        gap: 0.3rem;
    }

    .btn-sm {
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
    }

    .btn-edit {
        background-color: #e0f2fe;
        color: #0369a1;
        border: none;
    }

    .btn-delete {
        background-color: #fee2e2;
        color: #b91c1c;
        border: none;
    }

    .form-row {
        display: flex;
        flex-wrap: wrap;
        margin-right: -15px;
        margin-left: -15px;
    }

    .form-group {
        padding-right: 15px;
        padding-left: 15px;
        margin-bottom: 1rem;
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

    /* Style untuk form tambah buku */
    .modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100vh;
        background: rgba(0, 0, 0, 0.5);
        z-index: 9999;
    }

    .modal.show {
        display: flex !important;
        align-items: center;
        justify-content: center;
    }

    .modal-content {
        background-color: white;
        padding: 2rem;
        border-radius: 8px;
        width: 90%;
        max-width: 800px;
        max-height: 90vh;
        overflow-y: auto;
    }

    .modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
        padding-bottom: 1rem;
        border-bottom: 1px solid #eee;
    }

    .modal-title {
        margin: 0;
        color: #3a0ca3;
    }

    .close-modal {
        background: none;
        border: none;
        font-size: 1.5rem;
        cursor: pointer;
        color: #6c757d;
    }

    .modal-footer {
        margin-top: 1.5rem;
        padding-top: 1rem;
        border-top: 1px solid #eee;
        display: flex;
        justify-content: flex-end;
        gap: 0.5rem;
    }

    .is-invalid {
        border-color: #dc3545 !important;
    }

    .text-muted {
        color: #6c757d;
        font-size: 0.875rem;
    }

    .mt-2 {
        margin-top: 0.5rem;
    }

    .fa-trash,
    .fa-edit {
        margin-left: 10px;
    }

    /* Add this to your CSS */
    .modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
        z-index: 1000;
        justify-content: center;
        align-items: center;
    }

    .modal-content {
        background: white;
        padding: 20px;
        border-radius: 5px;
        width: 80%;
        max-width: 800px;
        max-height: 90vh;
        overflow-y: auto;
    }

    /* Filter */
    .advanced-filter-form {
        background: #f8f9fa;
        padding: 1.5rem;
        border-radius: 10px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        margin-bottom: 2rem;
    }

    .filter-grid {
        display: flex;
        flex-wrap: wrap;
        gap: 1rem;
        align-items: flex-end;
    }

    .filter-group {
        flex: 1 1 180px;
        min-width: 180px;
        position: relative;
    }

    .filter-actions {
        display: flex;
        gap: 0.8rem;
        align-items: flex-end;
        margin-left: auto;
    }

    /* Tombol Filter dan Reset */
    .btn-filter {
        background: #3a0ca3;
        color: #fff;
        border: none;
        border-radius: 6px;
        font-weight: 600;
        transition: background 0.2s, color 0.2s;
        box-shadow: 0 2px 6px rgba(58, 12, 163, 0.08);
    }

    .btn-filter:hover,
    .btn-filter:focus {
        background: #5f37ef;
        color: #fff;
    }

    .btn-reset {
        background: #f1f3f5;
        color: #3a0ca3;
        border: none;
        border-radius: 6px;
        font-weight: 600;
        transition: background 0.2s, color 0.2s;
        box-shadow: 0 2px 6px rgba(58, 12, 163, 0.05);
    }

    .btn-reset:hover,
    .btn-reset:focus {
        background: #e0e0e0;
        color: #3a0ca3;
    }

    /* Input icon inside input box */
    .input-icon {
        position: relative;
        width: 100%;
    }

    .input-icon i {
        position: absolute;
        left: 12px;
        top: 50%;
        transform: translateY(-50%);
        color: #888;
        font-size: 1rem;
        pointer-events: none;
        z-index: 2;
    }

    .input-icon .form-control.modern-input {
        padding-left: 2.2em;
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
        color: #888;
        font-size: 1rem;
        pointer-events: none;
        z-index: 2;
    }

    .select-wrapper select.modern-select {
        padding-left: 2.2em;
    }

    @media (max-width: 900px) {
        .filter-grid {
            flex-direction: column;
            gap: 1rem;
        }

        .filter-actions {
            margin-left: 0;
            width: 100%;
            justify-content: stretch;
        }

        .btn-filter,
        .btn-reset {
            flex: 1;
            justify-content: center;
        }
    }

    a {
        text-decoration: none;
        color: inherit;
    }

    /* Responsive Table Styles */
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
        background-color: #f8f9fa;
        color: #495057;
        vertical-align: bottom;
        border-bottom: 2px solid #dee2e6;
        padding: 12px 8px;
        text-align: left;
        font-weight: 600;
    }

    .table td {
        padding: 12px 8px;
        vertical-align: middle;
        border-top: 1px solid #dee2e6;
    }

    /* Responsive table behavior */
    @media (max-width: 768px) {

        /* Make table behave like blocks */
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
            margin-bottom: 15px;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 10px;
            background: #fff;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .table td {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 10px;
            border: none;
            border-bottom: 1px solid #f1f1f1;
        }

        .table td:last-child {
            border-bottom: none;
        }

        /* Add data-label pseudo-elements */
        .table td::before {
            content: attr(data-label);
            font-weight: 600;
            color: #495057;
            margin-right: 15px;
            flex: 0 0 120px;
        }

        /* Add data labels to each cell */
        .table td:nth-child(1) {
            data-label: "No";
        }

        .table td:nth-child(2) {
            data-label: "Cover";
        }

        .table td:nth-child(3) {
            data-label: "Judul";
        }

        .table td:nth-child(4) {
            data-label: "Penulis";
        }

        .table td:nth-child(5) {
            data-label: "Penerbit";
        }

        .table td:nth-child(6) {
            data-label: "Tahun";
        }

        .table td:nth-child(7) {
            data-label: "Kategori";
        }

        .table td:nth-child(8) {
            data-label: "Status";
        }

        .table td:nth-child(9) {
            data-label: "Aksi";
        }

        /* Special styling for cover image */
        .table td .book-cover {
            width: 40px;
            height: 60px;
        }

        /* Adjust action buttons */
        .table td .btn-group {
            justify-content: flex-end;
            flex: 1;
        }
    }

    /* For very small screens */
    @media (max-width: 480px) {
        .table td::before {
            flex: 0 0 90px;
        }

        .table td {
            font-size: 14px;
        }
    }

    /*Responsive Table Styles*/
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
        background-color: #f8f9fa;
        color: #495057;
        vertical-align: bottom;
        border-bottom: 2px solid #dee2e6;
        padding: 12px 8px;
        text-align: left;
        font-weight: 600;
    }

    .table td {
        padding: 12px 8px;
        vertical-align: middle;
        border-top: 1px solid #dee2e6;
    }

    /* Responsive table behavior */
    @media (max-width: 768px) {

        /* Make table behave like blocks */
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
            margin-bottom: 15px;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 10px;
            background: #fff;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .table td {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 10px;
            border: none;
            border-bottom: 1px solid #f1f1f1;
        }

        .table td:last-child {
            border-bottom: none;
        }

        /* Add data-label pseudo-elements */
        .table td::before {
            content: attr(data-label);
            font-weight: 600;
            color: #495057;
            margin-right: 15px;
            flex: 0 0 120px;
        }

        /* Add data labels to each cell */
        .table td:nth-child(1) {
            data-label: "No";
        }

        .table td:nth-child(2) {
            data-label: "Cover";
        }

        .table td:nth-child(3) {
            data-label: "Judul";
        }

        .table td:nth-child(4) {
            data-label: "Penulis";
        }

        .table td:nth-child(5) {
            data-label: "Penerbit";
        }

        .table td:nth-child(6) {
            data-label: "Tahun";
        }

        .table td:nth-child(7) {
            data-label: "Kategori";
        }

        .table td:nth-child(8) {
            data-label: "Status";
        }

        .table td:nth-child(9) {
            data-label: "Aksi";
        }

        /* Special styling for cover image */
        .table td .book-cover {
            width: 40px;
            height: 60px;
        }

        /* Adjust action buttons */
        .table td .btn-group {
            justify-content: flex-end;
            flex: 1;
        }
    }

    /* For very small screens */
    @media (max-width: 480px) {
        .table td::before {
            flex: 0 0 90px;
        }

        .table td {
            font-size: 14px;
        }
    }

    /*Pagination*/
    /* Pagination Modern */
    .pagination {
        display: flex;
        gap: 8px;
        list-style: none;
        padding: 0;
        margin: 2rem 0;
        justify-content: center;
    }

    .page-item {
        transition: transform 0.2s ease;
    }

    .page-item:hover {
        transform: translateY(-2px);
    }

    .page-link {
        display: block;
        padding: 10px 18px;
        text-decoration: none;
        border-radius: 8px;
        background: #f8f9fa;
        color: var(--primary);
        border: 1px solid #e9ecef;
        transition: all 0.3s ease;
        font-weight: 500;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    }

    .page-link:hover {
        background: var(--primary);
        color: white;
        border-color: var(--primary);
        box-shadow: 0 4px 8px rgba(108, 92, 231, 0.2);
    }

    .page-item.active .page-link {
        background: var(--primary);
        border-color: var(--primary);
        color: white;
        box-shadow: 0 4px 12px rgba(108, 92, 231, 0.3);
    }

    .page-item.disabled .page-link {
        background: #f8f9fa;
        color: #adb5bd;
        cursor: not-allowed;
        opacity: 0.7;
    }

    /* Mobile View */
    @media (max-width: 768px) {
        .pagination {
            flex-wrap: wrap;
            gap: 6px;
        }

        .page-link {
            padding: 8px 14px;
            font-size: 0.9rem;
            min-width: 36px;
            text-align: center;
        }
    }

    /* Search & Filter Nav */
    .search-filter-form {
        display: flex;
        gap: 12px;
        margin-bottom: 2rem;
        background: white;
        padding: 20px;
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    }

    .input-group {
        flex: 1;
        border-radius: 8px;
        overflow: hidden;
        transition: box-shadow 0.3s ease;
    }

    .input-group:focus-within {
        box-shadow: 0 0 0 3px rgba(108, 92, 231, 0.2);
    }

    .form-control[name="search"] {
        border: none;
        padding: 14px 20px;
        font-size: 1rem;
        background: #f8f9fa;
    }

    .form-control[name="search"]::placeholder {
        color: #868e96;
    }

    .btn-primary[type="submit"] {
        background: var(--primary);
        border: none;
        padding: 0 28px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    select[name="jenis_akun"] {
        background: #f8f9fa;
        border: none;
        padding: 14px 20px;
        font-size: 1rem;
        border-radius: 8px;
        cursor: pointer;
        appearance: none;
        background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%236c5ce7' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
        background-repeat: no-repeat;
        background-position: right 15px center;
        background-size: 18px;
        padding-right: 45px;
    }

    .fa-user-edit {
        margin-right: 15px;
    }
</style>

<script src="../../assets/js/buku_script.js"></script>