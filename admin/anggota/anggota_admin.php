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
    $sql = "SELECT a.*, u.last_login 
            FROM anggota a
            LEFT JOIN users u ON a.Email = u.email
            WHERE a.is_deleted = 0";

    if ($search) {
        $search = mysqli_real_escape_string($conn, $search);
        $sql .= " AND (a.Nama LIKE '%$search%' OR a.Email LIKE '%$search%')";
    }
    if ($jenisAkun && in_array($jenisAkun, ['Free', 'Premium'])) {
        $jenisAkun = mysqli_real_escape_string($conn, $jenisAkun);
        $sql .= " AND a.JenisAkun = '$jenisAkun'";
    }
    $sql .= " ORDER BY a.TanggalBergabung DESC LIMIT $limit OFFSET $offset";
    $result = mysqli_query($conn, $sql);
    if (!$result) {
        die("Error: " . mysqli_error($conn));
    }
    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}

function getTotalAnggota($conn, $search = null, $jenisAkun = null)
{
    $sql = "SELECT COUNT(*) as total FROM anggota a WHERE a.is_deleted = 0";
    if ($search) {
        $search = mysqli_real_escape_string($conn, $search);
        $sql .= " AND (a.Nama LIKE '%$search%' OR a.Email LIKE '%$search%')";
    }
    if ($jenisAkun && in_array($jenisAkun, ['Free', 'Premium'])) {
        $jenisAkun = mysqli_real_escape_string($conn, $jenisAkun);
        $sql .= " AND a.JenisAkun = '$jenisAkun'";
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
        $sql = "DELETE FROM users WHERE email = ?";
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

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];

        if ($action === 'update_status' && isset($_POST['member_id']) && isset($_POST['status'])) {
            $memberID = intval($_POST['member_id']);
            $status = $_POST['status'];

            if (updateStatusAnggota($conn, $memberID, $status)) {
                $_SESSION['success'] = "Status anggota berhasil diperbarui";
            } else {
                $_SESSION['error'] = "Gagal memperbarui status anggota";
            }

            header("Location: " . $_SERVER['PHP_SELF']);
            exit;
        }

        if ($action === 'delete' && isset($_POST['member_id'])) {
            $memberID = intval($_POST['member_id']);

            if (deleteAnggota($conn, $memberID)) {
                $_SESSION['success'] = "Anggota berhasil dihapus";
            } else {
                $_SESSION['error'] = "Gagal menghapus anggota";
            }

            header("Location: " . $_SERVER['PHP_SELF']);
            exit;
        }
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
                            <th>Email</th>
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
                                        $fotoProfil = !empty($a['FotoProfil']) ? $a['FotoProfil'] : 'default.jpg';
                                        $fotoPath = ($fotoProfil === 'default.jpg') ? '../../assets/profiles/default.jpg' : '../../uploads/profiles/' . htmlspecialchars($fotoProfil);
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
                                <td colspan="9" class="text-center">Tidak ada data anggota</td>
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

<?php include '../../views/footer.php'; ?>

<style>
    /* Base Styles */
    .search-filter-form {
        display: flex;
        gap: 12px;
        align-items: stretch;
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
        -webkit-overflow-scrolling: touch;
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
        padding: 0.5rem 1rem;
    }

    .btn-danger {
        background-color: #fee2e2;
        color: #b91c1c;
        padding: 0.5rem 1rem;
    }

    .fa-trash,
    .fa-edit {
        margin-left: 8px;
    }

    /* Enhanced Profile Photo Styles */
    .profile-img-container {
        display: flex;
        justify-content: center;
        align-items: center;
        width: 100%;
        padding: 5px 0;
    }

    .profile-img {
        width: 60px;
        height: 60px;
        min-width: 60px;
        object-fit: contain;
        padding: 2px;
        box-sizing: border-box;
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

    /* Mobile Styles (max-width: 768px) */
    @media (max-width: 768px) {
        .content-wrapper {
            padding: 15px;
        }

        .page-header {
            flex-direction: column;
            align-items: flex-start;
        }

        .page-header h1 {
            margin-bottom: 15px;
            font-size: 1.5rem;
        }

        .header-actions {
            width: 100%;
            margin-top: 10px;
        }

        .header-actions .btn {
            width: 100%;
            justify-content: center;
        }

        .search-filter-form {
            flex-direction: column;
            gap: 10px;
            padding: 15px;
        }

        .input-group {
            width: 100%;
        }

        .form-control[name="search"] {
            font-size: 0.9rem;
            padding: 15px 15px;
        }

        .form-control[name="search"]::placeholder {
            font-size: 0.85rem;
        }

        select[name="jenis_akun"] {
            max-width: 100%;
            width: 100%;
            font-size: 0.9rem;
        }

        /* Table adjustments */
        .table-responsive {
            border: none;
        }

        #anggotaTable {
            min-width: 100%;
        }

        #anggotaTable thead {
            display: none;
        }

        #anggotaTable tr {
            display: block;
            margin-bottom: 20px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        #anggotaTable td {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            align-items: center;
            padding: 10px 15px;
            border-bottom: 1px solid #f1f3f5;
        }

        #anggotaTable td:last-child {
            border-bottom: none;
        }

        #anggotaTable td::before {
            content: attr(data-label);
            font-weight: 600;
            color: var(--primary);
            margin-right: 10px;
            flex-basis: 40%;
            font-size: 0.85rem;
        }

        #anggotaTable td>*:not(.mobile-text) {
            flex-basis: 55%;
        }

        /* Profile Image Mobile Fixes */
        #anggotaTable td[data-label="Foto"] {
            display: flex;
            justify-content: center;
            padding: 10px !important;
        }

        .profile-img {
            width: 50px !important;
            height: 50px !important;
            min-width: 50px !important;
        }

        /* Status form */
        .status-form {
            width: 100%;
        }

        .status-select {
            width: 100%;
            padding: 6px 10px;
            font-size: 0.85rem;
        }

        /* Action buttons */
        .btn-group {
            width: 100%;
            flex-direction: row;
            justify-content: space-between;
            gap: 8px;
        }

        .btn-group .btn {
            flex: 1;
            margin: 0;
            padding: 8px 5px;
            font-size: 0;
            justify-content: center;
        }

        .btn-group .btn i {
            display: inline-block;
            font-size: 1rem;
            margin: 0;
        }

        .btn-group .btn .mobile-text {
            display: none;
        }

        /* Pagination */
        .pagination {
            flex-wrap: wrap;
            justify-content: center;
        }

        .page-item {
            margin: 3px;
        }

        .page-link {
            padding: 8px 12px;
            min-width: 36px;
            text-align: center;
            font-size: 0.85rem;
        }

        /* Alerts */
        .alert {
            padding: 12px 40px 12px 15px;
            font-size: 0.9rem;
        }

        .close {
            padding: 12px 15px;
        }
    }

    /* Very Small Devices (max-width: 480px) */
    @media (max-width: 480px) {
        #anggotaTable td {
            flex-direction: row;
            align-items: center;
        }

        #anggotaTable td::before {
            margin-bottom: 0;
            flex-basis: 40%;
        }

        #anggotaTable td>*:not(.mobile-text) {
            flex-basis: 55%;
        }

        .btn-group {
            flex-direction: row;
            gap: 5px;
        }

        .btn-group .btn {
            padding: 8px 5px;
        }

        /* Profile Image Smaller */
        .profile-img {
            width: 45px !important;
            height: 45px !important;
            min-width: 45px !important;
        }

        /* Search placeholder */
        .form-control[name="search"]::placeholder {
            font-size: 0.8rem;
        }
    }

    /* Small Mobile Devices (max-width: 360px) */
    @media (max-width: 360px) {
        .btn-group .btn i {
            font-size: 0.9rem;
        }

        #anggotaTable td::before {
            font-size: 0.8rem;
        }

        .status-select {
            font-size: 0.8rem;
        }
    }

    .fa-edit,
    .fa-trash {
        font-size: 1rem;

        margin-left: 19px;
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