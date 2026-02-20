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
} catch (PDOException $e) {
    die("Error fetching model: " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token($_POST['csrf_token'] ?? '');

    $plate_number = filter_var($_POST['plate_number'], FILTER_SANITIZE_STRING);
    $color = filter_var($_POST['color'], FILTER_SANITIZE_STRING);
    $status = $_POST['status'];
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    try {
        // Check for duplicate plate
        $stmt_check = $pdo->prepare("SELECT COUNT(*) FROM motors WHERE plate_number = ?");
        $stmt_check->execute([$plate_number]);
        if ($stmt_check->fetchColumn() > 0) {
            throw new Exception("Plat nomor '$plate_number' sudah terdaftar di sistem.");
        }

        $stmt = $pdo->prepare("INSERT INTO motors (model_id, plate_number, color, status, is_active) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$model_id, $plate_number, $color, $status, $is_active]);

        redirect_with_alert("view_units.php?model_id=$model_id", "Unit $plate_number berhasil ditambahkan.");
    } catch (Exception $e) {
        $_SESSION['alert_message'] = $e->getMessage();
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
            <h5 class="mb-0 fw-bold ms-2">Add New Unit for <?= htmlspecialchars($model['brand'] . ' ' . $model['model']) ?></h5>
            <div class="ms-auto">
                <a href="view_units.php?model_id=<?= $model_id ?>" class="btn btn-light rounded-pill px-3 py-1.5 border small"><i class="fas fa-chevron-left me-1"></i> Cancel</a>
            </div>
        </div>
    </nav>

    <div class="main-content">
        <?php display_alert(); ?>

        <div class="row justify-content-center">
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm rounded-4 p-4">
                    <form action="" method="POST">
                        <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">Plate Number</label>
                            <input type="text" name="plate_number" class="form-control bg-light-soft border" required placeholder="Ex: AB 1234 XY" autofocus>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">Bike Color</label>
                            <input type="text" name="color" class="form-control bg-light-soft border" required placeholder="Ex: Matte Black">
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">Initial Status</label>
                            <select name="status" class="form-select bg-light-soft border">
                                <option value="Tersedia">Available (Tersedia)</option>
                                <option value="Disewa">Rented (Disewa)</option>
                                <option value="Maintenance">Maintenance</option>
                            </select>
                        </div>
                        <div class="form-check form-switch mb-4">
                            <input class="form-check-input" type="checkbox" name="is_active" id="activeSwitch" checked>
                            <label class="form-check-label fw-bold small text-dark" for="activeSwitch">Live (Visible for users)</label>
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-accent fw-bold py-2 rounded-pill shadow-sm">
                                <i class="fas fa-save me-1"></i> Save Specific Unit
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../template/footer.php'; ?>
