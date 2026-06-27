<?php
// Khởi động session cho học sinh
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.name', 'HS_SESSION');
    session_start();
}

// Kết nối cơ sở dữ liệu chung
include '../config.php'; 

// Đẩy về trang đăng nhập nếu chưa đăng nhập hoặc không phải học sinh
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'student') {
    header("Location: ../trangdangnhap.php");
    exit();
}

// Dừng trang nếu URL không truyền ID lớp học
if (!isset($_GET['id'])) {
    die("Không tìm thấy lớp học!");
}

$id_lop = intval($_GET['id']);
$id_hocsinh = $_SESSION['user_id'];

// Lấy thông tin chi tiết lớp học và tên giáo viên
$sql_lop = "SELECT classes.*, users.hoten AS ten_gv 
            FROM classes 
            JOIN users ON classes.giaovien_id = users.id 
            WHERE classes.id = ?";
$stmt_lop = $conn->prepare($sql_lop);
$stmt_lop->bind_param("i", $id_lop);
$stmt_lop->execute();
$lop = $stmt_lop->get_result()->fetch_assoc();

// Báo lỗi nếu lớp học đã bị xóa
if (!$lop) die("Lớp học không tồn tại!");

// Xác định Tab hiện tại đang mở (mặc định là bảng thông báo)
$tab = $_GET['tab'] ?? 'thong-bao';

// Lấy danh sách các bài TỰ LUẬN đã nộp để hiển thị trạng thái điểm/file
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

// Lấy danh sách bài TRẮC NGHIỆM đã làm để chặn thi lại và hiện nhận xét
$quiz_done_list = [];
$stmt_check_quiz = $conn->prepare("SELECT quiz_id, score, nhan_xet_gv FROM quiz_results WHERE student_id = ?");
$stmt_check_quiz->bind_param("i", $id_hocsinh);
$stmt_check_quiz->execute();
$res_quiz = $stmt_check_quiz->get_result();
while ($r = $res_quiz->fetch_assoc()) {
    $quiz_done_list[$r['quiz_id']] = [
        'score'      => $r['score'],
        'nhan_xet'   => $r['nhan_xet_gv'] ?? '',
    ];
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
        /* Reset CSS và dùng font chữ Nunito */
        * { 
            margin: 0; 
            padding: 0; 
            box-sizing: border-box; 
            font-family: 'Nunito', sans-serif; 
        }

        /* Nền xám nhạt cho toàn bộ trang */
        body { 
            background-color: #f4f7f6; 
            display: flex; 
            flex-direction: column; 
            min-height: 100vh; 
        }

        /* Khu vực hiển thị nội dung chính */
        .main-content { 
            margin-left: 260px; 
            padding: 90px 30px 30px 30px; 
            transition: all 0.3s; 
        }
        
        /* Banner xanh gradient hiển thị tên lớp */
        .class-banner { 
            background: linear-gradient(135deg, #1e3a8a, #3b82f6); 
            color: white; 
            padding: 30px; 
            border-radius: 16px; 
            margin-bottom: 25px; 
            box-shadow: 0 4px 15px rgba(30, 58, 138, 0.15); 
        }
        .class-banner h1 { 
            font-size: 28px; 
            font-weight: 800; 
            margin-bottom: 8px; 
        }
        
        /* Thanh menu ngang chọn chức năng (Tabs) */
        .class-nav { 
            display: flex; 
            gap: 10px; 
            border-bottom: 2px solid #e2e8f0; 
            margin-bottom: 25px; 
            padding-bottom: 2px; 
        }
        .nav-tab { 
            padding: 12px 20px; 
            text-decoration: none; 
            color: #64748b; 
            font-weight: 700; 
            font-size: 16px; 
            border-bottom: 3px solid transparent; 
            transition: all 0.2s; 
            display: flex; 
            align-items: center; 
            gap: 8px; 
        }
        .nav-tab:hover { 
            color: #1e3a8a; 
        }
        .nav-tab.active { 
            color: #1e3a8a; 
            border-bottom-color: #1e3a8a; 
        }

        /* Khung giấy trắng bao bọc nội dung bên dưới tab */
        .content-card { 
            background: white; 
            padding: 25px; 
            border-radius: 16px; 
            box-shadow: 0 4px 12px rgba(0,0,0,0.03); 
            min-height: 300px; 
        }
        
        /* Khung hiển thị từng bài tập / bài trắc nghiệm */
        .item-box { 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            padding: 20px; 
            border: 1px solid #e2e8f0; 
            border-radius: 12px; 
            margin-bottom: 15px; 
            transition: all 0.2s; 
        }
        .item-box:hover { 
            box-shadow: 0 6px 18px rgba(0,0,0,0.05); 
        }

        /* Nút bấm mặc định màu xanh dương đậm */
        .btn-action { 
            background: #1e3a8a; 
            color: white; 
            padding: 10px 18px; 
            border-radius: 8px; 
            text-decoration: none; 
            font-weight: 700; 
            font-size: 14px; 
            transition: 0.2s; 
            border: none; 
            cursor: pointer; 
        }
        .btn-action:hover { 
            background: #1d4ed8; 
        }
        
        /* Khung chứa bài đăng (Thông báo) */
        .post-card { 
            border: 1px solid #e2e8f0; 
            border-radius: 12px; 
            padding: 20px; 
            margin-bottom: 20px; 
        }
        .post-header { 
            display: flex; 
            align-items: center; 
            gap: 12px; 
            margin-bottom: 15px; 
        }

        /* Ảnh đại diện chữ cái của người đăng bài */
        .post-avatar { 
            width: 40px; 
            height: 40px; 
            background: #e2e8f0; 
            border-radius: 50%; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            font-weight: bold; 
            color: #1e3a8a; 
        }

        /* Khu vực hiển thị bình luận dưới bài đăng */
        .bl-section { 
            margin-top: 16px; 
            padding-top: 14px; 
            border-top: 1px solid #e2e8f0; 
        }
        .bl-item { 
            display: flex; 
            gap: 10px; 
            margin-bottom: 10px; 
            align-items: flex-start; 
        }

        /* Ảnh đại diện chữ cái trong khung bình luận */
        .bl-avatar { 
            width: 32px; 
            height: 32px; 
            border-radius: 50%; 
            background: #e1f5fe; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            font-weight: 800; 
            font-size: 12px; 
            color: #0277bd; 
            flex-shrink: 0; 
            overflow: hidden; 
        }
        .bl-avatar img { 
            width: 100%; 
            height: 100%; 
            object-fit: cover; 
        }

        /* Bong bóng chat bình luận */
        .bl-bubble { 
            background: #f8fafc; 
            border-radius: 12px; 
            padding: 9px 13px; 
            flex: 1; 
        }

        /* Tên người bình luận */
        .bl-name { 
            font-size: 13px; 
            font-weight: 800; 
            color: #1e293b; 
            margin-bottom: 3px; 
        }

        /* Nội dung và thời gian bình luận */
        .bl-text { 
            font-size: 14px; 
            color: #374151; 
            line-height: 1.5; 
        }
        .bl-time { 
            font-size: 11px; 
            color: #94a3b8; 
            margin-top: 3px; 
        }

        /* Form nhập bình luận mới */
        .bl-form { 
            display: flex; 
            gap: 8px; 
            margin-top: 12px; 
            align-items: center; 
        }
        .bl-input { 
            flex: 1; 
            padding: 9px 14px; 
            border: 1px solid #cbd5e1; 
            border-radius: 20px; 
            font-family: inherit; 
            font-size: 14px; 
            outline: none; 
            transition: border-color 0.2s; 
        }
        .bl-input:focus { 
            border-color: #1e3a8a; 
        }

        /* Nút gửi bình luận (Icon máy bay giấy) */
        .bl-send { 
            width: 36px; 
            height: 36px; 
            border-radius: 50%; 
            background: #1e3a8a; 
            border: none; 
            color: white; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            cursor: pointer; 
            flex-shrink: 0; 
            transition: 0.2s; 
        }
        .bl-send:hover { 
            background: #1d4ed8; 
        }
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
            <a href="?id=<?= $id_lop ?>&tab=nhom" class="nav-tab <?= $tab == 'nhom' ? 'active' : '' ?>">
                <i class="fa-solid fa-users"></i> Nhóm học tập
            </a>
        </div>

        <div class="content-card">
            <?php switch ($tab): 
                
                // MỤC 1: TAB THÔNG BÁO VÀ BẢNG TIN
                case 'thong-bao': ?>
                    <h3 style="margin-bottom: 20px; color: #1e3a8a;"> Bảng tin & Thông báo từ Giáo viên</h3>
                    <?php
                    // Lấy bài đăng từ CSDL (Hỗ trợ 2 tên trường cũ/mới)
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
                            
                            <?php if (!empty($post['file_dinh_kem'])): ?>
                                <div style="margin:12px 0;padding:10px 14px;background:#f0f9ff;border:1px solid #bae6fd;border-radius:8px;">
                                    <p style="font-size:13px;font-weight:700;color:#0369a1;margin-bottom:8px;">📎 File đính kèm từ giáo viên:</p>
                                    <?php foreach(array_filter(explode(',', $post['file_dinh_kem'])) as $fn): $fn=trim($fn); ?>
                                        <a href="../uploads/<?= htmlspecialchars($fn) ?>" target="_blank"
                                        style="display:inline-flex;align-items:center;gap:6px;background:white;border:1px solid #bae6fd;padding:6px 12px;border-radius:6px;font-size:13px;font-weight:600;color:#0277bd;text-decoration:none;margin:3px;">
                                            <?= htmlspecialchars($fn) ?>
                                        </a>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                            
                            <div class="bl-section">
                                <?php
                                $stmt_bl = $conn->prepare("
                                    SELECT bl.*, u.hoten, u.avatar, u.role
                                    FROM binh_luan bl
                                    JOIN users u ON u.id = bl.id_user
                                    WHERE bl.id_bangtin = ?
                                    ORDER BY bl.ngay_tao ASC
                                ");
                                $stmt_bl->bind_param("i", $post['id']);
                                $stmt_bl->execute();
                                $bls = $stmt_bl->get_result();
                                ?>
                                <?php while ($bl = $bls->fetch_assoc()):
                                    $bl_av = !empty($bl['avatar']) ? '../uploads/' . ltrim($bl['avatar'], '/') : '';
                                    $bl_char = mb_substr($bl['hoten'], 0, 1, 'UTF-8');
                                    $is_gv = ($bl['role'] === 'teacher');
                                ?>
                                <div class="bl-item">
                                    <div class="bl-avatar">
                                        <?php if ($bl_av): ?>
                                            <img src="<?= htmlspecialchars($bl_av) ?>" onerror="this.parentNode.textContent='<?= $bl_char ?>'">
                                        <?php else: ?>
                                            <?= htmlspecialchars($bl_char) ?>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="bl-bubble" <?= $is_gv ? 'style="background:#eff6ff;border-left:3px solid #1e40af;"' : 'style="background:#f0fdf4;border-left:3px solid #15803d;"' ?>>
                                        <div class="bl-name">
                                            <?= htmlspecialchars($bl['hoten']) ?>
                                            <?php if ($is_gv): ?>
                                                <span style="font-size:11px;background:#1e40af;color:white;padding:2px 7px;border-radius:10px;margin-left:5px;font-weight:700;">GV</span>
                                            <?php else: ?>
                                                <span style="font-size:11px;background:#15803d;color:white;padding:2px 7px;border-radius:10px;margin-left:5px;font-weight:700;">HS</span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="bl-text"><?= nl2br(htmlspecialchars($bl['noi_dung'])) ?></div>
                                        <div class="bl-time"><?= date('H:i d/m/Y', strtotime($bl['ngay_tao'])) ?></div>
                                    </div>
                                </div>
                                <?php endwhile; ?>

                                <form action="xuly_binhluan.php" method="POST" class="bl-form">
                                    <input type="hidden" name="id_bangtin" value="<?= $post['id'] ?>">
                                    <input type="hidden" name="id_lop" value="<?= $id_lop ?>">
                                    <input type="text" name="noi_dung_bl" class="bl-input" placeholder="Viết bình luận công khai tại đây...">
                                    <button type="submit" class="bl-send" title="Gửi"><i class="fa-solid fa-paper-plane" style="font-size:13px;"></i></button>
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

                // MỤC 2: TAB BÀI TẬP VỀ NHÀ TỰ LUẬN
                case 'bai-tap': ?>
                    <h3 style="margin-bottom: 20px; color: #1e3a8a;"> Danh sách bài tập tự luận tự học</h3>
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
                                    Hạn nộp: <?= $btap['han_nop'] ? date("H:i d/m/Y", strtotime($btap['han_nop'])) : 'Không giới hạn' ?>
                                </p>
                                
                                <?php if (!empty($btap['file_dinh_kem'])): ?>
                                    <div style="margin-top:12px;
                                    padding:10px 14px;
                                    background:#f0fdf4;
                                    border:1px solid #bbf7d0;
                                    border-radius:8px;">
                                        <p style="font-size:13px;
                                        font-weight:700;
                                        color:#15803d;
                                        margin-bottom:8px;"> Tài liệu đính kèm từ giáo viên:</p>
                                        <?php foreach(array_filter(explode(',', $btap['file_dinh_kem'])) as $fn): $fn=trim($fn); ?>
                                            <a href="../uploads/<?= htmlspecialchars($fn) ?>" target="_blank"
                                            style="display:inline-flex;
                                            align-items:center;
                                            gap:6px;
                                            background:white;
                                            border:1px solid #bbf7d0;
                                            padding:6px 12px;
                                            border-radius:6px;
                                            font-size:13px;
                                            font-weight:600;
                                            color:#15803d;
                                            text-decoration:none;
                                            margin:3px;">
                                            <?= htmlspecialchars($fn) ?>
                                            </a>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <?php 
                            // Kiểm tra xem bài này học sinh đã nộp chưa
                            $info_nop = $nop_bai_list[$btap['id']] ?? null;
                            if ($info_nop): ?>
                                <div style="background:#ecfdf5; border: 1px solid #a7f3d0; padding: 15px; border-radius: 8px;">
                                    <p style="font-weight: bold; color: #065f46; margin-bottom: 10px;">
                                        Đã nộp bài — Điểm: 
                                        <?php if ($info_nop['diem'] !== null): ?>
                                            <b style="color:#1e3a8a"><?= $info_nop['diem'] ?></b>
                                        <?php else: ?>
                                            <i style="color:#64748b">Đang chờ giáo viên chấm</i>
                                        <?php endif; ?>
                                    </p>

                                    <?php if (!empty($info_nop['file_nop'])): ?>
                                        <p style="font-size: 13px; color: #374151; margin-bottom: 6px;">
                                            File đã nộp: 
                                            <a href="../uploads/baitap/<?= htmlspecialchars($info_nop['file_nop']) ?>" target="_blank" style="color:#1d4ed8; font-weight:600;">
                                                <?= htmlspecialchars($info_nop['file_nop']) ?>
                                            </a>
                                        </p>
                                    <?php endif; ?>

                                    <?php if (!empty($info_nop['link_nop'])): ?>
                                        <p style="font-size: 13px; color: #374151; margin-bottom: 6px;">
                                            Link đã nộp: 
                                            <a href="<?= htmlspecialchars($info_nop['link_nop']) ?>" target="_blank" style="color:#1d4ed8; font-weight:600;">
                                                <?= htmlspecialchars($info_nop['link_nop']) ?>
                                            </a>
                                        </p>
                                    <?php endif; ?>

                                    <button disabled style="width:100%; margin-top:10px; padding:12px; background:#9ca3af; color:white; border:none; border-radius:8px; font-size:15px; font-weight:bold; cursor:not-allowed;">
                                        Đã nộp bài
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

                // MỤC 3: TAB TRẮC NGHIỆM TRỰC TUYẾN
                case 'trac-nghiem': ?>
                    <h3 style="margin-bottom: 20px; color: #1e3a8a;"> Hệ thống bài thi trắc nghiệm trực tuyến</h3>
                    <?php
                    $stmt_quiz = $conn->prepare("SELECT * FROM quizzes WHERE class_id = ? ORDER BY created_at DESC");
                    $stmt_quiz->bind_param("i", $id_lop);
                    $stmt_quiz->execute();
                    $quizzes = $stmt_quiz->get_result();

                    if ($quizzes->num_rows > 0):
                        while ($quiz = $quizzes->fetch_assoc()):
                    ?>
                        <div class="item-box" style="<?= (isset($quiz_done_list[$quiz['id']]) && !empty($quiz_done_list[$quiz['id']]['nhan_xet'])) ? 'border-left: 4px solid #3b82f6; flex-wrap: wrap; gap: 12px;' : '' ?>">
                            <div style="flex: 1; min-width: 0;">
                                <h4 style="color: #1e293b; margin-bottom: 5px;"><?= htmlspecialchars($quiz['title']) ?></h4>
                                <p style="font-size: 13px; color: #64748b;">
                                    Thời gian làm bài: <b><?= intval($quiz['duration_minutes']) ?> phút</b>
                                </p>
                                
                                <?php if (isset($quiz_done_list[$quiz['id']]) && !empty($quiz_done_list[$quiz['id']]['nhan_xet'])): ?>
                                <div style="margin-top: 10px; padding: 10px 14px; background: #eff6ff; border-left: 3px solid #3b82f6; border-radius: 0 8px 8px 0;">
                                    <div style="font-size: 11px; font-weight: 800; color: #3b82f6; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px;">📝 Nhận xét của giáo viên</div>
                                    <div style="font-size: 13px; color: #1e40af; font-weight: 600; line-height: 1.5;"><?= nl2br(htmlspecialchars($quiz_done_list[$quiz['id']]['nhan_xet'])) ?></div>
                                </div>
                                <?php endif; ?>
                            </div>
                            
                            <?php if (isset($quiz_done_list[$quiz['id']])): ?>
                                <div style="display: flex; flex-direction: column; align-items: flex-end; gap: 6px; flex-shrink: 0;">
                                    <span style="background:#ecfdf5; color:#065f46; border: 1px solid #a7f3d0; padding: 10px 15px; border-radius: 8px; font-weight: bold; white-space: nowrap;">
                                        Hoàn thành (Điểm: <?= $quiz_done_list[$quiz['id']]['score'] ?>/10)
                                    </span>
                                    <?php if (!empty($quiz_done_list[$quiz['id']]['nhan_xet'])): ?>
                                    <span style="background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; padding: 5px 12px; border-radius: 20px; font-size: 12px; font-weight: 800; white-space: nowrap;">
                                        Nhận xét
                                    </span>
                                    <?php endif; ?>
                                </div>
                            <?php else: ?>
                                <a href="lambaitap/lamtracnghiem.php?quiz_id=<?= $quiz['id'] ?>&id_lop=<?= $id_lop ?>" class="btn-action" style="background: #10b981; display: inline-block;">Bắt đầu thi <i class="fa-solid fa-pen-to-square"></i></a>
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

                // MỤC 4: TAB VIDEO BÀI GIẢNG
                case 'xem-video': ?>
                    <h3 style="margin-bottom: 20px; color: #1e3a8a;">📺 Không gian xem video bài giảng</h3>
                    <div style="text-align: center; padding: 20px 0;">
                        <p style="color: #475569; margin-bottom: 20px; font-weight: 600;">Hệ thống ghi chú thông minh và theo dõi video bài giảng tương tác cùng Giáo viên.</p>
                        <a href="video.php?id_lop=<?= $id_lop ?>" class="btn-action" style="background: #ea580c; padding: 14px 28px; font-size: 16px; display: inline-block;">
                            <i class="fa-solid fa-play-circle"></i> Vào phòng xem Video Bài Giảng
                        </a>
                    </div>
                <?php break; 

                // MỤC 5: TAB NHÓM HỌC TẬP (Lỗi cú pháp của bạn đã được sửa ở đây)
                case 'nhom': 
                    include 'quanly_nhom.php'; 
                    break; 

            endswitch; ?>
        </div>
    </div>

</body>
</html>