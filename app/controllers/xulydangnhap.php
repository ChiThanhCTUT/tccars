<?php
// include central DB connect (starts session)
include_once __DIR__ . '/../../config/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Lấy dữ liệu an toàn
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        echo "Vui lòng nhập đầy đủ tên đăng nhập và mật khẩu.";
        exit();
    }

    // Câu truy vấn của bạn đã chính xác với CSDL mới (có is_admin)
    $sql = "SELECT id, password, is_admin FROM users WHERE username = ?";
    
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        echo "Lỗi hệ thống: Không thể chuẩn bị truy vấn.";
        $conn->close();
        exit();
    }
    
    $stmt->bind_param('s', $username);
    $stmt->execute();
    
    // SỬA 2: Xóa dấu "/" bị thừa ở cuối dòng này
    $stmt->store_result(); 

    // 3. Kiểm tra xem có tìm thấy người dùng không
    if ($stmt->num_rows === 1) {
        // Gắn kết quả vào các biến
        $stmt->bind_result($user_id, $hashed_password, $is_admin);
        $stmt->fetch();

        // 4. Xác minh mật khẩu
        if (password_verify($password, $hashed_password)) {
            // Đăng nhập thành công!
            
            // 5. Lưu thông tin quan trọng vào SESSION
            // (Giờ đã hoạt động vì có session_start())
            $_SESSION['user_id'] = $user_id;       
            $_SESSION['username'] = $username;   
            $_SESSION['is_admin'] = $is_admin;   

            // 6. Trả về kết quả cho JavaScript (AJAX)
            if ($is_admin == 1) {
                // Nếu là Admin
                echo "admin_success";
            } else {
                // Nếu là người dùng bình thường
                echo "user_success";
            }

        } else {
            // Sai mật khẩu
            echo "Sai tài khoản hoặc mật khẩu!";
        }
    } else {
        // Không tìm thấy tên đăng nhập
        echo "Sai tài khoản hoặc mật khẩu!";
    }

    // Đóng statement và kết nối
    $stmt->close();
    $conn->close();

} else {
    // Nếu ai đó cố gắng truy cập trực tiếp tệp này qua trình duyệt
    echo "Phương thức truy cập không hợp lệ.";
}
?>