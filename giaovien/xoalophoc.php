<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: ../trangdangnhap.php");
    exit;
}

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];

    // Chỉ cho phép xóa lớp của chính giáo viên đó
    $stmt = $conn->prepare("DELETE FROM classes WHERE id = ? AND giaovien_id = ?");
    $stmt->bind_param("ii", $id, $_SESSION['user_id']);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "Đã xóa lớp học thành công!";
    } else {
        $_SESSION['error'] = "Không thể xóa lớp!";
    }
}

header("Location: index.php");
exit;
?>