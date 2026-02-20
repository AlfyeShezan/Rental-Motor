<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../template/auth.php';
require_once __DIR__ . '/../../config/helper.php';

$id = $_GET['id'] ?? 0;

// Access control: Only Super Admin can edit others. Everyone can edit themselves.
if (!is_super_admin() && $id != $_SESSION['admin_id']) {
    redirect_with_alert('../index.php', 'Anda tidak memiliki hak akses.', 'danger');
}

// Fetch Admin Data
try {
    $stmt = $pdo->prepare("SELECT * FROM admins WHERE id = ?");
    $stmt->execute([$id]);
    $a = $stmt->fetch();

    if (!$a) {
        redirect_with_alert('index.php', 'Akun tidak ditemukan.', 'danger');
    }
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $full_name = $_POST['full_name'];
    $email = $_POST['email'];
    $phone_number = $_POST['phone_number'];
    $role = $_POST['role'] ?? $a['role'];
    
    $errors = [];

    // Validation
    if (strlen($username) < 4) $errors[] = "Username minimal 4 karakter.";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Format email tidak valid.";
    
    // Check Unique Username
    $stmt_u = $pdo->prepare("SELECT id FROM admins WHERE username = ? AND id != ?");
    $stmt_u->execute([$username, $id]);
    if ($stmt_u->fetch()) $errors[] = "Username sudah digunakan oleh akun lain.";

    // Check Unique Email
    $stmt_e = $pdo->prepare("SELECT id FROM admins WHERE email = ? AND id != ?");
    $stmt_e->execute([$email, $id]);
    if ($stmt_e->fetch()) $errors[] = "Email sudah digunakan oleh akun lain.";

    if (!empty($_POST['password'])) {
        if (strlen($_POST['password']) < 6) {
            $errors[] = "Password baru minimal 6 karakter.";
        } else {
            // Check for password reuse
            $stmt_check = $pdo->prepare("
                SELECT password as current_pw FROM admins WHERE id = ?
                UNION
                SELECT password_hash FROM admin_password_history WHERE admin_id = ?
            ");
            $stmt_check->execute([$id, $id]);
            $history = $stmt_check->fetchAll(PDO::FETCH_COLUMN);

            foreach ($history as $old_hash) {
                if (password_verify($_POST['password'], $old_hash)) {
                    $errors[] = "Password telah digunakan (Pernah dipakai). Silakan gunakan password lain.";
                    break;
                }
            }
        }
    }

    if (empty($errors)) {
        $password_clause = "";
        $params = [$username, $full_name, $email, $phone_number, $role];

        if (!empty($_POST['password'])) {
            $password_clause = ", password = ?";
            $params[] = password_hash($_POST['password'], PASSWORD_DEFAULT);
        }
        
        $params[] = $id;

        try {
            $stmt_u = $pdo->prepare("UPDATE admins SET username = ?, full_name = ?, email = ?, phone_number = ?, role = ? $password_clause WHERE id = ?");
            $stmt_u->execute($params);

            // SAVE TO HISTORY IF CHANGED
            if (!empty($_POST['password'])) {
                $new_hash = password_hash($_POST['password'], PASSWORD_DEFAULT);
                $stmt_hist = $pdo->prepare("INSERT INTO admin_password_history (admin_id, password_hash) VALUES (?, ?)");
                $stmt_hist->execute([$id, $new_hash]);
            }
            
            if ($id == $_SESSION['admin_id']) {
                $_SESSION['admin_full_name'] = $full_name;
                $_SESSION['admin_username'] = $username;
            }

            require_once __DIR__ . '/../../config/backup_helper.php';
            trigger_auto_backup($pdo);
            redirect_with_alert('index.php', 'Data admin berhasil diperbarui.');
        } catch (PDOException $e) {
            $errors[] = "Gagal memperbarui: " . $e->getMessage();
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
            <h5 class="mb-0 fw-bold ms-3">Edit Akun: <?= htmlspecialchars($a['full_name']) ?></h5>
            <div class="ms-auto">
                <a href="index.php" class="btn btn-light rounded-pill px-3 py-1.5 border small"><i class="fas fa-arrow-left me-1"></i> Kembali</a>
            </div>
        </div>
    </nav>

    <div class="main-content">
        <?php display_alert(); ?>

        <div class="row justify-content-center">
            <div class="col-lg-7">
                <div class="card p-4">
                    <form action="" method="POST">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label small fw-bold">Nama Lengkap</label>
                                <input type="text" name="full_name" class="form-control" required value="<?= htmlspecialchars($a['full_name']) ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label small fw-bold">Username</label>
                                <input type="text" name="username" class="form-control" required value="<?= htmlspecialchars($a['username']) ?>">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label small fw-bold">Email</label>
                                <input type="email" name="email" class="form-control" required value="<?= htmlspecialchars($a['email'] ?? '') ?>" placeholder="email@contoh.com">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label small fw-bold">No. WhatsApp/Telepon</label>
                                <input type="text" name="phone_number" class="form-control" required value="<?= htmlspecialchars($a['phone_number'] ?? '') ?>" placeholder="08123xxx">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold">Password Baru</label>
                            <input type="password" name="password" class="form-control" placeholder="Biarkan kosong jika tidak diubah">
                            <small class="text-muted">Minimal 6 karakter jika ingin mengganti password.</small>
                        </div>
                        
                        <?php if (is_super_admin()): ?>
                        <div class="mb-4">
                            <label class="form-label small fw-bold">Hak Akses (Role)</label>
                            <select name="role" class="form-select" required>
                                <option value="Admin" <?= $a['role'] == 'Admin' ? 'selected' : '' ?>>Admin</option>
                                <option value="Super Admin" <?= $a['role'] == 'Super Admin' ? 'selected' : '' ?>>Super Admin</option>
                            </select>
                        </div>
                        <?php endif; ?>

                        <div class="d-grid mt-2">
                            <button type="submit" class="btn btn-accent fw-bold py-2 rounded-pill shadow-sm small">Simpan Perubahan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../template/footer.php'; ?>
