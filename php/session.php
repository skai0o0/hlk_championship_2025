<?php
// php/session.php
declare(strict_types=1);

session_start();
header('Content-Type: application/json; charset=utf-8');

// Kết nối MySQL: đảm bảo bạn đã có file này và thông số đúng DB hlk_championship_2025
require_once __DIR__ . '/db_connect.php';

/**
 * Trả JSON và thoát
 */
function json_end(array $payload, int $status = 200): void {
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

/**
 * Lấy user_id từ session/cookie (nếu bạn có set)
 * - Ưu tiên $_SESSION['hlk_user_id'] (với prefix hlk_)
 * - Fallback cookie 'hlk_user_id' (số nguyên)
 */
function current_user_id(): ?int {
    if (isset($_SESSION['hlk_user_id']) && is_numeric($_SESSION['hlk_user_id'])) {
        return (int)$_SESSION['hlk_user_id'];
    }
    if (!empty($_COOKIE['hlk_user_id']) && ctype_digit($_COOKIE['hlk_user_id'])) {
        return (int)$_COOKIE['hlk_user_id'];
    }
    return null;
}

$uid = current_user_id();
if ($uid === null) {
    json_end(['ok' => false]); // chưa đăng nhập
}

// Truy vấn thông tin user (dựa trên schema: full_name, class, grade)
$sql = "SELECT full_name, `class`, grade FROM users WHERE id = ? LIMIT 1";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    json_end(['ok' => false, 'error' => 'DB prepare failed'], 500);
}
$stmt->bind_param('i', $uid);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    // Map sang keys frontend đang dùng
    $resp = [
        'ok'   => true,
        'user' => [
            'name'  => $row['full_name'],
            'class' => $row['class'],
            'grade' => $row['grade'],
        ],
    ];
    json_end($resp);
}

// Không tìm thấy user_id hợp lệ
json_end(['ok' => false]);
