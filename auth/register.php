<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard_user.php");
    exit;
}

// Display error if exists
$error = isset($_SESSION['register_error']) ? $_SESSION['register_error'] : '';
unset($_SESSION['register_error']);
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
            --primary: #4361ee;
            --primary-dark: #3a0ca3;
            --secondary: #f72585;
            --light: #f8f9fa;
            --dark: #212529;
            --gray: #6c757d;
            --error: #ef4444;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #e0f2fe 0%, #f0fdfa 100%);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            color: var(--dark);
        }
        
        .register-container {
            background-color: white;
            padding: 2.5rem;
            border-radius: 16px;
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 500px;
            position: relative;
            overflow: hidden;
        }
        
        .register-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 8px;
            background: linear-gradient(90deg, var(--primary), var(--secondary));
        }
        
        .register-container h2 {
            margin-bottom: 1.8rem;
            color: var(--primary-dark);
            font-size: 1.8rem;
            font-weight: 700;
            text-align: center;
            position: relative;
        }
        
        .register-container h2::after {
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
        
        .form-group {
            margin-bottom: 1.5rem;
            position: relative;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.6rem;
            font-weight: 500;
            color: var(--dark);
        }
        
        .form-group input {
            width: 100%;
            padding: 0.9rem 1rem 0.9rem 2.8rem;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background-color: #f8fafc;
        }
        
        .form-group input:focus {
            border-color: var(--primary);
            outline: none;
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.2);
            background-color: white;
        }
        
        .form-group i {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--gray);
            font-size: 1rem;
        }
        
        .register-btn {
            width: 100%;
            padding: 1rem;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            margin-top: 1rem;
            transition: all 0.3s ease;
        }
        
        .register-btn:hover {
            background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary) 100%);
            transform: translateY(-2px);
        }
        
        .login-link {
            margin-top: 1.5rem;
            text-align: center;
            font-size: 0.9rem;
        }
        
        .login-link a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
        }
        
        .login-link a:hover {
            text-decoration: underline;
        }
        
        .error-message {
            color: var(--error);
            font-size: 0.9rem;
            margin-top: 0.3rem;
            display: none;
        }
        
        @media (max-width: 480px) {
            .register-container {
                padding: 2rem 1.5rem;
                margin: 0 1rem;
            }
        }

        .fa-user, .fa-lock  {
            color: var(--primary);
            margin-top: 0.8rem;
        }
    </style>
</head>

<body>
    <div class="register-container">
        <h2>Daftar Akun Baru</h2>
        <?php if (!empty($error)): ?>
            <div class="error-message" style="display: block; text-align: center; margin-bottom: 1rem;">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        <form id="registerForm" action="proses_register.php" method="post">
            <div class="form-group">
                <label for="username">Username</label>
                <i class="fas fa-user"></i>
                <input type="text" id="username" name="username" placeholder="Masukkan username" required>
                <div class="error-message" id="username-error"></div>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <i class="fas fa-lock"></i>
                <input type="password" id="password" name="password" placeholder="Masukkan password" required>
                <div class="error-message" id="password-error"></div>
            </div>
            <div class="form-group">
                <label for="confirm_password">Konfirmasi Password</label>
                <i class="fas fa-lock"></i>
                <input type="password" id="confirm_password" name="confirm_password" placeholder="Ulangi password" required>
                <div class="error-message" id="confirm-error"></div>
            </div>
            <button type="submit" class="register-btn">
                <i class="fas fa-user-plus"></i> Daftar
            </button>
            <div class="login-link">
                Sudah punya akun? <a href="login.php">Login disini</a>
            </div>
        </form>
    </div>

    <script>
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
            }
        });
    </script>
</body>

</html>