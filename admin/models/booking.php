<?php
// booking.php
// (Giả sử bạn đã có tệp này với đường dẫn đúng)
// Từ admin/models/ đi lùi 2, vào config/
include '../../config/db_connect.php'; 

// --- 1. (MỚI) THÔNG TIN TÀI KHOẢN NGÂN HÀNG CỦA BẠN ---
// Thay thế bằng thông tin thật của bạn
define("BANK_BIN", "970488");        // Mã BIN ngân hàng (ví dụ: 970488 là Sacombank)
define("ACCOUNT_NUMBER", "0123456789"); // Số tài khoản của bạn
define("ACCOUNT_NAME", "NGUYEN VAN A"); // Tên chủ tài khoản
// ----------------------------------------------------

// 2. Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    header("Location: dangnhap.php");
    exit();
}
$user_id = $_SESSION['user_id'];

// 3. Lấy thông tin xe (như cũ)
$car_id = $_GET['car_id'] ?? 0;
if (!is_numeric($car_id) || $car_id <= 0) { die("ID xe không hợp lệ!"); }

$stmt_car = $conn->prepare("SELECT * FROM cars WHERE car_id = ? AND is_available = 1");
$stmt_car->bind_param("i", $car_id);
$stmt_car->execute();
$result_car = $stmt_car->get_result();
if ($result_car->num_rows == 0) { die("Xe này không tồn tại hoặc đã được thuê."); }
$car = $result_car->fetch_assoc();
$stmt_car->close();

$message = "";
$show_payment_qr = false; // Biến kiểm soát hiển thị
$qr_data_cho_js = [];     // Mảng dữ liệu cho JS

// 4. XỬ LÝ KHI NGƯỜI DÙNG ĐẶT XE (POST)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];

    if ($end_date <= $start_date) {
        $message = "<div class='msg error'>Ngày trả xe phải sau ngày nhận xe.</div>";
    } else {
        // Tính toán (như cũ)
        $date1 = new DateTime($start_date);
        $date2 = new DateTime($end_date);
        $diff = $date2->diff($date1);
        $total_days = ($diff->days == 0) ? 1 : $diff->days;
        $total_price = $total_days * $car['price_per_day'];

        try {
            // Chèn vào bảng `bookings`
            $sql_insert_booking = "INSERT INTO bookings (user_id, car_id, start_date, end_date, total_price, status) 
                                   VALUES (?, ?, ?, ?, ?, 'Pending')";
            $stmt_insert = $conn->prepare($sql_insert_booking);
            $stmt_insert->bind_param("iissd", $user_id, $car_id, $start_date, $end_date, $total_price);
            
            if ($stmt_insert->execute()) {
                // ---- (PHẦN QUAN TRỌNG NHẤT) ----
                // Lấy ID của đơn hàng vừa tạo
                $booking_id = $stmt_insert->insert_id; 
                
                // 1. Tạo nội dung duy nhất
                $unique_memo = "XE" . $booking_id; // Ví dụ: "XE17" (Không bao giờ trùng lặp)
                
                // 2. Bật cờ để hiển thị QR
                $show_payment_qr = true;
                
                // 3. Chuẩn bị dữ liệu cho JS
                $qr_data_cho_js = [
                    'amount' => $total_price,
                    'memo' => $unique_memo
                ];
                
                $message = "<div class='msg success'>Đơn hàng #{$booking_id} đã được tạo. Vui lòng thanh toán để hoàn tất.</div>";
                // ------------------------------------

            } else {
                $message = "<div class='msg error'>Lỗi khi đặt xe: " . $stmt_insert->error . "</div>";
            }
            $stmt_insert->close();
        } catch (Exception $e) {
            $message = "<div class='msg error'>Lỗi: " . $e->getMessage() . "</div>";
        }
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Đặt xe - <?php echo htmlspecialchars($car['brand']); ?></title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f7f6; margin: 0; }
        .header { background-color: #004a99; color: white; padding: 15px 30px; }
        .header a { color: white; text-decoration: none; font-weight: bold; }
        .container { width: 90%; max-width: 800px; margin: 20px auto; background-color: #fff; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); display: flex; flex-wrap: wrap; }
        .car-image { flex: 1; min-width: 300px; }
        .car-image img { width: 100%; height: auto; border-radius: 8px 0 0 8px; }
        .booking-details { flex: 1; padding: 25px; min-width: 300px; }
        .booking-details h1 { color: #34495e; margin-top: 0; }
        .car-info p { margin: 10px 0; color: #555; }
        .car-price { font-size: 1.5rem; font-weight: bold; color: #28a745; margin: 15px 0; }
        .booking-form .form-group { margin-bottom: 15px; }
        .booking-form label { display: block; margin-bottom: 5px; font-weight: bold; }
        .booking-form input[type="date"] { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; }
        .btn-submit { background-color: #e67e22; color: white; padding: 12px 20px; border: none; border-radius: 4px; cursor: pointer; font-size: 1rem; width: 100%; }
        .msg { padding: 10px; border-radius: 4px; margin-bottom: 15px; }
        .msg.success { background-color: #d4edda; color: #155724; }
        .msg.error { background-color: #f8d7da; color: #721c24; }
        
        /* (MỚI) CSS CHO KHUNG QR */
        .qr-container {
            border: 2px dashed #007bff;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            background: #f8f9fa;
        }
        .qr-container img {
            width: 300px; /* Kích thước QR */
            max-width: 100%;
            border: 1px solid #ddd;
        }
        .qr-info {
            text-align: left;
            margin-top: 15px;
        }
        .qr-info p { margin: 8px 0; font-size: 1.1rem; }
        .qr-info p span {
            font-weight: bold;
            color: #333;
            /* Cho phép copy */
            user-select: all; 
            -webkit-user-select: all;
            -moz-user-select: all;
        }
    </style>
</head>
<body>

    <header class="header">
        <a href="trangchu.php">← Quay lại danh sách xe</a>
    </header>

    <div class="container">
        <div class="car-image">
            <img src="<?php echo htmlspecialchars($car['image_url']); ?>" alt="<?php echo htmlspecialchars($car['brand']); ?>">
        </div>

        <div class="booking-details">
            <h1><?php echo htmlspecialchars($car['brand'] . ' ' . $car['model']); ?></h1>
            <div class="car-info">
                <p><strong>Năm:</strong> <?php echo $car['year']; ?></p>
                <div class="car-price">
                    Giá: <?php echo number_format($car['price_per_day'], 0, ',', '.'); ?> VNĐ/ngày
                </div>
            </div>
            <hr>
            
            <?php echo $message; // Hiển thị thông báo (Thành công/Lỗi) ?>

            <?php if ($show_payment_qr): ?>
                <div id="qr-payment-data"
                     data-bank-bin="<?php echo BANK_BIN; ?>"
                     data-stk="<?php echo ACCOUNT_NUMBER; ?>"
                     data-name="<?php echo htmlspecialchars(ACCOUNT_NAME); ?>"
                     data-amount="<?php echo $qr_data_cho_js['amount']; ?>"
                     data-memo="<?php echo htmlspecialchars($qr_data_cho_js['memo']); ?>"
                ></div>
                
                <div id="qr-display-area"></div>

            <?php else: ?>
                <h2>Đặt xe này</h2>
                <form class="booking-form" action="booking.php?car_id=<?php echo $car_id; ?>" method="POST">
                    <div class="form-group">
                        <label for="start_date">Ngày nhận xe:</label>
                        <input type="date" id="start_date" name="start_date" required>
                    </div>
                    <div class="form-group">
                        <label for="end_date">Ngày trả xe:</label>
                        <input type="date" id="end_date" name="end_date" required>
                    </div>
                    <button type="submit" class="btn-submit">Xác nhận Đặt xe</button>
                </form>
            <?php endif; ?>
            
        </div>
    </div>

    <script>
        // Script 1: Chặn chọn ngày quá khứ (như cũ)
        const today = new Date().toISOString().split('T')[0];
        const startDateInput = document.getElementById('start_date');
        const endDateInput = document.getElementById('end_date');
        
        if (startDateInput) {
            startDateInput.setAttribute('min', today);
        }
        if (endDateInput) {
            endDateInput.setAttribute('min', today);
        }

        // Script 2: (MỚI) TẠO MÃ QR
        // Chạy script này khi trang đã tải xong
        document.addEventListener('DOMContentLoaded', function() {
            // 1. Tìm thẻ div chứa dữ liệu mà PHP đã cung cấp
            const qrDataElement = document.getElementById('qr-payment-data');
            
            // 2. Nếu tìm thấy thẻ đó (nghĩa là PHP muốn JS tạo QR)
            if (qrDataElement) {
                
                // 3. Lấy toàn bộ dữ liệu ra
                const bin = qrDataElement.dataset.bankBin;
                const stk = qrDataElement.dataset.stk;
                const name = qrDataElement.dataset.name;
                const amount = qrDataElement.dataset.amount;
                const memo = qrDataElement.dataset.memo;
                
                // 4. JS "tạo ra" đường dẫn API VietQR
                // (Dùng 'compact' để QR đẹp hơn, nếu muốn mẫu có nền, dùng 'print')
                const vietQR_ApiUrl = `https://img.vietqr.io/image/${bin}-${stk}-compact.png?amount=${amount}&addInfo=${encodeURIComponent(memo)}`;
                
                // 5. JS "tạo ra" khối HTML để hiển thị
                const qrHtmlContent = `
                    <div class="qr-container">
                        <h3>Quét mã để thanh toán</h3>
                        <img src="${vietQR_ApiUrl}" alt="Mã QR Thanh toán">
                        <div class="qr-info">
                            <p>Tài khoản: <span>${name}</span></p>
                            <p>STK: <span>${stk}</span></p>
                            <p>Số tiền: <span style="color:red;">${parseInt(amount).toLocaleString('vi-VN')} VNĐ</span></p>
                            <p>Nội dung: <span style="color:blue;">${memo}</span></p>
                            <small>Lưu ý: Chụp lại màn hình thanh toán và nội dung chuyển khoản để đối chiếu.</small>
                        </div>
                    </div>
                `;
                
                // 6. Chèn khối HTML vào vị trí đã định
                document.getElementById('qr-display-area').innerHTML = qrHtmlContent;
            }
        });
    </script>

</body>
</html>