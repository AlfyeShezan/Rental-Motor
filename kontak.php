<?php
include 'includes/header.php';
include 'includes/navbar.php';
?>

<div class="theme-bg-main pt-5 pb-5 border-bottom theme-border">
    <div class="container py-5 mt-4 text-center">
        <span class="section-tag">Hubungi Kami</span>
        <h1 class="fw-bold display-4 mb-2 theme-text-heading">Butuh Bantuan <span class="text-accent">Dukungan?</span></h1>
        <p class="theme-text-muted lead mx-auto" style="max-width: 600px;">Kami siap melayani Anda 24/7. Hubungi tim support kami lewat saluran manapun yang paling nyaman bagi Anda.</p>
    </div>
</div>

<section class="py-5">
    <div class="container">
        <div class="row g-4 mb-5">
            <div class="col-lg-4">
                <div class="premium-card text-center p-5 shadow-sm theme-bg-card">
                    <div class="card-icon mx-auto theme-bg-alt theme-text-primary mb-4" style="width: 80px; height: 80px; font-size: 2rem;">
                        <i class="fab fa-whatsapp"></i>
                    </div>
                    <h5 class="fw-bold theme-text-heading">Respon Cepat</h5>
                    <p class="theme-text-muted small mb-4">Chat admin kami via WhatsApp untuk booking instan dan tanya jawab armada.</p>
                    <a href="https://wa.me/<?= get_setting('whatsapp_number') ?>" target="_blank" class="btn btn-accent rounded-pill px-4">Chat Sekarang</a>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="premium-card text-center p-5 shadow-sm theme-bg-card">
                    <div class="card-icon mx-auto theme-bg-alt theme-text-primary mb-4" style="width: 80px; height: 80px; font-size: 2rem;">
                        <i class="fas fa-envelope-open-text"></i>
                    </div>
                    <h5 class="fw-bold theme-text-heading">Email Bisnis</h5>
                    <p class="theme-text-muted small mb-4">Untuk kerjasama korporasi, event, atau dukungan jangka panjang, kirimkan email Anda.</p>
                    <a href="mailto:<?= get_setting('email') ?>" class="btn theme-bg-light-soft theme-text-heading border theme-border rounded-pill px-4"><?= get_setting('email') ?></a>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="premium-card text-center p-5 shadow-sm theme-bg-card">
                    <div class="card-icon mx-auto theme-bg-alt theme-text-primary mb-4" style="width: 80px; height: 80px; font-size: 2rem;">
                        <i class="fas fa-location-arrow"></i>
                    </div>
                    <h5 class="fw-bold theme-text-heading">Lokasi Kantor</h5>
                    <p class="theme-text-muted small mb-4">Kunjungi kantor kami untuk serah terima unit secara langsung atau sekedar konsultasi rute.</p>
                    <p class="fw-bold small mb-0 theme-text-primary"><?= get_setting('address') ?></p>
                </div>
            </div>
        </div>

        <!-- Google Map Section -->
        <div class="premium-card overflow-hidden p-0 border-0 shadow-lg" style="height: 450px; border-radius: 30px;">
            <iframe 
                src="<?= get_setting('google_maps_url', 'https://www.google.com/maps/embed?pb=...') ?>" 
                width="100%" height="100%" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade">
            </iframe>
        </div>
        
        <div class="text-center mt-5">
            <p class="theme-text-muted small">Jam Operasional Kantor: <span class="fw-bold theme-text-primary"><?= get_setting('operational_hours') ?></span></p>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
