
<?php
// Minimal admin_header.php — PHP only

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Require central DB connection
$dbPath = __DIR__ . '/../../config/db_connect.php';
if (!file_exists($dbPath)) {
    // fail early
    http_response_code(500);
    echo 'Missing DB include.';
    exit();
}
require_once $dbPath;

// Enforce admin authentication
if (empty($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header('Location: /tyqgwsgr_DbXe/app/views/dangnhap.php');
    exit();
}

$admin_username = htmlspecialchars($_SESSION['username'] ?? 'Admin', ENT_QUOTES, 'UTF-8');

// Ensure a CSRF token exists
if (empty($_SESSION['csrf_token'])) {
    try {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
    } catch (Exception $e) {
        $_SESSION['csrf_token'] = bin2hex(openssl_random_pseudo_bytes(16));
    }
}
// Default page title
$page_title = $page_title ?? 'Tổng quan';

// Output shared HTML header and sidebar
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?> - Admin</title>
    <link rel="stylesheet" href="../views/admin_style.css">
</head>
<body class="admin">
    <div class="admin-shell">
        <aside class="admin-sidebar">
            <div class="logo">
                <span>🚗</span>
                <span>Admin Panel</span>
            </div>
            <nav>
                <ul>
                    <li><a href="admin_dashboard.php">Dashboard</a></li>
                    <li><a href="admin_manage_cars.php">Quản lý xe</a></li>
                    <li><a href="admin_manage_bookings.php">Quản lý đơn</a></li>
                    <li><a href="admin_manage_revenue.php">Doanh thu</a></li>
                    <li><a href="/tyqgwsgr_DbXe/app/controllers/xulydangxuat.php">Đăng xuất</a></li>
                </ul>
            </nav>
        </aside>

        <main class="admin-main">
            <div class="admin-header">
                <h1><?php echo htmlspecialchars($page_title); ?></h1>
                <div class="admin-user">Chào, <strong><?php echo $admin_username; ?></strong></div>
            </div>

<?php
// End of header output; admin pages continue inside <main>

