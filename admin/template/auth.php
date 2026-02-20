<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: " . BASE_URL . "admin/login.php");
    exit();
}

/**
 * Check if the user is Super Admin
 */
function is_super_admin() {
    return isset($_SESSION['admin_role']) && $_SESSION['admin_role'] === 'Super Admin';
}

/**
 * Restrict access to Super Admin only
 */
function super_admin_only() {
    if (!is_super_admin()) {
        $_SESSION['alert_message'] = "Akses ditolak. Anda bukan Super Admin.";
        $_SESSION['alert_type'] = "danger";
        header("Location: " . BASE_URL . "admin/index.php");
        exit();
    }
}
?>
