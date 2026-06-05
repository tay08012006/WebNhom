<?php
ini_set('session.name', 'GV_SESSION');
session_start();
require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: ../trangdangnhap.php");
    exit;
}

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];

    // Kiểm tra lớp có thuộc giáo viên này không
    $stmt_check = $conn->prepare("SELECT id FROM classes WHERE id = ? AND giaovien_id = ?");
    $stmt_check->bind_param("ii", $id, $_SESSION['user_id']);
    $stmt_check->execute();
    if ($stmt_check->get_result()->num_rows === 0) {
        $_SESSION['error'] = "Bạn không có quyền xóa lớp này!";
        header("Location: index.php");
        exit;
    }

    // Xóa tuần tự các bảng con trước để tránh lỗi foreign key
    $conn->begin_transaction();
    try {
        // 1. Xóa câu trả lời chi tiết của bài thi thuộc lớp này
        $conn->query("DELETE qa FROM quiz_answers qa
                      JOIN quiz_results qr ON qr.id = qa.result_id
                      JOIN quizzes q ON q.id = qr.quiz_id
                      WHERE q.class_id = $id");

        // 2. Xóa kết quả thi
        $conn->query("DELETE qr FROM quiz_results qr
                      JOIN quizzes q ON q.id = qr.quiz_id
                      WHERE q.class_id = $id");

        // 3. Xóa câu hỏi
        $conn->query("DELETE qu FROM questions qu
                      JOIN quizzes q ON q.id = qu.quiz_id
                      WHERE q.class_id = $id");

        // 4. Xóa đề thi
        $conn->query("DELETE FROM quizzes WHERE class_id = $id");

        // 5. Xóa bài nộp của bài tập thuộc lớp này
        $conn->query("DELETE nb FROM nop_bai nb
                      JOIN bai_tap bt ON bt.id = nb.bai_tap_id
                      WHERE bt.class_id = $id");

        // 6. Xóa bài tập
        $conn->query("DELETE FROM bai_tap WHERE class_id = $id");

        // 7. Xóa bảng tin
        $conn->query("DELETE FROM bang_tin WHERE class_id = $id");

        // 8. Xóa danh sách học sinh trong lớp
        $conn->query("DELETE FROM class_enrollments WHERE class_id = $id");

        // 9. Cuối cùng xóa lớp
        $stmt = $conn->prepare("DELETE FROM classes WHERE id = ? AND giaovien_id = ?");
        $stmt->bind_param("ii", $id, $_SESSION['user_id']);
        $stmt->execute();

        $conn->commit();
        $_SESSION['success'] = "Đã xóa lớp học và toàn bộ dữ liệu liên quan thành công!";

    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['error'] = "Lỗi khi xóa lớp: " . $e->getMessage();
    }
}

header("Location: index.php");
exit;
?>