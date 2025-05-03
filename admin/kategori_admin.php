<?php
session_start();
require_once '../config.php';

// Cek role admin
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Tambah Kategori
    if (isset($_POST['add_category'])) {
        $nama = mysqli_real_escape_string($conn, trim($_POST['nama_kategori']));
        $deskripsi = mysqli_real_escape_string($conn, trim($_POST['deskripsi']));

        // Handle file upload
        $icon = '';
        if (isset($_FILES['icon']) && $_FILES['icon']['error'] == 0) {
            $target_dir = "assets/icon/";
            $file_extension = pathinfo($_FILES["icon"]["name"], PATHINFO_EXTENSION);
            $new_filename = uniqid() . '.' . $file_extension;
            $target_file = $target_dir . $new_filename;

            if (move_uploaded_file($_FILES["icon"]["tmp_name"], $target_file)) {
                $icon = $new_filename;
            }
        }

        if (empty($nama) || empty($icon)) {
            $_SESSION['error'] = "Nama Kategori dan Icon wajib diisi!";
        } else {
            $query = "INSERT INTO kategori (NamaKategori, Deskripsi, Icon) VALUES (?, ?, ?)";
            $stmt = mysqli_prepare($conn, $query);
            mysqli_stmt_bind_param($stmt, "sss", $nama, $deskripsi, $icon);
            mysqli_stmt_execute($stmt);
            $_SESSION['success'] = "Kategori berhasil ditambahkan!";
            mysqli_stmt_close($stmt);
        }
    }
    // Update Kategori
    elseif (isset($_POST['update_category'])) {
        $id = (int)$_POST['kategori_id'];
        $nama = mysqli_real_escape_string($conn, trim($_POST['nama_kategori']));
        $deskripsi = mysqli_real_escape_string($conn, trim($_POST['deskripsi']));

        // Handle file upload jika ada file baru
        $icon = $_POST['existing_icon']; // Default ke icon yang sudah ada
        if (isset($_FILES['icon']) && $_FILES['icon']['error'] == 0) {
            $target_dir = "assets/icon/";
            $file_extension = pathinfo($_FILES["icon"]["name"], PATHINFO_EXTENSION);
            $new_filename = uniqid() . '.' . $file_extension;
            $target_file = $target_dir . $new_filename;

            if (move_uploaded_file($_FILES["icon"]["tmp_name"], $target_file)) {
                // Hapus file lama jika ada
                if (!empty($_POST['existing_icon'])) {
                    $old_file = $target_dir . $_POST['existing_icon'];
                    if (file_exists($old_file)) {
                        unlink($old_file);
                    }
                }
                $icon = $new_filename;
            }
        }

        $query = "UPDATE kategori SET NamaKategori=?, Deskripsi=?, Icon=? WHERE KategoriID=?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "sssi", $nama, $deskripsi, $icon, $id);
        mysqli_stmt_execute($stmt);
        $_SESSION['success'] = "Kategori berhasil diperbarui!";
        mysqli_stmt_close($stmt);
    }
    // Hapus Kategori
    elseif (isset($_POST['delete_category'])) {
        $id = (int)$_POST['kategori_id'];

        // Dapatkan nama file icon untuk dihapus
        $query = "SELECT Icon FROM kategori WHERE KategoriID=?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);

        if ($row && !empty($row['Icon'])) {
            $icon_file = "assets/icon/" . $row['Icon'];
            if (file_exists($icon_file)) {
                unlink($icon_file);
            }
        }

        $query = "DELETE FROM kategori WHERE KategoriID=?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        $_SESSION['success'] = "Kategori berhasil dihapus!";
        mysqli_stmt_close($stmt);
    }

    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Ambil semua kategori dari database
$query = "SELECT * FROM kategori ORDER BY NamaKategori";
$categories = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Kategori</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        :root {
            --primary: #3a0ca3;
            --primary-light: #4361ee;
            --secondary: #f72585;
            --light: #f8f9fa;
            --dark: #212529;
            --gray: #6c757d;
            --sidebar-width: 250px;
            --sidebar-collapsed-width: 80px;
            --header-height: 70px;
        }

        /* Base Styles */
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f5f7fa;
            color: var(--dark);
        }

        /* Layout Structure */
        .main-content {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            transition: all 0.3s ease;
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

        .content-container {
            padding: 2rem;
        }

        /* Cards & Containers */
        .card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            padding: 1.5rem;
            margin-bottom: 2rem;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 15px rgba(0, 0, 0, 0.1);
        }

        /* Tables */
        .table-container {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
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
        }

        /* Buttons */
        .btn {
            padding: 0.7rem 1.5rem;
            border-radius: 6px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            border: none;
            font-size: 0.9rem;
        }

        .btn-primary {
            background-color: var(--primary);
            color: white;
        }

        .btn-primary:hover {
            background-color: #2e0a8a;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
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

        .btn-delete {
            background-color: #fee2e2;
            color: #b91c1c;
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
            max-width: 500px;
            padding: 2rem;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            animation: modalFadeIn 0.3s ease;
        }

        @keyframes modalFadeIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
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
            margin: 0;
        }

        .close-modal {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: var(--gray);
            transition: color 0.2s ease;
        }

        .close-modal:hover {
            color: var(--dark);
        }

        /* Form Elements */
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
            transition: all 0.3s ease;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2);
        }

        /* Icon Selection */
        .icon-preview {
            font-size: 1.5rem;
            margin-left: 10px;
            vertical-align: middle;
            color: var(--primary);
        }

        .icon-select {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 10px;
        }

        .icon-option {
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 4px;
            cursor: pointer;
            border: 1px solid #e2e8f0;
            transition: all 0.2s ease;
            background-color: white;
        }

        .icon-option:hover,
        .icon-option.selected {
            background-color: var(--primary);
            color: white;
            border-color: var(--primary);
        }

        /* Alerts */
        .alert {
            padding: 1rem;
            border-radius: 6px;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border-left: 4px solid #28a745;
        }

        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            border-left: 4px solid #dc3545;
        }

        /* Utility Classes */
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        .page-title {
            color: var(--primary);
            font-size: 1.8rem;
            font-weight: 600;
            margin: 0;
        }

        .action-btns {
            display: flex;
            gap: 0.5rem;
        }

        .text-muted {
            color: var(--gray);
            font-size: 0.85rem;
        }

        /* Responsive Design */
        @media (max-width: 992px) {
            .main-content {
                margin-left: var(--sidebar-collapsed-width);
            }

            .header {
                padding: 0 1rem;
            }

            .content-container {
                padding: 1.5rem;
            }
        }

        @media (max-width: 768px) {
            .page-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }

            .btn {
                padding: 0.6rem 1rem;
                font-size: 0.85rem;
            }

            th,
            td {
                padding: 0.75rem;
            }

            .modal-content {
                padding: 1.5rem;
                width: 95%;
            }
        }

        @media (max-width: 576px) {
            .content-container {
                padding: 1rem;
            }

            .card {
                padding: 1rem;
            }

            .modal-content {
                padding: 1rem;
            }
        }
    </style>
</head>

<body>
    <?php include '../views/sidebar_admin.php'; ?>

    <main class="main-content">
        <div class="header">
            <h1>Manajemen Kategori</h1>
        </div>

        <div class="content-container">
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <?= $_SESSION['success'];
                    unset($_SESSION['success']); ?>
                </div>
            <?php endif; ?>
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <?= $_SESSION['error'];
                    unset($_SESSION['error']); ?>
                </div>
            <?php endif; ?>

            <div class="page-header">
                <h2 class="page-title">Daftar Kategori Buku</h2>
                <button class="btn btn-primary" id="addCategoryBtn">
                    <i class="fas fa-plus"></i> Tambah Kategori
                </button>
            </div>

            <div class="card">
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama Kategori</th>
                                <th>Deskripsi</th>
                                <th>Icon</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $no = 1;
                            while ($row = mysqli_fetch_assoc($categories)): ?>
                                <tr>
                                    <td><?= $no++ ?></td>
                                    <td><?= htmlspecialchars($row['NamaKategori']) ?></td>
                                    <td><?= htmlspecialchars($row['Deskripsi']) ?></td>
                                    <td>
                                        <?php if (!empty($row['Icon'])): ?>
                                            <img src="assets/icon/<?= htmlspecialchars($row['Icon']) ?>" alt="Icon" style="width: 24px; height: 24px;">
                                        <?php else: ?>
                                            <i class="fas fa-image"></i>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="action-btns">
                                            <button class="btn-icon btn-edit edit-category"
                                                data-id="<?= $row['KategoriID'] ?>"
                                                data-nama="<?= htmlspecialchars($row['NamaKategori']) ?>"
                                                data-deskripsi="<?= htmlspecialchars($row['Deskripsi']) ?>"
                                                data-icon="<?= htmlspecialchars($row['Icon']) ?>">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn-icon btn-delete delete-category"
                                                data-id="<?= $row['KategoriID'] ?>">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>

    <!-- Modal untuk Tambah/Edit Kategori -->
    <div class="modal" id="categoryModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title" id="modalTitle">Tambah Kategori Baru</h3>
                <button class="close-modal">&times;</button>
            </div>
            <form id="categoryForm" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="kategori_id" id="kategori_id">
                <input type="hidden" name="add_category" id="formAction">
                <input type="hidden" name="existing_icon" id="existing_icon">

                <div class="form-group">
                    <label for="nama_kategori">Nama Kategori *</label>
                    <input type="text" id="nama_kategori" name="nama_kategori" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="deskripsi">Deskripsi</label>
                    <textarea id="deskripsi" name="deskripsi" class="form-control" rows="3"></textarea>
                </div>

                <div class="form-group">
                    <label for="icon">Icon *</label>
                    <div id="iconPreviewContainer" style="margin-bottom: 10px;">
                        <img id="iconPreview" src="" alt="Preview Icon" style="max-width: 100px; max-height: 100px; display: none;">
                    </div>
                    <input type="file" id="icon" name="icon" class="form-control" accept="image/*">
                    <small class="text-muted">Format: JPG, PNG, SVG. Maksimal 1MB.</small>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-cancel close-modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Form untuk Delete -->
    <form id="deleteForm" method="POST" style="display: none;">
        <input type="hidden" name="kategori_id" id="delete_kategori_id">
        <input type="hidden" name="delete_category" value="1">
    </form>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Modal elements
            const modal = document.getElementById('categoryModal');
            const addBtn = document.getElementById('addCategoryBtn');
            const closeBtns = document.querySelectorAll('.close-modal, .btn-cancel');

            // Form elements
            const categoryForm = document.getElementById('categoryForm');
            const formAction = document.getElementById('formAction');
            const iconInput = document.getElementById('icon');
            const iconPreview = document.getElementById('iconPreview');
            const existingIconInput = document.getElementById('existing_icon');

            // Delete form
            const deleteForm = document.getElementById('deleteForm');

            // Show modal for add
            addBtn.addEventListener('click', () => {
                document.getElementById('modalTitle').textContent = 'Tambah Kategori Baru';
                formAction.name = 'add_category';
                categoryForm.reset();
                document.getElementById('kategori_id').value = '';
                existingIconInput.value = '';
                iconPreview.style.display = 'none';
                modal.style.display = 'flex';
            });

            // Show modal for edit
            document.querySelectorAll('.edit-category').forEach(btn => {
                btn.addEventListener('click', function() {
                    document.getElementById('modalTitle').textContent = 'Edit Kategori';
                    formAction.name = 'update_category';
                    document.getElementById('kategori_id').value = this.dataset.id;
                    document.getElementById('nama_kategori').value = this.dataset.nama;
                    document.getElementById('deskripsi').value = this.dataset.deskripsi;

                    // Handle icon preview
                    if (this.dataset.icon) {
                        existingIconInput.value = this.dataset.icon;
                        iconPreview.src = 'assets/icon/' + this.dataset.icon;
                        iconPreview.style.display = 'block';
                    } else {
                        existingIconInput.value = '';
                        iconPreview.style.display = 'none';
                    }

                    modal.style.display = 'flex';
                });
            });

            // Close modal
            closeBtns.forEach(btn => {
                btn.addEventListener('click', () => {
                    modal.style.display = 'none';
                });
            });

            // Preview image when file selected
            iconInput.addEventListener('change', function() {
                const file = this.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        iconPreview.src = e.target.result;
                        iconPreview.style.display = 'block';
                    }
                    reader.readAsDataURL(file);
                }
            });

            // Delete confirmation
            document.querySelectorAll('.delete-category').forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    if (confirm('Apakah Anda yakin ingin menghapus kategori ini?')) {
                        document.getElementById('delete_kategori_id').value = this.dataset.id;
                        deleteForm.submit();
                    }
                });
            });

            // Form validation
            categoryForm.addEventListener('submit', function(e) {
                if (!document.getElementById('nama_kategori').value.trim() ||
                    (!document.getElementById('icon').files[0] && !existingIconInput.value)) {
                    e.preventDefault();
                    alert('Nama Kategori dan Icon wajib diisi!');
                }
            });
        });
    </script>
</body>

</html>