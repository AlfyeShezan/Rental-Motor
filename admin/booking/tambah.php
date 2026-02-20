<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../template/auth.php';
require_once __DIR__ . '/../../config/helper.php';

// Fetch Available Physical Units for Selection
try {
    $stmt_motors = $pdo->query("SELECT m.id, mm.brand, mm.model, m.plate_number, mm.price_per_day 
                               FROM motors m 
                               JOIN motor_models mm ON m.model_id = mm.id 
                               WHERE m.is_active = 1 
                               AND m.status = 'Tersedia' 
                               AND mm.is_active = 1
                               AND NOT EXISTS (SELECT 1 FROM bookings b WHERE b.motor_id = m.id AND b.status = 'Pending')");
    $motors = $stmt_motors->fetchAll();
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $motor_id = $_POST['motor_id'];
    $name = $_POST['name'];
    $phone = $_POST['phone'];
    $pickup_date = $_POST['pickup_date'];
    $return_date = $_POST['return_date'];
    $duration = $_POST['duration'];
    $location = $_POST['location'];
    $status = $_POST['status'];
    $notes = $_POST['notes'];

    // Calculate Price from model
    $stmt_p = $pdo->prepare("SELECT mm.price_per_day, m.status 
                            FROM motors m 
                            JOIN motor_models mm ON m.model_id = mm.id 
                            WHERE m.id = ?");
    $stmt_p->execute([$motor_id]);
    $unit_info = $stmt_p->fetch();
    
    if (!$unit_info || $unit_info['status'] !== 'Tersedia') {
        $_SESSION['alert_message'] = "Gagal: Unit motor tidak tersedia.";
        $_SESSION['alert_type'] = "danger";
        header("Location: index.php");
        exit();
    }

    $total_price = $unit_info['price_per_day'] * $duration;

    try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare("INSERT INTO bookings (motor_id, name, phone, pickup_date, return_date, duration, location, total_price, status, notes) 
                              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$motor_id, $name, $phone, $pickup_date, $return_date, $duration, $location, $total_price, $status, $notes]);

        // Update Unit Status if immediately rented
        if ($status === 'Disewa') {
            $stmt_m = $pdo->prepare("UPDATE motors SET status = 'Disewa' WHERE id = ?");
            $stmt_m->execute([$motor_id]);
        }

        $pdo->commit();
        require_once __DIR__ . '/../../config/backup_helper.php';
        trigger_auto_backup($pdo);
        
        redirect_with_alert('index.php', 'Booking manual berhasil ditambahkan.');
    } catch (PDOException $e) {
        $pdo->rollBack();
        $_SESSION['alert_message'] = "Gagal menambah booking: " . $e->getMessage();
        $_SESSION['alert_type'] = "danger";
    }
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
            <h5 class="mb-0 fw-bold ms-2">Input Booking Manual</h5>
            <div class="ms-auto">
                <a href="index.php" class="btn btn-light rounded-pill px-3 py-1.5 border small"><i class="fas fa-arrow-left me-1"></i> Kembali</a>
            </div>
        </div>
    </nav>

    <div class="main-content">
        <?php display_alert(); ?>

        <form action="" method="POST">
            <div class="row g-4">
                <div class="col-lg-8">
                    <div class="card border-0 shadow-sm rounded-4 p-4">
                        <h6 class="fw-bold mb-4 text-primary d-flex align-items-center gap-2">
                            <i class="fas fa-user-circle"></i> Data Pelanggan
                        </h6>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted">Nama Lengkap</label>
                                <input type="text" name="name" class="form-control bg-light-soft border" required placeholder="Nama sesuai KTP">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted">Nomor HP / WA</label>
                                <input type="text" name="phone" class="form-control bg-light-soft border" required placeholder="Contoh: 08123456789">
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-bold text-muted">Lokasi Pengantaran</label>
                                <textarea name="location" class="form-control bg-light-soft border" rows="2" placeholder="Alamat lengkap atau nama tempat..."></textarea>
                            </div>
                        </div>

                        <h6 class="fw-bold mb-4 mt-5 text-primary d-flex align-items-center gap-2">
                            <i class="fas fa-motorcycle"></i> Detail Sewa
                        </h6>
                        <div class="row g-3">
                            <div class="col-md-12">
                                <label class="form-label small fw-bold text-muted">Pilih Unit (Tersedia)</label>
                                <select name="motor_id" id="motor_id" class="form-select bg-light-soft border" required>
                                    <option value="">-- Pilih Unit Motor --</option>
                                    <?php foreach ($motors as $m): ?>
                                        <option value="<?= $m['id'] ?>" data-price="<?= $m['price_per_day'] ?>">
                                            <?= htmlspecialchars($m['brand'] . ' ' . $m['model'] . ' [' . $m['plate_number'] . ']') ?> (<?= format_rupiah($m['price_per_day']) ?>/hari)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Tanggal & Jam Mulai</label>
                                <input type="datetime-local" name="pickup_date" id="pickup_date" class="form-control" required min="<?= date('Y-m-d\TH:i') ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Tanggal & Jam Selesai</label>
                                <input type="datetime-local" name="return_date" id="return_date" class="form-control" required min="<?= date('Y-m-d\TH:i') ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Durasi (Hari)</label>
                                <input type="number" step="0.1" name="duration" id="duration" class="form-control" readonly value="0">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Total Harga</label>
                                <input type="text" id="total_price_display" class="form-control fw-bold text-dark" readonly value="Rp 0">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card p-4">
                        <h6 class="fw-bold mb-4">Pengaturan & Catatan</h6>
                        <div class="mb-4">
                            <label class="form-label small fw-bold">Status Awal</label>
                            <select name="status" class="form-select">
                                <option value="Pending">Pending</option>
                                <option value="Disewa">Langsung Sewa (Confirmed)</option>
                            </select>
                        </div>
                        <div class="mb-4">
                            <label class="form-label small fw-bold">Catatan Internal</label>
                            <textarea name="notes" class="form-control" rows="4" placeholder="Contoh: Titip KTP, Helm 2, dll"></textarea>
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-accent fw-bold py-2 rounded-pill shadow-sm small">Simpan Transaksi</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const motorId = document.getElementById('motor_id');
    const pickupDate = document.getElementById('pickup_date');
    const returnDate = document.getElementById('return_date');
    const durationInput = document.getElementById('duration');
    const totalDisplay = document.getElementById('total_price_display');

    function calculate() {
        const selected = motorId.options[motorId.selectedIndex];
        const price = selected ? selected.dataset.price : 0;
        
        if (pickupDate.value && returnDate.value && price) {
            const start = new Date(pickupDate.value);
            const end = new Date(returnDate.value);
            const diffMs = end - start;
            const diffDays = Math.ceil(diffMs / (1000 * 60 * 60 * 24));
            
            if (diffDays > 0) {
                durationInput.value = diffDays;
                const total = diffDays * price;
                totalDisplay.value = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(total);
            } else {
                durationInput.value = 0;
                totalDisplay.value = 'Rp 0';
            }
        }
    }

    motorId.addEventListener('change', calculate);
    pickupDate.addEventListener('change', calculate);
    returnDate.addEventListener('change', calculate);
});
</script>

<?php include __DIR__ . '/../template/footer.php'; ?>
