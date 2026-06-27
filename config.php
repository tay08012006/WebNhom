<?php

//  CONFIG.PHP — Cấu hình kết nối database & gửi email
//  SMTP (Gmail)
//  Hướng dẫn lấy App Password:
//  myaccount.google.com → Bảo mật → Mật khẩu ứng dụng

define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USER', 'phannhut2021@gmail.com');  // Gmail gửi đi
define('SMTP_PASS', 'kyjg xfmi upfz dknh');     // App Password (16 ký tự)
define('SMTP_FROM', 'phannhut2021@gmail.com');  // Địa chỉ hiển thị người gửi
define('SMTP_NAME', 'Góc Học Tập');             // Tên hiển thị người gửi

//  Database (MySQL)

$host     = 'localhost';
$dbname   = 'quanly_hoctap';
$username = 'root';
$password = '';

$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
    die("❌ Kết nối database thất bại: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");
?>