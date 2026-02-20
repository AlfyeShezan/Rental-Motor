<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../template/auth.php';
require_once __DIR__ . '/../../config/helper.php';

include __DIR__ . '/../template/header.php';
include __DIR__ . '/../template/sidebar.php';
?>

<div id="content">
    <!-- Top Navbar -->
    <nav class="navbar top-navbar no-print">
        <div class="container-fluid">
            <button type="button" id="sidebarCollapse" class="btn theme-bg-card theme-text-heading rounded-circle shadow-sm me-3 border-0 d-lg-none">
                <i class="fas fa-bars theme-text-heading"></i>
            </button>
            <h5 class="mb-0 fw-bold ms-2 theme-text-heading">Pusat Laporan Keuangan</h5>
            <div class="ms-auto">
                <button onclick="window.print()" class="btn btn-light rounded-pill px-4 fw-bold shadow-sm border no-print">
                    <i class="fas fa-print me-2 text-primary"></i> Cetak Ringkasan
                </button>
            </div>
        </div>
    </nav>

    <div class="main-content">
        <div class="row align-items-center mb-5">
            <div class="col">
                <h4 class="fw-bold mb-0">Financial Reporting Hub</h4>
                <p class="text-muted small mb-0">analisis pendapatan dan operasional berdasarkan periode waktu.</p>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-md-4">
                <div class="card border-0 shadow-sm rounded-4 p-4 h-100 text-center transition-up theme-bg-card">
                    <div class="bg-success bg-opacity-10 text-success mx-auto mb-4 rounded-circle d-flex align-items-center justify-content-center" style="width: 70px; height: 70px; font-size: 1.8rem;">
                        <i class="fas fa-calendar-day"></i>
                    </div>
                    <h5 class="fw-bold theme-text-heading">Laporan Harian</h5>
                    <p class="theme-text-muted small mb-4">Pantau transaksi dan pendapatan harian secara mendalam.</p>
                    <a href="harian.php" class="btn theme-bg-light-soft theme-text-heading rounded-pill px-4 w-100 fw-bold py-2 border theme-border">Buka Laporan</a>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card border-0 shadow-sm rounded-4 p-4 h-100 text-center transition-up theme-bg-card">
                    <div class="bg-primary bg-opacity-10 text-primary mx-auto mb-4 rounded-circle d-flex align-items-center justify-content-center" style="width: 70px; height: 70px; font-size: 1.8rem;">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <h5 class="fw-bold theme-text-heading">Laporan Bulanan</h5>
                    <p class="theme-text-muted small mb-4">Analisis performa bisnis bulanan untuk perbandingan strategi.</p>
                    <a href="bulanan.php" class="btn theme-bg-light-soft theme-text-heading rounded-pill px-4 w-100 fw-bold py-2 border theme-border">Buka Laporan</a>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card border-0 shadow-sm rounded-4 p-4 h-100 text-center transition-up theme-bg-card">
                    <div class="bg-info bg-opacity-10 text-info mx-auto mb-4 rounded-circle d-flex align-items-center justify-content-center" style="width: 70px; height: 70px; font-size: 1.8rem;">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <h5 class="fw-bold theme-text-heading">Laporan Tahunan</h5>
                    <p class="theme-text-muted small mb-4">Rekapitulasi pendapatan tahunan untuk perencanaan jangka panjang.</p>
                    <a href="tahunan.php" class="btn theme-bg-light-soft theme-text-heading rounded-pill px-4 w-100 fw-bold py-2 border theme-border">Buka Laporan</a>
                </div>
            </div>
        </div>

        <div class="mt-5 p-5 theme-bg-card rounded-4 border-0 shadow-sm text-center">
            <div class="opacity-10 mb-3"><i class="fas fa-chart-line fa-5x"></i></div>
            <h6 class="fw-bold theme-text-heading">Insight Bisnis</h6>
            <p class="theme-text-muted small mx-auto" style="max-width: 500px;">Gunakan data laporan untuk memahami trend penyewaan dan mengoptimalkan ketersediaan armada populer di waktu yang tepat.</p>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../template/footer.php'; ?>
