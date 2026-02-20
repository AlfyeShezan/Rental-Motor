<?php
require_once 'config/config.php';
require_once 'config/midtrans.php';

try {
    $notif = new \stdClass(); // Placeholder if library not loaded
    
    // Check if we can use the Midtrans PHP Library? 
    // Since we are using pure REST / Manual Curl in process_booking, we'll parse JSON manually here.
    
    $json_result = file_get_contents('php://input');
    $result = json_decode($json_result, true);

    if (!$result) {
        throw new Exception("No data received");
    }

    $order_id = $result['order_id'];
    $transaction_status = $result['transaction_status'];
    $fraud_status = $result['fraud_status'] ?? '';
    
    // Extract ID from Order ID (Format: ORDER-{id}-{timestamp})
    $parts = explode('-', $order_id);
    if (count($parts) < 2) throw new Exception("Invalid Order ID format");
    $booking_id = $parts[1];

    $new_status = 'Pending';
    
    if ($transaction_status == 'capture') {
        if ($fraud_status == 'challenge') {
            $new_status = 'Pending';
        } else {
            $new_status = 'Disewa'; // Paid
        }
    } else if ($transaction_status == 'settlement') {
        $new_status = 'Disewa'; // Paid
    } else if ($transaction_status == 'pending') {
        $new_status = 'Pending';
    } else if ($transaction_status == 'deny') {
        $new_status = 'Batal';
    } else if ($transaction_status == 'expire') {
        $new_status = 'Batal';
    } else if ($transaction_status == 'cancel') {
        $new_status = 'Batal';
    }

    // 2. Fetch Existing Status for stock management
    $stmt_old = $pdo->prepare("SELECT status FROM bookings WHERE id = ?");
    $stmt_old->execute([$booking_id]);
    $old_status = $stmt_old->fetchColumn() ?: 'Pending';

    // 3. Update Database Status
    $stmt = $pdo->prepare("UPDATE bookings SET status = ? WHERE id = ?");
    $stmt->execute([$new_status, $booking_id]);
    
    // 4. Manage Unit Status via Helper
    require_once 'config/stock_helper.php';
    update_motor_stock($pdo, $booking_id, $old_status, $new_status);
    
    http_response_code(200);
    echo "Notification processed. Order ID: $order_id Set to: $new_status";

} catch (Exception $e) {
    http_response_code(500);
    echo "Error: " . $e->getMessage();
}
?>
