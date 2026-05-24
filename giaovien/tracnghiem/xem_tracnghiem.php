<?php
ini_set('session.name', 'GV_SESSION');
session_start();
require_once '../../config.php'; 

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    die("Bạn không có quyền truy cập.");
}

$quiz_id = isset($_GET['quiz_id']) ? intval($_GET['quiz_id']) : 0;

if ($quiz_id <= 0) {
    die("Mã bài trắc nghiệm không hợp lệ.");
}

$stmt_quiz = $conn->prepare("SELECT * FROM quizzes WHERE id = ?");
$stmt_quiz->bind_param("i", $quiz_id);
$stmt_quiz->execute();
$quiz = $stmt_quiz->get_result()->fetch_assoc();

if (!$quiz) die("Bài trắc nghiệm không tồn tại.");

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
        body { font-family: 'Nunito', sans-serif; background: #f8fafc; padding: 20px; }
        .container { max-width: 900px; margin: auto; background: white; padding: 30px; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); }
        .question-card { 
            background: white; 
            border: 1px solid #e2e8f0; 
            border-radius: 16px; 
            padding: 24px; 
            margin-bottom: 24px; 
            box-shadow: 0 2px 8px rgba(0,0,0,0.04); 
        }
        .question-title { 
            font-weight: 700; 
            font-size: 18px; 
            margin-bottom: 18px; 
            color: #1e2937; 
        }
        .correct-answer {
            background: #ecfdf5;
            border: 2px solid #10b981;
            padding: 16px 20px;
            border-radius: 12px;
            font-size: 17px;
            font-weight: 600;
            color: #065f46;
        }
        .fill-answer {
            background: #ecfdf5;
            border: 2px solid #10b981;
            padding: 16px 20px;
            border-radius: 12px;
            font-size: 17px;
            font-weight: 600;
            color: #065f46;
            display: block;
        }
    </style>
</head>
<body>

<div class="container">
    <div style="margin-bottom: 20px;">
        <a href="javascript:history.back()" style="display: inline-flex; align-items: center; gap: 8px; background: #f1f5f9; color: #475569; padding: 10px 20px; border-radius: 10px; text-decoration: none; font-weight: 700; font-size: 15px; transition: all 0.2s;" onmouseover="this.style.background='#e2e8f0'" onmouseout="this.style.background='#f1f5f9'">
            ← Quay lại
        </a>
    </div>
    <h2 class="text-2xl font-bold text-center mb-10"><?= htmlspecialchars($quiz['title']) ?></h2>

    <?php $i = 1; while ($q = $questions->fetch_assoc()): ?>
        <div class="question-card">
            <div class="question-title">
                Câu <?= $i++ ?>: <?= nl2br(htmlspecialchars($q['question_text'])) ?>
            </div>

            <?php 
            $correct = trim($q['correct_ans']);
            // MC: có đáp án A và B được nhập
            $isMC  = !empty($q['ans_a']) && !empty($q['ans_b']);
            // Đúng/Sai: ans_a rỗng và correct_ans là A hoặc B
            $isTF  = !$isMC && in_array($correct, ['A', 'B']);
            // Điền chỗ trống: còn lại
            $isFill = !$isMC && !$isTF;
            ?>

            <?php if ($isTF): // ĐÚNG / SAI ?>
                <div class="correct-answer">
                    <?= $correct === 'A' ? '✅ Đúng' : '✅ Sai' ?>
                </div>

            <?php elseif ($isFill): // ĐIỀN CHỖ TRỐNG ?>
                <div class="fill-answer">
                    ✏️ <?= htmlspecialchars($q['correct_ans']) ?>
                </div>

            <?php else: // TRẮC NGHIỆM 4 ĐÁP ÁN - Chỉ hiện đáp án đúng ?>
                <div class="correct-answer">
                    <?php 
                    if ($correct === 'A') echo 'A. ' . htmlspecialchars($q['ans_a']);
                    elseif ($correct === 'B') echo 'B. ' . htmlspecialchars($q['ans_b']);
                    elseif ($correct === 'C') echo 'C. ' . htmlspecialchars($q['ans_c']);
                    elseif ($correct === 'D') echo 'D. ' . htmlspecialchars($q['ans_d']);
                    ?>
                    <span class="float-right text-emerald-600"></span>
                </div>
            <?php endif; ?>
        </div>
    <?php endwhile; ?>
</div>

</body>
</html>