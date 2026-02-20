<?php
require_once __DIR__ . '/../config/config.php';

header('Content-Type: application/json');

if (!isset($_GET['code'])) {
    echo json_encode(['status' => 'error', 'message' => 'Kode tidak ditemukan']);
    exit;
}

$code = strtoupper(trim($_GET['code']));
$today = date('Y-m-d');

try {
    $stmt = $pdo->prepare("SELECT * FROM promos WHERE code = ? AND is_active = 1 AND valid_from <= ? AND valid_to >= ?");
    $stmt->execute([$code, $today, $today]);
    $promo = $stmt->fetch();

    if ($promo) {
        // Check usage limit
        if ($promo['usage_limit'] !== null && $promo['used_count'] >= $promo['usage_limit']) {
            echo json_encode(['status' => 'error', 'message' => 'Kuota promo sudah habis']);
            exit;
        }

        echo json_encode([
            'status' => 'success',
            'data' => [
                'id' => $promo['id'],
                'code' => $promo['code'],
                'type' => $promo['discount_type'],
                'value' => (float)$promo['discount_value']
            ]
        ]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Kode promo tidak valid atau sudah kadaluarsa']);
    }
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Terjadi kesalahan sistem']);
}
