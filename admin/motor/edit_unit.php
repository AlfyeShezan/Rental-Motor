<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../template/auth.php';
require_once __DIR__ . '/../../config/helper.php';

$id = $_GET['id'] ?? 0;

try {
    $stmt = $pdo->prepare("SELECT m.*, mm.brand, mm.model 
                          FROM motors m 
                          JOIN motor_models mm ON m.model_id = mm.id 
                          WHERE m.id = ?");
    $stmt->execute([$id]);
    $unit = $stmt->fetch();

    if (!$unit) {
        redirect_with_alert('index.php', 'Unit tidak ditemukan.', 'danger');
    }

    $model_id = $unit['model_id'];

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        verify_csrf_token($_POST['csrf_token'] ?? '');

        $plate_number = strtoupper(trim($_POST['plate_number']));
        $color = trim($_POST['color']);
        $status = $_POST['status'];
        $is_active = isset($_POST['is_active']) ? 1 : 0;

        // Check if plate number is already used by another ID
        $stmt_check = $pdo->prepare("SELECT id FROM motors WHERE plate_number = ? AND id != ?");
        $stmt_check->execute([$plate_number, $id]);
        if ($stmt_check->fetch()) {
            $_SESSION['alert_message'] = "Gagal: Plat nomor $plate_number sudah terdaftar pada unit lain.";
            $_SESSION['alert_type'] = "danger";
        } else {
            $stmt_u = $pdo->prepare("UPDATE motors SET plate_number = ?, color = ?, status = ?, is_active = ? WHERE id = ?");
            if ($stmt_u->execute([$plate_number, $color, $status, $is_active, $id])) {
                require_once __DIR__ . '/../../config/backup_helper.php';
                trigger_auto_backup($pdo);
                redirect_with_alert("view_units.php?model_id=$model_id", 'Unit berhasil diperbarui.');
            }
        }
    }
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
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
            <h5 class="mb-0 fw-bold ms-2">Edit Unit: <?= htmlspecialchars($unit['plate_number']) ?></h5>
            <div class="ms-auto">
                <a href="view_units.php?model_id=<?= $model_id ?>" class="btn btn-light rounded-pill px-3 py-1.5 border small"><i class="fas fa-arrow-left me-1"></i> Kembali</a>
            </div>
        </div>
    </nav>

    <div class="main-content">
        <?php display_alert(); ?>

        <div class="row justify-content-center">
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm rounded-4 p-4">
                    <div class="mb-4">
                        <h6 class="fw-bold mb-1 text-primary">Model: <?= htmlspecialchars($unit['brand'] . ' ' . $unit['model']) ?></h6>
                        <p class="text-muted small">ID Unit: <?= $id ?></p>
                    </div>

                    <form action="" method="POST">
                        <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                        
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">Plat Nomor</label>
                            <input type="text" name="plate_number" class="form-control bg-light-soft border text-uppercase" required value="<?= htmlspecialchars($unit['plate_number']) ?>">
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">Warna</label>
                            <input type="text" name="color" class="form-control bg-light-soft border" required value="<?= htmlspecialchars($unit['color']) ?>">
                        </div>

                        <div class="mb-4">
                            <label class="form-label small fw-bold text-muted">Status Saat Ini</label>
                            <select name="status" class="form-select bg-light-soft border">
                                <option value="Tersedia" <?= $unit['status'] == 'Tersedia' ? 'selected' : '' ?>>Tersedia (Ready)</option>
                                <option value="Disewa" <?= $unit['status'] == 'Disewa' ? 'selected' : '' ?>>Sedang Disewa</option>
                                <option value="Maintenance" <?= $unit['status'] == 'Maintenance' ? 'selected' : '' ?>>Maintenance / Perbaikan</option>
                            </select>
                            <small class="text-muted">Status 'Disewa' akan otomatis berubah jika ada pembayaran masuk.</small>
                        </div>

                        <div class="form-check form-switch mb-4">
                            <input class="form-check-input" type="checkbox" name="is_active" id="activeSwitch" <?= $unit['is_active'] ? 'checked' : '' ?>>
                            <label class="form-check-label fw-bold small" for="activeSwitch">Unit Aktif (Siap Disewakan)</label>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary fw-bold py-2 rounded-pill shadow-sm">Simpan Perubahan</button>
                            <a href="view_units.php?model_id=<?= $model_id ?>" class="btn btn-light fw-bold py-2 rounded-pill border">Batal</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../template/footer.php'; ?>
