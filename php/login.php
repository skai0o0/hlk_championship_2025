<?php
// /php/login.php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/db_connect.php';

// --- Helpers ---
function json_fail(array $errors = [], string $message = 'Đăng nhập thất bại'): void {
    echo json_encode(['success' => false, 'errors' => $errors, 'message' => $message], JSON_UNESCAPED_UNICODE);
    exit;
}
function json_ok(array $user, string $redirect = 'index.html'): void {
    echo json_encode([
        'success' => true,
        'redirect' => $redirect,
        'user' => [
            'name' => $user['full_name'],
            'class' => $user['class']
        ]
    ], JSON_UNESCAPED_UNICODE);
    exit;
}
function field(string $key): string {
    return isset($_POST[$key]) ? trim((string)$_POST[$key]) : '';
}

// --- Collect ---
$login    = field('email'); // có thể là email hoặc username
$password = field('password');

if ($login === '' || $password === '') {
    json_fail(['email' => 'Vui lòng nhập email hoặc tên đăng nhập', 'password' => 'Vui lòng nhập mật khẩu']);
}

// --- Query ---
$stmt = $conn->prepare("SELECT * FROM users WHERE email = ? OR username = ? LIMIT 1");
$stmt->bind_param("ss", $login, $login);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows === 0) {
    json_fail(['email' => 'Tài khoản không tồn tại']);
}

$user = $res->fetch_assoc();
$stmt->close();

// --- Verify password ---
if (!password_verify($password, $user['password_hash'])) {
    json_fail(['password' => 'Mật khẩu không đúng']);
}

// --- Success ---
session_start();
$_SESSION['hlk_user_id']    = $user['id'];
$_SESSION['hlk_user_name']  = $user['full_name'];
$_SESSION['hlk_user_class'] = $user['class'];

// Set cookies for persistent login (7 ngày)
setcookie("hlk_user_id", (string)$user['id'], time() + 604800, "/");
setcookie("hlk_user_token", bin2hex(random_bytes(16)), time() + 604800, "/");
setcookie("hlk_user_name", $user['full_name'], time() + 604800, "/");
setcookie("hlk_user_class", $user['class'], time() + 604800, "/");
setcookie("hlk_user_grade", $user['grade'], time() + 604800, "/");

json_ok($user);
