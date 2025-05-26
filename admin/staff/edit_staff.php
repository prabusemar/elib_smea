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

// Ambil data staff jika ID disediakan
$staff = null;
if (isset($_GET['id'])) {
    $staff_id = (int)$_GET['id'];
    $query = "SELECT * FROM staff WHERE StaffID = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $staff_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $staff = mysqli_fetch_assoc($result);

    if (!$staff) {
        $error = 'Staff tidak ditemukan!';
    }
} else {
    $error = 'ID Staff tidak valid!';
}

// Proses form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $staff_id = (int)$_POST['staff_id'];
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $jabatan = mysqli_real_escape_string($conn, $_POST['jabatan']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    $foto_profil = $staff['FotoProfil']; // Default ke foto yang ada

    // Validasi input
    if (empty($nama) || empty($email) || empty($jabatan) || empty($status)) {
        $error = 'Semua field wajib diisi!';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Format email tidak valid!';
    } else {
        // Cek jika email diubah dan sudah ada
        if ($email !== $staff['Email']) {
            $check = mysqli_query($conn, "SELECT * FROM staff WHERE Email = '$email'");
            if (mysqli_num_rows($check) > 0) {
                $error = 'Email sudah terdaftar!';
            }
        }
    }

    if (empty($error)) {
        // Handle upload foto profil
        if (isset($_FILES['foto_profil']) && $_FILES['foto_profil']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = '../../uploads/profiles/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $fileExt = strtolower(pathinfo($_FILES['foto_profil']['name'], PATHINFO_EXTENSION));
            $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];

            if (in_array($fileExt, $allowedTypes)) {
                $fileName = uniqid('staff_') . '.' . $fileExt;
                if (move_uploaded_file($_FILES['foto_profil']['tmp_name'], $uploadDir . $fileName)) {
                    // Hapus foto lama jika bukan default
                    if ($staff['FotoProfil'] !== 'default.jpg' && file_exists($uploadDir . $staff['FotoProfil'])) {
                        unlink($uploadDir . $staff['FotoProfil']);
                    }
                    $foto_profil = $fileName;
                } else {
                    $error = 'Gagal mengunggah foto profil.';
                }
            } else {
                $error = 'Format file tidak didukung. Gunakan JPG, PNG, atau GIF.';
            }
        }

        if (empty($error)) {
            mysqli_begin_transaction($conn);

            try {
                // Update tabel staff
                $sql_staff = "UPDATE staff SET 
                    Nama = ?, 
                    Email = ?, 
                    FotoProfil = ?, 
                    Jabatan = ?, 
                    Status = ? 
                    WHERE StaffID = ?";
                $stmt = mysqli_prepare($conn, $sql_staff);
                mysqli_stmt_bind_param($stmt, "sssssi", $nama, $email, $foto_profil, $jabatan, $status, $staff_id);
                mysqli_stmt_execute($stmt);

                // Update tabel users jika email diubah
                if ($email !== $staff['Email']) {
                    $sql_users = "UPDATE users SET username = ? WHERE username = ? AND role = 'staff'";
                    $stmt = mysqli_prepare($conn, $sql_users);
                    mysqli_stmt_bind_param($stmt, "ss", $email, $staff['Email']);
                    mysqli_stmt_execute($stmt);
                }

                mysqli_commit($conn);
                $success = 'Data staff berhasil diperbarui!';

                // Refresh data staff
                $query = "SELECT * FROM staff WHERE StaffID = ?";
                $stmt = mysqli_prepare($conn, $query);
                mysqli_stmt_bind_param($stmt, "i", $staff_id);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);
                $staff = mysqli_fetch_assoc($result);
            } catch (Exception $e) {
                mysqli_rollback($conn);
                $error = 'Gagal memperbarui data staff: ' . $e->getMessage();
                // Hapus foto yang sudah diupload jika transaksi gagal
                if ($foto_profil !== $staff['FotoProfil'] && file_exists($uploadDir . $foto_profil)) {
                    unlink($uploadDir . $foto_profil);
                }
            }
        }
    }
}
?>

<?php include '../../views/header.php'; ?>

<div class="form-container">
    <div class="form-header">
        <h1><i class="fas fa-user-edit"></i> Edit Data Staff</h1>
        <a href="staff.php" class="back-button">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
    </div>

    <?php if ($error): ?>
        <div class="alert error">
            <i class="fas fa-exclamation-circle"></i> <?= $error ?>
        </div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="alert success">
            <i class="fas fa-check-circle"></i> <?= $success ?>
        </div>
    <?php endif; ?>

    <?php if ($staff): ?>
        <form method="POST" enctype="multipart/form-data" class="staff-form">
            <input type="hidden" name="staff_id" value="<?= $staff['StaffID'] ?>">

            <div class="form-grid">
                <div class="form-column">
                    <div class="form-group">
                        <label for="nama">Nama Lengkap <span class="required">*</span></label>
                        <input type="text" id="nama" name="nama" value="<?= htmlspecialchars($staff['Nama']) ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="email">Email <span class="required">*</span></label>
                        <input type="email" id="email" name="email" value="<?= htmlspecialchars($staff['Email']) ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="jabatan">Jabatan <span class="required">*</span></label>
                        <select id="jabatan" name="jabatan" required>
                            <option value="Librarian" <?= $staff['Jabatan'] === 'Librarian' ? 'selected' : '' ?>>Librarian</option>
                            <option value="Manager" <?= $staff['Jabatan'] === 'Manager' ? 'selected' : '' ?>>Manager</option>
                            <option value="IT Support" <?= $staff['Jabatan'] === 'IT Support' ? 'selected' : '' ?>>IT Support</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="status">Status <span class="required">*</span></label>
                        <select id="status" name="status" required>
                            <option value="Active" <?= $staff['Status'] === 'Active' ? 'selected' : '' ?>>Active</option>
                            <option value="Suspended" <?= $staff['Status'] === 'Suspended' ? 'selected' : '' ?>>Suspended</option>
                            <option value="Banned" <?= $staff['Status'] === 'Banned' ? 'selected' : '' ?>>Banned</option>
                        </select>
                    </div>
                </div>

                <div class="form-column">
                    <div class="form-group">
                        <label for="foto_profil">Foto Profil</label>
                        <div class="file-upload">
                            <label class="file-label">
                                <input type="file" id="foto_profil" name="foto_profil" accept="image/*">
                                <span class="file-button"><i class="fas fa-upload"></i> Pilih File</span>
                                <span class="file-name">Belum ada file dipilih</span>
                            </label>
                            <small class="hint">Format: JPG, PNG, GIF (Maks. 2MB)</small>
                        </div>
                        <div class="image-preview">
                            <img id="previewFoto" src="../../uploads/profiles/<?= htmlspecialchars($staff['FotoProfil']) ?>"
                                onerror="this.src='../../assets/profiles/default.jpg'"
                                alt="Preview Foto Profil">
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-actions">
                <button type="reset" class="reset-button">
                    <i class="fas fa-undo"></i> Reset
                </button>
                <button type="submit" class="submit-button">
                    <i class="fas fa-save"></i> Simpan Perubahan
                </button>
            </div>
        </form>
    <?php endif; ?>
</div>

<?php include '../../views/footer.php'; ?>

<style>
    /* Base Styles */
    :root {
        --primary: #3a0ca3;
        --primary-dark: #3a0ca3;
        --secondary: #f72585;
        --success: #4cc9f0;
        --danger: #f72585;
        --light: #f8f9fa;
        --dark: #212529;
        --gray: #6c757d;
        --border: #dee2e6;
        --border-radius: 8px;
        --box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        --transition: all 0.3s ease;
    }

    * {
        box-sizing: border-box;
        margin: 0;
        padding: 0;
    }

    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        line-height: 1.6;
        color: var(--dark);
        background-color: #f5f7fa;
    }

    /* Form Container */
    .form-container {
        max-width: 1000px;
        margin: 2rem auto;
        background: white;
        border-radius: var(--border-radius);
        box-shadow: var(--box-shadow);
        padding: 30px;
    }

    .form-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 30px;
        padding-bottom: 15px;
        border-bottom: 1px solid var(--border);
    }

    .form-header h1 {
        font-size: 24px;
        color: var(--primary-dark);
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .back-button {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 8px 15px;
        background-color: var(--light);
        color: var(--dark);
        text-decoration: none;
        border-radius: var(--border-radius);
        transition: var(--transition);
        border: 1px solid var(--border);
    }

    .back-button:hover {
        background-color: var(--border);
        color: var(--dark);
    }

    /* Alert Messages */
    .alert {
        padding: 15px;
        margin-bottom: 25px;
        border-radius: var(--border-radius);
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .alert.error {
        background-color: #fee2e2;
        color: #b91c1c;
        border-left: 4px solid #dc2626;
    }

    .alert.success {
        background-color: #dcfce7;
        color: #166534;
        border-left: 4px solid #22c55e;
    }

    .alert i {
        font-size: 20px;
    }

    /* Form Layout */
    .staff-form {
        display: flex;
        flex-direction: column;
        gap: 20px;
    }

    .form-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 25px;
    }

    @media (max-width: 768px) {
        .form-grid {
            grid-template-columns: 1fr;
        }
    }

    /* Form Groups */
    .form-group {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .form-group label {
        font-weight: 600;
        color: var(--dark);
        font-size: 14px;
    }

    .required {
        color: var(--danger);
    }

    .hint {
        color: var(--gray);
        font-size: 12px;
        font-style: italic;
    }

    /* Form Controls */
    input[type="text"],
    input[type="email"],
    input[type="password"],
    input[type="date"],
    select {
        width: 100%;
        padding: 12px 15px;
        border: 1px solid var(--border);
        border-radius: var(--border-radius);
        font-size: 14px;
        transition: var(--transition);
        background-color: white;
    }

    input[type="text"]:focus,
    input[type="email"]:focus,
    input[type="password"]:focus,
    input[type="date"]:focus,
    select:focus {
        outline: none;
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.2);
    }

    /* File Upload */
    .file-upload {
        display: flex;
        flex-direction: column;
        gap: 5px;
    }

    .file-label {
        display: flex;
        flex-direction: column;
        gap: 5px;
        cursor: pointer;
    }

    .file-button {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 15px;
        background-color: var(--primary);
        color: white;
        border-radius: var(--border-radius);
        transition: var(--transition);
        text-align: center;
        justify-content: center;
        font-size: 14px;
    }

    .file-button:hover {
        background-color: var(--primary-dark);
    }

    .file-name {
        font-size: 13px;
        color: var(--gray);
        padding: 5px 0;
    }

    input[type="file"] {
        display: none;
    }

    /* Image Preview */
    .image-preview {
        margin-top: 15px;
        text-align: center;
    }

    .image-preview img {
        width: 120px;
        height: 120px;
        object-fit: cover;
        border-radius: 50%;
        border: 3px solid var(--border);
        transition: var(--transition);
    }

    .image-preview img:hover {
        border-color: var(--primary);
    }

    /* Form Actions */
    .form-actions {
        display: flex;
        justify-content: flex-end;
        gap: 15px;
        margin-top: 20px;
        padding-top: 20px;
        border-top: 1px solid var(--border);
    }

    .submit-button,
    .reset-button {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 12px 20px;
        border-radius: var(--border-radius);
        font-weight: 600;
        cursor: pointer;
        transition: var(--transition);
        border: none;
        font-size: 14px;
    }

    .submit-button {
        background-color: var(--primary);
        color: white;
    }

    .submit-button:hover {
        background-color: var(--primary-dark);
        transform: translateY(-2px);
    }

    .reset-button {
        background-color: var(--light);
        color: var(--dark);
        border: 1px solid var(--border);
    }

    .reset-button:hover {
        background-color: var(--border);
        transform: translateY(-2px);
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Preview image before upload
        const fileInput = document.getElementById('foto_profil');
        const previewImg = document.getElementById('previewFoto');
        const fileNameDisplay = document.querySelector('.file-name');

        fileInput.addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                fileNameDisplay.textContent = file.name;

                const reader = new FileReader();
                reader.onload = function(e) {
                    previewImg.src = e.target.result;
                }
                reader.readAsDataURL(file);
            } else {
                fileNameDisplay.textContent = 'Belum ada file dipilih';
            }
        });
    });
</script>