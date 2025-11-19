<?php
// create_admin.php
// Simple script to create an admin user for local/dev use.
// Place this file in app/controllers/ and access via browser: http://localhost/.../app/controllers/create_admin.php

include_once __DIR__ . '/../../config/db_connect.php';

// Only allow running from localhost for safety
$allowed = ['127.0.0.1', '::1', 'localhost'];
$remote = $_SERVER['REMOTE_ADDR'] ?? '';
if (!in_array($remote, $allowed) && php_sapi_name() !== 'cli') {
    die('Access denied.');
}

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $email === '' || $password === '') {
        $message = '<p style="color:red">Vui lòng điền đủ username, email và password.</p>';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = '<p style="color:red">Email không hợp lệ.</p>';
    } else {
        // Check if users table exists
        $check_table = $conn->query("SHOW TABLES LIKE 'users'");
        if ($check_table->num_rows == 0) {
            $message = '<p style="color:red">Bảng `users` không tồn tại. Vui lòng import schema trước (database/schema.sql).</p>';
        } else {
            // Check duplicates
            $stmt = $conn->prepare('SELECT id FROM users WHERE username = ? OR email = ?');
            $stmt->bind_param('ss', $username, $email);
            $stmt->execute();
            $stmt->store_result();
            if ($stmt->num_rows > 0) {
                $message = '<p style="color:red">Username hoặc email đã tồn tại.</p>';
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $ins = $conn->prepare('INSERT INTO users (username, email, password, is_admin) VALUES (?, ?, ?, 1)');
                $ins->bind_param('sss', $username, $email, $hash);
                if ($ins->execute()) {
                    $message = '<p style="color:green">Tài khoản admin đã được tạo thành công.</p>';
                } else {
                    $message = '<p style="color:red">Lỗi khi tạo admin: ' . htmlspecialchars($ins->error) . '</p>';
                }
                $ins->close();
            }
            $stmt->close();
        }
    }
}
?>
<!doctype html>
<html lang="vi">
<head>
<meta charset="utf-8">
<title>Tạo Admin (dev)</title>
<style>body{font-family:Arial,Helvetica,sans-serif;padding:20px;} .box{max-width:500px;background:#fff;padding:20px;border:1px solid #eee;border-radius:6px;} input{width:100%;padding:10px;margin:8px 0;border:1px solid #ccc;border-radius:4px}</style>
</head>
<body>
<div class="box">
<h2>Tạo tài khoản Admin (cho dev)</h2>
<?php echo $message; ?>
<form method="post">
<label>Username</label>
<input name="username" required>
<label>Email</label>
<input name="email" type="email" required>
<label>Password</label>
<input name="password" type="password" required>
<button type="submit">Tạo Admin</button>
</form>
<p>Lưu ý: Chỉ chạy trên môi trường local. Nếu bảng <code>users</code> chưa có, hãy import schema.</p>
</div>
</body>
</html>