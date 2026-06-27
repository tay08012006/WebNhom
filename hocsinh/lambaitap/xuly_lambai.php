<?php
// Khởi động session dành riêng cho học sinh
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.name', 'HS_SESSION');
    session_start();
}

// Gọi file cấu hình kết nối CSDL
include '../../config.php';

// Dừng chương trình nếu tài khoản không phải là học sinh
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'student') {
    die("Lỗi quyền truy cập.");
}

// Chặn truy cập trực tiếp bằng URL, chỉ cho phép gửi form (POST)
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Phương thức truy cập không hợp lệ.");
}

// Nhận các tham số ID bài thi, ID lớp, ID học sinh và danh sách đáp án
$quiz_id    = intval($_POST['quiz_id'] ?? 0);
$id_lop     = intval($_POST['id_lop']  ?? 0);
$id_hocsinh = $_SESSION['user_id'];
$answers    = isset($_POST['answers']) ? $_POST['answers'] : [];

// Báo lỗi nếu thiếu thông số quan trọng
if ($quiz_id <= 0 || $id_lop <= 0) die("Tham số không hợp lệ.");

// Xác thực xem học sinh có thực sự thuộc lớp học này không
$stmt_enroll = $conn->prepare(
    "SELECT id FROM class_enrollments WHERE user_id = ? AND class_id = ?"
);
$stmt_enroll->bind_param("ii", $id_hocsinh, $id_lop);
$stmt_enroll->execute();
if ($stmt_enroll->get_result()->num_rows === 0) {
    die("Bạn không thuộc lớp học này.");
}

// Xác thực xem bài thi này có nằm trong lớp học đó không
$stmt_quiz = $conn->prepare(
    "SELECT id FROM quizzes WHERE id = ? AND class_id = ?"
);
$stmt_quiz->bind_param("ii", $quiz_id, $id_lop);
$stmt_quiz->execute();
if ($stmt_quiz->get_result()->num_rows === 0) {
    die("Bài thi không tồn tại hoặc không thuộc lớp của bạn.");
}

// Kiểm tra xem học sinh đã nộp bài này chưa (chống nộp trùng 2 lần)
$stmt_check = $conn->prepare(
    "SELECT id FROM quiz_results WHERE quiz_id = ? AND student_id = ?"
);
$stmt_check->bind_param("ii", $quiz_id, $id_hocsinh);
$stmt_check->execute();
if ($stmt_check->get_result()->num_rows > 0) {
    // Nếu đã nộp rồi thì báo lỗi và đẩy về trang xem lại bài thi
    echo "<script>
        alert('Bạn đã nộp bài này rồi. Hệ thống đã ghi nhận kết quả!');
        window.location.href = 'xem_baithi.php?quiz_id=$quiz_id&id_lop=$id_lop';
    </script>";
    exit();
}

// Lấy danh sách đáp án đúng từ Database để tiến hành chấm điểm
$stmt_q = $conn->prepare(
    "SELECT id, correct_ans FROM questions WHERE quiz_id = ?"
);
$stmt_q->bind_param("i", $quiz_id);
$stmt_q->execute();
$res_q = $stmt_q->get_result();

// Khởi tạo các biến lưu trữ kết quả chấm điểm
$total_questions = $res_q->num_rows;
$correct_count   = 0;
$student_details = [];

// Vòng lặp duyệt qua từng câu hỏi để so sánh đáp án
while ($q = $res_q->fetch_assoc()) {
    $q_id    = $q['id'];
    $correct = trim($q['correct_ans']);
    $user_ans = isset($answers[$q_id]) ? trim($answers[$q_id]) : '';

    $is_correct = 0;
    
    // So sánh đáp án của HS và đáp án đúng (không phân biệt chữ hoa/thường)
    if (strcasecmp($user_ans, $correct) === 0) {
        $correct_count++;
        $is_correct = 1;
    }

    // Lưu lại chi tiết trả lời của từng câu để lát nữa Insert vào CSDL
    $student_details[] = [
        'q_id'  => $q_id,
        'u_ans' => $user_ans,
        'is_c'  => $is_correct,
    ];
}

// Tính điểm tổng trên thang điểm 10 (làm tròn 2 chữ số thập phân)
$score = 0;
if ($total_questions > 0) {
    $score = round(($correct_count / $total_questions) * 10, 2);
}

// Bắt đầu quá trình lưu vào Database (Dùng Transaction để đảm bảo an toàn dữ liệu)
$conn->begin_transaction();
try {
    
    // Lưu kết quả tổng quan (tổng điểm, số câu đúng) vào bảng quiz_results
    $stmt_ins_res = $conn->prepare(
        "INSERT INTO quiz_results 
        (quiz_id, student_id, total_questions, correct_count, score, submitted_at) 
        VALUES (?, ?, ?, ?, ?, NOW())"
    );
    $stmt_ins_res->bind_param("iiiid", $quiz_id, $id_hocsinh, $total_questions, $correct_count, $score);
    $stmt_ins_res->execute();
    
    // Lấy ID của bảng điểm vừa tạo
    $result_id = $conn->insert_id;

    // Lưu chi tiết học sinh đã chọn đáp án nào cho từng câu vào bảng quiz_answers
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

    // Xác nhận lưu toàn bộ thay đổi vào CSDL
    $conn->commit();

    // Hiển thị hộp thoại kết quả và chuyển hướng về trang xem chi tiết (Đã xóa các icon)
    echo "<script>
        alert('Nộp bài thành công!\\nBạn làm đúng $correct_count/$total_questions câu.\\n\\nĐiểm của bạn: $score/10');
        window.location.href = 'xem_baithi.php?quiz_id=$quiz_id&id_lop=$id_lop';
    </script>";

} catch (Exception $e) {
    // Nếu có lỗi hệ thống thì hoàn tác lại toàn bộ (không lưu bậy bạ)
    $conn->rollback();
    echo "Lỗi hệ thống khi lưu kết quả: " . htmlspecialchars($e->getMessage());
}
?>
