<?php
// /public_html/php/register.php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

ini_set('display_errors', '0');
ini_set('log_errors', '1');
if (!is_dir(__DIR__ . '/logs')) { @mkdir(__DIR__ . '/logs', 0755, true); }
ini_set('error_log', __DIR__ . '/logs/register_error.log');

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
require_once __DIR__ . '/db_connect.php';

function json_fail(array $errors = [], string $message = 'Đăng ký thất bại'): void {
    http_response_code(empty($errors) ? 400 : 422);
    echo json_encode(['success' => false, 'errors' => $errors, 'message' => $message], JSON_UNESCAPED_UNICODE);
    exit;
}
function json_ok(string $redirect = 'login.html'): void {
    echo json_encode(['success' => true, 'redirect' => $redirect], JSON_UNESCAPED_UNICODE);
    exit;
}
function post_str(string $k): string {
    return isset($_POST[$k]) ? trim((string)$_POST[$k]) : '';
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    http_response_code(405);
    echo json_encode(['success'=>false,'message'=>'Method Not Allowed'], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    global $conn;
    $conn->set_charset('utf8mb4');

    $fullName        = post_str('fullName');
    $studentId       = post_str('studentId');
    $class           = post_str('class');
    $grade           = post_str('grade');
    $email           = post_str('email');
    $username        = post_str('username');
    $password        = (string)($_POST['password'] ?? '');
    $confirmPassword = (string)($_POST['confirmPassword'] ?? '');
    $gameUid         = post_str('gameId'); // map sang game_uid trong DB

    $ip         = $_SERVER['REMOTE_ADDR']     ?? '';
    $userAgent  = $_SERVER['HTTP_USER_AGENT'] ?? '';

    $errors = [];
    if ($fullName === '' || mb_strlen($fullName) < 2) $errors['fullName'] = 'Vui lòng nhập họ tên đầy đủ';
    if ($studentId === '' || !preg_match('/^[A-Za-z]{0,3}[0-9]{4,12}$/', $studentId)) $errors['studentId'] = 'Mã số học sinh không hợp lệ';
    if ($class === '' || !preg_match('/^[\p{L}\p{N}\- _\/()]{1,30}$/u', $class)) $errors['class'] = 'Lớp không hợp lệ';
    if ($grade === '' || !preg_match('/^(K28|K29|K30|K31|K32)$/', $grade)) $errors['grade'] = 'Khóa không hợp lệ';
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors['email'] = 'Email không hợp lệ';
    if ($username === '' || !preg_match('/^[a-zA-Z0-9_]{4,20}$/', $username)) $errors['username'] = 'Tên đăng nhập không hợp lệ';
    if ($password === '' || strlen($password) < 6) $errors['password'] = 'Mật khẩu phải có ít nhất 6 ký tự';
    if ($confirmPassword === '' || $password !== $confirmPassword) $errors['confirmPassword'] = 'Mật khẩu xác nhận không khớp';
    if (!empty($errors)) json_fail($errors);

    /* check unique username */
    $stmt = $conn->prepare('SELECT 1 FROM `users` WHERE `username` = ? LIMIT 1');
    $stmt->bind_param('s', $username);
    $stmt->execute(); $stmt->store_result();
    if ($stmt->num_rows > 0) { $stmt->close(); json_fail(['username'=>'Tên đăng nhập đã tồn tại']); }
    $stmt->close();

    /* check unique email */
    $stmt = $conn->prepare('SELECT 1 FROM `users` WHERE `email` = ? LIMIT 1');
    $stmt->bind_param('s', $email);
    $stmt->execute(); $stmt->store_result();
    if ($stmt->num_rows > 0) { $stmt->close(); json_fail(['email'=>'Email đã tồn tại']); }
    $stmt->close();

    /* check unique studentId */
    $stmt = $conn->prepare('SELECT 1 FROM `users` WHERE `student_id` = ? LIMIT 1');
    $stmt->bind_param('s', $studentId);
    $stmt->execute(); $stmt->store_result();
    if ($stmt->num_rows > 0) { $stmt->close(); json_fail(['studentId'=>'Mã số học sinh đã tồn tại']); }
    $stmt->close();

    /* check unique game_uid nếu có nhập */
    if ($gameUid !== '') {
        $stmt = $conn->prepare('SELECT 1 FROM `users` WHERE `game_uid` = ? LIMIT 1');
        $stmt->bind_param('s', $gameUid);
        $stmt->execute(); $stmt->store_result();
        if ($stmt->num_rows > 0) { $stmt->close(); json_fail(['gameId'=>'Game UID đã tồn tại']); }
        $stmt->close();
    }

    /* insert */
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare('
        INSERT INTO `users`
            (`full_name`,`student_id`,`class`,`grade`,`email`,`username`,`password_hash`,`game_uid`,`ip`,`user_agent`,`created_at`)
        VALUES
            (?,?,?,?,?,?,?,?,?,?,NOW())
    ');
    $stmt->bind_param(
        'ssssssssss',
        $fullName,
        $studentId,
        $class,
        $grade,
        $email,
        $username,
        $passwordHash,
        $gameUid,
        $ip,
        $userAgent
    );
    $stmt->execute();
    $stmt->close();

    json_ok('login.html');

} catch (mysqli_sql_exception $e) {
    error_log('[mysqli_sql_exception] '.$e->getMessage());
    http_response_code(422);
    echo json_encode(['success'=>false,'message'=>'Lỗi CSDL','debug'=>$e->getMessage()], JSON_UNESCAPED_UNICODE);
    exit;
} catch (Throwable $e) {
    error_log('[Throwable] '.$e->getMessage());
    http_response_code(500);
    echo json_encode(['success'=>false,'message'=>'Lỗi máy chủ'], JSON_UNESCAPED_UNICODE);
    exit;
}
