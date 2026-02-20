<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../template/auth.php';
require_once __DIR__ . '/../../config/helper.php';

$model_id = isset($_GET['model_id']) ? (int)$_GET['model_id'] : 0;

// Fetch model details
try {
    $stmt_model = $pdo->prepare("SELECT * FROM motor_models WHERE id = ?");
    $stmt_model->execute([$model_id]);
    $model = $stmt_model->fetch();

    if (!$model) {
        redirect_with_alert('index.php', 'Model tidak ditemukan.', 'danger');
    }

    // Fetch all units for this model
    $stmt_units = $pdo->prepare("SELECT * FROM motors WHERE model_id = ? ORDER BY plate_number ASC");
    $stmt_units->execute([$model_id]);
    $units = $stmt_units->fetchAll();
} catch (PDOException $e) {
    die("Error fetching units: " . $e->getMessage());
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
            <h5 class="mb-0 fw-bold ms-2">Manage Units: <?= htmlspecialchars($model['brand'] . ' ' . $model['model']) ?></h5>
            <div class="ms-auto d-flex align-items-center gap-2">
                <a href="index.php" class="btn btn-light rounded-pill px-3 py-1.5 border small"><i class="fas fa-chevron-left me-1"></i> Back to Models</a>
                <a href="tambah_unit.php?model_id=<?= $model_id ?>" class="btn btn-accent rounded-pill px-3 py-1.5 fw-bold shadow-sm small">
                    <i class="fas fa-plus-circle me-1"></i> Add Unit (Plate)
                </a>
            </div>
        </div>
    </nav>

    <div class="main-content">
        <div class="row align-items-center mb-4">
            <div class="col">
                <h4 class="fw-bold mb-0">Physical Units Inventory</h4>
                <p class="text-muted small mb-0">List of all physical bikes with their unique plate numbers and current status.</p>
            </div>
            <div class="col-auto">
                <span class="badge theme-bg-card theme-text-heading shadow-sm px-3 py-2 rounded-pill fw-bold border theme-border">
                    Total units: <?= count($units) ?>
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
                                <th class="ps-4 py-3 border-0">PLATE NUMBER</th>
                                <th class="py-3 border-0">COLOR</th>
                                <th class="py-3 border-0">STATUS</th>
                                <th class="py-3 border-0">ACTIVE</th>
                                <th class="text-end pe-4 py-3 border-0">ACTIONS</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php if (empty($units)): ?>
                                <tr>
                                    <td colspan="5" class="text-center py-5">
                                        <div class="opacity-20 mb-3"><i class="fas fa-id-card fa-4x"></i></div>
                                        <h5 class="fw-bold">No Units Added Yet</h5>
                                        <p class="text-muted">Every model needs at least one physical unit (plate) to be rentable.</p>
                                        <a href="tambah_unit.php?model_id=<?= $model_id ?>" class="btn btn-accent rounded-pill px-3 py-1.5 fw-bold shadow-sm small">Add First Unit</a>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($units as $u): ?>
                                    <tr>
                                        <td class="ps-4">
                                            <span class="theme-text-heading small"><?= htmlspecialchars($u['plate_number']) ?></span>
                                        </td>
                                        <td><?= htmlspecialchars($u['color']) ?></td>
                                        <td>
                                            <span class="badge rounded-pill bg-<?= get_status_badge($u['status']) ?> bg-opacity-10 text-<?= get_status_badge($u['status']) ?> px-3 py-2 fw-bold">
                                                <?= $u['status'] ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge <?= $u['is_active'] ? 'bg-success' : 'bg-danger' ?> bg-opacity-10 text-<?= $u['is_active'] ? 'success' : 'danger' ?> px-2 py-1" style="font-size: 0.7rem;">
                                                <?= $u['is_active'] ? 'LIVE' : 'HIDDEN' ?>
                                            </span>
                                        </td>
                                        <td class="text-end pe-4">
                                            <div class="d-flex justify-content-end gap-2">
                                                <a href="edit_unit.php?id=<?= $u['id'] ?>" class="btn theme-bg-light-soft btn-xs rounded-pill px-2 py-1 small fw-bold border theme-border" title="Edit Unit">
                                                    <i class="fas fa-edit theme-text-primary"></i>
                                                </a>
                                                <a href="hapus_unit.php?id=<?= $u['id'] ?>&model_id=<?= $model_id ?>&csrf_token=<?= generate_csrf_token() ?>" class="btn theme-bg-light-soft btn-xs rounded-pill px-2 py-1 small fw-bold border theme-border hover-danger" title="Delete Unit" onclick="return confirm('Note: Deleting a unit might affect active bookings. Continue?')">
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
