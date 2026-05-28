<?php
ini_set('session.name', 'HS_SESSION');
session_start();
include '../../config.php';

// [KIỂM TRA 1] Chỉ học sinh mới được nộp bài
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'student') {
    die("Lỗi quyền truy cập.");
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Phương thức truy cập không hợp lệ.");
}

$quiz_id    = intval($_POST['quiz_id'] ?? 0);
$id_lop     = intval($_POST['id_lop']  ?? 0);
$id_hocsinh = $_SESSION['user_id'];
$answers    = isset($_POST['answers']) ? $_POST['answers'] : [];

if ($quiz_id <= 0 || $id_lop <= 0) die("Tham số không hợp lệ.");

// [KIỂM TRA 2] Học sinh phải thuộc lớp này
$stmt_enroll = $conn->prepare(
    "SELECT id FROM class_enrollments WHERE user_id = ? AND class_id = ?"
);
$stmt_enroll->bind_param("ii", $id_hocsinh, $id_lop);
$stmt_enroll->execute();
if ($stmt_enroll->get_result()->num_rows === 0) {
    die("Bạn không thuộc lớp học này.");
}

// [KIỂM TRA 3] Bài thi phải thuộc đúng lớp đó
$stmt_quiz = $conn->prepare(
    "SELECT id FROM quizzes WHERE id = ? AND class_id = ?"
);
$stmt_quiz->bind_param("ii", $quiz_id, $id_lop);
$stmt_quiz->execute();
if ($stmt_quiz->get_result()->num_rows === 0) {
    die("Bài thi không tồn tại hoặc không thuộc lớp của bạn.");
}

// [KIỂM TRA 4] Chống nộp bài trùng lặp
$stmt_check = $conn->prepare(
    "SELECT id FROM quiz_results WHERE quiz_id = ? AND student_id = ?"
);
$stmt_check->bind_param("ii", $quiz_id, $id_hocsinh);
$stmt_check->execute();
if ($stmt_check->get_result()->num_rows > 0) {
    // Đã nộp rồi — chuyển về xem kết quả
    echo "<script>
        alert('Bạn đã nộp bài này rồi. Hệ thống đã ghi nhận kết quả!');
        window.location.href = 'xem_baithi.php?quiz_id=$quiz_id&id_lop=$id_lop';
    </script>";
    exit();
}

// ============================================================
// CHẤM ĐIỂM TỰ ĐỘNG
// ============================================================
$stmt_q = $conn->prepare(
    "SELECT id, correct_ans FROM questions WHERE quiz_id = ?"
);
$stmt_q->bind_param("i", $quiz_id);
$stmt_q->execute();
$res_q = $stmt_q->get_result();

$total_questions = $res_q->num_rows;
$correct_count   = 0;
$student_details = [];

while ($q = $res_q->fetch_assoc()) {
    $q_id    = $q['id'];
    $correct = trim($q['correct_ans']);
    $user_ans = isset($answers[$q_id]) ? trim($answers[$q_id]) : '';

    $is_correct = 0;
    // So sánh không phân biệt hoa thường
    if (strcasecmp($user_ans, $correct) === 0) {
        $correct_count++;
        $is_correct = 1;
    }

    $student_details[] = [
        'q_id' => $q_id,
        'u_ans' => $user_ans,
        'is_c'  => $is_correct,
    ];
}

$score = 0;
if ($total_questions > 0) {
    $score = round(($correct_count / $total_questions) * 10, 2);
}

// ============================================================
// LƯU KẾT QUẢ
// ============================================================
$conn->begin_transaction();
try {
    // 1. Bảng quiz_results — kết quả tổng
    $stmt_ins_res = $conn->prepare(
        "INSERT INTO quiz_results 
         (quiz_id, student_id, total_questions, correct_count, score, submitted_at) 
         VALUES (?, ?, ?, ?, ?, NOW())"
    );
    $stmt_ins_res->bind_param("iiiid", $quiz_id, $id_hocsinh, $total_questions, $correct_count, $score);
    $stmt_ins_res->execute();
    $result_id = $conn->insert_id;

    // 2. Bảng quiz_answers — chi tiết từng câu
    $stmt_ins_ans = $conn->prepare(
        "INSERT INTO quiz_answers (result_id, question_id, student_answer, is_correct) 
         VALUES (?, ?, ?, ?)"
    );
    foreach ($student_details as $detail) {
        $stmt_ins_ans->bind_param(
            "iisi",
            $result_id,
            $detail['q_id'],
            $detail['u_ans'],
            $detail['is_c']
        );
        $stmt_ins_ans->execute();
    }

    $conn->commit();

    // Thông báo kết quả rồi chuyển về trang xem bài làm của học sinh
    echo "<script>
        alert('🎉 Nộp bài thành công!\\nBạn làm đúng $correct_count/$total_questions câu.\\n\\n🏆 Điểm của bạn: $score/10');
        window.location.href = 'xem_baithi.php?quiz_id=$quiz_id&id_lop=$id_lop';
    </script>";

} catch (Exception $e) {
    $conn->rollback();
    echo "Lỗi hệ thống khi lưu kết quả: " . htmlspecialchars($e->getMessage());
}
?>
