<?php
include '../../config/db_connect.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    // Redirect to updated location after MVC reorg
    header('Location: dangnhap.php');
    exit();
}

// Lấy thông tin xe từ id
if (!isset($_GET['car_id'])) {
    header('Location: xe.php');
    exit();
}

$car_id = intval($_GET['car_id']);

// Lấy thông tin xe từ database
$stmt = $conn->prepare("SELECT * FROM cars WHERE car_id = ?");
$stmt->bind_param("i", $car_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: xe.php');
    exit();
}

$car = $result->fetch_assoc();

// Hiển thị thông báo lỗi từ query string (nếu có)
$error_message = '';
if (isset($_GET['error'])) {
    switch ($_GET['error']) {
        case 'invalid_dates':
            $error_message = 'Ngày trả phải sau ngày nhận.';
            break;
        case 'past_date':
            $error_message = 'Ngày nhận phải từ ngày hiện tại trở đi.';
            break;
        case 'car_unavailable':
            $error_message = 'Xe không khả dụng trong khoảng thời gian bạn chọn.';
            break;
        case 'system_error':
        default:
            $error_message = 'Có lỗi hệ thống. Vui lòng thử lại hoặc liên hệ hỗ trợ.';
            break;
    }
}

// Normalize field names between admin and user schemas
$displayName = trim((($car['brand'] ?? '') . ' ' . ($car['model'] ?? '')));
$price_per_day = isset($car['price_per_day']) ? (float)$car['price_per_day'] : (isset($car['price']) ? (float)$car['price'] : 0);
$image_url = $car['image_url'] ?? ($car['image'] ?? '');
$description = $car['description'] ?? '';
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đặt xe - <?php echo htmlspecialchars($displayName ?: 'Xe'); ?></title>
    <link rel="stylesheet" href="http://localhost:8080/tyqgwsgr_DbXe/public/css/main.css">
    <script src="https://cdn.jsdelivr.net/npm/qrcode-generator@1.4.4/qrcode.min.js"></script>
</head>
<body>
<?php include __DIR__ . '/partials/header.php'; ?>

    <div class="booking-page">
        <?php if (!empty(
            $error_message
        )): ?>
        <div class="alert alert-error" style="background:#ffe6e6;color:#900;padding:12px;border-radius:6px;margin:12px 0;">
            <?php echo htmlspecialchars($error_message); ?>
        </div>
        <?php endif; ?>
        <div class="booking-container">
            <div class="booking-card">
                <div class="car-preview">
                    <img src="<?php echo htmlspecialchars($image_url); ?>" alt="<?php echo htmlspecialchars($displayName); ?>">
                    <div class="meta">
                        <h4><?php echo htmlspecialchars($displayName); ?></h4>
                        <div class="sub">Giá thuê: <?php echo number_format($price_per_day); ?> đ/ngày</div>
                        <div class="description"><?php echo nl2br(htmlspecialchars($description)); ?></div>
                    </div>
                </div>

                <form id="bookingForm" action="../models/process_booking.php" method="POST">
            <input type="hidden" name="car_id" value="<?php echo $car_id; ?>">
            <input type="hidden" name="user_id" value="<?php echo $_SESSION['user_id']; ?>">
            
            <div class="form-row">
                <div class="form-group">
                    <label for="pickup_date">Ngày nhận xe:</label>
                    <input type="datetime-local" id="pickup_date" name="pickup_date" required>
                </div>

                <div class="form-group">
                    <label for="return_date">Ngày trả xe:</label>
                    <input type="datetime-local" id="return_date" name="return_date" required>
                </div>
            </div>

            <div class="form-group">
                <label for="location">Địa điểm nhận xe:</label>
                <select id="location" name="location" required>
                    <option value="Ninh Kiều">Phường Ninh Kiều, TP Cần Thơ</option>
                    <option value="Tận nơi">Giao xe tận nơi</option>
                </select>
            </div>

            <div class="insurance-options">
                <div class="checkbox-group">
                    <input type="checkbox" name="insurance" id="insurance" checked>
                    <label for="insurance">Bảo hiểm thuê xe (93.564đ/ngày)</label>
                </div>

                <div class="checkbox-group">
                    <input type="checkbox" name="extra_insurance" id="extraInsurance">
                    <label for="extraInsurance">Bảo hiểm người trên xe (100.000đ/ngày)</label>
                </div>
            </div>

            <div class="price-summary">
                <h3>Tổng chi phí</h3>
                <div class="price-row">
                    <span>Phí thuê xe</span>
                    <strong id="totalPrice">0 đ</strong>
                </div>
                <div class="price-row">
                    <span>Khuyến mãi</span>
                    <span>120.000 đ</span>
                </div>
                <div class="price-row total">
                    <span>Thành tiền</span>
                    <strong id="finalPrice">0 đ</strong>
                </div>
                <div class="price-note">* Giá đã bao gồm thuế VAT</div>
            </div>

            <div class="payment-method">
                <h3>Phương thức thanh toán</h3>
                <div class="payment-options">
                    <div class="radio-group">
                        <input type="radio" id="fullPayment" name="paymentMethod" value="full" checked>
                        <label for="fullPayment">Thanh toán toàn bộ (<span id="fullAmount">0 đ</span>)</label>
                    </div>
                    <div class="radio-group">
                        <input type="radio" id="depositPayment" name="paymentMethod" value="deposit">
                        <label for="depositPayment">Đặt cọc 40% (<span id="depositAmount">0 đ</span>)</label>
                    </div>
                </div>
                <div id="qrCodeContainer" style="text-align: center; margin-top: 20px; display: none;">
                    <h4>Quét mã QR để thanh toán</h4>
                    <div id="qrCode" style="margin: 10px auto;"></div>
                    <p style="font-size: 14px; color: #666;">
                        Số tiền cần thanh toán: <strong id="paymentAmount">0 đ</strong><br>
                        Nội dung chuyển khoản: <strong id="paymentReference"></strong>
                    </p>
                </div>
            </div>

            <div class="booking-actions">
                <button type="button" class="btn" onclick="showQRCode()">Tạo mã QR thanh toán</button>
                <button type="submit" class="btn" style="margin-left: 10px;">Xác nhận đặt xe</button>
            </div>
        </form>
    </div>

    <script>
    let currentTotal = 0;
    
    // Calculate rental duration and total price
    function calculateTotal() {
        const pickupDate = new Date(document.getElementById('pickup_date').value);
        const returnDate = new Date(document.getElementById('return_date').value);
        const basePrice = <?php echo json_encode($price_per_day); ?>;
        
        if (!pickupDate || !returnDate) return;
        
        const days = Math.ceil((returnDate - pickupDate) / (1000 * 60 * 60 * 24));
        if (days <= 0) {
            alert('Ngày trả xe phải sau ngày nhận xe!');
            return;
        }
        
        let total = basePrice * days;
        
        if (document.getElementById('insurance').checked) {
            total += 93564 * days;
        }
        if (document.getElementById('extraInsurance').checked) {
            total += 100000 * days;
        }
        
        const discount = 120000;
        currentTotal = total - discount;
        
        document.getElementById('totalPrice').textContent = total.toLocaleString() + ' đ';
        document.getElementById('finalPrice').textContent = currentTotal.toLocaleString() + ' đ';
        
        // Update payment amounts
        const deposit = Math.ceil(currentTotal * 0.4);
        document.getElementById('fullAmount').textContent = currentTotal.toLocaleString() + ' đ';
        document.getElementById('depositAmount').textContent = deposit.toLocaleString() + ' đ';
        updatePaymentAmount();
    }

    // Add event listeners for date and insurance changes
    ['pickup_date', 'return_date', 'insurance', 'extraInsurance'].forEach(id => {
        document.getElementById(id).addEventListener('change', calculateTotal);
    });

    // Add event listeners for payment method changes
    document.querySelectorAll('[name="paymentMethod"]').forEach(radio => {
        radio.addEventListener('change', updatePaymentAmount);
    });

    function updatePaymentAmount() {
        const isFullPayment = document.getElementById('fullPayment').checked;
        const amount = isFullPayment ? currentTotal : Math.ceil(currentTotal * 0.4);
        document.getElementById('paymentAmount').textContent = amount.toLocaleString() + ' đ';
    }

    function generateQRCode(text) {
        const qr = qrcode(0, 'M');
        qr.addData(text);
        qr.make();
        return qr.createImgTag(4);
    }

    function showQRCode() {
        const isFullPayment = document.getElementById('fullPayment').checked;
        const amount = isFullPayment ? currentTotal : Math.ceil(currentTotal * 0.4);
        const carId = <?php echo $car_id; ?>;
        const userId = <?php echo $_SESSION['user_id']; ?>;
        
        // Tạo nội dung chuyển khoản
        const reference = `THUEXE${carId}U${userId}`;
        document.getElementById('paymentReference').textContent = reference;
        
        // Tạo nội dung mã QR (ví dụ cho BIDV)
        const qrContent = `bidv://transfer?amount=${amount}&description=${reference}`;
        
        // Hiển thị container QR và tạo mã QR
        const container = document.getElementById('qrCodeContainer');
        const qrDiv = document.getElementById('qrCode');
        qrDiv.innerHTML = generateQRCode(qrContent);
        container.style.display = 'block';
        
        // Cuộn đến mã QR
        container.scrollIntoView({ behavior: 'smooth' });
    }

    // Form validation
    document.getElementById('bookingForm').addEventListener('submit', function(e) {
        const pickupDate = new Date(document.getElementById('pickup_date').value);
        const returnDate = new Date(document.getElementById('return_date').value);
        
        if (pickupDate >= returnDate) {
            e.preventDefault();
            alert('Ngày trả xe phải sau ngày nhận xe!');
            return;
        }

        if (pickupDate < new Date()) {
            e.preventDefault();
            alert('Ngày nhận xe phải từ ngày hiện tại trở đi!');
            return;
        }
    });
    </script>

    <!-- Custom booking form styles moved to main.css for consistency -->

    <script src="../../public/js/chucnangDKDN.js"></script>
</body>
</html>