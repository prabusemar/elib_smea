<?php
session_start();

// Check if user is logged in and is admin
if (!isset($_SESSION['username'])) {
    header("Location: ../../auth/login.php");
    exit;
}

// Database connection
include '../../config.php';

// Query untuk mendapatkan data buku
$query = "SELECT b.*, k.NamaKategori 
          FROM buku b 
          LEFT JOIN kategori k ON b.KategoriID = k.KategoriID
          ORDER BY b.TanggalUpload DESC";
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
    </style>
</head>

<body>
    <!-- Include your sidebar navigation -->
    <?php include '../../views/sidebar_admin.php'; ?>

    <!-- Main Content -->
    <main class="main-content">
        <div class="header">
            <h1>Manajemen Buku</h1>
        </div>

        <div class="content-container">
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
                        <option value="">Status</option>
                        <option value="tersedia">Tersedia</option>
                        <option value="dipinjam">Dipinjam</option>
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
                                <th>Penerbit</th>
                                <th>Tahun</th>
                                <th>Kategori</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $no = 1; ?>
                            <?php while ($buku = mysqli_fetch_assoc($result)) : ?>
                                <tr>
                                    <td><?= $no++ ?></td>
                                    <td>
                                        <?php if (!empty($buku['Cover'])) : ?>
                                            <img src="<?= $buku['Cover'] ?>" alt="Cover" style="max-width: 50px;">
                                        <?php else : ?>
                                            <i class="fas fa-book" style="font-size: 24px;"></i>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($buku['Judul']) ?></td>
                                    <td><?= htmlspecialchars($buku['Penulis']) ?></td>
                                    <td><?= htmlspecialchars($buku['Penerbit']) ?></td>
                                    <td><?= $buku['TahunTerbit'] ?></td>
                                    <td><?= $buku['NamaKategori'] ?? 'Tidak Berkategori' ?></td>
                                    <td>
                                        <span class="badge <?= $buku['Status'] == 'Tersedia' ? 'badge-success' : 'badge-warning' ?>">
                                            <?= $buku['Status'] ?>
                                        </span>
                                    </td>
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
                <div class="form-row" style="display: flex; gap: 1rem;">
                    <div class="form-group" style="flex: 1;">
                        <label for="judul">Judul Buku*</label>
                        <input type="text" id="judul" name="judul" class="form-control" required>
                    </div>
                    <div class="form-group" style="flex: 1;">
                        <label for="isbn">ISBN*</label>
                        <input type="text" id="isbn" name="isbn" class="form-control" required>
                    </div>
                </div>

                <div class="form-row" style="display: flex; gap: 1rem;">
                    <div class="form-group" style="flex: 1;">
                        <label for="penulis">Pengarang*</label>
                        <input type="text" id="penulis" name="penulis" class="form-control" required>
                    </div>
                    <div class="form-group" style="flex: 1;">
                        <label for="penerbit">Penerbit*</label>
                        <input type="text" id="penerbit" name="penerbit" class="form-control" required>
                    </div>
                </div>

                <div class="form-row" style="display: flex; gap: 1rem;">
                    <div class="form-group" style="flex: 1;">
                        <label for="tahun">Tahun Terbit*</label>
                        <input type="number" id="tahun" name="tahun" class="form-control" required>
                    </div>
                    <div class="form-group" style="flex: 1;">
                        <label for="kategori">Kategori*</label>
                        <select id="kategori" name="kategori" class="form-control" required>
                            <option value="">Pilih Kategori</option>
                            <?php
                            mysqli_data_seek($kategori_result, 0); // Reset pointer
                            while ($kategori = mysqli_fetch_assoc($kategori_result)) : ?>
                                <option value="<?= $kategori['KategoriID'] ?>"><?= $kategori['NamaKategori'] ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </div>

                <div class="form-row" style="display: flex; gap: 1rem;">
                    <div class="form-group" style="flex: 1;">
                        <label for="bahasa">Bahasa*</label>
                        <input type="text" id="bahasa" name="bahasa" class="form-control" required>
                    </div>
                    <div class="form-group" style="flex: 1;">
                        <label for="halaman">Jumlah Halaman*</label>
                        <input type="number" id="halaman" name="halaman" class="form-control" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="deskripsi">Deskripsi</label>
                    <textarea id="deskripsi" name="deskripsi" class="form-control" rows="3"></textarea>
                </div>

                <div class="form-row" style="display: flex; gap: 1rem;">
                    <div class="form-group" style="flex: 1;">
                        <label for="cover">Cover Buku</label>
                        <input type="file" id="cover" name="cover" class="form-control" accept="image/*">
                        <img id="coverPreview" class="cover-preview" src="" alt="Preview Cover">
                        <div class="file-info" id="currentCoverInfo"></div>
                    </div>
                    <div class="form-group" style="flex: 1;">
                        <label for="fileEbook">File E-Book*</label>
                        <input type="file" id="fileEbook" name="fileEbook" class="form-control" accept=".pdf,.epub,.mobi">
                        <div class="file-info" id="currentFileInfo"></div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="status">Status*</label>
                    <select id="status" name="status" class="form-control" required>
                        <option value="Tersedia">Tersedia</option>
                        <option value="Dipinjam">Dipinjam</option>
                    </select>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-cancel close-modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
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
        // Modal functionality
        const addBookBtn = document.getElementById('addBookBtn');
        const bookModal = document.getElementById('bookModal');
        const deleteModal = document.getElementById('deleteModal');
        const closeModalBtns = document.querySelectorAll('.close-modal');
        const bookForm = document.getElementById('bookForm');
        const coverInput = document.getElementById('cover');
        const coverPreview = document.getElementById('coverPreview');
        const currentCoverInfo = document.getElementById('currentCoverInfo');
        const currentFileInfo = document.getElementById('currentFileInfo');

        // Show add book modal
        addBookBtn.addEventListener('click', () => {
            document.getElementById('modalTitle').textContent = 'Tambah Buku Baru';
            bookForm.reset();
            currentCoverInfo.textContent = '';
            currentFileInfo.textContent = '';
            coverPreview.style.display = 'none';
            bookModal.style.display = 'flex';
        });

        // Close modals
        closeModalBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                bookModal.style.display = 'none';
                deleteModal.style.display = 'none';
            });
        });

        // Close modal when clicking outside
        window.addEventListener('click', (e) => {
            if (e.target === bookModal) {
                bookModal.style.display = 'none';
            }
            if (e.target === deleteModal) {
                deleteModal.style.display = 'none';
            }
        });

        // Cover image preview
        coverInput.addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    coverPreview.src = e.target.result;
                    coverPreview.style.display = 'block';
                    currentCoverInfo.textContent = `File: ${file.name}`;
                }
                reader.readAsDataURL(file);
            }
        });

        // Form submission
        bookForm.addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            const bukuId = document.getElementById('bukuId').value;
            const action = bukuId ? 'update' : 'add';

            fetch('process_book.php?action=' + action, {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(data.message);
                        bookModal.style.display = 'none';
                        window.location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Terjadi kesalahan saat menyimpan data');
                });
        });

        // Edit book function
        function editBook(id) {
            fetch('get_book.php?id=' + id)
                .then(response => response.json())
                .then(data => {
                    if (data) {
                        document.getElementById('modalTitle').textContent = 'Edit Buku';
                        document.getElementById('bukuId').value = data.BukuID;
                        document.getElementById('judul').value = data.Judul;
                        document.getElementById('isbn').value = data.ISBN;
                        document.getElementById('penulis').value = data.Penulis;
                        document.getElementById('penerbit').value = data.Penerbit;
                        document.getElementById('tahun').value = data.TahunTerbit;
                        document.getElementById('kategori').value = data.KategoriID;
                        document.getElementById('bahasa').value = data.Bahasa;
                        document.getElementById('halaman').value = data.JumlahHalaman;
                        document.getElementById('deskripsi').value = data.Deskripsi;
                        document.getElementById('status').value = data.Status;

                        // Show current cover info if exists
                        if (data.Cover) {
                            currentCoverInfo.textContent = `Current: ${data.Cover.split('/').pop()}`;
                            coverPreview.src = data.Cover;
                            coverPreview.style.display = 'block';
                        }

                        // Show current file info if exists
                        if (data.FileEbook) {
                            currentFileInfo.textContent = `Current: ${data.FileEbook.split('/').pop()}`;
                        }

                        bookModal.style.display = 'flex';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Gagal memuat data buku');
                });
        }

        // Delete confirmation
        function confirmDelete(id) {
            document.getElementById('deleteId').value = id;
            deleteModal.style.display = 'flex';
        }

        // Delete book
        document.getElementById('confirmDelete').addEventListener('click', function() {
            const id = document.getElementById('deleteId').value;

            fetch('process_book.php?action=delete', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'id=' + id
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(data.message);
                        deleteModal.style.display = 'none';
                        window.location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Terjadi kesalahan saat menghapus data');
                });
        });

        // Search functionality
        document.getElementById('searchInput').addEventListener('keyup', function() {
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

        // Filter by category
        document.getElementById('categoryFilter').addEventListener('change', function() {
            const categoryId = this.value;
            const rows = document.querySelectorAll('tbody tr');

            rows.forEach(row => {
                if (categoryId === '') {
                    row.style.display = '';
                } else {
                    const rowCategory = row.querySelector('td:nth-child(7)').textContent;
                    if (rowCategory === this.options[this.selectedIndex].text) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                }
            });
        });
    </script>
</body>

</html>