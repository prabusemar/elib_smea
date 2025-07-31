<?php
// Pastikan config.php sudah di-include di halaman yang memanggil navbar
if (!defined('BASE_URL')) {
    die('BASE_URL tidak didefinisikan. Pastikan config.php di-include');
}

// Start session jika belum dimulai
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<header>
    <div class="logo-container">
        <img src="<?= BASE_URL ?>/assets/logo/logo-smea.png" alt="Logo Perpus">
        <div class="logo-text">
            <h1>SMEA E-Lib</h1>
            <span>Perpustakaan Digital Modern</span>
        </div>
    </div>

    <nav class="nav-menu">
        <a href="<?= BASE_URL ?>/#home">Beranda</a>
        <a href="<?= BASE_URL ?>/#features">Fitur</a>
        <a href="<?= BASE_URL ?>/#collections">Koleksi</a>
        <a href="<?= BASE_URL ?>/#testimonials">Testimoni</a>
        <a href="<?= BASE_URL ?>/#pricing">Langganan</a>
    </nav>

    <div class="nav-actions">
        <?php if (isset($_SESSION['user_id'])): ?>
            <!-- Tampilan ketika user sudah login -->
            <div class="user-dropdown">
                <button class="user-profile-btn">
                    <?php if (!empty($_SESSION['profile_pic'])): ?>
                        <img src="<?= BASE_URL ?>/uploads/profiles/<?= htmlspecialchars($_SESSION['profile_pic']) ?>" alt="Profile" class="profile-pic">
                    <?php else: ?>
                        <div class="profile-initial"><?= strtoupper(substr($_SESSION['username'], 0, 1)) ?></div>
                    <?php endif; ?>
                    <span class="username"><?= htmlspecialchars($_SESSION['username']) ?></span>
                    <i class="fas fa-caret-down"></i>
                </button>

                <div class="dropdown-content">
                    <!-- Menu dasar untuk semua user yang login -->
                    <?php
                    // Tentukan dashboard dan profile sesuai role
                    if ($_SESSION['role'] === 'admin') {
                        $dashboardHref = BASE_URL . '/admin/dashboard_admin.php';
                        $profileHref = BASE_URL . '/admin/profile.php';
                    } elseif ($_SESSION['role'] === 'staff') {
                        $dashboardHref = BASE_URL . '/staff/dashboard_staff.php';
                        $profileHref = BASE_URL . '/staff/profile.php';
                    } else {
                        $dashboardHref = BASE_URL . '/user/dashboard.php';
                        $profileHref = BASE_URL . '/user/profile.php';
                    }
                    ?>
                    <a href="<?= $dashboardHref ?>">
                        <i class="fas fa-tachometer-alt"></i> Dashboard
                    </a>
                    <a href="<?= $profileHref ?>">
                        <i class="fas fa-user-circle"></i> Profil Saya
                    </a>

                    <?php if ($_SESSION['role'] === 'user'): ?>
                        <!-- Menu khusus user biasa -->
                        <a href="<?= BASE_URL ?>/user/loans.php">
                            <i class="fas fa-book"></i> Peminjaman Saya
                        </a>
                        <a href="<?= BASE_URL ?>/user/history.php">
                            <i class="fas fa-history"></i> Riwayat
                        </a>
                    <?php endif; ?>

                    <?php if ($_SESSION['role'] === 'staff'): ?>
                        <!-- Menu khusus staff -->
                        <div class="dropdown-divider"></div>
                        <span class="dropdown-role-label">Menu Staff</span>
                        <a href="<?= BASE_URL ?>/staff/loans.php">
                            <i class="fas fa-clipboard-list"></i> Kelola Peminjaman
                        </a>
                        <a href="<?= BASE_URL ?>/staff/books.php">
                            <i class="fas fa-book-open"></i> Daftar Buku
                        </a>
                    <?php endif; ?>

                    <?php if ($_SESSION['role'] === 'admin'): ?>
                        <!-- Menu khusus admin -->
                        <div class="dropdown-divider"></div>
                        <span class="dropdown-role-label">Menu Admin</span>
                        <a href="<?= BASE_URL ?>/admin/users.php">
                            <i class="fas fa-users-cog"></i> Kelola Pengguna
                        </a>
                        <a href="<?= BASE_URL ?>/admin/reports.php">
                            <i class="fas fa-chart-bar"></i> Laporan Sistem
                        </a>
                        <a href="<?= BASE_URL ?>/admin/settings.php">
                            <i class="fas fa-cog"></i> Pengaturan
                        </a>
                    <?php endif; ?>

                    <div class="dropdown-divider"></div>
                    <a href="<?= BASE_URL ?>../auth/proses_logout.php" class="logout-btn">
                        <i class="fas fa-sign-out-alt"></i> Keluar
                    </a>
                </div>
            </div>
        <?php else: ?>
            <!-- Tampilan ketika belum login -->
            <a href="<?= BASE_URL ?>/auth/login.php" class="btn btn-outline">
                <i class="fas fa-sign-in-alt"></i> Masuk
            </a>
            <a href="<?= BASE_URL ?>/auth/register.php" class="btn btn-primary">
                <i class="fas fa-user-plus"></i> Daftar
            </a>
        <?php endif; ?>
    </div>

    <button class="mobile-menu-btn">
        <i class="fas fa-bars"></i>
    </button>
</header>

<link rel="stylesheet" href="<?= BASE_URL ?>/assets/styles/navbar.css">
<script src="<?= BASE_URL ?>/assets/js/navbar.js"></script>