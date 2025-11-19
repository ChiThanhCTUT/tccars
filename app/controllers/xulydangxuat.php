<?php
// xulydangxuat.php - Vietnamese named logout (used by admin links)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Clear session
$_SESSION = [];

if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params['path'], $params['domain'], $params['secure'], $params['httponly']
    );
}

session_destroy();

// Redirect back to login page
header('Location: /tyqgwsgr_DbXe/app/views/dangnhap.php');
exit();
