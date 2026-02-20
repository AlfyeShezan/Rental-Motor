<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/helper.php';

// If already logged in, redirect to dashboard
if (isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = "Terjadi kesalahan keamanan (Invalid CSRF).";
    } else {
        $username = $_POST['username'];
        $password = $_POST['password'];

        try {
            $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = ?");
            $stmt->execute([$username]);
            $admin = $stmt->fetch();

            if ($admin && password_verify($password, $admin['password'])) {
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_username'] = $admin['username'];
                $_SESSION['admin_full_name'] = $admin['full_name'];
                $_SESSION['admin_role'] = $admin['role'];
                
                header("Location: index.php");
                exit();
            } else {
                $error = "Username atau password salah.";
            }
        } catch (PDOException $e) {
            $error = "Terjadi kesalahan sistem.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id" data-theme="light">
<head>
    <script>
        (function() {
            document.documentElement.setAttribute('data-theme', 'light');
        })();
    </script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin - <?= SITE_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css">
    <style>
        body {
            background-color: var(--bg-main);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: var(--transition);
        }
        .login-card {
            width: 100%;
            max-width: 420px;
            padding: 40px;
            margin: 20px;
        }
        .login-logo {
            font-size: 60px;
            color: var(--accent);
            margin-bottom: 24px;
            filter: drop-shadow(0 0 15px var(--accent-glow));
        }
        .form-control {
            background: var(--bg-main);
            border: 1px solid var(--border);
            color: var(--text-body);
            padding: 12px 16px;
            border-radius: var(--radius-md);
        }
        .form-control:focus {
            background: var(--bg-main);
            border-color: var(--accent);
            box-shadow: 0 0 0 4px var(--accent-glow);
            color: var(--text-heading);
        }
        .login-title {
            color: var(--text-heading);
            font-weight: 800;
            letter-spacing: -1px;
            margin-bottom: 8px;
        }
        .login-subtitle {
            color: var(--text-muted);
            margin-bottom: 32px;
            font-size: 0.95rem;
        }
        .btn-login {
            padding: 14px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            border-radius: var(--radius-md);
        }
    </style>
</head>
<body>
    <div class="login-card glass-card shadow-lg" data-aos="fade-up">
        <div class="text-center">
            <div class="login-logo">
                <i class="fas fa-motorcycle"></i>
            </div>
            <h3 class="login-title">ADMIN PORTAL</h3>
            <p class="login-subtitle">Silakan masuk untuk mengelola sistem</p>
            
            <?php if ($error): ?>
                <div class="alert alert-danger border-0 shadow-sm small py-2 mb-4">
                    <?= $error ?>
                </div>
            <?php endif; ?>

            <form action="" method="POST">
                <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                <div class="mb-3 text-start">
                    <label class="form-label small fw-bold text-muted">Username</label>
                    <div class="input-group">
                        <span class="input-group-text bg-transparent border-end-0 text-muted">
                            <i class="fas fa-user"></i>
                        </span>
                        <input type="text" name="username" class="form-control border-start-0 ps-0" required placeholder="Masukkan username">
                    </div>
                </div>
                <div class="mb-4 text-start">
                    <label class="form-label small fw-bold text-muted">Password</label>
                    <div class="input-group">
                        <span class="input-group-text bg-transparent border-end-0 text-muted">
                            <i class="fas fa-lock"></i>
                        </span>
                        <input type="password" name="password" class="form-control border-start-0 ps-0" required placeholder="Masukkan password">
                    </div>
                    <div class="text-end mt-1">
                        <a href="lupa_password.php" class="text-accent small text-decoration-none">Lupa Password?</a>
                    </div>
                </div>
                <div class="d-grid mb-4">
                    <button type="submit" class="btn btn-accent btn-login">
                        Log In <i class="fas fa-sign-in-alt ms-2"></i>
                    </button>
                </div>
            </form>
            <a href="<?= BASE_URL ?>" class="text-accent small text-decoration-none fw-600">
                <i class="fas fa-arrow-left me-1"></i> Kembali ke Website
            </a>
        </div>
    </div>

    <!-- AOS Animation -->
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        AOS.init({
            duration: 800,
            once: true
        });
    </script>
</body>
</html>
