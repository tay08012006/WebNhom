<?php
ini_set('session.name', 'GV_SESSION');
session_start();
require_once '../../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    die("Chỉ giáo viên mới có quyền thực hiện hành động này.");
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Phương thức truy cập không được hỗ trợ.");
}

$quiz_title       = trim($_POST['quiz_title'] ?? '');
$ma_lop           = trim($_POST['ma_lop'] ?? '');
$duration_minutes = isset($_POST['duration_minutes']) ? intval($_POST['duration_minutes']) : 15;
$so_made          = isset($_POST['so_made']) ? max(1, min(20, intval($_POST['so_made']))) : 1;
$shuffle_questions = isset($_POST['shuffle_questions']) ? intval($_POST['shuffle_questions']) : 1;
$shuffle_answers   = isset($_POST['shuffle_answers']) ? intval($_POST['shuffle_answers']) : 1;

if (empty($quiz_title) || empty($ma_lop)) {
    die("Vui lòng nhập đầy đủ tiêu đề bài trắc nghiệm.");
}

// Lấy class_id
$stmt_class = $conn->prepare("SELECT id FROM classes WHERE ma_lop = ?");
$stmt_class->bind_param("s", $ma_lop);
$stmt_class->execute();
$class_result = $stmt_class->get_result()->fetch_assoc();
if (!$class_result) die("Không tìm thấy lớp học hợp lệ.");
$class_id = $class_result['id'];

// Lưu quiz — quiz_mode = 'random' nếu so_made > 1, ngược lại = 'fixed'
$quiz_mode = $so_made > 1 ? 'random' : 'fixed';

$stmt_quiz = $conn->prepare(
    "INSERT INTO quizzes (class_id, title, duration_minutes, quiz_mode, questions_per_exam, shuffle_answers, shuffle_questions)
     VALUES (?, ?, ?, ?, ?, ?, ?)"
);
$stmt_quiz->bind_param("isisiis",
    $class_id, $quiz_title, $duration_minutes,
    $quiz_mode, $so_made, $shuffle_answers, $shuffle_questions
);

if (!$stmt_quiz->execute()) {
    die("Lỗi lưu quiz: " . $conn->error);
}
$quiz_id = $conn->insert_id;

// Lưu câu hỏi
$questions = $_POST['question_text'] ?? [];
$ans_a     = $_POST['ans_a'] ?? [];
$ans_b     = $_POST['ans_b'] ?? [];
$ans_c     = $_POST['ans_c'] ?? [];
$ans_d     = $_POST['ans_d'] ?? [];
$correct   = $_POST['correct_ans'] ?? [];

$stmt_q = $conn->prepare(
    "INSERT INTO questions (quiz_id, question_text, ans_a, ans_b, ans_c, ans_d, correct_ans)
     VALUES (?, ?, ?, ?, ?, ?, ?)"
);
for ($i = 0; $i < count($questions); $i++) {
    if (!isset($questions[$i]) || empty(trim($questions[$i]))) continue;
    $q_text = trim($questions[$i]);
    $a = trim($ans_a[$i] ?? '');
    $b = trim($ans_b[$i] ?? '');
    $c = trim($ans_c[$i] ?? '');
    $d = trim($ans_d[$i] ?? '');
    $ans_ok = trim($correct[$i] ?? 'A');
    $stmt_q->bind_param("issssss", $quiz_id, $q_text, $a, $b, $c, $d, $ans_ok);
    $stmt_q->execute();
}

$stmt_q->close();
$stmt_quiz->close();

header("Location: ../phonghoc.php?malop=" . urlencode($ma_lop) . "&tab=bai-tap");
exit;
?>
