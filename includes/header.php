<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/helper.php';

// Fetch global settings
$site_name = get_setting('site_name');
?>
<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $site_name ?> - Sewa Motor Terbaik di Yogyakarta</title>
    
    <!-- Primary Meta Tags -->
    <meta name="title" content="<?= $site_name ?> - Sewa Motor Terbaik di Yogyakarta">
    <meta name="description" content="<?= get_setting('meta_description', 'Pusat persewaan motor murah dan terpercaya di Yogyakarta. Armada terbaru, syarat mudah, dan pelayanan profesional.') ?>">
    <meta name="keywords" content="rental motor jogja, sewa motor yogyakarta, rental motor murah, sewa motor matic, rental motor dekat stasiun tugu">

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?= BASE_URL ?>">
    <meta property="og:title" content="<?= $site_name ?> - Sewa Motor Terbaik">
    <meta property="og:description" content="Sewa motor murah dan terpercaya di Yogyakarta. Booking online sekarang!">
    <meta property="og:image" content="<?= BASE_URL ?>assets/img/og-image.jpg">

    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="<?= BASE_URL ?>">
    <meta property="twitter:title" content="<?= $site_name ?> - Sewa Motor Terbaik">
    <meta property="twitter:description" content="Sewa motor murah dan terpercaya di Yogyakarta. Booking online sekarang!">
    <meta property="twitter:image" content="<?= BASE_URL ?>assets/img/og-image.jpg">

    <!-- PWA Support -->
    <link rel="manifest" href="<?= BASE_URL ?>manifest.json">
    <meta name="theme-color" content="#14b8a6">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="<?= $site_name ?>">
    <link rel="apple-touch-icon" href="<?= BASE_URL ?>uploads/settings/<?= get_setting('site_logo') ?>">
    
    <!-- SEO Meta Tags -->
    <meta name="description" content="<?= get_setting('meta_description') ?>">
    <meta name="keywords" content="<?= get_setting('meta_keywords') ?>">
    <meta name="author" content="<?= $site_name ?>">

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts: Plus Jakarta Sans, Montserrat, & Urbanist -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Montserrat:wght@700;800;900&family=Urbanist:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <!-- Flatpickr -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css?v=1.1">
    <!-- Swiper CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
    <!-- AOS Animation -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">

    <!-- Favicon -->
    <link rel="shortcut icon" href="<?= BASE_URL ?>assets/img/favicon.ico" type="image/x-icon">
</head>
<body>
    <main>
