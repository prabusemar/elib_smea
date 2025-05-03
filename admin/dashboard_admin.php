<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: ../auth/login.php");
    exit;
}

$username = $_SESSION['username'];
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Perpustakaan Digital</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        :root {
            --primary: #3a0ca3;
            --primary-light: #4361ee;
            --secondary: #f72585;
            --light: #f8f9fa;
            --dark: #212529;
            --gray: #6c757d;
            --sidebar-width: 250px;
            --sidebar-collapsed-width: 80px;
            --header-height: 70px;
        }

        /* Base Styles */
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f5f7fa;
            color: var(--dark);
        }

        /* Layout Structure */
        .main-content {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            transition: all 0.3s ease;
        }

        .sidebar.collapsed~.main-content {
            margin-left: var(--sidebar-collapsed-width);
        }

        .header {
            height: var(--header-height);
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 2rem;
            background-color: white;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            position: sticky;
            top: 0;
            z-index: 90;
        }

        .content-container {
            padding: 2rem;
        }

        .header h1 {
            color: var(--dark);
            font-size: 1.3rem;
            font-weight: 600;
            white-space: nowrap;
        }

        .header-actions {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .search-bar {
            position: relative;
        }

        .search-bar input {
            padding: 0.5rem 1rem 0.5rem 2.3rem;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            font-size: 0.9rem;
            width: 200px;
            transition: all 0.3s ease;
        }

        .search-bar input:focus {
            outline: none;
            border-color: var(--primary-light);
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.1);
        }

        .search-bar i {
            position: absolute;
            left: 0.8rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--gray);
            font-size: 0.9rem;
        }

        .greeting-card {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            margin-bottom: 1.5rem;
            border-left: 4px solid var(--primary);
        }

        .greeting-card h2 {
            color: var(--primary);
            margin-bottom: 0.5rem;
            font-size: 1.3rem;
        }

        .greeting-card p {
            color: var(--gray);
            line-height: 1.6;
            max-width: 100%;
            font-size: 0.95rem;
        }

        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
            gap: 1.2rem;
            margin-bottom: 1.5rem;
        }

        .card {
            background: white;
            border-radius: 10px;
            padding: 1.2rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .card:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.1);
        }

        .card h3 {
            color: var(--primary);
            margin-bottom: 0.8rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 1rem;
        }

        .card h3 i {
            font-size: 0.9rem;
        }

        .card p {
            color: var(--gray);
            line-height: 1.5;
            font-size: 0.9rem;
        }

        .card .stat {
            font-size: 1.6rem;
            font-weight: 700;
            color: var(--primary);
            margin: 0.8rem 0;
        }

        .recent-activity {
            background: white;
            border-radius: 10px;
            padding: 1.2rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        }

        .recent-activity h3 {
            color: var(--primary);
            margin-bottom: 1.2rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 1.1rem;
        }

        .activity-item {
            display: flex;
            align-items: flex-start;
            gap: 0.8rem;
            padding: 0.8rem 0;
            border-bottom: 1px solid #f1f1f1;
        }

        .activity-item:last-child {
            border-bottom: none;
        }

        .activity-icon {
            background-color: #f0f4ff;
            color: var(--primary);
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            font-size: 0.9rem;
        }

        .activity-content {
            flex: 1;
        }

        .activity-content p {
            margin-bottom: 0.2rem;
            color: var(--dark);
            font-size: 0.9rem;
        }

        .activity-time {
            font-size: 0.75rem;
            color: var(--gray);
        }

        /* Responsive Breakpoints */
        @media (max-width: 1200px) {
            .dashboard-grid {
                grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            }
        }

        @media (max-width: 992px) {
            .main-content {
                margin-left: var(--sidebar-collapsed-width);
            }

            .header {
                padding: 0 1rem;
            }

            .content-container {
                padding: 1.5rem;
            }
        }

        @media (max-width: 768px) {
            .header {
                padding: 0 1rem;
                flex-direction: column;
                height: auto;
                padding: 1rem;
                gap: 1rem;
            }

            .header h1 {
                font-size: 1.2rem;
                width: 100%;
            }

            .header-actions {
                width: 100%;
                justify-content: space-between;
            }

            .search-bar input {
                width: 100%;
                max-width: 200px;
            }

            .content-container {
                padding: 1rem;
            }

            .greeting-card {
                padding: 1.2rem;
            }

            .greeting-card h2 {
                font-size: 1.2rem;
            }

            .dashboard-grid {
                grid-template-columns: 1fr 1fr;
                gap: 1rem;
            }

            .card {
                padding: 1rem;
            }

            .card h3 {
                font-size: 0.95rem;
            }

            .card .stat {
                font-size: 1.4rem;
            }
        }

        @media (max-width: 576px) {
            .dashboard-grid {
                grid-template-columns: 1fr;
            }

            .search-bar input {
                max-width: 160px;
                padding-left: 2rem;
            }

            .greeting-card h2 {
                font-size: 1.1rem;
            }

            .greeting-card p {
                font-size: 0.9rem;
            }

            .recent-activity h3 {
                font-size: 1rem;
            }

            .activity-item {
                gap: 0.6rem;
            }

            .activity-icon {
                width: 32px;
                height: 32px;
                font-size: 0.8rem;
            }

            .activity-content p {
                font-size: 0.85rem;
            }

            .activity-time {
                font-size: 0.7rem;
            }

            .content-container {
                padding: 1rem;
            }

            .card {
                padding: 1rem;
            }
        }

        @media (max-width: 400px) {
            .header-actions {
                flex-direction: column;
                gap: 0.8rem;
                align-items: flex-start;
            }

            .search-bar {
                width: 100%;
            }

            .search-bar input {
                max-width: 100%;
            }

            .content-container {
                padding: 1rem;
            }

            .card {
                padding: 1rem;
            }


        }
    </style>
</head>

<body>
    <?php include '../views/sidebar_admin.php'; ?>

    <!-- Main Content -->
    <main class="main-content">
        <div class="header">
            <h1>Dashboard Admin</h1>
            <div class="header-actions">
                <div class="search-bar">
                    <i class="fas fa-search"></i>
                    <input type="text" placeholder="Cari...">
                </div>
            </div>
        </div>

        <div class="content-container">
            <div class="greeting-card">
                <h2>Selamat datang, Admin <?= htmlspecialchars($username) ?>!</h2>
                <p>Anda memiliki 5 buku baru yang perlu diverifikasi, 3 permohonan peminjaman, dan 2 laporan yang harus ditinjau.</p>
            </div>

            <div class="dashboard-grid">
                <div class="card">
                    <h3><i class="fas fa-book"></i> Total Buku</h3>
                    <div class="stat">1,245</div>
                    <p>12 buku baru ditambahkan minggu ini</p>
                </div>
                <div class="card">
                    <h3><i class="fas fa-users"></i> Pengguna</h3>
                    <div class="stat">586</div>
                    <p>5 pengguna baru bergabung hari ini</p>
                </div>
                <div class="card">
                    <h3><i class="fas fa-exchange-alt"></i> Transaksi</h3>
                    <div class="stat">78</div>
                    <p>3 transaksi menunggu persetujuan</p>
                </div>
                <div class="card">
                    <h3><i class="fas fa-clock"></i> Pending</h3>
                    <div class="stat">15</div>
                    <p>Tugas yang memerlukan tindakan</p>
                </div>
            </div>

            <div class="recent-activity">
                <h3><i class="fas fa-bell"></i> Aktivitas Terkini</h3>
                <div class="activity-item">
                    <div class="activity-icon">
                        <i class="fas fa-book"></i>
                    </div>
                    <div class="activity-content">
                        <p>Buku baru "Pemrograman Web Modern" ditambahkan oleh staf</p>
                        <div class="activity-time">10 menit yang lalu</div>
                    </div>
                </div>
                <div class="activity-item">
                    <div class="activity-icon">
                        <i class="fas fa-user-plus"></i>
                    </div>
                    <div class="activity-content">
                        <p>User baru "dian_sari" berhasil mendaftar</p>
                        <div class="activity-time">1 jam yang lalu</div>
                    </div>
                </div>
                <div class="activity-item">
                    <div class="activity-icon">
                        <i class="fas fa-exchange-alt"></i>
                    </div>
                    <div class="activity-content">
                        <p>Peminjaman buku "Belajar PHP" oleh user "andi_123"</p>
                        <div class="activity-time">3 jam yang lalu</div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</body>

</html>