<?php
session_start();
require_once __DIR__ . '/../../config.php';

// Check admin role
if (!isset($_SESSION['username'])) {
    header("Location: ../../auth/login.php");
    exit;
}

if ($_SESSION['role'] != 'admin') {
    header("Location: ../../index.php");
    exit;
}

// Handle form submissions
require_once 'kategori_handler.php';

// Query to get categories with search filter
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$query = "SELECT KategoriID, NamaKategori, Deskripsi, Icon FROM kategori";

if (!empty($search)) {
    $query .= " WHERE NamaKategori LIKE '%" . mysqli_real_escape_string($conn, $search) . "%' 
                OR Deskripsi LIKE '%" . mysqli_real_escape_string($conn, $search) . "%'";
}

$query .= " ORDER BY NamaKategori";
$categories = mysqli_query($conn, $query);

$page_title = "Manajemen Kategori - Perpustakaan Digital";
require_once '../../views/header.php';
?>

<div class="header">
    <h1>Manajemen Kategori</h1>
</div>

<div class="content-container">
    <?php include '../../views/alert_messages.php'; ?>

    <div class="page-header">
        <h2 class="page-title">Daftar Kategori Buku</h2>
        <button class="btn btn-primary" id="addCategoryBtn">
            <i class="fas fa-plus"></i> Tambah Kategori
        </button>
    </div>

    <div class="card">
        <div class="search-container">
            <form method="GET" action="" class="search-form">
                <div class="input-group">
                    <input type="text" name="search" class="form-control"
                        placeholder="Cari kategori..."
                        value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">
                    <button type="submit" class="btn btn-search">
                        <i class="fas fa-search"></i>
                    </button>
                    <?php if (isset($_GET['search']) && !empty($_GET['search'])): ?>
                        <a href="kategori_admin.php" class="btn btn-reset">
                            <i class="fas fa-times"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <!-- Desktop Table View -->
        <div class="table-view">
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
                        <tr data-id="<?= $row['KategoriID'] ?>">
                            <td><?= $no++ ?></td>
                            <td><?= htmlspecialchars($row['NamaKategori']) ?></td>
                            <td><?= htmlspecialchars($row['Deskripsi']) ?></td>
                            <td>
                                <?php if (!empty($row['Icon'])): ?>
                                    <img src="<?= BASE_URL ?>/assets/icon/<?= htmlspecialchars($row['Icon']) ?>"
                                        alt="Icon" style="width: 24px; height: 24px;">
                                <?php else: ?>
                                    <i class="fas fa-image"></i>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="action-btns">
                                    <button type="button" class="btn-icon btn-edit edit-category"
                                        data-id="<?= $row['KategoriID'] ?>"
                                        data-nama="<?= htmlspecialchars($row['NamaKategori']) ?>"
                                        data-deskripsi="<?= htmlspecialchars($row['Deskripsi']) ?>"
                                        data-icon="<?= htmlspecialchars($row['Icon']) ?>">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button type="button" class="btn-icon btn-delete delete-category"
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

        <!-- Mobile Card View -->
        <div class="card-view">
            <?php
            mysqli_data_seek($categories, 0);
            $no = 1;
            while ($row = mysqli_fetch_assoc($categories)): ?>
                <div class="category-card" data-id="<?= $row['KategoriID'] ?>">
                    <div class="card-header">
                        <span class="card-number"><?= $no++ ?></span>
                        <h3 class="card-title"><?= htmlspecialchars($row['NamaKategori']) ?></h3>
                        <div class="card-icon">
                            <?php if (!empty($row['Icon'])): ?>
                                <img src="<?= BASE_URL ?>/assets/icon/<?= htmlspecialchars($row['Icon']) ?>"
                                    alt="Icon" style="width: 24px; height: 24px;">
                            <?php else: ?>
                                <i class="fas fa-image"></i>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="card-body">
                        <p class="card-description"><?= htmlspecialchars($row['Deskripsi']) ?></p>
                    </div>
                    <div class="card-footer">
                        <button type="button" class="btn-icon btn-edit edit-category"
                            data-id="<?= $row['KategoriID'] ?>"
                            data-nama="<?= htmlspecialchars($row['NamaKategori']) ?>"
                            data-deskripsi="<?= htmlspecialchars($row['Deskripsi']) ?>"
                            data-icon="<?= htmlspecialchars($row['Icon']) ?>">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button type="button" class="btn-icon btn-delete delete-category"
                            data-id="<?= $row['KategoriID'] ?>">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
</div>

<style>
    /* Base Styles */
    .content-container {
        padding: 2rem;
        max-width: 1200px;
        margin: 0 auto;
    }

    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 2rem;
    }

    .page-title {
        font-size: 1.5rem;
        color: #2d3748;
        margin: 0;
    }

    .card {
        background: #ffffff;
        border-radius: 0.5rem;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        padding: 1.5rem;
    }

    /* Search Container */
    .search-container {
        margin-bottom: 2rem;
        padding: 0 1.5rem;
    }

    .search-form {
        max-width: 500px;
        margin: 0 auto;
        position: relative;
    }

    .input-group {
        display: flex;
        align-items: center;
        background: #f8fafc;
        border-radius: 50px;
        transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.1);
        border: 2px solid #e2e8f0;
    }

    .input-group:hover {
        border-color: #cbd5e1;
        background: #ffffff;
    }

    .input-group:focus-within {
        border-color: var(--primary);
        background: #ffffff;
        box-shadow: 0 10px 15px -3px rgba(99, 102, 241, 0.1),
            0 4px 6px -4px rgba(99, 102, 241, 0.1);
        transform: translateY(-2px);
    }

    .search-form .form-control {
        padding: 1.25rem 2rem;
        padding-left: 3.5rem;
        border: none;
        background: transparent;
        font-size: 1rem;
        color: #1e293b;
        border-radius: 50px;
        transition: all 0.3s ease;
        width: 100%;
    }

    .search-form .form-control::placeholder {
        color: #94a3b8;
        letter-spacing: 0.5px;
    }

    .btn-search {
        position: absolute;
        top: 9px;
        left: 0.1rem;
        background: none;
        border: none;
        color: #64748b;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .btn-search i {
        font-size: 1.25rem;
        transform: translateY(2px);
    }

    .btn-reset {
        position: absolute;
        right: 1rem;
        background: none;
        border: none;
        color: #94a3b8;
        cursor: pointer;
        padding: 6px;
        border-radius: 50%;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        text-align: center;
    }

    .btn-reset:hover {
        color: #ef4444;
    }

    /* Table View */
    .table-view {
        width: 100%;
        overflow-x: auto;
    }

    .table-view table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 1rem;
    }

    .table-view th,
    .table-view td {
        padding: 1rem;
        text-align: left;
        border-bottom: 1px solid #e2e8f0;
    }

    .table-view th {
        background-color: #f7fafc;
        color: #4a5568;
        font-weight: 600;
    }

    .table-view tr:hover {
        background-color: #f8fafc;
    }

    /* Card View */
    .card-view {
        display: none;
        grid-template-columns: 1fr;
        gap: 1rem;
        margin-top: 1rem;
    }

    .category-card {
        background: #ffffff;
        border-radius: 0.5rem;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        padding: 1rem;
        border: 1px solid #e2e8f0;
    }

    .card-header {
        display: flex;
        align-items: center;
        margin-bottom: 0.5rem;
    }

    .card-number {
        background: var(--primary);
        color: white;
        width: 24px;
        height: 24px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.75rem;
        margin-right: 0.75rem;
    }

    .card-title {
        font-size: 1rem;
        margin: 0;
        flex-grow: 1;
    }

    .card-icon {
        margin-left: auto;
    }

    .card-description {
        font-size: 0.875rem;
        color: #4a5568;
        margin: 0.5rem 0;
    }

    .card-footer {
        display: flex;
        justify-content: flex-end;
        gap: 0.5rem;
        margin-top: 0.5rem;
    }

    /* Action Buttons */
    .action-btns {
        display: flex;
        gap: 0.5rem;
    }

    .btn-icon {
        background: none;
        border: none;
        cursor: pointer;
        padding: 0.5rem;
        border-radius: 0.25rem;
        transition: all 0.2s ease;
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
        font-size: 0.875rem;
    }

    .btn-edit {
        color: #3182ce;
    }

    .btn-edit:hover {
        background-color: #ebf8ff;
    }

    .btn-delete {
        color: #e53e3e;
    }

    .btn-delete:hover {
        background-color: #fff5f5;
    }

    /* Button Styles */
    .btn {
        padding: 0.75rem 1.5rem;
        border-radius: 0.375rem;
        font-weight: 500;
        transition: all 0.2s ease;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
    }

    .btn-primary {
        background-color: var(--primary);
        color: white;
        border: none;
    }

    .btn-primary:hover {
        background-color: var(--primary-dark);
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        .content-container {
            padding: 1rem;
        }

        .page-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 1rem;
        }

        .card {
            padding: 1rem;
        }

        .table-view {
            display: none;
        }

        .card-view {
            display: grid;
        }

        .search-form {
            width: 100%;
        }

        .search-form .form-control {
            padding: 0.75rem 1rem;
            padding-left: 2.5rem;
        }

        .btn-search {
            top: 50%;
            transform: translateY(-50%);
            left: 0.75rem;
        }

        .btn-reset {
            right: 0.75rem;
        }

        #addCategoryBtn {
            width: 100%;
        }
    }

    @media (min-width: 769px) {
        .card-view {
            display: none;
        }

        .table-view {
            display: block;
        }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Handle edit button click for both table and card views
        document.querySelectorAll('.edit-category').forEach(button => {
            button.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                const nama = this.getAttribute('data-nama');
                const deskripsi = this.getAttribute('data-deskripsi');
                const icon = this.getAttribute('data-icon');

                // Fill the edit modal with data
                document.getElementById('editKategoriID').value = id;
                document.getElementById('editNamaKategori').value = nama;
                document.getElementById('editDeskripsi').value = deskripsi;

                // Show the edit modal
                $('#editCategoryModal').modal('show');
            });
        });

        // Handle delete button click for both table and card views
        document.querySelectorAll('.delete-category').forEach(button => {
            button.addEventListener('click', function() {
                const id = this.getAttribute('data-id');

                // Set the ID in the delete modal
                document.getElementById('deleteKategoriID').value = id;

                // Show the delete modal
                $('#deleteCategoryModal').modal('show');
            });
        });

        // Add category button
        document.getElementById('addCategoryBtn').addEventListener('click', function() {
            $('#addCategoryModal').modal('show');
        });
    });
</script>

<?php include 'modal_kategori.php'; ?>
<?php include '../../views/footer.php'; ?>