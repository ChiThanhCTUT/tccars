<?php
// include central DB connect (starts session)
include_once __DIR__ . '/../../config/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    // Sửa 1: Nhận 'confirm_password' từ form HTML
    $confirm_password = $_POST['confirm_password'] ?? '';
    $address = $_POST['address'] ?? ''; // Address là tùy chọn
    
    $errors = [];

    // Sửa 2: Bỏ 'address' ra khỏi kiểm tra 'empty'
    if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
        $errors[] = 'Vui lòng điền đầy đủ thông tin bắt buộc.';
    }

    // Sửa 3: Dùng biến đã sửa ở trên
    if ($password !== $confirm_password) {
        $errors[] = 'Mật khẩu xác nhận không khớp.';
    }
    
    // Kiểm tra username/email đã tồn tại chưa
    // (Giữ nguyên phần kiểm tra của bạn, nó đã tốt)
    if (empty($errors)) {
    $check = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $check->bind_param('ss', $username, $email);
        $check->execute();
        $check->store_result();
        
        if ($check->num_rows > 0) {
            $errors[] = 'Tên đăng nhập hoặc email đã tồn tại.';
        }
        $check->close();
    }

    // Sửa 4: Thay đổi toàn bộ logic trả về (echo)
    if (empty($errors)) {
        // Mọi thứ hợp lệ, tiến hành INSERT
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $sql = 'INSERT INTO users (username, email, password, address) VALUES (?, ?, ?, ?)';
        $stmt = $conn->prepare($sql);
        
        if ($stmt === false) {
            echo 'Lỗi phía máy chủ (prepare failed).';
            $conn->close();
            exit();
        }
        
        $stmt->bind_param('ssss', $username, $email, $hash, $address);
        
        if ($stmt->execute()) {
            // Đây là tín hiệu thành công mà JavaScript (fetch) mong đợi
            echo "success";
        } else {
            echo 'Lỗi khi đăng ký. Vui lòng thử lại.';
        }
        $stmt->close();
        
    } else {
        // Chỉ trả về lỗi đầu tiên tìm thấy
        echo $errors[0];
    }

    $conn->close();
    exit(); // Luôn exit sau khi xử lý POST
}
?>