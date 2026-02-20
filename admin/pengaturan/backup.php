<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../template/auth.php';
require_once __DIR__ . '/../../config/helper.php';
require_once __DIR__ . '/../../config/backup_helper.php';

$backup_dir = __DIR__ . '/../../backups/db/';

// Handle Manual Backup
if (isset($_GET['action']) && $_GET['action'] === 'create') {
    $result = perform_database_backup($pdo);
    if ($result['status'] === 'success') {
        redirect_with_alert('backup.php', 'Backup database berhasil dibuat: ' . $result['filename']);
    } else {
        $error = "Gagal membuat backup: " . $result['message'];
    }
}

// Handle Delete
if (isset($_GET['delete'])) {
    $file = $_GET['delete'];
    $path = $backup_dir . $file;
    if (file_exists($path) && strpos($file, '..') === false) {
        unlink($path);
        redirect_with_alert('backup.php', 'File backup berhasil dihapus.');
    }
}

// Handle Download
if (isset($_GET['download'])) {
    $file = $_GET['download'];
    $path = $backup_dir . $file;
    if (file_exists($path) && strpos($file, '..') === false) {
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="'.basename($path).'"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($path));
        readfile($path);
        exit;
    }
}

// Scan backup directory
$files = [];
if (is_dir($backup_dir)) {
    $items = scandir($backup_dir);
    foreach ($items as $item) {
        if ($item !== '.' && $item !== '..' && strpos($item, '.sql') !== false) {
            $files[] = [
                'name' => $item,
                'size' => filesize($backup_dir . $item),
                'date' => filemtime($backup_dir . $item)
            ];
        }
    }
    // Sort by date descending
    usort($files, function($a, $b) {
        return $b['date'] - $a['date'];
    });
}

include __DIR__ . '/../template/header.php';
include __DIR__ . '/../template/sidebar.php';
?>

<div id="content">
    <nav class="navbar top-navbar">
        <div class="container-fluid">
            <button type="button" id="sidebarCollapse" class="btn theme-bg-card theme-text-heading rounded-circle shadow-sm me-3 border-0 d-lg-none">
                <i class="fas fa-bars"></i>
            </button>
            <h5 class="mb-0 fw-bold ms-2 theme-text-heading">Database Backup & Security</h5>
            <div class="ms-auto flex-shrink-0">
                <a href="?action=create" class="btn btn-accent rounded-pill px-3 py-1.5 fw-bold shadow-sm small">
                    <i class="fas fa-database me-1"></i> Backup Sekarang
                </a>
            </div>
        </div>
    </nav>

    <div class="main-content">
        <div class="row g-4 mb-4">
            <div class="col-lg-8">
                <div class="mb-4">
                    <h4 class="fw-bold mb-0">Riwayat Backup</h4>
                    <p class="text-muted small mb-0">Daftar cadangan database yang tersimpan di folder server.</p>
                </div>

                <?php if (isset($error)): ?>
                    <div class="alert alert-danger shadow-sm border-0 rounded-3">
                        <i class="fas fa-exclamation-triangle me-2"></i> <?= $error ?>
                    </div>
                <?php endif; ?>
                <?php display_alert(); ?>

                <div class="card border-0 shadow-sm rounded-4 theme-bg-card">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="theme-bg-alt theme-text-heading">
                                    <tr>
                                        <th class="ps-4 py-3">Nama File</th>
                                        <th class="py-3">Ukuran</th>
                                        <th class="py-3">Tanggal Dibuat</th>
                                        <th class="py-3 text-end pe-4">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="theme-text-heading">
                                    <?php if (empty($files)): ?>
                                        <tr>
                                            <td colspan="4" class="text-center py-5 text-muted">Belum ada file backup tersedia.</td>
                                        </tr>
                                    <?php endif; ?>
                                    <?php foreach ($files as $f): ?>
                                        <tr>
                                            <td class="ps-4">
                                                <div class="d-flex align-items-center gap-3">
                                                    <div class="bg-primary bg-opacity-10 text-primary rounded-3 p-2">
                                                        <i class="fas fa-file-code fa-lg"></i>
                                                    </div>
                                                    <span class="fw-bold"><?= $f['name'] ?></span>
                                                </div>
                                            </td>
                                            <td><?= round($f['size'] / 1024, 2) ?> KB</td>
                                            <td><?= date('d M Y, H:i', $f['date']) ?></td>
                                            <td class="text-end pe-4">
                                                <div class="btn-group">
                                                    <a href="?download=<?= $f['name'] ?>" class="btn btn-sm btn-light-soft rounded-circle me-1" title="Download">
                                                        <i class="fas fa-download text-success"></i>
                                                    </a>
                                                    <a href="?delete=<?= $f['name'] ?>" class="btn btn-sm btn-light-soft rounded-circle" onclick="return confirm('Hapus file backup ini permanently?')" title="Hapus">
                                                        <i class="fas fa-trash text-danger"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card border-0 shadow-sm rounded-4 p-4 theme-bg-card sticky-top" style="top: 100px;">
                    <div class="d-flex align-items-center gap-3 mb-4">
                        <div class="bg-success bg-opacity-10 text-success rounded-3 p-2">
                            <i class="fas fa-clock"></i>
                        </div>
                        <h6 class="fw-bold mb-0">Automasi Backup</h6>
                    </div>
                    <p class="small theme-text-muted mb-4">Agar sistem melakukan backup otomatis tanpa harus Anda klik manual, silakan ikuti panduan berikut di Windows (XAMPP):</p>
                    
                    <div class="bg-light-soft theme-bg-alt p-3 rounded-4 mb-4">
                        <ol class="small theme-text-heading ps-3 mb-0">
                            <li class="mb-2">Buka <b>Task Scheduler</b> di Windows.</li>
                            <li class="mb-2">Klik <b>Create Basic Task</b>.</li>
                            <li class="mb-2">Beri nama "Rental Motor DB Backup".</li>
                            <li class="mb-2">Pilih Trigger <b>Daily</b> (Harian).</li>
                            <li class="mb-3">Di menu <b>Action</b>, pilih <b>Start a Program</b>.</li>
                        </ol>
                        <div class="mt-2 p-2 bg-dark rounded text-white-50 x-small font-monospace" style="font-size: 0.7rem;">
                            Program/script:<br>
                            <span class="text-info">C:\xampp2\php\php.exe</span><br><br>
                            Add arguments:<br>
                            <span class="text-info">-f C:\xampp2\htdocs\Rental-Motor\cron_backup.php</span>
                        </div>
                    </div>

                    <div class="alert alert-info border-0 shadow-sm small">
                        <i class="fas fa-info-circle me-2"></i> File backup disimpan di folder: <br>
                        <code>/backups/db/</code>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../template/footer.php'; ?>
