<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../template/auth.php';
require_once __DIR__ . '/../../config/helper.php';

$id = $_GET['id'] ?? 0;

try {
    $stmt = $pdo->prepare("SELECT b.*, mm.brand, mm.model, mm.price_per_day, m.id as mid, m.plate_number 
                          FROM bookings b 
                          JOIN motors m ON b.motor_id = m.id 
                          JOIN motor_models mm ON m.model_id = mm.id
                          WHERE b.id = ?");
    $stmt->execute([$id]);
    $booking = $stmt->fetch();

    if (!$booking) {
        redirect_with_alert('index.php', 'Booking tidak ditemukan.', 'danger');
    }

    // Fetch all available physical units (plus the currently assigned one)
    $stmt_motors = $pdo->prepare("SELECT m.id, mm.brand, mm.model, m.plate_number 
                                FROM motors m 
                                JOIN motor_models mm ON m.model_id = mm.id 
                                WHERE (m.status = 'Tersedia' OR m.id = ?) 
                                AND m.is_active = 1 AND mm.is_active = 1");
    $stmt_motors->execute([$booking['motor_id']]);
    $available_motors = $stmt_motors->fetchAll();

    // Handle Form Submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $old_status = $booking['status'];
        $new_status = $_POST['status'];
        $notes = $_POST['notes'];
        $additional_fees = $_POST['additional_fees'] ?: 0;
        $late_fees = $_POST['late_fees'] ?: 0;
        
        $total_bayar = $booking['total_price'] + $additional_fees + $late_fees;
        $new_motor_id = $_POST['motor_id'] ?? $booking['motor_id'];

        try {
            $pdo->beginTransaction();

            $stmt_u = $pdo->prepare("UPDATE bookings SET status = ?, notes = ?, total_price = ?, motor_id = ? WHERE id = ?");
            $stmt_u->execute([$new_status, $notes, $total_bayar, $new_motor_id, $id]);

            // Handle Stock/Unit Status
            require_once __DIR__ . '/../../config/stock_helper.php';
            
            // If motor ID changed, we need to manually handle the OLD motor status
            if ($new_motor_id != $booking['motor_id']) {
                // If the old motor was 'Disewa', revert it to 'Tersedia'
                if ($old_status == 'Disewa') {
                    $stmt_revert = $pdo->prepare("UPDATE motors SET status = 'Tersedia' WHERE id = ?");
                    $stmt_revert->execute([$booking['motor_id']]);
                }
                
                // Now mark the NEW motor as 'Disewa' if the new status is 'Disewa'
                if ($new_status == 'Disewa') {
                    $stmt_mark = $pdo->prepare("UPDATE motors SET status = 'Disewa' WHERE id = ?");
                    $stmt_mark->execute([$new_motor_id]);
                }
            } else {
                // No motor change, use standard helper
                update_motor_stock($pdo, $id, $old_status, $new_status);
            }
            
            $pdo->commit();
            require_once __DIR__ . '/../../config/backup_helper.php';
            trigger_auto_backup($pdo);
            // Preserve filter parameters from referer
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
            redirect_with_alert($redirect_url, 'Booking berhasil diperbarui.');
        } catch (PDOException $e) {
            $pdo->rollBack();
            $_SESSION['alert_message'] = "Gagal memperbarui: " . $e->getMessage();
            $_SESSION['alert_type'] = "danger";
        }
    }
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}

include __DIR__ . '/../template/header.php';
include __DIR__ . '/../template/sidebar.php';
?>

<div id="content">
    <nav class="navbar top-navbar">
        <div class="container-fluid">
            <button type="button" id="sidebarCollapse" class="btn btn-light rounded-circle shadow-sm me-3 border-0 d-lg-none">
                <i class="fas fa-bars text-dark"></i>
            </button>
            <h5 class="mb-0 fw-bold ms-2">Update Order: #<?= $id ?></h5>
            <div class="ms-auto shadow-sm rounded-pill overflow-hidden border">
                <a href="index.php" class="btn btn-white rounded-pill px-3 py-1.5 border small shadow-sm"><i class="fas fa-chevron-left me-1"></i> Kembali</a>
            </div>
        </div>
    </nav>

    <div class="main-content">
        <?php display_alert(); ?>

        <div class="row g-4">
            <div class="col-lg-7">
                <div class="card border-0 shadow-sm rounded-4 p-4">
                    <h6 class="fw-bold mb-4 text-primary d-flex align-items-center gap-2">
                        <i class="fas fa-receipt"></i> Ringkasan Pesanan
                    </h6>
                    <div class="table-responsive">
                        <table class="table table-borderless small mb-0">
                            <tr>
                                <td class="text-muted fw-bold ps-0" width="35%">Pelanggan</td>
                                <td>: <span class="fw-bold text-dark"><?= htmlspecialchars($booking['name']) ?></span> <br> 
                                    <span class="small text-muted">
                                        <i class="fas fa-id-card me-1"></i> <?= $booking['nik'] ?><br>
                                        <i class="fab fa-whatsapp text-success me-1"></i> <?= $booking['phone'] ?>
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted fw-bold ps-0">Alamat Domisili</td>
                                <td>: <span class="small"><?= htmlspecialchars($booking['address']) ?></span></td>
                            </tr>
                            <tr>
                                <td class="text-muted fw-bold ps-0">Kontak Darurat</td>
                                <td>: <span class="fw-bold"><?= $booking['emergency_contact'] ?></span> <span class="text-muted">(<?= $booking['emergency_phone'] ?>)</span></td>
                            </tr>
                            <tr>
                                <td class="text-muted fw-bold ps-0">Unit Motor</td>
                                <td>
                                    <div class="input-group input-group-sm">
                                        <select name="motor_id" class="form-select fw-bold text-primary border-primary bg-light-soft">
                                            <?php foreach($available_motors as $m): ?>
                                                <option value="<?= $m['id'] ?>" <?= $m['id'] == $booking['mid'] ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($m['brand'] . ' ' . $m['model'] . ' [' . $m['plate_number'] . ']') ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <small class="text-muted mt-1 d-block"><i class="fas fa-info-circle me-1"></i> Admin bisa menukar unit plat nomor jika diperlukan.</small>
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted fw-bold ps-0">Periode Sewa</td>
                                <td>: <?= format_date_id($booking['pickup_date']) ?> - <?= format_date_id($booking['return_date']) ?> (<?= $booking['duration'] ?> Hari)</td>
                            </tr>
                            <tr>
                                <td class="text-muted fw-bold ps-0">Lokasi Antar</td>
                                <td>: <?= $booking['location'] ?></td>
                            </tr>
                        </table>
                    </div>
                    <hr class="opacity-10 my-4">
                    <form action="" method="POST">
                        <div class="mb-4">
                            <label class="form-label small fw-bold text-muted">Update Status Pesanan</label>
                            <select name="status" class="form-select fw-bold bg-light-soft border">
                                <option value="Pending" <?= $booking['status'] == 'Pending' ? 'selected' : '' ?>>Pending</option>
                                <option value="Disewa" <?= $booking['status'] == 'Disewa' ? 'selected' : '' ?>>Sedang Disewa (Active)</option>
                                <option value="Selesai" <?= $booking['status'] == 'Selesai' ? 'selected' : '' ?>>Selesai (Motor Kembali)</option>
                                <option value="Batal" <?= $booking['status'] == 'Batal' ? 'selected' : '' ?>>Batal</option>
                            </select>
                        </div>
                        <div class="mb-4">
                            <label class="form-label small fw-bold text-muted">Catatan / Keterangan</label>
                            <textarea name="notes" class="form-control bg-light-soft border" rows="3"><?= htmlspecialchars($booking['notes']) ?></textarea>
                        </div>
                        <div class="d-grid mt-5">
                            <button type="submit" class="btn btn-accent fw-bold py-2 rounded-pill shadow-sm small">
                                <i class="fas fa-save me-1"></i> Simpan Update Perubahan
                            </button>
                        </div>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="card border-0 shadow-sm rounded-4 p-4">
                    <h6 class="fw-bold mb-4 text-primary d-flex align-items-center gap-2">
                        <i class="fas fa-wallet"></i> Rincian Pembayaran
                    </h6>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted small fw-bold">Biaya Sewa Pokok</span>
                        <span class="fw-bold text-dark"><?= format_rupiah($booking['total_price']) ?></span>
                    </div>
                    
                    <div class="mt-4 mb-3">
                        <label class="form-label small fw-bold text-muted">Biaya Tambahan (Helm, Antar, dll)</label>
                        <div class="input-group overflow-hidden rounded-3 border">
                            <span class="input-group-text bg-light border-0">Rp</span>
                            <input type="number" name="additional_fees" id="additional_fees" class="form-control border-0 bg-white" placeholder="0">
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label small fw-bold text-muted">Denda Keterlambatan</label>
                        <div class="input-group overflow-hidden rounded-3 border">
                            <span class="input-group-text bg-light border-0">Rp</span>
                            <input type="number" name="late_fees" id="late_fees" class="form-control border-0 bg-white" placeholder="0">
                        </div>
                        <small class="text-primary cursor-pointer d-block mt-2 fw-bold" onclick="calculateLateFee()">
                            <i class="fas fa-calculator me-1"></i> Hitung Denda Otomatis
                        </small>
                    </div>

                    <hr class="opacity-10 my-4">
                    <div class="d-flex justify-content-between align-items-center p-3 bg-light-soft rounded-3 border border-dashed">
                        <h6 class="fw-bold mb-0 text-dark">Total Tagihan</h6>
                        <h4 class="fw-bold text-accent mb-0" id="total_footer"><?= format_rupiah($booking['total_price']) ?></h4>
                    </div>
                </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
const basePrice = <?= $booking['total_price'] ?>;
const lateFeePerHour = <?= get_setting('late_fee_per_hour', 10000) ?>;
const lateFeeThreshold = <?= get_setting('late_fee_threshold_hours', 6) ?>;
const dailyPrice = <?= $booking['price_per_day'] ?>;

function calculateLateFee() {
    const returnTime = new Date("<?= str_replace(' ', 'T', $booking['return_date']) ?>");
    const today = new Date();
    
    if (today > returnTime) {
        const diffMs = today - returnTime;
        const diffHours = Math.floor(diffMs / (1000 * 60 * 60));
        const remainingMs = diffMs % (1000 * 60 * 60);
        const extraMinutes = Math.floor(remainingMs / (1000 * 60));
        
        // Count as full hour if more than 5 minutes late? (Optional, let's stick to simple floor/ceil)
        let totalHours = diffHours;
        if (extraMinutes > 5) totalHours += 1; // 5 mins grace period

        let totalLate = 0;
        if (totalHours <= lateFeeThreshold) {
            totalLate = totalHours * lateFeePerHour;
        } else {
            // Over threshold, calculate as full day(s)
            const diffDays = Math.ceil(totalHours / 24);
            totalLate = diffDays * dailyPrice;
        }

        document.getElementById('late_fees').value = totalLate;
        updateTotal();
        alert(`Keterlambatan: ${totalHours} jam.\nDenda: Rp ${totalLate.toLocaleString('id-ID')}`);
    } else {
        alert("Belum melewati batas jam pengembalian.");
    }
}

function updateTotal() {
    const add = parseInt(document.getElementById('additional_fees').value) || 0;
    const late = parseInt(document.getElementById('late_fees').value) || 0;
    const total = basePrice + add + late;
    document.getElementById('total_footer').innerText = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(total);
}

document.getElementById('additional_fees').addEventListener('input', updateTotal);
document.getElementById('late_fees').addEventListener('input', updateTotal);
</script>

<?php include __DIR__ . '/../template/footer.php'; ?>
