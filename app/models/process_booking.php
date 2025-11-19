<?php
include '../../config/db_connect.php';

// Kiểm tra phương thức request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('HTTP/1.1 405 Method Not Allowed');
    exit('Method not allowed');
}

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    header('HTTP/1.1 401 Unauthorized');
    exit('Unauthorized');
}

// Lấy và validate dữ liệu
$car_id = intval($_POST['car_id']);
// Lấy user_id ưu tiên từ session (an toàn hơn). Nếu không có, fallback sang POST.
$user_id = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : intval($_POST['user_id'] ?? 0);
$pickup_date = date('Y-m-d H:i:s', strtotime($_POST['pickup_date']));
$return_date = date('Y-m-d H:i:s', strtotime($_POST['return_date']));
$location = trim($_POST['location']);
$has_insurance = isset($_POST['insurance']) ? 1 : 0;
$has_extra_insurance = isset($_POST['extra_insurance']) ? 1 : 0;

// Validate dates
if (strtotime($pickup_date) >= strtotime($return_date)) {
    // Updated path after MVC reorganization: views contain markup
    header('Location: ../views/booking.php?car_id=' . $car_id . '&error=invalid_dates');
    exit();
}

if (strtotime($pickup_date) < time()) {
    header('Location: ../views/booking.php?car_id=' . $car_id . '&error=past_date');
    exit();
}

// Kiểm tra xem xe có sẵn không
$sql = "SELECT booking_id FROM bookings 
    WHERE car_id = ? 
    AND status != 'cancelled'
    AND (
        (start_date BETWEEN ? AND ?) 
        OR (end_date BETWEEN ? AND ?)
        OR (start_date <= ? AND end_date >= ?)
    )";

$stmt = $conn->prepare($sql);
if ($stmt === false) {
    error_log('DB prepare failed (availability check): ' . $conn->error . ' -- SQL: ' . $sql);
    header('Location: ../views/booking.php?car_id=' . $car_id . '&error=system_error');
    exit();
}

$stmt->bind_param('issssss', $car_id, $pickup_date, $return_date, $pickup_date, $return_date, $pickup_date, $return_date);
$stmt->execute();
$conflicting_bookings = $stmt->get_result();

if ($conflicting_bookings->num_rows > 0) {
    header('Location: ../views/booking.php?car_id=' . $car_id . '&error=car_unavailable');
    exit();
}

// Tính tổng tiền
$sql = "SELECT price_per_day FROM cars WHERE car_id = ?";
$stmt = $conn->prepare($sql);
if ($stmt === false) {
    error_log('DB prepare failed (get price): ' . $conn->error . ' -- SQL: ' . $sql);
    header('Location: ../views/booking.php?car_id=' . $car_id . '&error=system_error');
    exit();
}

$stmt->bind_param('i', $car_id);
$stmt->execute();
$result = $stmt->get_result();
$car = $result->fetch_assoc();

$days = ceil((strtotime($return_date) - strtotime($pickup_date)) / (86400));
$total_price = $car['price_per_day'] * $days;

if ($has_insurance) {
    $total_price += 93564 * $days;
}
if ($has_extra_insurance) {
    $total_price += 100000 * $days;
}

// Trừ khuyến mãi
$total_price -= 120000;

// Xử lý phương thức thanh toán
$payment_method = $_POST['paymentMethod'] ?? 'full';
$amount_paid = $payment_method === 'deposit' ? ceil($total_price * 0.4) : $total_price;
$payment_status = $payment_method === 'deposit' ? 'partial' : 'full';

// Debug: log values for diagnostics
error_log(sprintf(
    "Debug values: user_id=%d, car_id=%d, dates='%s' to '%s', loc='%s', ins=%d, extra=%d, price=%.2f",
    $user_id, $car_id, $pickup_date, $return_date, $location, 
    $has_insurance, $has_extra_insurance, $total_price
));

// Tạo mã thanh toán
$payment_reference = sprintf('THUEXE%dU%d%s', $car_id, $user_id, date('YmdHis'));

// Lưu đơn đặt xe với đầy đủ thông tin
$sql = "INSERT INTO bookings (
        user_id, car_id, start_date, end_date,
        total_price, status, payment_status, amount_paid,
        has_insurance, has_extra_insurance, location, payment_reference
    ) VALUES (?, ?, ?, ?, ?, 'Pending', ?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);
if ($stmt === false) {
    error_log('DB prepare failed (insert booking) with error: ' . $conn->error . ' -- SQL: ' . $sql);
    header('Location: ../views/booking.php?car_id=' . $car_id . '&error=system_error');
    exit();
}

try {
    if (!$stmt->bind_param(
      'iissdsdiiss', // <--- Sửa thành chuỗi này (11 ký tự)
        $user_id, $car_id, $pickup_date, $return_date,
        $total_price, $payment_status, $amount_paid,
        $has_insurance, $has_extra_insurance, $location, $payment_reference
    )) {
        error_log('bind_param failed with error: ' . $stmt->error);
        throw new Exception('Parameter binding failed');
    }
    
    if (!$stmt->execute()) {
        error_log('execute failed with error: ' . $stmt->error);
        throw new Exception('Query execution failed');
    }
    
    $booking_id = $stmt->insert_id;
    
    // Cập nhật trạng thái xe
    $updateSql = "UPDATE cars SET is_available = 0 WHERE car_id = ?";
    $updateStmt = $conn->prepare($updateSql);
    if ($updateStmt === false) {
        error_log('DB prepare failed (update car): ' . $conn->error . ' -- SQL: ' . $updateSql);
    } else {
        $updateStmt->bind_param('i', $car_id);
        $updateStmt->execute();
        $updateStmt->close();
    }
    
    // Redirect to view after booking created
    header('Location: ../views/booking_success.php?booking_id=' . $booking_id);
} catch (Exception $e) {
    error_log('Booking error: ' . $e->getMessage());
    header('Location: ../views/booking.php?car_id=' . $car_id . '&error=system_error');
} finally {
    $stmt->close();
    $conn->close();
}
exit();
?>