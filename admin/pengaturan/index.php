<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../template/auth.php';
require_once __DIR__ . '/../../config/helper.php';

// Fetch all settings from DB
try {
    $stmt = $pdo->query("SELECT setting_key, setting_value FROM settings");
    $settings_raw = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
} catch (PDOException $e) {
    die("Error fetching settings: " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Verify CSRF Token
    verify_csrf_token($_POST['csrf_token'] ?? '');

    try {
        $pdo->beginTransaction();
        
        if (isset($_POST['settings'])) {
            foreach ($_POST['settings'] as $key => $value) {
                // Handle Base64 encoded Maps URL (Sanitize)
                if ($key === 'google_maps_url_encoded' && !empty($value)) {
                    $decoded_value = base64_decode($value);
                    $stmt = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES ('google_maps_url', ?) ON DUPLICATE KEY UPDATE setting_value = ?");
                    $stmt->execute([$decoded_value, $decoded_value]);
                    continue;
                }
                
                if ($key === 'google_maps_url_encoded') continue;

                // Anti-XSS: Use Prepared Statements (Implicitly handles injection)
                $stmt = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?");
                $stmt->execute([$key, $value, $value]);
            }
        }

        // Handle File Uploads (Secure)
        $files_to_handle = [
            'hero_background' => 'hero_bg_',
            'site_logo' => 'logo_',
            'about_image' => 'about_'
        ];

        foreach ($files_to_handle as $post_key => $prefix) {
            if (!empty($_FILES[$post_key]['name'])) {
                // Secure Validation (MIME + Ext + Size)
                $check = validate_image_upload($post_key);
                if ($check !== true) {
                    $pdo->rollBack();
                    redirect_with_alert('index.php', ($check['error'] ?? 'Gagal upload file.'));
                    exit;
                }

                $file = $_FILES[$post_key];
                $upload_dir = __DIR__ . '/../../uploads/settings/';
                if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
                
                $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                $filename = $prefix . time() . '.' . $ext;
                $target = $upload_dir . $filename;
                
                if (move_uploaded_file($file['tmp_name'], $target)) {
                    $stmt = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?");
                    $stmt->execute([$post_key, $filename, $filename]);
                }
            }
        }
        
        $pdo->commit();
        require_once __DIR__ . '/../../config/backup_helper.php';
        trigger_auto_backup($pdo);
        redirect_with_alert('index.php', 'Pengaturan website berhasil diperbarui secara aman.');
    } catch (PDOException $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        $_SESSION['alert_message'] = "Gagal memperbarui: " . $e->getMessage();
        $_SESSION['alert_type'] = "danger";
    }
}

include __DIR__ . '/../template/header.php';
include __DIR__ . '/../template/sidebar.php';
?>

<div id="content">
    <nav class="navbar top-navbar">
        <div class="container-fluid">
            <button type="button" id="sidebarCollapse" class="btn theme-bg-card theme-text-heading rounded-circle shadow-sm me-3 border-0 d-lg-none">
                <i class="fas fa-bars theme-text-heading"></i>
            </button>
            <h5 class="mb-0 fw-bold ms-2 theme-text-heading">Konfigurasi Sistem</h5>
        </div>
    </nav>

    <div class="main-content">
        <div class="row align-items-center mb-4">
            <div class="col">
                <h4 class="fw-bold mb-0">Pengaturan Website</h4>
                <p class="text-muted small mb-0">Sesuaikan seluruh konten dan parameter operasional situs Anda.</p>
            </div>
        </div>

        <?php display_alert(); ?>

        <form action="" method="POST" enctype="multipart/form-data">
            <!-- CSRF Protection Token -->
            <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
            <div class="card border-0 shadow-sm rounded-4 theme-bg-card overflow-hidden">
                <div class="card-header bg-transparent border-0 p-0">
                    <ul class="nav nav-tabs admin-tabs border-bottom theme-border px-3 pt-3" id="settingsTabs" role="tablist">
                        <li class="nav-item">
                            <button class="nav-link active fw-bold small text-uppercase py-3 px-4" id="general-tab" data-bs-toggle="tab" data-bs-target="#general" type="button">Identitas</button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link fw-bold small text-uppercase py-3 px-4" id="content-tab" data-bs-toggle="tab" data-bs-target="#content-sec" type="button">Konten Home</button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link fw-bold small text-uppercase py-3 px-4" id="about-tab" data-bs-toggle="tab" data-bs-target="#about-sec" type="button">Tentang Kami</button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link fw-bold small text-uppercase py-3 px-4" id="terms-tab" data-bs-toggle="tab" data-bs-target="#terms-sec" type="button">Syarat & Fasilitas</button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link fw-bold small text-uppercase py-3 px-4" id="social-tab" data-bs-toggle="tab" data-bs-target="#social-sec" type="button">Media Sosial</button>
                        </li>
                    </ul>
                </div>
                
                <div class="card-body p-4">
                    <div class="tab-content" id="settingsTabsContent">
                        <!-- Tab 1: General Identity -->
                        <div class="tab-pane fade show active" id="general">
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-uppercase theme-text-muted">Nama Website</label>
                                    <input type="text" name="settings[site_name]" class="form-control border theme-border theme-bg-light-soft theme-text-heading p-2 px-3 rounded-3" value="<?= htmlspecialchars($settings_raw['site_name'] ?? '') ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-uppercase theme-text-muted">WhatsApp Admin</label>
                                    <input type="text" name="settings[whatsapp_number]" class="form-control border theme-border theme-bg-light-soft theme-text-heading p-2 px-3 rounded-3" value="<?= htmlspecialchars($settings_raw['whatsapp_number'] ?? '') ?>" required>
                                </div>
                                <div class="col-12">
                                    <label class="form-label small fw-bold text-uppercase theme-text-muted">Alamat Kantor</label>
                                    <textarea name="settings[address]" class="form-control border theme-border theme-bg-light-soft theme-text-heading p-3 rounded-3" rows="2"><?= htmlspecialchars($settings_raw['address'] ?? '') ?></textarea>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-uppercase theme-text-muted">Email Support</label>
                                    <input type="email" name="settings[email]" class="form-control border theme-border theme-bg-light-soft theme-text-heading p-2 px-3 rounded-3" value="<?= htmlspecialchars($settings_raw['email'] ?? '') ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-uppercase theme-text-muted">Jam Operasional</label>
                                    <input type="text" name="settings[operational_hours]" class="form-control border theme-border theme-bg-light-soft theme-text-heading p-2 px-3 rounded-3" value="<?= htmlspecialchars($settings_raw['operational_hours'] ?? '') ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-uppercase theme-text-muted">Logo Rental</label>
                                    <input type="file" name="site_logo" class="form-control border theme-border theme-bg-light-soft p-2 rounded-3">
                                </div>
                                <div class="col-12">
                                    <label class="form-label small fw-bold text-uppercase theme-text-muted">Deskripsi Footer (Singkat)</label>
                                    <textarea name="settings[footer_description]" class="form-control border theme-border theme-bg-light-soft theme-text-heading p-3 rounded-3" rows="2"><?= htmlspecialchars($settings_raw['footer_description'] ?? '') ?></textarea>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small fw-bold text-uppercase theme-text-muted">Kota (Copyright)</label>
                                    <input type="text" name="settings[footer_copyright_loc]" class="form-control border theme-border theme-bg-light-soft theme-text-heading p-2 px-3 rounded-3" value="<?= htmlspecialchars($settings_raw['footer_copyright_loc'] ?? 'Yogyakarta') ?>">
                                </div>
                                <div class="col-md-8">
                                    <label class="form-label small fw-bold text-uppercase theme-text-muted">Designer Text (Credits)</label>
                                    <input type="text" name="settings[footer_designer_text]" class="form-control border theme-border theme-bg-light-soft theme-text-heading p-2 px-3 rounded-3" value="<?= htmlspecialchars($settings_raw['footer_designer_text'] ?? 'Designed with Special for You') ?>">
                                </div>
                                <div class="col-12 mt-4">
                                    <h6 class="fw-bold mb-3 theme-text-heading text-accent">Kontak Section (Homepage)</h6>
                                    <div class="row g-3">
                                        <div class="col-md-4">
                                            <label class="form-label small fw-bold theme-text-muted">Contact Tag</label>
                                            <input type="text" name="settings[section_contact_tag]" class="form-control border theme-border theme-bg-light-soft" value="<?= htmlspecialchars($settings_raw['section_contact_tag'] ?? 'Hubungi Kami') ?>">
                                        </div>
                                        <div class="col-md-8">
                                            <label class="form-label small fw-bold theme-text-muted">Contact Title</label>
                                            <input type="text" name="settings[section_contact_title]" class="form-control border theme-border theme-bg-light-soft" value="<?= htmlspecialchars($settings_raw['section_contact_title'] ?? 'Siap Membantu Liburan Anda') ?>">
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label small fw-bold theme-text-muted">Contact Description</label>
                                            <textarea name="settings[section_contact_desc]" class="form-control border theme-border theme-bg-light-soft" rows="2"><?= htmlspecialchars($settings_raw['section_contact_desc'] ?? 'Jangan ragu untuk menghubungi kami jika ada pertanyaan seputar sewa motor atau rekomendasi wisata.') ?></textarea>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12 mt-4">
                                    <h6 class="fw-bold mb-3 theme-text-heading text-accent">Footer Column Titles</h6>
                                    <div class="row g-3">
                                        <div class="col-md-4">
                                            <label class="form-label small fw-bold theme-text-muted">Col 1 (Explore)</label>
                                            <input type="text" name="settings[footer_explore_title]" class="form-control border theme-border theme-bg-light-soft" value="<?= htmlspecialchars($settings_raw['footer_explore_title'] ?? 'Jelajahi') ?>">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label small fw-bold theme-text-muted">Col 2 (Services)</label>
                                            <input type="text" name="settings[footer_services_title]" class="form-control border theme-border theme-bg-light-soft" value="<?= htmlspecialchars($settings_raw['footer_services_title'] ?? 'Layanan') ?>">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label small fw-bold theme-text-muted">Col 3 (Contact)</label>
                                            <input type="text" name="settings[footer_contact_title]" class="form-control border theme-border theme-bg-light-soft" value="<?= htmlspecialchars($settings_raw['footer_contact_title'] ?? 'Kontak Support') ?>">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <label class="form-label small fw-bold text-uppercase theme-text-muted">Google Maps Embed URL</label>
                                    <textarea id="maps_display_input" class="form-control border theme-border theme-bg-light-soft theme-text-heading p-3 rounded-3" rows="2"><?= htmlspecialchars($settings_raw['google_maps_url'] ?? '') ?></textarea>
                                    <input type="hidden" name="settings[google_maps_url_encoded]" id="maps_hidden_input">
                                </div>
                            </div>
                        </div>

                        <!-- Tab 2: Home Sections -->
                        <div class="tab-pane fade" id="content-sec">
                            <h6 class="fw-bold mb-3 theme-text-heading text-accent">Hero Section</h6>
                            <div class="row g-3 mb-4">
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold theme-text-muted">Hero Tagline</label>
                                    <input type="text" name="settings[hero_tag]" class="form-control border theme-border theme-bg-light-soft theme-text-heading p-2 px-3 rounded-3" value="<?= htmlspecialchars($settings_raw['hero_tag'] ?? '') ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold theme-text-muted">Hero Background</label>
                                    <input type="file" name="hero_background" class="form-control border theme-border theme-bg-light-soft p-2 rounded-3">
                                </div>
                                <div class="col-12">
                                    <label class="form-label small fw-bold theme-text-muted">Hero Title Utama</label>
                                    <input type="text" name="settings[hero_title]" class="form-control border theme-border theme-bg-light-soft theme-text-heading p-2 px-3 rounded-3" value="<?= htmlspecialchars($settings_raw['hero_title'] ?? '') ?>">
                                </div>
                                <div class="col-12">
                                    <label class="form-label small fw-bold theme-text-muted">Hero Subtitle</label>
                                    <textarea name="settings[hero_subtitle]" class="form-control border theme-border theme-bg-light-soft theme-text-heading p-3 rounded-3" rows="2"><?= htmlspecialchars($settings_raw['hero_subtitle'] ?? '') ?></textarea>
                                </div>
                            </div>

                            <hr class="my-4 theme-border opacity-10">

                            <h6 class="fw-bold mb-3 theme-text-heading text-accent">Armada Section (Homepage)</h6>
                            <div class="row g-3 mb-4">
                                <div class="col-md-4">
                                    <label class="form-label small fw-bold theme-text-muted">Armada Tag</label>
                                    <input type="text" name="settings[section_motor_tag]" class="form-control border theme-border theme-bg-light-soft" value="<?= htmlspecialchars($settings_raw['section_motor_tag'] ?? 'Armada Kami') ?>">
                                </div>
                                <div class="col-md-8">
                                    <label class="form-label small fw-bold theme-text-muted">Armada Title</label>
                                    <input type="text" name="settings[section_motor_title]" class="form-control border theme-border theme-bg-light-soft" value="<?= htmlspecialchars($settings_raw['section_motor_title'] ?? 'Pilihan Motor Terbaru') ?>">
                                </div>
                                <div class="col-12">
                                    <label class="form-label small fw-bold theme-text-muted">Armada Description</label>
                                    <textarea name="settings[section_motor_desc]" class="form-control border theme-border theme-bg-light-soft" rows="2"><?= htmlspecialchars($settings_raw['section_motor_desc'] ?? 'Unit motor selalu dalam kondisi prima, servis rutin, dan surat-surat lengkap untuk kenyamanan berkendara Anda.') ?></textarea>
                                </div>
                            </div>

                            <hr class="my-4 theme-border opacity-10">

                            <h6 class="fw-bold mb-3 theme-text-heading text-accent">Testimoni Section (Homepage)</h6>
                            <div class="row g-3 mb-4">
                                <div class="col-md-4">
                                    <label class="form-label small fw-bold theme-text-muted">Testimoni Tag</label>
                                    <input type="text" name="settings[section_testi_tag]" class="form-control border theme-border theme-bg-light-soft" value="<?= htmlspecialchars($settings_raw['section_testi_tag'] ?? 'Testimoni') ?>">
                                </div>
                                <div class="col-md-8">
                                    <label class="form-label small fw-bold theme-text-muted">Testimoni Title</label>
                                    <input type="text" name="settings[section_testi_title]" class="form-control border theme-border theme-bg-light-soft" value="<?= htmlspecialchars($settings_raw['section_testi_title'] ?? 'Apa Kata Mereka?') ?>">
                                </div>
                            </div>

                            <hr class="my-4 theme-border opacity-10">

                            <h6 class="fw-bold mb-3 theme-text-heading text-accent">Cara Sewa (3 Steps)</h6>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold">Section Tag</label>
                                    <input type="text" name="settings[steps_tag]" class="form-control border theme-border theme-bg-light-soft" value="<?= htmlspecialchars($settings_raw['steps_tag'] ?? '') ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold">Section Title</label>
                                    <input type="text" name="settings[steps_title]" class="form-control border theme-border theme-bg-light-soft" value="<?= htmlspecialchars($settings_raw['steps_title'] ?? '') ?>">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small fw-bold">Step 1</label>
                                    <input type="text" name="settings[step1_title]" class="form-control border theme-border theme-bg-light-soft mb-2" value="<?= htmlspecialchars($settings_raw['step1_title'] ?? '') ?>">
                                    <input type="text" name="settings[step1_icon]" class="form-control border theme-border theme-bg-light-soft mb-2 small" placeholder="Ikon (FontAwesome e.g: id-card)" value="<?= htmlspecialchars($settings_raw['step1_icon'] ?? 'id-card') ?>">
                                    <textarea name="settings[step1_desc]" class="form-control border theme-border theme-bg-light-soft small" rows="2"><?= htmlspecialchars($settings_raw['step1_desc'] ?? '') ?></textarea>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small fw-bold">Step 2</label>
                                    <input type="text" name="settings[step2_title]" class="form-control border theme-border theme-bg-light-soft mb-2" value="<?= htmlspecialchars($settings_raw['step2_title'] ?? '') ?>">
                                    <input type="text" name="settings[step2_icon]" class="form-control border theme-border theme-bg-light-soft mb-2 small" placeholder="Ikon (e.g: motorcycle)" value="<?= htmlspecialchars($settings_raw['step2_icon'] ?? 'motorcycle') ?>">
                                    <textarea name="settings[step2_desc]" class="form-control border theme-border theme-bg-light-soft small" rows="2"><?= htmlspecialchars($settings_raw['step2_desc'] ?? '') ?></textarea>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small fw-bold">Step 3</label>
                                    <input type="text" name="settings[step3_title]" class="form-control border theme-border theme-bg-light-soft mb-2" value="<?= htmlspecialchars($settings_raw['step3_title'] ?? '') ?>">
                                    <input type="text" name="settings[step3_icon]" class="form-control border theme-border theme-bg-light-soft mb-2 small" placeholder="Ikon (e.g: hand-holding-usd)" value="<?= htmlspecialchars($settings_raw['step3_icon'] ?? 'hand-holding-usd') ?>">
                                    <textarea name="settings[step3_desc]" class="form-control border theme-border theme-bg-light-soft small" rows="2"><?= htmlspecialchars($settings_raw['step3_desc'] ?? '') ?></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Tab 3: About Us -->
                        <div class="tab-pane fade" id="about-sec">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold">About Tag</label>
                                    <input type="text" name="settings[about_tag]" class="form-control border theme-border theme-bg-light-soft" value="<?= htmlspecialchars($settings_raw['about_tag'] ?? '') ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold">About Title</label>
                                    <input type="text" name="settings[about_title]" class="form-control border theme-border theme-bg-light-soft" value="<?= htmlspecialchars($settings_raw['about_title'] ?? '') ?>">
                                </div>
                                <div class="col-12">
                                    <label class="form-label small fw-bold">Narasi Utama (Paragraf 1)</label>
                                    <textarea name="settings[about_desc_1]" class="form-control border theme-border theme-bg-light-soft" rows="3"><?= htmlspecialchars($settings_raw['about_desc_1'] ?? '') ?></textarea>
                                </div>
                                <div class="col-12">
                                    <label class="form-label small fw-bold">Narasi Utama (Paragraf 2)</label>
                                    <textarea name="settings[about_desc_2]" class="form-control border theme-border theme-bg-light-soft" rows="3"><?= htmlspecialchars($settings_raw['about_desc_2'] ?? '') ?></textarea>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small fw-bold">Visi</label>
                                    <textarea name="settings[about_visi]" class="form-control border theme-border theme-bg-light-soft" rows="2"><?= htmlspecialchars($settings_raw['about_visi'] ?? '') ?></textarea>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small fw-bold">Misi</label>
                                    <textarea name="settings[about_misi]" class="form-control border theme-border theme-bg-light-soft" rows="2"><?= htmlspecialchars($settings_raw['about_misi'] ?? '') ?></textarea>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small fw-bold">Nilai</label>
                                    <textarea name="settings[about_nilai]" class="form-control border theme-border theme-bg-light-soft" rows="2"><?= htmlspecialchars($settings_raw['about_nilai'] ?? '') ?></textarea>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small fw-bold">Exp Years (e.g 5+ Tahun)</label>
                                    <input type="text" name="settings[about_exp_years]" class="form-control border theme-border theme-bg-light-soft" value="<?= htmlspecialchars($settings_raw['about_exp_years'] ?? '') ?>">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small fw-bold">Exp Label (e.g Pengalaman)</label>
                                    <input type="text" name="settings[about_exp_label]" class="form-control border theme-border theme-bg-light-soft" value="<?= htmlspecialchars($settings_raw['about_exp_label'] ?? '') ?>">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small fw-bold">About Side Image</label>
                                    <input type="file" name="about_image" class="form-control border theme-border theme-bg-light-soft p-2">
                                </div>
                            </div>
                        </div>

                        <!-- Tab 4: Terms & Facilities -->
                        <div class="tab-pane fade" id="terms-sec">
                            <h6 class="fw-bold mb-3 theme-text-heading text-accent">Syarat & Kebijakan</h6>
                            <div class="row g-3 mb-4">
                                <div class="col-md-4">
                                    <label class="form-label small fw-bold">Terms Tag</label>
                                    <input type="text" name="settings[terms_tag]" class="form-control mb-2" value="<?= htmlspecialchars($settings_raw['terms_tag'] ?? '') ?>">
                                    <label class="form-label small fw-bold">Terms Title</label>
                                    <input type="text" name="settings[terms_title]" class="form-control mb-2" value="<?= htmlspecialchars($settings_raw['terms_title'] ?? '') ?>">
                                    <label class="form-label small fw-bold">Terms Desc</label>
                                    <textarea name="settings[terms_desc]" class="form-control small" rows="3"><?= htmlspecialchars($settings_raw['terms_desc'] ?? '') ?></textarea>
                                </div>
                                <div class="col-md-8">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label small fw-bold">Card 1 (Identitas)</label>
                                            <input type="text" name="settings[terms_card_1_title]" class="form-control mb-2 fw-500" value="<?= htmlspecialchars($settings_raw['terms_card_1_title'] ?? '') ?>">
                                            <textarea name="settings[terms_card_1_content]" class="form-control small" rows="3"><?= htmlspecialchars($settings_raw['terms_card_1_content'] ?? '') ?></textarea>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label small fw-bold">Card 2 (Waktu)</label>
                                            <input type="text" name="settings[terms_card_2_title]" class="form-control mb-2 fw-500" value="<?= htmlspecialchars($settings_raw['terms_card_2_title'] ?? '') ?>">
                                            <textarea name="settings[terms_card_2_content]" class="form-control small" rows="3"><?= htmlspecialchars($settings_raw['terms_card_2_content'] ?? '') ?></textarea>
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label small fw-bold">Card 3 (Tanggung Jawab)</label>
                                            <input type="text" name="settings[terms_card_3_title]" class="form-control mb-2 fw-500" value="<?= htmlspecialchars($settings_raw['terms_card_3_title'] ?? '') ?>">
                                            <textarea name="settings[terms_card_3_content]" class="form-control small" rows="2"><?= htmlspecialchars($settings_raw['terms_card_3_content'] ?? '') ?></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <hr class="my-4 theme-border opacity-10">

                            <h6 class="fw-bold mb-3 theme-text-heading text-accent">Fasilitas Penunjang</h6>
                            <div class="row g-4">
                                <div class="col-md-4">
                                    <label class="form-label small fw-bold">Facilities Tag</label>
                                    <input type="text" name="settings[facilities_tag]" class="form-control mb-2" value="<?= htmlspecialchars($settings_raw['facilities_tag'] ?? '') ?>">
                                    <label class="form-label small fw-bold">Facilities Title</label>
                                    <input type="text" name="settings[facilities_title]" class="form-control mb-2" value="<?= htmlspecialchars($settings_raw['facilities_title'] ?? '') ?>">
                                    <label class="form-label small fw-bold">Facilities Desc</label>
                                    <textarea name="settings[facilities_desc]" class="form-control small" rows="2"><?= htmlspecialchars($settings_raw['facilities_desc'] ?? '') ?></textarea>
                                </div>
                                <div class="col-md-8">
                                    <div class="row g-3">
                                        <div class="col-md-3 col-6">
                                            <label class="form-label small fw-bold">Fasilitas 1</label>
                                            <input type="text" name="settings[fac1_title]" class="form-control mb-2" placeholder="Judul" value="<?= htmlspecialchars($settings_raw['fac1_title'] ?? '') ?>">
                                            <input type="text" name="settings[fac1_icon]" class="form-control small" placeholder="Ikon (e.g: helmet-safety)" value="<?= htmlspecialchars($settings_raw['fac1_icon'] ?? 'helmet-safety') ?>">
                                        </div>
                                        <div class="col-md-3 col-6">
                                            <label class="form-label small fw-bold">Fasilitas 2</label>
                                            <input type="text" name="settings[fac2_title]" class="form-control mb-2" placeholder="Judul" value="<?= htmlspecialchars($settings_raw['fac2_title'] ?? '') ?>">
                                            <input type="text" name="settings[fac2_icon]" class="form-control small" placeholder="Ikon (e.g: cloud-rain)" value="<?= htmlspecialchars($settings_raw['fac2_icon'] ?? 'cloud-rain') ?>">
                                        </div>
                                        <div class="col-md-3 col-6">
                                            <label class="form-label small fw-bold">Fasilitas 3</label>
                                            <input type="text" name="settings[fac3_title]" class="form-control mb-2" placeholder="Judul" value="<?= htmlspecialchars($settings_raw['fac3_title'] ?? '') ?>">
                                            <input type="text" name="settings[fac3_icon]" class="form-control small" placeholder="Ikon (e.g: gas-pump)" value="<?= htmlspecialchars($settings_raw['fac3_icon'] ?? 'gas-pump') ?>">
                                        </div>
                                        <div class="col-md-3 col-6">
                                            <label class="form-label small fw-bold">Fasilitas 4</label>
                                            <input type="text" name="settings[fac4_title]" class="form-control mb-2" placeholder="Judul" value="<?= htmlspecialchars($settings_raw['fac4_title'] ?? '') ?>">
                                            <input type="text" name="settings[fac4_icon]" class="form-control small" placeholder="Ikon (e.g: clock)" value="<?= htmlspecialchars($settings_raw['fac4_icon'] ?? 'clock') ?>">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Tab 5: Social Media -->
                        <div class="tab-pane fade" id="social-sec">
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-uppercase theme-text-muted">Instagram URL</label>
                                    <div class="input-group border theme-border rounded-3 overflow-hidden">
                                        <span class="input-group-text border-0 theme-bg-alt"><i class="fab fa-instagram text-danger"></i></span>
                                        <input type="text" name="settings[social_instagram]" class="form-control border-0 theme-bg-light-soft theme-text-heading p-2 px-3" value="<?= htmlspecialchars($settings_raw['social_instagram'] ?? '') ?>">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-uppercase theme-text-muted">Facebook URL</label>
                                    <div class="input-group border theme-border rounded-3 overflow-hidden">
                                        <span class="input-group-text border-0 theme-bg-alt"><i class="fab fa-facebook text-primary"></i></span>
                                        <input type="text" name="settings[social_facebook]" class="form-control border-0 theme-bg-light-soft theme-text-heading p-2 px-3" value="<?= htmlspecialchars($settings_raw['social_facebook'] ?? '') ?>">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-uppercase theme-text-muted">TikTok URL</label>
                                    <div class="input-group border theme-border rounded-3 overflow-hidden">
                                        <span class="input-group-text border-0 theme-bg-alt"><i class="fab fa-tiktok text-dark"></i></span>
                                        <input type="text" name="settings[social_tiktok]" class="form-control border-0 theme-bg-light-soft theme-text-heading p-2 px-3" value="<?= htmlspecialchars($settings_raw['social_tiktok'] ?? '') ?>">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-uppercase theme-text-muted">YouTube URL</label>
                                    <div class="input-group border theme-border rounded-3 overflow-hidden">
                                        <span class="input-group-text border-0 theme-bg-alt"><i class="fab fa-youtube text-danger"></i></span>
                                        <input type="text" name="settings[social_youtube]" class="form-control border-0 theme-bg-light-soft theme-text-heading p-2 px-3" value="<?= htmlspecialchars($settings_raw['social_youtube'] ?? '') ?>">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-footer theme-bg-alt border-0 p-4">
                    <div class="d-flex justify-content-end gap-2">
                        <button type="submit" class="btn btn-primary fw-bold px-5 py-2.5 rounded-pill shadow-sm border-0">
                            <i class="fas fa-save me-2"></i> Simpan Perubahan
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<style>
.admin-tabs .nav-link { color: var(--text-muted); border: none; position: relative; border-bottom: 2px solid transparent; }
.admin-tabs .nav-link.active { color: var(--accent); background: transparent; border-bottom-color: var(--accent); }
.admin-tabs .nav-link:hover { color: var(--accent); }
.fw-500 { font-weight: 500; }
</style>

<script>
document.querySelector('form').addEventListener('submit', function(e) {
    const displayInput = document.getElementById('maps_display_input');
    const hiddenInput = document.getElementById('maps_hidden_input');
    if (displayInput && hiddenInput && displayInput.value.trim() !== '') {
        let value = displayInput.value.trim();
        if (value.includes('<iframe')) {
            const match = value.match(/src=["']([^"']+)["']/);
            if (match && match[1]) value = match[1];
        }
        hiddenInput.value = btoa(unescape(encodeURIComponent(value)));
        displayInput.value = ''; 
    }
});
</script>

<?php include __DIR__ . '/../template/footer.php'; ?>
