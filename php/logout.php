<?php
// php/logout.php
declare(strict_types=1);

session_start();

// Xóa toàn bộ biến session
$_SESSION = [];

// Hủy session cookie (nếu có)
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// Hủy session
session_destroy();

// Xóa cookie custom (nếu có set khi login)
setcookie("hlk_user_token", "", time() - 3600, "/");
setcookie("hlk_user_id",    "", time() - 3600, "/");
setcookie("hlk_user_name",  "", time() - 3600, "/");
setcookie("hlk_user_class", "", time() - 3600, "/");
setcookie("hlk_user_grade", "", time() - 3600, "/");

// Nếu request AJAX → trả JSON
if (
    isset($_SERVER['HTTP_X_REQUESTED_WITH'])
    && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest'
) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([ 'success' => true, 'redirect' => '../index.html' ]);
    exit;
}

// Nếu truy cập trực tiếp → redirect về index
header("Location: ../index.html");
exit;
