<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Perpustakaan Digital - SMEA E-Lib</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <style>
        :root {
            --primary: #3a0ca3;
            --primary-light: #4361ee;
            --secondary: #f72585;
            --accent: #4cc9f0;
            --light: #f8f9fa;
            --dark: #212529;
            --gray: #6c757d;
            --light-gray: #e9ecef;
            --success: #2ecc71;
            --warning: #f39c12;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            color: var(--dark);
            line-height: 1.6;
            background-color: #f9f9f9;
            overflow-x: hidden;
        }

        a {
            text-decoration: none;
            color: inherit;
        }

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

        /* Hero Section */
        .hero {
            background: linear-gradient(135deg, rgba(58, 12, 163, 0.9) 0%, rgba(67, 97, 238, 0.9) 100%), url('https://images.unsplash.com/photo-1507842217343-583bb7270b66?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1590&q=80') no-repeat center center/cover;
            color: white;
            padding: 10rem 5% 6rem;
            text-align: center;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            position: relative;
            overflow: hidden;
        }

        .hero-content {
            max-width: 800px;
            margin: 0 auto;
            position: relative;
            z-index: 2;
        }

        .hero h1 {
            font-size: 3.2rem;
            margin-bottom: 1.5rem;
            line-height: 1.2;
            font-weight: 700;
        }

        .hero p {
            font-size: 1.2rem;
            margin-bottom: 2.5rem;
            opacity: 0.9;
        }

        .hero-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
        }

        .hero-buttons .btn {
            padding: 0.9rem 2rem;
            font-size: 1rem;
        }

        .hero-stats {
            display: flex;
            justify-content: center;
            gap: 3rem;
            margin-top: 3rem;
            flex-wrap: wrap;
        }

        .stat-item {
            text-align: center;
        }

        .stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            color: white;
        }

        .stat-label {
            font-size: 1rem;
            opacity: 0.8;
        }

        /* Features Section */
        .features {
            padding: 6rem 5%;
            background-color: white;
        }

        .section-title {
            text-align: center;
            margin-bottom: 4rem;
        }

        .section-title h2 {
            font-size: 2.2rem;
            color: var(--primary);
            margin-bottom: 1rem;
            position: relative;
            display: inline-block;
        }

        .section-title h2::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 4px;
            background: linear-gradient(90deg, var(--primary), var(--secondary));
            border-radius: 2px;
        }

        .section-title p {
            color: var(--gray);
            max-width: 700px;
            margin: 0 auto;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin-top: 3rem;
        }

        .feature-card {
            background-color: white;
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            text-align: center;
            border: 1px solid var(--light-gray);
        }

        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.1);
        }

        .feature-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            color: white;
            font-size: 2rem;
        }

        .feature-card h3 {
            font-size: 1.3rem;
            margin-bottom: 1rem;
            color: var(--primary);
        }

        .feature-card p {
            color: var(--gray);
        }

        /* Book Collections */
        .collections {
            padding: 6rem 5%;
            background-color: var(--light);
        }

        .collections-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .collections-header h2 {
            font-size: 2rem;
            color: var(--primary);
        }

        .collection-tabs {
            display: flex;
            gap: 0.5rem;
            background-color: var(--light-gray);
            padding: 0.5rem;
            border-radius: 50px;
        }

        .tab-btn {
            padding: 0.6rem 1.5rem;
            border-radius: 50px;
            background: none;
            border: none;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .tab-btn.active {
            background-color: var(--primary);
            color: white;
        }

        .books-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 2rem;
            margin-top: 2rem;
        }

        .book-card {
            background-color: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            position: relative;
            min-height: 510px;
            /* Tambahkan tinggi minimum agar card lebih tinggi */
        }

        .book-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }

        .book-cover {
            width: 100%;
            height: 280px;
            object-fit: cover;
        }

        .book-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            background-color: var(--secondary);
            color: white;
            padding: 0.3rem 0.8rem;
            border-radius: 50px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .book-badge.premium {
            background-color: var(--warning);
        }

        .book-details {
            padding: 1.2rem;
        }

        .book-details h3 {
            font-size: 1.1rem;
            margin-bottom: 0.5rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .book-details .author {
            color: var(--gray);
            font-size: 0.9rem;
            margin-bottom: 0.8rem;
        }

        .book-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.85rem;
        }

        .book-rating {
            color: var(--warning);
        }

        .book-actions {
            display: flex;
            gap: 0.5rem;
            margin-top: 1rem;
        }

        .book-actions .btn {
            padding: 0.5rem 1rem;
            font-size: 0.85rem;
            flex: 1;
            text-align: center;
        }

        .view-all {
            text-align: center;
            margin-top: 3rem;
        }

        /* Testimonials */
        .testimonials {
            padding: 6rem 5%;
            background-color: white;
        }

        .testimonials-slider {
            max-width: 1000px;
            margin: 3rem auto 0;
            position: relative;
        }

        .testimonial-card {
            background-color: white;
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
            margin: 1rem;
            border: 1px solid var(--light-gray);
            position: relative;
        }

        .testimonial-card::before {
            content: '"';
            position: absolute;
            top: 20px;
            left: 20px;
            font-size: 5rem;
            color: var(--light-gray);
            font-family: serif;
            line-height: 1;
            z-index: 0;
            opacity: 0.5;
        }

        .testimonial-content {
            position: relative;
            z-index: 1;
            margin-bottom: 1.5rem;
            font-style: italic;
            color: var(--dark);
        }

        .testimonial-author {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .author-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
        }

        .author-info h4 {
            font-size: 1rem;
            margin-bottom: 0.2rem;
        }

        .author-info p {
            font-size: 0.85rem;
            color: var(--gray);
        }

        .slick-dots {
            display: flex;
            justify-content: center;
            margin-top: 2rem;
            list-style: none;
        }

        .slick-dots li {
            margin: 0 0.3rem;
        }

        .slick-dots button {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background-color: var(--light-gray);
            border: none;
            font-size: 0;
            padding: 0;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .slick-dots .slick-active button {
            background-color: var(--primary);
            transform: scale(1.2);
        }

        /* Pricing Section */
        .pricing {
            padding: 6rem 5%;
            background-color: var(--light);
        }

        .pricing-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin-top: 3rem;
            max-width: 1000px;
            margin-left: auto;
            margin-right: auto;
        }

        .pricing-card {
            background-color: white;
            border-radius: 15px;
            padding: 2.5rem 2rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            text-align: center;
            border: 1px solid var(--light-gray);
            position: relative;
            overflow: hidden;
        }

        .pricing-card.popular {
            border: 2px solid var(--primary);
        }

        .popular-badge {
            position: absolute;
            top: 0px;
            right: -10px;
            background: linear-gradient(90deg, var(--primary), var(--primary-light));
            color: #fff;
            padding: 0.4rem 2.2rem;
            font-size: 0.85rem;
            font-weight: 700;
            border-radius: 30px;
            box-shadow: 0 2px 8px rgba(67, 97, 238, 0.12);
            letter-spacing: 1px;
            z-index: 2;
        }

        .pricing-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.1);
        }

        .pricing-card h3 {
            font-size: 1.5rem;
            margin-bottom: 1rem;
            color: var(--primary);
        }

        .price {
            font-size: 3rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
            color: var(--dark);
        }

        .price span {
            font-size: 1rem;
            font-weight: normal;
            color: var(--gray);
        }

        .pricing-features {
            margin-bottom: 2rem;
            text-align: left;
        }

        .pricing-features li {
            margin-bottom: 0.8rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .pricing-features i {
            color: var(--success);
        }

        /* Newsletter */
        .newsletter {
            padding: 6rem 5%;
            background: linear-gradient(135deg, rgba(58, 12, 163, 0.9) 0%, rgba(67, 97, 238, 0.9) 100%), url('https://images.unsplash.com/photo-1524995997946-a1c2e315a42f?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1470&q=80') no-repeat center center/cover;
            color: white;
            text-align: center;
        }

        .newsletter .section-title h2,
        .newsletter .section-title p {
            color: white;
        }

        .newsletter-form {
            max-width: 600px;
            margin: 2rem auto 0;
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
            justify-content: center;
        }

        .newsletter-form input {
            flex: 1;
            min-width: 300px;
            padding: 1rem 1.5rem;
            border-radius: 50px;
            border: none;
            font-size: 1rem;
        }

        .newsletter-form input:focus {
            outline: none;
            box-shadow: 0 0 0 3px rgba(255, 255, 255, 0.3);
        }

        .newsletter-form .btn {
            padding: 1rem 2.5rem;
            font-size: 1rem;
            border-radius: 50px;
        }

        /* Footer */
        footer {
            background-color: var(--dark);
            color: white;
            padding: 4rem 5% 2rem;
        }

        .footer-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 3rem;
            margin-bottom: 3rem;
        }

        .footer-logo {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .footer-logo img {
            width: 150px;
        }

        .footer-logo p {
            color: var(--gray);
            font-size: 0.9rem;
        }

        .social-links {
            display: flex;
            gap: 1rem;
            margin-top: 1rem;
        }

        .social-links a {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: rgba(255, 255, 255, 0.1);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            transition: all 0.3s ease;
        }

        .social-links a:hover {
            background-color: var(--primary);
            transform: translateY(-3px);
        }

        .footer-links h3 {
            font-size: 1.2rem;
            margin-bottom: 1.5rem;
            position: relative;
            padding-bottom: 0.5rem;
        }

        .footer-links h3::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 40px;
            height: 2px;
            background-color: var(--primary);
        }

        .footer-links ul {
            list-style: none;
        }

        .footer-links li {
            margin-bottom: 0.8rem;
        }

        .footer-links a {
            color: var(--gray);
            text-decoration: none;
            transition: color 0.3s ease;
            font-size: 0.9rem;
        }

        .footer-links a:hover {
            color: white;
        }

        .footer-contact p {
            display: flex;
            align-items: flex-start;
            gap: 0.8rem;
            margin-bottom: 1rem;
            font-size: 0.9rem;
            color: var(--gray);
        }

        .footer-contact i {
            margin-top: 0.2rem;
            color: var(--primary);
        }

        .footer-bottom {
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            padding-top: 2rem;
            text-align: center;
            color: var(--gray);
            font-size: 0.9rem;
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

            .hero h1 {
                font-size: 2.5rem;
            }

            .hero p {
                font-size: 1.1rem;
            }

            .hero-buttons {
                flex-direction: column;
                align-items: center;
            }

            .hero-buttons .btn {
                width: 100%;
                max-width: 300px;
            }
        }

        @media (max-width: 768px) {
            .hero {
                padding: 8rem 5% 4rem;
            }

            .hero h1 {
                font-size: 2rem;
            }

            .section-title h2 {
                font-size: 1.8rem;
            }

            .price {
                font-size: 2.5rem;
            }
        }

        @media (max-width: 576px) {
            .logo-text h1 {
                font-size: 1.2rem;
            }

            .hero h1 {
                font-size: 1.8rem;
            }

            .hero p {
                font-size: 1rem;
            }

            .stat-number {
                font-size: 2rem;
            }

            .section-title h2 {
                font-size: 1.5rem;
            }

            .pricing-grid {
                grid-template-columns: 1fr;
            }

            .newsletter-form input {
                min-width: 100%;
            }
        }

        /* Animations */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .fade-in {
            animation: fadeIn 1s ease forwards;
        }

        .delay-1 {
            animation-delay: 0.2s;
        }

        .delay-2 {
            animation-delay: 0.4s;
        }

        .delay-3 {
            animation-delay: 0.6s;
        }

        .delay-4 {
            animation-delay: 0.8s;
        }

        /* Scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-track {
            background: var(--light-gray);
        }

        ::-webkit-scrollbar-thumb {
            background: var(--primary);
            border-radius: 10px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: var(--primary-light);
        }
    </style>
</head>

<body>
    <!-- Header & Navigation -->
    <header>
        <div class="logo-container">
            <img src="https://cdn-icons-png.flaticon.com/512/3565/3565418.png" alt="Logo Perpus" />
            <div class="logo-text">
                <h1>SMEA E-Lib</h1>
                <span>Perpustakaan Digital Modern</span>
            </div>
        </div>

        <nav class="nav-menu">
            <a href="#home">Beranda</a>
            <a href="#features">Fitur</a>
            <a href="#collections">Koleksi</a>
            <a href="#testimonials">Testimoni</a>
            <a href="#pricing">Langganan</a>
        </nav>

        <div class="nav-actions">
            <a href="auth/login.php" class="btn btn-outline">
                <i class="fas fa-sign-in-alt"></i> Masuk
            </a>
            <a href="auth/register.php" class="btn btn-primary">
                <i class="fas fa-user-plus"></i> Daftar
            </a>
        </div>

        <button class="mobile-menu-btn">
            <i class="fas fa-bars"></i>
        </button>
    </header>

    <!-- Hero Section -->
    <section class="hero" id="home">
        <div class="hero-content fade-in">
            <h1>Jelajahi Dunia Tanpa Batas dengan Ribuan Buku Digital</h1>
            <p>Temukan pengetahuan, petualangan, dan inspirasi dalam genggaman Anda. Akses ribuan buku berkualitas kapan saja, di mana saja.</p>

            <div class="hero-buttons">
                <a href="#collections" class="btn btn-primary">Lihat Koleksi</a>
                <a href="#pricing" class="btn btn-outline" style="background: linear-gradient(90deg, #FFD700 0%, #FFC300 100%); color: #fff; border: none; box-shadow: 0 4px 15px rgba(255, 215, 0, 0.2); font-weight: 700;">
                    <i class="fas fa-crown" style="color: #fff; margin-right: 0.5rem;"></i>Premium Gold
                </a>
            </div>

            <div class="hero-stats">
                <div class="stat-item">
                    <div class="stat-number">10,000+</div>
                    <div class="stat-label">Buku Digital</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number">500+</div>
                    <div class="stat-label">Penulis</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number">50+</div>
                    <div class="stat-label">Kategori</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features" id="features">
        <div class="section-title fade-in">
            <h2>Mengapa Memilih SMEA E-Lib?</h2>
            <p>Kami menyediakan pengalaman membaca digital yang tak tertandingi dengan fitur-fitur unggulan</p>
        </div>

        <div class="features-grid">
            <div class="feature-card fade-in delay-1">
                <div class="feature-icon">
                    <i class="fas fa-book-open"></i>
                </div>
                <h3>Akses Tanpa Batas</h3>
                <p>Baca ribuan buku kapan saja dan di mana saja dengan akses 24/7 melalui perangkat apapun.</p>
            </div>

            <div class="feature-card fade-in delay-2">
                <div class="feature-icon">
                    <i class="fas fa-mobile-alt"></i>
                </div>
                <h3>Baca Offline</h3>
                <p>Download buku favorit Anda dan baca tanpa koneksi internet saat sedang bepergian.</p>
            </div>

            <div class="feature-card fade-in delay-3">
                <div class="feature-icon">
                    <i class="fas fa-bookmark"></i>
                </div>
                <h3>Bookmark & Catatan</h3>
                <p>Simpan halaman favorit dan buat catatan pribadi untuk meningkatkan pengalaman membaca.</p>
            </div>

            <div class="feature-card fade-in delay-4">
                <div class="feature-icon">
                    <i class="fas fa-search"></i>
                </div>
                <h3>Pencarian Canggih</h3>
                <p>Temukan buku yang tepat dengan cepat menggunakan sistem pencarian dan rekomendasi kami.</p>
            </div>

            <div class="feature-card fade-in delay-1">
                <div class="feature-icon">
                    <i class="fas fa-user-friends"></i>
                </div>
                <h3>Komunitas Pembaca</h3>
                <p>Bergabunglah dengan grup diskusi dan berbagi pemikiran dengan pembaca lainnya.</p>
            </div>

            <div class="feature-card fade-in delay-2">
                <div class="feature-icon">
                    <i class="fas fa-chart-line"></i>
                </div>
                <h3>Statistik Membaca</h3>
                <p>Lacak kemajuan membaca Anda dengan fitur statistik dan pencapaian yang menarik.</p>
            </div>
        </div>
    </section>

    <!-- Book Collections -->
    <section class="collections" id="collections">
        <div class="container">
            <div class="collections-header fade-in">
                <h2>Koleksi Buku Terbaru</h2>
                <div class="collection-tabs">
                    <button class="tab-btn active">Semua</button>
                    <button class="tab-btn">Fiksi</button>
                    <button class="tab-btn">Non-Fiksi</button>
                    <button class="tab-btn">Edukasi</button>
                </div>
            </div>

            <div class="books-grid">
                <!-- Book 1 -->
                <div class="book-card fade-in delay-1" style="display: flex; flex-direction: column; height: 100%;">
                    <img src="https://cdn.gramedia.com/uploads/items/9786020398440_Seni-Hidup-Mi.jpg" alt="Book Cover" class="book-cover">
                    <span class="book-badge">Free</span>
                    <div class="book-details" style="flex: 1 1 auto; display: flex; flex-direction: column;">
                        <h3 style="font-size: 1rem; white-space: normal; word-break: break-word;">Seni Hidup Minimalis</h3>
                        <p class="author">oleh Jane Doe</p>
                        <div class="book-meta">
                            <span class="book-rating">
                                <i class="fas fa-star"></i> 4.5
                            </span>
                            <span>2023</span>
                        </div>
                        <div style="flex:1"></div>
                        <div class="book-actions" style="margin-top: auto;">
                            <a href="#" class="btn btn-outline"><i class="fas fa-info-circle"></i> Detail</a>
                            <a href="#" class="btn btn-primary"><i class="fas fa-book-reader"></i> Baca</a>
                        </div>
                    </div>
                </div>

                <!-- Book 2 -->
                <div class="book-card fade-in delay-2" style="display: flex; flex-direction: column; height: 100%;">
                    <img src="https://kitamenulis.id/wp-content/uploads/2023/08/Cover-Depan-17-scaled.jpg" alt="Book Cover" class="book-cover">
                    <span class="book-badge premium">Premium</span>
                    <div class="book-details" style="flex: 1 1 auto; display: flex; flex-direction: column;">
                        <h3 style="font-size: 1rem; white-space: normal; word-break: break-word;">Panduan Lengkap Pemrograman Web</h3>
                        <p class="author">oleh John Smith</p>
                        <div class="book-meta">
                            <span class="book-rating">
                                <i class="fas fa-star"></i> 4.8
                            </span>
                            <span>2023</span>
                        </div>
                        <div style="flex:1"></div>
                        <div class="book-actions" style="margin-top: auto;">
                            <a href="#" class="btn btn-outline"><i class="fas fa-info-circle"></i> Detail</a>
                            <a href="#" class="btn btn-primary"><i class="fas fa-book-reader"></i> Baca</a>
                        </div>
                    </div>
                </div>

                <!-- Book 3 -->
                <div class="book-card fade-in delay-3" style="display: flex; flex-direction: column; height: 100%;">
                    <img src="https://ebooks.gramedia.com/ebook-covers/59589/image_highres/du1bcl6j12d20h709idvvfqae8j9ja.jpg" alt="Book Cover" class="book-cover">
                    <span class="book-badge">Free</span>
                    <div class="book-details" style="flex: 1 1 auto; display: flex; flex-direction: column;">
                        <h3 style="font-size: 1rem; white-space: normal; word-break: break-word;">Petualangan di Hutan Amazon</h3>
                        <p class="author">oleh Michael Brown</p>
                        <div class="book-meta">
                            <span class="book-rating">
                                <i class="fas fa-star"></i> 4.2
                            </span>
                            <span>2022</span>
                        </div>
                        <div style="flex:1"></div>
                        <div class="book-actions" style="margin-top: auto;">
                            <a href="#" class="btn btn-outline"><i class="fas fa-info-circle"></i> Detail</a>
                            <a href="#" class="btn btn-primary"><i class="fas fa-book-reader"></i> Baca</a>
                        </div>
                    </div>
                </div>

                <!-- Book 4 -->
                <div class="book-card fade-in delay-4" style="display: flex; flex-direction: column; height: 100%;">
                    <img src="https://cdn.gramedia.com/uploads/product-metas/s8atcazs-c.jpg" alt="Book Cover" class="book-cover">
                    <span class="book-badge premium">Premium</span>
                    <div class="book-details" style="flex: 1 1 auto; display: flex; flex-direction: column;">
                        <h3 style="font-size: 1rem; white-space: normal; word-break: break-word;">Mastering Data Science</h3>
                        <p class="author">oleh Sarah Johnson</p>
                        <div class="book-meta">
                            <span class="book-rating">
                                <i class="fas fa-star"></i> 4.7
                            </span>
                            <span>2023</span>
                        </div>
                        <div style="flex:1"></div>
                        <div class="book-actions" style="margin-top: auto;">
                            <a href="#" class="btn btn-outline"><i class="fas fa-info-circle"></i> Detail</a>
                            <a href="#" class="btn btn-primary"><i class="fas fa-book-reader"></i> Baca</a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="view-all fade-in">
                <a href="#" class="btn btn-outline">Lihat Semua Buku</a>
            </div>
        </div>
    </section>

    <!-- Testimonials -->
    <section class="testimonials" id="testimonials">
        <div class="section-title fade-in">
            <h2>Apa Kata Mereka?</h2>
            <p>Testimoni dari anggota kami yang puas dengan layanan SMEA E-Lib</p>
        </div>

        <div class="testimonials-slider fade-in delay-1">
            <!-- Testimonial 1 -->
            <div class="testimonial-card">
                <p class="testimonial-content">"SMEA E-Lib telah mengubah cara saya membaca. Sekarang saya bisa mengakses ribuan buku dari mana saja. Fitur baca offline sangat membantu saat saya bepergian."</p>
                <div class="testimonial-author">
                    <img src="https://randomuser.me/api/portraits/women/32.jpg" alt="User" class="author-avatar">
                    <div class="author-info">
                        <h4>Diana Sari</h4>
                        <p>Guru & Pembaca Aktif</p>
                    </div>
                </div>
            </div>

            <!-- Testimonial 2 -->
            <div class="testimonial-card">
                <p class="testimonial-content">"Sebagai mahasiswa, koleksi buku akademik di SMEA E-Lib sangat membantu studi saya. Saya bisa menemukan referensi yang sulit didapatkan di perpustakaan fisik."</p>
                <div class="testimonial-author">
                    <img src="https://randomuser.me/api/portraits/men/45.jpg" alt="User" class="author-avatar">
                    <div class="author-info">
                        <h4>Andi Pratama</h4>
                        <p>Mahasiswa Teknik</p>
                    </div>
                </div>
            </div>

            <!-- Testimonial 3 -->
            <div class="testimonial-card">
                <p class="testimonial-content">"Saya suka fitur komunitas pembacanya. Bisa berdiskusi tentang buku yang sedang dibaca dengan orang lain membuat pengalaman membaca lebih menyenangkan."</p>
                <div class="testimonial-author">
                    <img src="https://randomuser.me/api/portraits/women/68.jpg" alt="User" class="author-avatar">
                    <div class="author-info">
                        <h4>Rina Wijaya</h4>
                        <p>Book Blogger</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Pricing Section -->
    <section class="pricing" id="pricing">
        <div class="section-title fade-in">
            <h2>Pilihan Langganan</h2>
            <p>Tingkatkan pengalaman membaca Anda dengan keanggotaan premium</p>
        </div>

        <div class="pricing-grid">
            <!-- Free Plan -->
            <div class="pricing-card fade-in delay-1">
                <h3>Free</h3>
                <div class="price" style="font-size: 3.5rem;">
                    <span style="word-break: break-all; white-space: normal; font-size: 0.75em;">Rp0</span>
                    <span style="font-size: 0.5em;">/bulan</span>
                </div>
                <ul class="pricing-features">
                    <li><i class="fas fa-check"></i> Akses ke 5.000+ buku gratis</li>
                    <li><i class="fas fa-check"></i> Baca online</li>
                    <li><i class="fas fa-check"></i> Bookmark dasar</li>
                    <li><i class="fas fa-check"></i> 1 buku offline</li>
                    <li><i class="fas fa-times"></i> Buku premium</li>
                    <li><i class="fas fa-times"></i> Fitur komunitas</li>
                </ul>
                <a href="auth/register.php" class="btn btn-outline">Daftar Gratis</a>
            </div>

            <!-- Premium Plan -->
            <div class="pricing-card popular fade-in delay-2">
                <div class="popular-badge">Populer</div>
                <h3>Premium</h3>
                <div class="price" style="font-size: 3.5rem;">
                    <span style="word-break: break-all; white-space: normal; font-size: 0.75em;">Rp50.000</span>
                    <span style="font-size: 0.5em;">/bulan</span>
                </div>
                <ul class="pricing-features">
                    <li><i class="fas fa-check"></i> Semua fitur Free</li>
                    <li><i class="fas fa-check"></i> Akses ke 10.000+ buku premium</li>
                    <li><i class="fas fa-check"></i> Download hingga 20 buku offline</li>
                    <li><i class="fas fa-check"></i> Catatan dan highlight</li>
                    <li><i class="fas fa-check"></i> Akses komunitas pembaca</li>
                    <li><i class="fas fa-check"></i> Rekomendasi personal</li>
                </ul>
                <a href="auth/register.php" class="btn btn-primary">Mulai Sekarang</a>
            </div>

            <!-- Annual Plan -->
            <div class="pricing-card fade-in delay-3">
                <h3>Tahunan</h3>
                <div class="price" style="font-size: 3.5rem;">
                    <span style="word-break: break-all; white-space: normal; font-size: 0.75em;">Rp450.000</span>
                    <span style="font-size: 0.5em;">/tahun</span>
                </div>
                <ul class="pricing-features">
                    <li><i class="fas fa-check"></i> Semua fitur Premium</li>
                    <li><i class="fas fa-check"></i> Hemat 25% dibanding bulanan</li>
                    <li><i class="fas fa-check"></i> Download hingga 50 buku offline</li>
                    <li><i class="fas fa-check"></i> Laporan membaca mingguan</li>
                    <li><i class="fas fa-check"></i> Prioritas dukungan</li>
                    <li><i class="fas fa-check"></i> Hadiah buku bulanan</li>
                </ul>
                <a href="auth/register.php" class="btn btn-outline">Pilih Tahunan</a>
            </div>
        </div>
    </section>

    <!-- Newsletter -->
    <section class="newsletter">
        <div class="section-title fade-in">
            <h2>Dapatkan Update Terbaru</h2>
            <p>Berlangganan newsletter kami untuk mendapatkan informasi tentang buku baru, promo, dan acara menarik</p>
        </div>

        <form class="newsletter-form fade-in delay-1">
            <input type="email" placeholder="Alamat email Anda" required>
            <button type="submit" class="btn btn-primary">Berlangganan</button>
        </form>
    </section>

    <!-- Footer -->
    <footer>
        <div class="footer-grid">
            <div class="footer-logo">
                <img src="https://cdn-icons-png.flaticon.com/512/3565/3565418.png" alt="Logo Perpus">
                <p>SMEA E-Lib adalah perpustakaan digital modern yang menyediakan akses ke ribuan buku berkualitas untuk semua kalangan.</p>
                <div class="social-links">
                    <a href="#"><i class="fab fa-facebook-f"></i></a>
                    <a href="#"><i class="fab fa-twitter"></i></a>
                    <a href="#"><i class="fab fa-instagram"></i></a>
                    <a href="#"><i class="fab fa-youtube"></i></a>
                </div>
            </div>

            <div class="footer-links">
                <h3>Tautan Cepat</h3>
                <ul>
                    <li><a href="#home">Beranda</a></li>
                    <li><a href="#features">Fitur</a></li>
                    <li><a href="#collections">Koleksi</a></li>
                    <li><a href="#testimonials">Testimoni</a></li>
                    <li><a href="#pricing">Langganan</a></li>
                </ul>
            </div>

            <div class="footer-links">
                <h3>Kategori</h3>
                <ul>
                    <li><a href="#">Fiksi</a></li>
                    <li><a href="#">Non-Fiksi</a></li>
                    <li><a href="#">Sains & Teknologi</a></li>
                    <li><a href="#">Bisnis & Ekonomi</a></li>
                    <li><a href="#">Kesehatan</a></li>
                </ul>
            </div>

            <div class="footer-contact">
                <h3>Kontak Kami</h3>
                <p><i class="fas fa-map-marker-alt"></i> Jl. Pendidikan No. 123, Jakarta</p>
                <p><i class="fas fa-phone-alt"></i> (021) 1234-5678</p>
                <p><i class="fas fa-envelope"></i> info@smea-elib.com</p>
                <p><i class="fas fa-clock"></i> Senin-Jumat: 08.00-17.00</p>
            </div>
        </div>

        <div class="footer-bottom">
            <p>&copy; 2023 SMEA E-Lib. All rights reserved. | <a href="#">Kebijakan Privasi</a> | <a href="#">Syarat & Ketentuan</a></p>
        </div>
    </footer>

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

                // Here you would typically filter the books based on the selected tab
                // For demo purposes, we're just changing the active state
            });
        });

        // Simple testimonial slider
        let currentTestimonial = 0;
        const testimonials = document.querySelectorAll('.testimonial-card');

        function showTestimonial(index) {
            testimonials.forEach(testimonial => {
                testimonial.style.display = 'none';
            });
            testimonials[index].style.display = 'block';
        }

        function nextTestimonial() {
            currentTestimonial = (currentTestimonial + 1) % testimonials.length;
            showTestimonial(currentTestimonial);
        }

        // Initialize
        showTestimonial(0);

        // Auto-rotate testimonials
        setInterval(nextTestimonial, 5000);

        // Animation on scroll
        function animateOnScroll() {
            const elements = document.querySelectorAll('.fade-in');

            elements.forEach(element => {
                const elementPosition = element.getBoundingClientRect().top;
                const screenPosition = window.innerHeight / 1.2;

                if (elementPosition < screenPosition) {
                    element.style.opacity = '1';
                    element.style.transform = 'translateY(0)';
                }
            });
        }

        // Set initial state
        document.querySelectorAll('.fade-in').forEach(el => {
            el.style.opacity = '0';
            el.style.transform = 'translateY(20px)';
            el.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
        });

        window.addEventListener('scroll', animateOnScroll);
        window.addEventListener('load', animateOnScroll);
    </script>
</body>

</html>