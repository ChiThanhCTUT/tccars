<?php include __DIR__ . '/partials/header.php'; ?>
<!DOCTYPE html>
<html lang="vi">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Quên mật khẩu</title>
    <link rel="stylesheet" href="http://localhost:8080/tyqgwsgr_DbXe/public/css/main.css" />
  </head>
  <body class="bg-muted">
    <div class="form-container">
      <form
        class="form-box"
        id="forgot-form"
        action="http://localhost:8080/tyqgwsgr_DbXe/app/controllers/xulyquenmatkhau.php"
        method="post"
      >
        <h2>Quên mật khẩu</h2>
        <input
          type="email"
          name="email"
          placeholder="Nhập email đã đăng ký"
          required
        />
        <button type="submit">Gửi yêu cầu lấy lại mật khẩu</button>
        <p>
          <a href="http://localhost:8080/tyqgwsgr_DbXe/app/views/dangnhap.php"
            >Quay về đăng nhập</a
          >
        </p>
        <p>
          <a href="http://localhost:8080/tyqgwsgr_DbXe/app/views/trangchu.php"
            >Quay về trang chủ</a
          >
        </p>
      </form>
    </div>
  </body>
</html>
