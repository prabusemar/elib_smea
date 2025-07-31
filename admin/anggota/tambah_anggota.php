<?php
session_start();
require_once '../../config.php';

// Ensure only admin can access
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../auth/login.php");
    exit;
}

$error = '';
$success = '';

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    $jenis_akun = mysqli_real_escape_string($conn, $_POST['jenis_akun']);
    $masa_berlaku = !empty($_POST['masa_berlaku']) ? mysqli_real_escape_string($conn, $_POST['masa_berlaku']) : null;
    $foto_profil = 'default.jpg';

    // Validate input
    if (empty($nama) || empty($email) || empty($password)) {
        $error = 'Nama, email, dan password wajib diisi!';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Format email tidak valid!';
    } else {
        $check = mysqli_query($conn, "SELECT * FROM anggota WHERE Email = '$email'");
        if (mysqli_num_rows($check) > 0) {
            $error = 'Email sudah terdaftar!';
        } else {
            // File upload handling
            if (isset($_FILES['foto_profil']) && $_FILES['foto_profil']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = '../../uploads/profiles/';
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

                $fileExt = strtolower(pathinfo($_FILES['foto_profil']['name'], PATHINFO_EXTENSION));
                $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];

                if (in_array($fileExt, $allowedTypes)) {
                    $fileName = uniqid('profile_') . '.' . $fileExt;
                    if (move_uploaded_file($_FILES['foto_profil']['tmp_name'], $uploadDir . $fileName)) {
                        $foto_profil = $fileName;
                    } else {
                        $error = 'Gagal mengunggah foto profil.';
                    }
                } else {
                    $error = 'Format file tidak didukung. Gunakan JPG, PNG, atau GIF.';
                }
            }

            if (empty($error)) {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                mysqli_begin_transaction($conn);

                try {
                    $sql_anggota = "INSERT INTO anggota (Nama, Email, Password, FotoProfil, TanggalBergabung, Status, JenisAkun, MasaBerlaku) 
                                   VALUES (?, ?, ?, ?, CURDATE(), 'Active', ?, ?)";
                    $stmt = mysqli_prepare($conn, $sql_anggota);
                    mysqli_stmt_bind_param($stmt, "ssssss", $nama, $email, $hashed_password, $foto_profil, $jenis_akun, $masa_berlaku);
                    mysqli_stmt_execute($stmt);

                    $sql_users = "INSERT INTO users (username, password, role) VALUES (?, ?, 'member')";
                    $stmt = mysqli_prepare($conn, $sql_users);
                    mysqli_stmt_bind_param($stmt, "ss", $email, $hashed_password);
                    mysqli_stmt_execute($stmt);

                    mysqli_commit($conn);
                    $success = 'Anggota berhasil ditambahkan!';
                    $_POST = array();
                } catch (Exception $e) {
                    mysqli_rollback($conn);
                    $error = 'Gagal menambahkan anggota: ' . $e->getMessage();
                    if ($foto_profil !== 'default.jpg' && file_exists($uploadDir . $foto_profil)) {
                        unlink($uploadDir . $foto_profil);
                    }
                }
            }
        }
    }
}
?>

<?php include '../../views/header.php'; ?>

<div class="form-container">
    <div class="form-header">
        <h1><i class="fas fa-user-plus"></i> Tambah Anggota Baru</h1>
        <a href="anggota_admin.php" class="back-button">
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

    <form method="POST" enctype="multipart/form-data" class="member-form">
        <div class="form-grid">
            <div class="form-column">
                <div class="form-group">
                    <label for="nama">Nama Lengkap <span class="required">*</span></label>
                    <input type="text" id="nama" name="nama" value="<?= htmlspecialchars($_POST['nama'] ?? '') ?>" required>
                </div>

                <div class="form-group">
                    <label for="email">Email <span class="required">*</span></label>
                    <input type="email" id="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                </div>

                <div class="form-group">
                    <label for="password">Password <span class="required">*</span></label>
                    <div class="password-wrapper">
                        <input type="password" id="password" name="password" required>
                        <button type="button" class="toggle-password" aria-label="Toggle password visibility">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    <small class="hint">Minimal 8 karakter</small>
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
                        <img id="previewFoto" src="../../uploads/profiles/default.jpg" alt="Preview Foto Profil">
                    </div>
                </div>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="jenis_akun">Jenis Akun <span class="required">*</span></label>
                <select id="jenis_akun" name="jenis_akun" required>
                    <option value="Free" <?= ($_POST['jenis_akun'] ?? '') === 'Free' ? 'selected' : '' ?>>Free</option>
                    <option value="Premium" <?= ($_POST['jenis_akun'] ?? '') === 'Premium' ? 'selected' : '' ?>>Premium</option>
                </select>
            </div>

            <div class="form-group" id="masa_berlaku_group">
                <label for="masa_berlaku">Masa Berlaku</label>
                <input type="date" id="masa_berlaku" name="masa_berlaku" value="<?= htmlspecialchars($_POST['masa_berlaku'] ?? '') ?>">
                <small class="hint">Hanya untuk akun Premium</small>
            </div>
        </div>

        <div class="form-actions">
            <button type="reset" class="reset-button">
                <i class="fas fa-undo"></i> Reset
            </button>
            <button type="submit" class="submit-button">
                <i class="fas fa-save"></i> Simpan Anggota
            </button>
        </div>
    </form>
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
        margin: 0 auto;
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
    .member-form {
        display: flex;
        flex-direction: column;
        gap: 20px;
    }

    .form-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 25px;
    }

    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 25px;
    }

    @media (max-width: 768px) {

        .form-grid,
        .form-row {
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

    /* Password Input */
    .password-wrapper {
        position: relative;
    }

    .password-wrapper input {
        padding-right: 40px;
    }

    .toggle-password {
        position: absolute;
        right: 10px;
        top: 50%;
        transform: translateY(-50%);
        background: none;
        border: none;
        color: var(--gray);
        cursor: pointer;
        padding: 5px;
    }

    .toggle-password:hover {
        color: var(--primary);
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

    /* Hide masa berlaku for Free accounts */
    #masa_berlaku_group {
        display: none;
    }

    #jenis_akun[value="Premium"]~#masa_berlaku_group {
        display: block;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Toggle password visibility
        const togglePassword = document.querySelector('.toggle-password');
        const passwordInput = document.getElementById('password');

        togglePassword.addEventListener('click', function() {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            this.querySelector('i').classList.toggle('fa-eye-slash');
        });

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
                previewImg.src = '../../uploads/profiles/default.jpg';
            }
        });

        // Show/hide masa berlaku based on account type
        const accountType = document.getElementById('jenis_akun');
        const masaBerlakuGroup = document.getElementById('masa_berlaku_group');

        accountType.addEventListener('change', function() {
            if (this.value === 'Premium') {
                masaBerlakuGroup.style.display = 'flex';
                document.getElementById('masa_berlaku').setAttribute('required', 'required');
            } else {
                masaBerlakuGroup.style.display = 'none';
                document.getElementById('masa_berlaku').removeAttribute('required');
            }
        });

        // Trigger change event on page load
        accountType.dispatchEvent(new Event('change'));
    });
</script>