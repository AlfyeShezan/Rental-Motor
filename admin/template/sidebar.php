<nav id="sidebar">
    <div class="sidebar-header">
        <a href="<?= BASE_URL ?>admin/index.php" class="sidebar-logo">
            <?php 
            $logo = get_setting('site_logo');
            if ($logo): 
                $logo_path = __DIR__ . '/../../uploads/settings/' . $logo;
                $ext = strtolower(pathinfo($logo, PATHINFO_EXTENSION));
                if ($ext === 'svg' && file_exists($logo_path)):
                    // Inline SVG for maximum compatibility and CSS control
                    $svg_content = file_get_contents($logo_path);
                    // Remove XML declaration if present
                    $svg_content = preg_replace('/<\?xml.*\?>/i', '', $svg_content);
                    echo '<div class="mb-2 logo-container" style="height: 45px; width: auto; display: inline-block;">' . $svg_content . '</div>';
                    echo '<style>.logo-container svg { height: 100%; width: auto; display: block; }</style>';
                else: ?>
                    <img src="<?= BASE_URL ?>uploads/settings/<?= $logo ?>" alt="Logo" class="img-fluid mb-2 d-block" style="max-height: 45px; width: auto; object-fit: contain;">
                <?php endif; ?>
            <?php else: ?>
                <span class="text-accent"><?= substr(SITE_NAME, 0, 2) ?></span> <?= substr(SITE_NAME, 2) ?>
            <?php endif; ?>
            <div class="small text-muted fw-normal mt-1" style="font-size: 0.65rem; letter-spacing: 2px; font-weight: 600 !important;">ADMIN SUITE</div>
        </a>
    </div>

    <ul class="list-unstyled components">
        <li class="<?= basename($_SERVER['PHP_SELF']) == 'index.php' && strpos($_SERVER['REQUEST_URI'], 'admin/index.php') !== false ? 'active' : '' ?>">
            <a href="<?= BASE_URL ?>admin/index.php">
                <i class="fas fa-chart-pie"></i>
                <span>Dashboard</span>
            </a>
        </li>
        
        <li class="small text-uppercase text-muted fw-bold mt-4 mb-2 ms-4" style="font-size: 0.65rem; letter-spacing: 1px; opacity: 0.8;">Manajemen Armada</li>
        <li class="<?= strpos($_SERVER['REQUEST_URI'], 'admin/motor/') !== false ? 'active' : '' ?>">
            <a href="<?= BASE_URL ?>admin/motor/index.php">
                <i class="fas fa-motorcycle"></i>
                <span>Katalog Motor</span>
            </a>
        </li>
        <li class="<?= strpos($_SERVER['REQUEST_URI'], 'admin/promo/') !== false ? 'active' : '' ?>">
            <a href="<?= BASE_URL ?>admin/promo/index.php">
                <i class="fas fa-ticket-alt"></i>
                <span>Promo & Diskon</span>
            </a>
        </li>

        <li class="small text-uppercase text-muted fw-bold mt-4 mb-2 ms-4" style="font-size: 0.65rem; letter-spacing: 1px; opacity: 0.8;">Operasional</li>
        <li class="<?= strpos($_SERVER['REQUEST_URI'], 'admin/booking/') !== false ? 'active' : '' ?>">
            <a href="<?= BASE_URL ?>admin/booking/index.php">
                <i class="fas fa-calendar-check"></i>
                <span>Data Booking</span>
            </a>
        </li>
        <li class="<?= strpos($_SERVER['REQUEST_URI'], 'admin/laporan/') !== false ? 'active' : '' ?>">
            <a href="<?= BASE_URL ?>admin/laporan/index.php">
                <i class="fas fa-file-invoice-dollar"></i>
                <span>Laporan Keuangan</span>
            </a>
        </li>

        <li class="small text-uppercase text-muted fw-bold mt-4 mb-2 ms-4" style="font-size: 0.65rem; letter-spacing: 1px; opacity: 0.8;">Interaksi</li>
        <li class="<?= strpos($_SERVER['REQUEST_URI'], 'admin/testimoni/') !== false ? 'active' : '' ?>">
            <a href="<?= BASE_URL ?>admin/testimoni/index.php">
                <i class="fas fa-star"></i>
                <span>Testimoni</span>
            </a>
        </li>

        <li class="small text-uppercase text-muted fw-bold mt-4 mb-2 ms-4" style="font-size: 0.65rem; letter-spacing: 1px; opacity: 0.8;">Sistem</li>
        <li class="<?= strpos($_SERVER['REQUEST_URI'], 'admin/admin_user/') !== false ? 'active' : '' ?>">
            <a href="<?= BASE_URL ?>admin/admin_user/index.php">
                <i class="fas fa-user-shield"></i>
                <span>Kelola Admin</span>
            </a>
        </li>
        <li class="<?= strpos($_SERVER['REQUEST_URI'], 'admin/pengaturan/') !== false && basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : '' ?>">
            <a href="<?= BASE_URL ?>admin/pengaturan/index.php">
                <i class="fas fa-cog"></i>
                <span>Settings</span>
            </a>
        </li>
        <li class="<?= strpos($_SERVER['REQUEST_URI'], 'admin/pengaturan/backup.php') !== false ? 'active' : '' ?>">
            <a href="<?= BASE_URL ?>admin/pengaturan/backup.php">
                <i class="fas fa-database"></i>
                <span>Database Backup</span>
            </a>
        </li>
        <li class="mt-4">
            <a href="<?= BASE_URL ?>admin/logout.php" class="text-danger fw-bold">
                <i class="fas fa-sign-out-alt"></i>
                <span>Logout</span>
            </a>
        </li>
    </ul>

</nav>
