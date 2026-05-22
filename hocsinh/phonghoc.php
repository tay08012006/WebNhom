<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include '../config.php'; 

// 1. Kiểm tra quyền truy cập của học sinh
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'student') {
    header("Location: ../trangdangnhap.php");
    exit();
}

if (!isset($_GET['id'])) {
    die("Không tìm thấy lớp học!");
}

$id_lop = intval($_GET['id']);
$id_hocsinh = $_SESSION['user_id'];

// 2. Lấy thông tin lớp học và giáo viên
$sql_lop = "SELECT classes.*, users.hoten AS ten_gv 
            FROM classes 
            JOIN users ON classes.giaovien_id = users.id 
            WHERE classes.id = ?";
$stmt_lop = $conn->prepare($sql_lop);
$stmt_lop->bind_param("i", $id_lop);
$stmt_lop->execute();
$lop = $stmt_lop->get_result()->fetch_assoc();
if (!$lop) die("Lớp học không tồn tại!");

// 3. Xử lý Tab hiện tại (Mặc định là 'thong-bao')
$tab = $_GET['tab'] ?? 'thong-bao';

// 4. Lấy danh sách bài TỰ LUẬN đã nộp (để cập nhật trạng thái "Đã nộp")
$nop_bai_list = [];
$stmt_check_nop = $conn->prepare("SELECT bai_tap_id, diem, file_nop, link_nop FROM nop_bai WHERE student_id = ?");
$stmt_check_nop->bind_param("i", $id_hocsinh);
$stmt_check_nop->execute();
$res_nop = $stmt_check_nop->get_result();
while ($r = $res_nop->fetch_assoc()) {
    $nop_bai_list[$r['bai_tap_id']] = [
        'diem'     => $r['diem'],
        'file_nop' => $r['file_nop'],
        'link_nop' => $r['link_nop'],
    ];
}

// 5. Lấy danh sách bài TRẮC NGHIỆM đã làm (để chặn làm lại)
$quiz_done_list = [];
$stmt_check_quiz = $conn->prepare("SELECT quiz_id, score FROM quiz_results WHERE student_id = ?");
$stmt_check_quiz->bind_param("i", $id_hocsinh);
$stmt_check_quiz->execute();
$res_quiz = $stmt_check_quiz->get_result();
while ($r = $res_quiz->fetch_assoc()) {
    $quiz_done_list[$r['quiz_id']] = $r['score'];
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($lop['ten_lop']) ?> | Học Sinh</title>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Nunito', sans-serif; }
        body { background-color: #f4f7f6; display: flex; flex-direction: column; min-height: 100vh; }
        .main-content { margin-left: 260px; padding: 90px 30px 30px 30px; transition: all 0.3s; }
        
        /* Banner lớp học */
        .class-banner { background: linear-gradient(135deg, #1e3a8a, #3b82f6); color: white; padding: 30px; border-radius: 16px; margin-bottom: 25px; box-shadow: 0 4px 15px rgba(30, 58, 138, 0.15); }
        .class-banner h1 { font-size: 28px; font-weight: 800; margin-bottom: 8px; }
        
        /* Thanh điều hướng Tabs giống Giáo viên */
        .class-nav { display: flex; gap: 10px; border-bottom: 2px solid #e2e8f0; margin-bottom: 25px; padding-bottom: 2px; }
        .nav-tab { padding: 12px 20px; text-decoration: none; color: #64748b; font-weight: 700; font-size: 16px; border-bottom: 3px solid transparent; transition: all 0.2s; display: flex; align-items: center; gap: 8px; }
        .nav-tab:hover { color: #1e3a8a; }
        .nav-tab.active { color: #1e3a8a; border-bottom-color: #1e3a8a; }

        /* Khung nội dung chung */
        .content-card { background: white; padding: 25px; border-radius: 16px; box-shadow: 0 4px 12px rgba(0,0,0,0.03); min-height: 300px; }
        
        /* Style danh sách/mục */
        .item-box { display: flex; justify-content: space-between; align-items: center; padding: 20px; border: 1px solid #e2e8f0; border-radius: 12px; margin-bottom: 15px; transition: all 0.2s; }
        .item-box:hover { box-shadow: 0 6px 18px rgba(0,0,0,0.05); }
        .btn-action { background: #1e3a8a; color: white; padding: 10px 18px; border-radius: 8px; text-decoration: none; font-weight: 700; font-size: 14px; transition: 0.2s; border: none; cursor: pointer; }
        .btn-action:hover { background: #1d4ed8; }
        
        /* Phần Thông báo / Bình luận */
        .post-card { border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px; margin-bottom: 20px; }
        .post-header { display: flex; align-items: center; gap: 12px; margin-bottom: 15px; }
        .post-avatar { width: 40px; height: 40px; background: #e2e8f0; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; color: #1e3a8a; }
        .comment-section { background: #f8fafc; padding: 15px; border-radius: 8px; margin-top: 15px; }
        .comment-form { display: flex; gap: 10px; margin-top: 10px; }
        .comment-input { flex: 1; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 6px; outline: none; }
    </style>
</head>
<body>

    <?php include 'thanh.php'; ?>

    <div class="main-content">
        <div class="class-banner">
            <h1>Lớp: <?= htmlspecialchars($lop['ten_lop']) ?></h1>
            <p><i class="fa-solid fa-chalkboard-user"></i> Giáo viên: <b><?= htmlspecialchars($lop['ten_gv']) ?></b> | Mã lớp: <b><?= htmlspecialchars($lop['ma_lop']) ?></b></p>
        </div>

        <div class="class-nav">
            <a href="?id=<?= $id_lop ?>&tab=thong-bao" class="nav-tab <?= $tab == 'thong-bao' ? 'active' : '' ?>">
                <i class="fa-solid fa-bullhorn"></i> Thông báo
            </a>
            <a href="?id=<?= $id_lop ?>&tab=bai-tap" class="nav-tab <?= $tab == 'bai-tap' ? 'active' : '' ?>">
                <i class="fa-solid fa-book-open"></i> Bài tập về nhà
            </a>
            <a href="?id=<?= $id_lop ?>&tab=trac-nghiem" class="nav-tab <?= $tab == 'trac-nghiem' ? 'active' : '' ?>">
                <i class="fa-solid fa-file-signature"></i> Kiểm tra trắc nghiệm
            </a>
            <a href="?id=<?= $id_lop ?>&tab=xem-video" class="nav-tab <?= $tab == 'xem-video' ? 'active' : '' ?>">
                <i class="fa-solid fa-video"></i> Bài giảng Video
            </a>
        </div>

        <div class="content-card">
            <?php switch ($tab): 
                // ==================== TAB 1: THÔNG BÁO / BẢNG TIN ====================
                case 'thong-bao': ?>
                    <h3 style="margin-bottom: 20px; color: #1e3a8a;">📢 Bảng tin & Thông báo từ Giáo viên</h3>
                    <?php
                    // Lấy các bài đăng trên bảng tin của lớp
                    $stmt_posts = $conn->prepare("SELECT * FROM bang_tin WHERE class_id = ? ORDER BY ngay_tao DESC");
                    if(!$stmt_posts) {
                        $stmt_posts = $conn->prepare("SELECT id, noi_dung, ngay_tao FROM bang_tin WHERE id_lop = ? ORDER BY ngay_tao DESC");
                    }
                    if($stmt_posts) {
                        $stmt_posts->bind_param("i", $id_lop);
                        $stmt_posts->execute();
                        $posts = $stmt_posts->get_result();
                    }
                    
                    if (isset($posts) && $posts->num_rows > 0):
                        while ($post = $posts->fetch_assoc()):
                    ?>
                        <div class="post-card">
                            <div class="post-header">
                                <div class="post-avatar"><?= mb_substr($lop['ten_gv'], 0, 1, 'UTF-8') ?></div>
                                <div>
                                    <h4 style="color: #334155;"><?= htmlspecialchars($lop['ten_gv']) ?></h4>
                                    <small style="color: #94a3b8;"><?= date("H:i d/m/Y", strtotime($post['ngay_tao'])) ?></small>
                                </div>
                            </div>
                            <div class="post-body" style="color: #334155; line-height: 1.6; white-space: pre-wrap;"><?= htmlspecialchars($post['noi_dung']) ?></div>
                            
                            <div class="comment-section">
                                <form action="xuly_binhluan.php" method="POST" class="comment-form">
                                    <input type="hidden" name="id_bangtin" value="<?= $post['id'] ?>">
                                    <input type="hidden" name="id_lop" value="<?= $id_lop ?>">
                                    <input type="text" name="noi_dung_bl" class="comment-input" placeholder="Viết bình luận công khai tại đây..." required>
                                    <button type="submit" class="btn-action" style="padding: 6px 12px;"><i class="fa-solid fa-paper-plane"></i></button>
                                </form>
                            </div>
                        </div>
                    <?php 
                        endwhile;
                    else: ?>
                        <div style="text-align:center; color:#94a3b8; padding: 40px 0;">
                            <i class="fa-solid fa-comment-slash" style="font-size: 40px; margin-bottom:10px;"></i>
                            <p>Lớp học chưa có thông báo hoặc bài đăng nào mới.</p>
                        </div>
                    <?php endif; ?>
                <?php break;

                // ==================== TAB 2: BÀI TẬP VỀ NHÀ ====================
                case 'bai-tap': ?>
                    <h3 style="margin-bottom: 20px; color: #1e3a8a;">📝 Danh sách bài tập tự luận tự học</h3>
                    <?php
                    $stmt_bt = $conn->prepare("SELECT * FROM bai_tap WHERE class_id = ? ORDER BY ngay_tao DESC");
                    $stmt_bt->bind_param("i", $id_lop);
                    $stmt_bt->execute();
                    $baitap = $stmt_bt->get_result();

                    if ($baitap->num_rows > 0):
                        while ($btap = $baitap->fetch_assoc()):
                    ?>
                        <div class="item-box" style="display: block;">
                            <div style="margin-bottom: 15px;">
                                <h4 style="color: #1e293b; margin-bottom: 5px;"><?= htmlspecialchars($btap['tieu_de']) ?></h4>
                                <?php if (!empty($btap['noi_dung'])): ?>
                                    <p style="font-size: 14px; color: #475569; margin-bottom: 10px;"><?= nl2br(htmlspecialchars($btap['noi_dung'])) ?></p>
                                <?php endif; ?>
                                <p style="font-size: 13px; color: #ef4444; font-weight: 600;">
                                    ⏰ Hạn nộp: <?= $btap['han_nop'] ? date("H:i d/m/Y", strtotime($btap['han_nop'])) : 'Không giới hạn' ?>
                                </p>
                            </div>
                            
                            <?php 
                            $info_nop = $nop_bai_list[$btap['id']] ?? null;
                            if ($info_nop): ?>
                                <div style="background:#ecfdf5; border: 1px solid #a7f3d0; padding: 15px; border-radius: 8px;">
                                    <p style="font-weight: bold; color: #065f46; margin-bottom: 10px;">
                                        ✅ Đã nộp bài — Điểm: 
                                        <?php if ($info_nop['diem'] !== null): ?>
                                            <b style="color:#1e3a8a"><?= $info_nop['diem'] ?></b>
                                        <?php else: ?>
                                            <i style="color:#64748b">Đang chờ giáo viên chấm</i>
                                        <?php endif; ?>
                                    </p>

                                    <?php if (!empty($info_nop['file_nop'])): ?>
                                        <p style="font-size: 13px; color: #374151; margin-bottom: 6px;">
                                            📎 File đã nộp: 
                                            <a href="../uploads/baitap/<?= htmlspecialchars($info_nop['file_nop']) ?>" 
                                               target="_blank" 
                                               style="color:#1d4ed8; font-weight:600;">
                                                <?= htmlspecialchars($info_nop['file_nop']) ?>
                                            </a>
                                        </p>
                                    <?php endif; ?>

                                    <?php if (!empty($info_nop['link_nop'])): ?>
                                        <p style="font-size: 13px; color: #374151; margin-bottom: 6px;">
                                            🔗 Link đã nộp: 
                                            <a href="<?= htmlspecialchars($info_nop['link_nop']) ?>" 
                                               target="_blank" 
                                               style="color:#1d4ed8; font-weight:600;">
                                                <?= htmlspecialchars($info_nop['link_nop']) ?>
                                            </a>
                                        </p>
                                    <?php endif; ?>

                                    <button disabled style="width:100%; margin-top:10px; padding:12px; background:#9ca3af; color:white; border:none; border-radius:8px; font-size:15px; font-weight:bold; cursor:not-allowed;">
                                        ✅ Đã nộp bài
                                    </button>
                                </div>
                            <?php else: ?>
                                <div style="background: #f8fafc; padding: 15px; border-radius: 8px; border: 1px dashed #cbd5e1;">
                                    <form action="nopbai.php" method="POST" enctype="multipart/form-data" style="display: flex; flex-direction: column; gap: 10px;">
                                        <input type="hidden" name="id_baitap" value="<?= $btap['id'] ?>">
                                        <input type="hidden" name="id_lop" value="<?= $id_lop ?>">
                                        
                                        <label style="font-size: 13px; font-weight: 600; color: #475569;">Cách 1: Tải file tài liệu / Hình ảnh lên</label>
                                        <input type="file" name="file_baitap" style="padding: 8px; border: 1px solid #cbd5e1; border-radius: 6px; background: white;">
                                        
                                        <label style="font-size: 13px; font-weight: 600; color: #475569;">Cách 2: Hoặc dán Link Google Drive/Tài liệu</label>
                                        <input type="url" name="link_baitap" placeholder="https://..." style="padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px;">
                                        
                                        <button type="submit" class="btn-action" style="margin-top: 5px;">Nộp bài hệ thống <i class="fa-solid fa-paper-plane"></i></button>
                                    </form>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php 
                        endwhile;
                    else: ?>
                        <div style="text-align:center; color:#94a3b8; padding: 40px 0;">
                            <i class="fa-solid fa-folder-open" style="font-size: 40px; margin-bottom:10px;"></i>
                            <p>Tuyệt vời! Hiện tại lớp học không có bài tập nào chưa làm.</p>
                        </div>
                    <?php endif; ?>
                <?php break;

                // ==================== TAB 3: TRẮC NGHIỆM TRỰC TUYẾN ====================
                case 'trac-nghiem': ?>
                    <h3 style="margin-bottom: 20px; color: #1e3a8a;">✍️ Hệ thống bài thi trắc nghiệm trực tuyến</h3>
                    <?php
                    $stmt_quiz = $conn->prepare("SELECT * FROM quizzes WHERE class_id = ? ORDER BY created_at DESC");
                    $stmt_quiz->bind_param("i", $id_lop);
                    $stmt_quiz->execute();
                    $quizzes = $stmt_quiz->get_result();

                    if ($quizzes->num_rows > 0):
                        while ($quiz = $quizzes->fetch_assoc()):
                    ?>
                        <div class="item-box">
                            <div>
                                <h4 style="color: #1e293b; margin-bottom: 5px;"><?= htmlspecialchars($quiz['title']) ?></h4>
                                <p style="font-size: 13px; color: #64748b;">
                                    ⏱️ Thời gian làm bài: <b><?= intval($quiz['duration_minutes']) ?> phút</b>
                                </p>
                            </div>
                            
                            <?php if (isset($quiz_done_list[$quiz['id']])): ?>
                                <span style="background:#ecfdf5; color:#065f46; border: 1px solid #a7f3d0; padding: 10px 15px; border-radius: 8px; font-weight: bold;">
                                    ⭐ Hoàn thành (Điểm: <?= $quiz_done_list[$quiz['id']] ?>/10)
                                </span>
                            <?php else: ?>
                                <a href="lam_tracnghiem.php?quiz_id=<?= $quiz['id'] ?>&id_lop=<?= $id_lop ?>" class="btn-action" style="background: #10b981; display: inline-block;">Bắt đầu thi <i class="fa-solid fa-pen-to-square"></i></a>
                            <?php endif; ?>
                        </div>
                    <?php 
                        endwhile;
                    else: ?>
                        <div style="text-align:center; color:#94a3b8; padding: 40px 0;">
                            <i class="fa-solid fa-square-poll-vertical" style="font-size: 40px; margin-bottom:10px;"></i>
                            <p>Không có đề thi trắc nghiệm trực tuyến nào được kích hoạt.</p>
                        </div>
                    <?php endif; ?>
                <?php break;

                // ==================== TAB 4: BÀI GIẢNG VIDEO ====================
                case 'xem-video': ?>
                    <h3 style="margin-bottom: 20px; color: #1e3a8a;">📺 Không gian xem video bài giảng</h3>
                    <div style="text-align: center; padding: 20px 0;">
                        <p style="color: #475569; margin-bottom: 20px; font-weight: 600;">Hệ thống ghi chú thông minh và theo dõi video bài giảng tương tác cùng Giáo viên.</p>
                        <a href="video.php?id_lop=<?= $id_lop ?>" class="btn-action" style="background: #ea580c; padding: 14px 28px; font-size: 16px; display: inline-block;">
                            <i class="fa-solid fa-play-circle"></i> Vào phòng xem Video Bài Giảng
                        </a>
                    </div>
                <?php break; ?>
            <?php endswitch; ?>
        </div>
    </div>

</body>
</html>