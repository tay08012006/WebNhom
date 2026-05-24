<?php
ini_set('session.name', 'HS_SESSION');
session_start();
include '../../config.php';

// Chỉ học sinh mới được vào
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'student') {
    header("Location: ../trangdangnhap.php");
    exit();
}

$id_lop     = isset($_GET['id_lop']) ? intval($_GET['id_lop']) : 0;
$student_id = $_SESSION['user_id'];

if ($id_lop <= 0) die("Mã lớp không hợp lệ.");

// Kiểm tra học sinh có trong lớp không
$stmt_check = $conn->prepare(
    "SELECT id FROM class_enrollments WHERE user_id = ? AND class_id = ?"
);
$stmt_check->bind_param("ii", $student_id, $id_lop);
$stmt_check->execute();
if ($stmt_check->get_result()->num_rows === 0) {
    die("Bạn không có quyền truy cập lớp này.");
}

// Lấy thông tin lớp
$stmt_class = $conn->prepare("SELECT * FROM classes WHERE id = ?");
$stmt_class->bind_param("i", $id_lop);
$stmt_class->execute();
$lop = $stmt_class->get_result()->fetch_assoc();
if (!$lop) die("Lớp không tồn tại.");

// Lấy danh sách bài thi kèm trạng thái của học sinh
$stmt_quiz = $conn->prepare("
    SELECT q.*,
        (SELECT COUNT(*) FROM quiz_results WHERE quiz_id = q.id AND student_id = ?) AS da_lam,
        (SELECT score       FROM quiz_results WHERE quiz_id = q.id AND student_id = ?) AS diem_cua_toi,
        (SELECT correct_count FROM quiz_results WHERE quiz_id = q.id AND student_id = ?) AS so_dung,
        (SELECT total_questions FROM quiz_results WHERE quiz_id = q.id AND student_id = ?) AS tong_cau
    FROM quizzes q
    WHERE q.class_id = ?
    ORDER BY q.created_at DESC
");
$stmt_quiz->bind_param("iiiii", $student_id, $student_id, $student_id, $student_id, $id_lop);
$stmt_quiz->execute();
$quizzes = $stmt_quiz->get_result();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bài thi trắc nghiệm — <?= htmlspecialchars($lop['ten_lop']) ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { 
            font-family: 'Nunito', sans-serif; 
            background: #f0f4f8; 
            min-height: 100vh;
        }

        .header { 
            background: linear-gradient(135deg, #0277bd 0%, #01579b 100%); 
            color: white; padding: 24px 32px;
            box-shadow: 0 4px 20px rgba(1,87,155,0.3);
        }
        .header h1 { font-size: 26px; font-weight: 900; margin-bottom: 6px; }
        .header p  { font-size: 14px; opacity: 0.85; }

        .container { max-width: 900px; margin: 28px auto; padding: 0 20px 60px; }

        .back-link { 
            display: inline-flex; align-items: center; gap: 8px; 
            color: #0277bd; text-decoration: none; font-weight: 700; 
            margin-bottom: 20px; padding: 10px 18px;
            background: white; border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
            transition: all 0.2s;
        }
        .back-link:hover { background: #f1f5f9; }

        .quiz-grid { display: grid; gap: 18px; }

        .quiz-card { 
            background: white; border-radius: 16px; padding: 24px 28px; 
            box-shadow: 0 2px 10px rgba(0,0,0,0.06);
            display: flex; justify-content: space-between; align-items: center;
            gap: 20px; transition: box-shadow 0.2s;
            border-left: 5px solid #e2e8f0;
        }
        .quiz-card:hover { box-shadow: 0 6px 20px rgba(0,0,0,0.1); }
        .quiz-card.done   { border-left-color: #22c55e; }
        .quiz-card.pending { border-left-color: #f59e0b; }

        .quiz-info { flex: 1; min-width: 0; }
        .quiz-info h3 { 
            font-size: 18px; font-weight: 800; color: #1e293b; 
            margin-bottom: 8px; 
        }
        .quiz-meta { 
            display: flex; gap: 16px; flex-wrap: wrap;
            font-size: 13px; color: #64748b; font-weight: 600;
        }
        .quiz-meta span { display: flex; align-items: center; gap: 4px; }

        .quiz-right { 
            display: flex; flex-direction: column; align-items: flex-end; 
            gap: 10px; flex-shrink: 0;
        }

        .badge { 
            font-size: 12px; font-weight: 800; padding: 5px 14px; 
            border-radius: 20px; letter-spacing: 0.3px;
        }
        .badge-done    { background: #dcfce7; color: #15803d; }
        .badge-pending { background: #fef3c7; color: #92400e; }

        .score-wrap { text-align: right; }
        .score-big  { 
            font-size: 32px; font-weight: 900; line-height: 1;
        }
        .score-sub  { font-size: 12px; color: #94a3b8; margin-top: 2px; }
        .score-gioi  { color: #16a34a; }
        .score-kha   { color: #0277bd; }
        .score-tb    { color: #f59e0b; }
        .score-yeu   { color: #ef4444; }

        .btn-start { 
            background: linear-gradient(135deg, #22c55e, #16a34a);
            color: white; border: none; padding: 12px 24px;
            border-radius: 10px; font-family: 'Nunito', sans-serif;
            font-size: 15px; font-weight: 800; cursor: pointer;
            text-decoration: none; display: inline-flex; align-items: center; gap: 8px;
            box-shadow: 0 4px 12px rgba(34,197,94,0.3);
            transition: all 0.2s;
        }
        .btn-start:hover { transform: translateY(-1px); box-shadow: 0 6px 16px rgba(34,197,94,0.4); }

        .btn-review { 
            background: #f1f5f9; color: #475569; border: none;
            padding: 12px 20px; border-radius: 10px; 
            font-family: 'Nunito', sans-serif;
            font-size: 14px; font-weight: 700; cursor: pointer;
            text-decoration: none; display: inline-flex; align-items: center; gap: 6px;
            transition: all 0.2s;
        }
        .btn-review:hover { background: #e2e8f0; }

        .empty { 
            text-align: center; padding: 60px 20px; color: #94a3b8;
            background: white; border-radius: 16px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.06);
        }
        .empty-icon { font-size: 52px; margin-bottom: 16px; }
        .empty p { font-size: 16px; font-weight: 600; }

        @media (max-width: 600px) {
            .quiz-card { flex-direction: column; align-items: flex-start; }
            .quiz-right { align-items: flex-start; flex-direction: row; flex-wrap: wrap; }
            .score-wrap { text-align: left; }
        }
    </style>
</head>
<body>

<div class="header">
    <h1>📋 Bài thi trắc nghiệm trực tuyến</h1>
    <p>Lớp: <?= htmlspecialchars($lop['ten_lop']) ?></p>
</div>

<div class="container">
    <a href="../phonghoc.php?id=<?= $id_lop ?>" class="back-link">← Quay lại lớp học</a>

    <?php if ($quizzes->num_rows > 0): ?>
        <div class="quiz-grid">
        <?php while ($quiz = $quizzes->fetch_assoc()):
            $da_lam = intval($quiz['da_lam']);
            $diem   = floatval($quiz['diem_cua_toi'] ?? 0);
            $so_dung  = intval($quiz['so_dung'] ?? 0);
            $tong_cau = intval($quiz['tong_cau'] ?? 0);

            $score_class = '';
            if ($da_lam) {
                if ($diem >= 8)        $score_class = 'score-gioi';
                elseif ($diem >= 6.5)  $score_class = 'score-kha';
                elseif ($diem >= 5)    $score_class = 'score-tb';
                else                   $score_class = 'score-yeu';
            }
        ?>
            <div class="quiz-card <?= $da_lam ? 'done' : 'pending' ?>">
                <div class="quiz-info">
                    <h3><?= htmlspecialchars($quiz['title']) ?></h3>
                    <div class="quiz-meta">
                        <span>⏱️ <?= $quiz['duration_minutes'] ?> phút</span>
                        <span>📅 <?= date('d/m/Y', strtotime($quiz['created_at'])) ?></span>
                        <?php if ($da_lam && $tong_cau > 0): ?>
                            <span>✅ <?= $so_dung ?>/<?= $tong_cau ?> câu đúng</span>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="quiz-right">
                    <?php if ($da_lam): ?>
                        <span class="badge badge-done">✓ Đã nộp bài</span>
                        <div class="score-wrap">
                            <div class="score-big <?= $score_class ?>">
                                <?= number_format($diem, 1) ?>
                            </div>
                            <div class="score-sub">/ 10 điểm</div>
                        </div>
                        <!-- SỬA: link đúng đến xem_baithi.php -->
                        <a href="xem_baithi.php?quiz_id=<?= $quiz['id'] ?>&id_lop=<?= $id_lop ?>" 
                           class="btn-review">👁 Xem bài làm</a>

                    <?php else: ?>
                        <span class="badge badge-pending">⏳ Chưa làm</span>
                        <a href="lamtracnghiem.php?quiz_id=<?= $quiz['id'] ?>&id_lop=<?= $id_lop ?>" 
                           class="btn-start">▶ Bắt đầu thi</a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endwhile; ?>
        </div>

    <?php else: ?>
        <div class="empty">
            <div class="empty-icon">📭</div>
            <p>Lớp chưa có bài thi trắc nghiệm nào.</p>
        </div>
    <?php endif; ?>
</div>

</body>
</html>
