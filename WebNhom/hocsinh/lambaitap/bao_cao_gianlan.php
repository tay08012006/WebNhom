<?php
/**
 * bao_cao_gianlam.php
 * AJAX endpoint — ghi nhận vi phạm thoát tab khi làm trắc nghiệm.
 * Khi số lần vi phạm >= 3 → khóa bài + gửi thông báo cho giáo viên.
 */
ini_set('session.name', 'HS_SESSION');
session_start();
include '../../config.php';

header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'student') {
    echo json_encode(['status' => 'error', 'message' => 'Không có quyền.']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Phương thức không hợp lệ.']);
    exit();
}

$quiz_id    = intval($_POST['quiz_id']        ?? 0);
$vi_pham    = intval($_POST['so_lan_vi_pham'] ?? 0);
$student_id = $_SESSION['user_id'];

if ($quiz_id <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Tham số không hợp lệ.']);
    exit();
}

// ── 1. Tạo bảng nếu chưa có ──────────────────────────────────────────────
$conn->query(
    "CREATE TABLE IF NOT EXISTS `quiz_cheating_log` (
        `id`             INT(11)    NOT NULL AUTO_INCREMENT,
        `quiz_id`        INT(11)    NOT NULL,
        `student_id`     INT(11)    NOT NULL,
        `so_lan_vi_pham` INT(11)    NOT NULL DEFAULT 1,
        `is_locked`      TINYINT(1) NOT NULL DEFAULT 0,
        `ghi_chu`        TEXT               DEFAULT NULL,
        `cap_nhat_luc`   TIMESTAMP  NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        UNIQUE KEY `uq_quiz_student` (`quiz_id`, `student_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
);

// ── 2. Lấy thông tin bài thi + giáo viên ────────────────────────────────
$stmt = $conn->prepare(
    "SELECT q.title, q.class_id, c.giaovien_id, c.ten_lop, u.hoten AS ten_hs
     FROM quizzes q
     JOIN classes c ON c.id = q.class_id
     JOIN users u   ON u.id = ?
     WHERE q.id = ?"
);
$stmt->bind_param("ii", $student_id, $quiz_id);
$stmt->execute();
$info = $stmt->get_result()->fetch_assoc();

if (!$info) {
    echo json_encode(['status' => 'error', 'message' => 'Không tìm thấy bài thi.']);
    exit();
}

$class_id  = $info['class_id'];
$ten_hs    = $info['ten_hs'];
$ten_baithi = $info['title'];
$ten_lop   = $info['ten_lop'];

// ── 3. Upsert quiz_cheating_log ──────────────────────────────────────────
$stmt2 = $conn->prepare(
    "SELECT id, so_lan_vi_pham, is_locked
     FROM quiz_cheating_log WHERE quiz_id = ? AND student_id = ?"
);
$stmt2->bind_param("ii", $quiz_id, $student_id);
$stmt2->execute();
$existing = $stmt2->get_result()->fetch_assoc();

$is_locked = 0;

if ($existing) {
    if ($existing['is_locked']) {
        echo json_encode(['status' => 'locked', 'so_lan' => $existing['so_lan_vi_pham']]);
        exit();
    }
    $new_count = max($existing['so_lan_vi_pham'], $vi_pham);
    $is_locked = ($new_count >= 3) ? 1 : 0;

    $stmt3 = $conn->prepare(
        "UPDATE quiz_cheating_log
         SET so_lan_vi_pham = ?, is_locked = ?, cap_nhat_luc = NOW()
         WHERE quiz_id = ? AND student_id = ?"
    );
    $stmt3->bind_param("iiii", $new_count, $is_locked, $quiz_id, $student_id);
    $stmt3->execute();
} else {
    $new_count = $vi_pham;
    $is_locked = ($new_count >= 3) ? 1 : 0;

    $stmt3 = $conn->prepare(
        "INSERT INTO quiz_cheating_log (quiz_id, student_id, so_lan_vi_pham, is_locked)
         VALUES (?, ?, ?, ?)"
    );
    $stmt3->bind_param("iiii", $quiz_id, $student_id, $new_count, $is_locked);
    $stmt3->execute();
}

// ── 4. Nếu đủ 3 lần → gửi thông báo giáo viên ──────────────────────────
if ($is_locked) {
    // Kiểm tra đã gửi thông báo chưa — dùng cột noi_dung tìm quiz_id + student_id
    $check_tag = "qid={$quiz_id};sid={$student_id}";
    $stmt_chk  = $conn->prepare(
        "SELECT id FROM thong_bao WHERE class_id = ? AND noi_dung LIKE ? LIMIT 1"
    );
    $like_val = "%{$check_tag}%";
    $stmt_chk->bind_param("is", $class_id, $like_val);
    $stmt_chk->execute();
    $already = $stmt_chk->get_result()->num_rows > 0;

    if (!$already) {
        $noi_dung = "🚨 GIAN LẬN | Học sinh \"$ten_hs\" đã thoát tab $new_count lần "
                  . "trong bài thi \"$ten_baithi\" (lớp $ten_lop). "
                  . "Bài thi đã bị khóa tự động. [qid={$quiz_id};sid={$student_id}]";

        // nguoi_gui_id = 0 → hệ thống tự động
        $stmt_tb = $conn->prepare(
            "INSERT INTO thong_bao (class_id, nguoi_gui_id, noi_dung) VALUES (?, 0, ?)"
        );
        $stmt_tb->bind_param("is", $class_id, $noi_dung);
        $stmt_tb->execute();
    }

    echo json_encode([
        'status'  => 'locked',
        'so_lan'  => $new_count,
        'message' => 'Bài thi đã bị khóa.'
    ]);
    exit();
}

echo json_encode([
    'status'  => 'ok',
    'so_lan'  => $new_count,
    'message' => "Ghi nhận vi phạm lần $new_count."
]);
