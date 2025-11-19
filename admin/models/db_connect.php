<?php
// Wrapper so admin code can include admin/models/db_connect.php
// It reuses the central DB connect located in config/db_connect.php
$central = __DIR__ . '/../../config/db_connect.php';
if (file_exists($central)) {
    require_once $central;
} else {
    // No DB connector found — stop with clear message for developer
    die('Không tìm thấy file db_connect.php ở config/. Vui lòng đặt file db_connect.php vào config/.');
}
?>