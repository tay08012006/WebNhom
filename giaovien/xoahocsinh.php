<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../config.php';

// Chỉ giáo viên mới có quyền xóa học sinh
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: ../trangdangnhap.php");
    exit;
}

$student_id = (int)($_GET['student_id'] ?? 0);
$ma_lop     = trim($_GET['malop'] ?? '');

if ($student_id > 0 && !empty($ma_lop)) {
    // Lấy class_id từ ma_lop, đồng thời kiểm tra lớp này có thuộc giáo viên hiện tại không
    $stmt = $conn->prepare("SELECT id FROM classes WHERE ma_lop = ? AND giaovien_id = ?");
    $stmt->bind_param("si", $ma_lop, $_SESSION['user_id']);
    $stmt->execute();
    $class = $stmt->get_result()->fetch_assoc();

    if ($class) {
        $class_id = $class['id'];
        $del = $conn->prepare("DELETE FROM class_enrollments WHERE user_id = ? AND class_id = ?");
        $del->bind_param("ii", $student_id, $class_id);
        $del->execute();
    }
}

header("Location: phonghoc.php?malop=" . urlencode($ma_lop) . "&tab=moi-nguoi");
exit;