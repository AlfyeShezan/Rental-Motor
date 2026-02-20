<?php
require_once 'includes/header.php';

$bookings = [];
$search_phone = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['phone'])) {
    $search_phone = filter_var($_POST['phone'], FILTER_SANITIZE_STRING);
    
    try {
        $stmt = $pdo->prepare("SELECT b.*, m.brand, m.model, m.primary_image 
                              FROM bookings b 
                              JOIN motors m ON b.motor_id = m.id 
                              WHERE b.phone = ? OR b.phone = ?
                              ORDER BY b.created_at DESC");
        // Check both original and variations if needed (62 vs 08)
        $clean_phone = preg_replace('/[^0-9]/', '', $search_phone);
        $alt_phone = (substr($clean_phone, 0, 2) === '62') ? '0' . substr($clean_phone, 2) : $clean_phone;
        
        $stmt->execute([$clean_phone, $alt_phone]);
        $bookings = $stmt->fetchAll();
    } catch (PDOException $e) {
        $error = "Terjadi kesalahan saat mencari data.";
    }
}
?>

<section class="section-padding bg-light-soft min-vh-100">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-6 text-center mb-5" data-aos="fade-up">
                <span class="section-tag">Self Service</span>
                <h2 class="section-title">Cek Pesanan Anda</h2>
                <p class="theme-text-muted">Masukkan nomor WhatsApp yang Anda gunakan saat melakukan pendaftaran untuk melihat riwayat penyewaan.</p>
            </div>
        </div>

        <div class="row justify-content-center">
            <div class="col-lg-5" data-aos="fade-up" data-aos-delay="100">
                <div class="premium-card p-4 shadow-lg border-0 mb-5">
                    <form action="" method="POST">
                        <div class="mb-4">
                            <label class="form-label fw-bold small text-muted">Nomor WhatsApp</label>
                            <input type="text" name="phone" class="form-control py-3 rounded-pill border shadow-sm px-4" 
                                   placeholder="Contoh: 08123456789" required value="<?= htmlspecialchars($search_phone) ?>">
                        </div>
                        <button type="submit" class="btn btn-accent w-100 py-3 rounded-pill fw-bold">
                            CARI PESANAN <i class="fas fa-search ms-2"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <?php if ($_SERVER['REQUEST_METHOD'] === 'POST'): ?>
        <div class="row justify-content-center">
            <div class="col-lg-8" data-aos="fade-up">
                <?php if (empty($bookings)): ?>
                    <div class="text-center py-5">
                        <div class="mb-3 opacity-20"><i class="fas fa-receipt fa-4x"></i></div>
                        <h5>Tidak Ada Pesanan Ditemukan</h5>
                        <p class="text-muted">Pastikan nomor yang Anda masukkan sama persis dengan saat melakukan booking.</p>
                    </div>
                <?php else: ?>
                    <div class="row g-4">
                        <?php foreach ($bookings as $b): ?>
                        <div class="col-12">
                            <div class="premium-card p-0 border-0 shadow-sm overflow-hidden d-flex flex-column flex-md-row">
                                <div class="bg-alt d-flex align-items-center justify-content-center py-4 px-5" style="min-width: 200px;">
                                    <i class="fas fa-motorcycle fa-4x opacity-10 position-absolute"></i>
                                    <div class="text-center position-relative">
                                        <div class="small text-muted mb-1">ID Pesanan</div>
                                        <div class="fw-bold theme-text-heading"><?= $b['midtrans_id'] ?: 'ORDER-'.$b['id'] ?></div>
                                    </div>
                                </div>
                                <div class="p-4 flex-grow-1 border-start">
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <div>
                                            <h5 class="fw-bold mb-0"><?= $b['brand'] . ' ' . $b['model'] ?></h5>
                                            <span class="small theme-text-muted">Dipesan pada <?= date('d M Y', strtotime($b['created_at'])) ?></span>
                                        </div>
                                        <span class="badge rounded-pill bg-<?= get_status_badge($b['status']) ?> bg-opacity-10 text-<?= get_status_badge($b['status']) ?> px-3 py-2 fw-normal">
                                            <?= $b['status'] ?>
                                        </span>
                                    </div>
                                    
                                    <div class="row g-3 mb-4">
                                        <div class="col-sm-4">
                                            <div class="small text-muted mb-1">Durasi</div>
                                            <div class="fw-bold"><?= $b['duration'] ?> Hari</div>
                                        </div>
                                        <div class="col-sm-4">
                                            <div class="small text-muted mb-1">Pengambilan</div>
                                            <div class="fw-bold"><?= format_date_id($b['pickup_date']) ?></div>
                                        </div>
                                        <div class="col-sm-4">
                                            <div class="small text-muted mb-1">Total Bayar</div>
                                            <div class="fw-bold text-accent"><?= format_rupiah($b['total_price']) ?></div>
                                        </div>
                                    </div>

                                    <?php if ($b['status'] === 'Pending'): ?>
                                        <div class="mt-3">
                                            <button onclick="window.snap.pay('<?= $b['snap_token'] ?>')" class="btn btn-primary btn-sm rounded-pill px-4 fw-bold">
                                                BAYAR SEKARANG <i class="fas fa-bolt ms-1"></i>
                                            </button>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
