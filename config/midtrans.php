<?php
// Midtrans Configuration
// MENCARI KUNCI AKSES?
// Daftar di https://dashboard.midtrans.com/register
// Ganti dengan Server Key & Client Key Anda

define('MIDTRANS_SERVER_KEY', 'Mid-server-T7ayzXvP_KdTHqZArozgcjs9'); // Ganti dengan Server Key Anda
define('MIDTRANS_CLIENT_KEY', 'Mid-client-mIYWS5A7hNjLDOdM'); // Ganti dengan Client Key Anda
define('MIDTRANS_IS_PRODUCTION', false); // Set true untuk Live

// Base URL API
define('MIDTRANS_API_URL', MIDTRANS_IS_PRODUCTION 
    ? 'https://app.midtrans.com/snap/v1/transactions' 
    : 'https://app.sandbox.midtrans.com/snap/v1/transactions');

// Core API URL (For Status Check)
define('MIDTRANS_CORE_API_URL', MIDTRANS_IS_PRODUCTION
    ? 'https://api.midtrans.com/v2'
    : 'https://api.sandbox.midtrans.com/v2');

define('MIDTRANS_SNAP_JS', MIDTRANS_IS_PRODUCTION
    ? 'https://app.midtrans.com/snap/snap.js'
    : 'https://app.sandbox.midtrans.com/snap/snap.js');
?>
