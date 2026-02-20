<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../template/auth.php';
require_once __DIR__ . '/../../config/helper.php';

$date = $_GET['date'] ?? date('Y-m-d');

// Fetch Daily Report Data
try {
    $stmt = $pdo->prepare("SELECT b.*, mm.brand, mm.model 
                          FROM bookings b 
                          JOIN motors m ON b.motor_id = m.id 
                          JOIN motor_models mm ON m.model_id = mm.id
                          WHERE DATE(b.created_at) = ? AND b.status = 'Selesai'
                          ORDER BY b.created_at ASC");
    $stmt->execute([$date]);
    $reports = $stmt->fetchAll();

    $total_revenue = array_sum(array_column($reports, 'total_price'));
    $total_bookings = count($reports);

} catch (PDOException $e) {
    die("Error fetching reports: " . $e->getMessage());
}

include __DIR__ . '/../template/header.php';
include __DIR__ . '/../template/sidebar.php';
?>

<div id="content">
    <nav class="navbar top-navbar no-print">
        <div class="container-fluid">
            <button type="button" id="sidebarCollapse" class="btn btn-light rounded-circle shadow-sm me-3 border-0 d-lg-none">
                <i class="fas fa-bars text-dark"></i>
            </button>
            <h5 class="mb-0 fw-bold ms-2">Laporan Harian</h5>
            <div class="ms-auto d-flex align-items-center gap-3">
                <button onclick="window.print()" class="btn btn-light rounded-pill px-4 fw-bold shadow-sm border">
                    <i class="fas fa-print me-2 text-primary"></i> Cetak
                </button>
            </div>
        </div>
    </nav>

    <div class="main-content">
        <div class="row align-items-center mb-4 no-print">
            <div class="col">
                <h4 class="fw-bold mb-0">Daily Performance</h4>
                <p class="text-muted small mb-0">Tanggal: <?= format_date_id($date) ?></p>
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 no-print">
            <form action="" method="GET" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label small fw-bold text-uppercase opacity-50">Pilih Tanggal</label>
                    <input type="date" name="date" class="form-control border-0 bg-light p-2 px-3 rounded-3" value="<?= $date ?>">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-dark w-100 py-2 rounded-3 fw-bold">Tampilkan</button>
                </div>
            </form>
        </div>

        <!-- Traditional Table-Based Print Header -->
        <table class="w-100 d-none d-print-table mb-2" style="border-bottom: 2px solid #000; font-family: 'Times New Roman', serif;">
            <tr>
                <td style="vertical-align: top; padding-bottom: 15px;">
                    <h1 style="margin: 0; padding: 0; color: #000; font-weight: bold; font-size: 18pt; letter-spacing: 2px;"><?= strtoupper(SITE_NAME) ?></h1>
                    <p style="margin: 2px 0; font-size: 9pt; color: #000;">
                        <?= CONTACT_ADDRESS ?> | WA: +<?= WA_NUMBER ?>
                    </p>
                </td>
                <td style="text-align: right; vertical-align: bottom; padding-bottom: 15px;">
                    <h5 style="margin: 0; color: #000; font-weight: bold; text-transform: uppercase;">Laporan Pendapatan Harian</h5>
                    <p style="margin: 0; font-size: 9pt;">Tanggal: <?= format_date_id($date) ?></p>
                </td>
            </tr>
        </table>

        <!-- Minimalist Print Summary Stats -->
        <table class="w-100 d-none d-print-table mb-4" style="border: 1px solid #000; border-collapse: collapse;">
            <tr>
                <td style="border: 1px solid #000; padding: 8px 15px; width: 50%;">
                    <span style="font-size: 8pt; text-transform: uppercase; color: #666; display: block;">Total Transaksi</span>
                    <strong style="font-size: 11pt;"><?= $total_bookings ?> Bookings</strong>
                </td>
                <td style="border: 1px solid #000; padding: 8px 15px; width: 50%;">
                    <span style="font-size: 8pt; text-transform: uppercase; color: #666; display: block;">Total Pendapatan</span>
                    <strong style="font-size: 11pt;"><?= format_rupiah($total_revenue) ?></strong>
                </td>
            </tr>
        </table>

        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th class="ps-4">TIME</th>
                                <th>CUSTOMER</th>
                                <th>VEHICLE</th>
                                <th>DURATION</th>
                                <th class="text-end pe-4">AMOUNT</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($reports)): ?>
                                <tr>
                                    <td colspan="5" class="text-center py-5 text-muted">Belum ada transaksi selesai hari ini.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($reports as $r): ?>
                                    <tr>
                                        <td class="ps-4 small text-muted"><?= date('H:i', strtotime($r['created_at'])) ?></td>
                                        <td class="fw-bold"><?= htmlspecialchars($r['name']) ?></td>
                                        <td><span class="badge bg-light text-dark fw-bold"><?= $r['brand'] . ' ' . $r['model'] ?></span></td>
                                        <td><?= $r['duration'] ?> Hari</td>
                                        <td class="text-end pe-4 fw-800 text-dark"><?= format_rupiah($r['total_price']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                        <tfoot class="bg-light bg-opacity-50">
                            <tr>
                                <td colspan="4" class="text-end fw-bold py-3 text-uppercase small opacity-50">Grand Total</td>
                                <td class="text-end pe-4 fw-900 text-dark h4 mb-0 py-3"><?= format_rupiah($total_revenue) ?></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
        <!-- Traditional Table-Based Signing Area -->
        <table class="w-100 d-none d-print-table mt-5">
            <tr>
                <td style="width: 60%;">
                    <p class="small text-muted" style="margin-top: 50px;">Dicetak pada: <?= date('d/m/Y H:i') ?></p>
                </td>
                <td style="width: 40%; text-align: center;">
                    <p style="margin-bottom: 70px;">Hormat Kami,</p>
                    <div style="border-top: 1px solid #000; width: 200px; margin: 0 auto; padding-top: 5px;">
                        <p style="font-weight: bold; margin: 0;"><?= $_SESSION['admin_full_name'] ?></p>
                        <p style="font-size: 9pt; margin: 0;">Administrator</p>
                    </div>
                </td>
            </tr>
        </table>
    </div>
</div>

<style>
@media print {
    #sidebar, .navbar, .no-print, .btn, .input-group, #sidebarCollapse, .stat-card { display: none !important; }
    #content { padding-left: 0 !important; width: 100% !important; background: white !important; margin: 0 !important; }
    .main-content { padding: 0 !important; }
    .card { box-shadow: none !important; border: 1px solid #000 !important; border-radius: 0 !important; margin-bottom: 0 !important; }
    body { background: white !important; font-size: 9pt; color: #000 !important; margin: 0.5cm !important; }
    .table thead th { background: #f0f0f0 !important; color: #000 !important; border-bottom: 1px solid #000 !important; font-size: 8pt; }
    .table td { padding: 5px !important; border-bottom: 1px solid #eee !important; }
    .badge { border: none !important; background: transparent !important; color: #000 !important; padding: 0 !important; font-weight: normal !important; }
}
</style>

<?php include __DIR__ . '/../template/footer.php'; ?>
