<?php
$host = "localhost";
$user = "root";
$pass = "";   
$dbname = "webnhom";

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Kết nối Database thất bại: " . $conn->connect_error);
}
// Set font tiếng Việt chống lỗi hiển thị dấu
$conn->set_charset("utf8mb4");
?>