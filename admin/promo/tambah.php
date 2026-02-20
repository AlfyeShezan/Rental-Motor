<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../template/auth.php';
require_once __DIR__ . '/../../config/helper.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = strtoupper($_POST['code']);
    $type = $_POST['type'];
    $value = $_POST['value'];
    $valid_from = $_POST['valid_from'];
    $valid_to = $_POST['valid_to'];
    $usage_limit = $_POST['usage_limit'] ?: null;
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    try {
        $stmt = $pdo->prepare("INSERT INTO promos (code, discount_type, discount_value, valid_from, valid_to, usage_limit, is_active) 
                              VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$code, $type, $value, $valid_from, $valid_to, $usage_limit, $is_active]);
        redirect_with_alert('index.php', 'Promo baru berhasil dibuat.');
    } catch (PDOException $e) {
        $_SESSION['alert_message'] = "Gagal membuat promo: " . $e->getMessage();
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
            <h5 class="mb-0 fw-bold ms-3">Buat Promo Baru</h5>
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
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Kode Promo (Caps)</label>
                            <input type="text" name="code" class="form-control" required placeholder="Contoh: HEMAT10" style="text-transform: uppercase;">
                        </div>
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Tipe Diskon</label>
                                <select name="type" class="form-select" required>
                                    <option value="Percent">Persentase (%)</option>
                                    <option value="Fixed">Potongan Tetap (Rp)</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Nilai Diskon</label>
                                <input type="number" name="value" class="form-control" required placeholder="Contoh: 10 atau 50000">
                            </div>
                        </div>
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Mulai Berlaku</label>
                                <input type="date" name="valid_from" class="form-control" required value="<?= date('Y-m-d') ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Hingga Tanggal</label>
                                <input type="date" name="valid_to" class="form-control" required value="<?= date('Y-m-d', strtotime('+7 days')) ?>">
                            </div>
                        </div>
                        <div class="mb-4">
                            <label class="form-label small fw-bold">Batas Penggunaan (Opsional)</label>
                            <input type="number" name="usage_limit" class="form-control" placeholder="Biarkan kosong jika tidak terbatas">
                        </div>
                        <div class="form-check form-switch mb-4">
                            <input class="form-check-input" type="checkbox" name="is_active" id="activeSwitch" checked>
                            <label class="form-check-label fw-bold small" for="activeSwitch">Aktifkan Kode Promo</label>
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-accent fw-bold py-2 rounded-pill shadow-sm small">Simpan Promo</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../template/footer.php'; ?>
