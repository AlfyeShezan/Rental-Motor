<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../template/auth.php';
require_once __DIR__ . '/../../config/helper.php';

$id = $_GET['id'] ?? 0;
$model_id = $_GET['model_id'] ?? 0;

// Verify CSRF Token
verify_csrf_token($_GET['csrf_token'] ?? '');

try {
    $pdo->beginTransaction();

    // Check if unit has active bookings
    $stmt_check = $pdo->prepare("SELECT id FROM bookings WHERE motor_id = ? AND status IN ('Pending', 'Disewa')");
    $stmt_check->execute([$id]);
    if ($stmt_check->fetch()) {
        throw new Exception("Unit tidak dapat dihapus karena masih terkait dengan data booking yang aktif.");
    }

    // Delete record 
    $stmt = $pdo->prepare("DELETE FROM motors WHERE id = ?");
    $stmt->execute([$id]);

    $pdo->commit();
    require_once __DIR__ . '/../../config/backup_helper.php';
    trigger_auto_backup($pdo);
    redirect_with_alert("view_units.php?model_id=$model_id", 'Unit berhasil dihapus.');
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    redirect_with_alert("view_units.php?model_id=$model_id", 'Gagal menghapus unit: ' . $e->getMessage(), 'danger');
}
