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

<?php include 'modal_kategori.php'; ?>
<?php include '../../views/footer.php'; ?>