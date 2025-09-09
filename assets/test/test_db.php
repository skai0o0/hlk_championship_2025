<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Test DB Connection</title>
</head>
<body>
    <h2>Kiểm tra kết nối cơ sở dữ liệu MySQL</h2>
    <?php
    // Cấu hình kết nối — thay bằng thông tin thực tế từ cPanel
    $dbHost = 'localhost';
    $dbName = 'hlkchamp_hlk_championship_2025';
    $dbUser = 'hlkchamp_hlkchamp';
    $dbPass = 'Gengar102206AmTech@';

    // Kết nối sử dụng MySQLi (procedural)
    $conn = mysqli_connect($dbHost, $dbUser, $dbPass, $dbName);

    // Kiểm tra kết nối
    if (!$conn) {
        die("<p style='color:red;'>Connection failed: " . mysqli_connect_error() . "</p>");
    }
    echo "<p style='color:green;'>Connected successfully to database <strong>$dbName</strong>.</p>";

    // Thử query đơn giản: đếm số bảng trong database
    $testQuery = "SHOW TABLES FROM `$dbName`";
    if ($result = mysqli_query($conn, $testQuery)) {
        $tableCount = mysqli_num_rows($result);
        echo "<p>Number of tables in database: <strong>$tableCount</strong></p>";
        mysqli_free_result($result);
    } else {
        echo "<p style='color:red;'>Query error: " . mysqli_error($conn) . "</p>";
    }

    mysqli_close($conn);
    ?>
</body>
</html>
