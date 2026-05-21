<?php
session_start();
require_once '../config.php'; // Đảm bảo đúng đường dẫn kết nối đến cơ sở dữ liệu

// Kiểm tra quyền truy cập của Giáo viên
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    die("Chỉ giáo viên mới có quyền thực hiện hành động này.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $quiz_title       = trim($_POST['quiz_title'] ?? '');
    $ma_lop           = trim($_POST['ma_lop'] ?? '');
    $duration_minutes = isset($_POST['duration_minutes']) ? intval($_POST['duration_minutes']) : 15;
    
    if (empty($quiz_title) || empty($ma_lop)) {
        die("Vui lòng nhập đầy đủ tiêu đề bài trắc nghiệm.");
    }
    
    // Lấy class_id từ ma_lop trong cơ sở dữ liệu
    $stmt_class = $conn->prepare("SELECT id FROM classes WHERE ma_lop = ?");
    $stmt_class->bind_param("s", $ma_lop);
    $stmt_class->execute();
    $class_result = $stmt_class->get_result()->fetch_assoc();
    
    if (!$class_result) {
        die("Không tìm thấy lớp học hợp lệ.");
    }
    $class_id = $class_result['id'];

    // 1. Lưu thông tin tiêu đề và thời gian bài kiểm tra vào bảng `quizzes`
    $stmt_quiz = $conn->prepare("INSERT INTO quizzes (class_id, title, duration_minutes) VALUES (?, ?, ?)");
    $stmt_quiz->bind_param("isi", $class_id, $quiz_title, $duration_minutes);
    
    if ($stmt_quiz->execute()) {
        $quiz_id = $conn->insert_id; // Lấy ID của bài quiz vừa sinh ra tự động

        // 2. Nhận mảng tập hợp các câu hỏi và đáp án từ Form gửi lên
        $questions = $_POST['question_text'] ?? [];
        $ans_a     = $_POST['ans_a'] ?? [];
        $ans_b     = $_POST['ans_b'] ?? [];
        $ans_c     = $_POST['ans_c'] ?? [];
        $ans_d     = $_POST['ans_d'] ?? [];
        $correct   = $_POST['correct_ans'] ?? [];

        // 3. Chuẩn bị câu lệnh mẫu để thêm câu hỏi lặp tuần tự
        $stmt_question = $conn->prepare("INSERT INTO questions (quiz_id, question_text, ans_a, ans_b, ans_c, ans_d, correct_ans) VALUES (?, ?, ?, ?, ?, ?, ?)");
        
        for ($i = 0; $i < count($questions); $i++) {
            // Loại bỏ các ô nhập liệu rỗng không hợp lệ
            if (isset($questions[$i]) && !empty(trim($questions[$i]))) {
                $q_text = trim($questions[$i]);
                $a      = trim($ans_a[$i] ?? '');
                $b      = trim($ans_b[$i] ?? '');
                $c      = trim($ans_c[$i] ?? '');
                $d      = trim($ans_d[$i] ?? '');
                $ans_ok = trim($correct[$i] ?? 'A');

                // Bind đầy đủ dữ liệu an toàn tránh SQL Injection
                $stmt_question->bind_param(
                    "issssss", 
                    $quiz_id, 
                    $q_text, 
                    $a, 
                    $b, 
                    $c, 
                    $d, 
                    $ans_ok
                );
                $stmt_question->execute();
            }
        }
        
        // Đóng các kết nối statement giải phóng bộ nhớ
        $stmt_question->close();
        $stmt_quiz->close();

        // ĐIỂM SỬA LỖI Ở ĐÂY: Điều hướng thẳng về tab "bai-tap"
        header("Location: phonghoc.php?malop=" . urlencode($ma_lop) . "&tab=bai-tap");
        exit;
    } else {
        die("Đã xảy ra lỗi trong quá trình lưu bài trắc nghiệm: " . $conn->error);
    }
} else {
    die("Phương thức truy cập không được hỗ trợ.");
}
?>