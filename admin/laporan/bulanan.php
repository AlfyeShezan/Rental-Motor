<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../template/auth.php';
require_once __DIR__ . '/../../config/helper.php';

$month = $_GET['month'] ?? date('m');
$year = $_GET['year'] ?? date('Y');

// Fetch Monthly Report Data
try {
    $stmt = $pdo->prepare("SELECT b.*, mm.brand, mm.model 
                          FROM bookings b 
                          JOIN motors m ON b.motor_id = m.id 
                          JOIN motor_models mm ON m.model_id = mm.id
                          WHERE MONTH(b.created_at) = ? AND YEAR(b.created_at) = ? AND b.status = 'Selesai'
                          ORDER BY b.created_at ASC");
    $stmt->execute([$month, $year]);
    $reports = $stmt->fetchAll();

    // Summary Statistics
    $total_revenue = array_sum(array_column($reports, 'total_price'));
    $total_bookings = count($reports);
    $total_duration = array_sum(array_column($reports, 'duration'));

} catch (PDOException $e) {
    die("Error fetching reports: " . $e->getMessage());
}

include __DIR__ . '/../template/header.php';
include __DIR__ . '/../template/sidebar.php';
?>

<div id="content">
    <!-- Top Navbar -->
    <nav class="navbar top-navbar no-print">
        <div class="container-fluid">
            <button type="button" id="sidebarCollapse" class="btn btn-light rounded-circle shadow-sm me-3 border-0 d-lg-none">
                <i class="fas fa-bars text-dark"></i>
            </button>
            <h5 class="mb-0 fw-bold ms-2">Laporan Bulanan</h5>
            <div class="ms-auto d-flex align-items-center gap-3">
                <button onclick="window.print()" class="btn btn-light rounded-pill px-4 fw-bold shadow-sm border">
                    <i class="fas fa-print me-2 text-primary"></i> Cetak
                </button>
                <a href="export.php?type=bulanan&month=<?= $month ?>&year=<?= $year ?>" class="btn btn-dark rounded-pill px-4 fw-bold shadow-sm">
                    <i class="fas fa-file-excel me-2 text-success"></i> Export
                </a>
            </div>
        </div>
    </nav>

    <div class="main-content">
        <div class="row align-items-center mb-4 no-print">
            <div class="col">
                <h4 class="fw-bold mb-0">Analisis Pendapatan</h4>
                <p class="text-muted small mb-0">Periode: <?= date('F', mktime(0,0,0, $month, 1)) ?> <?= $year ?></p>
            </div>
        </div>

        <?php display_alert(); ?>

        <!-- Elite Filter Bar -->
        <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 no-print">
            <form action="" method="GET" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label small fw-bold text-uppercase opacity-50">Filter Bulan</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-0"><i class="fas fa-calendar-alt text-muted"></i></span>
                        <select name="month" class="form-select border-0 bg-light">
                            <?php for($m=1; $m<=12; $m++): ?>
                                <option value="<?= str_pad($m, 2, '0', STR_PAD_LEFT) ?>" <?= $month == $m ? 'selected' : '' ?>>
                                    <?= date('F', mktime(0, 0, 0, $m, 1)) ?>
                                </option>
                            <?php endfor; ?>
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-uppercase opacity-50">Tahun</label>
                    <select name="year" class="form-select border-0 bg-light">
                        <?php for($y=date('Y'); $y>=2023; $y--): ?>
                            <option value="<?= $y ?>" <?= $year == $y ? 'selected' : '' ?>><?= $y ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-dark w-100 py-2 rounded-3 fw-bold">Tampilkan</button>
                </div>
            </form>
        </div>

        <!-- Print Header -->
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
                    <h5 style="margin: 0; color: #000; font-weight: bold; text-transform: uppercase;">Laporan Pendapatan Bulanan</h5>
                    <p style="margin: 0; font-size: 9pt;">Periode: <?= date('F', mktime(0,0,0, $month, 1)) ?> <?= $year ?></p>
                </td>
            </tr>
        </table>

        <!-- Minimalist Print Summary Stats -->
        <table class="w-100 d-none d-print-table mb-4" style="border: 1px solid #000; border-collapse: collapse;">
            <tr>
                <td style="border: 1px solid #000; padding: 8px 15px; width: 33.3%;">
                    <span style="font-size: 8pt; text-transform: uppercase; color: #666; display: block;">Total Transaksi</span>
                    <strong style="font-size: 11pt;"><?= $total_bookings ?> Bookings</strong>
                </td>
                <td style="border: 1px solid #000; padding: 8px 15px; width: 33.3%;">
                    <span style="font-size: 8pt; text-transform: uppercase; color: #666; display: block;">Total Pendapatan</span>
                    <strong style="font-size: 11pt;"><?= format_rupiah($total_revenue) ?></strong>
                </td>
                <td style="border: 1px solid #000; padding: 8px 15px; width: 33.3%;">
                    <span style="font-size: 8pt; text-transform: uppercase; color: #666; display: block;">Durasi Sewa</span>
                    <strong style="font-size: 11pt;"><?= $total_duration ?> Hari</strong>
                </td>
            </tr>
        </table>

        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th class="ps-4">DATE</th>
                                <th>CUSTOMER</th>
                                <th>VEHICLE</th>
                                <th class="text-center">DURATION</th>
                                <th class="text-end pe-4">AMOUNT</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($reports)): ?>
                                <tr>
                                    <td colspan="5" class="text-center py-5">
                                        <div class="opacity-20 mb-3"><i class="fas fa-folder-open fa-4x"></i></div>
                                        <p class="text-muted">Tidak ada data transaksi selesai pada periode ini.</p>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($reports as $r): ?>
                                    <tr>
                                        <td class="ps-4 fw-bold small"><?= date('d M, Y', strtotime($r['created_at'])) ?></td>
                                        <td>
                                            <div class="fw-bold text-dark"><?= htmlspecialchars($r['name']) ?></div>
                                            <div class="small text-muted"><?= $r['phone'] ?></div>
                                        </td>
                                        <td>
                                            <div class="fw-bold text-primary"><?= $r['brand'] . ' ' . $r['model'] ?></div>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-alt text-primary px-3 py-2 rounded-pill small fw-bold">
                                                <?= $r['duration'] ?> Hak
                                            </span>
                                        </td>
                                        <td class="text-end pe-4 fw-800 text-dark"><?= format_rupiah($r['total_price']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                        <tfoot class="bg-light bg-opacity-50">
                            <tr>
                                <td colspan="4" class="text-end fw-bold py-3 text-uppercase small opacity-50">Gross Revenue</td>
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
