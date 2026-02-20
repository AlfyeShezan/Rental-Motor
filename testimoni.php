<?php
include 'includes/header.php';
include 'includes/navbar.php';

try {
    $stmt = $pdo->query("SELECT * FROM testimonials WHERE is_displayed = 1 ORDER BY created_at DESC");
    $testimonials = $stmt->fetchAll();
} catch (PDOException $e) {
    $testimonials = [];
}
?>

<div class="theme-bg-main pt-5 pb-5 border-bottom theme-border">
    <div class="container py-5 mt-4 text-center">
        <span class="section-tag">Testimoni Pelanggan</span>
        <h1 class="fw-bold display-4 mb-2 theme-text-heading">Apa Kata <span class="text-accent">Dunia</span> Tentang Kami?</h1>
        <p class="theme-text-muted lead mx-auto" style="max-width: 600px;">Ribuan perjalanan telah dimulai bersama kami. Inilah cerita jujur dari mereka yang telah menjelajahi Yogyakarta bersama <?= SITE_NAME ?>.</p>
    </div>
</div>

<section class="py-5">
    <div class="container">
        <!-- Social Proof Stats -->
        <div class="row g-4 mb-5">
            <div class="col-md-4">
                <div class="premium-card theme-bg-card text-center p-4">
                    <h2 class="fw-bold text-accent mb-1">4.9/5</h2>
                    <p class="theme-text-muted small mb-0 fw-bold text-uppercase">Google Rating</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="premium-card theme-bg-card text-center p-4">
                    <h2 class="fw-bold text-accent mb-1">100%</h2>
                    <p class="theme-text-muted small mb-0 fw-bold text-uppercase">Real Feedback</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="premium-card theme-bg-card text-center p-4">
                    <h2 class="fw-bold text-accent mb-1">2000+</h2>
                    <p class="theme-text-muted small mb-0 fw-bold text-uppercase">Happy Travelers</p>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <?php if (empty($testimonials)): ?>
                <div class="col-12 text-center py-5">
                    <div class="premium-card theme-bg-card d-inline-block p-5 text-center">
                        <i class="fas fa-comment-slash fa-3x theme-text-muted mb-3"></i>
                        <p class="theme-text-muted mb-0">Belum ada testimoni tersedia.</p>
                    </div>
                </div>
            <?php else: ?>
                <?php foreach ($testimonials as $testi): ?>
                    <div class="col-lg-4 col-md-6">
                        <div class="premium-card theme-bg-card d-flex flex-column h-100 shadow-sm border-0 overflow-hidden">
                            <?php 
                            $local_path = __DIR__ . '/uploads/testimoni/' . $testi['photo'];
                            if ($testi['photo'] && file_exists($local_path)): 
                            ?>
                                <div class="testi-proof-img" style="aspect-ratio: 4/3; overflow: hidden;">
                                    <img src="<?= BASE_URL ?>uploads/testimoni/<?= $testi['photo'] ?>" class="w-100 h-100" style="object-fit: cover;">
                                </div>
                            <?php endif; ?>
                            
                            <div class="p-4 flex-grow-1">
                                <div class="text-accent mb-3">
                                    <?php for($i=1; $i<=5; $i++): ?>
                                        <i class="<?= $i <= $testi['rating'] ? 'fas' : 'far' ?> fa-star"></i>
                                    <?php endfor; ?>
                                </div>
                                <p class="fst-italic theme-text-body opacity-75 leading-relaxed mb-0">"<?= htmlspecialchars($testi['message']) ?>"</p>
                            </div>

                            <div class="d-flex align-items-center gap-3 p-4 pt-0">
                                <div class="theme-bg-alt theme-text-primary rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width: 40px; height: 40px; border: 1px solid var(--accent); font-size: 0.85rem;">
                                    <?= strtoupper(substr($testi['name'], 0, 1)) ?>
                                </div>
                                <div>
                                    <h6 class="fw-bold mb-0 theme-text-heading" style="font-size: 0.95rem;"><?= htmlspecialchars($testi['name']) ?></h6>
                                    <div class="d-flex align-items-center gap-1 text-success small" style="font-size: 0.75rem;">
                                        <i class="fas fa-check-circle"></i>
                                        <span>Verified Customer</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Share Experience CTA -->
        <div class="mt-5 pt-5 text-center">
            <div class="premium-card theme-bg-alt p-5 rounded-5 border-0 shadow-lg position-relative overflow-hidden">
                <div class="bg-accent rounded-circle position-absolute top-100 start-50 translate-middle" style="width: 300px; height: 300px; filter: blur(100px); opacity: 0.1;"></div>
                <h2 class="fw-bold mb-3 theme-text-heading">Punya Cerita Menarik?</h2>
                <p class="theme-text-muted mb-4">Bagikan pengalaman Anda menyewa di <?= SITE_NAME ?> dan bantu traveler lain menemukan armada terbaik.</p>
                <a href="https://wa.me/<?= get_setting('whatsapp_number') ?>?text=Halo%20<?= urlencode(SITE_NAME) ?>,%20saya%20ingin%20memberikan%20testimoni" target="_blank" class="btn btn-accent btn-lg px-5 py-3 rounded-pill fw-bold">
                    <i class="fab fa-whatsapp me-2"></i> Kirim Testimoni via WA
                </a>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
