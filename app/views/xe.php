<?php
// xe.php
// Từ /app/views/ đi lùi 2 cấp, vào /config/
include '../../config/db_connect.php';


// Lấy tên người dùng nếu đã đăng nhập (không bắt buộc)
$current_username = $_SESSION['username'] ?? '';

// Truy vấn CSDL để lấy tất cả xe (từ bảng `cars`) - thêm xử lý lỗi để debug nếu cần
$sql_cars = "SELECT * FROM cars WHERE is_available = 1";
$result_cars = mysqli_query($conn, $sql_cars);
if ($result_cars === false) {
    // Hiện lỗi truy vấn để dễ debug (bỏ hoặc log lại khi đưa lên production)
    echo '<p style="color:red;">Lỗi truy vấn CSDL: ' . htmlspecialchars(mysqli_error($conn)) . '</p>';
}

// Nếu không có xe nào có sẵn, in thêm thông tin debug (số hàng tổng và mẫu dữ liệu)
$show_debug = false;
if ($result_cars && mysqli_num_rows($result_cars) == 0) {
    $show_debug = true;
}
if ($result_cars === false) {
    $show_debug = true;
}
if ($show_debug) {
    // Tổng số xe trong bảng
    $res_total = mysqli_query($conn, "SELECT COUNT(*) as total FROM cars");
    $total = $res_total ? mysqli_fetch_assoc($res_total)['total'] : 'N/A';
    // Tổng số xe đánh dấu có sẵn
    $res_avail = mysqli_query($conn, "SELECT COUNT(*) as avail FROM cars WHERE is_available = 1");
    $avail = $res_avail ? mysqli_fetch_assoc($res_avail)['avail'] : 'N/A';

    echo '<div style="padding:12px; margin:12px 0; background:#fff3cd; border:1px solid #ffeeba; border-radius:6px;">';
    echo '<strong>DEBUG:</strong> Tổng số xe trong DB: ' . htmlspecialchars($total) . ". Có sẵn: " . htmlspecialchars($avail) . '<br>';

    // Hiển thị vài dòng mẫu để kiểm tra giá trị của is_available
    $sample = mysqli_query($conn, "SELECT car_id, brand, model, is_available FROM cars LIMIT 10");
    if ($sample && mysqli_num_rows($sample) > 0) {
        echo '<table style="width:100%; border-collapse:collapse; margin-top:8px;">';
        echo '<thead><tr><th style="text-align:left; padding:6px; border-bottom:1px solid #ddd;">ID</th><th style="text-align:left; padding:6px; border-bottom:1px solid #ddd;">Brand</th><th style="text-align:left; padding:6px; border-bottom:1px solid #ddd;">Model</th><th style="text-align:left; padding:6px; border-bottom:1px solid #ddd;">is_available</th></tr></thead>';
        echo '<tbody>';
        while ($r = mysqli_fetch_assoc($sample)) {
            echo '<tr>';
            echo '<td style="padding:6px; border-bottom:1px solid #f0f0f0;">'.htmlspecialchars($r['car_id']).'</td>';
            echo '<td style="padding:6px; border-bottom:1px solid #f0f0f0;">'.htmlspecialchars($r['brand']).'</td>';
            echo '<td style="padding:6px; border-bottom:1px solid #f0f0f0;">'.htmlspecialchars($r['model']).'</td>';
            echo '<td style="padding:6px; border-bottom:1px solid #f0f0f0;">'.htmlspecialchars($r['is_available']).'</td>';
            echo '</tr>';
        }
        echo '</tbody></table>';
    } else {
        echo 'Không có dữ liệu mẫu để hiển thị.';
    }
    echo '</div>';
}
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$current_username = $_SESSION['username'] ?? '';
$base = 'http://localhost:8080/tyqgwsgr_DbXe';
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Trang chủ - Thuê xe</title>
    <link rel="stylesheet" href="http://localhost:8080/tyqgwsgr_DbXe/public/css/main.css">
</head>

<body>
  <header>
    <div class="container">
      <div class="logo">DV Thuê Xe KTPM</div>
      <nav>
         <ul>
            <li><a href="<?= $base ?>/app/views/trangchu.php">Trang chủ</a></li>
            <li><a href="<?= $base ?>/app/views/xe.php">Các loại xe</a></li>
            <li><a href="<?= $base ?>/app/views/gioithieu.php">Giới thiệu</a></li>
            <?php if ($current_username !== ''): ?>
              <li class="welcome-header">Chào mừng, <strong><?= htmlspecialchars($current_username); ?></strong> | <a href="<?= $base ?>/app/controllers/logout.php">Đăng xuất</a></li>
            <?php else: ?>
              <li><a href="<?= $base ?>/app/views/dangnhap.php" id="login-logout-btn" class="btn">Đăng nhập</a></li>
            <?php endif; ?>
        
         </ul>
      </nav>
    </div>
</header>
    <section class="hero">
      <div class="container">
        <h1>Thuê xe tự lái toàn quốc</h1>
        <p>Giá tốt - Giao xe tận nơi - Đặt xe dễ dàng</p>
        <form id="searchForm" class="search-box">
          <input
            type="text"
            id="searchInput"
            placeholder="Nhập địa điểm, loại xe..."
          />
          <!-- Cong-Lọc giá theo yêu cầu -->
          <select id="priceFilter">
            <option value="">Tất cả giá</option>
            <option value="500"><= 500.000đ</option>
            <option value="700">500.000đ - 700.000đ</option>
            <option value="1000">700.000đ - 1.000.000đ</option>
            <option value="1001">> 1.000.000đ</option>
          </select>
          <button type="submit">Tìm xe</button>
        </form>
      </div>
    </section>
    <div class="container">
        <h2>Danh sách xe có sẵn</h2>
        <div class="car-grid">
           <?php
            if ($result_cars && mysqli_num_rows($result_cars) > 0) {
                while ($car = mysqli_fetch_assoc($result_cars)) {
                    $displayName = htmlspecialchars(trim(($car['brand'] ?? '') . ' ' . ($car['model'] ?? '')));
                    $price = number_format((float)($car['price_per_day'] ?? 0)) . ' đ/ngày';
                    $img = htmlspecialchars($car['image_url'] ?? '');
                    $desc = htmlspecialchars($car['description'] ?? '');
                    $car_id = (int)($car['car_id'] ?? 0);
            ?>
                <div class="car-item" data-id="<?= $car_id ?>" data-name="<?= $displayName ?>" data-price="<?= htmlspecialchars($price) ?>" data-img="<?= $img ?>" data-desc="<?= $desc ?>">
                    <img src="<?php echo $img; ?>" 
                         alt="<?php echo $displayName; ?>"
                         class="car-item-img">
                    
                    <div class="car-item-content">
                        <h3><?php echo $displayName; ?></h3>
                        <p class="price">Giá: <?php echo htmlspecialchars($price); ?></p>
                        <button>Xem chi tiết</button>
                    </div>
                </div>
            <?php
                }
            } else {
                echo "<p>Không có xe nào có sẵn.</p>";
            }
            mysqli_close($conn);
            ?>
        </div>
    </div>
    <script src="http://localhost:8080/tyqgwsgr_DbXe/public/js/chucnang.js"></script>
    <script src="http://localhost:8080/tyqgwsgr_DbXe/public/js/chucnangDKDN.js"></script>
    <script>
      // Xử lý điều hướng đến trang chi tiết
      document.addEventListener('DOMContentLoaded', function() {
        const detailBtns = document.querySelectorAll('.car-item button');
        detailBtns.forEach(btn => {
          btn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            const carId = this.closest('.car-item').dataset.id;
            if (carId) {
              window.location.href = 'chitetxe.php?car_id=' + carId;
            }
          });
        });
      });
    </script>
</html>
