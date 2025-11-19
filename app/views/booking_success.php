<?php
session_start();
require_once __DIR__ . '/../../config/db_connect.php';

if (!isset($_GET['booking_id']) || !isset($_SESSION['user_id'])) {
    header('Location: trangchu.php');
    exit();
}

$booking_id = intval($_GET['booking_id']);
$user_id = intval($_SESSION['user_id']);

// Lấy chi tiết đặt xe và thông tin xe
$stmt = $conn->prepare(
    "SELECT b.*, CONCAT(c.brand, ' ', c.model) AS car_name, c.image_url, c.price_per_day,
            DATEDIFF(b.end_date, b.start_date) as rental_days
     FROM bookings b
     JOIN cars c ON b.car_id = c.car_id
     WHERE b.booking_id = ? AND b.user_id = ?"
);
if ($stmt === false) {
    error_log('Prepare failed booking_success: ' . $conn->error);
    header('Location: trangchu.php');
    exit();
}
$stmt->bind_param('ii', $booking_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: trangchu.php');
    exit();
}

$booking = $result->fetch_assoc();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đặt xe thành công</title>
    <link rel="stylesheet" href="http://localhost:8080/tyqgwsgr_DbXe/public/css/main.css">
</head>
<body>
<?php include __DIR__ . '/partials/header.php'; ?>

<div class="container booking-success">
    <div class="success-message" style="background: #ecfdf5; border:1px solid #bbf7d0; padding:18px; border-radius:10px; margin-top:18px;">
        <h1 style="color:#065f46;">🎉 Đặt xe thành công!</h1>
        <p>Cảm ơn bạn đã đặt xe. Mã đơn của bạn: <strong>#<?php echo htmlspecialchars($booking['booking_id']); ?></strong></p>
    </div>

    <div class="booking-details" style="background:var(--surface); padding:18px; border-radius:10px; margin-top:14px; box-shadow:0 8px 24px rgba(2,6,23,0.06);">
        <h2>Chi tiết đơn đặt xe</h2>
        <div style="display:flex; gap:14px; align-items:center;">
            <img src="<?php echo htmlspecialchars($booking['image_url']); ?>" alt="<?php echo htmlspecialchars($booking['car_name']); ?>" style="width:180px; height:120px; object-fit:cover; border-radius:8px;">
            <div>
                <h3><?php echo htmlspecialchars($booking['car_name']); ?></h3>
                <p class="booking-id">Mã đơn: #<?php echo htmlspecialchars($booking['booking_id']); ?></p>
                <p><strong>Ngày nhận:</strong> <?php echo date('d/m/Y H:i', strtotime($booking['start_date'])); ?></p>
                <p><strong>Ngày trả:</strong> <?php echo date('d/m/Y H:i', strtotime($booking['end_date'])); ?></p>
            </div>
        </div>

        <div style="margin-top:12px; display:grid; grid-template-columns:1fr 1fr; gap:12px;">
            <div><strong>Tổng tiền:</strong><div><?php echo number_format($booking['total_price']); ?> đ</div></div>
            <div><strong>Đã thanh toán:</strong><div><?php echo number_format($booking['amount_paid']); ?> đ</div></div>
            <div><strong>Phương thức:</strong><div><?php echo htmlspecialchars($booking['payment_status']); ?></div></div>
            <div><strong>Trạng thái:</strong><div><?php echo htmlspecialchars($booking['status']); ?></div></div>
        </div>

        <div style="margin-top:14px;">
            <p>Chúng tôi sẽ xác nhận đơn đặt xe của bạn. Vui lòng chuẩn bị giấy tờ tùy thân khi nhận xe.</p>
            <a href="trangchu.php" class="btn" style="display:inline-block; margin-top:8px;">Về trang chủ</a>
        </div>
    </div>
</div>

<?php
$conn->close();
?>
</body>
</html>