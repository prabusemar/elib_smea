<?php
session_start();
require_once '../config.php';

// Aktifkan error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Validasi session admin - menggunakan 'user_id' yang konsisten dengan proses_login.php
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

// Fungsi untuk redirect yang aman
function safe_redirect($url)
{
    if (headers_sent()) {
        die("Redirect failed. Please <a href='$url'>click here</a> to continue.");
    }
    header("Location: $url");
    exit;
}

// Get admin data - menggunakan $_SESSION['user_id']
$adminId = $_SESSION['user_id'];
$sql = "SELECT u.*, a.Bio, a.NoTelepon 
        FROM users u
        JOIN admin a ON u.admin_id = a.AdminID
        WHERE u.id = ? AND u.role = 'admin'";
$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {
    die("Error prepare statement: " . mysqli_error($conn));
}

mysqli_stmt_bind_param($stmt, "i", $adminId);
if (!mysqli_stmt_execute($stmt)) {
    die("Error execute statement: " . mysqli_stmt_error($stmt));
}

$result = mysqli_stmt_get_result($stmt);
$admin = mysqli_fetch_assoc($result);

if (!$admin) {
    die("Error: Data admin tidak ditemukan di database untuk ID: " . htmlspecialchars($adminId));
}

// Tab aktif
$active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'profile';

// Profile update processing
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profile'])) {
    $full_name = mysqli_real_escape_string($conn, $_POST['full_name'] ?? '');
    $bio = mysqli_real_escape_string($conn, $_POST['bio'] ?? '');
    $no_telepon = mysqli_real_escape_string($conn, $_POST['no_telepon'] ?? '');
    $profile_pic = $admin['profile_pic'];

    // Handle file upload
    if (!empty($_FILES['profile_pic']['name'])) {
        $targetDir = "../uploads/profiles/";

        if (!file_exists($targetDir)) {
            if (!mkdir($targetDir, 0755, true)) {
                $_SESSION['error'] = "Gagal membuat direktori upload";
            }
        }

        if (!isset($_SESSION['error'])) {
            $fileName = uniqid() . '_' . basename($_FILES["profile_pic"]["name"]);
            $targetFile = $targetDir . $fileName;
            $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
            $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];

            $check = getimagesize($_FILES["profile_pic"]["tmp_name"]);

            if ($check === false) {
                $_SESSION['error'] = "File yang diupload bukan gambar";
            } elseif ($_FILES["profile_pic"]["size"] > 2000000) {
                $_SESSION['error'] = "Ukuran file terlalu besar (max 2MB)";
            } elseif (!in_array($imageFileType, $allowedTypes)) {
                $_SESSION['error'] = "Hanya format JPG, JPEG, PNG & GIF yang diizinkan";
            } elseif (!move_uploaded_file($_FILES["profile_pic"]["tmp_name"], $targetFile)) {
                $_SESSION['error'] = "Gagal mengupload gambar";
            } else {
                $profile_pic = $fileName;
                // Delete old photo if not default
                if ($admin['profile_pic'] != 'default.jpg' && file_exists($targetDir . $admin['profile_pic'])) {
                    unlink($targetDir . $admin['profile_pic']);
                }
            }
        }
    }

    if (!isset($_SESSION['error'])) {
        // Mulai transaksi
        mysqli_begin_transaction($conn);

        try {
            // Update tabel users
            $sqlUser = "UPDATE users SET 
                       full_name = ?, 
                       profile_pic = ?
                       WHERE id = ?";
            $stmtUser = mysqli_prepare($conn, $sqlUser);

            if (!$stmtUser || !mysqli_stmt_bind_param($stmtUser, "ssi", $full_name, $profile_pic, $adminId)) {
                throw new Exception("Gagal mempersiapkan update user");
            }

            if (!mysqli_stmt_execute($stmtUser)) {
                throw new Exception("Gagal memperbarui user: " . mysqli_error($conn));
            }

            // Update tabel admin
            $sqlAdmin = "UPDATE admin SET 
                        Bio = ?, 
                        NoTelepon = ?
                        WHERE AdminID = ?";
            $stmtAdmin = mysqli_prepare($conn, $sqlAdmin);

            if (!$stmtAdmin || !mysqli_stmt_bind_param($stmtAdmin, "ssi", $bio, $no_telepon, $admin['admin_id'])) {
                throw new Exception("Gagal mempersiapkan update admin");
            }

            if (!mysqli_stmt_execute($stmtAdmin)) {
                throw new Exception("Gagal memperbarui admin: " . mysqli_error($conn));
            }

            // Commit transaksi jika semua berhasil
            mysqli_commit($conn);

            $_SESSION['success'] = "Profil berhasil diperbarui";
            // Update session data
            $_SESSION['full_name'] = $full_name;
            $_SESSION['profile_pic'] = $profile_pic;
            safe_redirect("admin_profile.php?tab=profile");
        } catch (Exception $e) {
            // Rollback jika ada error
            mysqli_rollback($conn);
            $_SESSION['error'] = $e->getMessage();
        }
    }
}

// Password change processing
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    $errors = [];

    if (empty($current_password)) {
        $errors[] = "Password saat ini harus diisi";
    }
    if (empty($new_password)) {
        $errors[] = "Password baru harus diisi";
    } elseif (strlen($new_password) < 8) {
        $errors[] = "Password minimal 8 karakter";
    }
    if ($new_password !== $confirm_password) {
        $errors[] = "Konfirmasi password tidak cocok";
    }

    if (empty($errors)) {
        if (password_verify($current_password, $admin['password'])) {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $sql = "UPDATE users SET password = ? WHERE id = ?";
            $stmt = mysqli_prepare($conn, $sql);

            if ($stmt && mysqli_stmt_bind_param($stmt, "si", $hashed_password, $adminId)) {
                if (mysqli_stmt_execute($stmt)) {
                    $_SESSION['success'] = "Password berhasil diubah";
                    safe_redirect("admin_profile.php?tab=security");
                } else {
                    $errors[] = "Gagal mengubah password: " . mysqli_error($conn);
                }
            } else {
                $errors[] = "Terjadi kesalahan sistem";
            }
        } else {
            $errors[] = "Password saat ini salah";
        }
    }

    if (!empty($errors)) {
        $_SESSION['error'] = implode("<br>", $errors);
    }
}

$page_title = "Profil Admin - Perpustakaan Digital";
include '../views/header.php';
?>

<div class="admin-profile-container">
    <div class="profile-header">
        <h1>Pengaturan Profil</h1>
        <p>Kelola informasi profil dan akun Anda</p>
    </div>

    <div class="profile-content">
        <div class="profile-sidebar">
            <div class="profile-info">
                <div class="profile-avatar">
                    <img src="../uploads/profiles/<?= htmlspecialchars($admin['profile_pic'] ?? 'default.jpg') ?>"
                        alt="Foto Profil"
                        onerror="this.src='../assets/images/default-profile.jpg'">
                </div>
                <div class="profile-meta">
                    <h3><?= htmlspecialchars($admin['full_name'] ?? $admin['username']) ?></h3>
                    <p>Administrator</p>
                </div>
            </div>

            <nav class="profile-menu">
                <a href="?tab=profile" class="<?= $active_tab == 'profile' ? 'active' : '' ?>">
                    <i class="fas fa-user"></i> Informasi Pribadi
                </a>
                <a href="?tab=security" class="<?= $active_tab == 'security' ? 'active' : '' ?>">
                    <i class="fas fa-lock"></i> Keamanan
                </a>
            </nav>
        </div>

        <div class="profile-main">
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success">
                    <?= $_SESSION['success'] ?>
                    <button type="button" class="close-alert">&times;</button>
                </div>
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-error">
                    <?= is_array($_SESSION['error']) ? implode('<br>', $_SESSION['error']) : $_SESSION['error'] ?>
                    <button type="button" class="close-alert">&times;</button>
                </div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>

            <!-- Tab Profile -->
            <div class="profile-tab <?= $active_tab == 'profile' ? 'active' : '' ?>" id="profile-tab">
                <h2>Informasi Pribadi</h2>
                <p>Perbarui informasi profil dan foto Anda</p>

                <form method="POST" enctype="multipart/form-data" class="profile-form">
                    <input type="hidden" name="update_profile" value="1">

                    <div class="form-group avatar-upload">
                        <label>Foto Profil</label>
                        <div class="avatar-preview">
                            <img id="avatar-preview" src="../uploads/profiles/<?= htmlspecialchars($admin['profile_pic'] ?? 'default.jpg') ?>"
                                alt="Preview Foto Profil"
                                onerror="this.src='../assets/images/default-profile.jpg'">
                            <label for="profile_pic" class="upload-btn">
                                <i style="color: white;" class="fas fa-camera"></i>
                            </label>
                            <input type="file" id="profile_pic" name="profile_pic" accept="image/*">
                        </div>
                        <small class="form-hint">Format: JPG, JPEG, PNG (Max 2MB)</small>
                    </div>

                    <div class="form-group">
                        <label for="full_name">Nama Lengkap</label>
                        <input type="text" id="full_name" name="full_name"
                            value="<?= htmlspecialchars($admin['full_name'] ?? '') ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text" id="username" value="<?= htmlspecialchars($admin['username']) ?>" disabled>
                    </div>

                    <div class="form-group">
                        <label for="no_telepon">Nomor Telepon</label>
                        <input type="text" id="no_telepon" name="no_telepon"
                            value="<?= htmlspecialchars($admin['NoTelepon'] ?? '') ?>">
                    </div>

                    <div class="form-group">
                        <label for="bio">Bio</label>
                        <textarea id="bio" name="bio" rows="3"><?= htmlspecialchars($admin['Bio'] ?? '') ?></textarea>
                    </div>
                </form>
            </div>

            <!-- Tab Security -->
            <div class="profile-tab <?= $active_tab == 'security' ? 'active' : '' ?>" id="security-tab">
                <h2>Keamanan Akun</h2>
                <p>Kelola keamanan akun dan ganti password</p>

                <form method="POST" class="security-form" id="passwordForm">
                    <input type="hidden" name="change_password" value="1">

                    <div class="form-group password-input">
                        <label for="current_password">Password Saat Ini</label>
                        <div class="input-with-icon">
                            <input type="password" id="current_password" name="current_password" required>
                            <button type="button" class="toggle-password">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <div class="form-group password-input">
                        <label for="new_password">Password Baru</label>
                        <div class="input-with-icon">
                            <input type="password" id="new_password" name="new_password" required
                                pattern=".{8,}" title="Minimal 8 karakter">
                            <button type="button" class="toggle-password">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <small class="form-hint">Minimal 8 karakter</small>
                    </div>

                    <div class="form-group password-input">
                        <label for="confirm_password">Konfirmasi Password Baru</label>
                        <div class="input-with-icon">
                            <input type="password" id="confirm_password" name="confirm_password" required>
                            <button type="button" class="toggle-password">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <div class="password-match-error">Password tidak cocok</div>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-key"></i> Ubah Password
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
    /* Base Styles */
    :root {
        --primary: #3a0ca3;
        --primary-light: #4361ee;
        --primary-lighter: #f0f2ff;
        --secondary: #f72585;
        --light: #f8f9fa;
        --dark: #212529;
        --gray: #6c757d;
        --gray-light: #e9ecef;
        --gray-lighter: #f8f9fa;
        --danger: #dc3545;
        --danger-light: #f8d7da;
        --success: #28a745;
        --success-light: #d4edda;
        --warning: #ffc107;
        --warning-light: #fff3cd;
        --border-radius: 8px;
        --box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
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
        background-color: var(--gray-lighter);
    }

    a {
        text-decoration: none;
        color: inherit;
    }

    /* Admin Profile Container */
    .admin-profile-container {
        max-width: 1200px;
        margin: 2rem auto;
        padding: 0 1rem;
    }

    .profile-header {
        margin-bottom: 2rem;
    }

    .profile-header h1 {
        font-size: 2rem;
        color: var(--primary);
        margin-bottom: 0.5rem;
    }

    .profile-header p {
        color: var(--gray);
    }

    /* Profile Content Layout */
    .profile-content {
        display: flex;
        gap: 2rem;
    }

    .profile-sidebar {
        flex: 0 0 280px;
    }

    .profile-main {
        flex: 1;
        background: white;
        border-radius: var(--border-radius);
        box-shadow: var(--box-shadow);
        padding: 2rem;
    }

    /* Profile Info */
    .profile-info {
        background: white;
        border-radius: var(--border-radius);
        box-shadow: var(--box-shadow);
        padding: 1.5rem;
        text-align: center;
        margin-bottom: 1rem;
    }

    .profile-avatar {
        width: 120px;
        height: 120px;
        margin: 0 auto 1rem;
        position: relative;
    }

    .profile-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        border-radius: 50%;
        border: 4px solid var(--primary-lighter);
    }

    .profile-meta h3 {
        font-size: 1.25rem;
        margin-bottom: 0.25rem;
    }

    .profile-meta p {
        color: var(--gray);
        font-size: 0.9rem;
    }

    /* Profile Menu */
    .profile-menu {
        background: white;
        border-radius: var(--border-radius);
        box-shadow: var(--box-shadow);
        overflow: hidden;
    }

    .profile-menu a {
        display: flex;
        align-items: center;
        padding: 1rem 1.5rem;
        color: var(--gray);
        transition: var(--transition);
        border-left: 3px solid transparent;
    }

    .profile-menu a i {
        margin-right: 0.75rem;
        width: 20px;
        text-align: center;
    }

    .profile-menu a:hover {
        color: var(--primary);
        background-color: var(--primary-lighter);
    }

    .profile-menu a.active {
        color: var(--primary);
        background-color: var(--primary-lighter);
        border-left-color: var(--primary);
        font-weight: 500;
    }

    /* Profile Tabs */
    .profile-tab {
        display: none;
    }

    .profile-tab.active {
        display: block;
    }

    .profile-tab h2 {
        font-size: 1.5rem;
        margin-bottom: 0.5rem;
    }

    .profile-tab p {
        color: var(--gray);
        margin-bottom: 1.5rem;
    }

    /* Forms */
    .profile-form,
    .security-form {
        max-width: 600px;
    }

    .form-group {
        margin-bottom: 1.5rem;
    }

    .form-group label {
        display: block;
        margin-bottom: 0.5rem;
        font-weight: 500;
    }

    .form-group input[type="text"],
    .form-group input[type="password"],
    .form-group textarea {
        width: 100%;
        padding: 0.75rem 1rem;
        border: 1px solid var(--gray-light);
        border-radius: var(--border-radius);
        font-size: 1rem;
        transition: var(--transition);
    }

    .form-group input[type="text"]:focus,
    .form-group input[type="password"]:focus,
    .form-group textarea:focus {
        border-color: var(--primary);
        outline: none;
        box-shadow: 0 0 0 3px rgba(58, 12, 163, 0.1);
    }

    .form-group textarea {
        min-height: 100px;
        resize: vertical;
    }

    .form-hint {
        display: block;
        font-size: 0.8rem;
        color: var(--gray);
        margin-top: 0.25rem;
    }

    /* Avatar Upload */
    .avatar-upload {
        text-align: center;
    }

    .avatar-preview {
        position: relative;
        width: 120px;
        height: 120px;
        margin: 0 auto 1rem;
    }

    .avatar-preview img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        border-radius: 50%;
        border: 4px solid var(--primary-lighter);
    }

    .upload-btn {
        position: absolute;
        bottom: 0;
        right: 0;
        background: var(--primary);
        color: white;
        width: 36px;
        height: 36px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        border: 2px solid white;
        transition: var(--transition);
    }

    .upload-btn:hover {
        background: var(--primary-light);
    }

    .avatar-preview input[type="file"] {
        display: none;
    }

    /* Password Input */
    .password-input {
        position: relative;
    }

    .input-with-icon {
        position: relative;
    }

    .toggle-password {
        position: absolute;
        right: 12px;
        top: 50%;
        transform: translateY(-50%);
        background: none;
        border: none;
        color: var(--gray);
        cursor: pointer;
        padding: 0.5rem;
    }

    .password-match-error {
        color: var(--danger);
        font-size: 0.8rem;
        margin-top: 0.25rem;
        display: none;
    }

    /* Buttons */
    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0.75rem 1.5rem;
        border-radius: var(--border-radius);
        font-weight: 500;
        cursor: pointer;
        transition: var(--transition);
        border: none;
        font-size: 1rem;
    }

    .btn i {
        margin-right: 0.5rem;
    }

    .btn-primary {
        background-color: var(--primary);
        color: white;
    }

    .btn-primary:hover {
        background-color: var(--primary-light);
    }

    /* Alerts */
    .alert {
        position: relative;
        padding: 1rem;
        margin-bottom: 1.5rem;
        border-radius: var(--border-radius);
    }

    .alert-success {
        background-color: var(--success-light);
        color: var(--success);
        border: 1px solid rgba(40, 167, 69, 0.2);
    }

    .alert-error {
        background-color: var(--danger-light);
        color: var(--danger);
        border: 1px solid rgba(220, 53, 69, 0.2);
    }

    .close-alert {
        position: absolute;
        top: 0.5rem;
        right: 0.5rem;
        background: none;
        border: none;
        font-size: 1.25rem;
        cursor: pointer;
        color: inherit;
    }

    /* Responsive */
    @media (max-width: 992px) {
        .profile-content {
            flex-direction: column;
        }

        .profile-sidebar {
            flex: 1;
        }
    }

    @media (max-width: 576px) {
        .admin-profile-container {
            padding: 0 0.5rem;
        }

        .profile-main {
            padding: 1.5rem;
        }

        .profile-info {
            padding: 1rem;
        }

        .profile-avatar {
            width: 100px;
            height: 100px;
        }

        .form-group {
            margin-bottom: 1rem;
        }

        .btn {
            width: 100%;
        }
    }

    .fa-camera {
        color: white;
        transition: color 0.3s ease;
        margin-top: 8px;
        margin-left: 8px;
        cursor: pointer;
    }

    .fa-camera:hover {
        color: var(--primary-light);
    }
</style>

<script>
    // Preview avatar before upload
    document.getElementById('profile_pic').addEventListener('change', function(event) {
        const [file] = event.target.files;
        if (file) {
            const imgPreview = document.getElementById('avatar-preview');
            imgPreview.src = URL.createObjectURL(file);
        }
    });

    // Toggle password visibility
    document.querySelectorAll('.toggle-password').forEach(button => {
        button.addEventListener('click', function() {
            const input = this.parentElement.querySelector('input');
            const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
            input.setAttribute('type', type);
            this.querySelector('i').classList.toggle('fa-eye');
            this.querySelector('i').classList.toggle('fa-eye-slash');
        });
    });

    // Password match validation
    document.getElementById('passwordForm').addEventListener('submit', function(e) {
        const newPassword = document.getElementById('new_password');
        const confirmPassword = document.getElementById('confirm_password');
        const matchError = document.querySelector('.password-match-error');

        if (newPassword.value !== confirmPassword.value) {
            confirmPassword.style.borderColor = 'var(--danger)';
            matchError.style.display = 'block';
            e.preventDefault();
        }
    });

    // Real-time password match check
    document.getElementById('confirm_password').addEventListener('input', function() {
        const newPassword = document.getElementById('new_password').value;
        const confirmPassword = this.value;
        const matchError = document.querySelector('.password-match-error');

        if (confirmPassword && newPassword !== confirmPassword) {
            this.style.borderColor = 'var(--danger)';
            matchError.style.display = 'block';
        } else {
            this.style.borderColor = '';
            matchError.style.display = 'none';
        }
    });

    // Close alert buttons
    document.querySelectorAll('.close-alert').forEach(button => {
        button.addEventListener('click', function() {
            this.parentElement.style.display = 'none';
        });
    });
</script>

<?php include '../views/footer.php'; ?>