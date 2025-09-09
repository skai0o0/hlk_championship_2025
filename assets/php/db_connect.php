<?php
declare(strict_types=1);

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$dbHost = 'localhost';
$dbName = 'hlkchamp_hlk_championship_2025';
$dbUser = 'hlkchamp_hlkchamp';
$dbPass = 'Gengar102206AmTech@';

$conn = new mysqli($dbHost, $dbUser, $dbPass, $dbName);
$conn->set_charset('utf8mb4');
