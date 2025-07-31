<?php
session_start();
require_once '../../config.php';

// Pastikan hanya admin yang bisa mengakses
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../auth/login.php");
    exit;
}

// Fungsi untuk mendapatkan semua staff
function getAllStaff($conn, $search = null, $role = null, $limit = 10, $offset = 0)
{
    try {
        $sql = "SELECT s.*, u.username, u.role, u.last_login 
                FROM staff s
                LEFT JOIN users u ON s.Email = u.username AND u.role = 'staff'";

        if ($search) {
            $search = mysqli_real_escape_string($conn, $search);
            $sql .= " WHERE (s.Nama LIKE '%$search%' OR s.Email LIKE '%$search%' OR u.username LIKE '%$search%')";
        }

        if ($role) {
            $role = mysqli_real_escape_string($conn, $role);
            $sql .= ($search ? " AND" : " WHERE") . " s.Jabatan = '$role'";
        }

        $sql .= " ORDER BY s.TanggalBergabung DESC LIMIT $limit OFFSET $offset";
        $result = mysqli_query($conn, $sql);

        if (!$result) {
            throw new Exception("Query failed: " . mysqli_error($conn));
        }

        return mysqli_fetch_all($result, MYSQLI_ASSOC);
    } catch (Exception $e) {
        error_log("Error in getAllStaff: " . $e->getMessage());
        return [];
    }
}

function getTotalStaff($conn, $search = null, $role = null)
{
    try {
        $sql = "SELECT COUNT(*) as total FROM staff s LEFT JOIN users u ON s.Email = u.username AND u.role = 'staff'";

        if ($search) {
            $search = mysqli_real_escape_string($conn, $search);
            $sql .= " WHERE (s.Nama LIKE '%$search%' OR s.Email LIKE '%$search%' OR u.username LIKE '%$search%')";
        }

        if ($role) {
            $role = mysqli_real_escape_string($conn, $role);
            $sql .= ($search ? " AND" : " WHERE") . " s.Jabatan = '$role'";
        }

        $result = mysqli_query($conn, $sql);

        if (!$result) {
            throw new Exception("Query failed: " . mysqli_error($conn));
        }

        $row = mysqli_fetch_assoc($result);
        return $row['total'];
    } catch (Exception $e) {
        error_log("Error in getTotalStaff: " . $e->getMessage());
        return 0;
    }
}

// Fungsi untuk mengubah status staff
function updateStatusStaff($conn, $staffID, $status)
{
    $sql = "UPDATE staff SET Status = ? WHERE StaffID = ?";
    $stmt = mysqli_prepare($conn, $sql);

    if (!$stmt) {
        die("Prepare failed: " . mysqli_error($conn));
    }

    mysqli_stmt_bind_param($stmt, "si", $status, $staffID);
    return mysqli_stmt_execute($stmt);
}

// Fungsi untuk menghapus staff
function deleteStaff($conn, $staffID)
{
    // Mulai transaksi
    mysqli_begin_transaction($conn);

    try {
        // Dapatkan email staff untuk menghapus dari tabel users
        $sql = "SELECT Email FROM staff WHERE StaffID = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "i", $staffID);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);

        if (!$row) {
            throw new Exception("Staff tidak ditemukan");
        }

        $email = $row['Email'];

        // Hapus dari tabel staff
        $sql = "DELETE FROM staff WHERE StaffID = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "i", $staffID);
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
        error_log("Error deleting staff: " . $e->getMessage());
        return false;
    }
}

// Proses form jika ada aksi
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];

        if ($action == 'update_status' && isset($_POST['staff_id']) && isset($_POST['status'])) {
            $staffID = intval($_POST['staff_id']);
            $status = $_POST['status'];

            if (updateStatusStaff($conn, $staffID, $status)) {
                $_SESSION['success'] = "Status staff berhasil diperbarui";
            } else {
                $_SESSION['error'] = "Gagal memperbarui status staff";
            }
        } elseif ($action == 'delete' && isset($_POST['staff_id'])) {
            $staffID = intval($_POST['staff_id']);

            if (deleteStaff($conn, $staffID)) {
                $_SESSION['success'] = "Staff berhasil dihapus";
            } else {
                $_SESSION['error'] = "Gagal menghapus staff";
            }
        }

        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }
}

// Ambil data staff
$search = isset($_GET['search']) ? $_GET['search'] : null;
$role = isset($_GET['role']) ? $_GET['role'] : null;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limit = 10;
$offset = ($page - 1) * $limit;
$totalStaff = getTotalStaff($conn, $search, $role);
$totalPages = ceil($totalStaff / $limit);
$staff = getAllStaff($conn, $search, $role, $limit, $offset);
?>

<?php
$page_title = "Manajemen Staff - Perpustakaan Digital";
include '../../views/header.php';
?>

<div class="content-wrapper">
    <div class="page-header">
        <h1>Manajemen Staff</h1>
        <div class="header-actions">
            <a href="tambah_staff.php" class="btn btn-primary">
                <i class="fas fa-plus"></i> Tambah Staff
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
        <select name="role" class="form-control" style="flex:1;max-width:180px; font-size:0.9em;">
            <option value="">Semua Jabatan</option>
            <option value="Librarian" <?= ($role === 'Librarian') ? 'selected' : '' ?>>Librarian</option>
            <option value="Manager" <?= ($role === 'Manager') ? 'selected' : '' ?>>Manager</option>
            <option value="IT Support" <?= ($role === 'IT Support') ? 'selected' : '' ?>>IT Support</option>
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
                <table class="table" id="staffTable">
                    <thead>
                        <tr>
                            <th>Foto</th>
                            <th>Nama</th>
                            <th>Email/Username</th>
                            <th>Tanggal Bergabung</th>
                            <th>Terakhir Login</th>
                            <th>Jabatan</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($staff)): ?>
                            <?php foreach ($staff as $s): ?>
                                <tr>
                                    <td data-label="Foto">
                                        <?php
                                        $fotoProfil = !empty($s['FotoProfil']) ? $s['FotoProfil'] : 'assets/profiles/default.jpg';
                                        $fotoPath = ($fotoProfil === 'assets/profiles/default.jpg') ? '../../assets/profiles/default.jpg' : '../../uploads/profiles/' . htmlspecialchars($fotoProfil);
                                        ?>
                                        <img src="<?= $fotoPath ?>" alt="Foto Profil" class="profile-img">
                                    </td>
                                    <td data-label="Nama"><?= htmlspecialchars($s['Nama']); ?></td>
                                    <td data-label="Email"><?= htmlspecialchars($s['Email']); ?></td>
                                    <td data-label="Bergabung"><?= date('d M Y', strtotime($s['TanggalBergabung'])); ?></td>
                                    <td data-label="Login Terakhir">
                                        <?= $s['last_login'] ? date('d M Y H:i', strtotime($s['last_login'])) : 'Belum pernah'; ?>
                                    </td>
                                    <td data-label="Jabatan">
                                        <span class="badge <?=
                                                            $s['Jabatan'] === 'Manager' ? 'bg-primary' : ($s['Jabatan'] === 'Librarian' ? 'bg-info' : 'bg-secondary'); ?>">
                                            <?= htmlspecialchars($s['Jabatan']); ?>
                                        </span>
                                    </td>
                                    <td data-label="Status">
                                        <form method="POST" class="status-form">
                                            <input type="hidden" name="action" value="update_status">
                                            <input type="hidden" name="staff_id" value="<?= $s['StaffID']; ?>">
                                            <select name="status" class="status-select 
                                                <?= $s['Status'] === 'Active' ? 'bg-success-light' : ($s['Status'] === 'Suspended' ? 'bg-warning-light' : 'bg-danger-light'); ?>">
                                                <option value="Active" <?= $s['Status'] === 'Active' ? 'selected' : ''; ?>>Active</option>
                                                <option value="Suspended" <?= $s['Status'] === 'Suspended' ? 'selected' : ''; ?>>Suspended</option>
                                                <option value="Banned" <?= $s['Status'] === 'Banned' ? 'selected' : ''; ?>>Banned</option>
                                            </select>
                                        </form>
                                    </td>
                                    <td data-label="Aksi">
                                        <div class="btn-group">
                                            <a href="edit_staff.php?id=<?= $s['StaffID']; ?>"
                                                class="btn btn-info" title="Edit">
                                                <i class="fas fa-edit"></i>
                                                <span class="mobile-text"></span>
                                            </a>
                                            <form method="POST" class="delete-form">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="staff_id" value="<?= $s['StaffID']; ?>">
                                                <button type="submit" class="btn btn-danger"
                                                    title="Hapus" onclick="return confirm('Yakin ingin menghapus staff ini?')">
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
                                <td colspan="8" class="text-center">Tidak ada data staff</td>
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
                                href="?search=<?= urlencode($search ?? '') ?>&role=<?= urlencode($role ?? '') ?>&page=<?= $page - 1 ?>">
                                Previous
                            </a>
                        </li>
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                                <a class="page-link"
                                    href="?search=<?= urlencode($search ?? '') ?>&role=<?= urlencode($role ?? '') ?>&page=<?= $i ?>">
                                    <?= $i ?>
                                </a>
                            </li>
                        <?php endfor; ?>
                        <li class="page-item <?= ($page >= $totalPages) ? 'disabled' : '' ?>">
                            <a class="page-link"
                                href="?search=<?= urlencode($search ?? '') ?>&role=<?= urlencode($role ?? '') ?>&page=<?= $page + 1 ?>">
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
    /* Search & Filter Form */
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

    select[name="role"] {
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

    .bg-primary {
        background-color: #6c5ce7;
        color: white;
    }

    .bg-info {
        background-color: #17a2b8;
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

    /* Card and Table Responsiveness */
    .card,
    .card-body {
        overflow: visible !important;
    }

    .table-responsive {
        width: 100%;
        margin: 1rem 0;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.12);
    }

    #staffTable {
        min-width: 1000px;
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

    select[name="role"] {
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

    /* ==================== */
    /* MOBILE RESPONSIVENESS */
    /* ==================== */

    @media (max-width: 992px) {
        .content-wrapper {
            margin-left: 0;
        }
    }

    @media (max-width: 768px) {
        #staffTable thead {
            display: none;
        }

        #staffTable tr {
            display: block;
            margin-bottom: 1rem;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        #staffTable td {
            display: flex;
            justify-content: space-between;
            padding: 0.75rem 1rem;
            border-bottom: 1px solid #eee;
        }

        #staffTable td::before {
            content: attr(data-label);
            font-weight: bold;
            margin-right: 1rem;
            color: var(--primary);
        }

        .btn i {
            display: inline-block;
            margin: 0;
        }

        .mobile-text {
            display: none;
        }

        .status-select {
            width: 100%;
        }

        .table-responsive {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        #staffTable {
            min-width: 100%;
        }

        .btn-group {
            flex-direction: row;
            width: auto;
        }

        .btn {
            width: auto;
            justify-content: center;
            margin: 0;
            padding: 0.5rem 0.75rem;
        }
    }

    @media (max-width: 576px) {

        /* Mobile-friendly search and filter form */

        /* Untuk placeholder input search */
        .form-control[name="search"]::placeholder {
            font-size: 0.8rem;
            /* Ukuran font yang lebih kecil */
            opacity: 0.7;
            /* Membuat placeholder sedikit transparan */
        }

        /* Opsi alternatif jika perlu menargetkan semua browser */
        .form-control[name="search"]::-webkit-input-placeholder {
            /* Chrome/Opera/Safari */
            font-size: 0.8rem;
        }

        .form-control[name="search"]::-moz-placeholder {
            /* Firefox 19+ */
            font-size: 0.8rem;
        }

        .form-control[name="search"]:-ms-input-placeholder {
            /* IE 10+ */
            font-size: 0.8rem;
        }

        .form-control[name="search"]:-moz-placeholder {
            /* Firefox 18- */
            font-size: 0.8rem;
        }

        .search-filter-form {
            flex-direction: column;
            gap: 12px;
            padding: 15px;
        }

        .input-group {
            width: 100%;
        }

        select[name="role"] {
            max-width: 100%;
            width: 100%;
        }

        /* Make action buttons visible and properly spaced */
        .btn-group {
            flex-direction: row;
            justify-content: flex-start;
            gap: 8px;
        }

        .btn {
            padding: 0.5rem;
            min-width: 40px;
        }

        .btn i {
            display: inline-block;
            margin: 0;
        }

        .mobile-text {
            display: none;
        }

        /* Adjust table cells for better mobile display */
        #staffTable td {
            padding: 0.75rem 0.5rem;
            flex-wrap: wrap;
        }

        #staffTable td::before {
            margin-right: 0.5rem;
            font-size: 0.9rem;
            width: 100px;
            flex-shrink: 0;
        }

        /* Profile image size */
        .profile-img {
            width: 36px;
            height: 36px;
        }

        /* Status select dropdown */
        .status-select {
            padding: 4px 8px;
            font-size: 0.85rem;
            max-width: 120px;
        }

        /* Pagination adjustments */
        .pagination {
            gap: 4px;
            flex-wrap: wrap;
        }

        .page-link {
            padding: 8px 12px;
            font-size: 0.85rem;
        }
    }

    @media (max-width: 400px) {

        /* Even more compact view for very small screens */
        #staffTable td {
            padding: 0.5rem 0.25rem;
        }

        .btn-group {
            gap: 4px;
        }

        .btn {
            padding: 0.4rem;
            min-width: 36px;
        }

        .profile-img {
            width: 32px;
            height: 32px;
        }

        .status-select {
            font-size: 0.8rem;
            max-width: 100px;
        }

        #staffTable td::before {
            font-size: 0.8rem;
            width: 80px;
        }

        .search-filter-form {
            padding: 12px;
        }
    }
</style>

<!-- JavaScript -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    $(document).ready(function() {
        // Close button for alerts
        $('.close').on('click', function() {
            $(this).parent().fadeOut('fast', function() {
                $(this).remove();
            });
        });

        // Auto submit when status changes
        $('.status-select').on('change', function() {
            $(this).closest('form').submit();
        });

        // Auto submit when role filter changes
        $('select[name="role"]').on('change', function() {
            $(this).closest('form').submit();
        });

        // Reset to page 1 when searching or filtering
        $('form.search-filter-form').on('submit', function(e) {
            // Only add page parameter if it's not already in the URL
            if (!$(this).find('input[name="page"]').length) {
                $(this).append('<input type="hidden" name="page" value="1">');
            }
        });

        // Confirm before deleting staff
        $('.delete-form').on('submit', function(e) {
            if (!confirm('Apakah Anda yakin ingin menghapus staff ini?')) {
                e.preventDefault();
                return false;
            }
            return true;
        });

        // Mobile view adjustments
        function adjustMobileView() {
            const isMobile = $(window).width() <= 768;

            if (isMobile) {
                // Ensure action buttons are visible
                $('.btn-group').css({
                    'flex-direction': 'row',
                    'justify-content': 'flex-start'
                });

                $('.btn i').css('display', 'inline-block');
                $('.mobile-text').css('display', 'none');

                // Adjust status select width
                $('.status-select').css('width', '100%');

                // Make sure table cells display properly
                $('td[data-label="Aksi"]').css({
                    'display': 'flex',
                    'justify-content': 'space-between'
                });
            } else {
                // Reset to desktop view
                $('.btn-group').css('flex-direction', 'row');
                $('.status-select').css('width', 'auto');
            }
        }

        // Run on load and resize
        adjustMobileView();
        $(window).on('resize', adjustMobileView);

        // Enhance pagination links with current search parameters
        $('.pagination a').each(function() {
            const $link = $(this);
            const url = new URL($link.attr('href'));

            // Preserve search and role parameters
            const searchParam = $('input[name="search"]').val();
            const roleParam = $('select[name="role"]').val();

            if (searchParam) {
                url.searchParams.set('search', searchParam);
            }

            if (roleParam) {
                url.searchParams.set('role', roleParam);
            }

            $link.attr('href', url.toString());
        });

        // Handle click on pagination links to maintain scroll position
        $('.pagination').on('click', 'a', function(e) {
            e.preventDefault();
            const targetUrl = $(this).attr('href');

            // Save scroll position
            const scrollPosition = $(window).scrollTop();

            // Load new page
            window.location.href = targetUrl;

            // Restore scroll position after load
            $(window).on('load', function() {
                $(window).scrollTop(scrollPosition);
            });
        });

        // Improve mobile touch targets
        if ('ontouchstart' in document.documentElement) {
            $('.btn, .status-select, .page-link').css({
                'min-height': '44px',
                'min-width': '44px',
                'padding': '12px 16px'
            });

            $('.btn i').parent().css('padding', '12px 16px');
        }
    });
</script>
</body>

</html>