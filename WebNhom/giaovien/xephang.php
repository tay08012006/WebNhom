<?php
/*
 * xephang.php – Tab "Bảng Xếp Hạng" – include từ phonghoc.php
 * Xếp học sinh trong LỚP NÀY từ điểm cao → thấp.
 * Điểm = bài tập đã chấm (nop_bai.diem) + trắc nghiệm (quiz_results.score)
 */

if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.name', 'GV_SESSION');
    @session_start();
}

$ma_lop     = $_GET['malop'] ?? '';
$ds_xephang = [];

if (isset($conn) && !empty($ma_lop)) {
    $stmt_cid = $conn->prepare("SELECT id FROM classes WHERE ma_lop = ?");
    $stmt_cid->bind_param("s", $ma_lop);
    $stmt_cid->execute();
    $class_id = (int)($stmt_cid->get_result()->fetch_assoc()['id'] ?? 0);

    if ($class_id > 0) {
        $sql_rank = "
            SELECT
                u.id                                               AS uid,
                u.hoten,
                u.avatar,
                COALESCE(bt_agg.so_bai_bt,  0)                    AS so_bai_tap,
                COALESCE(tn_agg.so_bai_tn,  0)                    AS so_bai_tn,
                (COALESCE(bt_agg.so_bai_bt, 0) + COALESCE(tn_agg.so_bai_tn, 0)) AS tong_bai,
                CASE
                    WHEN (COALESCE(bt_agg.so_bai_bt,0)+COALESCE(tn_agg.so_bai_tn,0))=0 THEN 0
                    ELSE ROUND(
                        (COALESCE(bt_agg.tong_diem_bt,0)+COALESCE(tn_agg.tong_diem_tn,0))
                        /(COALESCE(bt_agg.so_bai_bt,0)+COALESCE(tn_agg.so_bai_tn,0))
                    ,2)
                END AS diem_tb
            FROM class_enrollments ce
            JOIN users u ON ce.user_id = u.id AND u.role = 'student'
            LEFT JOIN (
                SELECT nb.student_id,
                       SUM(nb.diem) AS tong_diem_bt,
                       COUNT(nb.id) AS so_bai_bt
                FROM nop_bai nb
                JOIN bai_tap bt2 ON bt2.id = nb.bai_tap_id AND bt2.class_id = ?
                WHERE nb.diem IS NOT NULL
                GROUP BY nb.student_id
            ) bt_agg ON bt_agg.student_id = u.id
            LEFT JOIN (
                SELECT qr.student_id,
                       SUM(qr.score) AS tong_diem_tn,
                       COUNT(qr.id)  AS so_bai_tn
                FROM quiz_results qr
                JOIN quizzes q2 ON q2.id = qr.quiz_id AND q2.class_id = ?
                GROUP BY qr.student_id
            ) tn_agg ON tn_agg.student_id = u.id
            WHERE ce.class_id = ?
            ORDER BY diem_tb DESC, u.hoten ASC
        ";
        $stmt_rank = $conn->prepare($sql_rank);
        $stmt_rank->bind_param("iii", $class_id, $class_id, $class_id);
        $stmt_rank->execute();
        $res = $stmt_rank->get_result();
        $hang = 1;
        while ($row = $res->fetch_assoc()) {
            $row['hang'] = $hang++;
            $ds_xephang[] = $row;
        }
    }
}

/* ---- helpers ---- */
if (!function_exists('xh_avatarColor')) {
    function xh_avatarColor($c) {
        $p = ['#f44336','#e91e63','#9c27b0','#673ab7','#3f51b5','#009688','#ff9800','#795548','#0288d1'];
        return $p[ord(strtoupper($c ?: 'A')) % count($p)];
    }
}
if (!function_exists('xh_lastName')) {
    function xh_lastName($n) {
        if (empty($n)) return 'A';
        $p = explode(' ', trim($n)); return end($p);
    }
}
if (!function_exists('xh_xepLoai')) {
    function xh_xepLoai($d) {
        if ($d >= 9)   return ['Xuất sắc','xh-xs'];
        if ($d >= 8)   return ['Giỏi',    'xh-g'];
        if ($d >= 6.5) return ['Khá',     'xh-k'];
        if ($d >= 5)   return ['TB',      'xh-tb'];
        if ($d > 0)    return ['Yếu',     'xh-y'];
        return ['Chưa có','xh-cc'];
    }
}

/* ---- avatar path helper ---- */
function xh_avPath($avatar, $name) {
    if (!empty($avatar)) {
        if (str_starts_with($avatar, 'http')) return $avatar;
        return '../uploads/' . $avatar;
    }
    return 'https://ui-avatars.com/api/?name=' . urlencode($name) . '&background=' . ltrim(xh_avatarColor(mb_substr(xh_lastName($name),0,1,'UTF-8')),'#') . '&color=fff&bold=true&size=128';
}
?>

<style>
/* ===== XẾP HẠNG ===== */
.xh-wrap { max-width: 860px; margin: 0 auto; padding: 10px 0 40px; font-family: 'Nunito',sans-serif; }

/* Header */
.xh-top { display:flex; align-items:center; justify-content:space-between; margin-bottom:22px; }
.xh-top-left h2 { font-size:21px; font-weight:800; color:#0f172a; margin:0; }
.xh-top-left p  { font-size:13px; color:#64748b; font-weight:600; margin:4px 0 0; }
.xh-cnt { background:#e1f5fe; color:#0277bd; font-size:12px; font-weight:800; padding:5px 14px; border-radius:20px; }

/* Podium */
.xh-podium { display:flex; gap:14px; justify-content:center; margin-bottom:28px; flex-wrap:wrap; }
.xh-pod {
    background:white; border-radius:18px; padding:22px 16px;
    text-align:center; width:190px; border:2px solid transparent;
    box-shadow:0 4px 18px rgba(0,0,0,.07); transition:transform .2s;
}
.xh-pod:hover { transform:translateY(-4px); }
.xh-pod.p1 { border-color:#f59e0b; background:linear-gradient(160deg,#fffbeb 0%,white 60%); }
.xh-pod.p2 { border-color:#94a3b8; background:linear-gradient(160deg,#f8fafc 0%,white 60%); }
.xh-pod.p3 { border-color:#cd7c3a; background:linear-gradient(160deg,#fdf6f0 0%,white 60%); }

.xh-pod-medal { font-size:34px; display:block; margin-bottom:8px; }
.xh-pod-av {
    width:68px; height:68px; border-radius:50%;
    margin:0 auto 10px; overflow:hidden;
    border:3px solid rgba(255,255,255,0.9);
    box-shadow:0 3px 10px rgba(0,0,0,.13);
}
.xh-pod-av img { width:100%; height:100%; object-fit:cover; border-radius:50%; }
.xh-pod-name { font-size:13px; font-weight:800; color:#0f172a; margin-bottom:6px;
               white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.xh-pod-score { font-size:28px; font-weight:900; color:#0277bd; line-height:1; }
.xh-pod-slabel { font-size:11px; font-weight:700; color:#94a3b8; margin-top:2px; }
.xh-pod-bai { font-size:12px; font-weight:700; color:#64748b; margin-top:8px; }

/* Table */
.xh-tbl-wrap { background:white; border-radius:18px; box-shadow:0 4px 16px rgba(0,0,0,.06); overflow:hidden; border:1px solid #e8f0f7; }
.xh-tbl { width:100%; border-collapse:collapse; }
.xh-tbl thead th {
    background:linear-gradient(135deg,#0277bd,#03a9f4);
    color:white; font-size:13px; font-weight:800;
    padding:14px 16px; text-align:left;
}
.xh-tbl thead th.tc { text-align:center; }
.xh-tbl thead th:first-child { width:64px; text-align:center; }
.xh-tbl tbody tr { border-bottom:1px solid #f1f5f9; transition:background .15s; }
.xh-tbl tbody tr:last-child { border-bottom:none; }
.xh-tbl tbody tr:hover { background:#f0f9ff; }
.xh-tbl tbody tr.top1 { background:linear-gradient(90deg,#fffbeb,#fefce8); }
.xh-tbl tbody tr.top1:hover { background:#fef9c3; }
.xh-tbl td { padding:13px 16px; font-size:14px; }
.xh-tbl td.rank-c { text-align:center; font-size:20px; font-weight:900; }
.rank-num { font-size:15px; font-weight:800; color:#475569; }

/* Student cell */
.xh-stu { display:flex; align-items:center; gap:10px; }
.xh-av-sm {
    width:38px; height:38px; border-radius:50%;
    overflow:hidden; flex-shrink:0;
    box-shadow:0 2px 6px rgba(0,0,0,.1);
}
.xh-av-sm img { width:100%; height:100%; object-fit:cover; border-radius:50%; }
.xh-stu-name { font-weight:700; color:#1e293b; }

/* Score */
.xh-score { font-size:18px; font-weight:900; color:#0277bd; display:block; text-align:center; }
.xh-score.zero { color:#94a3b8; font-size:14px; }

/* Badge */
.xh-badge { display:inline-block; font-size:11px; font-weight:800; padding:3px 10px; border-radius:20px; white-space:nowrap; }
.xh-xs  { background:#dcfce7; color:#15803d; }
.xh-g   { background:#dbeafe; color:#1d4ed8; }
.xh-k   { background:#e0f2fe; color:#0369a1; }
.xh-tb  { background:#fef9c3; color:#854d0e; }
.xh-y   { background:#fee2e2; color:#b91c1c; }
.xh-cc  { background:#f1f5f9; color:#94a3b8; }

.xh-bai-cell { font-size:13px; font-weight:700; color:#64748b; text-align:center; }

/* Empty */
.xh-empty { text-align:center; padding:60px 20px; color:#94a3b8; }
.xh-empty .ico { font-size:48px; margin-bottom:12px; }
.xh-empty p { font-size:15px; font-weight:700; }

/* Note */
.xh-note { font-size:12px; font-weight:600; color:#94a3b8; text-align:right; margin-top:10px; }

@media(max-width:700px){
    .xh-podium { flex-direction:column; align-items:center; }
    .xh-pod { width:88%; }
}
</style>

<div class="xh-wrap">

    <!-- Header -->
    <div class="xh-top">
        <div class="xh-top-left">
            <h2>Bảng Xếp Hạng Học Sinh</h2>
            <p>Chỉ tính học sinh trong lớp này · sắp xếp từ điểm cao → thấp</p>
        </div>
        <span class="xh-cnt"><?= count($ds_xephang) ?> học sinh</span>
    </div>

    <?php if (empty($ds_xephang)): ?>
    <div class="xh-tbl-wrap">
        <div class="xh-empty">
            <div class="ico">📭</div>
            <p>Chưa có học sinh nào trong lớp</p>
            <small>Mời học sinh tham gia bằng mã lớp: <b><?= htmlspecialchars($ma_lop) ?></b></small>
        </div>
    </div>

    <?php else: ?>

    <!-- PODIUM TOP 3 -->
    <?php
    $show = array_slice($ds_xephang, 0, min(3, count($ds_xephang)));
    $medals     = ['🥇','🥈','🥉'];
    $pod_cls    = ['p1','p2','p3'];
    // sắp thứ tự podium: hạng2-hạng1-hạng3
    if (count($show) === 3) {
        $display = [$show[1], $show[0], $show[2]];
        $ridx    = [1, 0, 2];
    } else {
        $display = $show;
        $ridx    = array_keys($show);
    }
    ?>
    <div class="xh-podium">
        <?php foreach ($display as $pi => $p):
            $ri   = $ridx[$pi];
            $xl   = xh_xepLoai($p['diem_tb']);
            $avsrc= xh_avPath($p['avatar'] ?? '', $p['hoten'] ?? '');
            $chu  = mb_strtoupper(mb_substr(xh_lastName($p['hoten']??''),0,1,'UTF-8'),'UTF-8');
        ?>
        <div class="xh-pod <?= $pod_cls[$ri] ?>">
            <span class="xh-pod-medal"><?= $medals[$ri] ?></span>
            <div class="xh-pod-av">
                <img src="<?= htmlspecialchars($avsrc) ?>"
                     alt="<?= htmlspecialchars($p['hoten']) ?>"
                     onerror="this.src='https://ui-avatars.com/api/?name=<?= urlencode($p['hoten']??'?') ?>&background=0288d1&color=fff&bold=true'">
            </div>
            <div class="xh-pod-name" title="<?= htmlspecialchars($p['hoten']) ?>">
                <?= htmlspecialchars($p['hoten']) ?>
            </div>
            <?php if ($p['tong_bai'] > 0): ?>
                <div class="xh-pod-score"><?= number_format($p['diem_tb'],1) ?></div>
                <div class="xh-pod-slabel">Điểm TB</div>
                <div class="xh-pod-bai"><?= $p['tong_bai'] ?> bài đã làm</div>
            <?php else: ?>
                <div class="xh-pod-score" style="font-size:14px;color:#94a3b8;">—</div>
                <div class="xh-pod-slabel">Chưa có điểm</div>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- BẢNG ĐẦY ĐỦ -->
    <div class="xh-tbl-wrap">
        <table class="xh-tbl">
            <thead>
                <tr>
                    <th>Hạng</th>
                    <th>Học sinh</th>
                    <th class="tc">Điểm TB</th>
                    <th class="tc">Xếp loại</th>
                    <th class="tc">Số bài</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($ds_xephang as $hs):
                $xl    = xh_xepLoai($hs['diem_tb']);
                $avsrc = xh_avPath($hs['avatar'] ?? '', $hs['hoten'] ?? '');
                $rc    = ($hs['hang'] === 1) ? 'top1' : '';
            ?>
            <tr class="<?= $rc ?>">
                <td class="rank-c">
                    <?php if ($hs['hang'] === 1): ?>🥇
                    <?php elseif ($hs['hang'] === 2): ?>🥈
                    <?php elseif ($hs['hang'] === 3): ?>🥉
                    <?php else: ?><span class="rank-num"><?= $hs['hang'] ?></span>
                    <?php endif; ?>
                </td>
                <td>
                    <div class="xh-stu">
                        <div class="xh-av-sm">
                            <img src="<?= htmlspecialchars($avsrc) ?>"
                                 alt="<?= htmlspecialchars($hs['hoten']) ?>"
                                 onerror="this.src='https://ui-avatars.com/api/?name=<?= urlencode($hs['hoten']??'?') ?>&background=0288d1&color=fff&bold=true'">
                        </div>
                        <span class="xh-stu-name"><?= htmlspecialchars($hs['hoten']) ?></span>
                    </div>
                </td>
                <td>
                    <?php if ($hs['tong_bai'] > 0): ?>
                        <span class="xh-score"><?= number_format($hs['diem_tb'],1) ?></span>
                    <?php else: ?>
                        <span class="xh-score zero">—</span>
                    <?php endif; ?>
                </td>
                <td style="text-align:center;">
                    <span class="xh-badge <?= $xl[1] ?>"><?= $xl[0] ?></span>
                </td>
                <td class="xh-bai-cell">
                    <?= $hs['tong_bai'] ?> bài
                    <?php if ($hs['so_bai_tap'] > 0 || $hs['so_bai_tn'] > 0): ?>
                        <br><small style="font-weight:600;color:#b0bec5;">BT:<?= $hs['so_bai_tap'] ?> · TN:<?= $hs['so_bai_tn'] ?></small>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="xh-note">* Điểm TB tính từ bài tập đã chấm + trắc nghiệm · Học sinh chưa nộp bài không được tính điểm</div>

    <?php endif; ?>
</div>