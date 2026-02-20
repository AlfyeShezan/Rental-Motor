<?php
header('Content-Type: application/json');
require_once '../config/config.php';
require_once '../config/midtrans.php';

$order_id = $_GET['order_id'] ?? '';

if (!$order_id) {
    echo json_encode(['status' => 'error', 'message' => 'Order ID is missing']);
    exit;
}

try {
    // 1. Get Transaction Status
    $url = MIDTRANS_CORE_API_URL . '/' . $order_id . '/status';
    $serverKey = MIDTRANS_SERVER_KEY;

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Accept: application/json',
        'Authorization: Basic ' . base64_encode($serverKey . ':')
    ]);
    
    $result = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode == 200) {
        $response = json_decode($result, true);
        $transaction_status = $response['transaction_status'];
        $fraud_status = $response['fraud_status'] ?? '';

        // Map Status
        $new_status = 'Pending';
        if ($transaction_status == 'capture') {
            if ($fraud_status == 'challenge') {
                $new_status = 'Pending';
            } else {
                $new_status = 'Disewa';
            }
        } else if ($transaction_status == 'settlement') {
            $new_status = 'Disewa';
        } else if ($transaction_status == 'pending') {
            $new_status = 'Pending';
        } else if ($transaction_status == 'deny' || $transaction_status == 'expire' || $transaction_status == 'cancel') {
            $new_status = 'Batal';
        }

        // 2. Fetch Existing Status for stock management and notification
        $stmt_old = $pdo->prepare("SELECT status, email FROM bookings WHERE midtrans_id = ?");
        $stmt_old->execute([$order_id]);
        $booking_data = $stmt_old->fetch();
        $old_status = $booking_data['status'] ?? 'Pending';

        // 3. Update Database Status
        $stmt = $pdo->prepare("UPDATE bookings SET status = ? WHERE midtrans_id = ?");
        $stmt->execute([$new_status, $order_id]);

        // 4. Auto-manage stock using central helper
        require_once '../config/stock_helper.php';
        update_motor_stock_by_order_id_with_old_status($pdo, $order_id, $old_status, $new_status);

        // 5. Send Email Confirmation to Customer if payment is successful
        if ($new_status == 'Disewa' && $old_status != 'Disewa') {
            try {
                // Fetch full details for the email (Join with motor_models)
                $stmt_full = $pdo->prepare("SELECT b.*, mm.brand, mm.model 
                                           FROM bookings b 
                                           JOIN motors m ON b.motor_id = m.id 
                                           JOIN motor_models mm ON m.model_id = mm.id
                                           WHERE b.midtrans_id = ?");
                $stmt_full->execute([$order_id]);
                $booking_full = $stmt_full->fetch();
                
                if ($booking_full && !empty($booking_full['email'])) {
                    require_once '../config/mailer.php';
                    send_booking_confirmation($booking_full['email'], $booking_full);
                }
            } catch (Exception $e) {
                error_log("Failed to send booking confirmation email: " . $e->getMessage());
            }
        }

        echo json_encode(['status' => 'success', 'booking_status' => $new_status]);
    } else {
        echo json_encode(['status' => 'error', 'HTTP' => $httpCode, 'Response' => $result]);
    }

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
