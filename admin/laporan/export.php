<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../template/auth.php';
require_once __DIR__ . '/../../config/helper.php';

$type = $_GET['type'] ?? '';
$filename = "report_" . $type . "_" . date('Ymd') . ".csv";

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=' . $filename);

$output = fopen('php://output', 'w');

if ($type === 'harian') {
    $date = $_GET['date'] ?? date('Y-m-d');
    fputcsv($output, ['Laporan Harian', $date]);
    fputcsv($output, ['No', 'Waktu', 'Pelanggan', 'Motor', 'Total Bayar']);
    
    $stmt = $pdo->prepare("SELECT b.*, mm.brand, mm.model FROM bookings b JOIN motors m ON b.motor_id = m.id JOIN motor_models mm ON m.model_id = mm.id WHERE DATE(b.created_at) = ? AND b.status = 'Selesai'");
    $stmt->execute([$date]);
    $items = $stmt->fetchAll();
    
    foreach ($items as $index => $row) {
        fputcsv($output, [$index + 1, date('H:i', strtotime($row['created_at'])), $row['name'], $row['brand'].' '.$row['model'], $row['total_price']]);
    }

} elseif ($type === 'bulanan') {
    $month = $_GET['month'] ?? date('m');
    $year = $_GET['year'] ?? date('Y');
    fputcsv($output, ['Laporan Bulanan', $month . '/' . $year]);
    fputcsv($output, ['No', 'Tanggal', 'Pelanggan', 'Motor', 'Durasi', 'Total Bayar']);
    
    $stmt = $pdo->prepare("SELECT b.*, mm.brand, mm.model FROM bookings b JOIN motors m ON b.motor_id = m.id JOIN motor_models mm ON m.model_id = mm.id WHERE MONTH(b.created_at) = ? AND YEAR(b.created_at) = ? AND b.status = 'Selesai'");
    $stmt->execute([$month, $year]);
    $items = $stmt->fetchAll();
    
    foreach ($items as $index => $row) {
        fputcsv($output, [$index + 1, date('d/m/Y', strtotime($row['created_at'])), $row['name'], $row['brand'].' '.$row['model'], $row['duration'], $row['total_price']]);
    }

} elseif ($type === 'tahunan') {
    $year = $_GET['year'] ?? date('Y');
    fputcsv($output, ['Laporan Tahunan', $year]);
    fputcsv($output, ['Bulan', 'Total Pendapatan']);
    
    $stmt = $pdo->prepare("SELECT MONTH(created_at) as bln, SUM(total_price) as total FROM bookings WHERE YEAR(created_at) = ? AND status = 'Selesai' GROUP BY MONTH(created_at)");
    $stmt->execute([$year]);
    $items = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    
    for ($i = 1; $i <= 12; $i++) {
        fputcsv($output, [date('F', mktime(0,0,0,$i,1)), $items[$i] ?? 0]);
    }
}

fclose($output);
exit();
?>
