<?php
// Check if user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

$username = $_SESSION['username'];

// Get current page filename
$current_page = basename($_SERVER['PHP_SELF']);
?>

<aside class="sidebar">
    <div class="sidebar-header">
        <h3>SMEA E-lib</h3>
        <button class="toggle-btn">
            <i class="fas fa-chevron-left"></i>
        </button>
    </div>

    <div class="user-info">
        <div class="avatar">
            <?php echo strtoupper(substr($username, 0, 1)); ?>
        </div>
        <div class="user-details">
            <div class="username"><?php echo htmlspecialchars($username); ?></div>
            <div class="role">Admin</div>
        </div>
    </div>

    <nav class="nav-menu">
        <!-- Dashboard -->
        <a href="/library/admin/dashboard_admin.php" class="nav-item <?php echo ($current_page == 'dashboard_admin.php') ? 'active' : ''; ?>">
            <div class="tooltip">
                <i class="fas fa-tachometer-alt"></i>
                <span>Dashboard</span>
                <span class="tooltiptext">Dashboard</span>
            </div>
        </a>

        <!-- Content Management -->
        <div class="menu-section">
            <div class="section-header">
                <div class="tooltip">
                    <i class="fas fa-book-open"></i>
                    <span>Manajemen Konten</span>
                    <span class="tooltiptext">Manajemen Konten</span>
                </div>
            </div>
            <a href="/library/admin/buku/buku_admin.php" class="nav-item <?php echo ($current_page == 'buku_admin.php') ? 'active' : ''; ?>">
                <div class="tooltip">
                    <i class="fas fa-book"></i>
                    <span>E-Books</span>
                    <span class="tooltiptext">E-Books</span>
                </div>
            </a>
            <a href="/library/admin/kategori/kategori_admin.php" class="nav-item <?php echo ($current_page == 'kategori_admin.php') ? 'active' : ''; ?>">
                <div class="tooltip">
                    <i class="fas fa-tags"></i>
                    <span>Kategori</span>
                    <span class="tooltiptext">Kategori</span>
                </div>
            </a>
            <a href="/library/admin/koleksi.php" class="nav-item <?php echo ($current_page == 'koleksi.php') ? 'active' : ''; ?>">
                <div class="tooltip">
                    <i class="fas fa-layer-group"></i>
                    <span>Koleksi</span>
                    <span class="tooltiptext">Koleksi</span>
                </div>
            </a>
        </div>

        <!-- User Management -->
        <div class="menu-section">
            <div class="section-header">
                <div class="tooltip">
                    <i class="fas fa-users-cog"></i>
                    <span>Manajemen Pengguna</span>
                    <span class="tooltiptext">Manajemen Pengguna</span>
                </div>
            </div>
            <a href="/library/admin/anggota.php" class="nav-item <?php echo ($current_page == 'anggota.php') ? 'active' : ''; ?>">
                <div class="tooltip">
                    <i class="fas fa-users"></i>
                    <span>Anggota</span>
                    <span class="tooltiptext">Anggota</span>
                </div>
            </a>
            <a href="/library/admin/staff.php" class="nav-item <?php echo ($current_page == 'staff.php') ? 'active' : ''; ?>">
                <div class="tooltip">
                    <i class="fas fa-user-shield"></i>
                    <span>Staff</span>
                    <span class="tooltiptext">Staff</span>
                </div>
            </a>
            <a href="/library/admin/grup_membaca.php" class="nav-item <?php echo ($current_page == 'grup_membaca.php') ? 'active' : ''; ?>">
                <div class="tooltip">
                    <i class="fas fa-user-friends"></i>
                    <span>Grup Membaca</span>
                    <span class="tooltiptext">Grup Membaca</span>
                </div>
            </a>
        </div>

        <!-- Transaction Management -->
        <div class="menu-section">
            <div class="section-header">
                <div class="tooltip">
                    <i class="fas fa-exchange-alt"></i>
                    <span>Transaksi</span>
                    <span class="tooltiptext">Transaksi</span>
                </div>
            </div>
            <a href="/library/admin/peminjaman.php" class="nav-item <?php echo ($current_page == 'peminjaman.php') ? 'active' : ''; ?>">
                <div class="tooltip">
                    <i class="fas fa-book-reader"></i>
                    <span>Peminjaman</span>
                    <span class="tooltiptext">Peminjaman</span>
                </div>
            </a>
            <a href="/library/admin/riwayat_baca.php" class="nav-item <?php echo ($current_page == 'riwayat_baca.php') ? 'active' : ''; ?>">
                <div class="tooltip">
                    <i class="fas fa-history"></i>
                    <span>Riwayat Baca</span>
                    <span class="tooltiptext">Riwayat Baca</span>
                </div>
            </a>
            <a href="/library/admin/favorit.php" class="nav-item <?php echo ($current_page == 'favorit.php') ? 'active' : ''; ?>">
                <div class="tooltip">
                    <i class="fas fa-heart"></i>
                    <span>Favorit</span>
                    <span class="tooltiptext">Favorit</span>
                </div>
            </a>
        </div>

        <!-- Reports & Analytics -->
        <div class="menu-section">
            <div class="section-header">
                <div class="tooltip">
                    <i class="fas fa-chart-line"></i>
                    <span>Analitik & Laporan</span>
                    <span class="tooltiptext">Analitik & Laporan</span>
                </div>
            </div>
            <a href="/library/admin/laporan.php" class="nav-item <?php echo ($current_page == 'laporan.php') ? 'active' : ''; ?>">
                <div class="tooltip">
                    <i class="fas fa-file-alt"></i>
                    <span>Laporan</span>
                    <span class="tooltiptext">Laporan</span>
                </div>
            </a>
            <a href="/library/admin/statistik.php" class="nav-item <?php echo ($current_page == 'statistik.php') ? 'active' : ''; ?>">
                <div class="tooltip">
                    <i class="fas fa-chart-pie"></i>
                    <span>Statistik</span>
                    <span class="tooltiptext">Statistik</span>
                </div>
            </a>
            <a href="/library/admin/pencarian.php" class="nav-item <?php echo ($current_page == 'pencarian.php') ? 'active' : ''; ?>">
                <div class="tooltip">
                    <i class="fas fa-search"></i>
                    <span>Trend Pencarian</span>
                    <span class="tooltiptext">Trend Pencarian</span>
                </div>
            </a>
        </div>

        <!-- System -->
        <div class="menu-section">
            <div class="section-header">
                <div class="tooltip">
                    <i class="fas fa-cogs"></i>
                    <span>Sistem</span>
                    <span class="tooltiptext">Sistem</span>
                </div>
            </div>
            <a href="/library/admin/pengaturan.php" class="nav-item <?php echo ($current_page == 'pengaturan.php') ? 'active' : ''; ?>">
                <div class="tooltip">
                    <i class="fas fa-wrench"></i>
                    <span>Pengaturan</span>
                    <span class="tooltiptext">Pengaturan</span>
                </div>
            </a>
            <a href="/library/admin/backup.php" class="nav-item <?php echo ($current_page == 'backup.php') ? 'active' : ''; ?>">
                <div class="tooltip">
                    <i class="fas fa-database"></i>
                    <span>Backup Data</span>
                    <span class="tooltiptext">Backup Data</span>
                </div>
            </a>
            <a href="/library/admin/notifikasi.php" class="nav-item <?php echo ($current_page == 'notifikasi.php') ? 'active' : ''; ?>">
                <div class="tooltip">
                    <i class="fas fa-bell"></i>
                    <span>Notifikasi</span>
                    <span class="tooltiptext">Notifikasi</span>
                </div>
            </a>
        </div>
    </nav>

    <div class="logout-container">
        <a href="/library/admin/proses_logout.php" class="logout-btn">
            <div class="tooltip">
                <i class="fas fa-sign-out-alt"></i>
                <span>Keluar</span>
                <span class="tooltiptext">Keluar</span>
            </div>
        </a>
    </div>
</aside>

<style>
    /* CSS Variables */
    :root {
        --primary: #3a0ca3;
        --primary-light: #4361ee;
        --secondary: #f72585;
        --light: #f8f9fa;
        --dark: #212529;
        --gray: #6c757d;
        --sidebar-width: 280px;
        --sidebar-collapsed-width: 80px;
        --header-height: 70px;
        --tooltip-bg: rgba(51, 51, 51, 0.95);
    }

    /* Base Styles */
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
        font-family: 'Poppins', sans-serif;
    }

    /* Main Content */
    .main-content {
        position: relative;
        z-index: 50;
        margin-left: var(--sidebar-width);
        min-height: 100vh;
        transition: margin-left 0.3s ease;
        background-color: #f5f7fa;
        padding: 20px;
    }

    .sidebar.collapsed~.main-content {
        margin-left: var(--sidebar-collapsed-width);
    }

    /* Sidebar Container */
    .sidebar {
        width: var(--sidebar-width);
        background: linear-gradient(180deg, var(--primary) 0%, #2a0a7a 100%);
        color: white;
        height: 100vh;
        position: fixed;
        transition: all 0.3s ease;
        z-index: 1000;
        box-shadow: 2px 0 15px rgba(0, 0, 0, 0.1);
        display: flex;
        flex-direction: column;
        overflow: visible;
    }

    .sidebar.collapsed {
        width: var(--sidebar-collapsed-width);
    }

    /* Sidebar Header */
    .sidebar-header {
        height: var(--header-height);
        padding: 1rem;
        display: flex;
        align-items: center;
        justify-content: center;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        position: relative;
        flex-shrink: 0;
    }

    .sidebar-header h3 {
        font-size: 1.3rem;
        white-space: nowrap;
        opacity: 1;
        transition: opacity 0.3s ease;
    }

    .sidebar.collapsed .sidebar-header h3 {
        opacity: 0;
        width: 0;
    }

    /* Toggle Button */
    .toggle-btn {
        position: absolute;
        right: -15px;
        top: 20px;
        background: white;
        border: none;
        width: 32px;
        height: 32px;
        border-radius: 50%;
        color: var(--primary);
        font-size: 1rem;
        cursor: pointer;
        box-shadow: 0 3px 10px rgba(0, 0, 0, 0.3);
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s ease;
        z-index: 110;
        border: 2px solid var(--primary);
    }

    .sidebar.collapsed .toggle-btn {
        transform: rotate(180deg);
    }

    .toggle-btn:hover {
        transform: scale(1.1);
    }

    .sidebar.collapsed .toggle-btn:hover {
        transform: rotate(180deg) scale(1.1);
    }

    /* User Info Section */
    .user-info {
        padding: 1.5rem 1rem;
        display: flex;
        align-items: center;
        gap: 1rem;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        flex-shrink: 0;
    }

    .user-info .avatar {
        width: 40px;
        height: 40px;
        background-color: var(--secondary);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        color: white;
        flex-shrink: 0;
    }

    .user-info .user-details {
        white-space: nowrap;
        overflow: hidden;
        transition: all 0.3s ease;
    }

    .sidebar.collapsed .user-details {
        opacity: 0;
        width: 0;
    }

    .user-info .username {
        font-weight: 600;
        margin-bottom: 0.2rem;
        font-size: 0.95rem;
    }

    .user-info .role {
        font-size: 0.75rem;
        opacity: 0.8;
        background-color: rgba(255, 255, 255, 0.2);
        padding: 0.2rem 0.5rem;
        border-radius: 10px;
        display: inline-block;
    }

    /* Navigation Menu */
    .nav-menu {
        flex: 1;
        overflow-y: auto;
        padding: 1rem 0;
        scrollbar-width: none;
        -ms-overflow-style: none;
    }

    .nav-menu::-webkit-scrollbar {
        display: none;
    }

    .nav-item {
        padding: 0.8rem 1.5rem;
        display: flex;
        align-items: center;
        gap: 1rem;
        color: rgba(255, 255, 255, 0.8);
        text-decoration: none;
        transition: all 0.3s ease;
        white-space: nowrap;
        font-size: 0.95rem;
        position: relative;
        overflow: visible;
    }

    .nav-item:hover,
    .nav-item.active {
        background-color: rgba(255, 255, 255, 0.1);
        color: white;
    }

    .nav-item.active {
        background-color: rgba(255, 255, 255, 0.2);
    }

    .nav-item i {
        font-size: 1.1rem;
        flex-shrink: 0;
        width: 24px;
        text-align: center;
    }

    .sidebar.collapsed .nav-item span {
        opacity: 0;
        width: 0;
    }

    /* Logout Section */
    .logout-container {
        padding: 1rem;
        flex-shrink: 0;
        background-color: rgb(97, 10, 10);
    }

    .logout-btn {
        display: flex;
        align-items: center;
        gap: 1rem;
        color: white;
        text-decoration: none;
        padding: 0.6rem 1rem;
        border-radius: 5px;
        transition: all 0.3s ease;
        white-space: nowrap;
        font-size: 0.95rem;
        overflow: visible;
    }

    .logout-btn:hover {
        background-color: rgba(255, 255, 255, 0.1);
    }

    .sidebar.collapsed .logout-btn span {
        opacity: 0;
        width: 0;
    }

    /* Menu Sections */
    .menu-section {
        margin-bottom: 1rem;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    }

    .section-header {
        padding: 0.8rem 1.5rem;
        display: flex;
        align-items: center;
        gap: 1rem;
        color: rgba(255, 255, 255, 0.6);
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 1px;
        position: relative;
        overflow: visible;
    }

    .section-header i {
        font-size: 0.9rem;
        width: 24px;
        text-align: center;
    }

    /* Collapsed State */
    .sidebar.collapsed .section-header span,
    .sidebar.collapsed .section-header i {
        opacity: 0;
        width: 0;
        display: none;
    }

    .sidebar.collapsed .menu-section {
        border-bottom: none;
    }

    /* Tooltip Styles - Fixed */
    .tooltip {
        position: relative;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .tooltip .tooltiptext {
        visibility: hidden;
        width: auto;
        background-color: var(--tooltip-bg);
        color: #fff;
        text-align: center;
        padding: 8px 12px;
        border-radius: 6px;
        position: fixed;
        z-index: 1100;
        opacity: 0;
        transition: opacity 0.2s ease;
        white-space: nowrap;
        font-size: 0.85rem;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
        pointer-events: none;
        backdrop-filter: blur(2px);
        max-width: 200px;
        line-height: 1.4;
    }

    .tooltip .tooltiptext::after {
        content: "";
        position: absolute;
        left: -10px;
        top: 50%;
        transform: translateY(-50%);
        border-width: 5px;
        border-style: solid;
        border-color: transparent transparent transparent var(--tooltip-bg);
    }

    .sidebar.collapsed .tooltip:hover .tooltiptext {
        visibility: visible;
        opacity: 1;
    }

    /* Adjust collapsed items */
    .sidebar.collapsed .nav-item {
        justify-content: center;
        padding: 0.8rem 0;
    }

    .sidebar.collapsed .section-header {
        justify-content: center;
        padding: 0.8rem 0;
    }

    .sidebar.collapsed .logout-btn {
        justify-content: center;
    }

    /* Mobile Responsiveness */
    @media (max-width: 992px) {
        .sidebar {
            transform: translateX(-100%);
            z-index: 1000;
        }

        .sidebar.active {
            transform: translateX(0);
        }

        .sidebar.collapsed {
            transform: translateX(0);
        }

        .main-content {
            margin-left: 0;
            padding-left: 20px;
        }

        .sidebar.collapsed~.main-content {
            margin-left: var(--sidebar-collapsed-width);
        }

        .toggle-btn {
            display: none;
        }

        /* Mobile menu button */
        .mobile-menu-btn {
            position: fixed;
            top: 10px;
            left: 10px;
            z-index: 1001;
            background: var(--primary);
            color: white;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
            cursor: pointer;
        }

        /* Mobile tooltip adjustments */
        .sidebar.collapsed .tooltip .tooltiptext {
            left: auto !important;
            right: calc(var(--sidebar-collapsed-width) + 15px) !important;
            top: 50% !important;
            transform: translateY(-50%) !important;
        }

        .sidebar.collapsed .tooltip .tooltiptext::after {
            right: -10px;
            left: auto;
            border-color: transparent transparent transparent var(--tooltip-bg);
        }
    }

    .fas {
        margin-right: 10px
    }

    .fa-chevron-left {
        margin-right: 0px;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // DOM Elements
        const sidebar = document.querySelector('.sidebar');
        const toggleBtn = document.querySelector('.toggle-btn');
        const tooltips = document.querySelectorAll('.tooltip');
        const mainContent = document.querySelector('.main-content');

        // Initialize sidebar state
        function initSidebarState() {
            const isMobile = window.innerWidth <= 992;
            const savedState = localStorage.getItem('sidebarCollapsed');

            if (isMobile) {
                sidebar.classList.add('collapsed');
            } else {
                if (savedState === 'true') {
                    sidebar.classList.add('collapsed');
                } else {
                    sidebar.classList.remove('collapsed');
                }
            }
        }

        // Toggle sidebar
        function toggleSidebar() {
            sidebar.classList.toggle('collapsed');
            localStorage.setItem('sidebarCollapsed', sidebar.classList.contains('collapsed'));
            setTimeout(initTooltips, 300); // Wait for transition to complete
        }

        // Initialize tooltips with perfect positioning
        function initTooltips() {
            tooltips.forEach(tooltip => {
                const tooltipText = tooltip.querySelector('.tooltiptext');

                // Clear existing event listeners
                tooltip.removeEventListener('mouseenter', showTooltip);
                tooltip.removeEventListener('mouseleave', hideTooltip);
                tooltip.removeEventListener('touchstart', handleTouch);

                if (sidebar.classList.contains('collapsed')) {
                    // Desktop hover events
                    tooltip.addEventListener('mouseenter', showTooltip);
                    tooltip.addEventListener('mouseleave', hideTooltip);

                    // Mobile touch events
                    tooltip.addEventListener('touchstart', handleTouch, {
                        passive: false
                    });
                }
            });
        }

        // Show tooltip with perfect positioning
        function showTooltip(e) {
            const tooltip = e.currentTarget || e;
            const tooltipText = tooltip.querySelector('.tooltiptext');
            const rect = tooltip.getBoundingClientRect();

            // Calculate position (right side of the icon)
            const rightPosition = window.innerWidth - rect.right - 5;
            const topPosition = rect.top + (rect.height / 2);

            // Apply styles
            tooltipText.style.cssText = `
                position: fixed;
                right: ${rightPosition}px;
                top: ${topPosition}px;
                left: 5px;
                transform: translateY(-50%);
                visibility: visible;
                opacity: 1;
                z-index: 1100;
                background-color: rgba(51, 51, 51, 0.95);
                backdrop-filter: blur(2px);
                max-width: 200px;
                padding: 8px 105px 8px 10px;
                border-radius: 6px;
                box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
            `;

            // Create arrow
            tooltipText.setAttribute('data-arrow', 'right');
        }

        // Hide tooltip
        function hideTooltip(e) {
            const tooltip = e.currentTarget || e;
            const tooltipText = tooltip.querySelector('.tooltiptext');
            tooltipText.style.visibility = 'hidden';
            tooltipText.style.opacity = '0';
        }

        // Handle touch events for mobile
        function handleTouch(e) {
            e.preventDefault();
            const tooltip = e.currentTarget;
            const tooltipText = tooltip.querySelector('.tooltiptext');
            const isVisible = tooltipText.style.visibility === 'visible';

            // Hide all other tooltips
            document.querySelectorAll('.tooltip').forEach(t => {
                if (t !== tooltip) hideTooltip(t);
            });

            // Toggle current tooltip
            if (!isVisible) {
                showTooltip(tooltip);
            } else {
                hideTooltip(tooltip);
            }
        }

        // Handle window resize with debounce
        let resizeTimer;

        function handleResize() {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(() => {
                const isMobile = window.innerWidth <= 992;

                if (isMobile) {
                    sidebar.classList.add('collapsed');
                } else {
                    const savedState = localStorage.getItem('sidebarCollapsed');
                    if (savedState === 'true') {
                        sidebar.classList.add('collapsed');
                    } else {
                        sidebar.classList.remove('collapsed');
                    }
                }

                initTooltips();
            }, 250);
        }

        // Close tooltips when clicking outside
        function handleClickOutside(e) {
            if (!e.target.closest('.tooltip') && sidebar.classList.contains('collapsed')) {
                tooltips.forEach(tooltip => {
                    hideTooltip(tooltip);
                });
            }
        }

        // Setup mobile menu toggle button
        function setupMobileMenu() {
            if (window.innerWidth > 992) return;

            const mobileMenuBtn = document.createElement('button');
            mobileMenuBtn.className = 'mobile-menu-btn';
            mobileMenuBtn.innerHTML = '<i class="fas fa-bars"></i>';
            document.body.appendChild(mobileMenuBtn);

            mobileMenuBtn.addEventListener('click', () => {
                sidebar.classList.toggle('active');
            });
        }

        // Initialize everything
        initSidebarState();
        initTooltips();
        setupMobileMenu();

        // Event Listeners
        if (toggleBtn) toggleBtn.addEventListener('click', toggleSidebar);
        window.addEventListener('resize', handleResize);
        document.addEventListener('click', handleClickOutside);

        // Add arrow styles dynamically
        const style = document.createElement('style');
        style.textContent = `
            [data-arrow="right"]::after {
                content: "";
                position: absolute;
                right: -10px;
                top: 50%;
                transform: translateY(-50%);
                border-width: 5px;
                border-style: solid;
                border-color: transparent transparent transparent rgba(51, 51, 51, 0.95);
            }
        `;
        document.head.appendChild(style);
    });
</script>