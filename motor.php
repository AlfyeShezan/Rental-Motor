<?php
include 'includes/header.php';
include 'includes/navbar.php';

// Filtering logic
$search = $_GET['search'] ?? '';
$brand = $_GET['brand'] ?? '';
$type = $_GET['type'] ?? '';
$page = isset($_GET['page']) && $_GET['page'] > 0 ? (int)$_GET['page'] : 1;
$per_page = 6; // Motors per page
$offset = ($page - 1) * $per_page;

try {
    // Count total motor models for pagination
    $count_query = "SELECT COUNT(*) FROM motor_models mm WHERE is_active = 1";
    $count_params = [];

    if ($search) {
        $count_query .= " AND (brand LIKE ? OR model LIKE ?)";
        $count_params[] = "%$search%";
        $count_params[] = "%$search%";
    }
    if ($brand) {
        $count_query .= " AND brand = ?";
        $count_params[] = $brand;
    }
    if ($type) {
        $count_query .= " AND type = ?";
        $count_params[] = $type;
    }

    $count_stmt = $pdo->prepare($count_query);
    $count_stmt->execute($count_params);
    $total_motors = $count_stmt->fetchColumn();
    $total_pages = ceil($total_motors / $per_page);

    // Fetch motor models for current page with ready stock count
    $query = "SELECT mm.*, 
              (SELECT image_path FROM motor_images mi WHERE mi.model_id = mm.id ORDER BY is_primary DESC, id ASC LIMIT 1) as primary_image,
              (SELECT COUNT(*) FROM motors m WHERE m.model_id = mm.id AND m.status = 'Tersedia' AND m.is_active = 1 AND NOT EXISTS (SELECT 1 FROM bookings b WHERE b.motor_id = m.id AND b.status = 'Pending')) as tersedia_count
              FROM motor_models mm WHERE mm.is_active = 1";
    $params = [];

    if ($search) {
        $query .= " AND (brand LIKE ? OR model LIKE ?)";
        $params[] = "%$search%";
        $params[] = "%$search%";
    }
    if ($brand) {
        $query .= " AND brand = ?";
        $params[] = $brand;
    }
    if ($type) {
        $query .= " AND type = ?";
        $params[] = $type;
    }

    $query .= " LIMIT $per_page OFFSET $offset";

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $motors = $stmt->fetchAll();

    // Get unique brands and types for filters from motor_models
    $brands = $pdo->query("SELECT DISTINCT brand FROM motor_models WHERE is_active = 1")->fetchAll(PDO::FETCH_COLUMN);
    $types = $pdo->query("SELECT DISTINCT type FROM motor_models WHERE is_active = 1")->fetchAll(PDO::FETCH_COLUMN);

} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>

<div class="theme-bg-main pt-5 pb-5 border-bottom theme-border">
    <div class="container py-5 mt-4">
        <span class="section-tag">Katalog Lengkap</span>
        <h1 class="fw-bold display-4 mb-2 theme-text-heading">Temukan Armada <span class="text-accent">Impian</span> Anda</h1>
        <p class="theme-text-muted lead" style="max-width: 600px;">Pilih dari koleksi motor terbaik kami yang selalu siap menemani petualangan Anda di Yogyakarta.</p>
    </div>
</div>

<section class="py-5" style="margin-top: -80px;">
    <div class="container">
        <!-- Floating Glass Filter Bar -->
        <div class="premium-card glass-card p-4 mb-5 border-0 shadow-lg position-relative" style="z-index: 10;">
            <form action="" method="GET" class="row g-3 align-items-end">
                <div class="col-lg-4">
                    <label class="form-label small fw-bold text-uppercase opacity-50">Cari Motor</label>
                    <div class="input-group">
                        <span class="input-group-text theme-bg-main theme-border-end-0 theme-text-muted border-end-0"><i class="fas fa-search"></i></span>
                        <input type="text" name="search" class="form-control border-start-0 theme-bg-main theme-text-heading" placeholder="Ketik brand atau model..." value="<?= htmlspecialchars($search) ?>">
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-uppercase opacity-50">Pilih Merk</label>
                    <select name="brand" class="form-select">
                        <option value="">Semua Merk</option>
                        <?php foreach($brands as $b): ?>
                            <option value="<?= $b ?>" <?= $brand == $b ? 'selected' : '' ?>><?= $b ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-uppercase opacity-50">Tipe Kendaraan</label>
                    <select name="type" class="form-select">
                        <option value="">Semua Tipe</option>
                        <?php foreach($types as $t): ?>
                            <option value="<?= $t ?>" <?= $type == $t ? 'selected' : '' ?>><?= $t ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col">
                    <button type="submit" class="btn btn-accent w-100 py-2 fw-bold">
                        <i class="fas fa-filter me-2"></i> Cari
                    </button>
                </div>
            </form>
        </div>

        <!-- Skeleton Loader (Shown while loading) -->
        <div id="skeleton-loader" class="row g-4">
            <?php for($i=0; $i<8; $i++): ?>
            <div class="col-lg-3 col-md-6 col-12">
                <div class="motor-card p-0 border-0">
                    <div class="skeleton skeleton-img w-100 mb-3"></div>
                    <div class="p-3">
                        <div class="skeleton skeleton-title"></div>
                        <div class="skeleton skeleton-text" style="width: 40%"></div>
                        <div class="d-flex justify-content-between mt-4">
                            <div class="skeleton skeleton-text" style="width: 30%"></div>
                            <div class="skeleton skeleton-text" style="width: 20%"></div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endfor; ?>
        </div>

        <!-- Result Grid (Initially Hidden) -->
        <div id="actual-content" style="display: none;">
            <div class="row g-4">
                <?php if (empty($motors)): ?>
                    <div class="col-12 text-center py-5">
                        <div class="premium-card glass-card d-inline-block p-5 text-center">
                            <div class="card-icon mx-auto mb-4" style="width: 150px; height: 150px;">
                                <lottie-player src="https://assets10.lottiefiles.com/packages/lf20_ghp9m0iq.json" background="transparent" speed="1" style="width: 100%; height: 100%;" loop autoplay></lottie-player>
                            </div>
                            <h4 class="fw-bold theme-text-heading">Armada Belum Tersedia</h4>
                            <p class="theme-text-muted mb-4">Maaf, kami tidak menemukan motor yang sesuai dengan kriteria Anda saat ini.</p>
                            <a href="motor.php" class="btn btn-accent px-5 py-2 rounded-pill fw-bold">Lihat Semua Armada</a>
                        </div>
                    </div>
                <?php else: ?>
                    <?php foreach ($motors as $motor): ?>
                        <div class="col-lg-3 col-md-6 col-12" data-aos="fade-up">
                            <div class="motor-card">
                                <div class="motor-img-wrapper">
                                    <?php if ($motor['tersedia_count'] <= 0): ?>
                                        <span class="motor-badge" style="background: var(--primary); color: white;">Habis</span>
                                    <?php endif; ?>
                                    
                                    <img src="<?= BASE_URL ?>uploads/motors/<?= $motor['primary_image'] ?: 'default.jpg' ?>" alt="<?= $motor['brand'] ?>" loading="lazy">
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

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
                <nav aria-label="Motor pagination" class="mt-5">
                    <ul class="pagination justify-content-center">
                        <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                            <a class="page-link rounded-pill me-2" href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>&brand=<?= urlencode($brand) ?>&type=<?= urlencode($type) ?>" aria-label="Previous">
                                <i class="fas fa-chevron-left"></i>
                            </a>
                        </li>

                        <?php 
                        $start = max(1, $page - 2);
                        $end = min($total_pages, $page + 2);
                        for ($i = $start; $i <= $end; $i++): 
                        ?>
                            <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                <a class="page-link rounded-pill mx-1" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&brand=<?= urlencode($brand) ?>&type=<?= urlencode($type) ?>"><?= $i ?></a>
                            </li>
                        <?php endfor; ?>

                        <li class="page-item <?= $page >= $total_pages ? 'disabled' : '' ?>">
                            <a class="page-link rounded-pill ms-2" href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>&brand=<?= urlencode($brand) ?>&type=<?= urlencode($type) ?>" aria-label="Next">
                                <i class="fas fa-chevron-right"></i>
                            </a>
                        </li>
                    </ul>
                </nav>
            <?php endif; ?>
        </div>
    </div>
</section>

<script>
    window.addEventListener('load', () => {
        setTimeout(() => {
            document.getElementById('skeleton-loader').style.display = 'none';
            document.getElementById('actual-content').style.display = 'block';
            AOS.refresh();
        }, 500); // Small delay for that modern feel
    });
</script>

<?php include 'includes/footer.php'; ?>
