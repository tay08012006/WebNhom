<?php
session_start();
// Gọi file config.php (giống cấu trúc các file khác của bạn)
require_once '../config.php'; 

// Kiểm tra quyền (chỉ giáo viên mới được xem)
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    die("<div style='padding: 20px; font-family: sans-serif;'>Bạn không có quyền truy cập trang này hoặc phiên đăng nhập đã hết hạn. <a href='../trangdangnhap.php'>Đăng nhập lại</a></div>");
}

$quiz_id = isset($_GET['quiz_id']) ? intval($_GET['quiz_id']) : 0;
$ma_lop = isset($_GET['malop']) ? trim($_GET['malop']) : '';

if ($quiz_id <= 0) {
    die("<div style='padding: 20px; font-family: sans-serif;'>Mã bài trắc nghiệm không hợp lệ.</div>");
}

// 1. Lấy thông tin tiêu đề của bài quiz từ database
$stmt_quiz = $conn->prepare("SELECT * FROM quizzes WHERE id = ?");
$stmt_quiz->bind_param("i", $quiz_id);
$stmt_quiz->execute();
$result_quiz = $stmt_quiz->get_result();

if ($result_quiz->num_rows === 0) {
    die("<div style='padding: 20px; font-family: sans-serif;'>Bài trắc nghiệm không tồn tại hoặc đã bị xóa.</div>");
}
$quiz = $result_quiz->fetch_assoc();

// 2. Lấy danh sách các câu hỏi thuộc bài quiz này
$stmt_questions = $conn->prepare("SELECT * FROM questions WHERE quiz_id = ? ORDER BY id ASC");
$stmt_questions->bind_param("i", $quiz_id);
$stmt_questions->execute();
$questions = $stmt_questions->get_result();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chi tiết đề thi: <?= htmlspecialchars($quiz['title']) ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Nunito', sans-serif; background: #f0f2f5; padding: 20px; color: #333; margin: 0; }
        .container { max-width: 850px; margin: auto; background: #fff; padding: 30px; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); }
        .header { display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #e4e6eb; padding-bottom: 15px; margin-bottom: 25px; }
        .header h2 { margin: 0; color: #1a73e8; font-size: 24px; }
        .btn-back { background: #e4e6eb; color: #050505; padding: 10px 18px; text-decoration: none; border-radius: 8px; font-weight: 600; transition: 0.2s; font-size: 14px; }
        .btn-back:hover { background: #d8dadf; }
        .question-card { background: #fff; border: 1px solid #ddd; border-radius: 12px; padding: 20px; margin-bottom: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.02); }
        .question-title { font-weight: 700; font-size: 16px; margin-bottom: 15px; color: #1c1e21; border-left: 4px solid #1a73e8; padding-left: 10px; }
        .grid-options { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
        .option { padding: 12px 15px; border-radius: 8px; background: #f0f2f5; border: 1px solid transparent; font-size: 15px; color: #1c1e21; }
        .option.correct { border-color: #31a24c; background: #e7f3eb; font-weight: 600; color: #187a33; }
        .correct-badge { display: inline-block; margin-left: 10px; background: #31a24c; color: white; font-size: 12px; padding: 3px 8px; border-radius: 12px; font-weight: normal; }
        .empty-state { text-align: center; color: #65676b; padding: 40px 0; font-style: italic; }
        
        /* Mobile responsive */
        @media (max-width: 600px) {
            .grid-options { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <h2><?= htmlspecialchars($quiz['title']) ?></h2>
        <a href="javascript:history.back()" class="btn-back">⬅ Quay lại</a>
    </div>

    <?php if ($questions->num_rows > 0): ?>
        <?php $i = 1; while ($q = $questions->fetch_assoc()): ?>
            <div class="question-card">
                <div class="question-title">
                    Câu <?= $i++ ?>: <?= nl2br(htmlspecialchars($q['question_text'])) ?>
                </div>
                
                <div class="grid-options">
                    <div class="option <?= trim($q['correct_ans']) === 'A' ? 'correct' : '' ?>">
                        <b>A.</b> <?= htmlspecialchars($q['ans_a']) ?>
                        <?= trim($q['correct_ans']) === 'A' ? '<span class="correct-badge">✓ Đáp án đúng</span>' : '' ?>
                    </div>
                    
                    <div class="option <?= trim($q['correct_ans']) === 'B' ? 'correct' : '' ?>">
                        <b>B.</b> <?= htmlspecialchars($q['ans_b']) ?>
                        <?= trim($q['correct_ans']) === 'B' ? '<span class="correct-badge">✓ Đáp án đúng</span>' : '' ?>
                    </div>
                    
                    <div class="option <?= trim($q['correct_ans']) === 'C' ? 'correct' : '' ?>">
                        <b>C.</b> <?= htmlspecialchars($q['ans_c']) ?>
                        <?= trim($q['correct_ans']) === 'C' ? '<span class="correct-badge">✓ Đáp án đúng</span>' : '' ?>
                    </div>
                    
                    <div class="option <?= trim($q['correct_ans']) === 'D' ? 'correct' : '' ?>">
                        <b>D.</b> <?= htmlspecialchars($q['ans_d']) ?>
                        <?= trim($q['correct_ans']) === 'D' ? '<span class="correct-badge">✓ Đáp án đúng</span>' : '' ?>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="empty-state">
            <h3 style="margin-bottom: 5px;">Chưa có câu hỏi nào</h3>
            <p>Bài trắc nghiệm này hiện tại chưa có dữ liệu câu hỏi.</p>
        </div>
    <?php endif; ?>
</div>

</body>
</html>