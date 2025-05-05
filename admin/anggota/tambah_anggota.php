<?php
session_start();
require_once '../../config.php';

// Pastikan hanya admin yang bisa mengakses
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../auth/login.php");
    exit;
}

$error = '';
$success = '';

// Proses form tambah anggota
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    $jenis_akun = mysqli_real_escape_string($conn, $_POST['jenis_akun']);
    $masa_berlaku = !empty($_POST['masa_berlaku']) ? mysqli_real_escape_string($conn, $_POST['masa_berlaku']) : null;

    // Validasi input
    if (empty($nama) || empty($email) || empty($password)) {
        $error = 'Nama, email, dan password wajib diisi!';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Format email tidak valid!';
    } else {
        // Cek apakah email sudah terdaftar
        $check = mysqli_query($conn, "SELECT * FROM anggota WHERE Email = '$email'");
        if (mysqli_num_rows($check) > 0) {
            $error = 'Email sudah terdaftar!';
        } else {
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Mulai transaksi
            mysqli_begin_transaction($conn);

            try {
                // Insert ke tabel anggota
                $sql_anggota = "INSERT INTO anggota (Nama, Email, Password, FotoProfil, TanggalBergabung, Status, JenisAkun, MasaBerlaku) 
                               VALUES (?, ?, ?, 'default.jpg', CURDATE(), 'Active', ?, ?)";
                $stmt = mysqli_prepare($conn, $sql_anggota);
                mysqli_stmt_bind_param($stmt, "sssss", $nama, $email, $hashed_password, $jenis_akun, $masa_berlaku);
                mysqli_stmt_execute($stmt);

                // Insert ke tabel users
                $sql_users = "INSERT INTO users (username, password, role) VALUES (?, ?, 'member')";
                $stmt = mysqli_prepare($conn, $sql_users);
                mysqli_stmt_bind_param($stmt, "ss", $email, $hashed_password);
                mysqli_stmt_execute($stmt);

                // Commit transaksi
                mysqli_commit($conn);

                $success = 'Anggota berhasil ditambahkan!';
                $_POST = array(); // Clear form
            } catch (Exception $e) {
                mysqli_rollback($conn);
                $error = 'Gagal menambahkan anggota: ' . $e->getMessage();
            }
        }
    }
}
?>

<?php include '../../views/header.php'; ?>


<div class="content-wrapper">
    <div class="page-header">
        <h1>Tambah Anggota Baru</h1>
        <div class="header-actions">
            <a href="../anggota_admin.php" class="btn btn-primary">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <?= $error; ?>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success">
                    <?= $success; ?>
                </div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="nama">Nama Lengkap</label>
                    <input type="text" id="nama" name="nama" class="form-control"
                        value="<?= isset($_POST['nama']) ? htmlspecialchars($_POST['nama']) : '' ?>" required>
                </div>

                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" class="form-control"
                        value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>" required>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="jenis_akun">Jenis Akun</label>
                    <select id="jenis_akun" name="jenis_akun" class="form-control" required>
                        <option value="Free" <?= (isset($_POST['jenis_akun']) && $_POST['jenis_akun'] === 'Free') ? 'selected' : '' ?>>Free</option>
                        <option value="Premium" <?= (isset($_POST['jenis_akun']) && $_POST['jenis_akun'] === 'Premium') ? 'selected' : '' ?>>Premium</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="masa_berlaku">Masa Berlaku (untuk Premium)</label>
                    <input type="date" id="masa_berlaku" name="masa_berlaku" class="form-control"
                        value="<?= isset($_POST['masa_berlaku']) ? htmlspecialchars($_POST['masa_berlaku']) : '' ?>">
                    <small class="text-muted">Biarkan kosong untuk akun Free</small>
                </div>

                <div class="form-group">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Simpan
                    </button>
                    <button type="reset" class="btn btn-secondary">
                        <i class="fas fa-undo"></i> Reset
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../../views/footer.php'; ?>

<style>
    .form-group {
        margin-bottom: 1.5rem;
    }

    label {
        display: block;
        margin-bottom: 0.5rem;
        font-weight: 600;
    }

    .form-control {
        width: 100%;
        padding: 0.75rem;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-size: 1rem;
        transition: border-color 0.3s ease;
    }

    .form-control:focus {
        border-color: var(--primary);
        outline: none;
        box-shadow: 0 0 0 2px rgba(58, 12, 163, 0.2);
    }

    select.form-control {
        height: auto;
        padding: 0.75rem;
    }

    .btn {
        padding: 0.75rem 1.5rem;
        border-radius: 4px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        border: none;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }

    .btn-primary {
        background-color: var(--primary);
        color: white;
    }

    .btn-primary:hover {
        background-color: #2e0a8a;
        transform: translateY(-2px);
    }

    .btn-secondary {
        background-color: #6c757d;
        color: white;
    }

    .btn-secondary:hover {
        background-color: #5a6268;
        transform: translateY(-2px);
    }

    .text-muted {
        color: #6c757d;
        font-size: 0.875rem;
    }

    .alert {
        padding: 1rem;
        margin-bottom: 1.5rem;
        border-radius: 4px;
    }

    .alert-danger {
        background-color: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }

    .alert-success {
        background-color: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const jenisAkunSelect = document.getElementById('jenis_akun');
        const masaBerlakuInput = document.getElementById('masa_berlaku');

        // Update masa berlaku required status based on account type
        function updateMasaBerlakuRequired() {
            if (jenisAkunSelect.value === 'Premium') {
                masaBerlakuInput.required = true;
            } else {
                masaBerlakuInput.required = false;
            }
        }

        // Initial check
        updateMasaBerlakuRequired();

        // Add event listener
        jenisAkunSelect.addEventListener('change', updateMasaBerlakuRequired);
    });
</script>