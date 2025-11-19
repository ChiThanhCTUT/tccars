<?php
// admin_manage_bookings.php
$page_title = "Quản lý Đơn Đặt Xe"; // Đặt tiêu đề
include 'admin_header.php'; // Bao gồm header, CSDL, và kiểm tra admin

// Flash messages from actions
$flash_msg = '';
if (!empty($_GET['success'])) {
    if ($_GET['success'] === 'confirmed') {
        $flash_msg = "<div class='msg success'>Đã duyệt đơn và đánh dấu xe là 'Đã thuê' (is_available = 0).</div>";
    } elseif ($_GET['success'] === 'cancelled') {
        $flash_msg = "<div class='msg success'>Đã hủy đơn và trả lại trạng thái xe 'Có sẵn' (is_available = 1).</div>";
    }
} elseif (!empty($_GET['error'])) {
    $err = htmlspecialchars($_GET['error']);
    $flash_msg = "<div class='msg error'>Lỗi: {$err}.</div>";
}

// 1. LẤY TẤT CẢ ĐƠN HÀNG (JOIN VỚI BẢNG USER VÀ CARS)
$sql_bookings = "SELECT 
                    b.booking_id, 
                    b.start_date, 
                    b.end_date, 
                    b.total_price, 
                    b.status,
                    b.booking_date,
                    u.username, 
                    c.brand, 
                    c.model
                FROM bookings AS b
                JOIN users AS u ON b.user_id = u.id
                JOIN cars AS c ON b.car_id = c.car_id
                ORDER BY b.booking_date DESC"; // Hiển thị đơn mới nhất lên đầu

$result_bookings = mysqli_query($conn, $sql_bookings);
?>

<div class="container">
    <?php if (!empty($flash_msg)) echo $flash_msg; ?>
    
    <table>
        <thead>
            <tr>
                <th>ID Đơn</th>
                <th>Người dùng</th>
                <th>Tên xe</th>
                <th>Ngày nhận</th>
                <th>Ngày trả</th>
                <th>Tổng tiền</th>
                <th>Trạng thái</th>
                <th>Hành động</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if (mysqli_num_rows($result_bookings) > 0) {
                while ($booking = mysqli_fetch_assoc($result_bookings)) {
                    echo "<tr>";
                    echo "<td>" . $booking['booking_id'] . "</td>";
                    echo "<td>" . htmlspecialchars($booking['username']) . "</td>";
                    echo "<td>" . htmlspecialchars($booking['brand'] . ' ' . $booking['model']) . "</td>";
                    echo "<td>" . $booking['start_date'] . "</td>";
                    echo "<td>" . $booking['end_date'] . "</td>";
                    echo "<td>" . number_format($booking['total_price'], 0, ',', '.') . " VNĐ</td>";
                    
                    // Hiển thị trạng thái với màu sắc
                    $status = $booking['status'];
                    if ($status == 'Pending') {
                        echo "<td><span style='color:#f39c12; font-weight:bold;'>Chờ duyệt</span></td>";
                    } elseif ($status == 'Confirmed') {
                        echo "<td><span style='color:green; font-weight:bold;'>Đã xác nhận</span></td>";
                    } else { // 'Cancelled'
                        echo "<td><span style='color:red; font-weight:bold;'>Đã hủy</span></td>";
                    }

                    // Nút Hành động (dùng POST + CSRF token để an toàn)
                    echo "<td class='action-links'>";
                    if ($status == 'Pending') {
                        // Form duyệt
                        echo "<form method='POST' action='admin_update_booking.php' style='display:inline-block;margin-right:6px;'>";
                        echo "<input type='hidden' name='booking_id' value='" . $booking['booking_id'] . "'>";
                        echo "<input type='hidden' name='action' value='confirm'>";
                        echo "<input type='hidden' name='csrf_token' value='" . htmlspecialchars(
                            
                          
                            
                            $_SESSION['csrf_token'] ?? '') . "'>";
                        echo "<button type='submit' class='edit' onclick=\"return confirm('Bạn có muốn duyệt đơn này?');\">Duyệt</button>";
                        echo "</form>";

                        // Form hủy
                        echo "<form method='POST' action='admin_update_booking.php' style='display:inline-block;'>";
                        echo "<input type='hidden' name='booking_id' value='" . $booking['booking_id'] . "'>";
                        echo "<input type='hidden' name='action' value='cancel'>";
                        echo "<input type='hidden' name='csrf_token' value='" . htmlspecialchars($_SESSION['csrf_token'] ?? '') . "'>";
                        echo "<button type='submit' class='delete' onclick=\"return confirm('Bạn có chắc muốn hủy đơn này?');\">Hủy</button>";
                        echo "</form>";
                    } else {
                        echo "N/A"; // Không có hành động
                    }
                    echo "</td>";

                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='8'>Chưa có đơn đặt xe nào.</td></tr>";
            }
            ?>
        </tbody>
    </table>
    </div>
<?php
// Close DB connection and include footer
if (isset($conn) && is_resource($conn)) {
    mysqli_close($conn);
}
include 'admin_footer.php'; // Include footer
?>