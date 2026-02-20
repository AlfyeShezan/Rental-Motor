<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../template/auth.php';
require_once __DIR__ . '/../../config/helper.php';

$id = $_GET['id'] ?? 0;

// Verify CSRF Token
verify_csrf_token($_GET['csrf_token'] ?? '');

try {
    // Get photo path to delete file
    $stmt_get = $pdo->prepare("SELECT photo FROM testimonials WHERE id = ?");
    $stmt_get->execute([$id]);
    $photo = $stmt_get->fetchColumn();

    if ($photo) {
        $path = __DIR__ . '/../../uploads/testimoni/' . $photo;
        if (file_exists($path)) {
            unlink($path);
        }
    }

    $stmt = $pdo->prepare("DELETE FROM testimonials WHERE id = ?");
    $stmt->execute([$id]);
    require_once __DIR__ . '/../../config/backup_helper.php';
    trigger_auto_backup($pdo);
    redirect_with_alert('index.php', 'Testimoni berhasil dihapus.');
} catch (PDOException $e) {
    redirect_with_alert('index.php', 'Gagal menghapus: ' . $e->getMessage(), 'danger');
}
