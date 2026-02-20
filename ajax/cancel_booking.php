<?php
require_once '../config/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$order_id = $data['order_id'] ?? '';

if (empty($order_id)) {
    echo json_encode(['status' => 'error', 'message' => 'Order ID required']);
    exit;
}

try {
    // Determine if order_id is the 'ORDER-ID-TIMESTAMP' string or just the ID?
    // In process_booking.php we generate: 'ORDER-' . $booking_id . '-' . time()
    // and we save it in midtrans_id column.
    
    // So we search by midtrans_id = $order_id
    $stmt = $pdo->prepare("UPDATE bookings SET status = 'Batal' WHERE midtrans_id = ?");
    $stmt->execute([$order_id]);
    
    echo json_encode(['status' => 'success']);

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
