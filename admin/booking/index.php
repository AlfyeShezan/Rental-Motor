<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../template/auth.php';
require_once __DIR__ . '/../../config/helper.php';

// Filter by Status and Date
$status_filter = $_GET['status'] ?? '';
$date_filter = $_GET['date'] ?? '';

try {
    $query = "SELECT b.*, mm.brand, mm.model, m.plate_number 
              FROM bookings b 
              JOIN motors m ON b.motor_id = m.id
              JOIN motor_models mm ON m.model_id = mm.id";
    $params = [];

    $conditions = [];
    if ($status_filter) {
        if ($status_filter == 'Telat') {
            $conditions[] = "b.status = 'Disewa' AND b.return_date < CURDATE()";
        } else {
            $conditions[] = "b.status = ?";
            $params[] = $status_filter;
        }
    }
    if ($date_filter) {
        $conditions[] = "(b.pickup_date = ? OR b.return_date = ?)";
        $params[] = $date_filter;
        $params[] = $date_filter;
    }

    if (!empty($conditions)) {
        $query .= " WHERE " . implode(" AND ", $conditions);
    }

    $query .= " ORDER BY b.created_at DESC";
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $bookings = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Error fetching bookings: " . $e->getMessage());
}

include __DIR__ . '/../template/header.php';
include __DIR__ . '/../template/sidebar.php';
?>

<div id="content">
    <!-- Top Navbar -->
    <nav class="navbar top-navbar">
        <div class="container-fluid">
            <button type="button" id="sidebarCollapse" class="btn theme-bg-card theme-text-heading rounded-circle shadow-sm me-3 border-0 d-lg-none">
                <i class="fas fa-bars theme-text-heading"></i>
            </button>
            <h5 class="mb-0 fw-bold ms-2 theme-text-heading">Manajemen Booking</h5>
            <div class="ms-auto d-flex align-items-center gap-3">
                <a href="tambah.php" class="btn btn-accent rounded-pill px-3 py-1.5 fw-bold shadow-sm small">
                    <i class="fas fa-plus-circle me-1"></i> Input Manual
                </a>
            </div>

        </div>
    </nav>

    <div class="main-content">
        <div class="row align-items-center mb-4">
            <div class="col">
                <h4 class="fw-bold mb-0">Riwayat Transaksi</h4>
                <p class="text-muted small mb-0">Kelola pesanan pelanggan dan update status penyewaan unit.</p>
            </div>
        </div>

        <?php display_alert(); ?>

        <!-- Premium Filter Bar -->
        <div class="card border-0 shadow-sm rounded-4 p-3 p-md-4 mb-4 theme-bg-card">
            <form action="" method="GET" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label small fw-bold text-uppercase theme-text-muted mb-2">Filter Status</label>
                    <select name="status" class="form-select theme-border rounded-3 theme-bg-light-soft theme-text-heading py-2">
                        <option value="">Semua Status</option>
                        <option value="Pending" <?= $status_filter == 'Pending' ? 'selected' : '' ?>>Pending</option>
                        <option value="Disewa" <?= $status_filter == 'Disewa' ? 'selected' : '' ?>>Disewa</option>
                        <option value="Selesai" <?= $status_filter == 'Selesai' ? 'selected' : '' ?>>Selesai</option>
                        <option value="Batal" <?= $status_filter == 'Batal' ? 'selected' : '' ?>>Batal</option>
                        <option value="Telat" <?= $status_filter == 'Telat' ? 'selected' : '' ?>>Telat (Overdue)</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-bold text-uppercase theme-text-muted mb-2">Filter Tanggal</label>
                    <input type="date" name="date" class="form-control theme-border rounded-3 theme-bg-light-soft theme-text-heading py-2" value="<?= $date_filter ?>">
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary w-100 py-2 rounded-3 fw-bold small">
                        <i class="fas fa-filter me-1"></i> Filter
                    </button>
                </div>
                <div class="col-md-1">
                    <a href="index.php" class="btn theme-bg-light-soft theme-text-heading w-100 py-2 rounded-3 theme-border" title="Reset">
                        <i class="fas fa-sync-alt"></i>
                    </a>
                </div>
            </form>
        </div>
>

        <div class="card border-0 shadow-sm rounded-4 overflow-hidden theme-bg-card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="theme-bg-light-soft">
                            <tr>
                                <th class="ps-4 py-3 border-0">CUSTOMER</th>
                                <th class="py-3 border-0">VEHICLE</th>
                                <th class="py-3 border-0">SCHEDULE</th>
                                <th class="py-3 border-0">DURATION</th>
                                <th class="py-3 border-0">PAYMENT</th>
                                <th class="py-3 border-0">STATUS</th>
                                <th class="text-end pe-4 py-3 border-0">ACTION</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php if (empty($bookings)): ?>
                                <tr>
                                    <td colspan="7" class="text-center py-5">
                                        <div class="opacity-20 mb-3"><i class="fas fa-receipt fa-4x"></i></div>
                                        <h5 class="fw-bold">Belum Ada Booking</h5>
                                        <p class="text-muted">Data transaksi akan muncul di sini.</p>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($bookings as $b): ?>
                                    <tr>
                                        <td class="ps-4">
                                            <div class="d-flex align-items-center gap-3">
                                                <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width: 45px; height: 45px;">
                                                    <?= strtoupper(substr($b['name'], 0, 1)) ?>
                                                </div>
                                                <div>
                                                    <div class="fw-bold theme-text-heading"><?= htmlspecialchars($b['name']) ?></div>
                                                    <a href="https://wa.me/<?= $b['phone'] ?>" target="_blank" class="small theme-text-muted text-decoration-none">
                                                        <i class="fab fa-whatsapp text-success me-1"></i> <?= $b['phone'] ?>
                                                    </a>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="fw-bold theme-text-primary"><?= htmlspecialchars($b['brand'] . ' ' . $b['model']) ?></div>
                                            <div class="small fw-bold theme-text-muted text-uppercase"><?= htmlspecialchars($b['plate_number']) ?></div>
                                        </td>
                                        <td>
                                            <div class="d-flex flex-column">
                                                <span class="small theme-text-heading"><i class="fas fa-arrow-right text-success me-1" style="font-size: 0.7rem;"></i> <?= format_date_id($b['pickup_date']) ?></span>
                                                <span class="small theme-text-muted"><i class="fas fa-arrow-left text-danger me-1" style="font-size: 0.7rem;"></i> <?= format_date_id($b['return_date']) ?></span>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge theme-bg-alt theme-text-heading rounded-pill px-3 py-2 fw-normal" style="font-size: 0.75rem;">
                                                <i class="fas fa-clock me-1 opacity-50"></i> <?= $b['duration'] ?> Hari
                                            </span>
                                        </td>
                                        <td>
                                            <div class="theme-text-heading"><?= format_rupiah($b['total_price']) ?></div>
                                            <small class="theme-text-muted" style="font-size: 0.65rem;">NET PRICE</small>
                                        <td>
                                            <?php 
                                            // Check Overdue
                                            $is_overdue = ($b['status'] == 'Disewa' && strtotime($b['return_date']) < strtotime(date('Y-m-d')));
                                            ?>
                                            <?php if($is_overdue): ?>
                                                <span class="badge bg-danger mb-1 blink-soft">TERLAMBAT</span><br>
                                            <?php endif; ?>

                                            <span class="badge rounded-pill bg-<?= get_status_badge($b['status']) ?> bg-opacity-10 text-<?= get_status_badge($b['status']) ?> px-3 py-2 fw-normal">
                                                <?= $b['status'] ?>
                                            </span>
                                            <?php if($b['status'] == 'Pending'): ?>
                                                <div class="mt-2 text-center">
                                                    <a href="check_status.php?id=<?= $b['id'] ?>" class="btn btn-outline-primary btn-sm rounded-pill px-3 py-1" style="font-size: 0.7rem;">
                                                        <i class="fas fa-sync-alt me-1"></i> Cek Pembayaran
                                                    </a>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-end pe-4">
                                            <div class="d-flex justify-content-end gap-2">
                                                <a href="edit.php?id=<?= $b['id'] ?>" class="btn theme-bg-light-soft btn-xs rounded-pill px-2 py-1 small fw-bold theme-border" title="Update Status">
                                                    <i class="fas fa-edit theme-text-primary"></i>
                                                </a>
                                                <a href="hapus.php?id=<?= $b['id'] ?>" class="btn theme-bg-light-soft btn-xs rounded-pill px-2 py-1 small fw-bold theme-border hover-danger" title="Hapus" onclick="return confirm('Apakah Anda yakin ingin menghapus booking ini?')">
                                                    <i class="fas fa-trash text-danger"></i>
                                                </a>
                                            </div>

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
</div>

<?php include __DIR__ . '/../template/footer.php'; ?>
