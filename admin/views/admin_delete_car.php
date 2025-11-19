<?php
// admin_delete_car.php
// Include header (để lấy $conn VÀ kiểm tra quyền admin)
include 'admin_header.php'; 

// 1. Kiểm tra xem có 'id' được gửi lên không
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $car_id = $_GET['id'];

    // 2. Chuẩn bị lệnh DELETE (Dùng prepared statement)
    $stmt = $conn->prepare("DELETE FROM cars WHERE car_id = ?");
    $stmt->bind_param("i", $car_id);
    
    // 3. Thực thi
    if ($stmt->execute()) {
        // Xóa thành công, không cần làm gì
    } else {
        // Có lỗi, ví dụ: xe đang được tham chiếu bởi 1 đơn hàng
        // Bạn có thể lưu lỗi vào session để hiển thị
        // $_SESSION['error_message'] = "Lỗi khi xóa xe: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}

// 4. Quay trở lại trang admin (dù thành công hay thất bại)
header("Location: admin_manage_cars.php");
exit();
?>