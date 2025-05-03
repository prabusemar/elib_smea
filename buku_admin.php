<?php
session_start();

// Check if user is logged in and is admin
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

// Database connection
include 'config.php';
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
    </style>
</head>

<body>
    <!-- Include your sidebar navigation -->
    <?php include 'sidebar_admin.php'; ?>

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
                        <input type="text" placeholder="Cari buku...">
                    </div>
                    <select class="filter-select">
                        <option value="">Semua Kategori</option>
                        <option value="fiksi">Fiksi</option>
                        <option value="non-fiksi">Non-Fiksi</option>
                        <option value="pelajaran">Pelajaran</option>
                    </select>
                    <select class="filter-select">
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
                            <tr>
                                <td>1</td>
                                <td>Pemrograman Web Modern</td>
                                <td>John Doe</td>
                                <td>Penerbit Informatika</td>
                                <td>2023</td>
                                <td>Teknologi</td>
                                <td><span class="badge badge-success">Tersedia</span></td>
                                <td>
                                    <div class="action-btns">
                                        <div class="btn-icon btn-edit" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </div>
                                        <div class="btn-icon btn-delete" title="Hapus">
                                            <i class="fas fa-trash"></i>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <!-- More rows would be generated from PHP -->
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
    <div class="modal" id="addBookModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Tambah Buku Baru</h3>
                <button class="close-modal">&times;</button>
            </div>
            <form id="bookForm">
                <div class="form-group">
                    <label for="judul">Judul Buku</label>
                    <input type="text" id="judul" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="pengarang">Pengarang</label>
                    <input type="text" id="pengarang" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="penerbit">Penerbit</label>
                    <input type="text" id="penerbit" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="tahun">Tahun Terbit</label>
                    <input type="number" id="tahun" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="isbn">ISBN</label>
                    <input type="text" id="isbn" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="kategori">Kategori</label>
                    <select id="kategori" class="form-control" required>
                        <option value="">Pilih Kategori</option>
                        <option value="fiksi">Fiksi</option>
                        <option value="non-fiksi">Non-Fiksi</option>
                        <option value="pelajaran">Pelajaran</option>
                        <option value="teknologi">Teknologi</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="deskripsi">Deskripsi</label>
                    <textarea id="deskripsi" class="form-control" rows="3"></textarea>
                </div>
                <div class="form-group">
                    <label for="cover">Cover Buku</label>
                    <input type="file" id="cover" class="form-control">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-cancel close-modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Modal functionality
        const addBookBtn = document.getElementById('addBookBtn');
        const addBookModal = document.getElementById('addBookModal');
        const closeModalBtns = document.querySelectorAll('.close-modal');

        addBookBtn.addEventListener('click', () => {
            addBookModal.style.display = 'flex';
        });

        closeModalBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                addBookModal.style.display = 'none';
            });
        });

        // Close modal when clicking outside
        window.addEventListener('click', (e) => {
            if (e.target === addBookModal) {
                addBookModal.style.display = 'none';
            }
        });

        // Form submission
        document.getElementById('bookForm').addEventListener('submit', (e) => {
            e.preventDefault();
            // Here you would handle form submission via AJAX or PHP
            alert('Buku berhasil ditambahkan!');
            addBookModal.style.display = 'none';
            // Reset form
            e.target.reset();
        });
    </script>
</body>

</html>