<?php
header('Content-Type: application/json');
require_once 'config/config.php';
require_once 'config/midtrans.php';

// Helper function to send JSON response
function json_response($status, $message, $data = []) {
    echo json_encode(['status' => $status, 'message' => $message, 'data' => $data]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response('error', 'Method not allowed');
}

if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
    json_response('error', 'Invalid security token (CSRF). Silakan refresh halaman.');
}

// 1. Validate Input
$required = ['motor_id', 'name', 'phone', 'duration', 'pickup_date', 'total_price', 'nik', 'address', 'emergency_contact', 'emergency_phone'];
foreach ($required as $field) {
    if (empty($_POST[$field])) {
        json_response('error', "Field $field is required");
    }
}

$model_id = (int)$_POST['motor_id']; // This is actually the model ID from frontend
$name = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
$phone = filter_var($_POST['phone'], FILTER_SANITIZE_STRING);
$duration_frontend = (int)$_POST['duration'];
$pickup_datetime_str = $_POST['pickup_date']; // Format: Y-m-d H:i:s
$total_price_frontend = (int)$_POST['total_price'];
$email = filter_var($_POST['email'] ?? 'customer@example.com', FILTER_SANITIZE_EMAIL);
$location = filter_var($_POST['location'] ?? 'Ambil di Tempat', FILTER_SANITIZE_STRING);

// New Fields
$nik = filter_var($_POST['nik'], FILTER_SANITIZE_STRING);
$address = filter_var($_POST['address'], FILTER_SANITIZE_STRING);
$emergency_contact = filter_var($_POST['emergency_contact'], FILTER_SANITIZE_STRING);
$emergency_phone = filter_var($_POST['emergency_phone'], FILTER_SANITIZE_STRING);

try {
    // 2. Security Checks & Backend Re-calculation
    
    // Fetch Motor Model details
    $stmt_model = $pdo->prepare("SELECT brand, model, price_per_day FROM motor_models WHERE id = ? AND is_active = 1");
    $stmt_model->execute([$model_id]);
    $model = $stmt_model->fetch();
    
    if (!$model) throw new Exception("Model armada tidak ditemukan.");

    // Find an available Physical Unit for this model
    // CRITICAL: Exclude units that are currently assigned to a 'Pending' booking
    $stmt_unit = $pdo->prepare("SELECT m.id FROM motors m 
                               WHERE m.model_id = ? 
                               AND m.status = 'Tersedia' 
                               AND m.is_active = 1 
                               AND NOT EXISTS (SELECT 1 FROM bookings b WHERE b.motor_id = m.id AND b.status = 'Pending')
                               LIMIT 1");
    $stmt_unit->execute([$model_id]);
    $unit = $stmt_unit->fetch();

    if (!$unit) throw new Exception("Maaf, semua unit model " . $model['brand'] . " " . $model['model'] . " baru saja terbooking.");
    
    $assigned_motor_id = $unit['id']; // This is the physical bike ID

    // Recalculate Duration (Logika 24 Jam)
    // We assume the frontend passes Tanggal Ambil and Tanggal Kembali, but here we only have duration.
    // Wait, the frontend logic passed `pickupDate` (hidden input) which is `startPicker.formatDate(selectedDates[0], "Y-m-d H:i:s")`.
    // And it passed `duration` based on (end - start).
    // To be perfectly safe, we should ideally have both start and end datetime on backend.
    // However, since we only have duration and start, we use the duration but we must trust the math.
    // Let's stick to validating the price calculation based on the duration provided.
    
    // Recalculate Duration (Logika 24 Jam)
    $calculated_base_price = $model['price_per_day'] * $duration_frontend;
    $final_total_price = $calculated_base_price;
    $final_discount = 0;
    
    // Promo handling validation
    $promo_id = !empty($_POST['promo_id']) ? (int)$_POST['promo_id'] : null;
    if ($promo_id) {
        $stmt_promo = $pdo->prepare("SELECT * FROM promos WHERE id = ? AND is_active = 1 AND valid_from <= CURRENT_DATE AND valid_to >= CURRENT_DATE");
        $stmt_promo->execute([$promo_id]);
        $promo = $stmt_promo->fetch();
        
        if (!$promo) {
            throw new Exception("Promo tidak valid atau sudah kadaluarsa.");
        }
        
        if ($promo['usage_limit'] !== null && $promo['used_count'] >= $promo['usage_limit']) {
            throw new Exception("Kuota promo sudah habis.");
        }
        
        // Recalculate discount
        if ($promo['discount_type'] === 'Percentage') {
            $final_discount = ($calculated_base_price * $promo['discount_value']) / 100;
        } else {
            $final_discount = $promo['discount_value'];
        }
        
        $final_total_price = $calculated_base_price - $final_discount;
    }

    // Safety check: Final price must match or be very close (rounding) to frontend
    if (abs($final_total_price - $total_price_frontend) > 10) {
        throw new Exception("Terjadi kesalahan validasi harga. Silakan coba lagi.");
    }

    // Calculate return date for DB
    $pickup_dt = new DateTime($pickup_datetime_str);
    $return_dt = clone $pickup_dt;
    $return_dt->modify("+$duration_frontend days");
    $return_date = $return_dt->format('Y-m-d H:i:s');
    $pickup_date_only = $pickup_dt->format('Y-m-d H:i:s');

    // 3. Save Booking to Database
    $pdo->beginTransaction();

    // Use $assigned_motor_id (the physical bike)
    $stmt = $pdo->prepare("INSERT INTO bookings (motor_id, name, phone, email, nik, address, emergency_contact, emergency_phone, pickup_date, return_date, duration, location, total_price, promo_id, discount_amount, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pending')");
    $stmt->execute([$assigned_motor_id, $name, $phone, $email, $nik, $address, $emergency_contact, $emergency_phone, $pickup_date_only, $return_date, $duration_frontend, $location, $final_total_price, $promo_id, $final_discount]);
    
    $booking_id = $pdo->lastInsertId();
    
    // Increment promo usage counter
    if ($promo_id) {
        $promo_update = $pdo->prepare("UPDATE promos SET used_count = used_count + 1 WHERE id = ?");
        $promo_update->execute([$promo_id]);
    }
    
    $order_id = 'ORDER-' . $booking_id . '-' . time(); 

    // 4. Request Snap Token from Midtrans
    $transaction_details = [
        'order_id' => $order_id,
        'gross_amount' => (int)$final_total_price,
    ];

    $item_details = [
        [
            'id' => 'UNIT-' . $assigned_motor_id,
            'price' => (int)$final_total_price,
            'quantity' => 1,
            'name' => $model['brand'] . " " . $model['model'] . " ($duration_frontend Hari)"
        ]
    ];

    $customer_details = [
        'first_name' => $name,
        'email' => $email,
        'phone' => $phone,
        'billing_address' => [
            'first_name' => $name,
            'address' => $address,
            'phone' => $phone
        ]
    ];

    $params = [
        'transaction_details' => $transaction_details,
        'item_details' => $item_details,
        'customer_details' => $customer_details
    ];

    $payload = json_encode($params);
    $server_key = MIDTRANS_SERVER_KEY;
    
    if (strpos($server_key, 'your-server-key') !== false) {
       throw new Exception("Midtrans Server Key belum disetting!"); 
    }

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, MIDTRANS_API_URL);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Accept: application/json',
        'Authorization: Basic ' . base64_encode($server_key . ':')
    ]);

    $result = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode !== 201 && $httpCode !== 200) {
        throw new Exception("Midtrans Error ($httpCode): " . $result);
    }

    $response = json_decode($result, true);
    if (isset($response['token'])) {
        $snap_token = $response['token'];
        
        $update = $pdo->prepare("UPDATE bookings SET snap_token = ?, midtrans_id = ? WHERE id = ?");
        $update->execute([$snap_token, $order_id, $booking_id]);
        
        $pdo->commit();
        
        json_response('success', 'Token generated', ['token' => $snap_token, 'order_id' => $order_id]);
    } else {
        throw new Exception("Gagal mendapatkan Snap Token");
    }

} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    json_response('error', $e->getMessage());
}
?>
