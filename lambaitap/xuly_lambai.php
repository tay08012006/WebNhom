<?php
session_start();
include '../config.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'student') {
    die("Lỗi quyền truy cập.");
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Phương thức truy cập không hợp lệ.");
}

$quiz_id    = intval($_POST['quiz_id']);
$id_lop     = intval($_POST['id_lop']);
$id_hocsinh = $_SESSION['user_id'];
$answers    = isset($_POST['answers']) ? $_POST['answers'] : [];

if ($quiz_id <= 0) die("Mã bài thi không hợp lệ.");

// 1. Chống lỗi nộp bài trùng lặp
$stmt_check = $conn->prepare("SELECT id FROM quiz_results WHERE quiz_id = ? AND student_id = ?");
$stmt_check->bind_param("ii", $quiz_id, $id_hocsinh);
$stmt_check->execute();
if ($stmt_check->get_result()->num_rows > 0) {
    die("Bạn đã nộp bài này hệ thống đã ghi nhận rồi.");
}

// 2. Chấm điểm tự động
$stmt_q = $conn->prepare("SELECT id, correct_ans FROM questions WHERE quiz_id = ?");
$stmt_q->bind_param("i", $quiz_id);
$stmt_q->execute();
$res_q = $stmt_q->get_result();

$total_questions = $res_q->num_rows;
$correct_count = 0;
$student_details = [];

while ($q = $res_q->fetch_assoc()) {
    $q_id = $q['id'];
    $correct = trim($q['correct_ans']);
    $user_ans = isset($answers[$q_id]) ? trim($answers[$q_id]) : '';
    
    $is_correct = 0;
    if (strcasecmp($user_ans, $correct) === 0) {
        $correct_count++;
        $is_correct = 1;
    }
    
    $student_details[] = [
        'q_id' => $q_id,
        'u_ans' => $user_ans,
        'is_c' => $is_correct
    ];
}

$score = 0;
if ($total_questions > 0) {
    $score = round(($correct_count / $total_questions) * 10, 2);
}

// 3. LƯU BẢNG: quiz_results (Kết quả chung)
$sql_res = "INSERT INTO quiz_results (quiz_id, student_id, total_questions, correct_count, score, submitted_at) VALUES (?, ?, ?, ?, ?, NOW())";
$stmt_ins_res = $conn->prepare($sql_res);
// 'iiiid' = int, int, int, int, double (vì score là số thực)
$stmt_ins_res->bind_param("iiiid", $quiz_id, $id_hocsinh, $total_questions, $correct_count, $score);

if ($stmt_ins_res->execute()) {
    $result_id = $stmt_ins_res->insert_id;
    
    // 4. LƯU BẢNG: quiz_answers (Chi tiết từng câu học sinh chọn)
    $sql_ans = "INSERT INTO quiz_answers (result_id, question_id, student_answer, is_correct) VALUES (?, ?, ?, ?)";
    $stmt_ins_ans = $conn->prepare($sql_ans);
    
    foreach ($student_details as $detail) {
        $stmt_ins_ans->bind_param("iisi", $result_id, $detail['q_id'], $detail['u_ans'], $detail['is_c']);
        $stmt_ins_ans->execute();
    }
    
    echo "<script>
        alert('Đã hoàn thành! Bạn làm đúng $correct_count/$total_questions câu. Hệ thống chấm $score điểm.');
        window.location.href = 'phonghoc.php?id=$id_lop';
    </script>";
} else {
    echo "Lỗi hệ thống: " . $conn->error;
}
?>