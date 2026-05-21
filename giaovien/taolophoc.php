<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: ../trangdangnhap.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ten_lop = trim($_POST['ten_lop']);
    $hoc_ky  = trim($_POST['hoc_ky']); // Lấy giá trị học kỳ từ form
    $mo_ta   = trim($_POST['mo_ta'] ?? '');
    
    // Tạo mã lớp ngẫu nhiên 6 ký tự
    $ma_lop = strtoupper(substr(md5(uniqid()), 0, 6));
    
    // Đã thêm trường hoc_ky vào câu lệnh SQL INSERT dưới đây:
    $stmt = $conn->prepare("INSERT INTO classes (ma_lop, ten_lop, hoc_ky, giaovien_id, mo_ta) VALUES (?, ?, ?, ?, ?)");
    
    // Cập nhật bind_param thành "ssssi" tương ứng với 5 dấu hỏi chấm ở trên
    $stmt->bind_param("sssis", $ma_lop, $ten_lop, $hoc_ky, $_SESSION['user_id'], $mo_ta);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "Tạo lớp thành công! Mã lớp: <b>$ma_lop</b>";
        header("Location: index.php");
        exit;
    } else {
        $_SESSION['error'] = "Lỗi khi tạo lớp học!";
    }
}
header("Location: index.php");
exit;
?>