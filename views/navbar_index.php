<?php
// Pastikan config.php sudah di-include di halaman yang memanggil navbar
if (!defined('BASE_URL')) {
    die('BASE_URL tidak didefinisikan. Pastikan config.php di-include');
}

// Start session jika belum dimulai
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Tentukan dashboard dan profile sesuai role
if (isset($_SESSION['user_id']) && $_SESSION['logged_in']) {
    switch ($_SESSION['role']) {
        case 'admin':
            $dashboardHref = BASE_URL . '/admin/dashboard_admin.php';
            $profileHref = BASE_URL . '/admin/admin_profile.php';
            break;
        case 'staff':
            $dashboardHref = BASE_URL . '/staff/dashboard_staff.php';
            $profileHref = BASE_URL . '/staff/profile.php';
            break;
        default: // member/user
            $dashboardHref = BASE_URL . '/user/dashboard.php';
            $profileHref = BASE_URL . '/user/profile.php';
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SMEA E-Lib</title>
    <!-- Pastikan Font Awesome terload -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* ====================== VARIABLES ====================== */
        :root {
            --primary: #6c5ce7;
            --primary-light: #8579e9;
            --primary-dark: #5649c0;
            --dark: #2d3436;
            --gray: #636e72;
            --light-gray: #dfe6e9;
            --white: #ffffff;
            --danger: #e74c3c;
            --danger-dark: #c0392b;
            --success: #2ecc71;
            --warning: #f39c12;
            --info: #3498db;
        }

        /* ====================== BASE STYLES ====================== */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: var(--dark);
            line-height: 1.6;
            padding-top: 80px;
            /* Space for fixed header */
        }

        header {
            background-color: var(--white);
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.1);
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1000;
            padding: 1rem 5%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: all 0.3s ease;
        }

        header.sticky {
            padding: 0.7rem 5%;
            box-shadow: 0 5px 10px rgba(0, 0, 0, 0.1);
        }

        /* ====================== LOGO STYLES ====================== */
        .logo-container {
            display: flex;
            align-items: center;
            gap: 1rem;
            z-index: 1001;
        }

        .logo-container img {
            height: 50px;
            width: auto;
            transition: transform 0.3s ease;
        }

        .logo-container:hover img {
            transform: scale(1.05);
        }

        .logo-text {
            display: flex;
            flex-direction: column;
        }

        .logo-text h1 {
            font-size: 1.5rem;
            color: var(--primary);
            font-weight: 700;
            line-height: 1.2;
            margin: 0;
        }

        .logo-text span {
            font-size: 0.8rem;
            color: var(--gray);
            margin: 0;
        }

        /* ====================== NAVIGATION MENU ====================== */
        .nav-menu {
            display: flex;
            gap: 2rem;
            margin: 0;
            padding: 0;
            list-style: none;
        }

        .nav-menu a {
            text-decoration: none;
            color: var(--dark);
            font-weight: 500;
            position: relative;
            padding: 0.5rem 0;
            transition: color 0.3s ease;
            font-size: 0.95rem;
        }

        .nav-menu a:hover {
            color: var(--primary);
        }

        .nav-menu a::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 0;
            height: 2px;
            background-color: var(--primary);
            transition: width 0.3s ease;
        }

        .nav-menu a:hover::after {
            width: 100%;
        }

        /* ====================== ACTION BUTTONS ====================== */
        .nav-actions {
            display: flex;
            align-items: center;
            gap: 1rem;
            z-index: 1001;
        }

        .btn {
            padding: 0.7rem 1.5rem;
            border-radius: 30px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            border: none;
            font-size: 0.95rem;
        }

        .btn-primary {
            background-color: var(--primary);
            color: var(--white);
        }

        .btn-primary:hover {
            background-color: var(--primary-light);
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(108, 92, 231, 0.3);
        }

        .btn-outline {
            background-color: transparent;
            color: var(--primary);
            border: 2px solid var(--primary);
        }

        .btn-outline:hover {
            background-color: var(--primary);
            color: var(--white);
            transform: translateY(-3px);
        }

        /* ====================== USER DROPDOWN ====================== */
        .user-dropdown {
            position: relative;
            display: inline-block;
        }

        .user-profile-btn {
            display: flex;
            align-items: center;
            gap: 10px;
            background: none;
            border: none;
            cursor: pointer;
            padding: 8px 15px;
            border-radius: 50px;
            transition: all 0.3s ease;
            background-color: rgba(108, 92, 231, 0.1);
        }

        .user-profile-btn:hover {
            background-color: rgba(108, 92, 231, 0.2);
        }

        .profile-pic {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid rgba(108, 92, 231, 0.3);
        }

        .profile-initial {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background-color: var(--primary);
            color: var(--white);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 1rem;
        }

        .username {
            font-weight: 500;
            color: var(--dark);
            transition: color 0.3s ease;
        }

        .user-profile-btn:hover .username {
            color: var(--primary);
        }

        .dropdown-content {
            display: none;
            position: absolute;
            right: 0;
            background-color: var(--white);
            min-width: 220px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            z-index: 1000;
            overflow: hidden;
            margin-top: 5px;
            opacity: 0;
            transform: translateY(-10px);
            transition: all 0.3s ease;
        }

        .dropdown-content.show {
            display: block;
            opacity: 1;
            transform: translateY(0);
        }

        .dropdown-content a {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 16px;
            text-decoration: none;
            color: var(--dark);
            transition: all 0.2s;
            font-size: 0.9rem;
        }

        .dropdown-content a:hover {
            background-color: rgba(108, 92, 231, 0.05);
            color: var(--primary);
            padding-left: 20px;
        }

        .dropdown-content i {
            width: 20px;
            text-align: center;
            color: var(--primary);
            font-size: 0.95rem;
        }

        .dropdown-divider {
            border-top: 1px solid var(--light-gray);
            margin: 5px 0;
        }

        .dropdown-role-label {
            display: block;
            padding: 8px 16px;
            font-size: 0.75rem;
            text-transform: uppercase;
            color: var(--gray);
            letter-spacing: 1px;
            font-weight: 600;
        }

        .logout-btn {
            color: var(--danger) !important;
        }

        .logout-btn:hover {
            color: var(--danger-dark) !important;
            background-color: rgba(231, 76, 60, 0.05) !important;
        }

        /* ====================== MOBILE MENU ====================== */
        .mobile-menu-btn {
            display: none;
            background: none;
            border: none;
            font-size: 1.5rem;
            color: var(--primary);
            cursor: pointer;
            z-index: 1001;
            transition: transform 0.3s ease;
        }

        .mobile-menu-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 999;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
        }

        .mobile-menu-overlay.show {
            opacity: 1;
            visibility: visible;
        }

        .mobile-menu {
            position: fixed;
            top: 0;
            right: -100%;
            width: 80%;
            max-width: 320px;
            height: 100vh;
            background-color: white;
            z-index: 1000;
            transition: right 0.3s ease;
            overflow-y: auto;
            padding: 1rem;
        }

        .mobile-menu.show {
            right: 0;
        }

        .mobile-menu-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-bottom: 1rem;
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
            margin-bottom: 1rem;
        }

        .mobile-menu-close {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: var(--text-primary);
        }

        .mobile-menu-items {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .mobile-menu-items a {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem 1rem;
            text-decoration: none;
            color: var(--dark);
            font-weight: 500;
            border-radius: 0.375rem;
            transition: background-color 0.2s ease;
        }

        .mobile-menu-items a:hover {
            background-color: rgba(0, 0, 0, 0.05);
        }

        .mobile-menu-divider {
            height: 1px;
            background-color: rgba(0, 0, 0, 0.1);
            margin: 0.5rem 0;
        }

        .mobile-menu-label {
            display: block;
            padding: 0.5rem 1rem;
            font-size: 0.75rem;
            font-weight: 600;
            color: var(--gray);
            text-transform: uppercase;
        }

        .mobile-login-btn {
            color: var(--primary);
        }

        .mobile-register-btn {
            color: white;
            background-color: var(--primary);
        }

        .mobile-logout-btn {
            color: #e53e3e;
        }

        /* ====================== RESPONSIVE DESIGN ====================== */
        @media (max-width: 1150px) {

            .nav-menu,
            .nav-actions {
                display: none;
            }

            .mobile-menu-btn {
                display: block;
            }

            /* Pastikan navbar tidak transparan di mobile/tablet */
            header {
                background-color: var(--white) !important;
                box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1) !important;
            }

            header .logo-text h1,
            header .logo-text span {
                color: var(--primary) !important;
            }
        }

        @media (max-width: 480px) {
            .logo-text h1 {
                font-size: 1.3rem;
            }

            .logo-text span {
                font-size: 0.7rem;
            }
        }

        /* ====================== ANIMATIONS ====================== */
        @keyframes fadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        .dropdown-content {
            animation: fadeIn 0.3s ease forwards;
        }
    </style>
</head>

<body>
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
            <?php if (isset($_SESSION['user_id']) && $_SESSION['logged_in']): ?>
                <!-- Tampilan ketika user sudah login -->
                <div class="user-dropdown">
                    <button class="user-profile-btn" id="dropdownToggle">
                        <?php if (!empty($_SESSION['profile_pic'])): ?>
                            <img src="<?= BASE_URL ?>/uploads/profiles/<?= htmlspecialchars($_SESSION['profile_pic']) ?>" alt="Profile" class="profile-pic">
                        <?php else: ?>
                            <div class="profile-initial"><?= strtoupper(substr($_SESSION['username'], 0, 1)) ?></div>
                        <?php endif; ?>
                        <span class="username"><?= htmlspecialchars($_SESSION['username']) ?></span>
                        <i class="fas fa-caret-down"></i>
                    </button>

                    <div class="dropdown-content" id="dropdownMenu">
                        <a href="<?= $dashboardHref ?>">
                            <i class="fas fa-tachometer-alt"></i> Dashboard
                        </a>
                        <a href="<?= $profileHref ?>">
                            <i class="fas fa-user-circle"></i> Profil Saya
                        </a>

                        <?php if ($_SESSION['role'] === 'admin'): ?>
                            <!-- Menu khusus admin -->
                            <div class="dropdown-divider"></div>
                            <span class="dropdown-role-label">Menu Admin</span>
                            <a href="<?= BASE_URL ?>/admin/anggota/anggota_admin.php">
                                <i class="fas fa-users-cog"></i> Kelola Anggota
                            </a>
                            <a href="<?= BASE_URL ?>/admin/buku/buku_admin.php">
                                <i class="fas fa-book-open"></i> Kelola Buku
                            </a>
                            <a href="<?= BASE_URL ?>/admin/peminjaman/peminjaman_admin.php">
                                <i class="fas fa-clipboard-list"></i> Kelola Peminjaman
                            </a>
                        <?php elseif ($_SESSION['role'] === 'staff'): ?>
                            <!-- Menu khusus staff -->
                            <div class="dropdown-divider"></div>
                            <span class="dropdown-role-label">Menu Staff</span>
                            <a href="<?= BASE_URL ?>/admin/buku/buku_admin.php">
                                <i class="fas fa-book-open"></i> Kelola Buku
                            </a>
                            <a href="<?= BASE_URL ?>/admin/peminjaman/peminjaman_admin.php">
                                <i class="fas fa-clipboard-list"></i> Kelola Peminjaman
                            </a>
                        <?php endif; ?>

                        <div class="dropdown-divider"></div>
                        <a href="<?= BASE_URL ?>/auth/proses_logout.php" class="logout-btn">
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

        <button class="mobile-menu-btn" id="mobileMenuToggle">
            <i class="fas fa-bars"></i>
        </button>
    </header>

    <!-- Mobile Menu (untuk perangkat mobile) -->
    <div class="mobile-menu-overlay" id="mobileMenuOverlay"></div>
    <nav class="mobile-menu" id="mobileMenu">
        <div class="mobile-menu-header">
            <?php if (isset($_SESSION['user_id']) && $_SESSION['logged_in']): ?>
                <div style="display: flex; align-items: center; gap: 10px;">
                    <img src="<?= BASE_URL ?>/assets/profiles/default.jpg" alt="User" style="width:36px;height:36px;border-radius:50%;object-fit:cover;">
                    <span>Hai, <?= htmlspecialchars($_SESSION['username']) ?></span>
                </div>
            <?php endif; ?>
            <button class="mobile-menu-close" id="mobileMenuClose">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <div class="mobile-menu-items">
            <a href="<?= BASE_URL ?>/#home">Beranda</a>
            <a href="<?= BASE_URL ?>/#features">Fitur</a>
            <a href="<?= BASE_URL ?>/#collections">Koleksi</a>
            <a href="<?= BASE_URL ?>/#testimonials">Testimoni</a>
            <a href="<?= BASE_URL ?>/#pricing">Langganan</a>

            <?php if (isset($_SESSION['user_id']) && $_SESSION['logged_in']): ?>
                <div class="mobile-menu-divider"></div>
                <a href="<?= $dashboardHref ?>">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
                <a href="<?= $profileHref ?>">
                    <i class="fas fa-user-circle"></i> Profil Saya
                </a>

                <?php if ($_SESSION['role'] === 'admin'): ?>
                    <div class="mobile-menu-divider"></div>
                    <span class="mobile-menu-label">Menu Admin</span>
                    <a href="<?= BASE_URL ?>/admin/anggota/anggota_admin.php">
                        <i class="fas fa-users-cog"></i> Kelola Anggota
                    </a>
                    <a href="<?= BASE_URL ?>/admin/buku/buku_admin.php">
                        <i class="fas fa-book-open"></i> Kelola Buku
                    </a>
                    <a href="<?= BASE_URL ?>/admin/peminjaman/peminjaman_admin.php">
                        <i class="fas fa-clipboard-list"></i> Kelola Peminjaman
                    </a>
                <?php elseif ($_SESSION['role'] === 'staff'): ?>
                    <div class="mobile-menu-divider"></div>
                    <span class="mobile-menu-label">Menu Staff</span>
                    <a href="<?= BASE_URL ?>/admin/buku/buku_admin.php">
                        <i class="fas fa-book-open"></i> Kelola Buku
                    </a>
                    <a href="<?= BASE_URL ?>/admin/peminjaman/peminjaman_admin.php">
                        <i class="fas fa-clipboard-list"></i> Kelola Peminjaman
                    </a>
                <?php endif; ?>

                <div class="mobile-menu-divider"></div>
                <a href="<?= BASE_URL ?>/auth/proses_logout.php" class="mobile-logout-btn">
                    <i class="fas fa-sign-out-alt"></i> Keluar
                </a>
            <?php else: ?>
                <div class="mobile-menu-divider"></div>
                <a href="<?= BASE_URL ?>/auth/login.php" class="mobile-login-btn">
                    <i class="fas fa-sign-in-alt"></i> Masuk
                </a>
                <a href="<?= BASE_URL ?>/auth/register.php" class="mobile-register-btn">
                    <i class="fas fa-user-plus"></i> Daftar
                </a>
            <?php endif; ?>
        </div>
    </nav>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // ====================== VARIABLES ======================
            const mobileMenuToggle = document.getElementById('mobileMenuToggle');
            const mobileMenuClose = document.getElementById('mobileMenuClose');
            const mobileMenuOverlay = document.getElementById('mobileMenuOverlay');
            const mobileMenu = document.getElementById('mobileMenu');
            const dropdownToggle = document.getElementById('dropdownToggle');
            const dropdownMenu = document.getElementById('dropdownMenu');
            const header = document.querySelector('header');

            // ====================== MOBILE MENU TOGGLE ======================
            if (mobileMenuToggle) {
                mobileMenuToggle.addEventListener('click', function() {
                    mobileMenuOverlay.classList.add('show');
                    mobileMenu.classList.add('show');
                    document.body.style.overflow = 'hidden';
                });
            }

            function closeMobileMenu() {
                mobileMenuOverlay.classList.remove('show');
                mobileMenu.classList.remove('show');
                document.body.style.overflow = '';
            }

            if (mobileMenuClose) {
                mobileMenuClose.addEventListener('click', closeMobileMenu);
            }

            if (mobileMenuOverlay) {
                mobileMenuOverlay.addEventListener('click', closeMobileMenu);
            }

            // ====================== USER DROPDOWN FUNCTIONALITY ======================
            if (dropdownToggle && dropdownMenu) {
                // Toggle dropdown on click
                dropdownToggle.addEventListener('click', function(e) {
                    e.stopPropagation();
                    dropdownMenu.classList.toggle('show');
                });

                // Close dropdown when clicking outside
                document.addEventListener('click', function(e) {
                    if (!e.target.closest('.user-dropdown')) {
                        dropdownMenu.classList.remove('show');
                    }
                });
            }

            // ====================== SMOOTH SCROLLING ======================
            document.querySelectorAll('.mobile-menu-items a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function(e) {
                    e.preventDefault();
                    const target = document.querySelector(this.getAttribute('href'));

                    if (target) {
                        target.scrollIntoView({
                            behavior: 'smooth'
                        });
                        closeMobileMenu();
                    }
                });
            });

            // ====================== STICKY HEADER ======================
            if (header) {
                window.addEventListener('scroll', function() {
                    header.classList.toggle('sticky', window.scrollY > 0);
                });
            }

            // ====================== RESPONSIVE ADJUSTMENTS ======================
            function handleResponsive() {
                if (dropdownMenu) {
                    if (window.innerWidth <= 1150) {
                        // Mobile view adjustments
                        dropdownMenu.style.position = 'static';
                        dropdownMenu.style.width = '100%';
                        dropdownMenu.style.boxShadow = 'none';
                        dropdownMenu.style.borderRadius = '0';
                    } else {
                        // Desktop view adjustments
                        dropdownMenu.style.position = 'absolute';
                        dropdownMenu.style.width = 'auto';
                        dropdownMenu.style.boxShadow = '0 8px 16px rgba(0, 0, 0, 0.1)';
                        dropdownMenu.style.borderRadius = '8px';
                    }
                }
            }

            // Initialize and add resize listener
            handleResponsive();
            window.addEventListener('resize', handleResponsive);
        });

        // ====================== GLOBAL CLICK HANDLER ======================
        // For closing dropdowns when clicking anywhere on the page
        window.addEventListener('click', function(e) {
            if (!e.target.closest('.user-dropdown')) {
                document.querySelectorAll('.dropdown-content').forEach(dropdown => {
                    dropdown.classList.remove('show');
                });
            }
        });
    </script>
</body>

</html>