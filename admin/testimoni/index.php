<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../template/auth.php';
require_once __DIR__ . '/../../config/helper.php';

// Fetch all testimonials
try {
    $stmt = $pdo->query("SELECT * FROM testimonials ORDER BY created_at DESC");
    $testimonials = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Error fetching testimonials: " . $e->getMessage());
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
            <h5 class="mb-0 fw-bold ms-2">Moderasi Testimoni</h5>
            <div class="ms-auto flex-shrink-0">
                <button class="btn btn-accent rounded-pill px-3 py-1.5 fw-bold shadow-sm small" onclick="window.location.href='tambah.php'">
                    <i class="fas fa-plus-circle me-1"></i> Tambah Testimoni
                </button>
            </div>

        </div>
    </nav>

    <div class="main-content">
        <div class="row align-items-center mb-4">
            <div class="col">
                <h4 class="fw-bold mb-0">Ulasan & Feedback</h4>
                <p class="text-muted small mb-0">Kelola testimoni pelanggan yang akan ditampilkan di halaman utama website.</p>
            </div>
        </div>

        <?php display_alert(); ?>

        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4 py-3 border-0">CUSTOMER</th>
                                <th class="py-3 border-0">RATING</th>
                                <th class="py-3 border-0">CONTENT</th>
                                <th class="py-3 border-0">DISPLAY STATUS</th>
                                <th class="text-end pe-4 py-3 border-0">ACTION</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php if (empty($testimonials)): ?>
                                <tr>
                                    <td colspan="5" class="text-center py-5">
                                        <div class="opacity-20 mb-3"><i class="fas fa-comments fa-4x"></i></div>
                                        <p class="text-muted">Belum ada testimoni masuk.</p>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($testimonials as $t): ?>
                                    <tr>
                                        <td class="ps-4">
                                            <div class="d-flex align-items-center gap-3">
                                                <?php 
                                                $admin_photo_path = __DIR__ . '/../../uploads/testimoni/' . $t['photo'];
                                                if ($t['photo'] && file_exists($admin_photo_path)): 
                                                ?>
                                                    <img src="<?= BASE_URL ?>uploads/testimoni/<?= $t['photo'] ?>" class="rounded shadow-sm" style="width: 80px; aspect-ratio: 4/3; object-fit: cover; border: 1px solid var(--accent-glow);">
                                                <?php else: ?>
                                                    <div class="bg-alt text-primary rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width: 45px; height: 45px; border: 1px solid var(--accent-glow);">
                                                        <?= strtoupper(substr($t['name'], 0, 1)) ?>
                                                    </div>
                                                <?php endif; ?>
                                                <div class="fw-bold text-dark"><?= htmlspecialchars($t['name']) ?></div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="text-accent small d-flex gap-1">
                                                <?php for($i=1; $i<=5; $i++): ?>
                                                    <i class="<?= $i <= $t['rating'] ? 'fas' : 'far' ?> fa-star"></i>
                                                <?php endfor; ?>
                                            </div>
                                            <small class="text-muted fw-bold" style="font-size: 0.65rem;"><?= $t['rating'] ?> OUT OF 5</small>
                                        </td>
                                        <td>
                                            <p class="mb-0 small text-muted fst-italic" style="max-width: 300px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                                "<?= htmlspecialchars($t['message']) ?>"
                                            </p>
                                        </td>
                                        <td>
                                            <span class="badge rounded-pill bg-<?= $t['is_displayed'] ? 'success' : 'secondary' ?> bg-opacity-10 text-<?= $t['is_displayed'] ? 'success' : 'secondary' ?> px-3 py-2 fw-bold">
                                                <i class="fas <?= $t['is_displayed'] ? 'fa-eye' : 'fa-eye-slash' ?> me-1 opacity-50"></i>
                                                <?= $t['is_displayed'] ? 'TAMPIL' : 'DISEMBUNYIKAN' ?>
                                            </span>
                                        </td>
                                        <td class="text-end pe-4">
                                            <div class="d-flex justify-content-end gap-2">
                                                <a href="edit.php?id=<?= $t['id'] ?>" class="btn btn-light-soft btn-xs rounded-pill px-2 py-1 small fw-bold border" title="Moderasi">
                                                    <i class="fas fa-user-check text-primary"></i>
                                                </a>
                                                <a href="hapus.php?id=<?= $t['id'] ?>&csrf_token=<?= generate_csrf_token() ?>" class="btn btn-light-soft btn-xs rounded-pill px-2 py-1 small fw-bold border hover-danger" onclick="return confirm('Hapus testimoni ini?')" title="Hapus">
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
