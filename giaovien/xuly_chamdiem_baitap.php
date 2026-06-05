<?php
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.name', 'GV_SESSION');
    session_start();
}
require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Không có quyền truy cập']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Phương thức không hợp lệ']);
    exit;
}

$nop_bai_id = isset($_POST['nop_bai_id']) ? intval($_POST['nop_bai_id']) : 0;
$diem       = isset($_POST['diem'])       ? floatval($_POST['diem'])      : null;
$nhan_xet   = isset($_POST['nhan_xet'])   ? trim($_POST['nhan_xet'])      : '';
$ma_lop     = isset($_POST['ma_lop'])     ? trim($_POST['ma_lop'])        : '';

if ($nop_bai_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID bài nộp không hợp lệ']);
    exit;
}
if ($diem === null || $diem < 0 || $diem > 10) {
    echo json_encode(['success' => false, 'message' => 'Điểm phải từ 0 đến 10']);
    exit;
}

// Kiểm tra bài nộp thuộc lớp của giáo viên đang đăng nhập
$stmt_check = $conn->prepare("
    SELECT n.id FROM nop_bai n
    INNER JOIN bai_tap bt ON n.bai_tap_id = bt.id
    INNER JOIN classes c ON bt.class_id = c.id
    WHERE n.id = ? AND c.giaovien_id = ?
");
$stmt_check->bind_param("ii", $nop_bai_id, $_SESSION['user_id']);
$stmt_check->execute();
if ($stmt_check->get_result()->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Bạn không có quyền chấm điểm bài này']);
    exit;
}

$stmt = $conn->prepare("UPDATE nop_bai SET diem = ?, nhan_xet = ? WHERE id = ?");
$stmt->bind_param("dsi", $diem, $nhan_xet, $nop_bai_id);

if ($stmt->execute()) {
    // Nếu là request AJAX
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
        echo json_encode(['success' => true, 'message' => 'Chấm điểm thành công', 'diem' => $diem, 'nhan_xet' => $nhan_xet]);
    } else {
        header("Location: phonghoc.php?malop=" . urlencode($ma_lop) . "&tab=bai-tap");
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Lỗi CSDL: ' . $conn->error]);
}
exit;
?>
