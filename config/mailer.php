<?php
/**
 * Mailer Utility for OTP using PHPMailer
 */

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

require_once __DIR__ . '/../includes/PHPMailer/Exception.php';
require_once __DIR__ . '/../includes/PHPMailer/PHPMailer.php';
require_once __DIR__ . '/../includes/PHPMailer/SMTP.php';

function send_otp_email($to, $otp) {
    // 1. Debugging for Localhost/Development (Always log for safety)
    $log_file = __DIR__ . '/../otp_debug.log';
    $log_entry = "[" . date('Y-m-d H:i:s') . "] OTP for $to: $otp" . PHP_EOL;
    file_put_contents($log_file, $log_entry, FILE_APPEND);
    
    // 2. PHPMailer Configuration
    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USER;
        $mail->Password   = SMTP_PASS;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = SMTP_PORT;

        // Bypass SSL certificate verification for local development
        $mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );

        // Recipients
        $mail->setFrom('no-reply@rental-motor.com', SITE_NAME);
        $mail->addAddress($to);

        // Content
        $mail->isHTML(true);
        $mail->Subject = "OTP Verifikasi Lupa Password - " . SITE_NAME;
        
        $mail->Body = "
        <div style='font-family: Arial, sans-serif; padding: 20px; border: 1px solid #ddd; border-radius: 10px; max-width: 600px;'>
            <h2 style='color: #14b8a6;'>Permintaan Lupa Password</h2>
            <p>Halo,</p>
            <p>Kami menerima permintaan untuk mereset password akun Admin Anda. Silakan gunakan kode OTP di bawah ini untuk melanjutkan:</p>
            <div style='background: #f4f4f4; padding: 15px; text-align: center; border-radius: 5px; margin: 20px 0;'>
                <h1 style='letter-spacing: 5px; color: #333; margin: 0;'>$otp</h1>
            </div>
            <p>Kode ini hanya berlaku selama <strong>10 menit</strong>.</p>
            <hr style='border: 0; border-top: 1px solid #eee; margin-top: 20px;'>
            <p style='font-size: 12px; color: #888;'>&copy; " . date('Y') . " " . SITE_NAME . "</p>
        </div>";

        $mail->send();
        return true;
    } catch (Exception $e) {
        // Log mailer error if SMTP fails
        $error_msg = "[" . date('Y-m-d H:i:s') . "] PHPMailer Error: " . $mail->ErrorInfo . " | Exception: " . $e->getMessage() . PHP_EOL;
        file_put_contents($log_file, $error_msg, FILE_APPEND);
        return false;
    }
}

function send_booking_confirmation($to, $booking_details) {
    require_once __DIR__ . '/helper.php';
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USER;
        $mail->Password   = SMTP_PASS;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = SMTP_PORT;

        $mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );

        $mail->setFrom('no-reply@rental-motor.com', SITE_NAME);
        $mail->addAddress($to);

        $mail->isHTML(true);
        $mail->Subject = "Konfirmasi Pembayaran Sewa Motor - " . SITE_NAME;
        
        $body = "
        <div style='font-family: Arial, sans-serif; padding: 20px; border: 1px solid #ddd; border-radius: 10px; max-width: 600px;'>
            <h2 style='color: #14b8a6;'>Pembayaran Berhasil!</h2>
            <p>Halo <strong>{$booking_details['name']}</strong>,</p>
            <p>Terima kasih telah melakukan pembayaran. Pesanan Anda telah resmi terkonfirmasi.</p>
            
            <div style='background: #f9f9f9; padding: 15px; border-radius: 5px; margin: 20px 0;'>
                <p style='margin: 5px 0;'><strong>ID Pesanan:</strong> {$booking_details['midtrans_id']}</p>
                <p style='margin: 5px 0;'><strong>Unit Motor:</strong> {$booking_details['brand']} {$booking_details['model']}</p>
                <p style='margin: 5px 0;'><strong>Tanggal Ambil:</strong> " . format_date_id($booking_details['pickup_date']) . "</p>
                <p style='margin: 5px 0;'><strong>Durasi:</strong> {$booking_details['duration']} Hari</p>
                <p style='margin: 5px 0;'><strong>Total Bayar:</strong> " . format_rupiah($booking_details['total_price']) . "</p>
            </div>
            
            <p>Silakan tunjukkan email ini atau kartu identitas (KTP/SIM) saat pengambilan unit di lokasi kami.</p>
            <p>Jika ada pertanyaan, silakan hubungi kami via WhatsApp: " . WA_NUMBER . "</p>
            
            <hr style='border: 0; border-top: 1px solid #eee; margin-top: 20px;'>
            <p style='font-size: 12px; color: #888;'>&copy; " . date('Y') . " " . SITE_NAME . "</p>
        </div>";

        $mail->Body = $body;
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Mailer Error: " . $mail->ErrorInfo);
        return false;
    }
}
