<?php
/**
 * Stock Management Helper Functions
 * Manages motor stock based on booking status changes
 */

function update_motor_stock($pdo, $booking_id, $old_status, $new_status) {
    try {
        // Get booking motor_id
        $stmt = $pdo->prepare("SELECT motor_id FROM bookings WHERE id = ?");
        $stmt->execute([$booking_id]);
        $motor_id = $stmt->fetchColumn();
        
        if (!$motor_id) {
            return false;
        }

        // Determine status change for the physical unit
        $new_unit_status = null;
        
        // Mark as "Disewa" when status changes TO "Disewa"
        if ($new_status == 'Disewa' && $old_status != 'Disewa') {
            $new_unit_status = 'Disewa';
        }
        
        // Restore to "Tersedia" when status changes FROM "Disewa" to "Selesai" or "Batal"
        if ($old_status == 'Disewa' && ($new_status == 'Selesai' || $new_status == 'Batal')) {
            $new_unit_status = 'Tersedia';
        }
        
        // Apply status change to the specific physical bike
        if ($new_unit_status !== null) {
            $update = $pdo->prepare("UPDATE motors SET status = ? WHERE id = ?");
            $update->execute([$new_unit_status, $motor_id]);
            return true;
        }
        
        return true;
    } catch (Exception $e) {
        error_log("Stock update error: " . $e->getMessage());
        return false;
    }
}

function update_motor_stock_by_order_id($pdo, $order_id, $new_status) {
    try {
        // Get current booking status and motor_id
        $stmt = $pdo->prepare("SELECT id, motor_id, status FROM bookings WHERE midtrans_id = ?");
        $stmt->execute([$order_id]);
        $booking = $stmt->fetch();
        
        if (!$booking) {
            return false;
        }
        
        $old_status = $booking['status'];
        $booking_id = $booking['id'];
        
        return update_motor_stock($pdo, $booking_id, $old_status, $new_status);
    } catch (Exception $e) {
        error_log("Stock update error: " . $e->getMessage());
        return false;
    }
}

function update_motor_stock_by_order_id_with_old_status($pdo, $order_id, $old_status, $new_status) {
    try {
        $stmt = $pdo->prepare("SELECT id, motor_id FROM bookings WHERE midtrans_id = ?");
        $stmt->execute([$order_id]);
        $booking = $stmt->fetch();
        
        if (!$booking) {
            return false;
        }
        
        $booking_id = $booking['id'];
        return update_motor_stock($pdo, $booking_id, $old_status, $new_status);
    } catch (Exception $e) {
        error_log("Stock update error: " . $e->getMessage());
        return false;
    }
}
?>
