<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../template/auth.php';
require_once __DIR__ . '/../../config/helper.php';

$id = $_GET['id'] ?? 0;

try {
    $stmt = $pdo->prepare("DELETE FROM promos WHERE id = ?");
    $stmt->execute([$id]);
    redirect_with_alert('index.php', 'Promo berhasil dihapus.');
} catch (PDOException $e) {
    redirect_with_alert('index.php', 'Gagal menghapus promo: ' . $e->getMessage(), 'danger');
}
