<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/template/auth.php';
require_once __DIR__ . '/../config/helper.php';
require_once __DIR__ . '/../config/backup_helper.php';

// --- AUTOMATIC BACKUP LOGIC (Set & Forget) ---
$backup_dir = __DIR__ . '/../backups/db/';
$last_backup_file = $backup_dir . 'last_backup_check.txt';
$today = date('Y-m-d');

// Check if we already backed up today
$should_backup = true;
if (file_exists($last_backup_file)) {
    $last_date = file_get_contents($last_backup_file);
    if ($last_date === $today) {
        $should_backup = false;
    }
}

if ($should_backup) {
    // Perform backup silently
    perform_database_backup($pdo);
    // Update last check file
    file_put_contents($last_backup_file, $today);
}
// ---------------------------------------------


// Fetch Statistics
try {
    $motor_tersedia = $pdo->query("SELECT COUNT(*) FROM motors m 
                                   WHERE m.status = 'Tersedia' 
                                   AND m.is_active = 1 
                                   AND NOT EXISTS (SELECT 1 FROM bookings b WHERE b.motor_id = m.id AND b.status = 'Pending')")->fetchColumn() ?: 0;
    $motor_disewa = $pdo->query("SELECT COUNT(*) FROM motors WHERE status = 'Disewa' AND is_active = 1")->fetchColumn() ?: 0;
    $total_motor = $pdo->query("SELECT COUNT(*) FROM motors WHERE is_active = 1")->fetchColumn() ?: 0;
    
    $total_booking = $pdo->query("SELECT COUNT(*) FROM bookings")->fetchColumn();
    $total_pendapatan = $pdo->query("SELECT SUM(total_price) FROM bookings WHERE status = 'Selesai'")->fetchColumn() ?: 0;
    
    $stmt_recent = $pdo->query("SELECT b.*, mm.brand, mm.model 
                               FROM bookings b 
                               JOIN motors m ON b.motor_id = m.id 
                               JOIN motor_models mm ON m.model_id = mm.id
                               ORDER BY b.created_at DESC LIMIT 5");
    $recent_bookings = $stmt_recent->fetchAll();

} catch (PDOException $e) {
    die("Error performance statistics: " . $e->getMessage());
}

include 'template/header.php';
include 'template/sidebar.php';
?>

<div id="content">
    <!-- Top Navbar -->
    <nav class="navbar top-navbar">
        <div class="container-fluid">
            <button type="button" id="sidebarCollapse" class="btn btn-light rounded-circle shadow-sm me-3 border-0 d-lg-none">
                <i class="fas fa-bars theme-text-heading"></i>
            </button>
            <div class="d-flex align-items-center ms-auto">
                <div class="dropdown">
                    <button class="btn theme-bg-card theme-text-heading shadow-sm border-0 rounded-pill px-3 py-1.5 dropdown-toggle d-flex align-items-center gap-2" type="button" id="userDropdown" data-bs-toggle="dropdown">
                        <img src="https://ui-avatars.com/api/?name=<?= urlencode($_SESSION['admin_full_name']) ?>&background=0f172a&color=fff" class="rounded-circle" width="26">
                        <span class="small fw-bold d-none d-md-block"><?= $_SESSION['admin_full_name'] ?></span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow border-0 rounded-4 mt-2 py-2" aria-labelledby="userDropdown">
                        <li class="px-3 py-2 border-bottom mb-2">
                            <span class="d-block small text-muted">Signed in as</span>
                            <span class="fw-bold"><?= $_SESSION['admin_full_name'] ?></span>
                        </li>
                        <li><a class="dropdown-item py-2" href="<?= BASE_URL ?>admin/admin_user/edit.php?id=<?= $_SESSION['admin_id'] ?>"><i class="fas fa-user-edit me-2 opacity-50"></i> Profil</a></li>
                        <li><a class="dropdown-item py-2" href="<?= BASE_URL ?>admin/pengaturan/index.php"><i class="fas fa-cog me-2 opacity-50"></i> Pengaturan</a></li>
                        <li><hr class="dropdown-divider opacity-10"></li>
                        <li><a class="dropdown-item py-2 text-danger" href="<?= BASE_URL ?>admin/logout.php"><i class="fas fa-sign-out-alt me-2"></i> Logout</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <div class="main-content">
        <div class="row align-items-end mb-4 mb-md-5">
            <div class="col">
                <span class="text-accent fw-bold text-uppercase small letter-spacing-1">Dashboard Management</span>
                <h2 class="fw-bold mb-0">Overview Analytics</h2>
            </div>
            <div class="col-auto d-none d-md-block">
                <div class="theme-bg-card theme-text-heading px-4 py-2 rounded-pill shadow-sm small fw-bold">
                    <i class="fas fa-calendar-alt text-accent me-2"></i> <?= date('d M, Y') ?>
                </div>
            </div>
        </div>

        <?php display_alert(); ?>

        <!-- Stat Cards -->
        <div class="row g-4 mb-5">
            <div class="col-md-3">
                <div class="card border-0 shadow-sm p-3 p-md-4 h-100">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted small fw-bold text-uppercase mb-2">Total Armada</p>
                            <h2 class="fw-bold mb-1 text-primary"><?= $total_motor ?></h2>
                            <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-2 py-1" style="font-size: 0.7rem;">Active Fleet</span>
                        </div>
                        <div class="bg-accent bg-opacity-10 text-accent rounded-4 p-3 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                            <i class="fas fa-motorcycle fa-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm p-3 p-md-4 h-100">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted small fw-bold text-uppercase mb-2">Tersedia</p>
                            <h2 class="fw-bold mb-1 text-success"><?= $motor_tersedia ?></h2>
                            <span class="text-muted small">Siap Disewa</span>
                        </div>
                        <div class="bg-success bg-opacity-10 text-success rounded-4 p-3 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                            <i class="fas fa-check-circle fa-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm p-3 p-md-4 h-100">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted small fw-bold text-uppercase mb-2">Dalam Sewa</p>
                            <h2 class="fw-bold mb-1 text-info"><?= $motor_disewa ?></h2>
                            <span class="text-muted small">Booking Aktif</span>
                        </div>
                        <div class="bg-info bg-opacity-10 text-info rounded-4 p-3 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                            <i class="fas fa-clock fa-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm p-4 h-100 theme-bg-card">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted small fw-bold text-uppercase mb-2">Total Revenue</p>
                            <h3 class="fw-bold mb-1 text-accent"><?= format_rupiah($total_pendapatan) ?></h3>
                            <span class="badge bg-accent bg-opacity-10 text-accent rounded-pill px-2 py-1" style="font-size: 0.7rem;">Selesai Dibayar</span>
                        </div>
                        <div class="bg-accent bg-opacity-10 text-accent rounded-4 p-3 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                            <i class="fas fa-wallet fa-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden theme-bg-card">
                    <div class="card-header theme-bg-card p-3 p-md-4 border-0 d-flex justify-content-between align-items-center">
                        <h5 class="fw-bold mb-0 theme-text-heading">Booking Terbaru</h5>
                        <a href="<?= BASE_URL ?>admin/booking/index.php" class="btn btn-primary btn-sm rounded-pill px-3 py-1.5 fw-bold">Lihat Semua</a>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>PELANGGAN</th>
                                        <th>ARMADA</th>
                                        <th>RENTAL</th>
                                        <th>TOTAL</th>
                                        <th>STATUS</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($recent_bookings)): ?>
                                        <tr>
                                            <td colspan="6" class="text-center py-5">
                                                <div class="opacity-20 mb-2"><i class="fas fa-folder-open fa-3x"></i></div>
                                                <p class="text-muted mb-0">Belum ada data transaksi.</p>
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($recent_bookings as $b): ?>
                                            <tr>
                                                <td class="fw-600">
                                                    <div class="d-flex align-items-center gap-3">
                                                        <div class="bg-alt rounded-3 p-2 text-primary small fw-bold" style="width: 40px; text-align:center;">
                                                            <?= strtoupper(substr($b['name'], 0, 1)) ?>
                                                        </div>
                                                        <div>
                                                            <div class="fw-bold"><?= htmlspecialchars($b['name']) ?></div>
                                                            <small class="text-muted"><?= $b['phone'] ?></small>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td><span class="badge bg-light text-dark fw-bold"><?= $b['brand'] . ' ' . $b['model'] ?></span></td>
                                                <td class="small fw-500"><?= $b['duration'] ?> Hari</td>
                                                <td class="fw-bold"><?= format_rupiah($b['total_price']) ?></td>
                                                <td>
                                                    <span class="badge rounded-pill bg-<?= get_status_badge($b['status']) ?> bg-opacity-10 text-<?= get_status_badge($b['status']) ?> px-3 py-2">
                                                        <?= $b['status'] ?>
                                                    </span>
                                                </td>
                                                <td class="text-end">
                                                    <a href="<?= BASE_URL ?>admin/booking/edit.php?id=<?= $b['id'] ?>" class="btn btn-light btn-sm rounded-circle"><i class="fas fa-chevron-right text-muted"></i></a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-header theme-bg-card p-4 border-0">
                        <h5 class="fw-bold mb-0 theme-text-heading">Fleet Distribution</h5>
                    </div>
                    <div class="card-body">
                        <div style="height: 300px;">
                            <canvas id="availabilityChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php 
$chartData = [
    'labels' => ['Tersedia (Unit)', 'Sedang Disewa'],
    'data' => [(int)$motor_tersedia, (int)$motor_disewa]
];
?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('availabilityChart').getContext('2d');
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: <?= json_encode($chartData['labels']) ?>,
            datasets: [{
                data: <?= json_encode($chartData['data']) ?>,
                backgroundColor: ['#14b8a6', '#0f172a', '#e2e8f0'],
                hoverOffset: 15,
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom', labels: { padding: 20, usePointStyle: true, font: { family: 'Outfit', size: 12 } } }
            },
            cutout: '75%'
        }
    });
});
</script>

<?php include 'template/footer.php'; ?>
