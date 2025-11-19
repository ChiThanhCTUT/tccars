<?php
// include central DB connect (starts session)
include_once __DIR__ . '/../../config/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');

    if (empty($email)) {
        echo '<p style="color:red">Vui lòng nhập email đã đăng ký.</p>';
        exit();
    }

    // Xác thực định dạng email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo '<p style="color:red">Email không hợp lệ.</p>';
        exit();
    }

    // Tìm người dùng theo email bằng prepared statement
    $stmt = $conn->prepare('SELECT id FROM users WHERE email = ?');
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 1) {
        $stmt->bind_result($user_id);
        $stmt->fetch();

        // Tạo token reset an toàn
        try {
            $token = bin2hex(random_bytes(16));
        } catch (Exception $e) {
            $token = bin2hex(openssl_random_pseudo_bytes(16));
        }
        $expires_at = date('Y-m-d H:i:s', time() + 3600); // 1 giờ

        // Tạo bảng password_resets nếu chưa tồn tại (không phá hủy dữ liệu)
        $create_sql = "CREATE TABLE IF NOT EXISTS password_resets (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            token VARCHAR(128) NOT NULL,
            expires_at DATETIME NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        $conn->query($create_sql);

        // Lưu token vào bảng
        $ins = $conn->prepare('INSERT INTO password_resets (user_id, token,expires_at) VALUES (?, ?, ?)');
        $ins->bind_param('iss', $user_id, $token, $expires_at);
        $ins->execute();
        $ins->close();

        // Gửi email thực tế => cần cấu hình SMTP. Hiện tại ghi log token vào file để dev kiểm tra
        $logLine = date('Y-m-d H:i:s') . " | password reset token for user_id={$user_id} token={$token}\n";
        @file_put_contents(__DIR__ . '/password_reset.log', $logLine, FILE_APPEND | LOCK_EX);

        // Thông báo chung (không tiết lộ token cho client trong môi trường production)
        echo '<p style="color:green">Nếu email tồn tại trong hệ thống, bạn sẽ nhận được hướng dẫn đặt lại mật khẩu (hoặc liên hệ quản trị viên).</p>';
    } else {
        echo '<p style="color:red">Email chưa được đăng ký trong hệ thống.</p>';
    }

    $stmt->close();
    $conn->close();
    exit();
}
?>