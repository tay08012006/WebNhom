<?php
ini_set('session.name', 'HS_SESSION');
session_start();
include '../../config.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'student') {
    header("Location: ../trangdangnhap.php");
    exit();
}

$quiz_id = isset($_GET['quiz_id']) ? intval($_GET['quiz_id']) : 0;
$id_lop  = isset($_GET['id_lop']) ? intval($_GET['id_lop']) : 0;

if ($quiz_id <= 0) die("Mã bài thi không hợp lệ.");

// Lấy thông tin bài thi
$stmt_quiz = $conn->prepare("SELECT * FROM quizzes WHERE id = ?");
$stmt_quiz->bind_param("i", $quiz_id);
$stmt_quiz->execute();
$quiz = $stmt_quiz->get_result()->fetch_assoc();
if (!$quiz) die("Đề thi không tồn tại.");

// Lấy kết quả của học sinh
$stmt_result = $conn->prepare("SELECT * FROM quiz_results WHERE quiz_id = ? AND student_id = ?");
$stmt_result->bind_param("ii", $quiz_id, $_SESSION['user_id']);
$stmt_result->execute();
$result = $stmt_result->get_result()->fetch_assoc();
if (!$result) die("Bạn chưa làm bài thi này.");

$result_id = $result['id'];

// Lấy chi tiết câu trả lời
$stmt_answers = $conn->prepare("
    SELECT qa.*, q.question_text, q.ans_a, q.ans_b, q.ans_c, q.ans_d, q.correct_ans
    FROM quiz_answers qa
    JOIN questions q ON q.id = qa.question_id
    WHERE qa.result_id = ?
    ORDER BY q.id ASC
");
$stmt_answers->bind_param("i", $result_id);
$stmt_answers->execute();
$answers = $stmt_answers->get_result();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chi tiết bài làm: <?= htmlspecialchars($quiz['title']) ?></title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; background: #f0f4f8; }
        
        .header { background: linear-gradient(135deg, #0277bd 0%, #01579b 100%); color: white; padding: 24px 32px; }
        .header h1 { font-size: 24px; margin-bottom: 8px; }
        
        .score-bar { background: white; padding: 20px 32px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 8px rgba(0,0,0,0.06); }
        .score-info { display: flex; gap: 40px; }
        .score-item { display: flex; flex-direction: column; }
        .score-item .label { font-size: 12px; color: #718096; font-weight: 700; text-transform: uppercase; }
        .score-item .value { font-size: 24px; font-weight: 800; color: #0277bd; margin-top: 4px; }
        
        .container { max-width: 900px; margin: 32px auto; padding: 0 20px 40px; }
        
        .back-link { display: inline-flex; align-items: center; gap: 8px; color: #0277bd; text-decoration: none; font-weight: 600; margin-bottom: 20px; }
        .back-link:hover { color: #01579b; }
        
        .answer-card { background: white; padding: 24px; margin-bottom: 20px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); }
        .q-number { font-size: 14px; font-weight: 800; color: #718096; margin-bottom: 8px; }
        .q-text { font-size: 16px; font-weight: 700; color: #2d3748; margin-bottom: 16px; }
        
        .options { display: flex; flex-direction: column; gap: 10px; margin-bottom: 16px; }
        .option { padding: 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 14px; }
        .option.user-ans { border-color: #0277bd; background: #dbeafe; }
        .option.correct-ans { border-color: #16a34a; background: #dcfce7; }
        .option.wrong-ans { border-color: #dc2626; background: #fee2e2; }
        
        .result-row { display: flex; gap: 12px; padding-top: 12px; border-top: 1px solid #e2e8f0; }
        .result-item { flex: 1; }
        .result-label { font-size: 12px; color: #718096; font-weight: 700; }
        .result-value { font-size: 14px; font-weight: 700; color: #2d3748; margin-top: 4px; }
        .correct { color: #16a34a; }
        .incorrect { color: #dc2626; }

        /* NHẬN XÉT GV - BANNER NỔI BẬT */
        .teacher-comment-banner {
            background: linear-gradient(135deg, #dbeafe 0%, #eff6ff 100%);
            border: 2px solid #93c5fd;
            border-radius: 16px;
            padding: 20px 24px;
            margin: 0 0 24px 0;
            box-shadow: 0 4px 16px rgba(59,130,246,0.15);
        }
        .teacher-comment-banner .banner-header {
            display: flex; align-items: center; gap: 10px; margin-bottom: 12px;
        }
        .teacher-comment-banner .banner-icon {
            width: 40px; height: 40px; background: #3b82f6; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 18px; flex-shrink: 0;
        }
        .teacher-comment-banner .banner-title {
            font-size: 15px; font-weight: 800; color: #1d4ed8;
        }
        .teacher-comment-banner .banner-sub {
            font-size: 12px; color: #3b82f6; font-weight: 600;
        }
        .teacher-comment-banner .banner-body {
            font-size: 15px; color: #1e3a8a; line-height: 1.7; font-weight: 600;
            background: white; padding: 14px 18px; border-radius: 10px;
            border-left: 4px solid #3b82f6;
        }
    </style>
</head>
<body>

<div class="header">
    <h1><?= htmlspecialchars($quiz['title']) ?></h1>
</div>

<div class="score-bar">
    <div class="score-info">
        <div class="score-item">
            <span class="label">Điểm</span>
            <span class="value"><?= number_format($result['score'], 1) ?>/10</span>
        </div>
        <div class="score-item">
            <span class="label">Câu đúng</span>
            <span class="value"><?= $result['correct_count'] ?>/<?= $result['total_questions'] ?></span>
        </div>
        <div class="score-item">
            <span class="label">Thời gian nộp</span>
            <span class="value" style="font-size:14px"><?= date('H:i d/m/Y', strtotime($result['submitted_at'])) ?></span>
        </div>
    </div>
</div>

<div class="container">
    <a href="danhsach_baithi.php?id_lop=<?= $id_lop ?>" class="back-link">← Quay lại danh sách bài thi</a>

    <?php if (!empty($result['nhan_xet_gv'])): ?>
    <div class="teacher-comment-banner">
        <div class="banner-header">
            <div class="banner-icon">📝</div>
            <div>
                <div class="banner-title">Nhận xét của giáo viên</div>
                <div class="banner-sub">Giáo viên đã gửi nhận xét cho bài thi này của bạn</div>
            </div>
        </div>
        <div class="banner-body"><?= nl2br(htmlspecialchars($result['nhan_xet_gv'])) ?></div>
    </div>
    <?php endif; ?>
    
    <?php $count = 1; while ($ans = $answers->fetch_assoc()): 
        $is_mc = !empty($ans['ans_a']) && !empty($ans['ans_b']);
        $is_correct = boolval($ans['is_correct']);
    ?>
        <div class="answer-card">
            <div class="q-number">Câu <?= $count++ ?></div>
            <div class="q-text"><?= htmlspecialchars($ans['question_text']) ?></div>
            
            <?php if ($is_mc): ?>
                <div class="options">
                    <?php 
                        $opts = ['A' => $ans['ans_a'], 'B' => $ans['ans_b']];
                        if (!empty($ans['ans_c'])) $opts['C'] = $ans['ans_c'];
                        if (!empty($ans['ans_d'])) $opts['D'] = $ans['ans_d'];
                        
                        foreach ($opts as $letter => $text): 
                            $is_user_choice = ($ans['student_answer'] === $letter);
                            $is_correct_choice = ($ans['correct_ans'] === $letter);
                            $class = '';
                            if ($is_user_choice) $class = 'user-ans';
                            if ($is_correct_choice) $class = ($is_user_choice ? '' : 'correct-ans');
                            if ($is_user_choice && !$is_correct_choice) $class = 'wrong-ans';
                    ?>
                        <div class="option <?= $class ?>">
                            <strong><?= $letter ?>.</strong> <?= htmlspecialchars($text) ?>
                            <?php if ($is_user_choice): ?><span> ← Câu trả lời của bạn</span><?php endif; ?>
                            <?php if ($is_correct_choice && !$is_user_choice): ?><span> ← Đáp án đúng</span><?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div style="padding: 12px; background: #f8fafc; border-radius: 6px; margin-bottom: 16px;">
                    <div style="font-size: 12px; color: #718096; margin-bottom: 4px;">Câu trả lời của bạn:</div>
                    <div style="font-size: 14px; font-weight: 600; color: #2d3748;"><?= htmlspecialchars($ans['student_answer']) ?></div>
                </div>
                <div style="padding: 12px; background: #dcfce7; border-radius: 6px;">
                    <div style="font-size: 12px; color: #166534; margin-bottom: 4px;">Đáp án đúng:</div>
                    <div style="font-size: 14px; font-weight: 600; color: #166534;"><?= htmlspecialchars($ans['correct_ans']) ?></div>
                </div>
            <?php endif; ?>
            
            <div class="result-row">
                <div class="result-item">
                    <span class="result-label">Kết quả</span>
                    <span class="result-value <?= $is_correct ? 'correct' : 'incorrect' ?>">
                        <?= $is_correct ? '✓ Đúng' : '✗ Sai' ?>
                    </span>
                </div>
            </div>
        </div>
    <?php endwhile; ?>
    
</div>

</body>
</html>