<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../template/auth.php';
require_once __DIR__ . '/../../config/helper.php';

$id = $_GET['id'] ?? 0;

// Fetch Motor Model Data
try {
    $stmt = $pdo->prepare("SELECT * FROM motor_models WHERE id = ?");
    $stmt->execute([$id]);
    $motor = $stmt->fetch();

    if (!$motor) {
        redirect_with_alert('index.php', 'Armada tidak ditemukan.', 'danger');
    }

    // Fetch Images linked to Model
    $stmt_img = $pdo->prepare("SELECT * FROM motor_images WHERE model_id = ?");
    $stmt_img->execute([$id]);
    $images = $stmt_img->fetchAll();
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Standardized CSRF Check
    verify_csrf_token($_POST['csrf_token'] ?? '');

    $brand = $_POST['brand'];
    $model = $_POST['model'];
    $type = $_POST['type'];
    $year = $_POST['year'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $is_popular = isset($_POST['is_popular']) ? 1 : 0;
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare("UPDATE motor_models SET brand=?, model=?, type=?, year=?, description=?, price_per_day=?, is_popular=?, is_active=? WHERE id=?");
        $stmt->execute([$brand, $model, $type, $year, $description, $price, $is_popular, $is_active, $id]);

        // Handle Image Deletion
        if (!empty($_POST['delete_images'])) {
            foreach ($_POST['delete_images'] as $img_id) {
                $stmt_get_img = $pdo->prepare("SELECT image_path FROM motor_images WHERE id = ?");
                $stmt_get_img->execute([$img_id]);
                $filename = $stmt_get_img->fetchColumn();
                
                if ($filename) {
                    $path = __DIR__ . '/../../uploads/motors/' . $filename;
                    if (file_exists($path)) unlink($path);
                    
                    $stmt_del_img = $pdo->prepare("DELETE FROM motor_images WHERE id = ?");
                    $stmt_del_img->execute([$img_id]);
                }
            }
        }

        // Handle New Image Upload (Secure)
        if (!empty($_FILES['images']['name'][0])) {
            $files = $_FILES['images'];
            $upload_dir = __DIR__ . '/../../uploads/motors/';
            
            for ($i = 0; $i < count($files['name']); $i++) {
                $check = validate_image_upload_multi('images', $i);
                if ($check !== true) {
                    $pdo->rollBack();
                    redirect_with_alert("edit.php?id=$id", ($check['error'] ?? 'Gagal upload foto.'));
                    exit;
                }

                $ext = strtolower(pathinfo($files['name'][$i], PATHINFO_EXTENSION));
                $filename = bin2hex(random_bytes(10)) . '_' . time() . '_u' . $i . '.' . $ext;
                $target = $upload_dir . $filename;
                
                if (move_uploaded_file($files['tmp_name'][$i], $target)) {
                    // Update to use model_id
                    $stmt_img = $pdo->prepare("INSERT INTO motor_images (model_id, image_path) VALUES (?, ?)");
                    $stmt_img->execute([$id, $filename]);
                }
            }
        }

        $pdo->commit();
        require_once __DIR__ . '/../../config/backup_helper.php';
        trigger_auto_backup($pdo);
        redirect_with_alert('index.php', 'Data model armada berhasil diperbarui.');
    } catch (PDOException $e) {
        $pdo->rollBack();
        $_SESSION['alert_message'] = "Gagal memperbarui model: " . $e->getMessage();
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
            <h5 class="mb-0 fw-bold ms-3">Edit Motor: <?= $motor['brand'] . ' ' . $motor['model'] ?></h5>
            <div class="ms-auto">
                <a href="index.php" class="btn btn-light rounded-pill px-3 py-1.5 border small"><i class="fas fa-arrow-left me-1"></i> Kembali</a>
            </div>
        </div>
    </nav>

    <div class="main-content">
        <?php display_alert(); ?>

        <form action="" method="POST" enctype="multipart/form-data" id="editForm">
            <!-- CSRF Protection -->
            <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
           <div class="row g-4">
                <div class="col-lg-8">
                    <div class="card p-4">
                        <h6 class="fw-bold mb-4">Informasi Dasar</h6>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Brand / Merk</label>
                                <input type="text" name="brand" class="form-control" required value="<?= htmlspecialchars($motor['brand']) ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Model / Nama Motor</label>
                                <input type="text" name="model" class="form-control" required value="<?= htmlspecialchars($motor['model']) ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">Tipe</label>
                                <select name="type" class="form-select" required>
                                    <option value="Matic" <?= $motor['type'] == 'Matic' ? 'selected' : '' ?>>Matic</option>
                                    <option value="Manual" <?= $motor['type'] == 'Manual' ? 'selected' : '' ?>>Manual</option>
                                    <option value="Sport" <?= $motor['type'] == 'Sport' ? 'selected' : '' ?>>Sport</option>
                                    <option value="Trail" <?= $motor['type'] == 'Trail' ? 'selected' : '' ?>>Trail</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">Tahun</label>
                                <input type="number" name="year" class="form-control" required value="<?= $motor['year'] ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Harga Per Hari (Rp)</label>
                                <input type="number" name="price" class="form-control" required value="<?= $motor['price_per_day'] ?>">
                            </div>
                            <div class="col-12 mt-4">
                                <div class="p-3 border rounded-3 bg-light-soft d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="fw-bold mb-1">Manajemen Unit Fisik</h6>
                                        <p class="text-muted small mb-0">Kelola plat nomor, warna, dan status ketersediaan unit untuk model ini.</p>
                                    </div>
                                    <a href="view_units.php?model_id=<?= $id ?>" class="btn btn-sm btn-outline-primary px-3 rounded-pill fw-bold">
                                        <i class="fas fa-motorcycle me-1"></i> Kelola Unit
                                    </a>
                                </div>
                            </div>
                            <div class="col-12 mt-3">
                                <label class="form-label small fw-bold">Deskripsi</label>
                                <textarea name="description" class="form-control" rows="5"><?= htmlspecialchars($motor['description']) ?></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm rounded-4 p-4 mt-4">
                        <h6 class="fw-bold mb-4">Kelola Foto Model</h6>
                        
                        <div class="row g-3 mb-4">
                            <?php foreach ($images as $img): ?>
                                <div class="col-md-3">
                                    <div class="position-relative border rounded overflow-hidden">
                                        <img src="<?= BASE_URL ?>uploads/motors/<?= $img['image_path'] ?>" class="img-fluid" style="height: 120px; width: 100%; object-fit: cover;">
                                        <div class="form-check position-absolute top-0 end-0 m-2 bg-white bg-opacity-75 rounded px-2">
                                            <input class="form-check-input" type="checkbox" name="delete_images[]" value="<?= $img['id'] ?>" id="del_<?= $img['id'] ?>">
                                            <label class="form-check-label small text-danger fw-bold" for="del_<?= $img['id'] ?>">Hapus</label>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold">Tambah Foto Baru</label>
                            <input type="file" name="images[]" class="form-control" multiple accept="image/*">
                            <small class="text-muted">Foto ini akan muncul di halaman detail untuk model ini.</small>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card border-0 shadow-sm rounded-4 p-4">
                        <h6 class="fw-bold mb-4 d-flex align-items-center gap-2">
                            <i class="fas fa-cog text-primary"></i> Pengaturan
                        </h6>
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" name="is_popular" id="popularSwitch" <?= $motor['is_popular'] ? 'checked' : '' ?>>
                            <label class="form-check-label fw-bold small" for="popularSwitch">Tampilkan di Motor Populer</label>
                        </div>
                        <div class="form-check form-switch mb-4">
                            <input class="form-check-input" type="checkbox" name="is_active" id="activeSwitch" <?= $motor['is_active'] ? 'checked' : '' ?>>
                            <label class="form-check-label fw-bold small" for="activeSwitch">Aktifkan Model (Terlihat di User)</label>
                        </div>
                        <hr class="opacity-10">
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-accent fw-bold py-2 rounded-pill shadow-sm small">Simpan Perubahan</button>
                            <a href="index.php" class="btn btn-light fw-bold py-2 rounded-pill border small">Batal</a>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<?php include __DIR__ . '/../template/footer.php'; ?>
