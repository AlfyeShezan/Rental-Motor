<?php
require_once 'config/config.php';
require_once 'config/helper.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $motor_id = $_POST['motor_id'];
    $motor_name = $_POST['motor_name'];
    $name = $_POST['name'];
    $phone = $_POST['phone'];
    $pickup_date = $_POST['pickup_date'];
    $return_date = $_POST['return_date'];
    $duration = $_POST['duration'];
    $location = $_POST['location'] ?: 'Ambil di Tempat';

    // Get motor price again just to be sure
    $stmt = $pdo->prepare("SELECT price_per_day FROM motors WHERE id = ?");
    $stmt->execute([$motor_id]);
    $price = $stmt->fetchColumn();
    
    $total_price = $price * $duration;

    // Build the WhatsApp message template
    $message = "Halo " . SITE_NAME . ",\n\n";
    $message .= "Saya ingin melakukan booking motor dengan detail berikut:\n";
    $message .= "----------------------------------\n";
    $message .= "*Nama:* " . $name . "\n";
    $message .= "*No HP:* " . $phone . "\n";
    $message .= "*Motor:* " . $motor_name . "\n";
    $message .= "*Tanggal Sewa:* " . format_date_id($pickup_date) . "\n";
    $message .= "*Lama Sewa:* " . $duration . " Hari\n";
    $message .= "*Total Harga:* " . format_rupiah($total_price) . "\n";
    $message .= "*Lokasi Antar:* " . $location . "\n";
    $message .= "----------------------------------\n";
    $message .= "Mohon konfirmasi ketersediaannya. Terima kasih!";

    // Generate WA URL
    $wa_url = generate_wa_url(WA_NUMBER, $message);

    // Redirect to WhatsApp
    header("Location: $wa_url");
    exit();
} else {
    header("Location: motor.php");
    exit();
}
