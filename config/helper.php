<?php
// Global Helper Functions

/**
 * Format number to Indonesian Rupiah
 */
function format_rupiah($number) {
    return 'Rp ' . number_format($number, 0, ',', '.');
}

/**
 * Clean string for WhatsApp message
 */
function clean_wa_text($text) {
    return urlencode($text);
}

/**
 * Generate WhatsApp URL
 */
function generate_wa_url($phone, $message) {
    $phone = preg_replace('/[^0-9]/', '', $phone);
    if (substr($phone, 0, 1) === '0') {
        $phone = '62' . substr($phone, 1);
    }
    return "https://wa.me/{$phone}?text=" . urlencode($message);
}

/**
 * Get background color based on status
 */
function get_status_badge($status) {
    switch ($status) {
        case 'Tersedia':
            return 'success';
        case 'Disewa':
            return 'info';
        case 'Maintenance':
            return 'danger';
        case 'Pending':
            return 'info';
        case 'Selesai':
            return 'primary';
        case 'Batal':
            return 'secondary';
        default:
            return 'light';
    }
}

/**
 * Redirect with alert (Session based)
 */
function redirect_with_alert($url, $message, $type = 'success') {
    $_SESSION['alert_message'] = $message;
    $_SESSION['alert_type'] = $type;
    header("Location: $url");
    exit();
}

/**
 * Display alert if exists
 */
function display_alert() {
    if (isset($_SESSION['alert_message'])) {
        $message = $_SESSION['alert_message'];
        $type = $_SESSION['alert_type'];
        unset($_SESSION['alert_message']);
        unset($_SESSION['alert_type']);
        echo "
        <div class='alert alert-{$type} alert-dismissible fade show' role='alert'>
            {$message}
            <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
        </div>";
    }
}

/**
 * Get website setting by key
 */
function get_setting($key, $default = '') {
    global $pdo;
    try {
        $stmt = $pdo->prepare("SELECT setting_value FROM settings WHERE setting_key = ?");
        $stmt->execute([$key]);
        $val = $stmt->fetchColumn();
        return $val !== false ? $val : $default;
    } catch (PDOException $e) {
        return $default;
    }
}


/**
 * Indonesian Date Format
 */
function format_date_id($date) {
    if (strlen($date) > 10) {
        return date('d-m-Y H:i', strtotime($date));
    }
    return date('d-m-Y', strtotime($date));
}
?>
