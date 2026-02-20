<?php
require_once 'config/config.php';
require_once 'config/helper.php';
require_once 'includes/header.php';
require_once 'includes/navbar.php';

// Fetch Featured Motor Models
try {
    $stmt = $pdo->prepare("
        SELECT mm.*, 
        (SELECT image_path FROM motor_images mi WHERE mi.model_id = mm.id ORDER BY is_primary DESC, id ASC LIMIT 1) as primary_image,
        (SELECT COUNT(*) FROM motors m 
         WHERE m.model_id = mm.id 
         AND m.status = 'Tersedia' 
         AND m.is_active = 1 
         AND NOT EXISTS (SELECT 1 FROM bookings b WHERE b.motor_id = m.id AND b.status = 'Pending')
        ) as tersedia_count
        FROM motor_models mm 
        WHERE mm.is_active = 1 
        ORDER BY mm.created_at DESC 
    ");
    $stmt->execute();
    $featured_motors = $stmt->fetchAll();
} catch (PDOException $e) {
    $featured_motors = [];
}

// Fetch Testimonials
try {
    $stmt_testi = $pdo->query("SELECT * FROM testimonials ORDER BY created_at DESC LIMIT 6");
    $testimonials = $stmt_testi->fetchAll();
} catch (PDOException $e) {
    $testimonials = [];
}
?>

<!-- 1. HERO SECTION (HOME) -->
<section id="home" class="hero-section" style="background-image: url('<?= BASE_URL ?>uploads/settings/<?= get_setting('hero_background', 'hero_bg_1771057554.png') ?>');">
    <div class="container hero-content">
        <div class="row align-items-center justify-content-center">
            <div class="col-lg-8 text-center" data-aos="fade-up">
                <span class="hero-badge mb-3">
                    <?= get_setting('hero_tag', 'Official Rental Motor') ?>
                </span>
                <h1 class="display-3 fw-bold text-white mb-4 leading-tight">
                    <?= get_setting('hero_title', 'Jelajahi Kota Dengan Kebebasan Penuh') ?>
                </h1>
                <p class="lead text-white-50 mb-5 mx-auto">
                    <?= get_setting('hero_subtitle', 'Sewa motor matic terbaru dengan harga terjangkau. Persyaratan mudah, fasilitas lengkap, dan layanan antar jemput gratis.') ?>
                </p>
                <div class="d-flex flex-column flex-sm-row gap-3 justify-content-center">
                    <a href="#motor" class="btn btn-accent btn-lg px-5">
                        <i class="fas fa-motorcycle me-2"></i> Pilih Motor
                    </a>
                    <a href="https://wa.me/<?= get_setting('whatsapp_number') ?>" class="btn btn-outline-white btn-lg px-5">
                        <i class="fab fa-whatsapp me-2"></i> Pesan Sekarang
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<div class="section-divider"></div>

<!-- 2. MOTOR SECTION (ARMADA) -->
<section id="motor" class="bg-light-soft">
    <div class="container py-4">
        <div class="text-center mb-5" data-aos="fade-up">
            <span class="section-tag"><?= get_setting('section_motor_tag', 'Armada Kami') ?></span>
            <h2 class="section-title"><?= get_setting('section_motor_title', 'Pilihan Motor Terbaru') ?></h2>
            <p class="text-muted mw-600 mx-auto"><?= get_setting('section_motor_desc', 'Unit motor selalu dalam kondisi prima, servis rutin, dan surat-surat lengkap untuk kenyamanan berkendara Anda.') ?></p>
        </div>

        <div class="row g-4">
            <?php if (empty($featured_motors)): ?>
                <?php for ($i = 0; $i < 4; $i++): ?>
                <div class="col-lg-3 col-md-6 col-12">
                    <div class="skeleton">
                        <div class="skeleton-img"></div>
                        <div class="p-3">
                            <div class="skeleton-title"></div>
                            <div class="skeleton-text"></div>
                            <div class="skeleton-text"></div>
                        </div>
                    </div>
                </div>
                <?php endfor; ?>
            <?php else: ?>
                <?php foreach ($featured_motors as $motor): ?>
                    <div class="col-lg-3 col-md-6 col-12" data-aos="fade-up">
                        <div class="motor-card">
                            <div class="motor-img-wrapper">
                                <?php if ($motor['tersedia_count'] <= 0): ?>
                                    <span class="motor-badge" style="background: var(--primary); color: white;">Habis</span>
                                <?php endif; ?>
                                
                                <img src="<?= BASE_URL ?>uploads/motors/<?= $motor['primary_image'] ?: 'default.jpg' ?>" alt="<?= htmlspecialchars($motor['brand'] . ' ' . $motor['model']) ?>" loading="lazy">
                            </div>
                            <div class="motor-info">
                                <h6 class="card-title-minimal fw-bold mb-1 theme-text-heading"><?= htmlspecialchars($motor['brand'] . ' ' . $motor['model']) ?></h6>
                                <div class="d-flex align-items-center gap-2 mb-3">
                                    <span class="text-muted small"><?= $motor['type'] ?> • <?= $motor['year'] ?></span>
                                    <span class="text-muted-opacity mx-1">|</span>
                                    <span class="text-accent small fw-bold">
                                        <span class="availability-dot me-1"></span>
                                        <?= $motor['tersedia_count'] ?> Ready
                                    </span>
                                </div>
                                <div class="d-flex align-items-center justify-content-between mt-auto">
                                    <h5 class="motor-price mb-0">Rp <?= number_format($motor['price_per_day'], 0, ',', '.') ?><span class="price-unit">/hari</span></h5>
                                    <a href="detail.php?id=<?= $motor['id'] ?>" class="btn-minimal">Detail <i class="fas fa-chevron-right ms-1"></i></a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</section>

<div class="section-divider"></div>

<!-- 3. TENTANG KAMI (ABOUT US) -->
<section id="tentang" class="bg-white">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-5" data-aos="fade-right">
                <div class="position-relative">
                    <img src="<?= BASE_URL ?>uploads/settings/<?= get_setting('about_image', 'about-bg.jpg') ?>" class="img-fluid rounded-4 shadow-lg" alt="Tentang Kami">
                    <div class="position-absolute bottom-0 end-0 bg-white p-4 rounded-4 shadow-lg m-4 d-none d-md-block">
                        <h3 class="fw-bold text-accent mb-0"><?= get_setting('about_exp_years', '5+') ?></h3>
                        <p class="text-muted small fw-bold mb-0 text-uppercase"><?= get_setting('about_exp_label', 'Tahun Pengalaman') ?></p>
                    </div>
                </div>
            </div>
            <div class="col-lg-7 d-flex flex-column justify-content-center" data-aos="fade-left">
                <span class="section-tag"><?= get_setting('about_tag', 'Tentang Kami') ?></span>
                <h2 class="section-title mb-4"><?= get_setting('about_title', 'Solusi Transportasi Terbaik Untuk Liburan Anda') ?></h2>
                <div class="text-muted mb-4 text-justify">
                    <?= get_setting('about_desc_1', 'Kami adalah penyedia jasa sewa motor terpercaya yang berkomitmen untuk memberikan pengalaman berkendara terbaik bagi wisatawan maupun warga lokal.') ?>
                </div>
                <div class="text-muted mb-4 text-justify">
                    <?= get_setting('about_desc_2', 'Dengan armada terbaru dan perawatan rutin, kami menjamin keamanan dan kenyamanan perjalanan Anda selama mengeksplorasi keindahan kota.') ?>
                </div>
                
                <div class="row g-4 mt-2">
                    <div class="col-md-4" data-aos="fade-up" data-aos-delay="100">
                        <div class="premium-card text-center h-100 p-3">
                            <div class="term-icon mx-auto mb-3" style="width: 50px; height: 50px; line-height: 50px; font-size: 1.2rem;">
                                <i class="fas fa-bullseye"></i>
                            </div>
                            <h6 class="fw-bold mb-2">Visi</h6>
                            <p class="small text-muted mb-0"><?= get_setting('about_visi', 'Menjadi rental motor terbaik & terpercaya.') ?></p>
                        </div>
                    </div>
                    <div class="col-md-4" data-aos="fade-up" data-aos-delay="200">
                        <div class="premium-card text-center h-100 p-3">
                            <div class="term-icon mx-auto mb-3" style="width: 50px; height: 50px; line-height: 50px; font-size: 1.2rem;">
                                <i class="fas fa-tasks"></i>
                            </div>
                            <h6 class="fw-bold mb-2">Misi</h6>
                            <p class="small text-muted mb-0"><?= get_setting('about_misi', 'Pelayanan ramah & unit berkualitas.') ?></p>
                        </div>
                    </div>
                    <div class="col-md-4" data-aos="fade-up" data-aos-delay="300">
                        <div class="premium-card text-center h-100 p-3">
                            <div class="term-icon mx-auto mb-3" style="width: 50px; height: 50px; line-height: 50px; font-size: 1.2rem;">
                                <i class="fas fa-heart"></i>
                            </div>
                            <h6 class="fw-bold mb-2">Nilai</h6>
                            <p class="small text-muted mb-0"><?= get_setting('about_nilai', 'Jujur, Amanah, & Disiplin.') ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<div class="section-divider"></div>

<!-- 4. SYARAT SEWA (TERMS) -->
<section id="terms" class="bg-light-soft">
    <div class="container">
        <div class="text-center mb-5" data-aos="fade-up">
            <span class="section-tag"><?= get_setting('steps_tag', 'Mudah & Cepat') ?></span>
            <h2 class="section-title"><?= get_setting('steps_title', 'Syarat Sewa Motor') ?></h2>
            <p class="text-muted mw-600 mx-auto"><?= get_setting('steps_desc', 'Persyaratan sewa motor yang mudah dan tidak ribet untuk kenyamanan Anda.') ?></p>
        </div>

        <div class="row g-4 justify-content-center">
            <?php 
            $icons = ['id-card', 'motorcycle', 'hand-holding-usd'];
            for($i=1; $i<=3; $i++): 
                $step_icon = get_setting("step{$i}_icon", $icons[$i-1]);
            ?>
            <div class="col-md-4" data-aos="fade-up" data-aos-delay="<?= $i * 100 ?>">
                <div class="premium-card text-center h-100">
                    <div class="term-icon">
                        <i class="fas fa-<?= $step_icon ?>"></i>
                    </div>
                    <h5 class="fw-bold mb-3"><?= get_setting("step{$i}_title", "Langkah $i") ?></h5>
                    <p class="text-muted small mb-0"><?= get_setting("step{$i}_desc", "Deskripsi langkah $i.") ?></p>
                </div>
            </div>
            <?php endfor; ?>
        </div>
    </div>
</section>

<div class="section-divider"></div>

<!-- 5. FASILITAS (FACILITIES) -->
<section id="facilities" class="bg-white">
    <div class="container">
        <div class="text-center mb-5" data-aos="fade-up">
            <span class="section-tag"><?= get_setting('facilities_tag', 'Layanan Kami') ?></span>
            <h2 class="section-title"><?= get_setting('facilities_title', 'Fasilitas Terbaik') ?></h2>
            <p class="text-muted mw-600 mx-auto"><?= get_setting('facilities_desc', 'Nikmati berbagai fasilitas gratis yang kami berikan untuk setiap penyewaan.') ?></p>
        </div>

        <div class="row g-4">
            <?php 
            $default_icons = ['helmet-safety', 'cloud-rain', 'gas-pump', 'clock'];
            for($i=1; $i<=4; $i++): 
                $fac_icon = get_setting("fac{$i}_icon", $default_icons[$i-1]);
            ?>
            <div class="col-lg-3 col-sm-6" data-aos="fade-up" data-aos-delay="<?= $i * 100 ?>">
                <div class="facility-card">
                    <i class="fas fa-<?= $fac_icon ?> text-accent fs-1 mb-3"></i>
                    <h6 class="fw-bold mb-2"><?= get_setting("facilities_{$i}_title", "Fasilitas $i") ?></h6>
                    <p class="small text-muted mb-0"><?= get_setting("facilities_{$i}_desc", "Deskripsi fasilitas $i.") ?></p>
                </div>
            </div>
            <?php endfor; ?>
        </div>
    </div>
</section>

<div class="section-divider"></div>

<!-- TESTIMONIALS (Optional placement, user didn't specify but good to have) -->
<section id="testimonials" class="bg-light-soft overflow-hidden">
    <div class="container">
         <div class="text-center mb-5" data-aos="fade-up">
            <span class="section-tag"><?= get_setting('section_testi_tag', 'Testimoni') ?></span>
            <h2 class="section-title"><?= get_setting('section_testi_title', 'Apa Kata Mereka?') ?></h2>
        </div>
        
        <div class="swiper testimonialSwiper pb-5" data-aos="fade-up">
            <div class="swiper-wrapper">
                <?php foreach ($testimonials as $testi): ?>
                <div class="swiper-slide">
                    <div class="premium-card h-100 p-4 d-flex flex-column">
                        <div class="mb-3">
                             <h6 class="fw-bold mb-1 scheme-text-heading"><?= htmlspecialchars($testi['name']) ?></h6>
                             <div class="text-warning small">
                                 <?php for($k=0; $k<$testi['rating']; $k++): ?><i class="fas fa-star"></i><?php endfor; ?>
                             </div>
                        </div>
                        
                        <p class="text-muted fst-italic mb-3 small">"<?= htmlspecialchars($testi['message']) ?>"</p>

                        <?php if(!empty($testi['photo'])): ?>
                        <div class="mb-3 rounded-3 overflow-hidden border theme-border position-relative" style="aspect-ratio: 4/3;">
                            <img src="<?= BASE_URL ?>uploads/testimoni/<?= htmlspecialchars($testi['photo']) ?>" class="w-100 h-100 object-fit-cover" alt="Testimoni <?= htmlspecialchars($testi['name']) ?>" loading="lazy">
                        </div>
                        <?php endif; ?>

                        <div class="d-flex align-items-center justify-content-between mt-auto pt-3 border-top theme-border">
                            <small class="text-accent fw-bold"><i class="fas fa-motorcycle me-1"></i><?= htmlspecialchars($testi['motor_rented'] ?? 'Pelanggan Setia') ?></small>
                            <small class="text-muted"><?= date('d M Y', strtotime($testi['created_at'])) ?></small>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <div class="swiper-pagination"></div>
        </div>
    </div>
</section>

<div class="section-divider"></div>

<!-- 6. KONTAK (CONTACT) -->
<section id="contact" class="bg-white">
    <div class="container">
        <div class="row g-5">
            <div class="col-lg-5" data-aos="fade-right">
                <span class="section-tag"><?= get_setting('section_contact_tag', 'Hubungi Kami') ?></span>
                <h2 class="section-title mb-4"><?= get_setting('section_contact_title', 'Siap Membantu Liburan Anda') ?></h2>
                <p class="text-muted mb-5 lead small"><?= get_setting('section_contact_desc', 'Jangan ragu untuk menghubungi kami jika ada pertanyaan seputar sewa motor atau rekomendasi wisata.') ?></p>
                
                <div class="d-flex mb-4">
                    <div class="flex-shrink-0">
                        <div class="icon-circle">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h5 class="fw-bold mb-1">Alamat</h5>
                        <p class="text-muted mb-0"><?= get_setting('address', 'Jl. Contoh No. 123, Kota Wisata') ?></p>
                    </div>
                </div>
                
                <div class="d-flex mb-4">
                    <div class="flex-shrink-0">
                        <div class="icon-circle">
                            <i class="fab fa-whatsapp"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h5 class="fw-bold mb-1">WhatsApp</h5>
                        <p class="text-muted mb-0"><?= get_setting('whatsapp_number', '081234567890') ?></p>
                    </div>
                </div>

                <div class="d-flex">
                    <div class="flex-shrink-0">
                        <div class="icon-circle">
                            <i class="fas fa-envelope"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h5 class="fw-bold mb-1">Email</h5>
                        <p class="text-muted mb-0"><?= get_setting('email') ?></p>
                    </div>
                </div>
            </div>
            <div class="col-lg-7" data-aos="fade-left">
                <div class="ratio ratio-4x3 rounded-4 overflow-hidden shadow-sm border">
                    <iframe src="<?= get_setting('google_maps_url', 'https://www.google.com/maps/embed?pb=...') ?>" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Floating WhatsApp Button -->
<a href="https://wa.me/<?= get_setting('whatsapp_number', '081234567890') ?>?text=Halo,%20saya%20ingin%20bertanya%20tentang%20sewa%20motor" class="floating-whatsapp" target="_blank" rel="noopener noreferrer" aria-label="Chat via WhatsApp" title="Chat via WhatsApp">
    <i class="fab fa-whatsapp"></i>
</a>

<?php require_once 'includes/footer.php'; ?>
