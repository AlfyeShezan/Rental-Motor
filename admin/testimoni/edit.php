<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../template/auth.php';
require_once __DIR__ . '/../../config/helper.php';

$id = $_GET['id'] ?? 0;

// Fetch Testimonial Data
try {
    $stmt = $pdo->prepare("SELECT * FROM testimonials WHERE id = ?");
    $stmt->execute([$id]);
    $t = $stmt->fetch();

    if (!$t) {
        redirect_with_alert('index.php', 'Testimoni tidak ditemukan.', 'danger');
    }
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Verify CSRF Token
    verify_csrf_token($_POST['csrf_token'] ?? '');

    $name = $_POST['name'];
    $rating = $_POST['rating'];
    $message = $_POST['message'];
    $is_displayed = isset($_POST['is_displayed']) ? 1 : 0;
    $photo_name = $t['photo'];

    // Handle New Photo Upload (Secure)
    if (!empty($_FILES['photo']['name'])) {
        // Secure Validation
        $check = validate_image_upload('photo');
        if ($check !== true) {
            redirect_with_alert('edit.php?id=' . $id, ($check['error'] ?? 'Gagal upload foto.'));
            exit;
        }

        $files = $_FILES['photo'];
        $upload_dir = __DIR__ . '/../../uploads/testimoni/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
        
        $ext = strtolower(pathinfo($files['name'], PATHINFO_EXTENSION));
        $new_photo_name = 'testi_' . time() . '.' . $ext;
        
        if (move_uploaded_file($files['tmp_name'], $upload_dir . $new_photo_name)) {
            // Remove old photo
            if ($photo_name && file_exists($upload_dir . $photo_name)) {
                @unlink($upload_dir . $photo_name);
            }
            $photo_name = $new_photo_name;
        }
    }

    try {
        $stmt = $pdo->prepare("UPDATE testimonials SET name=?, rating=?, message=?, photo=?, is_displayed=? WHERE id=?");
        $stmt->execute([$name, $rating, $message, $photo_name, $is_displayed, $id]);
        require_once __DIR__ . '/../../config/backup_helper.php';
        trigger_auto_backup($pdo);
        redirect_with_alert('index.php', 'Testimoni berhasil diperbarui.');
    } catch (PDOException $e) {
        $_SESSION['alert_message'] = "Gagal memperbarui: " . $e->getMessage();
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
            <h5 class="mb-0 fw-bold ms-3">Moderasi Dokumentasi Sewa</h5>
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
                    <form action="" method="POST" enctype="multipart/form-data">
                        <!-- CSRF Protection -->
                        <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Nama Pelanggan</label>
                            <input type="text" name="name" class="form-control" required value="<?= htmlspecialchars($t['name']) ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Rating</label>
                            <select name="rating" class="form-select" required>
                                <option value="5" <?= $t['rating'] == 5 ? 'selected' : '' ?>>5 - Sangat Puas</option>
                                <option value="4" <?= $t['rating'] == 4 ? 'selected' : '' ?>>4 - Puas</option>
                                <option value="3" <?= $t['rating'] == 3 ? 'selected' : '' ?>>3 - Cukup</option>
                                <option value="2" <?= $t['rating'] == 2 ? 'selected' : '' ?>>2 - Kecewa</option>
                                <option value="1" <?= $t['rating'] == 1 ? 'selected' : '' ?>>1 - Sangat Kecewa</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Isi Testimoni</label>
                            <textarea name="message" class="form-control" rows="5" required><?= htmlspecialchars($t['message']) ?></textarea>
                        </div>
                        <div class="mb-4">
                            <label class="form-label small fw-bold">Foto Bukti / Dokumentasi Sewa</label>
                            <?php if ($t['photo']): ?>
                                <div class="mb-2">
                                    <img src="<?= BASE_URL ?>uploads/testimoni/<?= $t['photo'] ?>" class="rounded shadow-sm" style="max-width: 200px; max-height: 200px; object-fit: cover;">
                                </div>
                            <?php endif; ?>
                            <input type="file" name="photo" class="form-control" accept="image/*">
                            <small class="text-muted">Biarkan kosong jika tidak ingin mengubah dokumentasi.</small>
                        </div>
                        <div class="form-check form-switch mb-4">
                            <input class="form-check-input" type="checkbox" name="is_displayed" id="displaySwitch" <?= $t['is_displayed'] ? 'checked' : '' ?>>
                            <label class="form-check-label fw-bold small" for="displaySwitch">Tampilkan di Halaman Utama</label>
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-accent fw-bold py-2 rounded-pill shadow-sm small">Simpan Perubahan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../template/footer.php'; ?>
