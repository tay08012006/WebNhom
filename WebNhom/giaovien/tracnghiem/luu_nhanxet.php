<?php
ini_set('session.name', 'GV_SESSION');
session_start();
require_once '../../config.php';

header('Content-Type: application/json');

// Kiểm tra session giáo viên
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    echo json_encode(['success' => false, 'message' => 'Phiên đăng nhập hết hạn. Vui lòng đăng nhập lại.']);
    exit;
}

// Chỉ chấp nhận POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Phương thức không hợp lệ.']);
    exit;
}

$result_id = isset($_POST['result_id']) ? intval($_POST['result_id']) : 0;
$nhan_xet  = isset($_POST['nhan_xet'])  ? trim($_POST['nhan_xet'])   : '';

if ($result_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID kết quả không hợp lệ.']);
    exit;
}

// Kiểm tra result_id có thuộc bài thi của lớp giáo viên này không (bảo mật)
$stmt_check = $conn->prepare(
    "SELECT qr.id FROM quiz_results qr
     JOIN quizzes q ON q.id = qr.quiz_id
     JOIN classes c ON c.id = q.class_id
     WHERE qr.id = ? AND c.giaovien_id = ?"
);
$stmt_check->bind_param("ii", $result_id, $_SESSION['user_id']);
$stmt_check->execute();
if ($stmt_check->get_result()->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Không có quyền chỉnh sửa nhận xét này.']);
    exit;
}

$stmt = $conn->prepare("UPDATE quiz_results SET nhan_xet_gv = ? WHERE id = ?");
$stmt->bind_param("si", $nhan_xet, $result_id);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Lưu nhận xét thành công.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Lỗi DB: ' . $conn->error]);
}
$stmt->close();
?>