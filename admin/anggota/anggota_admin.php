<?php
session_start();
require_once '../../config.php';

// Pastikan hanya admin yang bisa mengakses
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../auth/login.php");
    exit;
}

// Fungsi untuk mendapatkan semua anggota
function getAllAnggota($conn)
{
    $sql = "SELECT m.*, u.username, u.role, u.last_login 
            FROM anggota m
            JOIN users u ON m.Email = u.username
            WHERE u.role = 'member'";
    $result = mysqli_query($conn, $sql);

    if (!$result) {
        die("Error: " . mysqli_error($conn));
    }

    return mysqli_fetch_all($result, MYSQLI_ASSOC);
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
$anggota = getAllAnggota($conn);

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
                            <th>ID</th>
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
                                    <td data-label="ID"><?= htmlspecialchars($a['MemberID']); ?></td>
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
                                                <span class="mobile-text">Edit</span>
                                            </a>
                                            <form method="POST" class="delete-form">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="member_id" value="<?= $a['MemberID']; ?>">
                                                <button type="submit" class="btn btn-danger"
                                                    title="Hapus" onclick="return confirm('Yakin ingin menghapus anggota ini?')">
                                                    <i class="fas fa-trash"></i>
                                                    <span class="mobile-text">Hapus</span>
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
        </div>
    </div>
</div>

<?php include '../../views/footer.php'; ?>


<style>
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
    }

    .btn-danger {
        background-color: #fee2e2;
        color: #b91c1c;
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
    });
</script>
</body>

</html>