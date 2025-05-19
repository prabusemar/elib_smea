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

// Query to get categories
$query = "SELECT KategoriID, NamaKategori, Deskripsi, Icon FROM kategori ORDER BY NamaKategori";
$categories = mysqli_query($conn, $query);

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
                                    <img src="<?= BASE_URL ?>/assets/icon/<?= htmlspecialchars($row['Icon']) ?>"
                                        alt="Icon" style="width: 24px; height: 24px;">
                                <?php else: ?>
                                    <i class="fas fa-image"></i>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="action-btns">
                                    <!-- Edit Button - Fixed structure -->
                                    <button type="button" class="btn-icon btn-edit edit-category"
                                        data-id="<?= $row['KategoriID'] ?>"
                                        data-nama="<?= htmlspecialchars($row['NamaKategori']) ?>"
                                        data-deskripsi="<?= htmlspecialchars($row['Deskripsi']) ?>"
                                        data-icon="<?= htmlspecialchars($row['Icon']) ?>">
                                        <i style="margin-left: 10px" class="fas fa-edit"></i>
                                    </button>

                                    <!-- Delete Button -->
                                    <button type="button" class="btn-icon btn-delete delete-category"
                                        data-id="<?= $row['KategoriID'] ?>">
                                        <i style="margin-left: 10px" class="fas fa-trash"></i>
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

<style>
    /* Search Container - Style 2 */
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

    /* Micro-interaction for search icon */
    .input-group:focus-within .btn-search {
        color: var(--primary);
        transform: scale(1.1);
        animation: iconPulse 0.6s ease;
    }

    @keyframes iconPulse {
        0% {
            transform: scale(1);
        }

        50% {
            transform: scale(1.2);
        }

        100% {
            transform: scale(1.1);
        }
    }

    /* Responsive Design */
    @media (max-width: 640px) {
        .search-form .form-control {
            padding: 1rem 1.5rem;
            padding-left: 3rem;
        }

        .btn-search {
            left: 1rem;
        }

        .btn-reset {
            right: 1rem;
        }
    }
</style>

<?php include 'modal_kategori.php'; ?>
<?php include '../../views/footer.php'; ?>