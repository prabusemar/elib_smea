<?php
session_start();
require_once '../../config.php';

// Pastikan hanya admin yang bisa mengakses
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../auth/login.php");
    exit;
}

// Fungsi untuk mendapatkan semua anggota
function getAllAnggota($conn, $search = null, $jenisAkun = null, $limit = 10, $offset = 0)
{
    $sql = "SELECT m.*, u.username, u.role, u.last_login 
            FROM anggota m
            JOIN users u ON m.Email = u.username
            WHERE u.role = 'member'";
    if ($search) {
        $search = mysqli_real_escape_string($conn, $search);
        $sql .= " AND (m.Nama LIKE '%$search%' OR m.Email LIKE '%$search%' OR u.username LIKE '%$search%')";
    }
    if ($jenisAkun && in_array($jenisAkun, ['Free', 'Premium'])) {
        $jenisAkun = mysqli_real_escape_string($conn, $jenisAkun);
        $sql .= " AND m.JenisAkun = '$jenisAkun'";
    }
    $sql .= " ORDER BY m.TanggalBergabung DESC LIMIT $limit OFFSET $offset";
    $result = mysqli_query($conn, $sql);
    if (!$result) {
        die("Error: " . mysqli_error($conn));
    }
    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}

function getTotalAnggota($conn, $search = null, $jenisAkun = null)
{
    $sql = "SELECT COUNT(*) as total FROM anggota m JOIN users u ON m.Email = u.username WHERE u.role = 'member'";
    if ($search) {
        $search = mysqli_real_escape_string($conn, $search);
        $sql .= " AND (m.Nama LIKE '%$search%' OR m.Email LIKE '%$search%' OR u.username LIKE '%$search%')";
    }
    if ($jenisAkun && in_array($jenisAkun, ['Free', 'Premium'])) {
        $jenisAkun = mysqli_real_escape_string($conn, $jenisAkun);
        $sql .= " AND m.JenisAkun = '$jenisAkun'";
    }
    $result = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($result);
    return $row['total'];
}

// Fungsi untuk mengubah status anggota
function updateStatusAnggota($conn, $memberID, $status)
{
    $sql = "UPDATE anggota SET Status = ? WHERE MemberID = ?";
    $stmt = mysqli_prepare($conn, $sql);

    if (!$stmt) {
        die("Prepare failed: " . mysqli_error($conn));
    }

    mysqli_stmt_bind_param($stmt, "si", $status, $memberID);
    return mysqli_stmt_execute($stmt);
}

// Fungsi untuk menghapus anggota
function deleteAnggota($conn, $memberID)
{
    // Mulai transaksi
    mysqli_begin_transaction($conn);

    try {
        // Dapatkan email anggota untuk menghapus dari tabel users
        $sql = "SELECT Email FROM anggota WHERE MemberID = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "i", $memberID);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);

        if (!$row) {
            throw new Exception("Anggota tidak ditemukan");
        }

        $email = $row['Email'];

        // Hapus dari tabel anggota
        $sql = "DELETE FROM anggota WHERE MemberID = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "i", $memberID);
        mysqli_stmt_execute($stmt);

        // Hapus dari tabel users
        $sql = "DELETE FROM users WHERE username = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);

        // Commit transaksi
        mysqli_commit($conn);
        return true;
    } catch (Exception $e) {
        mysqli_rollback($conn);
        error_log("Error deleting member: " . $e->getMessage());
        return false;
    }
}

// Proses aksi
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'update_status':
                if (isset($_POST['member_id'], $_POST['status'])) {
                    if (updateStatusAnggota($conn, $_POST['member_id'], $_POST['status'])) {
                        $_SESSION['success'] = "Status anggota berhasil diubah";
                    } else {
                        $_SESSION['error'] = "Gagal mengubah status anggota: " . mysqli_error($conn);
                    }
                }
                break;
            case 'edit_anggota':
                // Proses update anggota dari modal
                $id = (int)$_POST['edit_member_id'];
                $nama = mysqli_real_escape_string($conn, $_POST['edit_nama']);
                $email = mysqli_real_escape_string($conn, $_POST['edit_email']);
                $jenis_akun = mysqli_real_escape_string($conn, $_POST['edit_jenis_akun']);
                $masa_berlaku = !empty($_POST['edit_masa_berlaku']) ? mysqli_real_escape_string($conn, $_POST['edit_masa_berlaku']) : null;
                $status = mysqli_real_escape_string($conn, $_POST['edit_status']);
                $fotoProfil = $_POST['edit_foto_profil_lama'];
                // Handle upload foto profil jika ada
                if (isset($_FILES['edit_foto_profil']) && $_FILES['edit_foto_profil']['error'] === UPLOAD_ERR_OK) {
                    $targetDir = '../../uploads/profiles/';
                    $fileName = uniqid() . '_' . basename($_FILES['edit_foto_profil']['name']);
                    $targetFile = $targetDir . $fileName;
                    if (move_uploaded_file($_FILES['edit_foto_profil']['tmp_name'], $targetFile)) {
                        $fotoProfil = 'uploads/profiles/' . $fileName;
                    }
                }
                $sql = "UPDATE anggota SET Nama=?, Email=?, JenisAkun=?, MasaBerlaku=?, Status=?, FotoProfil=? WHERE MemberID=?";
                $stmt = mysqli_prepare($conn, $sql);
                mysqli_stmt_bind_param($stmt, "ssssssi", $nama, $email, $jenis_akun, $masa_berlaku, $status, $fotoProfil, $id);
                if (mysqli_stmt_execute($stmt)) {
                    $_SESSION['success'] = "Data anggota berhasil diupdate.";
                } else {
                    $_SESSION['error'] = "Gagal update anggota: " . mysqli_error($conn);
                }
                break;
            case 'delete':
                if (isset($_POST['member_id'])) {
                    if (deleteAnggota($conn, $_POST['member_id'])) {
                        $_SESSION['success'] = "Anggota berhasil dihapus";
                    } else {
                        $_SESSION['error'] = "Gagal menghapus anggota: " . mysqli_error($conn);
                    }
                }
                break;
        }
        header("Location: anggota_admin.php");
        exit;
    }
}

// Ambil data anggota
$search = isset($_GET['search']) ? $_GET['search'] : null;
$jenisAkun = isset($_GET['jenis_akun']) ? $_GET['jenis_akun'] : null;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limit = 10;
$offset = ($page - 1) * $limit;
$totalAnggota = getTotalAnggota($conn, $search, $jenisAkun);
$totalPages = ceil($totalAnggota / $limit);
$anggota = getAllAnggota($conn, $search, $jenisAkun, $limit, $offset);

?>

<?php
$page_title = "Manajemen Anggota - Perpustakaan Digital";
include '../../views/header.php';
?>

<div class="content-wrapper">
    <div class="page-header">
        <h1>Manajemen Anggota</h1>
        <div class="header-actions">
            <a href="tambah_anggota.php" class="btn btn-primary">
                <i class="fas fa-plus"></i> Tambah Anggota
            </a>
        </div>
    </div>
    <form method="GET" class="search-filter-form">
        <div class="input-group" style="flex:2;">
            <input type="text" name="search" class="form-control" placeholder="Cari nama, email, atau username..." value="<?= htmlspecialchars($search ?? '') ?>">
            <div class="input-group-append">
                <button class="btn btn-primary" type="submit"><i class="fas fa-search"></i> Cari</button>
            </div>
        </div>
        <select name="jenis_akun" class="form-control" style="flex:1;max-width:180px;">
            <option value="">Semua Akun</option>
            <option value="Free" <?= ($jenisAkun === 'Free') ? 'selected' : '' ?>>Free</option>
            <option value="Premium" <?= ($jenisAkun === 'Premium') ? 'selected' : '' ?>>Premium</option>
        </select>
    </form>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible">
            <?= $_SESSION['success'];
            unset($_SESSION['success']); ?>
            <button type="button" class="close">&times;</button>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible">
            <?= $_SESSION['error'];
            unset($_SESSION['error']); ?>
            <button type="button" class="close">&times;</button>
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table" id="anggotaTable">
                    <thead>
                        <tr>

                            <th>Foto</th>
                            <th>Nama</th>
                            <th>Email/Username</th>
                            <th>Tanggal Bergabung</th>
                            <th>Terakhir Login</th>
                            <th>Jenis Akun</th>
                            <th>Masa Berlaku</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($anggota)): ?>
                            <?php foreach ($anggota as $a): ?>
                                <tr>

                                    <td data-label="Foto">
                                        <?php
                                        $fotoProfil = !empty($a['FotoProfil']) ? $a['FotoProfil'] : 'assets/profiles/default.jpg';
                                        // Jika bukan default, pastikan path benar
                                        $fotoPath = ($fotoProfil === 'assets/profiles/default.jpg') ? '../../assets/profiles/default.jpg' : '../../uploads/profiles/' . htmlspecialchars($fotoProfil);
                                        ?>
                                        <img src="<?= $fotoPath ?>" alt="Foto Profil" class="profile-img">
                                    </td>
                                    <td data-label="Nama"><?= htmlspecialchars($a['Nama']); ?></td>
                                    <td data-label="Email"><?= htmlspecialchars($a['Email']); ?></td>
                                    <td data-label="Bergabung"><?= date('d M Y', strtotime($a['TanggalBergabung'])); ?></td>
                                    <td data-label="Login Terakhir">
                                        <?= $a['last_login'] ? date('d M Y H:i', strtotime($a['last_login'])) : 'Belum pernah'; ?>
                                    </td>
                                    <td data-label="Jenis Akun">
                                        <span class="badge <?= $a['JenisAkun'] === 'Premium' ? 'bg-success' : 'bg-secondary'; ?>">
                                            <?= htmlspecialchars($a['JenisAkun']); ?>
                                        </span>
                                    </td>
                                    <td data-label="Masa Berlaku">
                                        <?= $a['MasaBerlaku'] ? date('d M Y', strtotime($a['MasaBerlaku'])) : '-'; ?>
                                    </td>
                                    <td data-label="Status">
                                        <form method="POST" class="status-form">
                                            <input type="hidden" name="action" value="update_status">
                                            <input type="hidden" name="member_id" value="<?= $a['MemberID']; ?>">
                                            <select name="status" class="status-select 
                        <?= $a['Status'] === 'Active' ? 'bg-success-light' : ($a['Status'] === 'Suspended' ? 'bg-warning-light' : 'bg-danger-light'); ?>">
                                                <option value="Active" <?= $a['Status'] === 'Active' ? 'selected' : ''; ?>>Active</option>
                                                <option value="Suspended" <?= $a['Status'] === 'Suspended' ? 'selected' : ''; ?>>Suspended</option>
                                                <option value="Banned" <?= $a['Status'] === 'Banned' ? 'selected' : ''; ?>>Banned</option>
                                            </select>
                                        </form>
                                    </td>
                                    <td data-label="Aksi">
                                        <div class="btn-group">
                                            <a href="edit_anggota.php?id=<?= $a['MemberID']; ?>"
                                                class="btn btn-info" title="Edit">
                                                <i class="fas fa-edit"></i>
                                                <span class="mobile-text"></span>
                                            </a>
                                            <form method="POST" class="delete-form">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="member_id" value="<?= $a['MemberID']; ?>">
                                                <button type="submit" class="btn btn-danger"
                                                    title="Hapus" onclick="return confirm('Yakin ingin menghapus anggota ini?')">
                                                    <i class="fas fa-trash"></i>
                                                    <span class="mobile-text"></span>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="10" class="text-center">Tidak ada data anggota</td>
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
                                href="?search=<?= urlencode($search ?? '') ?>&jenis_akun=<?= urlencode($jenisAkun ?? '') ?>&page=<?= $page - 1 ?>">
                                Previous
                            </a>
                        </li>
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                                <a class="page-link"
                                    href="?search=<?= urlencode($search ?? '') ?>&jenis_akun=<?= urlencode($jenisAkun ?? '') ?>&page=<?= $i ?>">
                                    <?= $i ?>
                                </a>
                            </li>
                        <?php endfor; ?>
                        <li class="page-item <?= ($page >= $totalPages) ? 'disabled' : '' ?>">
                            <a class="page-link"
                                href="?search=<?= urlencode($search ?? '') ?>&jenis_akun=<?= urlencode($jenisAkun ?? '') ?>&page=<?= $page + 1 ?>">
                                Next
                            </a>
                        </li>
                    </ul>
                </nav>
            <?php endif; ?>
        </div>
    </div>
</div>
<!-- Modal Edit Anggota -->
<div class="modal fade" id="editAnggotaModal" tabindex="-1" aria-labelledby="editAnggotaModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" enctype="multipart/form-data">
                <div class="modal-header">
                    <h5 class="modal-title" id="editAnggotaModalLabel">Edit Data Anggota</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body modal-body-grid">
                    <div class="modal-col">
                        <div class="form-group">
                            <label for="edit_nama">Nama Lengkap</label>
                            <input type="text" class="form-control" id="edit_nama" name="edit_nama" required>
                        </div>
                        <div class="form-group">
                            <label for="edit_email">Email</label>
                            <input type="email" class="form-control" id="edit_email" name="edit_email" required>
                        </div>
                        <div class="form-group">
                            <label for="edit_jenis_akun">Jenis Akun</label>
                            <select class="form-control" id="edit_jenis_akun" name="edit_jenis_akun" required>
                                <option value="Free">Free</option>
                                <option value="Premium">Premium</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-col">
                        <div class="form-group">
                            <label for="edit_masa_berlaku">Masa Berlaku (Premium)</label>
                            <input type="date" class="form-control" id="edit_masa_berlaku" name="edit_masa_berlaku">
                        </div>
                        <div class="form-group">
                            <label for="edit_status">Status</label>
                            <select class="form-control" id="edit_status" name="edit_status" required>
                                <option value="Active">Active</option>
                                <option value="Suspended">Suspended</option>
                                <option value="Banned">Banned</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="edit_foto_profil">Foto Profil</label>
                            <input type="file" class="form-control" id="edit_foto_profil" name="edit_foto_profil" accept="image/*">
                            <img id="edit_foto_preview" src="" alt="Preview" style="max-width:80px;max-height:80px;margin-top:10px;display:none;">
                        </div>
                    </div>
                    <input type="hidden" name="action" value="edit_anggota">
                    <input type="hidden" name="edit_member_id" id="edit_member_id">
                    <input type="hidden" name="edit_foto_profil_lama" id="edit_foto_profil_lama">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../../views/footer.php'; ?>


<style>
    /* Search & Filter Form */
    .search-filter-form {
        display: flex;
        gap: 12px;
        align-items: stretch;
        /* Memastikan tinggi elemen sama */
        margin-bottom: 2rem;
    }

    .input-group {
        flex: 1;
        display: flex;
        align-items: stretch;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    .form-control[name="search"] {
        flex: 1;
        height: 48px;
        border: none;
        padding: 0 20px;
        font-size: 1rem;
        border-radius: 8px 0 0 8px;
    }

    .input-group-append {
        display: flex;
    }

    .btn-primary[type="submit"] {
        height: 48px;
        border-radius: 0 8px 8px 0;
        padding: 0 25px;
        display: flex;
        align-items: center;
        gap: 8px;
        white-space: nowrap;
    }

    select[name="jenis_akun"] {
        height: 48px;
        min-width: 180px;
        border: none;
        border-radius: 8px;
        padding: 0 20px;
        appearance: none;
        background: white url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%236c5ce7' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M2 5l6 6 6-6'/%3e%3c/svg%3e") no-repeat right 15px center/12px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    .table-responsive {
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

    .btn {
        padding: 0.5rem 1rem;
        border-radius: 6px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        border: none;
        font-size: 0.9rem;
        text-decoration: none;
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

    .btn-info {
        background-color: #e0f2fe;
        color: #0369a1;
        padding: 0.5rem 0rem;

    }

    .btn-danger {
        background-color: #fee2e2;
        color: #b91c1c;
        padding: 0.5rem 0rem;

    }

    .fa-trash,
    .fa-edit {
        margin-left: 15px;
    }

    .profile-img {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        object-fit: cover;
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

    .bg-secondary {
        background-color: #6c757d;
        color: white;
    }

    .bg-success-light {
        background-color: rgba(40, 167, 69, 0.2);
        color: #28a745;
    }

    .bg-warning-light {
        background-color: rgba(255, 193, 7, 0.2);
        color: #ffc107;
    }

    .bg-danger-light {
        background-color: rgba(220, 53, 69, 0.2);
        color: #dc3545;
    }

    .status-select {
        border: none;
        border-radius: 4px;
        padding: 5px 10px;
        cursor: pointer;
        transition: all 0.3s;
    }

    .status-select:focus {
        outline: none;
        box-shadow: none;
    }

    .btn-group {
        display: flex;
        gap: 5px;
    }

    .alert {
        position: relative;
        padding: 1rem;
        margin-bottom: 1rem;
        border: 1px solid transparent;
        border-radius: 0.25rem;
    }

    .alert-success {
        color: #0f5132;
        background-color: #d1e7dd;
        border-color: #badbcc;
    }

    .alert-danger {
        color: #842029;
        background-color: #f8d7da;
        border-color: #f5c2c7;
    }

    .alert-dismissible {
        padding-right: 3rem;
    }

    .close {
        position: absolute;
        top: 0;
        right: 0;
        padding: 1rem;
        background: transparent;
        border: 0;
        font-size: 1.5rem;
        line-height: 1;
        cursor: pointer;
    }

    @media (max-width: 992px) {
        .content-wrapper {
            margin-left: 0;
        }
    }

    @media (max-width: 768px) {

        /* Hide table headers */
        #anggotaTable thead {
            display: none;
        }

        /* Make each row a card */
        #anggotaTable tr {
            display: block;
            margin-bottom: 1rem;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        /* Display cells as flex with label */
        #anggotaTable td {
            display: flex;
            justify-content: space-between;
            padding: 0.5rem 1rem;
            border-bottom: 1px solid #eee;
        }

        /* Add labels before content */
        #anggotaTable td::before {
            content: attr(data-label);
            font-weight: bold;
            margin-right: 1rem;
        }

        /* Hide icons and show text on mobile */
        .btn i {
            display: none;
        }

        .mobile-text {
            display: inline;
        }

        /* Make form elements full width */
        .status-select {
            width: 100%;
        }
    }

    /* Modal Bootstrap custom center & z-index */
    .modal.fade.show {
        display: flex !important;
        align-items: center;
        justify-content: center;
    }

    .modal-dialog {
        margin: 0 auto;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        position: relative;
        display: flex;
        align-items: center;
        min-height: calc(100vh - 1rem);
        z-index: 1080;
    }

    .modal-backdrop {
        z-index: 1079;
    }

    .modal-content {
        z-index: 1090;
    }

    /* Modal body grid for edit anggota */
    .modal-body-grid {
        display: flex;
        gap: 2rem;
    }

    .modal-col {
        flex: 1 1 0;
        min-width: 0;
    }

    @media (max-width: 600px) {
        .modal-body-grid {
            flex-direction: column;
            gap: 0;
        }
    }

    /* Tambahan CSS agar card dan card-body tidak membatasi overflow */
    .card,
    .card-body {
        overflow: visible !important;
    }

    /* Tambahkan CSS berikut */
    .table-responsive {
        width: 100%;
        margin: 1rem 0;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.12);
    }

    #anggotaTable {
        min-width: 1000px;
        /* Minimum width untuk desktop */
    }

    @media (max-width: 768px) {
        .table-responsive {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        #anggotaTable {
            min-width: 100%;
        }

        /* Perbaikan tampilan mobile */
        #anggotaTable td {
            flex-direction: column;
            align-items: flex-start;
            padding: 1rem;
        }

        #anggotaTable td::before {
            content: attr(data-label);
            font-weight: bold;
            margin-bottom: 0.5rem;
            color: #6c5ce7;
        }

        .btn-group {
            flex-direction: column;
            width: 100%;
        }

        .btn {
            width: 100%;
            justify-content: center;
            margin: 0.25rem 0;
        }
    }

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
</style>

<!-- JavaScript -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {
        // Close button for alerts
        $('.close').click(function() {
            $(this).parent().fadeOut();
        });

        // Auto submit saat status diubah
        $('.status-select').change(function() {
            $(this).closest('form').submit();
        });
        // Auto submit saat filter jenis akun diubah
        $('select[name="jenis_akun"]').change(function() {
            $(this).closest('form').submit();
        });
    });
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    $(document).ready(function() {
        // Edit button click
        $('.btn-info').on('click', function(e) {
            e.preventDefault();
            var row = $(this).closest('tr');
            var id = row.find('td[data-label="ID"]').text().trim();
            var nama = row.find('td[data-label="Nama"]').text().trim();
            var email = row.find('td[data-label="Email"]').text().trim();
            var jenisAkun = row.find('span.badge').text().trim();
            var masaBerlaku = row.find('td[data-label="Masa Berlaku"]').text().trim();
            var status = row.find('select[name="status"]').val();
            var fotoProfil = row.find('img.profile-img').attr('src');
            // Remove ../../uploads/profiles/ or ../../assets/profiles/ from path
            if (fotoProfil.includes('uploads/profiles/')) {
                fotoProfil = fotoProfil.split('uploads/profiles/')[1];
            } else if (fotoProfil.includes('assets/profiles/')) {
                fotoProfil = 'assets/profiles/default.jpg';
            }
            $('#edit_member_id').val(id);
            $('#edit_nama').val(nama);
            $('#edit_email').val(email);
            $('#edit_jenis_akun').val(jenisAkun);
            $('#edit_masa_berlaku').val(masaBerlaku !== '-' ? masaBerlaku.split(' ')[2] + '-' + getMonthNumber(masaBerlaku.split(' ')[1]) + '-' + masaBerlaku.split(' ')[0] : '');
            $('#edit_status').val(status);
            $('#edit_foto_profil_lama').val(fotoProfil);
            if (fotoProfil && fotoProfil !== 'assets/profiles/default.jpg') {
                $('#edit_foto_preview').attr('src', '../../uploads/profiles/' + fotoProfil).show();
            } else {
                $('#edit_foto_preview').attr('src', '../../assets/profiles/default.jpg').show();
            }
            $('#editAnggotaModal').modal('show');
        });
        // Preview foto profil
        $('#edit_foto_profil').on('change', function() {
            if (this.files && this.files[0]) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    $('#edit_foto_preview').attr('src', e.target.result).show();
                }
                reader.readAsDataURL(this.files[0]);
            }
        });
        // Helper: convert bulan indo ke angka
        function getMonthNumber(bulan) {
            var bulanMap = {
                Jan: '01',
                Feb: '02',
                Mar: '03',
                Apr: '04',
                Mei: '05',
                Jun: '06',
                Jul: '07',
                Agu: '08',
                Sep: '09',
                Okt: '10',
                Nov: '11',
                Des: '12'
            };
            return bulanMap[bulan] || '01';
        }
    });

    // Tambahkan ini di bagian akhir file
    $(document).ready(function() {
        // Reset ke halaman 1 saat melakukan search atau filter
        $('form.search-filter-form').on('submit', function() {
            $(this).append('<input type="hidden" name="page" value="1">');
        });

        // Pastikan semua link pagination memiliki parameter yang benar
        $('.pagination a').each(function() {
            const url = new URL(this.href);
            url.searchParams.set('search', '<?= $search ?>');
            url.searchParams.set('jenis_akun', '<?= $jenisAkun ?>');
            this.href = url.toString();
        });
    });
</script>
</body>

</html>