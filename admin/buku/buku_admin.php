<?php
session_start();

// Check if user is logged in and is admin
if (!isset($_SESSION['username'])) {
    header("Location: ../../auth/login.php");
    exit;
}

// Database connection
include '../../config.php';

// Query untuk mendapatkan data buku dengan join ke tabel kategori
$query = "SELECT b.*, k.NamaKategori 
          FROM buku b 
          LEFT JOIN kategori k ON b.KategoriID = k.KategoriID
          WHERE b.DeletedAt IS NULL
          ORDER BY b.CreatedAt DESC";
$result = mysqli_query($conn, $query);

// Query untuk opsi kategori
$kategori_query = "SELECT * FROM kategori";
$kategori_result = mysqli_query($conn, $kategori_query);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Buku - Perpustakaan Digital</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        :root {
            --primary: #3a0ca3;
            --primary-light: #4361ee;
            --secondary: #f72585;
            --light: #f8f9fa;
            --dark: #212529;
            --gray: #6c757d;
            --sidebar-width: 280px;
            --sidebar-collapsed-width: 80px;
            --header-height: 70px;
        }

        /* Main Content Styles - Optimized */
        .main-content {
            flex: 1;
            margin-left: var(--sidebar-width);
            transition: all 0.3s ease;
            min-height: 100vh;
            background-color: #f8fafc;
        }

        .sidebar.collapsed~.main-content {
            margin-left: var(--sidebar-collapsed-width);
        }

        .header {
            height: var(--header-height);
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 2rem;
            background-color: white;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            position: sticky;
            top: 0;
            z-index: 90;
        }

        .header h1 {
            color: var(--dark);
            font-size: 1.5rem;
            font-weight: 600;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .header-actions {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .search-bar {
            position: relative;
            min-width: 150px;
        }

        .search-bar input {
            padding: 0.6rem 1rem 0.6rem 2.5rem;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            font-size: 0.9rem;
            width: 250px;
            max-width: 100%;
            transition: all 0.3s ease;
        }

        .search-bar input:focus {
            outline: none;
            border-color: var(--primary-light);
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.1);
        }

        .search-bar i {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--gray);
        }

        .content-container {
            padding: 2rem;
            width: 100%;
            box-sizing: border-box;
        }

        .page-header {
            display: flex;
            flex-direction: column;
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .page-title {
            color: var(--primary);
            font-size: 1.8rem;
            font-weight: 600;
            margin: 0;
        }

        .btn {
            padding: 0.7rem 1.5rem;
            border-radius: 6px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            min-height: 36px;
            box-sizing: border-box;
        }

        .btn-primary {
            background-color: var(--primary);
            color: white;
            border: none;
        }

        .btn-primary:hover {
            background-color: #2e0a8a;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            padding: 1.5rem;
            margin-bottom: 2rem;
            overflow: hidden;
        }

        .table-container {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
        }

        th {
            background-color: #f8fafc;
            color: var(--primary);
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.8rem;
            letter-spacing: 0.5px;
        }

        tr:hover {
            background-color: #f9fafb;
        }

        .badge {
            display: inline-block;
            padding: 0.3rem 0.6rem;
            border-radius: 50px;
            font-size: 0.75rem;
            font-weight: 500;
        }

        .badge-success {
            background-color: #d1fae5;
            color: #065f46;
        }

        .badge-warning {
            background-color: #fef3c7;
            color: #92400e;
        }

        .action-btns {
            display: flex;
            gap: 0.5rem;
        }

        .btn-icon {
            width: 32px;
            height: 32px;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s ease;
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

        .search-filter {
            display: flex;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .search-box {
            flex: 1;
            position: relative;
            min-width: 200px;
        }

        .search-box input {
            width: 100%;
            padding: 0.7rem 1rem 0.7rem 2.5rem;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            font-size: 0.9rem;
        }

        .search-box i {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--gray);
        }

        .filter-select {
            min-width: 200px;
            padding: 0.7rem 1rem;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            font-size: 0.9rem;
        }

        .pagination {
            display: flex;
            justify-content: flex-end;
            gap: 0.5rem;
            margin-top: 1.5rem;
            flex-wrap: wrap;
        }

        .page-link {
            padding: 0.5rem 0.8rem;
            border-radius: 4px;
            background-color: white;
            border: 1px solid #e2e8f0;
            color: var(--dark);
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .page-link:hover {
            background-color: #f1f5f9;
        }

        .page-link.active {
            background-color: var(--primary);
            color: white;
            border-color: var(--primary);
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
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }

        .modal-content {
            background-color: white;
            border-radius: 10px;
            width: 90%;
            max-width: 600px;
            max-height: 90vh;
            overflow-y: auto;
            padding: 2rem;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .modal-title {
            font-size: 1.5rem;
            color: var(--primary);
            font-weight: 600;
        }

        .close-modal {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: var(--gray);
        }

        .form-group {
            margin-bottom: 1.2rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: var(--dark);
        }

        .form-control {
            width: 100%;
            padding: 0.7rem 1rem;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            font-size: 0.9rem;
            box-sizing: border-box;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2);
        }

        .modal-footer {
            display: flex;
            justify-content: flex-end;
            gap: 1rem;
            margin-top: 2rem;
        }

        .btn-cancel {
            background-color: #f1f5f9;
            color: var(--dark);
            border: none;
        }

        .btn-cancel:hover {
            background-color: #e2e8f0;
        }

        /* Responsive Enhancements */
        @media (max-width: 768px) {
            .search-filter {
                flex-direction: column;
            }

            .header {
                padding: 0 1rem;
            }

            .content-container {
                padding: 1.5rem 1rem;
            }

            .page-header {
                flex-direction: column;
                align-items: flex-start;
            }

            .search-bar input {
                width: 100%;
            }
        }

        @media (min-width: 768px) {
            .page-header {
                flex-direction: row;
                justify-content: space-between;
                align-items: center;
            }
        }

        @media (max-width: 640px) {
            .modal-content {
                padding: 1.5rem 1rem;
            }

            table {
                display: block;
                width: 100%;
            }

            thead {
                display: none;
            }

            tbody tr {
                display: block;
                margin-bottom: 1rem;
                border: 1px solid #e2e8f0;
                border-radius: 6px;
            }

            tbody td {
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: 0.75rem;
                border-bottom: 1px solid #f1f5f9;
            }

            tbody td:before {
                content: attr(data-label);
                font-weight: 600;
                color: var(--primary);
                margin-right: 1rem;
            }

            .action-btns {
                justify-content: flex-end;
            }

            .pagination {
                justify-content: center;
            }
        }

        .cover-preview {
            max-width: 100px;
            max-height: 150px;
            margin-top: 10px;
            display: none;
        }

        .file-info {
            font-size: 0.8rem;
            color: var(--gray);
            margin-top: 5px;
        }

        .fa-edit,
        .fa-trash {
            margin-left: 10px;
        }

        .form-row {
            display: flex;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .form-row .form-group {
            flex: 1;
        }

        .badge-free {
            background-color: #d1fae5;
            color: #065f46;
        }

        .badge-premium {
            background-color: #e0f2fe;
            color: #0369a1;
        }

        .badge-public {
            background-color: #d1fae5;
            color: #065f46;
        }

        .badge-private {
            background-color: #fee2e2;
            color: #b91c1c;
        }

        .badge-draft {
            background-color: #fef3c7;
            color: #92400e;
        }
    </style>
</head>

<body>
    <?php include '../../views/sidebar_admin.php'; ?>

    <!-- Main Content -->
    <main class="main-content">
        <div class="header">
            <h1>Manajemen Buku</h1>
        </div>

        <div class="content-container">
            <?php include '../../views/alert_messages.php'; ?>

            <div class="page-header">
                <h2 class="page-title">Daftar Buku</h2>
                <button class="btn btn-primary" id="addBookBtn">
                    <i class="fas fa-plus"></i> Tambah Buku
                </button>
            </div>

            <div class="card">
                <div class="search-filter">
                    <div class="search-box">
                        <i class="fas fa-search"></i>
                        <input type="text" id="searchInput" placeholder="Cari buku...">
                    </div>
                    <select class="filter-select" id="categoryFilter">
                        <option value="">Semua Kategori</option>
                        <?php while ($kategori = mysqli_fetch_assoc($kategori_result)) : ?>
                            <option value="<?= $kategori['KategoriID'] ?>"><?= $kategori['NamaKategori'] ?></option>
                        <?php endwhile; ?>
                    </select>
                    <select class="filter-select" id="statusFilter">
                        <option value="">Semua Status</option>
                        <option value="Published">Published</option>
                        <option value="Archived">Archived</option>
                        <option value="PendingReview">Pending Review</option>
                    </select>
                    <select class="filter-select" id="accessFilter">
                        <option value="">Semua Akses</option>
                        <option value="Free">Free</option>
                        <option value="Premium">Premium</option>
                    </select>
                </div>

                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Cover</th>
                                <th>Judul</th>
                                <th>Pengarang</th>
                                <th>Kategori</th>
                                <th>Akses</th>
                                <th>Status</th>
                                <th>Dibuat</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            mysqli_data_seek($result, 0); // Reset pointer
                            $no = 1;
                            ?>
                            <?php while ($buku = mysqli_fetch_assoc($result)) : ?>
                                <tr>
                                    <td><?= $no++ ?></td>
                                    <td>
                                        <?php if (!empty($buku['Cover'])) : ?>
                                            <img src="<?= BASE_URL . $buku['Cover'] ?>" alt="Cover" style="max-width: 50px; height: auto;"
                                                onerror="this.onerror=null;this.src='https://via.placeholder.com/50x75?text=No+Cover';">
                                        <?php else : ?>
                                            <i class="fas fa-book" style="font-size: 24px; color: var(--primary);"></i>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?= htmlspecialchars($buku['Judul']) ?>
                                        <?php if (!empty($buku['Slug'])) : ?>
                                            <br><small class="text-muted">/<?= $buku['Slug'] ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($buku['Penulis']) ?></td>
                                    <td><?= $buku['NamaKategori'] ?? 'Tidak Berkategori' ?></td>
                                    <td>
                                        <span class="badge <?= $buku['JenisAkses'] === 'Premium' ? 'badge-premium' : 'badge-free' ?>">
                                            <?= $buku['JenisAkses'] ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge <?= 'badge-' . strtolower($buku['Status']) ?>">
                                            <?= $buku['Status'] ?>
                                        </span>
                                    </td>
                                    <td><?= date('d M Y', strtotime($buku['CreatedAt'])) ?></td>
                                    <td>
                                        <div class="action-btns">
                                            <div class="btn-icon btn-edit" title="Edit" onclick="editBook(<?= $buku['BukuID'] ?>)">
                                                <i class="fas fa-edit"></i>
                                            </div>
                                            <div class="btn-icon btn-delete" title="Hapus" onclick="confirmDelete(<?= $buku['BukuID'] ?>)">
                                                <i class="fas fa-trash"></i>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>

                <div class="pagination">
                    <div class="page-link"><i class="fas fa-chevron-left"></i></div>
                    <div class="page-link active">1</div>
                    <div class="page-link">2</div>
                    <div class="page-link">3</div>
                    <div class="page-link"><i class="fas fa-chevron-right"></i></div>
                </div>
            </div>
        </div>
    </main>

    <!-- Add Book Modal -->
    <div class="modal" id="bookModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title" id="modalTitle">Tambah Buku Baru</h3>
                <button class="close-modal">&times;</button>
            </div>
            <form id="bookForm" enctype="multipart/form-data">
                <input type="hidden" id="bukuId" name="bukuId">
                <input type="hidden" id="existingCover" name="existingCover">
                <input type="hidden" id="existingFile" name="existingFile">

                <div class="form-row">
                    <div class="form-group">
                        <label for="judul">Judul Buku*</label>
                        <input type="text" id="judul" name="judul" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="slug">Slug URL</label>
                        <input type="text" id="slug" name="slug" class="form-control" placeholder="Akan digenerate otomatis">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="penulis">Pengarang*</label>
                        <input type="text" id="penulis" name="penulis" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="penerbit">Penerbit</label>
                        <input type="text" id="penerbit" name="penerbit" class="form-control">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="tahun">Tahun Terbit</label>
                        <input type="number" id="tahun" name="tahun" class="form-control" min="1900" max="<?= date('Y') ?>">
                    </div>
                    <div class="form-group">
                        <label for="isbn">ISBN</label>
                        <input type="text" id="isbn" name="isbn" class="form-control">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="kategori">Kategori</label>
                        <select id="kategori" name="kategori" class="form-control">
                            <option value="">Pilih Kategori</option>
                            <?php
                            mysqli_data_seek($kategori_result, 0); // Reset pointer
                            while ($kategori = mysqli_fetch_assoc($kategori_result)) : ?>
                                <option value="<?= $kategori['KategoriID'] ?>"><?= $kategori['NamaKategori'] ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="bahasa">Bahasa*</label>
                        <input type="text" id="bahasa" name="bahasa" class="form-control" value="Indonesia" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="halaman">Jumlah Halaman</label>
                        <input type="number" id="halaman" name="halaman" class="form-control" min="1">
                    </div>
                    <div class="form-group">
                        <label for="format">Format E-book*</label>
                        <select id="format" name="format" class="form-control" required>
                            <option value="PDF">PDF</option>
                            <option value="EPUB">EPUB</option>
                            <option value="MOBI">MOBI</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="deskripsi">Deskripsi</label>
                    <textarea id="deskripsi" name="deskripsi" class="form-control" rows="3"></textarea>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="cover">Cover Buku</label>
                        <input type="file" id="cover" name="cover" class="form-control" accept="image/*">
                        <img id="coverPreview" class="cover-preview" src="" alt="Preview Cover">
                        <div class="file-info" id="currentCoverInfo"></div>
                    </div>
                    <div class="form-group">
                        <label for="fileEbook">File E-book (Link Google Drive)*</label>
                        <input type="url" id="fileEbook" name="fileEbook" class="form-control"
                            placeholder="https://drive.google.com/file/d/..." required>
                        <small class="text-muted">Masukkan link Google Drive untuk file PDF/EPUB/MOBI</small>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="jenisAkses">Jenis Akses*</label>
                        <select id="jenisAkses" name="jenisAkses" class="form-control" required>
                            <option value="Free">Free</option>
                            <option value="Premium">Premium</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="visibility">Visibilitas*</label>
                        <select id="visibility" name="visibility" class="form-control" required>
                            <option value="Public">Public</option>
                            <option value="Private">Private</option>
                            <option value="Draft">Draft</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="status">Status*</label>
                    <select id="status" name="status" class="form-control" required>
                        <option value="Published">Published</option>
                        <option value="Archived">Archived</option>
                        <option value="PendingReview">Pending Review</option>
                    </select>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-cancel close-modal">Batal</button>
                    <button type="submit" class="btn btn-primary" id="submitBtn">
                        <i class="fas fa-save"></i> Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal" id="deleteModal">
        <div class="modal-content" style="max-width: 400px;">
            <div class="modal-header">
                <h3 class="modal-title">Konfirmasi Hapus</h3>
                <button class="close-modal">&times;</button>
            </div>
            <div style="padding: 1.5rem;">
                <p>Apakah Anda yakin ingin menghapus buku ini?</p>
                <input type="hidden" id="deleteId">
                <div class="modal-footer">
                    <button type="button" class="btn btn-cancel close-modal">Batal</button>
                    <button type="button" class="btn btn-danger" id="confirmDelete">Hapus</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            console.log("DOM fully loaded and parsed");

            // Define BASE_URL globally
            const BASE_URL = '<?= BASE_URL ?>';

            // Get all necessary elements
            const addBookBtn = document.getElementById('addBookBtn');
            const bookModal = document.getElementById('bookModal');
            const deleteModal = document.getElementById('deleteModal');
            const closeModalBtns = document.querySelectorAll('.close-modal');
            const bookForm = document.getElementById('bookForm');
            const coverInput = document.getElementById('cover');
            const coverPreview = document.getElementById('coverPreview');
            const currentCoverInfo = document.getElementById('currentCoverInfo');
            const submitBtn = document.getElementById('submitBtn');
            const searchInput = document.getElementById('searchInput');
            const categoryFilter = document.getElementById('categoryFilter');
            const statusFilter = document.getElementById('statusFilter');
            const accessFilter = document.getElementById('accessFilter');
            const judulInput = document.getElementById('judul');
            const slugInput = document.getElementById('slug');
            const isbnInput = document.getElementById('isbn');

            // Debugging: Check if elements exist
            console.log({
                addBookBtn,
                bookModal,
                deleteModal,
                bookForm,
                coverInput,
                coverPreview,
                currentCoverInfo,
                submitBtn
            });

            // ======================
            // MODAL FUNCTIONALITY
            // ======================

            // Show add book modal
            if (addBookBtn && bookModal) {
                addBookBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    console.log("Add book button clicked");

                    // Set modal title
                    document.getElementById('modalTitle').textContent = 'Tambah Buku Baru';

                    // Reset form
                    if (bookForm) bookForm.reset();

                    // Clear cover preview
                    if (currentCoverInfo) currentCoverInfo.textContent = '';
                    if (coverPreview) {
                        coverPreview.style.display = 'none';
                        coverPreview.src = '';
                    }

                    // Clear hidden fields
                    document.getElementById('bukuId').value = '';
                    document.getElementById('existingCover').value = '';
                    document.getElementById('existingFile').value = '';

                    // Set default values
                    document.getElementById('bahasa').value = 'Indonesia';
                    document.getElementById('format').value = 'PDF';
                    document.getElementById('jenisAkses').value = 'Free';
                    document.getElementById('visibility').value = 'Public';
                    document.getElementById('status').value = 'Published';

                    // Clear error messages
                    document.querySelectorAll('.error-message').forEach(el => {
                        el.textContent = '';
                    });

                    // Show modal
                    bookModal.style.display = 'flex';
                    console.log("Modal should be visible now");
                });
            } else {
                console.error("Critical elements missing - addBookBtn or bookModal not found");
            }

            // Close modals
            if (closeModalBtns) {
                closeModalBtns.forEach(btn => {
                    btn.addEventListener('click', function() {
                        if (bookModal) bookModal.style.display = 'none';
                        if (deleteModal) deleteModal.style.display = 'none';
                    });
                });
            }

            // Close modal when clicking outside
            window.addEventListener('click', function(e) {
                if (e.target === bookModal) {
                    bookModal.style.display = 'none';
                }
                if (e.target === deleteModal) {
                    deleteModal.style.display = 'none';
                }
            });

            // ======================
            // FORM FUNCTIONALITY
            // ======================

            // Cover image preview
            if (coverInput && coverPreview) {
                coverInput.addEventListener('change', function() {
                    const file = this.files[0];
                    if (file) {
                        // Validate image file
                        const validTypes = ['image/jpeg', 'image/png', 'image/gif'];
                        if (!validTypes.includes(file.type)) {
                            alert('Format file harus JPG, PNG, atau GIF');
                            this.value = '';
                            return;
                        }

                        // Validate file size (max 2MB)
                        if (file.size > 2 * 1024 * 1024) {
                            alert('Ukuran file maksimal 2MB');
                            this.value = '';
                            return;
                        }

                        const reader = new FileReader();
                        reader.onload = function(e) {
                            coverPreview.src = e.target.result;
                            coverPreview.style.display = 'block';
                            if (currentCoverInfo) {
                                currentCoverInfo.textContent = `File: ${file.name} (${(file.size / 1024).toFixed(2)} KB)`;
                            }
                        }
                        reader.readAsDataURL(file);
                    }
                });
            }

            // Auto-generate slug from title
            if (judulInput && slugInput) {
                judulInput.addEventListener('input', function() {
                    const title = this.value;
                    if (!slugInput.value) {
                        const slug = title.toLowerCase()
                            .replace(/[^\w\s-]/g, '')
                            .replace(/[\s_-]+/g, '-')
                            .replace(/^-+|-+$/g, '');
                        slugInput.value = slug;
                    }
                });
            }

            // ISBN format validation
            if (isbnInput) {
                isbnInput.addEventListener('input', function() {
                    // Allow only numbers and hyphens
                    this.value = this.value.replace(/[^0-9-]/g, '');

                    // Auto-format ISBN if typing numbers only
                    if (this.value.length === 13 && !this.value.includes('-')) {
                        this.value = `${this.value.substring(0, 3)}-${this.value.substring(3, 8)}-${this.value.substring(8, 12)}-${this.value.substring(12)}`;
                    }
                });
            }

            // Form submission
            if (bookForm) {
                bookForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    console.log("Form submission started");

                    // Validate form
                    let isValid = true;
                    const requiredFields = [
                        'judul', 'penulis', 'bahasa', 'format', 'fileEbook'
                    ];

                    // Clear previous errors
                    document.querySelectorAll('.error-message').forEach(el => {
                        el.textContent = '';
                    });

                    // Validate required fields
                    requiredFields.forEach(field => {
                        const element = document.getElementById(field);
                        if (element && !element.value.trim()) {
                            const errorElement = document.getElementById(`${field}-error`) || element.nextElementSibling;
                            if (errorElement) {
                                errorElement.textContent = 'Field ini wajib diisi';
                            }
                            isValid = false;
                        }
                    });

                    // Validate ISBN format if provided
                    const isbnValue = isbnInput.value.trim();
                    if (isbnValue && !/^(\d{3}-)?\d{1,5}-\d{1,7}-\d{1,7}-\d{1}$|^\d{9}[\dX]$/i.test(isbnValue)) {
                        alert('Format ISBN tidak valid. Gunakan format seperti 978-602-06-3724-2');
                        isValid = false;
                    }

                    if (!isValid) {
                        alert('Harap isi semua field yang wajib diisi dan pastikan format valid');
                        return;
                    }

                    // Show loading state
                    const originalBtnText = submitBtn.innerHTML;
                    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memproses...';
                    submitBtn.disabled = true;

                    const formData = new FormData(this);
                    const bukuId = document.getElementById('bukuId').value;
                    const action = bukuId ? 'update' : 'add';

                    fetch('process_book.php?action=' + action, {
                            method: 'POST',
                            body: formData
                        })
                        .then(response => {
                            if (!response.ok) {
                                throw new Error('Network response was not ok');
                            }
                            return response.json();
                        })
                        .then(data => {
                            if (data.success) {
                                alert(data.message);
                                bookModal.style.display = 'none';
                                window.location.reload();
                            } else {
                                throw new Error(data.message || 'Terjadi kesalahan saat menyimpan data');
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            alert('Error: ' + error.message);
                        })
                        .finally(() => {
                            submitBtn.innerHTML = originalBtnText;
                            submitBtn.disabled = false;
                        });
                });
            }

            // ======================
            // BOOK MANAGEMENT
            // ======================

            // Edit book function
            window.editBook = function(id) {
                console.log("Editing book ID:", id);

                // Show loading state
                const originalBtnText = submitBtn.innerHTML;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memuat...';
                submitBtn.disabled = true;

                // Clear previous data
                coverPreview.style.display = 'none';
                coverPreview.src = '';
                currentCoverInfo.textContent = '';

                fetch('get_book.php?id=' + id)
                    .then(response => {
                        // First check for HTTP errors
                        if (!response.ok) {
                            return response.text().then(text => {
                                throw new Error(`HTTP error! status: ${response.status}, response: ${text}`);
                            });
                        }

                        // Then check if response is JSON
                        const contentType = response.headers.get('content-type');
                        if (!contentType || !contentType.includes('application/json')) {
                            return response.text().then(text => {
                                throw new Error(`Expected JSON but got: ${text.substring(0, 100)}`);
                            });
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (!data.success) {
                            throw new Error(data.message || 'Gagal memuat data buku');
                        }

                        const book = data.data;
                        console.log("Book data:", book);

                        // Fill form
                        document.getElementById('modalTitle').textContent = 'Edit Buku';
                        document.getElementById('bukuId').value = book.BukuID;
                        document.getElementById('judul').value = book.Judul || '';
                        document.getElementById('slug').value = book.Slug || '';
                        document.getElementById('penulis').value = book.Penulis || '';
                        document.getElementById('penerbit').value = book.Penerbit || '';
                        document.getElementById('tahun').value = book.TahunTerbit || '';
                        document.getElementById('isbn').value = book.ISBN || ''; // Handle null ISBN
                        document.getElementById('kategori').value = book.KategoriID || '';
                        document.getElementById('bahasa').value = book.Bahasa || 'Indonesia';
                        document.getElementById('halaman').value = book.JumlahHalaman || '';
                        document.getElementById('deskripsi').value = book.Deskripsi || '';
                        document.getElementById('format').value = book.FormatEbook || 'PDF';
                        document.getElementById('fileEbook').value = book.FileEbook || '';
                        document.getElementById('jenisAkses').value = book.JenisAkses || 'Free';
                        document.getElementById('visibility').value = book.Visibility || 'Public';
                        document.getElementById('status').value = book.Status || 'Published';
                        document.getElementById('existingCover').value = book.Cover || '';
                        document.getElementById('existingFile').value = book.FileEbook || '';

                        // Handle cover image
                        if (book.Cover) {
                            // Ensure proper URL
                            let coverUrl = book.Cover;
                            if (!coverUrl.startsWith('http') && !coverUrl.startsWith('/')) {
                                coverUrl = BASE_URL + coverUrl;
                            }

                            coverPreview.src = coverUrl;
                            coverPreview.style.display = 'block';
                            currentCoverInfo.textContent = 'Current cover';

                            coverPreview.onerror = function() {
                                this.src = 'https://via.placeholder.com/150x200?text=Cover+Error';
                                console.error('Failed to load cover:', coverUrl);
                            };
                        }

                        bookModal.style.display = 'flex';
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Gagal memuat data buku: ' + error.message);
                    })
                    .finally(() => {
                        submitBtn.innerHTML = originalBtnText;
                        submitBtn.disabled = false;
                    });
            };

            // Delete confirmation
            window.confirmDelete = function(id) {
                console.log("Confirm delete for ID:", id);
                document.getElementById('deleteId').value = id;
                deleteModal.style.display = 'flex';
            };

            // Delete book
            if (document.getElementById('confirmDelete')) {
                document.getElementById('confirmDelete').addEventListener('click', function() {
                    const id = document.getElementById('deleteId').value;
                    const deleteBtn = this;
                    const originalBtnText = deleteBtn.innerHTML;

                    deleteBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menghapus...';
                    deleteBtn.disabled = true;

                    fetch('process_book.php?action=delete', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded',
                            },
                            body: 'id=' + id
                        })
                        .then(response => {
                            if (!response.ok) {
                                throw new Error('Network response was not ok');
                            }
                            return response.json();
                        })
                        .then(data => {
                            if (data.success) {
                                alert(data.message);
                                deleteModal.style.display = 'none';
                                window.location.reload();
                            } else {
                                throw new Error(data.message || 'Gagal menghapus buku');
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            alert('Error: ' + error.message);
                        })
                        .finally(() => {
                            deleteBtn.innerHTML = originalBtnText;
                            deleteBtn.disabled = false;
                        });
                });
            }

            // ======================
            // SEARCH & FILTER
            // ======================

            // Search functionality
            if (searchInput) {
                searchInput.addEventListener('keyup', function() {
                    const searchTerm = this.value.toLowerCase();
                    const rows = document.querySelectorAll('tbody tr');

                    rows.forEach(row => {
                        const title = row.querySelector('td:nth-child(3)').textContent.toLowerCase();
                        const author = row.querySelector('td:nth-child(4)').textContent.toLowerCase();
                        if (title.includes(searchTerm) || author.includes(searchTerm)) {
                            row.style.display = '';
                        } else {
                            row.style.display = 'none';
                        }
                    });
                });
            }

            // Filter by category
            if (categoryFilter) {
                categoryFilter.addEventListener('change', function() {
                    const categoryId = this.value;
                    const rows = document.querySelectorAll('tbody tr');

                    rows.forEach(row => {
                        if (categoryId === '') {
                            row.style.display = '';
                        } else {
                            const rowCategory = row.querySelector('td:nth-child(5)').textContent;
                            if (rowCategory === this.options[this.selectedIndex].text) {
                                row.style.display = '';
                            } else {
                                row.style.display = 'none';
                            }
                        }
                    });
                });
            }

            // Filter by status
            if (statusFilter) {
                statusFilter.addEventListener('change', function() {
                    const status = this.value;
                    const rows = document.querySelectorAll('tbody tr');

                    rows.forEach(row => {
                        if (status === '') {
                            row.style.display = '';
                        } else {
                            const rowStatus = row.querySelector('td:nth-child(7) span').textContent.toLowerCase();
                            if (rowStatus === status.toLowerCase()) {
                                row.style.display = '';
                            } else {
                                row.style.display = 'none';
                            }
                        }
                    });
                });
            }

            // Filter by access type
            if (accessFilter) {
                accessFilter.addEventListener('change', function() {
                    const accessType = this.value;
                    const rows = document.querySelectorAll('tbody tr');

                    rows.forEach(row => {
                        if (accessType === '') {
                            row.style.display = '';
                        } else {
                            const rowAccess = row.querySelector('td:nth-child(6) span').textContent;
                            if (rowAccess === accessType) {
                                row.style.display = '';
                            } else {
                                row.style.display = 'none';
                            }
                        }
                    });
                });
            }
        });
    </script>
</body>

</html>