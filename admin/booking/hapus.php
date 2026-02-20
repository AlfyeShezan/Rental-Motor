<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../template/auth.php';
require_once __DIR__ . '/../../config/helper.php';

$id = $_GET['id'] ?? 0;

try {
    $pdo->beginTransaction();

    // Get motor_id and status before deleting
    $stmt_get = $pdo->prepare("SELECT motor_id, status FROM bookings WHERE id = ?");
    $stmt_get->execute([$id]);
    $booking = $stmt_get->fetch();

    if ($booking) {
        // If booking was 'Disewa', restore stock
        if ($booking['status'] === 'Disewa') {
            $pdo->prepare("UPDATE motors SET stok = stok + 1 WHERE id = ?")->execute([$booking['motor_id']]);
            // Restore status to Tersedia if it was Disewa
            $pdo->prepare("UPDATE motors SET status = 'Tersedia' WHERE id = ? AND status = 'Disewa'")->execute([$booking['motor_id']]);
        }

        $stmt = $pdo->prepare("DELETE FROM bookings WHERE id = ?");
        $stmt->execute([$id]);
    }

    $pdo->commit();
    require_once __DIR__ . '/../../config/backup_helper.php';
    trigger_auto_backup($pdo);
    
    // Preserve filter parameters
    $redirect_url = 'index.php';
    if (isset($_SERVER['HTTP_REFERER'])) {
        $referer_query = parse_url($_SERVER['HTTP_REFERER'], PHP_URL_QUERY);
        if ($referer_query) {
            parse_str($referer_query, $params);
            $filter_params = [];
            if (!empty($params['status'])) $filter_params['status'] = $params['status'];
            if (!empty($params['date'])) $filter_params['date'] = $params['date'];
            if (!empty($filter_params)) {
                $redirect_url .= '?' . http_build_query($filter_params);
            }
        }
    }
    
    redirect_with_alert($redirect_url, 'Booking berhasil dihapus.');
} catch (PDOException $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    redirect_with_alert('index.php', 'Gagal menghapus booking: ' . $e->getMessage(), 'danger');
}
