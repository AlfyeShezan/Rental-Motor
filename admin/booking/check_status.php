<?php
require_once '../../config/config.php';
require_once '../../admin/template/auth.php';
require_once '../../config/helper.php';
require_once '../../config/midtrans.php';

$id = $_GET['id'] ?? 0;

if (!$id) {
    redirect_with_alert('index.php', 'ID Booking tidak valid.', 'danger');
}

try {
    // 1. Get Booking Data
    $stmt = $pdo->prepare("SELECT * FROM bookings WHERE id = ?");
    $stmt->execute([$id]);
    $booking = $stmt->fetch();

    if (!$booking) {
        redirect_with_alert('index.php', 'Booking tidak ditemukan.', 'danger');
    }

    // 2. Determine Order ID to check
    // If we saved midtrans_id, use that. Otherwise use ORDER-{id}-... pattern if stored, 
    // or try to find it. Since we didn't store the exact ORDER-ID in the database in previous steps (my bad),
    // we might have an issue. 
    // Wait, process_booking.php generated 'ORDER-' . $booking_id . '-' . time();
    // But it didn't update the `bookings` table with this Order ID!
    // It only updated `snap_token`.
    
    // CRITICAL FIX: We need the Order ID to check status. 
    // If we don't have it, we can't check status efficiently unless we search by something else?
    // Midtrans API requires Order ID.
    
    // Workaround: 
    // 1. Check if we have `midtrans_id` column (added in add_snap_token.php)? Yes.
    // 2. If it's empty, we might be in trouble for *past* transactions.
    // 3. BUT, for the transaction the user *just* made, the `notification_handler.php` SHOULD have theoretically received it?
    // No, localhost.
    
    // Let's try to fetch status using the `snap_token`? No, API uses Order ID.
    
    // Alternative: The user just made a booking. The Order ID format is `ORDER-ID-TIMESTAMP`.
    // We don't know the timestamp. DARN.
    
    // WAIT! In `process_booking.php`, we saved `snap_token`.
    // Maybe we can get status from Snap Token? No, usually Order ID.
    
    // Let's look at `bookings` table again. 
    // If the user *just* made a booking, and we didn't save Order ID, we can't check it easily.
    
    // HOWEVER, for future bookings, we MUST save the Order ID.
    // Let's patch `process_booking.php` to save Order ID in `midtrans_id` column.
    
    // For NOW (the current stuck transaction), we might fail to check it if we don't know the Order ID.
    // But maybe the `midtrans_id` column IS `order_id`?
    // In `add_snap_token.php`, I added `midtrans_id`.
    // In `process_booking.php`, did I update it? 
    // Let me check `process_booking.php` content again.
    
    // Checking `process_booking.php` in my memory/context...
    // It did: $update = $pdo->prepare("UPDATE bookings SET snap_token = ? WHERE id = ?");
    // It did NOT save order_id. 
    
    // OK, I must fix `process_booking.php` first to save the Order ID.
    // For the existing transaction, the user is stuck. 
    // I can try to guess the Order ID? No, timestamp is random.
    
    // ACTUALLY, there is a "Get Status by Order ID" API.
    // Is there a "Get Status by Transaction ID"? Yes. 
    // Do we have Transaction ID? No.

    // Is there a way to see recent transactions in Midtrans Dashboard? Yes using User's Screenshot!
    // User screenshot showed Order ID: `ORDER-2-...`? No, it showed `A120260216082211gV9lgzAwLkID` which looks like a GO-PAY reference or something.
    // Wait, let's look at the user request again.
    // "https://merchants-app.sbx.midtrans.com/v4/qris/gopay/A120260216082211gV9lgzAwLkID/qr-code"
    
    // Ok, I will implement the check script assuming we HAVE the Order ID.
    // If the field is empty, I'll show an error "Order ID not found in database".
    
    $order_id_to_check = $booking['midtrans_id']; // This column exists now
    
    if (empty($order_id_to_check)) {
        // Fallback: If we stored it in another column? No.
        // Try to construct it? impossible due to time().
        
        // TEMPORARY HACK for this specific user session if they just made it?
        // No, can't relying on that.
        
        // We will just report error.
        $msg = "Order ID tidak ditemukan.";
        redirect_with_alert('index.php', $msg, 'warning');
    }

    // 3. Call Midtrans API
    $serverKey = MIDTRANS_SERVER_KEY;
    $isProduction = MIDTRANS_IS_PRODUCTION;
    $baseUrl = $isProduction ? 'https://api.midtrans.com/v2' : 'https://api.sandbox.midtrans.com/v2';
    
    $url = "$baseUrl/$order_id_to_check/status";
    
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
    
    if ($httpCode != 200) {
        $error = json_decode($result, true);
        $errMsg = $error['status_message'] ?? 'Gagal menghubungi Midtrans';
        redirect_with_alert('index.php', "Error Midtrans: $errMsg", 'danger');
    }

    $response = json_decode($result, true);
    $transaction_status = $response['transaction_status'];
    $fraud_status = $response['fraud_status'] ?? '';

    // 4. Map Status
    $new_status = 'Pending';
    if ($transaction_status == 'capture') {
        if ($fraud_status == 'challenge') {
            $new_status = 'Pending';
        } else {
            $new_status = 'Disewa';
        }
    } else if ($transaction_status == 'settlement') {
        $new_status = 'Disewa';
    } else if ($transaction_status == 'deny' || $transaction_status == 'cancel' || $transaction_status == 'expire') {
        $new_status = 'Batal';
    } else if ($transaction_status == 'pending') {
        $new_status = 'Pending';
    }

    // 5. Update Database
    require_once '../../config/stock_helper.php';
    update_motor_stock($pdo, $id, $booking['status'], $new_status);
    
    $update = $pdo->prepare("UPDATE bookings SET status = ? WHERE id = ?");
    $update->execute([$new_status, $id]);

    $message = "Status berhasil diperbarui menjadi: <strong>$new_status</strong>";
    $type = ($new_status == 'Disewa') ? 'success' : 'info';
    
    // Preserve filter parameters
    $redirect_url = 'index.php';
    if (isset($_SERVER['HTTP_REFERER'])) {
        $referer_query = parse_url($_SERVER['HTTP_REFERER'], PHP_URL_QUERY);
        if ($referer_query) {
            parse_str($referer_query, $params);
            $filter_params = [];
            if (!empty($params['status'])) $filter_params['status'] = $params['status'];
            if (!empty($params['date'])) $filter_params['date'] = $params['date'];
            if (!empty($filter_params)) {
                $redirect_url .= '?' . http_build_query($filter_params);
            }
        }
    }
    
    redirect_with_alert($redirect_url, $message, $type);

} catch (Exception $e) {
    redirect_with_alert('index.php', "Error: " . $e->getMessage(), 'danger');
}
?>
