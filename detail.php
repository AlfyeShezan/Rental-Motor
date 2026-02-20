<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include 'includes/header.php';
include 'includes/navbar.php';

$id = $_GET['id'] ?? 0;

try {
    // Fetch Motor Model Details
    $stmt = $pdo->prepare("SELECT mm.*, 
                           (SELECT COUNT(*) FROM motors m WHERE m.model_id = mm.id AND m.status = 'Tersedia' AND m.is_active = 1 AND NOT EXISTS (SELECT 1 FROM bookings b WHERE b.motor_id = m.id AND b.status = 'Pending')) as tersedia_count
                           FROM motor_models mm WHERE mm.id = ? AND mm.is_active = 1");
    $stmt->execute([$id]);
    $motor = $stmt->fetch();

    if (!$motor) {
        redirect_with_alert('motor.php', 'Armada tidak ditemukan.', 'danger');
    }

    // Fetch Images linked to Model
    $stmt_img = $pdo->prepare("SELECT * FROM motor_images WHERE model_id = ?");
    $stmt_img->execute([$id]);
    $images = $stmt_img->fetchAll();

} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>

<!-- Minimalist Detail Section -->
<section class="py-4 theme-bg-main">
    <div class="container py-4 mt-5 pt-lg-4">
        <!-- Minimal Breadcrumb -->
        <div class="d-flex justify-content-between align-items-center mb-3" data-aos="fade-down">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb small mb-0">
                    <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none text-muted transition-all hover-accent">Beranda</a></li>
                    <li class="breadcrumb-item active text-accent fw-bold" aria-current="page"><?= htmlspecialchars($motor['brand'] . ' ' . $motor['model']) ?></li>
                </ol>
            </nav>
            <a href="javascript:void(0);" onclick="history.back();" class="btn btn-outline-secondary btn-sm rounded-pill px-4 fw-bold">
                <i class="fas fa-arrow-left me-2"></i> Kembali
            </a>
        </div>

        <div class="row g-0 border theme-border rounded-5 overflow-hidden shadow-sm theme-bg-main">
            <!-- Left Side: Visual Experience -->
            <div class="col-lg-7 border-end theme-border p-4 p-md-4 theme-bg-alt">
                <div data-aos="fade-right">
                    <div id="motorCarousel" class="carousel slide rounded-4 overflow-hidden border-0 theme-bg-main mb-3 shadow-sm" data-bs-ride="carousel">
                        <div class="carousel-inner">
                            <?php if (empty($images)): ?>
                                <div class="carousel-item active">
                                    <img src="https://images.unsplash.com/photo-1558981403-c5f9899a28bc?ixlib=rb-1.2.1&auto=format&fit=crop&w=1200&q=80" 
                                         class="d-block w-100" style="height: 420px; object-fit: cover;">
                                </div>
                            <?php else: ?>
                                <?php foreach ($images as $index => $img): ?>
                                    <div class="carousel-item <?= $index === 0 ? 'active' : '' ?>">
                                        <img src="<?= BASE_URL . 'uploads/motors/' . $img['image_path'] ?>" 
                                             class="d-block w-100" style="height: 420px; object-fit: cover;">
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                        <?php if (count($images) > 1): ?>
                            <button class="carousel-control-prev minimalist-nav" type="button" data-bs-target="#motorCarousel" data-bs-slide="prev">
                                <i class="fas fa-arrow-left"></i>
                            </button>
                            <button class="carousel-control-next minimalist-nav" type="button" data-bs-target="#motorCarousel" data-bs-slide="next">
                                <i class="fas fa-arrow-right"></i>
                            </button>
                        <?php endif; ?>
                    </div>

                    <!-- Minimalist Thumbnails -->
                    <?php if (count($images) > 1): ?>
                    <div class="d-flex gap-2 overflow-auto pb-2 mb-4 no-scrollbar slider-dots">
                        <?php foreach ($images as $index => $img): ?>
                            <div class="flex-shrink-0" style="width: 70px;">
                                <img src="<?= BASE_URL . 'uploads/motors/' . $img['image_path'] ?>" 
                                     class="img-fluid rounded-3 border-2 cursor-pointer minimalist-thumb <?= $index === 0 ? 'active' : '' ?>" 
                                     data-bs-target="#motorCarousel" 
                                     data-bs-slide-to="<?= $index ?>" 
                                     style="height: 45px; width: 100%; object-fit: cover;">
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>

                    <div class="description-section">
                        <h6 class="fw-800 theme-text-heading mb-2 text-uppercase letter-spacing-1 small" style="font-size: 0.7rem;">Tentang Armada</h6>
                        <div class="theme-text-muted" style="line-height: 1.6; font-size: 0.9rem;">
                            <?= nl2br(htmlspecialchars($motor['description'])) ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Side: Unit Control & Booking -->
            <div class="col-lg-5 p-4 p-md-4 theme-bg-main d-flex flex-column">
                <div data-aos="fade-left">
                    <div class="mb-4 pb-3 border-bottom">
                        <span class="text-accent fw-bold small text-uppercase mb-1 d-block letter-spacing-1" style="font-size: 0.75rem;">Prime Inventory</span>
                        <h2 class="fw-800 theme-text-heading mb-1 h2"><?= $motor['brand'] . ' ' . $motor['model'] ?></h2>
                        <div class="d-flex align-items-center gap-2 mt-2">
                            <h3 class="theme-text-primary fw-800 mb-0 h3">Rp <?= number_format($motor['price_per_day'], 0, ',', '.') ?></h3>
                            <span class="text-muted">/ hari</span>
                        </div>
                    </div>

                    <!-- Simple Specs Grid -->
                    <div class="row g-2 mb-4">
                        <div class="col-6">
                            <div class="p-2 border rounded-3 theme-bg-alt bg-opacity-50 h-100">
                                <small class="text-muted d-block mb-1 fw-bold text-uppercase" style="font-size: 0.65rem;">Transmisi</small>
                                <span class="fw-bold theme-text-heading"><?= $motor['type'] ?></span>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-2 border rounded-3 theme-bg-alt bg-opacity-50 h-100">
                                <small class="text-muted d-block mb-1 fw-bold text-uppercase" style="font-size: 0.65rem;">Tahun</small>
                                <span class="fw-bold theme-text-heading"><?= $motor['year'] ?></span>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="p-2 border rounded-3 theme-bg-alt bg-opacity-50 d-flex justify-content-between align-items-center h-100">
                                <div>
                                    <small class="text-muted d-block mb-1 fw-bold text-uppercase" style="font-size: 0.65rem;">Unit Tersedia</small>
                                    <span class="fw-bold theme-text-heading"><?= $motor['tersedia_count'] ?> Unit Ready</span>
                                </div>
                                <div class="text-end text-success">
                                    <i class="fas fa-check-circle me-1"></i> <span class="fw-bold" style="font-size: 0.75rem;">Verified Fleet</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Simplified Booking Hub -->
                    <div class="booking-hub mt-auto p-3 border theme-border rounded-4 theme-bg-main shadow-sm">
                        <form id="bookingControlForm">
                            <!-- Separate Date Selection with Time -->
                            <div class="row g-2 mb-4">
                                <div class="col-6">
                                    <label class="form-label small fw-800 text-muted text-uppercase mb-2" style="font-size: 0.75rem;">Tanggal & Jam Ambil</label>
                                    <div class="input-group border rounded-pill overflow-hidden bg-light-soft theme-bg-alt">
                                        <span class="input-group-text bg-transparent border-0 theme-text-muted ps-3 pe-0"><i class="fas fa-calendar-alt small"></i></span>
                                        <input type="text" id="startDate" class="form-control border-0 bg-transparent py-2 fw-bold theme-text-heading small" placeholder="Pilih Waktu" readonly>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <label class="form-label small fw-800 text-muted text-uppercase mb-2" style="font-size: 0.75rem;">Tanggal & Jam Kembali</label>
                                    <div class="input-group border rounded-pill overflow-hidden bg-light-soft theme-bg-alt">
                                        <span class="input-group-text bg-transparent border-0 theme-text-muted ps-3 pe-0"><i class="fas fa-calendar-check small"></i></span>
                                        <input type="text" id="endDate" class="form-control border-0 bg-transparent py-2 fw-bold theme-text-heading small" placeholder="Pilih Waktu" readonly>
                                    </div>
                                </div>
                                <input type="hidden" id="pickupDate" name="pickup_date">
                                <input type="hidden" id="duration" name="duration" value="1">
                                <div class="col-12">
                                    <p class="small text-accent mt-2 ps-2 mb-0 fw-bold" id="rangeSummary" style="display:none;"></p>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label small fw-800 text-muted text-uppercase mb-2" style="font-size: 0.75rem;">Kode Promo (Opsional)</label>
                                <div class="input-group border rounded-pill overflow-hidden bg-light-soft theme-bg-alt">
                                    <input type="text" id="promoCode" class="form-control border-0 bg-transparent ps-4 py-2 text-uppercase fw-bold" placeholder="Masukkan kode">
                                    <button class="btn btn-accent px-4 fw-bold" type="button" onclick="checkPromo()" id="btnApplyPromo">CEK</button>
                                </div>
                                <div id="promoMsg" class="small mt-1 ps-2 fw-600" style="display:none;"></div>
                            </div>

                             <div class="d-flex justify-content-between align-items-center mb-3 px-1">
                                <div class="total-label">
                                    <span class="text-muted fw-bold text-uppercase d-block" style="font-size: 0.65rem;">Total Estimasi</span>
                                    <h3 class="fw-800 text-primary mb-0" id="totalDisplay">Rp <?= number_format($motor['price_per_day'], 0, ',', '.') ?></h3>
                                </div>
                                <div class="text-end">
                                    <?php if($motor['tersedia_count'] > 0): ?>
                                        <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3 py-2 fw-bold" style="font-size: 0.75rem;">READY <?= $motor['tersedia_count'] ?> UNIT</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger bg-opacity-10 text-danger rounded-pill px-3 py-2 fw-bold" style="font-size: 0.75rem;">STOK HABIS</span>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <button type="button" onclick="openBookingModal()" class="btn btn-primary w-100 py-3 fw-800 rounded-pill shadow-sm transition-all primary-cta" <?= $motor['tersedia_count'] <= 0 ? 'disabled' : '' ?>>
                                <i class="fas fa-bolt me-2"></i> <?= $motor['tersedia_count'] > 0 ? 'BOOKING & BAYAR' : 'STOK TIDAK TERSEDIA' ?>
                            </button>
                        </form>
                    </div>

                    <!-- Support Card -->
                    <div class="mt-3 p-3 border rounded-4 theme-bg-alt d-flex align-items-center gap-3">
                        <div class="theme-bg-main p-2 rounded-circle border theme-border shadow-sm" style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-headset text-primary"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-0 theme-text-heading" style="font-size: 0.85rem;">Layanan Support 24/7</h6>
                            <p class="text-muted small mb-0" style="font-size: 0.8rem;">Admin kami siap membantu Anda.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Booking Modal -->
<div class="modal fade" id="bookingModal" tabindex="-1" aria-labelledby="bookingModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content rounded-4 border-0">
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title fw-bold" id="bookingModalLabel">Lengkapi Data Pemesan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body pt-2">
                <!-- Order Summary -->
                <div class="ringkasan-pesanan mb-4" data-aos="fade-in">
                    <h6 class="fw-bold theme-text-heading mb-3 small text-uppercase letter-spacing-1">Ringkasan Pesanan</h6>
                    <div class="summary-item">
                        <span class="text-muted">Unit:</span>
                        <span class="fw-bold theme-text-heading"><?= $motor['brand'] . ' ' . $motor['model'] ?></span>
                    </div>
                    <div class="summary-item">
                        <span class="text-muted">Durasi:</span>
                        <span class="fw-bold theme-text-heading" id="summaryDuration">1 Hari</span>
                    </div>
                    <div class="summary-item">
                        <span class="text-muted">Tanggal Ambil:</span>
                        <span class="fw-bold theme-text-heading" id="summaryStart">-</span>
                    </div>
                    <div class="summary-item">
                        <span class="text-muted">Total Bayar:</span>
                        <span class="fw-bold text-primary" id="summaryTotal">Rp 0</span>
                    </div>
                </div>

                <form id="finalBookingForm">
                    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                    <input type="hidden" id="final_motor_id" name="motor_id" value="<?= $id ?>">
                    <input type="hidden" id="final_duration" name="duration">
                    <input type="hidden" id="final_pickup_date" name="pickup_date">
                    <input type="hidden" id="final_total_price" name="total_price">
                    <input type="hidden" id="final_promo_id" name="promo_id" value="">
                    <input type="hidden" id="final_discount_amount" name="discount_amount" value="0">

                    <div class="row g-3">
                        <div class="col-md-6 mb-3">
                            <label class="form-label small fw-bold text-muted">Nama Lengkap sesuai KTP</label>
                            <input type="text" class="form-control rounded-pill" id="customerName" name="name" required placeholder="Contoh: Budi Santoso" title="Nama lengkap minimal 3 karakter">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label small fw-bold text-muted">NIK / No. KTP</label>
                            <input type="text" class="form-control rounded-pill" id="customerNIK" name="nik" required placeholder="16 Digit NIK" maxlength="16" pattern="\d{16}" title="NIK harus 16 digit angka">
                        </div>
                    </div>
                    
                    <div class="row g-3">
                        <div class="col-md-6 mb-3">
                            <label class="form-label small fw-bold text-muted">Nomor WhatsApp Aktif</label>
                            <input type="tel" class="form-control rounded-pill" id="customerPhone" name="phone" required placeholder="Contoh: 08123456789" pattern="^08[0-9]{8,11}$" title="Format: 08xxxxxxxx (10-13 digit)">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label small fw-bold text-muted">Email (Opsional)</label>
                            <input type="email" class="form-control rounded-pill" id="customerEmail" name="email" placeholder="Contoh: budi@gmail.com">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">Alamat Lengkap (Domisili)</label>
                        <textarea class="form-control rounded-4" id="customerAddress" name="address" rows="2" required placeholder="Jalan, RT/RW, Kelurahan, Kecamatan"></textarea>
                    </div>
                    
                    <hr class="opacity-10 my-3">
                    <h6 class="small fw-bold text-primary mb-3">Kontak Darurat (Wajib)</h6>
                    
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted">Nama Kerabat</label>
                            <input type="text" class="form-control rounded-pill" id="emergencyName" name="emergency_contact" required placeholder="Nama Kerabat">
                        </div>
                        <div class="col-md-6">
                             <label class="form-label small fw-bold text-muted">No. HP Kerabat</label>
                             <input type="tel" class="form-control rounded-pill" id="emergencyPhone" name="emergency_phone" required placeholder="08xxx">
                        </div>
                    </div>

                    <div class="mb-3">
                         <label class="form-label small fw-bold text-muted">Lokasi Pengambilan Unit</label>
                         <select class="form-select rounded-pill" id="locSelect" name="location">
                             <option value="Ambil di Tempat">Ambil di Garasi (Jl. Malioboro No. 123)</option>
                             <option value="Antar ke Lokasi">Antar ke Lokasi (Gratis / Free)</option>
                         </select>
                    </div>

                    <div class="alert alert-info small border-0 bg-opacity-10 bg-info">
                        Pastikan data Anda benar. Kami akan menghubungi via WhatsApp untuk konfirmasi lanjut jika diperlukan.
                    </div>
                    
                    <div class="row g-2">
                        <div class="col-6">
                            <button type="button" class="btn btn-outline-secondary w-100 rounded-pill fw-bold py-2" data-bs-dismiss="modal">
                                Batal
                            </button>
                        </div>
                        <div class="col-6">
                            <button type="submit" class="btn btn-primary w-100 rounded-pill fw-bold py-2">
                                Bayar <i class="fas fa-arrow-right ms-1"></i>
                            </button>
                        </div>
                    </div>
                    <div id="paymentLoading" class="text-center mt-2" style="display:none;">
                        <div class="spinner-border spinner-border-sm text-primary" role="status"></div> Sedang memproses...
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Load Midtrans Snap.js -->
<script src="<?= MIDTRANS_SNAP_JS ?>" data-client-key="<?= MIDTRANS_CLIENT_KEY ?>"></script>

<script>
let pricePerDay = <?= $motor['price_per_day'] ?>;
const motorId = <?= $id ?>;
let activePromo = null;

// Initialize Flatpickr for Separate Inputs with Time
window.addEventListener('load', () => {
    if (typeof flatpickr !== 'undefined') {
        const startPicker = flatpickr("#startDate", {
            minDate: "today",
            enableTime: true,
            time_24hr: true,
            dateFormat: "d M Y H:i",
            onChange: function(selectedDates, dateStr) {
                endPicker.set("minDate", dateStr);
                document.getElementById('pickupDate').value = startPicker.formatDate(selectedDates[0], "Y-m-d H:i:s");
                calculateDuration();
            }
        });

        const endPicker = flatpickr("#endDate", {
            minDate: "today",
            enableTime: true,
            time_24hr: true,
            dateFormat: "d M Y H:i",
            onChange: function() {
                calculateDuration();
            }
        });

        function calculateDuration() {
            const start = startPicker.selectedDates[0];
            const end = endPicker.selectedDates[0];

            if (start && end) {
                // Calculate difference in milliseconds
                const diffMs = end - start;
                // Convert to hours
                const diffHours = diffMs / (1000 * 60 * 60);
                
                // 24-hour based logic: 1-24 hours = 1 day, 24.1-48 hours = 2 days, etc.
                // We use Math.ceil(diffHours / 24) to handle this.
                // If diffHours is exactly 0 or negative (shouldn't happen with minDate), we default to 1
                let diffDays = Math.ceil(diffHours / 24);
                if (diffDays <= 0) diffDays = 1;
                
                document.getElementById('duration').value = diffDays;
                
                const summary = document.getElementById('rangeSummary');
                summary.style.display = 'block';
                summary.innerHTML = `<i class="fas fa-info-circle me-1"></i> Sewa selama <strong>${diffDays} Hari</strong> (Logika 24 Jam)`;
                
                updatePrice();
            }
        }
    } else {
        console.error("Flatpickr library not loaded correctly.");
    }
});

function formatIDR(amount) {
    return new Intl.NumberFormat('id-ID', { 
        style: 'currency', 
        currency: 'IDR', 
        maximumFractionDigits: 0 
    }).format(amount).replace('IDR', 'Rp');
}

function calculateTotal() {
    const dur = Math.max(1, parseInt(document.getElementById('duration').value) || 1);
    let total = dur * pricePerDay;
    let disc = 0;

    if (activePromo) {
        if (activePromo.type === 'Percent') {
            disc = (total * activePromo.value) / 100;
        } else {
            disc = activePromo.value;
        }
    }
    total = Math.max(0, total - disc);
    return total;
}

function updatePrice() {
    let total = calculateTotal();
    const formatted = formatIDR(total);
    document.getElementById('totalDisplay').innerText = formatted;
}

function checkPromo() {
    const code = document.getElementById('promoCode').value;
    const msgDiv = document.getElementById('promoMsg');
    const btn = document.getElementById('btnApplyPromo');
    
    if (!code) return;

    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

    fetch(`ajax/check_promo.php?code=${code}`)
        .then(res => res.json())
        .then(res => {
            btn.disabled = false;
            btn.innerHTML = 'CEK';
            msgDiv.style.display = 'block';

            if (res.status === 'success') {
                activePromo = res.data;
                msgDiv.className = 'small mt-1 ps-2 fw-600 text-success';
                msgDiv.innerHTML = `<i class="fas fa-check-circle me-1"></i> Promo Berhasil: Potongan ${res.data.type === 'Percent' ? res.data.value + '%' : formatIDR(res.data.value)}`;
                
                // Update hidden fields for backend
                document.getElementById('final_promo_id').value = res.data.id;
                const discountAmount = res.data.type === 'Percent' ? 
                    Math.floor(calculateTotal() * res.data.value / 100) : res.data.value;
                document.getElementById('final_discount_amount').value = discountAmount;
                
                updatePrice();
            } else {
                activePromo = null;
                msgDiv.className = 'small mt-1 ps-2 fw-600 text-danger';
                msgDiv.innerHTML = res.message;
                updatePrice();
            }
        });
}

function changeQty(val) {
    const input = document.getElementById('duration');
    let current = parseInt(input.value) || 1;
    input.value = Math.max(1, current + val);
    updatePrice();
}

document.getElementById('duration').addEventListener('input', updatePrice);

// --- New Booking Flow ---
function openBookingModal() {
    const dur = document.getElementById('duration').value;
    const date = document.getElementById('pickupDate').value;
    const start = document.getElementById('startDate').value;
    const end = document.getElementById('endDate').value;

    if (!start || !end || !date) {
        alert("Silakan pilih tanggal ambil dan kembali terlebih dahulu!");
        return;
    }

    // Fill hidden inputs
    document.getElementById('final_duration').value = dur;
    document.getElementById('final_pickup_date').value = date;
    document.getElementById('final_total_price').value = calculateTotal();

    // Populate Summary
    document.getElementById('summaryDuration').innerText = dur + " Hari";
    document.getElementById('summaryStart').innerText = start;
    document.getElementById('summaryTotal').innerText = formatIDR(calculateTotal());

    const bsModal = new bootstrap.Modal(document.getElementById('bookingModal'));
    bsModal.show();
}

document.getElementById('finalBookingForm').addEventListener('submit', function(e) {
    e.preventDefault();

    // Field Values
    const name = document.getElementById('customerName').value.trim();
    const nik = document.getElementById('customerNIK').value.trim();
    const phone = document.getElementById('customerPhone').value.trim();
    const address = document.getElementById('customerAddress').value.trim();
    const emergencyName = document.getElementById('emergencyName').value.trim();
    const emergencyPhone = document.getElementById('emergencyPhone').value.trim();

    // Strict Validation
    if(name.length < 3) { alert('Nama terlalu pendek!'); return; }
    if(!/^\d{16}$/.test(nik)) { alert('NIK harus 16 digit angka!'); return; }
    if(!/^08\d{8,11}$/.test(phone)) { alert('Nomor WhatsApp tidak valid (Gunakan 08xxx)!'); return; }
    if(address.length < 10) { alert('Alamat harus lengkap (min 10 karakter)!'); return; }
    if(emergencyName.length < 3) { alert('Nama kontak darurat wajib diisi!'); return; }
    if(!/^08\d{8,11}$/.test(emergencyPhone)) { alert('Nomor kontak darurat tidak valid!'); return; }
    if(phone === emergencyPhone) { alert('Nomor darurat tidak boleh sama dengan nomor pemesan!'); return; }
    
    const btn = this.querySelector('button[type="submit"]');
    const loading = document.getElementById('paymentLoading');
    const formData = new FormData(this);

    btn.disabled = true;
    loading.style.display = 'block';

    fetch('process_booking.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        btn.disabled = false;
        loading.style.display = 'none';

        if (data.status === 'success') {
            // Hide Modal
            bootstrap.Modal.getInstance(document.getElementById('bookingModal')).hide();
            
            // Trigger Snap
            window.snap.pay(data.data.token, {
                onSuccess: function(result){
                    // Auto-Update Status via AJAX (Silent Check)
                    fetch('ajax/update_payment_status.php?order_id=' + data.data.order_id)
                        .then(response => response.json())
                        .then(data => {
                            if(data.status === 'success') {
                                alert("Pembayaran LUNAS! Terima kasih.");
                            } else {
                                alert("Pembayaran berhasil! Status akan diperbarui otomatis. Jika belum berubah, hubungi admin.");
                            }
                            window.location.href = "index.php";
                        })
                        .catch(err => {
                            // Network error - still redirect but warn user
                            alert("Pembayaran berhasil! Jika status belum berubah, silakan hubungi admin untuk verifikasi.");
                            window.location.href = "index.php";
                        });
                },
                onPending: function(result){
                    alert("Menunggu pembayaran! Silakan selesaikan pembayaran Anda.");
                    window.location.href = "index.php";
                },
                onError: function(result){
                    alert("Pembayaran gagal!");
                },
                onClose: function(){
                    // User closed the popup without finishing payment
                    fetch('ajax/cancel_booking.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({ order_id: data.data.order_id })
                    }).then(() => {
                        alert('Pembayaran dibatalkan.');
                        window.location.reload(); 
                    }).catch(err => {
                        // Silent fail - just reload
                        window.location.reload();
                    });
                }
            });
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        btn.disabled = false;
        loading.style.display = 'none';
        alert('Terjadi kesalahan koneksi.');
        console.error('Error:', error);
    });
});

document.querySelectorAll('.minimalist-thumb').forEach(thumb => {
    thumb.addEventListener('click', function() {
        document.querySelectorAll('.minimalist-thumb').forEach(t => t.classList.remove('active'));
        this.classList.add('active');
    });
});
</script>

<style>
.fw-800 { font-weight: 800; }
.letter-spacing-1 { letter-spacing: 1.5px; }
.no-scrollbar::-webkit-scrollbar { display: none; }
.no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }

.minimalist-thumb { opacity: 0.4; transition: all 0.3s ease; border-color: transparent !important; }
.minimalist-thumb:hover, .minimalist-thumb.active { opacity: 1; border-color: var(--accent) !important; transform: scale(1.05); }

.minimalist-nav { width: 40px; height: 40px; background: white; border-radius: 50%; top: 50%; transform: translateY(-50%); opacity: 0; transition: all 0.3s ease; display: flex; align-items: center; justify-content: center; color: var(--primary); box-shadow: 0 5px 15px rgba(0,0,0,0.05); border: none; font-size: 0.8rem; }
.carousel:hover .minimalist-nav { opacity: 1; }
.minimalist-nav:hover { background: var(--primary); color: white; }
.carousel-control-prev.minimalist-nav { left: 15px; }
.carousel-control-next.minimalist-nav { right: 15px; }

.primary-cta { background: var(--primary); border: none; }
.primary-cta:hover { background: #000; transform: translateY(-3px); box-shadow: 0 10px 20px rgba(0,0,0,0.1); }

.rounded-5 { border-radius: 2rem !important; }
.hover-accent:hover { color: var(--accent) !important; }
</style>


<?php include 'includes/footer.php'; ?>
