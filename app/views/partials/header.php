<?php
// Shared header partial for USER pages
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$current_username = $_SESSION['username'] ?? '';
$base = 'http://localhost:8080/tyqgwsgr_DbXe';
?>
<header>
    <div class="container">
      <div class="logo">DV Thuê Xe KTPM</div>
      <nav>
         <ul>
            <li><a href="<?= $base ?>/app/views/trangchu.php">Trang chủ</a></li>
            <li><a href="<?= $base ?>/app/views/xe.php">Các loại xe</a></li>
            <li><a href="<?= $base ?>/app/views/gioithieu.php">Giới thiệu</a></li>
            <?php if ($current_username !== ''): ?>
              <li class="welcome-header">Chào mừng, <strong><?= htmlspecialchars($current_username); ?></strong> | <a href="<?= $base ?>/app/controllers/logout.php">Đăng xuất</a></li>
            <?php else: ?>
              <li><a href="<?= $base ?>/app/views/dangnhap.php" id="login-logout-btn" class="btn">Đăng nhập</a></li>
            <?php endif; ?>
         </ul>
      </nav>
    </div>
</header>
