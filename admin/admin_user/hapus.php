<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../template/auth.php';
require_once __DIR__ . '/../../config/helper.php';

if (!is_super_admin()) {
    redirect_with_alert('../index.php', 'Anda tidak memiliki hak akses.', 'danger');
}

$id = $_GET['id'] ?? 0;

if ($id == $_SESSION['admin_id']) {
    redirect_with_alert('index.php', 'Anda tidak bisa menghapus akun sendiri.', 'danger');
}

try {
    $stmt = $pdo->prepare("DELETE FROM admins WHERE id = ?");
    $stmt->execute([$id]);
    require_once __DIR__ . '/../../config/backup_helper.php';
    trigger_auto_backup($pdo);
    redirect_with_alert('index.php', 'Akun admin berhasil dihapus.');
} catch (PDOException $e) {
    redirect_with_alert('index.php', 'Gagal menghapus: ' . $e->getMessage(), 'danger');
}
