<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/helper.php';
require_once __DIR__ . '/../config/mailer.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$step = $_GET['step'] ?? 1;
$error = '';
$success = '';

// Handle Step 1: Submit Email
if ($step == 1 && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    
    $stmt = $pdo->prepare("SELECT * FROM admins WHERE email = ?");
    $stmt->execute([$email]);
    $admin = $stmt->fetch();
    
    if ($admin) {
        $otp = sprintf("%06d", mt_rand(0, 999999));
        $expiry = date('Y-m-d H:i:s', strtotime('+10 minutes'));
        
        $stmt_update = $pdo->prepare("UPDATE admins SET otp_code = ?, otp_expiry = ? WHERE id = ?");
        $stmt_update->execute([$otp, $expiry, $admin['id']]);
        
        send_otp_email($email, $otp);
        
        $_SESSION['reset_email'] = $email;
        header("Location: lupa_password.php?step=2");
        exit();
    } else {
        $error = "Email tidak terdaftar atau tidak ditemukan.";
    }
}

// Handle Step 2: Verify OTP
if ($step == 2 && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $otp_input = $_POST['otp'];
    $email = $_SESSION['reset_email'] ?? '';
    
    if (!$email) {
        header("Location: lupa_password.php?step=1");
        exit();
    }
    
    $stmt = $pdo->prepare("SELECT * FROM admins WHERE email = ? AND otp_code = ? AND otp_expiry > NOW()");
    $stmt->execute([$email, $otp_input]);
    $admin = $stmt->fetch();
    
    if ($admin) {
        $_SESSION['otp_verified'] = true;
        header("Location: lupa_password.php?step=3");
        exit();
    } else {
        $error = "Kode OTP salah atau sudah kedaluwarsa.";
    }
}

// Handle Step 3: Reset Password
if ($step == 3 && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $email = $_SESSION['reset_email'] ?? '';
    
    if (!isset($_SESSION['otp_verified']) || !$email) {
        header("Location: lupa_password.php?step=1");
        exit();
    }
    
    if (strlen($password) < 6) {
        $error = "Password minimal 6 karakter.";
    } elseif ($password !== $confirm_password) {
        $error = "Konfirmasi password tidak cocok.";
    } else {
        // 1. Fetch current password & history
        $stmt_check = $pdo->prepare("
            SELECT password as current_pw FROM admins WHERE email = ?
            UNION
            SELECT password_hash FROM admin_password_history WHERE admin_id = (SELECT id FROM admins WHERE email = ?)
        ");
        $stmt_check->execute([$email, $email]);
        $history = $stmt_check->fetchAll(PDO::FETCH_COLUMN);

        $is_reused = false;
        foreach ($history as $old_hash) {
            if (password_verify($password, $old_hash)) {
                $is_reused = true;
                break;
            }
        }

        if ($is_reused) {
            $error = "Password telah digunakan (Pernah dipakai). Silakan gunakan password lain.";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            try {
                $pdo->beginTransaction();
                
                // Get Admin ID
                $stmt_id = $pdo->prepare("SELECT id FROM admins WHERE email = ?");
                $stmt_id->execute([$email]);
                $admin_id = $stmt_id->fetchColumn();

                // 2. Update Password
                $stmt = $pdo->prepare("UPDATE admins SET password = ?, otp_code = NULL, otp_expiry = NULL WHERE id = ?");
                $stmt->execute([$hashed_password, $admin_id]);

                // 3. Save to History
                $stmt_hist = $pdo->prepare("INSERT INTO admin_password_history (admin_id, password_hash) VALUES (?, ?)");
                $stmt_hist->execute([$admin_id, $hashed_password]);

                $pdo->commit();

                unset($_SESSION['reset_email']);
                unset($_SESSION['otp_verified']);
                
                $_SESSION['alert_message'] = "Password berhasil diperbarui. Silakan login.";
                $_SESSION['alert_type'] = "success";
                header("Location: login.php");
                exit();
            } catch (PDOException $e) {
                $pdo->rollBack();
                $error = "Gagal memperbarui password: " . $e->getMessage();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id" data-theme="light">
<head>
    <script>
        document.documentElement.setAttribute('data-theme', 'light');
    </script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lupa Password - JS Rental</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css">
    <style>
        body { background-color: var(--bg-main); min-height: 100vh; display: flex; align-items: center; justify-content: center; }
        .reset-card { width: 100%; max-width: 400px; padding: 30px; }
        .otp-input { letter-spacing: 12px; font-size: 24px; text-align: center; font-weight: 800; }
    </style>
</head>
<body>
    <div class="reset-card glass-card shadow-lg">
        <h4 class="fw-bold mb-1">Lupa Password</h4>
        <p class="text-muted small mb-4">
            <?php if($step == 1): ?>
                Masukkan email Anda untuk menerima kode OTP.
            <?php elseif($step == 2): ?>
                Masukkan 6 digit kode OTP yang dikirim ke email Anda.
            <?php else: ?>
                Buat password baru yang aman.
            <?php endif; ?>
        </p>

        <?php if($error): ?>
            <div class="alert alert-danger py-2 small border-0"><?= $error ?></div>
        <?php endif; ?>

        <?php if($step == 1): ?>
            <form action="" method="POST">
                <div class="mb-4">
                    <label class="form-label small fw-bold">Email Terdaftar</label>
                    <input type="email" name="email" class="form-control" required placeholder="nama@email.com">
                </div>
                <div class="d-grid mb-3">
                    <button type="submit" class="btn btn-accent fw-bold py-2">Kirim Kode OTP</button>
                </div>
            </form>
        <?php elseif($step == 2): ?>
            <form action="" method="POST">
                <div class="mb-4">
                    <label class="form-label small fw-bold text-center d-block">Kode Verifikasi</label>
                    <input type="text" name="otp" class="form-control otp-input" maxlength="6" required placeholder="000000">
                    <div class="text-center mt-2">
                        <small class="text-muted">Dikirim ke: <?= htmlspecialchars($_SESSION['reset_email'] ?? '') ?></small>
                    </div>
                </div>
                <div class="d-grid mb-3">
                    <button type="submit" class="btn btn-accent fw-bold py-2">Verifikasi OTP</button>
                </div>
                <div class="text-center">
                    <a href="lupa_password.php?step=1" class="small text-accent text-decoration-none">Ganti Email</a>
                </div>
            </form>
        <?php elseif($step == 3): ?>
            <form action="" method="POST">
                <div class="mb-3">
                    <label class="form-label small fw-bold">Password Baru</label>
                    <input type="password" name="password" class="form-control" required placeholder="Minimal 6 karakter">
                </div>
                <div class="mb-4">
                    <label class="form-label small fw-bold">Konfirmasi Password</label>
                    <input type="password" name="confirm_password" class="form-control" required placeholder="Ulangi password">
                </div>
                <div class="d-grid mb-3">
                    <button type="submit" class="btn btn-accent fw-bold py-2">Simpan Password</button>
                </div>
            </form>
        <?php endif; ?>

        <div class="text-center mt-2">
            <a href="login.php" class="text-muted small text-decoration-none"><i class="fas fa-arrow-left me-1"></i> Kembali ke Login</a>
        </div>
    </div>
</body>
</html>
