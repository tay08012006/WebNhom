<?php
ini_set('session.name', 'GV_SESSION');
session_start();
require_once '../../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    die("Bạn không có quyền truy cập.");
}

$quiz_id = isset($_GET['quiz_id']) ? intval($_GET['quiz_id']) : 0;
$ma_lop  = isset($_GET['malop'])   ? trim($_GET['malop'])     : '';

if ($quiz_id <= 0) die("Mã bài trắc nghiệm không hợp lệ.");

// Lấy thông tin bài quiz
$stmt_quiz = $conn->prepare("SELECT q.*, c.ten_lop, c.ma_lop FROM quizzes q JOIN classes c ON c.id = q.class_id WHERE q.id = ?");
$stmt_quiz->bind_param("i", $quiz_id);
$stmt_quiz->execute();
$quiz = $stmt_quiz->get_result()->fetch_assoc();
if (!$quiz) die("Bài trắc nghiệm không tồn tại.");

// Xử lý lọc
$filter_diem  = isset($_GET['filter_diem'])  ? trim($_GET['filter_diem'])  : '';
$filter_order = isset($_GET['filter_order']) ? trim($_GET['filter_order']) : 'name_asc';

// Xây dựng query kết quả
$order_sql = "u.hoten ASC"; // mặc định A-Z
if ($filter_order === 'name_desc') $order_sql = "u.hoten DESC";
elseif ($filter_order === 'score_desc') $order_sql = "r.score DESC, u.hoten ASC";
elseif ($filter_order === 'score_asc')  $order_sql = "r.score ASC, u.hoten ASC";
elseif ($filter_order === 'time_desc')  $order_sql = "r.submitted_at DESC";

$where_diem = "";
$params     = [$quiz_id];
$types      = "i";

if ($filter_diem === "pass")       { $where_diem = " AND r.score >= 5"; }
elseif ($filter_diem === "fail")   { $where_diem = " AND r.score < 5"; }
elseif ($filter_diem === "gioi")   { $where_diem = " AND r.score >= 8"; }
elseif ($filter_diem === "kha")    { $where_diem = " AND r.score >= 6.5 AND r.score < 8"; }
elseif ($filter_diem === "tb")     { $where_diem = " AND r.score >= 5 AND r.score < 6.5"; }
elseif ($filter_diem === "yeu")    { $where_diem = " AND r.score < 5"; }

$sql = "SELECT r.*, u.hoten, u.gioitinh, u.email
        FROM quiz_results r
        JOIN users u ON u.id = r.student_id
        WHERE r.quiz_id = ? $where_diem
        ORDER BY $order_sql";

$stmt_res = $conn->prepare($sql);
$stmt_res->bind_param($types, ...$params);
$stmt_res->execute();
$results = $stmt_res->get_result();

// Thống kê tổng quan
$stmt_stat = $conn->prepare("
    SELECT COUNT(*) as tong,
           AVG(score) as avg_score,
           MAX(score) as max_score,
           MIN(score) as min_score,
           SUM(CASE WHEN score >= 5 THEN 1 ELSE 0 END) as so_dat
    FROM quiz_results WHERE quiz_id = ?
");
$stmt_stat->bind_param("i", $quiz_id);
$stmt_stat->execute();
$stat = $stmt_stat->get_result()->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Danh sách thi: <?= htmlspecialchars($quiz['title']) ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Nunito', sans-serif; background: #f0f4f8; min-height: 100vh; }

        /* HEADER */
        .page-header {
            background: linear-gradient(135deg, #0277bd 0%, #01579b 100%);
            color: white; padding: 24px 32px;
            display: flex; align-items: center; gap: 20px;
            box-shadow: 0 4px 20px rgba(2,119,189,0.3);
        }
        .back-btn {
            background: rgba(255,255,255,0.15); color: white; border: none;
            padding: 10px 18px; border-radius: 10px; cursor: pointer;
            font-family: 'Nunito', sans-serif; font-weight: 700; font-size: 14px;
            text-decoration: none; display: inline-flex; align-items: center; gap: 6px;
            transition: background 0.2s;
        }
        .back-btn:hover { background: rgba(255,255,255,0.25); }
        .header-info h1 { font-size: 22px; font-weight: 900; }
        .header-info p  { font-size: 13px; opacity: 0.85; margin-top: 3px; }

        .container { max-width: 1100px; margin: 28px auto; padding: 0 20px 40px; }

        /* STAT CARDS */
        .stat-grid {
            display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
            gap: 16px; margin-bottom: 24px;
        }
        .stat-card {
            background: white; border-radius: 14px; padding: 20px;
            text-align: center; box-shadow: 0 2px 10px rgba(0,0,0,0.06);
        }
        .stat-card .val { font-size: 32px; font-weight: 900; line-height: 1; }
        .stat-card .lbl { font-size: 12px; color: #718096; margin-top: 6px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; }
        .val-blue   { color: #0277bd; }
        .val-green  { color: #16a34a; }
        .val-orange { color: #ea580c; }
        .val-purple { color: #7c3aed; }
        .val-red    { color: #dc2626; }

        /* FILTER BAR */
        .filter-bar {
            background: white; border-radius: 14px; padding: 18px 22px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.06); margin-bottom: 20px;
            display: flex; flex-wrap: wrap; gap: 14px; align-items: center;
        }
        .filter-bar label { font-size: 13px; font-weight: 700; color: #4a5568; }
        .filter-bar select {
            border: 1.5px solid #e2e8f0; border-radius: 8px; padding: 8px 14px;
            font-family: 'Nunito', sans-serif; font-size: 14px; font-weight: 600;
            color: #2d3748; background: #f8fafc; cursor: pointer; outline: none;
            transition: border-color 0.2s;
        }
        .filter-bar select:focus { border-color: #0277bd; }
        .filter-group { display: flex; align-items: center; gap: 8px; }
        .filter-bar button[type="submit"] {
            background: #0277bd; color: white; border: none; padding: 9px 20px;
            border-radius: 8px; font-family: 'Nunito', sans-serif; font-size: 14px;
            font-weight: 700; cursor: pointer; transition: background 0.2s;
        }
        .filter-bar button[type="submit"]:hover { background: #01579b; }
        
        .reset-btn {
            background: #f1f5f9; color: #64748b; border: none; padding: 9px 20px;
            border-radius: 8px; font-family: 'Nunito', sans-serif; font-size: 14px;
            font-weight: 700; cursor: pointer; text-decoration: none; 
            display: inline-flex; align-items: center; transition: background 0.2s;
        }
        .reset-btn:hover { background: #e2e8f0; }
        .result-count { margin-left: auto; font-size: 13px; color: #718096; font-weight: 700; }

        /* TABLE */
        .table-wrap { background: white; border-radius: 16px; box-shadow: 0 2px 10px rgba(0,0,0,0.06); overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; min-width: 800px; }
        thead tr { background: #f8fafc; }
        thead th {
            padding: 14px 18px; text-align: left; font-size: 12px; font-weight: 800;
            color: #718096; text-transform: uppercase; letter-spacing: 0.5px;
            border-bottom: 2px solid #e2e8f0;
        }
        tbody tr { border-bottom: 1px solid #f1f5f9; transition: background 0.15s; }
        tbody tr:last-child { border-bottom: none; }
        tbody tr:hover { background: #f8fafc; }
        td { padding: 14px 18px; font-size: 14px; color: #2d3748; vertical-align: middle; }

        /* STT */
        .stt { width: 48px; text-align: center; font-weight: 800; color: #a0aec0; }

        /* AVATAR / NAME */
        .student-cell { display: flex; align-items: center; gap: 12px; }
        .avatar-circle {
            width: 40px; height: 40px; border-radius: 50%; display: flex;
            align-items: center; justify-content: center; font-size: 16px;
            font-weight: 800; flex-shrink: 0;
        }
        .avatar-nam  { background: #dbeafe; color: #1d4ed8; }
        .avatar-nu   { background: #fce7f3; color: #be185d; }
        .avatar-other{ background: #e0e7ff; color: #4338ca; }
        .student-name { font-weight: 700; font-size: 15px; }
        .student-email{ font-size: 12px; color: #a0aec0; margin-top: 2px; }

        /* GENDER BADGE */
        .badge-gender {
            display: inline-block; padding: 3px 10px; border-radius: 20px;
            font-size: 12px; font-weight: 700;
        }
        .badge-nam  { background: #dbeafe; color: #1d4ed8; }
        .badge-nu   { background: #fce7f3; color: #be185d; }
        .badge-other{ background: #f3f4f6; color: #6b7280; }

        /* SCORE */
        .score-cell { text-align: center; }
        .score-big { font-size: 22px; font-weight: 900; line-height: 1; }
        .score-sub { font-size: 12px; color: #a0aec0; margin-top: 3px; }
        .score-gioi  { color: #16a34a; }
        .score-kha   { color: #0277bd; }
        .score-tb    { color: #ea580c; }
        .score-yeu   { color: #dc2626; }

        /* CORRECT COUNT */
        .correct-cell { text-align: center; font-weight: 700; }
        .correct-frac { font-size: 16px; }
        .correct-pct  { font-size: 11px; color: #a0aec0; margin-top: 2px; }

        /* COMMENT */
        .comment-cell { min-width: 260px; }
        .comment-form { display: flex; gap: 8px; align-items: flex-start; }
        .comment-input {
            flex: 1; border: 1.5px solid #e2e8f0; border-radius: 8px;
            padding: 8px 12px; font-family: 'Nunito', sans-serif; font-size: 13px;
            resize: vertical; min-height: 38px; outline: none; transition: border-color 0.2s;
            color: #2d3748;
        }
        .comment-input:focus { border-color: #0277bd; }
        .comment-save-btn {
            background: #16a34a; color: white; border: none; padding: 8px 14px;
            border-radius: 8px; cursor: pointer; font-size: 13px; font-weight: 700;
            font-family: 'Nunito', sans-serif; white-space: nowrap; transition: background 0.2s, opacity 0.2s;
            flex-shrink: 0;
        }
        .comment-save-btn:hover { background: #15803d; }
        .comment-save-btn:disabled { opacity: 0.6; cursor: not-allowed; }
        .comment-saved { 
            font-size: 12px; color: #16a34a; font-weight: 700;
            display: none; margin-top: 4px;
        }

        /* TIME */
        .time-cell { font-size: 13px; color: #718096; white-space: nowrap; }

        /* EMPTY STATE */
        .empty-state {
            text-align: center; padding: 60px 20px; color: #a0aec0;
        }
        .empty-state .icon { font-size: 56px; margin-bottom: 16px; }
        .empty-state p { font-size: 16px; font-weight: 600; }

        /* RESPONSIVE */
        @media (max-width: 768px) {
            .page-header { padding: 16px 18px; }
            .container { padding: 0 12px 30px; }
            thead th:nth-child(3),
            td:nth-child(3) { display: none; }
        }
    </style>
</head>
<body>

<div class="page-header">
    <a href="../phonghoc.php?malop=<?= urlencode($quiz['ma_lop']) ?>&tab=bai-tap" class="back-btn">← Quay lại</a>
    <div class="header-info">
        <h1>📋 Danh sách thi — <?= htmlspecialchars($quiz['title']) ?></h1>
        <p>Lớp: <?= htmlspecialchars($quiz['ten_lop']) ?> (<?= htmlspecialchars($quiz['ma_lop']) ?>)
           &nbsp;•&nbsp; Thời gian: <?= $quiz['duration_minutes'] ?> phút
           &nbsp;•&nbsp; Tạo: <?= date('d/m/Y', strtotime($quiz['created_at'])) ?>
        </p>
    </div>
</div>

<div class="container">

    <?php if ($stat['tong'] > 0): ?>
    <div class="stat-grid">
        <div class="stat-card">
            <div class="val val-blue"><?= $stat['tong'] ?></div>
            <div class="lbl">Đã thi</div>
        </div>
        <div class="stat-card">
            <div class="val val-green"><?= $stat['so_dat'] ?></div>
            <div class="lbl">Đạt (≥5)</div>
        </div>
        <div class="stat-card">
            <div class="val val-red"><?= $stat['tong'] - $stat['so_dat'] ?></div>
            <div class="lbl">Không đạt</div>
        </div>
        <div class="stat-card">
            <div class="val val-purple"><?= number_format($stat['avg_score'], 1) ?></div>
            <div class="lbl">Điểm TB</div>
        </div>
        <div class="stat-card">
            <div class="val val-green"><?= number_format($stat['max_score'], 1) ?></div>
            <div class="lbl">Điểm cao nhất</div>
        </div>
        <div class="stat-card">
            <div class="val val-orange"><?= number_format($stat['min_score'], 1) ?></div>
            <div class="lbl">Điểm thấp nhất</div>
        </div>
    </div>
    <?php endif; ?>

    <form method="GET" action="" class="filter-bar">
        <input type="hidden" name="quiz_id" value="<?= $quiz_id ?>">
        <input type="hidden" name="malop"   value="<?= htmlspecialchars($ma_lop) ?>">

        <div class="filter-group">
            <label>🔽 Lọc điểm:</label>
            <select name="filter_diem">
                <option value=""      <?= $filter_diem===''      ?'selected':'' ?>>Tất cả</option>
                <option value="gioi"  <?= $filter_diem==='gioi'  ?'selected':'' ?>>Giỏi (≥8)</option>
                <option value="kha"   <?= $filter_diem==='kha'   ?'selected':'' ?>>Khá (6.5–7.9)</option>
                <option value="tb"    <?= $filter_diem==='tb'    ?'selected':'' ?>>Trung bình (5–6.4)</option>
                <option value="yeu"   <?= $filter_diem==='yeu'   ?'selected':'' ?>>Yếu (&lt;5)</option>
                <option value="pass"  <?= $filter_diem==='pass'  ?'selected':'' ?>>Đạt (≥5)</option>
                <option value="fail"  <?= $filter_diem==='fail'  ?'selected':'' ?>>Không đạt (&lt;5)</option>
            </select>
        </div>

        <div class="filter-group">
            <label>↕ Sắp xếp:</label>
            <select name="filter_order">
                <option value="name_asc"   <?= $filter_order==='name_asc'  ?'selected':'' ?>>Tên A → Z</option>
                <option value="name_desc"  <?= $filter_order==='name_desc' ?'selected':'' ?>>Tên Z → A</option>
                <option value="score_desc" <?= $filter_order==='score_desc'?'selected':'' ?>>Điểm cao → thấp</option>
                <option value="score_asc"  <?= $filter_order==='score_asc' ?'selected':'' ?>>Điểm thấp → cao</option>
                <option value="time_desc"  <?= $filter_order==='time_desc' ?'selected':'' ?>>Mới nộp nhất</option>
            </select>
        </div>

        <button type="submit">Áp dụng</button>
        <a href="?quiz_id=<?= $quiz_id ?>&malop=<?= urlencode($ma_lop) ?>" class="reset-btn">
            ↺ Đặt lại
        </a>

        <span class="result-count">
            <?= $results->num_rows ?> học sinh
        </span>
    </form>

    <div class="table-wrap">
        <?php if ($results->num_rows > 0): ?>
        <table>
            <thead>
                <tr>
                    <th class="stt">#</th>
                    <th>Học sinh</th>
                    <th>Giới tính</th>
                    <th style="text-align:center">Câu đúng</th>
                    <th style="text-align:center">Điểm</th>
                    <th>Thời gian nộp</th>
                    <th>Nhận xét của GV</th>
                </tr>
            </thead>
            <tbody>
            <?php $stt = 1; while ($row = $results->fetch_assoc()):
                $gender  = strtolower(trim($row['gioitinh'] ?? ''));
                $is_nam  = ($gender === 'nam');
                $is_nu   = ($gender === 'nữ' || $gender === 'nu');
                $initial = mb_strtoupper(mb_substr($row['hoten'], 0, 1, 'UTF-8'), 'UTF-8');

                // Điểm màu
                $score = floatval($row['score']);
                $score_class = $score >= 8 ? 'score-gioi' : ($score >= 6.5 ? 'score-kha' : ($score >= 5 ? 'score-tb' : 'score-yeu'));

                // Phần trăm đúng
                $pct = $row['total_questions'] > 0
                     ? round($row['correct_count'] / $row['total_questions'] * 100)
                     : 0;
            ?>
                <tr>
                    <td class="stt"><?= $stt++ ?></td>

                    <td>
                        <div class="student-cell">
                            <div class="avatar-circle <?= $is_nam ? 'avatar-nam' : ($is_nu ? 'avatar-nu' : 'avatar-other') ?>">
                                <?= $initial ?>
                            </div>
                            <div>
                                <div class="student-name"><?= htmlspecialchars($row['hoten']) ?></div>
                                <div class="student-email"><?= htmlspecialchars($row['email']) ?></div>
                            </div>
                        </div>
                    </td>

                    <td>
                        <span class="badge-gender <?= $is_nam ? 'badge-nam' : ($is_nu ? 'badge-nu' : 'badge-other') ?>">
                            <?= $is_nam ? '♂ Nam' : ($is_nu ? '♀ Nữ' : '— Khác') ?>
                        </span>
                    </td>

                    <td class="correct-cell">
                        <div class="correct-frac">
                            <?= $row['correct_count'] ?> / <?= $row['total_questions'] ?>
                        </div>
                        <div class="correct-pct"><?= $pct ?>%</div>
                    </td>

                    <td class="score-cell">
                        <div class="score-big <?= $score_class ?>">
                            <?= number_format($score, 1) ?>
                        </div>
                        <div class="score-sub">/ 10</div>
                    </td>

                    <td class="time-cell">
                        <?= date('H:i', strtotime($row['submitted_at'])) ?><br>
                        <span style="font-size:11px"><?= date('d/m/Y', strtotime($row['submitted_at'])) ?></span>
                    </td>

                    <td class="comment-cell">
                        <div class="comment-form">
                            <textarea class="comment-input"
                                      id="cmt_<?= $row['id'] ?>"
                                      rows="2"
                                      placeholder="Nhập nhận xét..."><?= htmlspecialchars($row['nhan_xet_gv'] ?? '') ?></textarea>
                            <button class="comment-save-btn" onclick="saveComment(this, <?= $row['id'] ?>)">
                                💾 Lưu
                            </button>
                        </div>
                        <div class="comment-saved" id="saved_<?= $row['id'] ?>">✅ Đã lưu nhận xét!</div>
                    </td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>

        <?php else: ?>
        <div class="empty-state">
            <div class="icon">📭</div>
            <p>Chưa có học sinh nào nộp bài cho đề thi này.</p>
        </div>
        <?php endif; ?>
    </div>

</div>

<script>
async function saveComment(btn, resultId) {
    const textarea = document.getElementById('cmt_' + resultId);
    const savedMsg = document.getElementById('saved_' + resultId);
    if (!textarea || !savedMsg) return;

    const comment = textarea.value;

    // Vô hiệu hóa nút để tránh double click gửi yêu cầu liên tục
    btn.disabled = true;
    const oldText = btn.innerHTML;
    btn.innerHTML = '⏳ Lưu...';

    try {
        const resp = await fetch('luu_nhanxet.php', {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `result_id=${resultId}&nhan_xet=${encodeURIComponent(comment)}`
        });
        const data = await resp.json();
        if (data.success) {
            savedMsg.style.display = 'block';
            setTimeout(() => { savedMsg.style.display = 'none'; }, 2500);
            textarea.style.borderColor = '#16a34a';
            setTimeout(() => { textarea.style.borderColor = '#e2e8f0'; }, 2000);
        } else {
            alert('Lỗi: ' + (data.message || 'Không lưu được nhận xét.'));
        }
    } catch (e) {
        alert('Lỗi kết nối máy chủ.');
    } finally {
        // Mở lại trạng thái nút bấm bình thường
        btn.disabled = false;
        btn.innerHTML = oldText;
    }
}
</script>

</body>
</html>