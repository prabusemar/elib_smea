<?php
session_start();
require_once '../../config.php';

// Ensure only admin can access
if (!isset($_SESSION['role'])) {
    header("Location: ../../auth/login.php");
    exit;
}

$error = '';
$success = '';

// Get member data if ID is provided
$member = null;
if (isset($_GET['id'])) {
    $member_id = (int)$_GET['id'];
    $query = "SELECT * FROM anggota WHERE MemberID = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $member_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $member = mysqli_fetch_assoc($result);
    
    if (!$member) {
        $error = 'Anggota tidak ditemukan!';
    }
} else {
    $error = 'ID Anggota tidak valid!';
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $member_id = (int)$_POST['member_id'];
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $jenis_akun = mysqli_real_escape_string($conn, $_POST['jenis_akun']);
    $masa_berlaku = !empty($_POST['masa_berlaku']) ? mysqli_real_escape_string($conn, $_POST['masa_berlaku']) : null;
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    $foto_profil = $member['FotoProfil']; // Default to existing photo
    
    // Validate input
    if (empty($nama) || empty($email)) {
        $error = 'Nama dan email wajib diisi!';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Format email tidak valid!';
    } else {
        // Check if email is changed and already exists
        if ($email !== $member['Email']) {
            $check = mysqli_query($conn, "SELECT * FROM anggota WHERE Email = '$email'");
            if (mysqli_num_rows($check) > 0) {
                $error = 'Email sudah terdaftar!';
            }
        }
    }

    if (empty($error)) {
        // File upload handling
        if (isset($_FILES['foto_profil']) && $_FILES['foto_profil']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = '../../uploads/profiles/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

            $fileExt = strtolower(pathinfo($_FILES['foto_profil']['name'], PATHINFO_EXTENSION));
            $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];

            if (in_array($fileExt, $allowedTypes)) {
                $fileName = uniqid('profile_') . '.' . $fileExt;
                if (move_uploaded_file($_FILES['foto_profil']['tmp_name'], $uploadDir . $fileName)) {
                    // Delete old photo if not default
                    if ($member['FotoProfil'] !== 'default.jpg' && file_exists($uploadDir . $member['FotoProfil'])) {
                        unlink($uploadDir . $member['FotoProfil']);
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
                // Update anggota table
                $sql_anggota = "UPDATE anggota SET 
                    Nama = ?, 
                    Email = ?, 
                    FotoProfil = ?, 
                    JenisAkun = ?, 
                    MasaBerlaku = ?, 
                    Status = ? 
                    WHERE MemberID = ?";
                $stmt = mysqli_prepare($conn, $sql_anggota);
                mysqli_stmt_bind_param($stmt, "ssssssi", $nama, $email, $foto_profil, $jenis_akun, $masa_berlaku, $status, $member_id);
                mysqli_stmt_execute($stmt);

                // Update users table if email changed
                if ($email !== $member['Email']) {
                    $sql_users = "UPDATE users SET username = ? WHERE username = ? AND role = 'member'";
                    $stmt = mysqli_prepare($conn, $sql_users);
                    mysqli_stmt_bind_param($stmt, "ss", $email, $member['Email']);
                    mysqli_stmt_execute($stmt);
                }

                mysqli_commit($conn);
                $success = 'Data anggota berhasil diperbarui!';
                
                // Refresh member data
                $query = "SELECT * FROM anggota WHERE MemberID = ?";
                $stmt = mysqli_prepare($conn, $query);
                mysqli_stmt_bind_param($stmt, "i", $member_id);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);
                $member = mysqli_fetch_assoc($result);
            } catch (Exception $e) {
                mysqli_rollback($conn);
                $error = 'Gagal memperbarui data anggota: ' . $e->getMessage();
                // Delete uploaded photo if transaction failed
                if ($foto_profil !== $member['FotoProfil'] && file_exists($uploadDir . $foto_profil)) {
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
        <h1><i class="fas fa-user-edit"></i> Edit Data Anggota</h1>
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

    <?php if ($member): ?>
    <form method="POST" enctype="multipart/form-data" class="member-form">
        <input type="hidden" name="member_id" value="<?= $member['MemberID'] ?>">
        
        <div class="form-grid">
            <div class="form-column">
                <div class="form-group">
                    <label for="nama">Nama Lengkap <span class="required">*</span></label>
                    <input type="text" id="nama" name="nama" value="<?= htmlspecialchars($member['Nama']) ?>" required>
                </div>

                <div class="form-group">
                    <label for="email">Email <span class="required">*</span></label>
                    <input type="email" id="email" name="email" value="<?= htmlspecialchars($member['Email']) ?>" required>
                </div>

                <div class="form-group">
                    <label for="status">Status <span class="required">*</span></label>
                    <select id="status" name="status" required>
                        <option value="Active" <?= $member['Status'] === 'Active' ? 'selected' : '' ?>>Active</option>
                        <option value="Suspended" <?= $member['Status'] === 'Suspended' ? 'selected' : '' ?>>Suspended</option>
                        <option value="Banned" <?= $member['Status'] === 'Banned' ? 'selected' : '' ?>>Banned</option>
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
                        <img id="previewFoto" src="../../uploads/profiles/<?= htmlspecialchars($member['FotoProfil']) ?>" alt="Preview Foto Profil">
                    </div>
                </div>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="jenis_akun">Jenis Akun <span class="required">*</span></label>
                <select id="jenis_akun" name="jenis_akun" required>
                    <option value="Free" <?= $member['JenisAkun'] === 'Free' ? 'selected' : '' ?>>Free</option>
                    <option value="Premium" <?= $member['JenisAkun'] === 'Premium' ? 'selected' : '' ?>>Premium</option>
                </select>
            </div>

            <div class="form-group" id="masa_berlaku_group">
                <label for="masa_berlaku">Masa Berlaku</label>
                <input type="date" id="masa_berlaku" name="masa_berlaku" value="<?= htmlspecialchars($member['MasaBerlaku']) ?>">
                <small class="hint">Hanya untuk akun Premium</small>
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

        // Show/hide masa berlaku based on account type
        const accountType = document.getElementById('jenis_akun');
        const masaBerlakuGroup = document.getElementById('masa_berlaku_group');

        accountType.addEventListener('change', function() {
            if (this.value === 'Premium') {
                masaBerlakuGroup.style.display = 'flex';
            } else {
                masaBerlakuGroup.style.display = 'none';
            }
        });

        // Trigger change event on page load
        accountType.dispatchEvent(new Event('change'));
    });
</script>