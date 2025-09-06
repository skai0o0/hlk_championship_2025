<?php
// /php/register.php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/db_connect.php';

// --- Helpers ---
function json_fail(array $errors = [], string $message = 'Đăng ký thất bại'): void {
    echo json_encode(['success' => false, 'errors' => $errors, 'message' => $message], JSON_UNESCAPED_UNICODE);
    exit;
}
function json_ok(string $redirect = 'login.html'): void {
    echo json_encode(['success' => true, 'redirect' => $redirect], JSON_UNESCAPED_UNICODE);
    exit;
}
function field(string $key): string {
    return isset($_POST[$key]) ? trim((string)$_POST[$key]) : '';
}

// --- Collect ---
$fullName        = field('fullName');
$studentId       = field('studentId');
$class           = field('class');
$grade           = field('grade');
$email           = field('email');
$username        = field('username');
$password        = field('password');
$confirmPassword = field('confirmPassword');
$gameId          = field('gameId');
$termsAccepted   = isset($_POST['terms']);

// --- Validate ---
$errors = [];

if ($fullName === '' || mb_strlen($fullName) < 2) {
    $errors['fullName'] = 'Họ tên không hợp lệ';
}
if ($studentId === '' || !preg_match('/^[A-Za-z]{0,3}[0-9]{4,8}$/', $studentId)) {
    $errors['studentId'] = 'Mã số học sinh không hợp lệ';
}
if ($class === '') {
    $errors['class'] = 'Vui lòng chọn lớp';
}
if ($grade === '' || !preg_match('/^(K28|K29|K30|K31|K32)$/', $grade)) {
    $errors['grade'] = 'Khoá không hợp lệ';
}
if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors['email'] = 'Email không hợp lệ';
}
if ($username === '' || !preg_match('/^[a-zA-Z0-9_]{4,20}$/', $username)) {
    $errors['username'] = 'Tên đăng nhập không hợp lệ';
}
if ($password === '' || strlen($password) < 6) {
    $errors['password'] = 'Mật khẩu phải có ít nhất 6 ký tự';
}
if ($confirmPassword === '' || $password !== $confirmPassword) {
    $errors['confirmPassword'] = 'Mật khẩu xác nhận không khớp';
}
if ($gameId === '') {
    $errors['gameId'] = 'Vui lòng nhập UID trong game';
}
if (!$termsAccepted) {
    $errors['terms'] = 'Bạn phải đồng ý thể lệ';
}

if (!empty($errors)) {
    json_fail($errors);
}

// --- Duplicate check ---
$stmt = $conn->prepare("SELECT id FROM users WHERE email = ? OR username = ? LIMIT 1");
$stmt->bind_param("ss", $email, $username);
$stmt->execute();
$res = $stmt->get_result();
if ($res->num_rows > 0) {
    $row = $res->fetch_assoc();
    if ($row) {
        // Chỉ báo lỗi chung
        $errors['email'] = 'Email hoặc Username đã tồn tại';
    }
    json_fail($errors);
}
$stmt->close();

// --- Insert ---
$pwdHash = password_hash($password, PASSWORD_DEFAULT);
$ip      = $_SERVER['REMOTE_ADDR'] ?? '';
$ua      = $_SERVER['HTTP_USER_AGENT'] ?? '';

$stmt = $conn->prepare("INSERT INTO users 
    (full_name, student_id, class, grade, email, username, password_hash, game_uid, ip, user_agent) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("ssssssssss",
    $fullName, $studentId, $class, $grade,
    $email, $username, $pwdHash, $gameId, $ip, $ua
);

if ($stmt->execute()) {
    json_ok('login.html');
} else {
    json_fail([], "Lỗi khi lưu dữ liệu: " . $conn->error);
}
