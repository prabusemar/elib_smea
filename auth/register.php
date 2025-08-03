<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard_user.php");
    exit;
}

// Display error if exists
$error = isset($_SESSION['register_error']) ? $_SESSION['register_error'] : '';
unset($_SESSION['register_error']);
include_once '../config.php';
?>


<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Akun - Perpustakaan Digital</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
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
            --white: #ffffff;
            --error: #ef4444;
            --shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s ease;
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
            background-color: var(--light);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }

        .register-wrapper {
            display: flex;
            width: 950px;
            max-width: 95%;
            height: auto;
            min-height: 650px;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: var(--shadow);
            transition: var(--transition);
        }

        .register-wrapper:hover {
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
            transform: translateY(-5px);
        }

        .register-image {
            flex: 1;
            background: linear-gradient(rgba(58, 12, 163, 0.7), rgba(67, 97, 238, 0.7)),
                url('https://images.unsplash.com/photo-1524995997946-a1c2e315a42f?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1590&q=80') no-repeat center center/cover;
            color: var(--white);
            padding: 2.5rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }

        .register-image::after {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 100px;
            height: 100px;
            border-top-right-radius: 20px;
            background-color: var(--white);
            transform: translate(50%, -50%) rotate(45deg);
        }

        .register-content {
            flex: 1;
            background-color: var(--white);
            padding: 2.5rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
            overflow-y: auto;
        }

        .register-logo {
            display: flex;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .register-logo img {
            height: 45px;
            margin-right: 1rem;
        }

        .logo-text h1 {
            font-size: 1.4rem;
            color: var(--white);
            line-height: 1.2;
        }

        .logo-text span {
            font-size: 0.75rem;
            opacity: 0.8;
        }

        .welcome-text h2 {
            font-size: 1.8rem;
            margin-bottom: 0.8rem;
            position: relative;
            display: inline-block;
        }

        .welcome-text h2::after {
            content: '';
            position: absolute;
            bottom: -8px;
            left: 0;
            width: 45px;
            height: 3px;
            background-color: var(--white);
        }

        .welcome-text p {
            opacity: 0.9;
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
        }

        .register-title {
            text-align: center;
            margin-bottom: 1.5rem;
        }

        .register-title h2 {
            font-size: 1.6rem;
            color: var(--primary);
            margin-bottom: 0.5rem;
            position: relative;
            display: inline-block;
        }

        .register-title h2::after {
            content: '';
            position: absolute;
            bottom: -6px;
            left: 50%;
            transform: translateX(-50%);
            width: 50px;
            height: 3px;
            background: linear-gradient(90deg, var(--primary), var(--secondary));
            border-radius: 3px;
        }

        .register-title p {
            color: var(--gray);
            font-size: 0.85rem;
        }

        .form-group {
            margin-bottom: 1.2rem;
            position: relative;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.4rem;
            font-weight: 500;
            color: var(--dark);
            font-size: 0.85rem;
        }

        .form-control {
            width: 100%;
            padding: 0.8rem 1rem 0.8rem 2.8rem;
            border: 1px solid var(--light-gray);
            border-radius: 8px;
            font-size: 0.9rem;
            transition: var(--transition);
            background-color: var(--light);
        }

        .form-control:focus {
            border-color: var(--primary);
            outline: none;
            box-shadow: 0 0 0 3px rgba(58, 12, 163, 0.1);
            background-color: var(--white);
        }

        .input-icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--primary);
            font-size: 0.9rem;
        }

        .register-btn {
            width: 100%;
            padding: 0.9rem;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
            color: var(--white);
            border: none;
            border-radius: 8px;
            font-size: 0.95rem;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            margin: 1.2rem 0;
            box-shadow: var(--shadow);
        }

        .register-btn:hover {
            background: linear-gradient(135deg, var(--primary-light) 0%, var(--primary) 100%);
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(58, 12, 163, 0.15);
        }

        .register-btn:active {
            transform: translateY(0);
        }

        .login-link {
            text-align: center;
            font-size: 0.85rem;
            color: var(--gray);
            margin-top: 0.5rem;
        }

        .login-link a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
            transition: var(--transition);
        }

        .login-link a:hover {
            color: var(--secondary);
            text-decoration: underline;
        }

        .error-message {
            color: var(--error);
            background-color: rgba(239, 68, 68, 0.1);
            padding: 0.7rem;
            border-radius: 8px;
            font-size: 0.85rem;
            margin-bottom: 1.2rem;
            text-align: center;
            display: <?php echo !empty($error) ? 'block' : 'none'; ?>;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .register-wrapper {
                flex-direction: column;
                height: auto;
                min-height: auto;
            }

            .register-image {
                padding: 2rem;
            }

            .register-content {
                padding: 2rem;
            }

            .register-logo img {
                height: 40px;
            }

            .logo-text h1 {
                font-size: 1.3rem;
            }

            .welcome-text h2 {
                font-size: 1.6rem;
            }
        }

        @media (max-width: 480px) {
            .register-content {
                padding: 1.5rem;
            }

            .register-title h2 {
                font-size: 1.4rem;
            }

            .form-control {
                padding: 0.7rem 1rem 0.7rem 2.7rem;
            }

            .register-btn {
                padding: 0.8rem;
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
            animation: fadeIn 0.6s ease forwards;
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

        a {
            text-decoration: none;
        }

        a:hover {
            text-decoration: none;
        }

        .fa-user,
        .fa-lock,
        .fa-user-tag,
        .fa-envelope,
        .fa-eye,
        .fa-eye-slash {
            margin-top: 0.9rem;
        }

        .alert {
            padding: 15px;
            margin: 15px 0;
            border-radius: 5px;
            text-align: center;
            font-size: 14px;
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        /* Add to your existing CSS */
        .toggle-password {
            color: var(--primary);
            z-index: 10;
            transition: var(--transition);
            padding: 10px;
            /* Makes it easier to click */
        }

        .toggle-password:hover {
            color: var(--secondary);
        }

        /* Adjust input padding to accommodate the eye icon */
        .form-control {
            padding-right: 3rem !important;
        }
    </style>
</head>

<body>
    <div class="register-wrapper">
        <!-- Bagian Kiri (Gambar) -->
        <div class="register-image fade-in">
            <div class="register-logo">
                <img src="<?= BASE_URL ?>/assets/logo/logo-smea.png" alt="Logo Perpus">
                <div class="logo-text">
                    <h1>SMEA E-Lib</h1>
                    <span>Perpustakaan Digital</span>
                </div>
            </div>

            <div class="welcome-text">
                <h2>Bergabung Bersama Kami</h2>
                <p>Daftarkan diri Anda untuk mengakses ribuan koleksi buku digital kami dan mulai petualangan membaca Anda.</p>
            </div>
        </div>

        <!-- Bagian Kanan (Form) -->
        <div class="register-content">
            <?php

            if (isset($_SESSION['register_alert'])) {
                $alert = $_SESSION['register_alert'];
                $alertType = $alert['type'];
                $alertMessage = $alert['message'];
                unset($_SESSION['register_alert']);

                echo "<div class='alert alert-$alertType'>$alertMessage</div>";
            }
            ?>
            <div class="register-title fade-in delay-1">
                <h2>Buat Akun Baru</h2>
                <p>Isi formulir berikut untuk mendaftar</p>
            </div>

            <?php if (!empty($error)): ?>
                <div class="error-message fade-in delay-1">
                    <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form id="registerForm" action="proses_register.php" method="post" class="fade-in delay-2">
                <div class="form-group">
                    <label for="full_name">Nama Lengkap</label>
                    <i class="fas fa-user input-icon"></i>
                    <input type="text" id="full_name" name="full_name" class="form-control" placeholder="Masukkan nama lengkap" required>
                    <div class="error-message" id="fullname-error" style="display: none; margin-top: 0.5rem;"></div>
                </div>

                <div class="form-group">
                    <label for="username">Username</label>
                    <i class="fas fa-user-tag input-icon"></i>
                    <input type="text" id="username" name="username" class="form-control" placeholder="Masukkan username" required>
                    <div class="error-message" id="username-error" style="display: none; margin-top: 0.5rem;"></div>
                </div>

                <div class="form-group">
                    <label for="email">Email</label>
                    <i class="fas fa-envelope input-icon"></i>
                    <input type="email" id="email" name="email" class="form-control" placeholder="Masukkan alamat email" required>
                    <div class="error-message" id="email-error" style="display: none; margin-top: 0.5rem;"></div>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <i class="fas fa-lock input-icon"></i>
                    <input type="password" id="password" name="password" class="form-control" placeholder="Masukkan password" required>
                    <i class="fas fa-eye toggle-password" data-target="password" style="position: absolute; right: 15px; top: 50%; transform: translateY(-50%); cursor: pointer;"></i>
                    <div class="error-message" id="password-error" style="display: none; margin-top: 0.5rem;"></div>
                </div>

                <div class="form-group">
                    <label for="confirm_password">Konfirmasi Password</label>
                    <i class="fas fa-lock input-icon"></i>
                    <input type="password" id="confirm_password" name="confirm_password" class="form-control" placeholder="Ulangi password" required>
                    <i class="fas fa-eye toggle-password" data-target="confirm_password" style="position: absolute; right: 15px; top: 50%; transform: translateY(-50%); cursor: pointer;"></i>
                    <div class="error-message" id="confirm-error" style="display: none; margin-top: 0.5rem;"></div>
                </div>

                <button type="submit" class="register-btn">
                    <i class="fas fa-user-plus"></i> Daftar Sekarang
                </button>

                <div class="login-link">
                    <span>Sudah punya akun?</span>
                    <a href="login.php" style="margin-left: 0.3rem; color: var(--primary); font-weight: 600; display: inline-flex; align-items: center; transition: color 0.2s; text-decoration: none;"
                        onmouseover="this.style.textDecoration='none';"
                        onmouseout="this.style.textDecoration='none';">
                        <i class="fas fa-sign-in-alt" style="margin-right: 0.4em; font-size: 0.9em;"></i>
                        <span>Masuk di sini</span>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Toggle password visibility for both fields
        document.querySelectorAll('.toggle-password').forEach(function(icon) {
            icon.addEventListener('click', function() {
                const targetId = this.getAttribute('data-target');
                const passwordField = document.getElementById(targetId);

                if (passwordField.type === 'password') {
                    passwordField.type = 'text';
                    this.classList.remove('fa-eye');
                    this.classList.add('fa-eye-slash');
                } else {
                    passwordField.type = 'password';
                    this.classList.remove('fa-eye-slash');
                    this.classList.add('fa-eye');
                }
            });
        });

        // Animasi saat halaman dimuat
        document.addEventListener('DOMContentLoaded', function() {
            // Set opacity 0 untuk elemen yang akan dianimasikan
            const fadeElements = document.querySelectorAll('.fade-in');
            fadeElements.forEach(el => {
                el.style.opacity = '0';
            });

            // Trigger animasi setelah halaman selesai dimuat
            setTimeout(() => {
                fadeElements.forEach(el => {
                    el.style.opacity = '1';
                });
            }, 100);

            // Client-side validation
            document.getElementById('registerForm').addEventListener('submit', function(e) {
                let valid = true;
                const full_name = document.getElementById('full_name').value;
                const username = document.getElementById('username').value;
                const email = document.getElementById('email').value;
                const password = document.getElementById('password').value;
                const confirmPassword = document.getElementById('confirm_password').value;

                // Reset error messages
                document.querySelectorAll('.error-message').forEach(el => {
                    el.style.display = 'none';
                });

                // Check full name length
                if (full_name.length < 3) {
                    document.getElementById('fullname-error').textContent = 'Nama lengkap minimal 3 karakter!';
                    document.getElementById('fullname-error').style.display = 'block';
                    valid = false;
                }

                // Check username length
                if (username.length < 3) {
                    document.getElementById('username-error').textContent = 'Username minimal 3 karakter!';
                    document.getElementById('username-error').style.display = 'block';
                    valid = false;
                }

                // Check email format
                if (!/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/.test(email)) {
                    document.getElementById('email-error').textContent = 'Format email tidak valid!';
                    document.getElementById('email-error').style.display = 'block';
                    valid = false;
                }

                // Check password match
                if (password !== confirmPassword) {
                    document.getElementById('confirm-error').textContent = 'Password tidak cocok!';
                    document.getElementById('confirm-error').style.display = 'block';
                    valid = false;
                }

                // Check password length
                if (password.length < 6) {
                    document.getElementById('password-error').textContent = 'Password minimal 6 karakter!';
                    document.getElementById('password-error').style.display = 'block';
                    valid = false;
                }

                if (!valid) {
                    e.preventDefault();
                    // Scroll to first error
                    const firstError = document.querySelector('.error-message[style="display: block;"]');
                    if (firstError) {
                        firstError.scrollIntoView({
                            behavior: 'smooth',
                            block: 'center'
                        });
                    }
                }
            });
        });
    </script>
</body>

</html>