<nav class="navbar navbar-expand-lg fixed-top">
    <div class="container">
        <a class="navbar-brand" href="<?= BASE_URL ?>">
            <?php 
            $logo = get_setting('site_logo');
            if ($logo): 
                $logo_path = __DIR__ . '/../uploads/settings/' . $logo;
                $ext = strtolower(pathinfo($logo, PATHINFO_EXTENSION));
                if ($ext === 'svg' && file_exists($logo_path)):
                    // Inline SVG for maximum compatibility
                    $svg_content = file_get_contents($logo_path);
                    $svg_content = preg_replace('/<\?xml.*\?>/i', '', $svg_content);
                    echo '<div class="d-inline-block align-top navbar-logo-container" style="height: 40px; width: auto;">' . $svg_content . '</div>';
                    echo '<style>.navbar-logo-container svg { height: 100%; width: auto; display: block; }</style>';
                else: ?>
                    <img src="<?= BASE_URL ?>uploads/settings/<?= $logo ?>" alt="<?= SITE_NAME ?>" height="40" class="d-inline-block align-top">
                <?php endif; ?>
            <?php else: ?>
                <span class="text-accent"><?= substr(SITE_NAME, 0, 2) ?></span> <?= substr(SITE_NAME, 2) ?>
            <?php endif; ?>
        </a>
        <button class="navbar-toggler border-0 shadow-none p-2" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <div class="menu-icon">
                <span></span>
                <span></span>
                <span></span>
            </div>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <div class="navbar-nav-container ms-auto">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="<?= BASE_URL ?>#home">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= BASE_URL ?>#motor">Motor</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= BASE_URL ?>#tentang">Tentang</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= BASE_URL ?>#terms">Syarat</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= BASE_URL ?>#facilities">Fasilitas</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= BASE_URL ?>#contact">Kontak</a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</nav>

<script>
document.addEventListener('scroll', () => {
    const nav = document.querySelector('.navbar');
    if (window.scrollY > 50) {
        nav.classList.add('scrolled');
    } else {
        nav.classList.remove('scrolled');
    }
});

// Auto-close mobile menu on link click
document.addEventListener('DOMContentLoaded', () => {
    const navLinks = document.querySelectorAll('.navbar-nav .nav-link');
    const menuToggle = document.getElementById('navbarNav');
    const bsCollapse = new bootstrap.Collapse(menuToggle, {toggle:false});
    
    navLinks.forEach((l) => {
        l.addEventListener('click', () => { 
            if (window.innerWidth < 992 && menuToggle.classList.contains('show')) {
                bsCollapse.hide();
            }
        });
    });
});
</script>
