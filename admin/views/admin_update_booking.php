<?php
// admin_update_booking.php
// Handles admin actions to confirm or cancel bookings.
// Requirements:
// - POST only
// - Admin user (checked via $_SESSION['is_admin'])
// - CSRF token validation
// - Transactional updates (bookings and cars)

// No output before redirects
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include DB connection (adjust path relative to this file)
require_once __DIR__ . '/../../config/db_connect.php';

// Only accept POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: admin_manage_bookings.php?error=invalid_method');
    exit();
}

// Check admin
if (empty($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header('Location: /tyqgwsgr_DbXe/app/views/dangnhap.php');
    exit();
}

// CSRF token
$posted_token = $_POST['csrf_token'] ?? '';
if (empty($posted_token) || empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $posted_token)) {
    header('Location: admin_manage_bookings.php?error=csrf');
    exit();
}

// Get inputs
$booking_id = isset($_POST['booking_id']) ? intval($_POST['booking_id']) : 0;
$action = isset($_POST['action']) ? trim($_POST['action']) : '';

if ($booking_id <= 0 || !in_array($action, ['confirm', 'cancel'], true)) {
    header('Location: admin_manage_bookings.php?error=invalid_request');
    exit();
}

// Use transaction
mysqli_begin_transaction($conn);
try {
    // Lock booking row
    $sql = "SELECT booking_id, car_id, status FROM bookings WHERE booking_id = ? FOR UPDATE";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        throw new Exception('Prepare failed: ' . $conn->error);
    }
    $stmt->bind_param('i', $booking_id);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res->num_rows === 0) {
        $stmt->close();
        throw new Exception('not_found');
    }
    $booking = $res->fetch_assoc();
    $current_status = $booking['status'];
    $car_id = intval($booking['car_id']);
    $stmt->close();

    // Only allow action when booking is Pending
    if (strtolower($current_status) !== 'pending') {
        throw new Exception('already_processed');
    }

    if ($action === 'confirm') {
        // Mark booking confirmed and make car unavailable
        $u1 = $conn->prepare("UPDATE bookings SET status = 'Confirmed' WHERE booking_id = ?");
        if ($u1 === false) throw new Exception('Prepare failed: ' . $conn->error);
        $u1->bind_param('i', $booking_id);
        if (!$u1->execute()) throw new Exception('Update booking failed: ' . $u1->error);
        $u1->close();

        $u2 = $conn->prepare("UPDATE cars SET is_available = 0 WHERE car_id = ?");
        if ($u2 === false) throw new Exception('Prepare failed: ' . $conn->error);
        $u2->bind_param('i', $car_id);
        if (!$u2->execute()) throw new Exception('Update car failed: ' . $u2->error);
        $u2->close();

        mysqli_commit($conn);
        header('Location: admin_manage_bookings.php?success=confirmed');
        exit();
    }

    if ($action === 'cancel') {
        // Mark booking cancelled. Car remains available (since booking was pending)
        $u1 = $conn->prepare("UPDATE bookings SET status = 'Cancelled' WHERE booking_id = ?");
        if ($u1 === false) throw new Exception('Prepare failed: ' . $conn->error);
        $u1->bind_param('i', $booking_id);
        if (!$u1->execute()) throw new Exception('Update booking failed: ' . $u1->error);
        $u1->close();

        mysqli_commit($conn);
        header('Location: admin_manage_bookings.php?success=cancelled');
        exit();
    }

    // Should not reach here
    throw new Exception('unknown_action');

} catch (Exception $e) {
    mysqli_rollback($conn);
    $msg = $e->getMessage();
    // Map some internal codes to user-friendly redirects
    if ($msg === 'not_found') {
        header('Location: admin_manage_bookings.php?error=not_found');
    } elseif ($msg === 'already_processed') {
        header('Location: admin_manage_bookings.php?error=already_processed');
    } else {
        header('Location: admin_manage_bookings.php?error=server_error');
    }
    exit();
}

?>