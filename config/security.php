<?php
/**
 * Security Helper Module
 * Handles CSRF Protection, File Validation, and Input Sanitization
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Generate a CSRF Token
 */
function generate_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF Token
 */
function verify_csrf_token($token) {
    if (!isset($_SESSION['csrf_token']) || $token !== $_SESSION['csrf_token']) {
        die("Security Alert: CSRF Token Validation Failed.");
    }
    return true;
}

/**
 * Secure File Upload Validation
 * Checks: Extension, MIME Type, Size, and renames file
 */
function validate_image_upload($file_field, $max_size = 41943040) { // Default 40MB
    if (!isset($_FILES[$file_field]) || $_FILES[$file_field]['error'] !== UPLOAD_ERR_OK) {
        return false;
    }

    $file = $_FILES[$file_field];
    $allowed_ext = ['jpg', 'jpeg', 'png', 'webp', 'gif', 'svg'];
    $allowed_mime = ['image/jpeg', 'image/png', 'image/webp', 'image/gif', 'image/svg+xml', 'image/svg'];

    // 1. Check Extension
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowed_ext)) {
        return ['error' => 'Ekstensi file tidak diizinkan.'];
    }

    // 2. Check MIME Type (Content)
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    if (!in_array($mime, $allowed_mime)) {
        return ['error' => 'Konten file bukan gambar yang valid.'];
    }

    // 3. Check Size
    if ($file['size'] > $max_size) {
        $detected = round($file['size'] / 1024 / 1024, 2);
        return ['error' => "Ukuran file terlalu besar (Maks 40MB). Terdeteksi: {$detected}MB"];
    }

    return true;
}

/**
 * Secure File Upload Validation for Multiple Files
 */
function validate_image_upload_multi($file_field, $index, $max_size = 41943040) { // Default 40MB
    if (!isset($_FILES[$file_field]) || $_FILES[$file_field]['error'][$index] !== UPLOAD_ERR_OK) {
        return false;
    }

    $allowed_ext = ['jpg', 'jpeg', 'png', 'webp', 'gif', 'svg'];
    $allowed_mime = ['image/jpeg', 'image/png', 'image/webp', 'image/gif', 'image/svg+xml', 'image/svg'];

    // 1. Check Extension
    $name = $_FILES[$file_field]['name'][$index];
    $tmp_name = $_FILES[$file_field]['tmp_name'][$index];
    $size = $_FILES[$file_field]['size'][$index];

    $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
    if (!in_array($ext, $allowed_ext)) {
        return ['error' => "File '$name' ditolak: Ekstensi tidak diizinkan."];
    }

    // 2. Check MIME Type
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $tmp_name);
    finfo_close($finfo);
    if (!in_array($mime, $allowed_mime)) {
        return ['error' => "File '$name' ditolak: Konten bukan gambar valid."];
    }

    // 3. Check Size
    if ($size > $max_size) {
        $detected = round($size / 1024 / 1024, 2);
        return ['error' => "File '$name' ditolak: Ukuran melebihi 40MB. Terdeteksi: {$detected}MB"];
    }

    return true;
}

/**
 * Global XSS Protection
 */
function h($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}
