<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../template/auth.php';
require_once __DIR__ . '/../../config/helper.php';

// Handle Delete
if (isset($_GET['delete'])) {
    // CSRF Check
    verify_csrf_token($_GET['csrf_token'] ?? '');

    $id = $_GET['delete'];
    try {
        $stmt = $pdo->prepare("DELETE FROM promos WHERE id = ?");
        $stmt->execute([$id]);
        require_once __DIR__ . '/../../config/backup_helper.php';
        trigger_auto_backup($pdo);
        redirect_with_alert('index.php', 'Promo berhasil dihapus.');
    } catch (PDOException $e) {
        $error = "Gagal menghapus promo: " . $e->getMessage();
    }
}

// Handle Add/Edit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF Check
    verify_csrf_token($_POST['csrf_token'] ?? '');

    $code = strtoupper(trim($_POST['code']));
    $type = $_POST['discount_type'];
    $val = $_POST['discount_value'];
    $start = $_POST['valid_from'];
    $end = $_POST['valid_to'];
    $limit = !empty($_POST['usage_limit']) ? $_POST['usage_limit'] : NULL;
    $id = $_POST['promo_id'] ?? null;

    try {
        if ($id) {
            $stmt = $pdo->prepare("UPDATE promos SET code=?, discount_type=?, discount_value=?, valid_from=?, valid_to=?, usage_limit=? WHERE id=?");
            $stmt->execute([$code, $type, $val, $start, $end, $limit, $id]);
            $msg = "Promo berhasil diperbarui.";
        } else {
            $stmt = $pdo->prepare("INSERT INTO promos (code, discount_type, discount_value, valid_from, valid_to, usage_limit) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$code, $type, $val, $start, $end, $limit]);
            $msg = "Promo baru berhasil ditambahkan.";
        }
        require_once __DIR__ . '/../../config/backup_helper.php';
        trigger_auto_backup($pdo);
        redirect_with_alert('index.php', $msg);
    } catch (PDOException $e) {
        $error = "Gagal menyimpan promo: " . $e->getMessage();
    }
}

// Fetch all promos
$promos = $pdo->query("SELECT * FROM promos ORDER BY created_at DESC")->fetchAll();

include __DIR__ . '/../template/header.php';
include __DIR__ . '/../template/sidebar.php';
?>

<div id="content">
    <nav class="navbar top-navbar">
        <div class="container-fluid">
            <button type="button" id="sidebarCollapse" class="btn theme-bg-card theme-text-heading rounded-circle shadow-sm me-3 border-0 d-lg-none">
                <i class="fas fa-bars"></i>
            </button>
            <h5 class="mb-0 fw-bold ms-2 theme-text-heading">Manajemen Promo & Kupon</h5>
            <div class="ms-auto flex-shrink-0">
                <button class="btn btn-accent rounded-pill px-3 py-1.5 fw-bold shadow-sm small" data-bs-toggle="modal" data-bs-target="#addPromoModal">
                    <i class="fas fa-plus-circle me-1"></i> Tambah Promo
                </button>
            </div>
        </div>
    </nav>

    <div class="main-content">
        <div class="mb-4">
            <h4 class="fw-bold mb-0">Program Promo</h4>
            <p class="text-muted small mb-0">Kelola kode kupon diskon untuk pelanggan.</p>
        </div>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>
        <?php display_alert(); ?>

        <div class="card border-0 shadow-sm rounded-4 theme-bg-card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="theme-bg-alt theme-text-heading">
                            <tr>
                                <th class="ps-4 py-3">Kode Promo</th>
                                <th class="py-3">Diskon</th>
                                <th class="py-3">Masa Berlaku</th>
                                <th class="py-3">Limit/Digunakan</th>
                                <th class="py-3">Status</th>
                                <th class="py-3 text-end pe-4">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="theme-text-heading">
                            <?php if (empty($promos)): ?>
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-muted">Belum ada promo yang dibuat.</td>
                                </tr>
                            <?php endif; ?>
                            <?php foreach ($promos as $p): ?>
                                <tr>
                                    <td class="ps-4">
                                        <span class="fw-bold text-accent"><?= $p['code'] ?></span>
                                    </td>
                                    <td>
                                        <?= $p['discount_type'] === 'Percent' ? $p['discount_value'] . '%' : format_rupiah($p['discount_value']) ?>
                                    </td>
                                    <td>
                                        <div class="small">
                                            <div><i class="far fa-calendar-check me-1 text-success"></i> <?= format_date_id($p['valid_from']) ?></div>
                                            <div><i class="far fa-calendar-times me-1 text-danger"></i> <?= format_date_id($p['valid_to']) ?></div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border">
                                            <?= $p['used_count'] ?> / <?= $p['usage_limit'] ?? '∞' ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php 
                                        $today = date('Y-m-d');
                                        if (!$p['is_active']) {
                                            echo '<span class="badge bg-secondary">Nonaktif</span>';
                                        } elseif ($today < $p['valid_from']) {
                                            echo '<span class="badge bg-info">Mendatang</span>';
                                        } elseif ($today > $p['valid_to']) {
                                            echo '<span class="badge bg-danger">Kadaluarsa</span>';
                                        } else {
                                            echo '<span class="badge bg-success">Aktif</span>';
                                        }
                                        ?>
                                    </td>
                                    <td class="text-end pe-4">
                                        <div class="btn-group">
                                            <button class="btn btn-sm btn-light-soft rounded-circle me-1 edit-promo" 
                                                    data-id="<?= $p['id'] ?>"
                                                    data-code="<?= $p['code'] ?>"
                                                    data-type="<?= $p['discount_type'] ?>"
                                                    data-val="<?= $p['discount_value'] ?>"
                                                    data-from="<?= $p['valid_from'] ?>"
                                                    data-to="<?= $p['valid_to'] ?>"
                                                    data-limit="<?= $p['usage_limit'] ?>"
                                                    data-bs-toggle="modal" data-bs-target="#addPromoModal">
                                                <i class="fas fa-edit text-primary"></i>
                                            </button>
                                            <a href="?delete=<?= $p['id'] ?>&csrf_token=<?= generate_csrf_token() ?>" class="btn btn-sm btn-light-soft rounded-circle" onclick="return confirm('Hapus promo ini?')">
                                                <i class="fas fa-trash text-danger"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add/Edit Modal -->
<div class="modal fade" id="addPromoModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 theme-bg-card">
            <div class="modal-header border-0 pb-0">
                <h5 class="fw-bold theme-text-heading" id="modalTitle">Tambah Promo Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="" method="POST">
                <!-- CSRF Token -->
                <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                <input type="hidden" name="promo_id" id="promo_id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-uppercase">Kode Promo</label>
                        <input type="text" name="code" id="modal_code" class="form-control rounded-3" placeholder="Contoh: JOGJAASIK" required>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-bold text-uppercase">Tipe Diskon</label>
                            <select name="discount_type" id="modal_type" class="form-select rounded-3">
                                <option value="Percent">Persentase (%)</option>
                                <option value="Nominal">Nominal (Rp)</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold text-uppercase">Nilai Diskon</label>
                            <input type="number" name="discount_value" id="modal_val" class="form-control rounded-3" required>
                        </div>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-bold text-uppercase">Mulai Berlaku</label>
                            <input type="date" name="valid_from" id="modal_from" class="form-control rounded-3" value="<?= date('Y-m-d') ?>" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold text-uppercase">Berakhir Pada</label>
                            <input type="date" name="valid_to" id="modal_to" class="form-control rounded-3" required>
                        </div>
                    </div>
                    <div class="mb-0">
                        <label class="form-label small fw-bold text-uppercase">Limit Penggunaan (Kosongkan jika tak terbatas)</label>
                        <input type="number" name="usage_limit" id="modal_limit" class="form-control rounded-3" placeholder="∞">
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="submit" class="btn btn-accent w-100 rounded-pill fw-bold py-2 small shadow-sm">Simpan Promo</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.querySelectorAll('.edit-promo').forEach(btn => {
    btn.addEventListener('click', function() {
        document.getElementById('modalTitle').innerText = 'Edit Promo';
        document.getElementById('promo_id').value = this.dataset.id;
        document.getElementById('modal_code').value = this.dataset.code;
        document.getElementById('modal_type').value = this.dataset.type;
        document.getElementById('modal_val').value = this.dataset.val;
        document.getElementById('modal_from').value = this.dataset.from;
        document.getElementById('modal_to').value = this.dataset.to;
        document.getElementById('modal_limit').value = this.dataset.limit || '';
    });
});

document.getElementById('addPromoModal').addEventListener('hidden.bs.modal', function () {
    document.getElementById('modalTitle').innerText = 'Tambah Promo Baru';
    document.getElementById('promo_id').value = '';
    document.querySelector('form').reset();
});
</script>

<?php include __DIR__ . '/../template/footer.php'; ?>
