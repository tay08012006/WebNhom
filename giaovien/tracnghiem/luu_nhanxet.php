<?php
session_start();
require_once '../../config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    echo json_encode(['success' => false, 'message' => 'Không có quyền.']);
    exit;
}

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

$stmt = $conn->prepare("UPDATE quiz_results SET nhan_xet_gv = ? WHERE id = ?");
$stmt->bind_param("si", $nhan_xet, $result_id);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Lỗi DB: ' . $conn->error]);
}
$stmt->close();
?>