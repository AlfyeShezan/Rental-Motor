<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/helper.php';
?>
<!DOCTYPE html>
<html lang="id" data-theme="light">
<head>
    <script>
        // Force light mode for admin area
        document.documentElement.setAttribute('data-theme', 'light');
    </script>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - <?= SITE_NAME ?></title>
    
    <!-- PWA Support -->
    <link rel="manifest" href="<?= BASE_URL ?>manifest.json">
    <meta name="theme-color" content="#14b8a6">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="<?= SITE_NAME ?>">
    <link rel="apple-touch-icon" href="<?= BASE_URL ?>uploads/settings/<?= get_setting('site_logo') ?>">
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Global Style -->
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css?v=1.1">

    
    <!-- Custom Admin CSS -->
    <style>
        :root {
            --sidebar-width: 280px;
        }

        
        body {
            font-family: 'Outfit', sans-serif;
            background-color: var(--bg-main);
            color: var(--text-body);
            overflow-x: hidden;
            -webkit-font-smoothing: antialiased;
            transition: background-color 0.3s ease, color 0.3s ease;
        }
        
        #sidebar {
            min-width: var(--sidebar-width);
            max-width: var(--sidebar-width);
            background: var(--bg-sidebar);
            color: var(--text-body);
            transition: var(--transition);
            min-height: 100vh;
            position: fixed;
            height: 100%;
            z-index: 1100;
            border-right: 1px solid var(--border);
        }
        
        #sidebar .sidebar-header {
            padding: 40px 30px;
            background: var(--bg-sidebar);
            text-align: left;
        }
        
        .sidebar-logo {
            font-size: 1.5rem;
            font-weight: 800;
            color: var(--text-heading);
            text-decoration: none !important;
            display: block;
        }
        
        #sidebar ul.components {
            padding: 10px 20px;
        }
        
        #sidebar ul li {
            margin-bottom: 5px;
        }
        
        #sidebar ul li a {
            padding: 14px 18px;
            display: flex;
            align-items: center;
            color: var(--text-muted);
            text-decoration: none;
            transition: var(--transition);
            font-size: 0.95rem;
            font-weight: 500;
            border-radius: var(--radius-md);
        }
        
        #sidebar ul li a:hover {
            color: var(--text-heading);
            background: var(--bg-main);
        }
        
        #sidebar ul li.active > a {
            background: var(--bg-card);
            color: var(--text-heading);
            box-shadow: var(--shadow-md);
            border: 1px solid var(--border);
            font-weight: 700;
        }

        #sidebar ul li.active > a i {
            color: var(--accent);
        }
        
        #sidebar ul li a i {
            margin-right: 15px;
            font-size: 1.1rem;
            width: 24px;
            text-align: center;
            transition: var(--transition);
        }
        
        #content {
            width: 100%;
            padding-left: var(--sidebar-width);
            min-height: 100vh;
            transition: var(--transition);
        }
        
        .top-navbar {
            padding: 15px 40px;
            background: var(--glass);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-bottom: 1px solid var(--border);
            position: sticky;
            top: 0;
            z-index: 1050;
        }
        
        .main-content {
            padding: 40px;
        }
        
        /* Stats & Cards */
        .card {
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-md);
            transition: var(--transition);
            overflow: hidden;
            background: var(--bg-card);
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
        }
        
        .stat-card {
            position: relative;
            padding: 25px;
        }
        
        .stat-icon {
            width: 54px;
            height: 54px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            background: var(--bg-main);
            transition: var(--transition);
        }
        
        .card:hover .stat-icon {
            background: var(--accent);
            color: #000;
            transform: scale(1.1) rotate(5deg);
        }
        
        /* Badges & Tables */
        .table {
            --bs-table-bg: transparent;
            margin-bottom: 0;
            color: var(--text-body);
        }
        
        .table thead th {
            background: var(--bg-main);
            border-bottom: 1px solid var(--border);
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 15px 20px;
            color: var(--text-muted);
        }
        
        .table tbody td {
            padding: 18px 20px;
            vertical-align: middle;
            border-bottom: 1px solid var(--border);
        }

        .dropdown-menu {
            background-color: var(--bg-card);
            border: 1px solid var(--border);
        }

        .dropdown-item {
            color: var(--text-body);
        }

        .dropdown-item:hover {
            background-color: var(--bg-main);
            color: var(--text-heading);
        }
        
        /* Responsive Sidebar Toggle */
        @media (max-width: 992px) {
            :root {
                --sidebar-width: 260px;
            }
            #sidebar {
                margin-left: calc(-1 * var(--sidebar-width));
                box-shadow: none;
            }
            #sidebar.active {
                margin-left: 0;
                box-shadow: 10px 0 30px rgba(0,0,0,0.1);
            }
            #content {
                padding-left: 0;
            }
            .top-navbar {
                padding: 15px 20px;
            }
            .main-content {
                padding: 20px;
            }
            
            /* Sidebar Overlay */
            #sidebar-overlay {
                display: none;
                position: fixed;
                width: 100vw;
                height: 100vh;
                background: rgba(0, 0, 0, 0.3);
                z-index: 1090;
                top: 0;
                left: 0;
                backdrop-filter: blur(2px);
            }
            #sidebar-overlay.active {
                display: block;
            }

            .sidebar-header {
                padding: 30px 20px !important;
            }
        }

        @media (max-width: 576px) {
            .stat-card {
                padding: 20px;
            }
            .stat-icon {
                width: 45px;
                height: 45px;
                font-size: 1.2rem;
            }
            h4.fw-bold {
                font-size: 1.25rem;
            }
            .top-navbar h5 {
                font-size: 1rem;
            }
        }
    </style>
</head>
<body class="">
    <div id="sidebar-overlay"></div>
    <div id="wrapper">
