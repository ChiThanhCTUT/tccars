<?php
// admin_manage_cars.php
$page_title = 'Quản lý Xe';
include 'admin_header.php'; // includes DB, session check and emits header

// Fetch all cars
$sql = "SELECT * FROM cars ORDER BY car_id DESC";
$result = $conn->query($sql);
?>

<div class="admin-card">
    <h2>Danh sách Xe</h2>
    <div style="margin-bottom:12px;"><a href="admin_form_car.php" class="btn">+ Thêm Xe Mới</a></div>
    <table class="table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Hãng</th>
                <th>Mẫu</th>
                <th>Năm</th>
                <th>Giá/ngày</th>
                <th>Trạng thái</th>
                <th>Hành động</th>
            </tr>
        </thead>
        <tbody>
        <?php
        if ($result && $result->num_rows > 0) {
            while ($car = $result->fetch_assoc()) {
                $status = $car['is_available'] ? 'Có sẵn' : 'Đã thuê';
                echo '<tr>';
                echo '<td>' . $car['car_id'] . '</td>';
                echo '<td>' . htmlspecialchars($car['brand']) . '</td>';
                echo '<td>' . htmlspecialchars($car['model']) . '</td>';
                echo '<td>' . htmlspecialchars($car['year']) . '</td>';
                echo '<td>' . number_format($car['price_per_day'], 0, ',', '.') . 'đ</td>';
                echo '<td>' . $status . '</td>';
                echo '<td>';
                echo '<a href="admin_form_car.php?edit_id=' . $car['car_id'] . '" class="edit">Sửa</a> ';
                echo '<a href="admin_delete_car.php?id=' . $car['car_id'] . '" class="delete" onclick="return confirm(\'Bạn có chắc muốn xóa?\')">Xóa</a>';
                echo '</td>';
                echo '</tr>';
            }
        } else {
            echo '<tr><td colspan="7">Chưa có xe nào.</td></tr>';
        }
        ?>
        </tbody>
    </table>
</div>

<?php
$conn->close();
include 'admin_footer.php';
?>
