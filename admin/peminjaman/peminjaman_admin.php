<?php
// c:/laragon/www/library/admin/peminjaman/peminjaman_admin.php

session_start();
require_once '../../config.php';

// Pastikan hanya admin yang bisa mengakses
if (!isset($_SESSION['role'])) {
    header("Location: ../../auth/login.php");
    exit;
} elseif ($_SESSION['role'] !== 'admin') {
    header("Location: ../../index.php");
    exit;
}

// Fungsi untuk mendapatkan data peminjaman dengan filter & pagination
function getAllPeminjaman($conn, $filters = [], $limit = 10, $offset = 0)
{
    $query = "SELECT p.*, b.Judul, b.Cover, a.Nama AS NamaAnggota, a.Email
              FROM peminjaman p
              LEFT JOIN buku b ON p.BukuID = b.BukuID
              LEFT JOIN anggota a ON p.MemberID = a.MemberID
              WHERE 1=1";
    $params = [];
    $types = '';

    // Filter Nama Anggota
    if (!empty($filters['nama'])) {
        $query .= " AND a.Nama LIKE ?";
        $params[] = '%' . $filters['nama'] . '%';
        $types .= 's';
    }
    // Filter Judul Buku
    if (!empty($filters['judul'])) {
        $query .= " AND b.Judul LIKE ?";
        $params[] = '%' . $filters['judul'] . '%';
        $types .= 's';
    }
    // Filter Status
    if (!empty($filters['status'])) {
        $query .= " AND p.Status = ?";
        $params[] = $filters['status'];
        $types .= 's';
    }

    $query .= " ORDER BY p.TanggalPinjam DESC LIMIT ? OFFSET ?";
    $params[] = $limit;
    $params[] = $offset;
    $types .= 'ii';

    $stmt = mysqli_prepare($conn, $query);
    if ($params) {
        mysqli_stmt_bind_param($stmt, $types, ...$params);
    }
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}

// Fungsi untuk menghitung total peminjaman (untuk pagination)
function getTotalPeminjaman($conn, $filters = [])
{
    $query = "SELECT COUNT(*) as total
              FROM peminjaman p
              LEFT JOIN buku b ON p.BukuID = b.BukuID
              LEFT JOIN anggota a ON p.MemberID = a.MemberID
              WHERE 1=1";
    $params = [];
    $types = '';

    if (!empty($filters['nama'])) {
        $query .= " AND a.Nama LIKE ?";
        $params[] = '%' . $filters['nama'] . '%';
        $types .= 's';
    }
    if (!empty($filters['judul'])) {
        $query .= " AND b.Judul LIKE ?";
        $params[] = '%' . $filters['judul'] . '%';
        $types .= 's';
    }
    if (!empty($filters['status'])) {
        $query .= " AND p.Status = ?";
        $params[] = $filters['status'];
        $types .= 's';
    }

    $stmt = mysqli_prepare($conn, $query);
    if ($params) {
        mysqli_stmt_bind_param($stmt, $types, ...$params);
    }
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    return $row['total'];
}

// Ambil parameter filter
$filters = [
    'nama' => trim($_GET['nama'] ?? ''),
    'judul' => trim($_GET['judul'] ?? ''),
    'status' => trim($_GET['status'] ?? '')
];

// Pagination
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limit = 10;
$offset = ($page - 1) * $limit;
$totalPeminjaman = getTotalPeminjaman($conn, $filters);
$totalPages = ceil($totalPeminjaman / $limit);

// Ambil data peminjaman
$peminjaman = getAllPeminjaman($conn, $filters, $limit, $offset);

$page_title = "Manajemen Peminjaman - Perpustakaan Digital";
include '../../views/header.php';
?>

<div class="content-wrapper">
    <div class="page-header d-flex justify-content-between align-items-center mb-4">
        <h1 class="mb-0">Manajemen Peminjaman</h1>
        <a href="tambah_peminjaman.php" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Tambah Peminjaman
        </a>
    </div>

    <?php include '../../views/alert_messages.php'; ?>

    <!-- Filter Form -->
    <div class="card mb-4 border-0 shadow-sm">
        <div class="card-body p-3">
            <form method="GET" class="search-filter-form">
                <div class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <div class="form-group mb-0">
                            <label for="nama" class="form-label small fw-semibold mb-2 d-flex align-items-center">
                                <i class="fas fa-user text-muted me-2"></i> Nama Anggota
                            </label>
                            <input type="text" id="nama" name="nama" class="form-control"
                                placeholder="Cari nama anggota..."
                                value="<?= htmlspecialchars($filters['nama']) ?>">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group mb-0">
                            <label for="judul" class="form-label small fw-semibold mb-2 d-flex align-items-center">
                                <i class="fas fa-book text-muted me-2"></i> Judul Buku
                            </label>
                            <input type="text" id="judul" name="judul" class="form-control"
                                placeholder="Cari judul buku..."
                                value="<?= htmlspecialchars($filters['judul']) ?>">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group mb-0">
                            <label for="status" class="form-label small fw-semibold mb-2">Status</label>
                            <select id="status" name="status" class="form-select py-2">
                                <option value="">Semua Status</option>
                                <option value="Active" <?= ($filters['status'] === 'Active') ? 'selected' : '' ?>>Aktif</option>
                                <option value="Expired" <?= ($filters['status'] === 'Expired') ? 'selected' : '' ?>>Kadaluarsa</option>
                                <option value="Returned" <?= ($filters['status'] === 'Returned') ? 'selected' : '' ?>>Dikembalikan</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="d-flex gap-2">
                            <button class="btn btn-primary flex-grow-1 py-2" type="submit">
                                <i class="fas fa-search me-2"></i>Cari
                            </button>
                            <a href="peminjaman_admin.php" class="btn btn-outline-secondary py-2 px-3">
                                <i class="fas fa-undo"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0" id="peminjamanTable">
                    <thead class="table-light">
                        <tr>
                            <th width="50">No</th>
                            <th>Anggota</th>
                            <th>Buku</th>
                            <th width="160">Tanggal Pinjam</th>
                            <th width="160">Tanggal Kembali</th>
                            <th width="120">Status</th>
                            <th width="100">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($peminjaman)): ?>
                            <?php foreach ($peminjaman as $index => $row): ?>
                                <tr>
                                    <td class="text-muted"><?= $offset + $index + 1 ?></td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar avatar-sm me-3">
                                                <span class="avatar-title rounded-circle bg-primary text-white">
                                                    <?= strtoupper(substr($row['NamaAnggota'], 0, 1)) ?>
                                                </span>
                                            </div>
                                            <div>
                                                <h6 class="mb-0 fw-semibold"><?= htmlspecialchars($row['NamaAnggota']) ?></h6>
                                                <small class="text-muted"><?= htmlspecialchars($row['Email']) ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="me-3">
                                                <?php if (!empty($row['Cover'])): ?>
                                                    <img src="<?= '../../' . htmlspecialchars($row['Cover']) ?>"
                                                        alt="Cover" class="book-cover rounded border">
                                                <?php else: ?>
                                                    <img src="../../assets/icon/default-book.png"
                                                        alt="Default" class="book-cover rounded border">
                                                <?php endif; ?>
                                            </div>
                                            <div>
                                                <h6 class="mb-0 fw-semibold"><?= htmlspecialchars($row['Judul']) ?></h6>
                                                <small class="text-muted">ID: <?= $row['BukuID'] ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-muted"><?= date('d M Y H:i', strtotime($row['TanggalPinjam'])) ?></td>
                                    <td class="text-muted">
                                        <?= $row['TanggalKembali'] ? date('d M Y H:i', strtotime($row['TanggalKembali'])) : '-' ?>
                                    </td>
                                    <td>
                                        <span class="badge rounded-pill 
                                            <?= $row['Status'] === 'Active' ? 'bg-primary-light text-primary' : ($row['Status'] === 'Returned' ? 'bg-success-light text-success' : 'bg-danger-light text-danger') ?>">
                                            <?= htmlspecialchars($row['Status']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="d-flex">
                                            <?php if ($row['Status'] === 'Active' || $row['Status'] === 'Expired'): ?>
                                                <button type="button" class="btn btn-sm btn-success me-1"
                                                    data-bs-toggle="modal" data-bs-target="#returnModal<?= $row['PeminjamanID'] ?>">
                                                    <i class="fas fa-undo"></i>
                                                </button>
                                            <?php endif; ?>
                                            <button type="button" class="btn btn-sm btn-danger"
                                                data-bs-toggle="modal" data-bs-target="#deleteModal<?= $row['PeminjamanID'] ?>">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>

                                        <!-- Return Modal -->
                                        <div class="modal fade" id="returnModal<?= $row['PeminjamanID'] ?>" tabindex="-1" role="dialog">
                                            <div class="modal-dialog" role="document">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Konfirmasi Pengembalian</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <p>Apakah Anda yakin ingin menandai buku ini sebagai dikembalikan?</p>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <form method="POST" action="peminjaman_handler.php">
                                                            <input type="hidden" name="action" value="kembalikan">
                                                            <input type="hidden" name="id" value="<?= $row['PeminjamanID'] ?>">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                            <button type="submit" class="btn btn-success">Ya, Kembalikan</button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Delete Modal -->
                                        <div class="modal fade" id="deleteModal<?= $row['PeminjamanID'] ?>" tabindex="-1" role="dialog">
                                            <div class="modal-dialog" role="document">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Konfirmasi Penghapusan</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <p>Apakah Anda yakin ingin menghapus data peminjaman ini?</p>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <form method="POST" action="peminjaman_handler.php">
                                                            <input type="hidden" name="action" value="hapus">
                                                            <input type="hidden" name="id" value="<?= $row['PeminjamanID'] ?>">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                            <button type="submit" class="btn btn-danger">Ya, Hapus</button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center">
                                    <div class="empty-state d-flex flex-column align-items-center justify-content-center py-5 px-3">
                                        <i class="fas fa-book-open fa-4x text-muted mb-4 opacity-50"></i>
                                        <h4 class="fw-semibold text-center mb-2">Tidak ada data peminjaman</h4>
                                        <p class="text-muted text-center mb-4">Tidak ditemukan data peminjaman yang sesuai dengan kriteria pencarian Anda</p>
                                        <a href="peminjaman_admin.php" class="btn btn-primary px-4">
                                            <i class="fas fa-sync-alt me-2"></i> Reset Pencarian
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <?php if ($totalPages > 1): ?>
                <div class="card-footer border-top-0 bg-white">
                    <nav aria-label="Page navigation">
                        <ul class="pagination justify-content-center mb-0">
                            <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                                <a class="page-link" href="?<?= http_build_query(array_merge($filters, ['page' => $page - 1])) ?>">
                                    <i class="fas fa-chevron-left"></i>
                                </a>
                            </li>

                            <?php
                            // Tampilkan maksimal 5 halaman di sekitar halaman aktif
                            $startPage = max(1, $page - 2);
                            $endPage = min($totalPages, $page + 2);

                            if ($startPage > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?<?= http_build_query(array_merge($filters, ['page' => 1])) ?>">1</a>
                                </li>
                                <?php if ($startPage > 2): ?>
                                    <li class="page-item disabled">
                                        <span class="page-link">...</span>
                                    </li>
                                <?php endif; ?>
                            <?php endif; ?>

                            <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                                <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                                    <a class="page-link" href="?<?= http_build_query(array_merge($filters, ['page' => $i])) ?>">
                                        <?= $i ?>
                                    </a>
                                </li>
                            <?php endfor; ?>

                            <?php if ($endPage < $totalPages): ?>
                                <?php if ($endPage < $totalPages - 1): ?>
                                    <li class="page-item disabled">
                                        <span class="page-link">...</span>
                                    </li>
                                <?php endif; ?>
                                <li class="page-item">
                                    <a class="page-link" href="?<?= http_build_query(array_merge($filters, ['page' => $totalPages])) ?>">
                                        <?= $totalPages ?>
                                    </a>
                                </li>
                            <?php endif; ?>

                            <li class="page-item <?= ($page >= $totalPages) ? 'disabled' : '' ?>">
                                <a class="page-link" href="?<?= http_build_query(array_merge($filters, ['page' => $page + 1])) ?>">
                                    <i class="fas fa-chevron-right"></i>
                                </a>
                            </li>
                        </ul>
                    </nav>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
    :root {
        --primary: #3a0ca3;
        --primary-light: #f0e7ff;
        --primary-dark: #2c0980;
        --secondary: #4cc9f0;
        --success: #38b000;
        --success-light: #e6f7e6;
        --danger: #ef233c;
        --danger-light: #ffebee;
        --warning: #ff9e00;
        --light: #f8f9fa;
        --dark: #212529;
        --gray: #6c757d;
        --gray-light: #e9ecef;
        --border-color: #dee2e6;
    }

    body {
        background-color: #f5f7fa;
    }

    .content-wrapper {
        max-width: 1200px;
        margin: 0 auto;
        padding: 1.5rem;
    }

    .page-header {
        margin-bottom: 1.5rem;
    }

    .page-header h1 {
        font-size: 1.75rem;
        font-weight: 600;
        color: var(--dark);
    }

    /* Filter Card */
    .filter-card {
        border-radius: 0.5rem;
        background-color: #fff;
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        margin-bottom: 1.5rem;
    }

    .search-filter-form .form-label {
        font-size: 0.8125rem;
        font-weight: 500;
        color: var(--gray);
        margin-bottom: 0.5rem;
    }

    .input-group-text {
        background-color: #fff;
        color: var(--gray);
        border-right: 0;
        padding: 0.5rem 0.75rem;
    }

    .form-control,
    .form-select {
        border-radius: 0.375rem;
        font-size: 0.875rem;
        padding: 0.5rem 0.75rem;
        height: auto;
    }

    .form-select {
        padding: 0.5rem 2.25rem 0.5rem 0.75rem;
    }

    .form-control:focus,
    .form-select:focus {
        box-shadow: none;
        border-color: var(--border-color);
    }

    /* Buttons */
    .btn {
        font-weight: 500;
        border-radius: 0.375rem;
        font-size: 0.875rem;
        padding: 0.5rem 1rem;
        transition: all 0.2s;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .btn-primary {
        background-color: var(--primary);
        border-color: var(--primary);
    }

    .btn-primary:hover {
        background-color: var(--primary-dark);
        border-color: var(--primary-dark);
    }

    .btn-outline-secondary {
        border-color: var(--border-color);
    }

    .btn-outline-secondary:hover {
        background-color: var(--gray-light);
    }

    .btn-sm {
        padding: 0.25rem 0.5rem;
        font-size: 0.8125rem;
    }

    /* Table */
    .card {
        border-radius: 0.5rem;
        overflow: hidden;
    }

    .table {
        font-size: 0.875rem;
        margin-bottom: 0;
    }

    .table th {
        font-weight: 600;
        font-size: 0.8125rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        padding: 0.75rem 1rem;
        background-color: #f8f9fa;
        color: var(--gray);
        border-bottom-width: 1px;
    }

    .table td {
        padding: 1rem;
        vertical-align: middle;
        border-color: var(--border-color);
    }

    .table-hover tbody tr:hover {
        background-color: rgba(58, 12, 163, 0.02);
    }

    /* Avatar */
    .avatar {
        width: 2.25rem;
        height: 2.25rem;
    }

    .avatar-sm {
        width: 2rem;
        height: 2rem;
        font-size: 0.875rem;
    }

    .avatar-title {
        font-weight: 500;
    }

    /* Book Cover */
    .book-cover {
        width: 2.5rem;
        height: 3.75rem;
        object-fit: cover;
    }

    /* Badges */
    .badge {
        font-weight: 500;
        padding: 0.35em 0.65em;
        font-size: 0.75rem;
    }

    .bg-primary-light {
        background-color: var(--primary-light);
    }

    .bg-success-light {
        background-color: var(--success-light);
    }

    .bg-danger-light {
        background-color: var(--danger-light);
    }

    /* Empty State */
    .empty-state {
        padding: 2rem 0;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        text-align: center;
        width: 100%;
    }

    .empty-state i {
        opacity: 0.4;
        margin-bottom: 1rem;
    }

    .empty-state h4 {
        font-weight: 600;
        margin-bottom: 0.5rem;
    }

    .empty-state p {
        max-width: 28rem;
        margin: 0 auto 1.5rem;
        color: var(--gray);
    }

    /* Pagination */
    .pagination {
        margin-bottom: 0;
    }

    .page-link {
        min-width: 2.25rem;
        height: 2.25rem;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--gray);
        border: 1px solid var(--border-color);
        margin: 0 0.125rem;
    }

    .page-item.active .page-link {
        background-color: var(--primary);
        border-color: var(--primary);
    }

    /* Modal */
    .modal-content {
        border-radius: 0.5rem;
    }

    .modal-header {
        border-bottom-color: var(--border-color);
        padding: 1rem 1.25rem;
    }

    .modal-title {
        font-weight: 600;
        font-size: 1.125rem;
    }

    .modal-footer {
        border-top-color: var(--border-color);
        padding: 0.75rem 1.25rem;
    }

    /* Links */
    a {
        text-decoration: none;
    }

    .fa-sync-alt {

        margin-top: 15px;
    }

    /* Responsive */
    @media (max-width: 767.98px) {
        .content-wrapper {
            padding: 1rem;
        }

        .page-header {
            flex-direction: column;
            align-items: flex-start;
        }

        .page-header h1 {
            margin-bottom: 1rem;
        }

        .search-filter-form .col-md-4,
        .search-filter-form .col-md-2 {
            margin-bottom: 0.75rem;
        }

        .table-responsive {
            border-radius: 0.5rem;
        }

        .table th,
        .table td {
            padding: 0.75rem;
        }
    }
</style>

<?php include '../../views/footer.php'; ?>