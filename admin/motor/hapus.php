<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../template/auth.php';
require_once __DIR__ . '/../../config/helper.php';

$id = $_GET['id'] ?? 0;

// Verify CSRF Token
verify_csrf_token($_GET['csrf_token'] ?? '');

try {
    $pdo->beginTransaction();

    // 1. Get image paths to delete files (linked to model_id)
    $stmt_img = $pdo->prepare("SELECT image_path FROM motor_images WHERE model_id = ?");
    $stmt_img->execute([$id]);
    $images = $stmt_img->fetchAll(PDO::FETCH_COLUMN);

    // 2. Delete files from server
    foreach ($images as $img) {
        $path = __DIR__ . '/../../uploads/motors/' . $img;
        if (file_exists($path)) {
            unlink($path);
        }
    }

    // 3. Delete Physical Units (motors table) tied to this model
    // Note: If there are bookings for these units, this will throw a FK constraint error
    $stmt_units = $pdo->prepare("DELETE FROM motors WHERE model_id = ?");
    $stmt_units->execute([$id]);

    // 4. Delete model record
    $stmt = $pdo->prepare("DELETE FROM motor_models WHERE id = ?");
    $stmt->execute([$id]);

    $pdo->commit();
    
    require_once __DIR__ . '/../../config/backup_helper.php';
    trigger_auto_backup($pdo);
    
    redirect_with_alert('index.php', 'Model armada dan semua unit terkait berhasil dihapus.');
} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    $msg = $e->getMessage();
    if (strpos($msg, 'foreign key constraint fails') !== false) {
        $msg = "Tidak dapat menghapus model ini karena sudah ada data pemesanan (booking) terkait unit tersebut.";
    }
    
    redirect_with_alert('index.php', 'Gagal menghapus model: ' . $msg, 'danger');
}
