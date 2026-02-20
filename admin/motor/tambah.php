<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../template/auth.php';
require_once __DIR__ . '/../../config/helper.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Standardized CSRF Check
    verify_csrf_token($_POST['csrf_token'] ?? '');

    $brand = filter_var($_POST['brand'], FILTER_SANITIZE_STRING);
    $model = filter_var($_POST['model'], FILTER_SANITIZE_STRING);
    $type = $_POST['type'];
    $year = (int)$_POST['year'];
    $plate_number = filter_var($_POST['plate_number'], FILTER_SANITIZE_STRING);
    $color = filter_var($_POST['color'], FILTER_SANITIZE_STRING);
    $description = filter_var($_POST['description'], FILTER_SANITIZE_STRING);
    $price = (int)$_POST['price'];
    $stok = (int)($_POST['stok'] ?? 1);
    $status = $_POST['status'];
    $is_popular = isset($_POST['is_popular']) ? 1 : 0;
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    $errors = [];

    // Secure Image Validation
    if (!empty($_FILES['images']['name'][0])) {
        // We'll use a slightly modified validation loop for multiple files
        foreach ($_FILES['images']['name'] as $key => $name) {
            // Manually check each file since validate_image_upload is for single files
            $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
            $allowed_ext = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
            $allowed_mime = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
            
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($finfo, $_FILES['images']['tmp_name'][$key]);
            finfo_close($finfo);

            if (!in_array($ext, $allowed_ext) || !in_array($mime, $allowed_mime)) {
                $errors[] = "File '$name' ditolak (Bukan gambar yang valid).";
            }
            if ($_FILES['images']['size'][$key] > 10 * 1024 * 1024) {
                $detected = round($_FILES['images']['size'][$key] / 1024 / 1024, 2);
                $errors[] = "File '$name' terlalu besar (Maksimal 10MB). Terdeteksi: {$detected}MB";
            }
        }
    }

    if (empty($errors)) {
        require_once __DIR__ . '/../../config/backup_helper.php';
        try {
            $pdo->beginTransaction();
            
            $stmt = $pdo->prepare("INSERT INTO motor_models (brand, model, type, year, description, price_per_day, is_popular, is_active) 
                                  VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$brand, $model, $type, $year, $description, $price, $is_popular, $is_active]);
            $model_id = $pdo->lastInsertId();

            // Handle Multiple Images linked to Model
            if (!empty($_FILES['images']['name'][0])) {
                $upload_dir = __DIR__ . '/../../uploads/motors/';
                foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
                    $ext = pathinfo($_FILES['images']['name'][$key], PATHINFO_EXTENSION);
                    $filename = bin2hex(random_bytes(10)) . '_' . time() . '.' . $ext;
                    
                    if (move_uploaded_file($tmp_name, $upload_dir . $filename)) {
                        $stmt_img = $pdo->prepare("INSERT INTO motor_images (model_id, image_path, is_primary) VALUES (?, ?, ?)");
                        $stmt_img->execute([$model_id, $filename, ($key === 0 ? 1 : 0)]);
                    }
                }
            }

            $pdo->commit();
            trigger_auto_backup($pdo);
            redirect_with_alert('index.php', 'Model armada baru berhasil ditambahkan. Silakan tambahkan unit (plat nomor) di menu Kelola Unit.');
        } catch (PDOException $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            $_SESSION['alert_message'] = "Gagal menambah model: " . $e->getMessage();
            $_SESSION['alert_type'] = "danger";
        }
    } else {
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
            <button type="button" id="sidebarCollapse" class="btn btn-light rounded-circle shadow-sm me-3 border-0 d-lg-none">
                <i class="fas fa-bars text-dark"></i>
            </button>
            <h5 class="mb-0 fw-bold ms-2">Tambah Model Armada</h5>
            <div class="ms-auto">
                <a href="index.php" class="btn btn-light rounded-pill px-3 py-1.5 border small"><i class="fas fa-chevron-left me-1"></i> Kembali</a>
            </div>
        </div>
    </nav>

    <div class="main-content">
        <?php display_alert(); ?>

        <form action="" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
            <div class="row g-4">
                <div class="col-lg-8">
                    <div class="card border-0 shadow-sm rounded-4 p-4">
                        <h6 class="fw-bold mb-4 text-primary d-flex align-items-center gap-2">
                            <i class="fas fa-info-circle"></i> Informasi Dasar Model
                        </h6>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted">Brand / Merk</label>
                                <input type="text" name="brand" class="form-control bg-light-soft border" required placeholder="Contoh: Honda">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted">Model / Nama Motor</label>
                                <input type="text" name="model" class="form-control bg-light-soft border" required placeholder="Contoh: Vario 160">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold text-muted">Tipe</label>
                                <select name="type" class="form-select bg-light-soft border" required>
                                    <option value="Matic">Matic</option>
                                    <option value="Manual">Manual</option>
                                    <option value="Sport">Sport</option>
                                    <option value="Trail">Trail</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold text-muted">Tahun</label>
                                <input type="number" name="year" class="form-control bg-light-soft border" required value="<?= date('Y') ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold text-muted">Harga Per Hari (Rp)</label>
                                <input type="number" name="price" class="form-control bg-light-soft border" required placeholder="Contoh: 150000">
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-bold text-muted">Deskripsi</label>
                                <textarea name="description" class="form-control bg-light-soft border" rows="5" placeholder="Tuliskan spesifikasi detail atau fitur tambahan..."></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="card p-4 mt-4 border-0 shadow-sm rounded-4">
                        <h6 class="fw-bold mb-4 text-primary d-flex align-items-center gap-2">
                            <i class="fas fa-images"></i> Upload Foto Motor (Multiple)
                        </h6>
                        <div class="mb-3">
                            <input type="file" name="images[]" class="form-control bg-light-soft border" multiple accept="image/*">
                            <small class="text-muted">Foto pertama akan menjadi foto utama. Anda bisa memilih lebih dari satu file.</small>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card border-0 shadow-sm rounded-4 p-4">
                        <h6 class="fw-bold mb-4 text-primary d-flex align-items-center gap-2">
                            <i class="fas fa-toggle-on"></i> Atur Status
                        </h6>
                        <div class="mb-4">
                            <label class="form-label small fw-bold text-muted">Status Ketersediaan</label>
                            <select name="status" class="form-select bg-light-soft border">
                                <option value="Tersedia">Tersedia</option>
                                <option value="Disewa">Disewa</option>
                                <option value="Maintenance">Maintenance</option>
                            </select>
                        </div>
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" name="is_popular" id="popularSwitch" checked>
                            <label class="form-check-label fw-bold small text-dark" for="popularSwitch">Tampilkan di Motor Populer</label>
                        </div>
                        <div class="form-check form-switch mb-4">
                            <input class="form-check-input" type="checkbox" name="is_active" id="activeSwitch" checked>
                            <label class="form-check-label fw-bold small text-dark" for="activeSwitch">Aktifkan Motor (Live)</label>
                        </div>
                        <hr class="opacity-10 mb-4">
                        <div class="d-grid gap-3">
                            <button type="submit" class="btn btn-accent fw-bold py-2 rounded-pill shadow-sm small">
                                <i class="fas fa-save me-1"></i> Simpan Unit
                            </button>
                            <a href="index.php" class="btn btn-light fw-bold py-2 rounded-pill border small">Batal</a>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<?php include __DIR__ . '/../template/footer.php'; ?>
