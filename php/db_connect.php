<?php
declare(strict_types=1);

$DB_HOST = "localhost";
$DB_USER = "root";
$DB_PASS = "";
$DB_NAME = "hlk_championship_2025";

$conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);

// Kiểm tra lỗi kết nối
if ($conn->connect_error) {
    die("Kết nối database thất bại: " . $conn->connect_error);
}

// Set charset UTF-8 cho chuẩn
$conn->set_charset("utf8mb4");
?>
