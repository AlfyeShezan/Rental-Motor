<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../template/auth.php';
require_once __DIR__ . '/../../config/helper.php';

// Fetch all motor models with stock count
try {
    $stmt = $pdo->query("SELECT mm.*, 
                         (SELECT image_path FROM motor_images WHERE model_id = mm.id LIMIT 1) as primary_image,
                         (SELECT COUNT(*) FROM motors WHERE model_id = mm.id) as total_stok,
                         (SELECT COUNT(*) FROM motors m 
                          WHERE m.model_id = mm.id 
                          AND m.status = 'Tersedia' 
                          AND m.is_active = 1 
                          AND NOT EXISTS (SELECT 1 FROM bookings b WHERE b.motor_id = m.id AND b.status = 'Pending')) as tersedia_stok,
                         (SELECT COUNT(*) FROM motors m 
                          WHERE m.model_id = mm.id 
                          AND m.is_active = 1 
                          AND EXISTS (SELECT 1 FROM bookings b WHERE b.motor_id = m.id AND b.status = 'Pending')) as pending_stok
                         FROM motor_models mm 
                         ORDER BY mm.created_at DESC");
    $motors = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Error fetching motor models: " . $e->getMessage());
}

include __DIR__ . '/../template/header.php';
include __DIR__ . '/../template/sidebar.php';
?>

<div id="content">
    <!-- Top Navbar -->
    <nav class="navbar top-navbar">
        <div class="container-fluid">
            <button type="button" id="sidebarCollapse" class="btn theme-bg-card theme-text-heading rounded-circle shadow-sm me-3 border-0 d-lg-none">
                <i class="fas fa-bars theme-text-heading"></i>
            </button>
            <h5 class="mb-0 fw-bold ms-2 theme-text-heading">Katalog Armada</h5>
            <div class="ms-auto d-flex align-items-center gap-3">
                <a href="tambah.php" class="btn btn-accent rounded-pill px-3 py-1.5 fw-bold shadow-sm small">
                    <i class="fas fa-plus-circle me-1"></i> Tambah Armada
                </a>
            </div>

        </div>
    </nav>

    <div class="main-content">
        <div class="row align-items-center mb-4">
            <div class="col">
                <h4 class="fw-bold mb-0">Manajemen Motor</h4>
                <p class="text-muted small mb-0">Kelola informasi unit, harga, dan status ketersediaan armada.</p>
            </div>
            <div class="col-auto">
                <span class="badge theme-bg-card theme-text-heading shadow-sm px-3 py-2 rounded-pill fw-bold border theme-border">
                    Total: <?= count($motors) ?> Unit
                </span>
            </div>
        </div>

        <?php display_alert(); ?>

        <div class="card border-0 shadow-sm rounded-4 overflow-hidden theme-bg-card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="theme-bg-light-soft">
                            <tr>
                                <th class="ps-4 py-3 border-0">PREVIEW</th>
                                <th class="py-3 border-0">MODEL NAME</th>
                                <th class="py-3 border-0">TYPE / YEAR</th>
                                <th class="py-3 border-0">STOK (TOTAL/READY)</th>
                                <th class="py-3 border-0">PRICE / DAY</th>
                                <th class="py-3 border-0">ACTIVE</th>
                                <th class="text-end pe-4 py-3 border-0">ACTIONS</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php if (empty($motors)): ?>
                                <tr>
                                    <td colspan="7" class="text-center py-5">
                                        <div class="opacity-20 mb-3"><i class="fas fa-motorcycle fa-4x"></i></div>
                                        <h5 class="fw-bold">Belum Ada Model</h5>
                                        <p class="text-muted">Mulai dengan menambahkan model motor pertama Anda.</p>
                                        <a href="tambah.php" class="btn btn-accent rounded-pill px-3 py-1.5 fw-bold shadow-sm small">Tambah Sekarang</a>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($motors as $m): ?>
                                    <tr>
                                        <td class="ps-4">
                                            <div class="position-relative d-inline-block">
                                                <img src="<?= $m['primary_image'] ? BASE_URL . 'uploads/motors/' . $m['primary_image'] : 'https://placehold.co/120x80?text=No+Photo' ?>" 
                                                     class="rounded-3 shadow-sm" width="100" height="65" style="object-fit: cover; border: 1px solid rgba(0,0,0,0.05);">
                                                <?php if($m['is_popular']): ?>
                                                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-warning text-dark border border-white" style="font-size: 0.6rem;">
                                                        <i class="fas fa-star"></i>
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="fw-bold theme-text-primary mb-0" style="font-size: 1rem;"><?= htmlspecialchars($m['brand'] . ' ' . $m['model']) ?></div>
                                            <small class="text-muted">ID Model: #<?= $m['id'] ?></small>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <span class="badge theme-bg-alt theme-text-heading px-2 py-1 rounded small fw-600"><?= $m['type'] ?></span>
                                                <span class="small theme-text-muted fw-500"><?= $m['year'] ?></span>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="fw-bold">
                                                <span class="text-primary" title="Total Unit"><?= $m['total_stok'] ?></span> 
                                                <span class="text-muted">/</span>
                                                <span class="text-success" title="Tersedia (Siap Sewa)"><?= $m['tersedia_stok'] ?></span>
                                                <?php if($m['pending_stok'] > 0): ?>
                                                    <span class="text-muted">/</span>
                                                    <span class="text-warning" title="Pending (Menunggu Pembayaran)"><?= $m['pending_stok'] ?></span>
                                                <?php endif; ?>
                                                <small class="text-muted fw-normal ms-1">Units</small>
                                            </div>
                                            <a href="view_units.php?model_id=<?= $m['id'] ?>" class="small text-accent text-decoration-none fw-bold">Kelola Unit <i class="fas fa-arrow-right ms-1" style="font-size: 0.7rem;"></i></a>
                                        </td>
                                        <td>
                                            <div class="fw-800 theme-text-heading"><?= format_rupiah($m['price_per_day']) ?></div>
                                        </td>
                                        <td>
                                            <span class="badge <?= $m['is_active'] ? 'bg-success' : 'bg-danger' ?> bg-opacity-10 text-<?= $m['is_active'] ? 'success' : 'danger' ?> px-2 py-1" style="font-size: 0.7rem;">
                                                <?= $m['is_active'] ? 'AKTIF' : 'NON-AKTIF' ?>
                                            </span>
                                        </td>
                                        <td class="text-end pe-4">
                                            <div class="d-flex justify-content-end gap-2">
                                                <a href="edit.php?id=<?= $m['id'] ?>" class="btn theme-bg-light-soft btn-xs rounded-pill px-2 py-1 small fw-bold border theme-border" title="Edit Model">
                                                    <i class="fas fa-edit theme-text-primary"></i>
                                                </a>
                                                <a href="hapus.php?id=<?= $m['id'] ?>&csrf_token=<?= generate_csrf_token() ?>" class="btn theme-bg-light-soft btn-xs rounded-pill px-2 py-1 small fw-bold border theme-border hover-danger" title="Hapus Model" onclick="return confirm('Menghapus model akan menghapus semua unit dan gambar terkait. Lanjutkan?')">
                                                    <i class="fas fa-trash text-danger"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../template/footer.php'; ?>
