<?php 
include __DIR__ . '/partials/header.php';
?>
<!DOCTYPE html>
<html lang="vi">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Đăng nhập</title>
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
      <form class="form-box" id="login-form">
        <h2>Đăng nhập</h2>
        <input
          type="text"
          id="username"
          name="username"
          placeholder="Tên đăng nhập"
          required
          autocomplete="username"
        />
        <input
          type="password"
          id="password"
          name="password"
          placeholder="Mật khẩu"
          required
          autocomplete="current-password"
        />
        <div class="forgot-link">
          <a href="http://localhost:8080/tyqgwsgr_DbXe/app/views/quenmatkhau.php">Quên mật khẩu?</a>
        </div>
        <button type="submit">Đăng nhập</button>
  <div id="login-error" class="form-error"></div>
        <p>
          Chưa có tài khoản?
            <a href="http://localhost:8080/tyqgwsgr_DbXe/app/views/dangky.php"><u>Đăng ký</u></a>
        </p>
        <p>
          <a href="http://localhost:8080/tyqgwsgr_DbXe/app/views/trangchu.php"><u>Quay về trang chủ</u> </a>
        </p>
      </form>
    </div>
    <script>
      document
        .getElementById("login-form")
        .addEventListener("submit", function (e) {
          e.preventDefault();
          const form = e.target;
          const formData = new FormData(form);
          document.getElementById("login-error").textContent = "";
          
          fetch("http://localhost:8080/tyqgwsgr_DbXe/app/controllers/xulydangnhap.php", {
            method: "POST",
            body: formData,
            credentials: 'same-origin'
          })
            .then((response) => response.text())
            .then((data) => {
              const response_text = data.trim();

              if (response_text === "user_success") {
                localStorage.setItem("isLoggedIn", "true");
                window.location.href = "http://localhost:8080/tyqgwsgr_DbXe/app/views/trangchu.php";
              
              } else if (response_text === "admin_success") {
                localStorage.setItem("isLoggedIn", "true");
                window.location.href = "/tyqgwsgr_DbXe/admin/views/admin_dashboard.php";
              
              } else {
                document.getElementById("login-error").textContent =
                  response_text || "Sai tài khoản hoặc mật khẩu!";
              }
            })
            .catch(() => {
              document.getElementById("login-error").textContent =
                "Lỗi kết nối máy chủ!";
            });
        });
    </script>
  </body>
</html>
