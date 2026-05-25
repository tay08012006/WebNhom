<?php
ini_set('session.name', 'HS_SESSION');
session_start();
include '../../config.php';

// [KIỂM TRA 1] Chỉ học sinh mới được vào
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'student') {
    header("Location: ../trangdangnhap.php");
    exit();
}

$quiz_id    = isset($_GET['quiz_id']) ? intval($_GET['quiz_id']) : 0;
$id_lop     = isset($_GET['id_lop'])  ? intval($_GET['id_lop'])  : 0;
$student_id = $_SESSION['user_id'];

if ($quiz_id <= 0 || $id_lop <= 0) die("Tham số không hợp lệ.");

// [KIỂM TRA 2] Học sinh phải thuộc lớp này
$stmt_enroll = $conn->prepare(
    "SELECT id FROM class_enrollments WHERE user_id = ? AND class_id = ?"
);
$stmt_enroll->bind_param("ii", $student_id, $id_lop);
$stmt_enroll->execute();
if ($stmt_enroll->get_result()->num_rows === 0) {
    die("Bạn không thuộc lớp học này.");
}

// [KIỂM TRA 3] Bài thi phải thuộc đúng lớp đó
$stmt_quiz = $conn->prepare(
    "SELECT q.* FROM quizzes q WHERE q.id = ? AND q.class_id = ?"
);
$stmt_quiz->bind_param("ii", $quiz_id, $id_lop);
$stmt_quiz->execute();
$quiz = $stmt_quiz->get_result()->fetch_assoc();
if (!$quiz) die("Đề thi không tồn tại hoặc không thuộc lớp của bạn.");

// [KIỂM TRA 4] Chưa làm bài thì mới được vào — nếu đã làm thì chuyển về xem kết quả
$stmt_done = $conn->prepare(
    "SELECT id FROM quiz_results WHERE quiz_id = ? AND student_id = ?"
);
$stmt_done->bind_param("ii", $quiz_id, $student_id);
$stmt_done->execute();
if ($stmt_done->get_result()->num_rows > 0) {
    // Đã làm rồi — chuyển sang trang xem bài làm
    header("Location: xem_baithi.php?quiz_id=$quiz_id&id_lop=$id_lop");
    exit();
}

// Lấy danh sách câu hỏi
$stmt_questions = $conn->prepare(
    "SELECT * FROM questions WHERE quiz_id = ? ORDER BY id ASC"
);
$stmt_questions->bind_param("i", $quiz_id);
$stmt_questions->execute();
$questions = $stmt_questions->get_result();
$total_q   = $questions->num_rows;

// Key localStorage duy nhất cho học sinh + bài thi này
$timer_key = "quiz_timer_{$quiz_id}_{$student_id}";
$duration_sec = intval($quiz['duration_minutes']) * 60;
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Làm bài: <?= htmlspecialchars($quiz['title']) ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: 'Nunito', sans-serif; 
            background: #f0f4f8; 
            min-height: 100vh;
        }

        /* ===== STICKY HEADER ===== */
        .sticky-header {
            position: sticky; top: 0; z-index: 200;
            background: white;
            box-shadow: 0 2px 12px rgba(0,0,0,0.08);
            padding: 14px 24px;
            display: flex; justify-content: space-between; align-items: center;
            gap: 16px;
        }
        .header-left { display: flex; align-items: center; gap: 14px; flex: 1; min-width: 0; }
        .header-left h2 { 
            font-size: 18px; font-weight: 800; color: #1e293b; 
            white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
        }
        .back-link { 
            color: #64748b; text-decoration: none; font-weight: 700; font-size: 13px; 
            padding: 8px 14px; background: #f1f5f9; border-radius: 8px; 
            transition: all 0.2s; white-space: nowrap; flex-shrink: 0;
        }
        .back-link:hover { background: #e2e8f0; }

        /* ===== TIMER ===== */
        .timer-box { 
            display: flex; align-items: center; gap: 10px;
            background: #1e293b; color: white;
            padding: 10px 20px; border-radius: 10px;
            font-size: 22px; font-weight: 900;
            letter-spacing: 2px;
            flex-shrink: 0;
            transition: background 0.5s;
        }
        .timer-box.warning  { background: #f59e0b; }
        .timer-box.danger   { background: #ef4444; animation: pulse 1s infinite; }
        @keyframes pulse { 0%,100%{opacity:1} 50%{opacity:.8} }

        /* Progress bar */
        .progress-wrap { 
            background: #f1f5f9; height: 6px; 
            border-radius: 0; 
        }
        .progress-bar { 
            height: 6px; background: linear-gradient(90deg, #3b82f6, #6366f1);
            transition: width 0.5s ease; border-radius: 0 3px 3px 0;
        }

        /* ===== MAIN LAYOUT ===== */
        .main-wrap { max-width: 860px; margin: 28px auto; padding: 0 20px 80px; }

        /* ===== QUESTION NAVIGATOR ===== */
        .q-navigator {
            background: white; border-radius: 14px; padding: 18px 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06); margin-bottom: 24px;
        }
        .q-navigator h4 { 
            font-size: 13px; font-weight: 800; color: #64748b; 
            margin-bottom: 12px; text-transform: uppercase; letter-spacing: 0.5px;
        }
        .q-nav-grid { display: flex; flex-wrap: wrap; gap: 8px; }
        .q-nav-btn {
            width: 36px; height: 36px; border-radius: 8px; border: 2px solid #e2e8f0;
            background: white; font-size: 13px; font-weight: 700; color: #64748b;
            cursor: pointer; transition: all 0.2s; display: flex; align-items: center; justify-content: center;
        }
        .q-nav-btn:hover  { border-color: #3b82f6; color: #3b82f6; }
        .q-nav-btn.answered { background: #dcfce7; border-color: #22c55e; color: #15803d; }
        .q-nav-btn.current  { border-color: #3b82f6; background: #eff6ff; color: #3b82f6; }

        /* ===== QUESTION CARD ===== */
        .q-card { 
            background: white; padding: 28px; margin-bottom: 20px; 
            border-radius: 16px; box-shadow: 0 2px 8px rgba(0,0,0,0.06);
            border-left: 4px solid #e2e8f0;
            transition: border-color 0.3s;
            scroll-margin-top: 90px;
        }
        .q-card.answered { border-left-color: #22c55e; }

        .q-header { display: flex; align-items: flex-start; gap: 14px; margin-bottom: 20px; }
        .q-num-badge {
            min-width: 36px; height: 36px; border-radius: 10px; 
            background: #f1f5f9; display: flex; align-items: center; justify-content: center;
            font-size: 14px; font-weight: 800; color: #1e293b; flex-shrink: 0;
        }
        .q-text { font-size: 16px; font-weight: 700; color: #1e293b; line-height: 1.6; }

        /* ===== OPTIONS ===== */
        .options-list { display: flex; flex-direction: column; gap: 10px; }
        
        .opt-label {
            display: flex; align-items: flex-start; gap: 14px;
            padding: 14px 18px; 
            border: 2px solid #e2e8f0; border-radius: 12px;
            cursor: pointer; transition: all 0.2s;
            position: relative;
        }
        .opt-label:hover { border-color: #3b82f6; background: #eff6ff; }
        .opt-label input[type="radio"] { display: none; }
        .opt-label.selected { 
            border-color: #3b82f6; background: #eff6ff; 
        }
        .opt-letter {
            min-width: 32px; height: 32px; border-radius: 8px;
            background: #f1f5f9; display: flex; align-items: center; justify-content: center;
            font-size: 14px; font-weight: 800; color: #64748b; flex-shrink: 0;
            transition: all 0.2s;
        }
        .opt-label.selected .opt-letter { background: #3b82f6; color: white; }
        .opt-text { font-size: 15px; font-weight: 600; color: #374151; padding-top: 5px; line-height: 1.5; }

        /* True / False style */
        .tf-wrap { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
        .tf-label {
            display: flex; align-items: center; justify-content: center; gap: 10px;
            padding: 16px; border: 2px solid #e2e8f0; border-radius: 12px;
            cursor: pointer; transition: all 0.2s; font-size: 16px; font-weight: 800;
        }
        .tf-label input { display: none; }
        .tf-label:hover { border-color: #3b82f6; background: #eff6ff; }
        .tf-label.selected-true { border-color: #22c55e; background: #dcfce7; color: #15803d; }
        .tf-label.selected-false { border-color: #ef4444; background: #fee2e2; color: #dc2626; }

        /* Fill input */
        .fill-input {
            width: 100%; padding: 14px 18px;
            border: 2px solid #e2e8f0; border-radius: 12px;
            font-family: 'Nunito', sans-serif; font-size: 15px; font-weight: 600;
            color: #1e293b; outline: none; transition: border-color 0.2s;
        }
        .fill-input:focus { border-color: #3b82f6; }
        .fill-input.has-value { border-color: #22c55e; background: #f0fdf4; }

        /* ===== SUBMIT ===== */
        .submit-bar {
            position: fixed; bottom: 0; left: 0; right: 0;
            background: white; border-top: 1px solid #e2e8f0;
            padding: 16px 24px;
            display: flex; justify-content: space-between; align-items: center;
            z-index: 100;
            box-shadow: 0 -4px 16px rgba(0,0,0,0.08);
        }
        .answered-count { font-size: 14px; font-weight: 700; color: #64748b; }
        .answered-count strong { color: #22c55e; font-size: 18px; }
        .btn-submit {
            background: linear-gradient(135deg, #3b82f6, #6366f1);
            color: white; border: none; padding: 14px 36px;
            border-radius: 12px; font-family: 'Nunito', sans-serif;
            font-size: 17px; font-weight: 800; cursor: pointer;
            transition: all 0.2s; box-shadow: 0 4px 14px rgba(59,130,246,0.4);
        }
        .btn-submit:hover { transform: translateY(-1px); box-shadow: 0 6px 20px rgba(59,130,246,0.5); }
        .btn-submit:active { transform: translateY(0); }
        .btn-submit:disabled { opacity: 0.7; cursor: not-allowed; transform: none; }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 600px) {
            .header-left h2 { font-size: 15px; }
            .timer-box { font-size: 18px; padding: 8px 14px; }
            .tf-wrap { grid-template-columns: 1fr; }
            .q-card { padding: 20px 16px; }
        }
    </style>
</head>
<body>

<!-- ===== STICKY HEADER ===== -->
<div class="sticky-header">
    <div class="header-left">
        <a href="danhsach_baithi.php?id_lop=<?= $id_lop ?>" class="back-link">← Quay lại</a>
        <h2>📝 <?= htmlspecialchars($quiz['title']) ?></h2>
    </div>
    <div class="timer-box" id="timerBox">
        ⏱ <span id="clock">--:--</span>
    </div>
</div>

<!-- PROGRESS BAR -->
<div class="progress-wrap">
    <div class="progress-bar" id="progressBar" style="width: 100%"></div>
</div>

<!-- ===== MAIN ===== -->
<div class="main-wrap">

    <!-- Navigator -->
    <div class="q-navigator">
        <h4>📌 Điều hướng câu hỏi — <span id="navDoneCount">0</span>/<?= $total_q ?> đã trả lời</h4>
        <div class="q-nav-grid" id="navGrid">
            <?php for ($n = 1; $n <= $total_q; $n++): ?>
                <button type="button" class="q-nav-btn" id="nav_<?= $n ?>" onclick="scrollToQ(<?= $n ?>)"><?= $n ?></button>
            <?php endfor; ?>
        </div>
    </div>

    <!-- FORM -->
    <form id="quizForm" action="xuly_lambai.php" method="POST">
        <input type="hidden" name="quiz_id" value="<?= $quiz_id ?>">
        <input type="hidden" name="id_lop"  value="<?= $id_lop ?>">

        <?php 
        $count = 1;
        while ($q = $questions->fetch_assoc()):
            $isMC = !empty($q['ans_a']) && !empty($q['ans_b']);
            $isTF = !$isMC && in_array(trim($q['correct_ans']), ['A', 'B']);
            $isFill = !$isMC && !$isTF;
        ?>
        <div class="q-card" id="qcard_<?= $count ?>" data-qnum="<?= $count ?>">
            <div class="q-header">
                <div class="q-num-badge"><?= $count ?></div>
                <div class="q-text"><?= nl2br(htmlspecialchars($q['question_text'])) ?></div>
            </div>

            <?php if ($isMC): ?>
                <div class="options-list">
                    <?php 
                    $opts = ['A' => $q['ans_a'], 'B' => $q['ans_b']];
                    if (!empty($q['ans_c'])) $opts['C'] = $q['ans_c'];
                    if (!empty($q['ans_d'])) $opts['D'] = $q['ans_d'];
                    foreach ($opts as $letter => $text):
                    ?>
                    <label class="opt-label" id="opt_<?= $q['id'] ?>_<?= $letter ?>">
                        <input type="radio" 
                               name="answers[<?= $q['id'] ?>]" 
                               value="<?= $letter ?>"
                               onchange="onMCChange(this, <?= $count ?>, '<?= $q['id'] ?>')">
                        <div class="opt-letter"><?= $letter ?></div>
                        <div class="opt-text"><?= htmlspecialchars($text) ?></div>
                    </label>
                    <?php endforeach; ?>
                </div>

            <?php elseif ($isTF): ?>
                <div class="tf-wrap">
                    <label class="tf-label" id="tf_<?= $q['id'] ?>_A">
                        <input type="radio" 
                               name="answers[<?= $q['id'] ?>]" 
                               value="A"
                               onchange="onTFChange(this, <?= $count ?>, '<?= $q['id'] ?>', 'A')">
                        ✅ ĐÚNG
                    </label>
                    <label class="tf-label" id="tf_<?= $q['id'] ?>_B">
                        <input type="radio" 
                               name="answers[<?= $q['id'] ?>]" 
                               value="B"
                               onchange="onTFChange(this, <?= $count ?>, '<?= $q['id'] ?>', 'B')">
                        ❌ SAI
                    </label>
                </div>

            <?php else: // FILL ?>
                <input type="text" 
                       class="fill-input" 
                       name="answers[<?= $q['id'] ?>]" 
                       id="fill_<?= $q['id'] ?>"
                       placeholder="✏️ Ghi câu trả lời vào đây..."
                       oninput="onFillChange(this, <?= $count ?>)">
            <?php endif; ?>
        </div>
        <?php $count++; endwhile; ?>

        <!-- Nút nộp bài (ẩn — trigger từ submit bar) -->
        <button type="submit" id="realSubmitBtn" style="display:none;"></button>
    </form>
</div>

<!-- ===== SUBMIT BAR (fixed bottom) ===== -->
<div class="submit-bar">
    <div class="answered-count">
        Đã trả lời: <strong id="answeredCount">0</strong> / <?= $total_q ?> câu
    </div>
    <button class="btn-submit" id="submitBtn" onclick="confirmSubmit()">
        📤 Nộp Bài Chấm Điểm
    </button>
</div>


<script>
// ============================================================
// CẤU HÌNH
// ============================================================
const TIMER_KEY      = <?= json_encode($timer_key) ?>;
const DURATION_SEC   = <?= $duration_sec ?>;
const TOTAL_Q        = <?= $total_q ?>;
const quizForm       = document.getElementById('quizForm');

let answeredSet = new Set();   // Tập câu đã trả lời (số thứ tự 1-based)
let timeLeft;

// ============================================================
// TIMER — lưu thời điểm bắt đầu vào localStorage để tránh reload reset
// ============================================================
function initTimer() {
    const stored = localStorage.getItem(TIMER_KEY);
    let startTimestamp;

    if (stored) {
        startTimestamp = parseInt(stored, 10);
    } else {
        startTimestamp = Date.now();
        localStorage.setItem(TIMER_KEY, startTimestamp);
    }

    function tick() {
        const elapsed = Math.floor((Date.now() - startTimestamp) / 1000);
        timeLeft = DURATION_SEC - elapsed;

        if (timeLeft <= 0) {
            // Hết giờ — tự nộp
            localStorage.removeItem(TIMER_KEY);
            document.getElementById('clock').innerText = '00:00';
            document.getElementById('quizForm').submit();
            return;
        }

        const m = Math.floor(timeLeft / 60).toString().padStart(2, '0');
        const s = (timeLeft % 60).toString().padStart(2, '0');
        document.getElementById('clock').innerText = m + ':' + s;

        // Màu cảnh báo
        const box = document.getElementById('timerBox');
        box.className = 'timer-box';
        if (timeLeft <= 60)  box.classList.add('danger');
        else if (timeLeft <= 300) box.classList.add('warning');

        // Progress bar theo thời gian còn lại
        const pct = (timeLeft / DURATION_SEC) * 100;
        document.getElementById('progressBar').style.width = pct + '%';
    }

    tick();
    setInterval(tick, 500);
}

// ============================================================
// THEO DÕI CÂU ĐÃ TRẢ LỜI
// ============================================================
function markAnswered(qnum) {
    answeredSet.add(qnum);
    updateAnswerUI(qnum);
    updateCounters();
}

function updateAnswerUI(qnum) {
    const card = document.getElementById('qcard_' + qnum);
    const nav  = document.getElementById('nav_' + qnum);
    if (card) card.classList.add('answered');
    if (nav)  nav.classList.add('answered');
}

function updateCounters() {
    const n = answeredSet.size;
    document.getElementById('answeredCount').textContent = n;
    document.getElementById('navDoneCount').textContent  = n;
}

// ============================================================
// EVENT HANDLERS CHO TỪNG LOẠI CÂU HỎI
// ============================================================
function onMCChange(input, qnum, qid) {
    // Bỏ selected cho tất cả option của câu này
    document.querySelectorAll(`[id^="opt_${qid}_"]`).forEach(el => el.classList.remove('selected'));
    // Đánh dấu selected cho label được chọn
    const letter = input.value;
    const lbl = document.getElementById(`opt_${qid}_${letter}`);
    if (lbl) lbl.classList.add('selected');
    markAnswered(qnum);
}

function onTFChange(input, qnum, qid, val) {
    const lblA = document.getElementById(`tf_${qid}_A`);
    const lblB = document.getElementById(`tf_${qid}_B`);
    if (lblA) { lblA.classList.remove('selected-true', 'selected-false'); }
    if (lblB) { lblB.classList.remove('selected-true', 'selected-false'); }

    const chosen = document.getElementById(`tf_${qid}_${val}`);
    if (chosen) chosen.classList.add(val === 'A' ? 'selected-true' : 'selected-false');
    markAnswered(qnum);
}

function onFillChange(input, qnum) {
    if (input.value.trim() !== '') {
        input.classList.add('has-value');
        markAnswered(qnum);
    } else {
        input.classList.remove('has-value');
        answeredSet.delete(qnum);
        document.getElementById('qcard_' + qnum)?.classList.remove('answered');
        document.getElementById('nav_' + qnum)?.classList.remove('answered');
        updateCounters();
    }
}

// ============================================================
// ĐIỀU HƯỚNG
// ============================================================
function scrollToQ(qnum) {
    const card = document.getElementById('qcard_' + qnum);
    if (card) card.scrollIntoView({ behavior: 'smooth', block: 'start' });

    // Highlight current trong nav
    document.querySelectorAll('.q-nav-btn').forEach(b => b.classList.remove('current'));
    const nb = document.getElementById('nav_' + qnum);
    if (nb) nb.classList.add('current');
}

// Highlight khi scroll qua câu hỏi
const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            const qnum = entry.target.dataset.qnum;
            document.querySelectorAll('.q-nav-btn').forEach(b => b.classList.remove('current'));
            const nb = document.getElementById('nav_' + qnum);
            if (nb) nb.classList.add('current');
        }
    });
}, { threshold: 0.5, rootMargin: '-80px 0px -40% 0px' });

document.querySelectorAll('.q-card').forEach(card => observer.observe(card));

// ============================================================
// NỘP BÀI
// ============================================================
function confirmSubmit() {
    const unanswered = TOTAL_Q - answeredSet.size;
    let msg = `Bạn đã trả lời ${answeredSet.size}/${TOTAL_Q} câu.`;
    if (unanswered > 0) {
        msg += `\n⚠️ Còn ${unanswered} câu chưa trả lời.`;
    }
    msg += '\n\nXác nhận nộp bài?';

    if (confirm(msg)) {
        localStorage.removeItem(TIMER_KEY);
        const btn = document.getElementById('submitBtn');
        btn.disabled = true;
        btn.textContent = '⏳ Đang nộp bài...';
        quizForm.submit();
    }
}

// ============================================================
// KHỞI CHẠY
// ============================================================
initTimer();
</script>

</body>
</html>
