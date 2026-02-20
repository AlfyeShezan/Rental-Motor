<?php
// Main Configuration File
date_default_timezone_set('Asia/Jakarta');
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'database.php';
require_once 'midtrans.php';
require_once 'security.php';

// Base URL (Automatically detect protocol, host and directory)
$protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? "https://" : "http://";
$host = $_SERVER['HTTP_HOST'];
$script_name = $_SERVER['SCRIPT_NAME'];
$base_dir = str_replace(basename($script_name), '', $script_name);
// Ensure we get the correct base directory even if accessed from subfolders
$base_dir = preg_replace('/config\/$/', '', $base_dir);
$base_dir = preg_replace('/admin\/.*$/', '', $base_dir);
define('BASE_URL', $protocol . $host . $base_dir);

// Fetch global settings from database
try {
    $stmt = $pdo->query("SELECT setting_key, setting_value FROM settings");
    $settings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
} catch (PDOException $e) {
    $settings = [];
}

define('SITE_NAME', $settings['site_name'] ?? 'JS Rental');
define('WA_NUMBER', $settings['whatsapp_number'] ?? '');
define('CONTACT_ADDRESS', $settings['address'] ?? '');
define('SITE_DESC', $settings['meta_description'] ?? '');

// Currency and Fees
define('CURRENCY', 'Rp ');
define('LATE_FEE_PER_DAY', $settings['late_fee_per_day'] ?? 0);
define('DELIVERY_FEE', $settings['delivery_fee'] ?? 0);

// SEO
define('META_TITLE', $settings['meta_title'] ?? SITE_NAME);
define('META_DESC', $settings['meta_description'] ?? SITE_DESC);

// Path aliases
define('UPLOAD_PATH', __DIR__ . '/../uploads/');
define('ASSET_PATH', BASE_URL . 'assets/');

// SMTP Configuration (for PHPMailer)
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USER', 'alfidias1511@gmail.com'); // Ganti dengan email Gmail Anda
define('SMTP_PASS', 'waht qfgw uuyw lwsd'); // Masukkan App Password Gmail Anda di sini
?>
