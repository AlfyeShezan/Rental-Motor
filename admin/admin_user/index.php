<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../template/auth.php';
require_once __DIR__ . '/../../config/helper.php';

// Fetch all admin users
try {
    $stmt = $pdo->query("SELECT id, username, full_name, role, created_at FROM admins ORDER BY id ASC");
    $admins = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Error fetching admins: " . $e->getMessage());
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
            <h5 class="mb-0 fw-bold ms-2">Manajemen User</h5>
            <div class="ms-auto">
                <a href="tambah.php" class="btn btn-accent rounded-pill px-3 py-1.5 fw-bold shadow-sm small">
                    <i class="fas fa-user-plus me-1"></i> Tambah Admin
                </a>
            </div>
        </div>
    </nav>

    <div class="main-content">
        <div class="row align-items-center mb-4">
            <div class="col">
                <h4 class="fw-bold mb-0">Administrator Access</h4>
                <p class="text-muted small mb-0">Daftar pengguna yang memiliki akses ke sistem manajemen backend.</p>
            </div>
        </div>

        <?php display_alert(); ?>

        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="card-body p-0 p-md-3">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th class="ps-4">FULL NAME</th>
                                <th>USERNAME</th>
                                <th>PRIVILEGE LEVEL</th>
                                <th>REGISTRATION</th>
                                <th class="text-end pe-4">ACTION</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($admins as $a): ?>
                                <tr>
                                    <td class="ps-4">
                                        <div class="d-flex align-items-center gap-3">
                                            <img src="https://ui-avatars.com/api/?name=<?= urlencode($a['full_name']) ?>&background=0f172a&color=fff&rounded=true" width="35" class="rounded-circle border border-white shadow-sm">
                                            <div class="fw-bold text-dark"><?= htmlspecialchars($a['full_name']) ?></div>
                                        </div>
                                    </td>
                                    <td><span class="badge bg-light text-muted border px-2 py-1"><?= htmlspecialchars($a['username']) ?></span></td>
                                    <td>
                                        <span class="badge rounded-pill bg-<?= $a['role'] == 'Super Admin' ? 'danger' : 'primary' ?> bg-opacity-10 text-<?= $a['role'] == 'Super Admin' ? 'danger' : 'primary' ?> px-3 py-2 fw-bold">
                                            <i class="fas <?= $a['role'] == 'Super Admin' ? 'fa-shield-alt' : 'fa-user' ?> me-1 opacity-50"></i>
                                            <?= strtoupper($a['role']) ?>
                                        </span>
                                    </td>
                                    <td class="small text-muted"><?= date('d/m/Y', strtotime($a['created_at'])) ?></td>
                                    <td class="text-end pe-4">
                                        <div class="d-flex justify-content-end gap-2">
                                            <a href="edit.php?id=<?= $a['id'] ?>" class="btn btn-outline-primary btn-xs rounded-pill px-2 py-1 small shadow-sm border-0 bg-light-hover" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <?php if ($a['id'] != $_SESSION['admin_id']): ?>
                                                <a href="hapus.php?id=<?= $a['id'] ?>" class="btn btn-outline-danger btn-xs rounded-pill px-2 py-1 small shadow-sm border-0 bg-light-hover" title="Hapus" onclick="return confirm('Hapus user admin ini?')">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            <?php endif; ?>
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
</div>


<?php include __DIR__ . '/../template/footer.php'; ?>
