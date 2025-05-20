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
        }

        .register-wrapper {
            display: flex;
            width: 900px;
            max-width: 90%;
            height: 600px;
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
            padding: 3rem;
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
            padding: 3rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .register-logo {
            display: flex;
            align-items: center;
            margin-bottom: 2rem;
        }

        .register-logo img {
            height: 50px;
            margin-right: 1rem;
        }

        .logo-text h1 {
            font-size: 1.5rem;
            color: var(--white);
            line-height: 1.2;
        }

        .logo-text span {
            font-size: 0.8rem;
            opacity: 0.8;
        }

        .welcome-text h2 {
            font-size: 2rem;
            margin-bottom: 1rem;
            position: relative;
            display: inline-block;
        }

        .welcome-text h2::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 0;
            width: 50px;
            height: 3px;
            background-color: var(--white);
        }

        .welcome-text p {
            opacity: 0.9;
            margin-bottom: 2rem;
        }

        .register-title {
            text-align: center;
            margin-bottom: 2rem;
        }

        .register-title h2 {
            font-size: 1.8rem;
            color: var(--primary);
            margin-bottom: 0.5rem;
            position: relative;
            display: inline-block;
        }

        .register-title h2::after {
            content: '';
            position: absolute;
            bottom: -8px;
            left: 50%;
            transform: translateX(-50%);
            width: 60px;
            height: 3px;
            background: linear-gradient(90deg, var(--primary), var(--secondary));
            border-radius: 3px;
        }

        .register-title p {
            color: var(--gray);
            font-size: 0.9rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
            position: relative;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: var(--dark);
            font-size: 0.9rem;
        }

        .form-control {
            width: 100%;
            padding: 0.9rem 1rem 0.9rem 3rem;
            border: 1px solid var(--light-gray);
            border-radius: 8px;
            font-size: 1rem;
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
            font-size: 1rem;
        }

        .register-btn {
            width: 100%;
            padding: 1rem;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
            color: var(--white);
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            margin: 1.5rem 0;
            box-shadow: var(--shadow);
        }

        .register-btn:hover {
            background: linear-gradient(135deg, var(--primary-light) 0%, var(--primary) 100%);
            transform: translateY(-3px);
            box-shadow: 0 6px 12px rgba(58, 12, 163, 0.15);
        }

        .register-btn:active {
            transform: translateY(0);
        }

        .login-link {
            text-align: center;
            font-size: 0.9rem;
            color: var(--gray);
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
            padding: 0.8rem;
            border-radius: 8px;
            font-size: 0.9rem;
            margin-bottom: 1.5rem;
            text-align: center;
            display: <?php echo !empty($error) ? 'block' : 'none'; ?>;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .register-wrapper {
                flex-direction: column;
                height: auto;
            }

            .register-image {
                display: none;
            }

            .register-content {
                padding: 2rem;
            }
        }

        @media (max-width: 480px) {
            .register-content {
                padding: 1.5rem;
            }

            .register-title h2 {
                font-size: 1.5rem;
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
        .fa-lock {
            margin-top: 0.8rem;
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
                    <label for="username">Username</label>
                    <i class="fas fa-user input-icon"></i>
                    <input type="text" id="username" name="username" class="form-control" placeholder="Masukkan username" required>
                    <div class="error-message" id="username-error" style="display: none; margin-top: 0.5rem;"></div>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <i class="fas fa-lock input-icon"></i>
                    <input type="password" id="password" name="password" class="form-control" placeholder="Masukkan password" required>
                    <div class="error-message" id="password-error" style="display: none; margin-top: 0.5rem;"></div>
                </div>

                <div class="form-group">
                    <label for="confirm_password">Konfirmasi Password</label>
                    <i class="fas fa-lock input-icon"></i>
                    <input type="password" id="confirm_password" name="confirm_password" class="form-control" placeholder="Ulangi password" required>
                    <div class="error-message" id="confirm-error" style="display: none; margin-top: 0.5rem;"></div>
                </div>

                <button type="submit" class="register-btn">
                    <i class="fas fa-user-plus"></i> Daftar Sekarang
                </button>

                <div class="login-link" style="margin-top: 1.5rem;">
                    <span style="color: var(--gray);">Sudah punya akun?</span>
                    <a href="login.php" style="margin-left: 0.3rem; color: var(--primary); font-weight: 600; display: inline-flex; align-items: center; transition: color 0.2s; text-decoration: none;"
                        onmouseover="this.style.textDecoration='none';"
                        onmouseout="this.style.textDecoration='none';">
                        <i class="fas fa-sign-in-alt" style="margin-right: 0.4em; font-size: 1em;"></i>
                        <span>Masuk di sini</span>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <script>
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
                const username = document.getElementById('username').value;
                const password = document.getElementById('password').value;
                const confirmPassword = document.getElementById('confirm_password').value;

                // Reset error messages
                document.querySelectorAll('.error-message').forEach(el => {
                    el.style.display = 'none';
                });

                // Check username length
                if (username.length < 3) {
                    document.getElementById('username-error').textContent = 'Username minimal 3 karakter!';
                    document.getElementById('username-error').style.display = 'block';
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