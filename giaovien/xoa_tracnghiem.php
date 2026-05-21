<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    die("Bạn không có quyền thực hiện hành động này.");
}

$quiz_id = isset($_GET['quiz_id']) ? intval($_GET['quiz_id']) : 0;
$ma_lop = isset($_GET['malop']) ? trim($_GET['malop']) : '';

if ($quiz_id > 0) {
    // 1. Xóa các câu hỏi của bài quiz trước
    $stmt1 = $conn->prepare("DELETE FROM questions WHERE quiz_id = ?");
    $stmt1->bind_param("i", $quiz_id);
    $stmt1->execute();

    // 2. Xóa bài quiz
    $stmt2 = $conn->prepare("DELETE FROM quizzes WHERE id = ?");
    $stmt2->bind_param("i", $quiz_id);
    $stmt2->execute();
}

// Trở về trang trước đó
header("Location: " . $_SERVER['HTTP_REFERER']);
exit;
?>