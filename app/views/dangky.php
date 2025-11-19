<?php 
include __DIR__ . '/partials/header.php';
?>
<!DOCTYPE html>
<html lang="vi">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Đăng ký</title>
    <link rel="stylesheet" href="http://localhost:8080/tyqgwsgr_DbXe/public/css/main.css" />
    <style>
      body {
        background: #f0f1f3;
      }
    </style>
  </head>
  <body>
    <header class="main-header">HỆ THỐNG CHO THUÊ XE Ô TÔ</header>
    <div class="form-container">
      <form class="form-box" id="register-form">
        <h2>Đăng ký</h2>

        <input
          type="text"
          id="username"
          name="username"
          placeholder="Tên đăng nhập"
          required
        />
        <input
          type="email"
          id="email"
          name="email"
          placeholder="Email"
          required
        />
        <input
          type="password"
          id="password"
          name="password"
          placeholder="Mật khẩu"
          required
        />
        <input
          type="password"
          id="confirm_password"
          name="confirm_password"
          placeholder="Xác nhận mật khẩu"
          required
        />
        <input
          type="text"
          id="address"
          name="address"
          placeholder="Địa chỉ"
        />
        <button type="submit">Đăng ký</button>
  <div id="register-error" class="form-error"></div>
        <p>
          Đã có tài khoản?
          <a href="http://localhost:8080/tyqgwsgr_DbXe/app/views/dangnhap.php"><u>Đăng nhập</u></a>
        </p>
        <p>
          <a href="http://localhost:8080/tyqgwsgr_DbXe/app/views/trangchu.php"><u>Quay về trang chủ</u> </a>
        </p>
      </form>
    </div>

    <script>
      document
        .getElementById("register-form")
        .addEventListener("submit", function (e) {
          e.preventDefault();
          const form = e.target;
          const password = document.getElementById("password").value;
          const confirm_password =
            document.getElementById("confirm_password").value;
          const errorDiv = document.getElementById("register-error");

          errorDiv.textContent = ""; // Xóa lỗi cũ

          // Kiểm tra mật khẩu khớp
          if (password !== confirm_password) {
            errorDiv.textContent = "Mật khẩu xác nhận không khớp!";
            return;
          }

          const formData = new FormData(form);
          
          fetch("http://localhost:8080/tyqgwsgr_DbXe/app/controllers/xulydangky.php", {
            method: "POST",
            body: formData,
          })
            .then((response) => response.text())
            .then((data) => {
              const response_text = data.trim();

              if (response_text === "success") {
                alert("Đăng ký thành công! Vui lòng đăng nhập.");
                window.location.href = "dangnhap.php";
              } else {
                errorDiv.textContent =
                  response_text || "Đăng ký thất bại! Tên đăng nhập hoặc email có thể đã tồn tại.";
              }
            })
            .catch(() => {
              errorDiv.textContent = "Lỗi kết nối máy chủ!";
            });
        });
    </script>
  </body>
</html>
