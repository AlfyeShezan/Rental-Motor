<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../template/auth.php';
require_once __DIR__ . '/../../config/helper.php';

if (!is_super_admin()) {
    redirect_with_alert('../index.php', 'Anda tidak memiliki hak akses.', 'danger');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $full_name = $_POST['full_name'];
    $email = $_POST['email'];
    $phone_number = $_POST['phone_number'];
    $password_raw = $_POST['password'];
    $role = $_POST['role'];

    $errors = [];

    // Validation
    if (strlen($username) < 4) $errors[] = "Username minimal 4 karakter.";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Format email tidak valid.";
    if (strlen($password_raw) < 6) $errors[] = "Password minimal 6 karakter.";
    
    // Check Unique Username
    $stmt_u = $pdo->prepare("SELECT id FROM admins WHERE username = ?");
    $stmt_u->execute([$username]);
    if ($stmt_u->fetch()) $errors[] = "Username sudah digunakan.";

    // Check Unique Email
    $stmt_e = $pdo->prepare("SELECT id FROM admins WHERE email = ?");
    $stmt_e->execute([$email]);
    if ($stmt_e->fetch()) $errors[] = "Email sudah digunakan.";

    if (empty($errors)) {
        try {
            $password_hashed = password_hash($password_raw, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO admins (username, password, full_name, email, phone_number, role) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$username, $password_hashed, $full_name, $email, $phone_number, $role]);
            
            // Save initial password to history
            $new_admin_id = $pdo->lastInsertId();
            $stmt_hist = $pdo->prepare("INSERT INTO admin_password_history (admin_id, password_hash) VALUES (?, ?)");
            $stmt_hist->execute([$new_admin_id, $password_hashed]);

            require_once __DIR__ . '/../../config/backup_helper.php';
            trigger_auto_backup($pdo);
            redirect_with_alert('index.php', 'Akun admin berhasil dibuat.');
        } catch (PDOException $e) {
            $errors[] = "Gagal: " . $e->getMessage();
        }
    }

    if (!empty($errors)) {
        $_SESSION['alert_message'] = implode("<br>", $errors);
        $_SESSION['alert_type'] = "danger";
    }
}

include __DIR__ . '/../template/header.php';
include __DIR__ . '/../template/sidebar.php';
?>

<div id="content">
    <nav class="navbar top-navbar">
        <div class="container-fluid">
            <button type="button" id="sidebarCollapse" class="btn btn-light">
                <i class="fas fa-align-left text-dark"></i>
            </button>
            <h5 class="mb-0 fw-bold ms-3">Tambah Admin Baru</h5>
            <div class="ms-auto">
                <a href="index.php" class="btn btn-light rounded-pill px-3 py-1.5 border small"><i class="fas fa-arrow-left me-1"></i> Kembali</a>
            </div>
        </div>
    </nav>

    <div class="main-content">
        <?php display_alert(); ?>

        <div class="row justify-content-center">
            <div class="col-lg-6">
                <div class="card p-4">
                    <form action="" method="POST">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label small fw-bold">Nama Lengkap</label>
                                <input type="text" name="full_name" class="form-control" required placeholder="Contoh: Administrator Utama">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label small fw-bold">Username</label>
                                <input type="text" name="username" class="form-control" required placeholder="Contoh: admin_pusat">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label small fw-bold">Email</label>
                                <input type="email" name="email" class="form-control" required placeholder="email@contoh.com">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label small fw-bold">No. WhatsApp/Telepon</label>
                                <input type="text" name="phone_number" class="form-control" required placeholder="08123xxx">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold">Password</label>
                            <input type="password" name="password" class="form-control" required placeholder="Minimal 6 karakter">
                        </div>
                        <div class="mb-4">
                            <label class="form-label small fw-bold">Hak Akses (Role)</label>
                            <select name="role" class="form-select" required>
                                <option value="Admin">Admin (Terbatas)</option>
                                <option value="Super Admin">Super Admin (Full Access)</option>
                            </select>
                        </div>
                        <div class="d-grid mt-2">
                            <button type="submit" class="btn btn-accent fw-bold py-2 rounded-pill shadow-sm small">Buat Akun</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../template/footer.php'; ?>
