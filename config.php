<?php
// =============================================
// CONFIG.PHP - KẾT NỐI DATABASE & BẢO MẬT
// =============================================

// =============================================
// CẤU HÌNH GỬI EMAIL (Gmail SMTP)
// Điền email Gmail và App Password của bạn vào đây
// Hướng dẫn lấy App Password: myaccount.google.com > Bảo mật > Mật khẩu ứng dụng
// =============================================
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USER', 'your_email@gmail.com');  // <-- Đổi thành Gmail của bạn
define('SMTP_PASS', '');                       // <-- Điền App Password 16 ký tự tại đây
define('SMTP_FROM', 'your_email@gmail.com');   // Phải giống SMTP_USER
define('SMTP_NAME', 'Góc Học Tập');

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
