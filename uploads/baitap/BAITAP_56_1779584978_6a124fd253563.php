<?php
// =============================================
// CONFIG.PHP - KẾT NỐI DATABASE
// =============================================

$host     = 'localhost';
$dbname   = 'quanly_hoctap';     // Tên database của bạn
$username = 'root';              // Mặc định của XAMPP
$password = '';                  // Mặc định của XAMPP để trống

// Kết nối
$conn = new mysqli($host, $username, $password, $dbname);

// Kiểm tra kết nối
if ($conn->connect_error) {
    die("❌ Kết nối database thất bại: " . $conn->connect_error);
}

// Thiết lập charset tiếng Việt
$conn->set_charset("utf8mb4");

echo "<!-- Kết nối Database thành công -->";   // Dòng này chỉ để kiểm tra, sau có thể xóa
?>