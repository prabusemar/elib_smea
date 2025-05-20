<?php
// Pastikan config.php sudah di-include di halaman yang memanggil navbar
if (!defined('BASE_URL')) {
    die('BASE_URL tidak didefinisikan. Pastikan config.php di-include');
}
?>

<header>
    <div class="logo-container">
        <img src="https://cdn-icons-png.flaticon.com/512/3565/3565418.png" alt="Logo Perpus" />
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
        <a href="<?= BASE_URL ?>/auth/login.php" class="btn btn-outline">
            <i class="fas fa-sign-in-alt"></i> Masuk
        </a>
        <a href="<?= BASE_URL ?>/auth/register.php" class="btn btn-primary">
            <i class="fas fa-user-plus"></i> Daftar
        </a>
    </div>

    <button class="mobile-menu-btn">
        <i class="fas fa-bars"></i>
    </button>
</header>

<style>
    /* Header & Navigation */
    header {
        background-color: white;
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

    .logo-container {
        display: flex;
        align-items: center;
        gap: 1rem;
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
    }

    .logo-text span {
        font-size: 0.8rem;
        color: var(--gray);
    }

    .nav-menu {
        display: flex;
        gap: 2rem;
    }

    .nav-menu a {
        text-decoration: none;
        color: var(--dark);
        font-weight: 500;
        position: relative;
        padding: 0.5rem 0;
        transition: color 0.3s ease;
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

    .nav-actions {
        display: flex;
        align-items: center;
        gap: 1rem;
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
        color: white;
    }

    .btn-primary:hover {
        background-color: var(--primary-light);
        transform: translateY(-3px);
        box-shadow: 0 5px 15px rgba(67, 97, 238, 0.3);
    }

    .btn-outline {
        background-color: transparent;
        color: var(--primary);
        border: 2px solid var(--primary);
    }

    .btn-outline:hover {
        background-color: var(--primary);
        color: white;
        transform: translateY(-3px);
    }

    .mobile-menu-btn {
        display: none;
        background: none;
        border: none;
        font-size: 1.5rem;
        color: var(--primary);
        cursor: pointer;
    }

    /* Responsive Design */
    @media (max-width: 1150px) {
        .nav-menu {
            position: fixed;
            top: 80px;
            left: 0;
            width: 100%;
            background-color: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            flex-direction: column;
            align-items: center;
            padding: 2rem 0 0 0;
            box-shadow: 0 10px 15px rgba(0, 0, 0, 0.1);
            transform: translateY(-150%);
            transition: transform 0.3s ease;
            z-index: 999;
            gap: 1.5rem;
            display: none;
        }

        .nav-menu.active {
            transform: translateY(0);
            display: flex;
        }

        .mobile-menu-btn {
            display: block;
            z-index: 1001;
        }

        .nav-actions {
            display: flex;
            flex-direction: row;
            justify-content: center;
            gap: 1rem;
            width: 100%;
            margin: 1.5rem 0 1rem 0;
            position: fixed;
            left: 0;
            background: white;
            z-index: 999;
            padding-bottom: 1.5rem;
            transition: transform 0.3s ease;
            transform: translateY(-150%);
            box-shadow: 0 10px 15px rgba(0, 0, 0, 0.05);
            background-color: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
        }

        .nav-menu.active~.nav-actions {
            transform: translateY(0);
            display: flex;
        }

        header {
            flex-wrap: wrap;
        }
    }
</style>

<script>
    // Mobile Menu Toggle
    document.querySelector('.mobile-menu-btn').addEventListener('click', function() {
        document.querySelector('.nav-menu').classList.toggle('active');
    });

    // Smooth Scrolling for Anchor Links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();

            document.querySelector(this.getAttribute('href')).scrollIntoView({
                behavior: 'smooth'
            });

            // Close mobile menu if open
            document.querySelector('.nav-menu').classList.remove('active');
        });
    });

    // Sticky Header
    window.addEventListener('scroll', function() {
        const header = document.querySelector('header');
        header.classList.toggle('sticky', window.scrollY > 0);
    });

    // Tab Functionality
    const tabButtons = document.querySelectorAll('.tab-btn');
    tabButtons.forEach(button => {
        button.addEventListener('click', () => {
            tabButtons.forEach(btn => btn.classList.remove('active'));
            button.classList.add('active');

        });
    });
</script>