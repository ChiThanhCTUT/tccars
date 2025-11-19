<?php
// Central DB connect + session starter
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$DB_HOST = 'localhost';
$DB_USER = 'root';
$DB_PASS = '';
$DB_NAME = 'tyqgwsgr_dbxe';  // Fixed case to match actual database name

$conn = mysqli_connect($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
if (!$conn) {
    // Don't leak credentials; show generic message
    die('Không thể kết nối CSDL.');
}
mysqli_set_charset($conn, 'utf8mb4');

// Optional base URL for redirects (adjust if your dev environment differs)
if (!defined('BASE_URL')) {
    define('BASE_URL', 'http://localhost:8080/tyqgwsgr_DbXe');
}
?>