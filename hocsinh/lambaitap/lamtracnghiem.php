<?php
session_start();
include '../config.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'student') {
    header("Location: ../trangdangnhap.php");
    exit();
}

$quiz_id = isset($_GET['quiz_id']) ? intval($_GET['quiz_id']) : 0;
$id_lop  = isset($_GET['id_lop']) ? intval($_GET['id_lop']) : 0;

$stmt_quiz = $conn->prepare("SELECT * FROM quizzes WHERE id = ?");
$stmt_quiz->bind_param("i", $quiz_id);
$stmt_quiz->execute();
$quiz = $stmt_quiz->get_result()->fetch_assoc();
if (!$quiz) die("Đề thi không tồn tại.");

$stmt_questions = $conn->prepare("SELECT * FROM questions WHERE quiz_id = ? ORDER BY id ASC");
$stmt_questions->bind_param("i", $quiz_id);
$stmt_questions->execute();
$questions = $stmt_questions->get_result();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Làm bài: <?= htmlspecialchars($quiz['title']) ?></title>
    <style>
        body { font-family: sans-serif; background: #f0f4f8; padding: 20px; }
        .container { max-width: 800px; margin: auto; }
        .header-bar { background: white; padding: 20px; border-radius: 10px; display: flex; justify-content: space-between; position: sticky; top: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        .timer { background: #ef4444; color: white; font-size: 20px; font-weight: bold; padding: 10px 20px; border-radius: 8px; }
        .q-card { background: white; padding: 20px; margin-top: 20px; border-radius: 10px; }
        .q-title { font-weight: bold; margin-bottom: 15px; font-size: 16px; }
        .opt-label { display: block; padding: 10px; border: 1px solid #ddd; margin-bottom: 8px; border-radius: 6px; cursor: pointer; }
        .opt-label:hover { background: #f8fafc; }
        .btn-submit { display: block; width: 100%; background: #10b981; color: white; font-weight: bold; font-size: 18px; padding: 15px; border: none; border-radius: 10px; margin-top: 30px; cursor: pointer; }
    </style>
</head>
<body>

<div class="container">
    <div class="header-bar">
        <h2><?= htmlspecialchars($quiz['title']) ?></h2>
        <div class="timer" id="timerBox">⏱️ <span id="clock">--:--</span></div>
    </div>

    <form id="quizForm" action="xuly_lambai.php" method="POST">
        <input type="hidden" name="quiz_id" value="<?= $quiz_id ?>">
        <input type="hidden" name="id_lop" value="<?= $id_lop ?>">

        <?php $count = 1; while ($q = $questions->fetch_assoc()): 
            $isMC = !empty($q['ans_a']) && !empty($q['ans_b']);
            $isTF = !$isMC && in_array(trim($q['correct_ans']), ['A', 'B']);
        ?>
            <div class="q-card">
                <div class="q-title">Câu <?= $count++ ?>: <?= htmlspecialchars($q['question_text']) ?></div>
                
                <?php if ($isMC): ?>
                    <label class="opt-label"><input type="radio" name="answers[<?= $q['id'] ?>]" value="A"> A. <?= htmlspecialchars($q['ans_a']) ?></label>
                    <label class="opt-label"><input type="radio" name="answers[<?= $q['id'] ?>]" value="B"> B. <?= htmlspecialchars($q['ans_b']) ?></label>
                    <?php if (!empty($q['ans_c'])): ?><label class="opt-label"><input type="radio" name="answers[<?= $q['id'] ?>]" value="C"> C. <?= htmlspecialchars($q['ans_c']) ?></label><?php endif; ?>
                    <?php if (!empty($q['ans_d'])): ?><label class="opt-label"><input type="radio" name="answers[<?= $q['id'] ?>]" value="D"> D. <?= htmlspecialchars($q['ans_d']) ?></label><?php endif; ?>
                <?php elseif ($isTF): ?>
                    <label class="opt-label"><input type="radio" name="answers[<?= $q['id'] ?>]" value="A"> ĐÚNG</label>
                    <label class="opt-label"><input type="radio" name="answers[<?= $q['id'] ?>]" value="B"> SAI</label>
                <?php else: ?>
                    <input type="text" name="answers[<?= $q['id'] ?>]" style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 6px;" placeholder="Ghi câu trả lời vào đây...">
                <?php endif; ?>
            </div>
        <?php endwhile; ?>

        <button type="submit" class="btn-submit" onclick="return confirm('Nộp bài ngay bây giờ?')">Nộp Bài Chấm Điểm</button>
    </form>
</div>

<script>
    let seconds = <?= intval($quiz['duration_minutes']) ?> * 60;
    setInterval(() => {
        if (seconds <= 0) { document.getElementById('quizForm').submit(); }
        let m = Math.floor(seconds/60).toString().padStart(2, '0');
        let s = (seconds%60).toString().padStart(2, '0');
        document.getElementById('clock').innerText = m + ":" + s;
        seconds--;
    }, 1000);
</script>

</body>
</html>