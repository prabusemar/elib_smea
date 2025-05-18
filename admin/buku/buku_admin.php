<?php
session_start();
require_once '../../config.php';

// Pastikan hanya admin yang bisa mengakses
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../auth/login.php");
    exit;
}

// Fungsi untuk mendapatkan semua buku
function getAllBooks($conn)
{
    $query = "SELECT b.*, k.NamaKategori 
              FROM buku b
              LEFT JOIN kategori k ON b.KategoriID = k.KategoriID
              WHERE b.DeletedAt IS NULL
              ORDER BY b.Judul";
    $result = mysqli_query($conn, $query);

    if (!$result) {
        die("Error: " . mysqli_error($conn));
    }

    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}

// Ambil data buku
$books = getAllBooks($conn);

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
                                    <td><?= $index + 1 ?></td>
                                    <td>
                                        <?php if (!empty($book['Cover'])): ?>
                                            <img src="<?= '../../' . htmlspecialchars($book['Cover']) ?>"
                                                alt="Cover Buku" class="book-cover"
                                                onerror="this.src='../../assets/icon/default-book.png'">
                                        <?php else: ?>
                                            <img src="../../assets/icon/default-book.png"
                                                alt="Cover Default" class="book-cover">
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($book['Judul']) ?></td>
                                    <td><?= htmlspecialchars($book['Penulis']) ?></td>
                                    <td><?= htmlspecialchars($book['Penerbit']) ?></td>
                                    <td><?= htmlspecialchars($book['TahunTerbit']) ?></td>
                                    <td><?= htmlspecialchars($book['NamaKategori'] ?? '-') ?></td>
                                    <td>
                                        <span class="badge <?= $book['Status'] === 'Tersedia' ? 'bg-success' : ($book['Status'] === 'Dipinjam' ? 'bg-warning' : 'bg-secondary') ?>">
                                            <?= htmlspecialchars($book['Status']) ?>
                                        </span>
                                    </td>
                                    <td>
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
        </div>
    </div>
</div>

<!-- Modal untuk Tambah/Edit Buku -->
<div class="modal" id="bookModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title" id="modalTitle">Tambah Buku Baru</h3>
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
                        <option value="0">0</option>
                        <option value="1">1</option>
                        <option value="2">2</option>
                        <option value="3">3</option>
                        <option value="4">4</option>
                        <option value="5">5</option>
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
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
        z-index: 1000;
        justify-content: center;
        align-items: center;
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
</style>

<script src="../../assets/js/buku_script.js"></script>