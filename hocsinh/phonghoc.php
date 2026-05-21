<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include '../config.php'; // ← Sửa từ dp.php → config.php

// Kiểm tra quyền học sinh
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'student') {
    header("Location: ../trangdangnhap.php");
    exit();
}

// Lấy ID lớp học từ URL (Ví dụ: phonghoc.php?id=1)
if (!isset($_GET['id'])) {
    die("Không tìm thấy lớp học!");
}
$id_lop = intval($_GET['id']);
$id_hocsinh = $_SESSION['user_id'];

// ============================================================
// 1. Lấy thông tin lớp học
//    Bảng: classes | Cột: giaovien_id | users: hoten
// ============================================================
$sql_lop = "SELECT classes.*, users.hoten AS ten_gv 
            FROM classes 
            JOIN users ON classes.giaovien_id = users.id 
            WHERE classes.id = ?";
$stmt_lop = $conn->prepare($sql_lop);
$stmt_lop->bind_param("i", $id_lop);
$stmt_lop->execute();
$lop = $stmt_lop->get_result()->fetch_assoc();
if (!$lop) die("Lớp học không tồn tại!");

// ============================================================
// 2. Lấy danh sách bảng tin
//    Bảng: bang_tin | Cột: class_id, noi_dung, ngay_tao
//    Lấy thông tin GV qua: bang_tin → classes → users
// ============================================================
$sql_bangtin = "SELECT bang_tin.*, users.hoten, users.avatar 
                FROM bang_tin 
                JOIN classes ON bang_tin.class_id = classes.id
                JOIN users ON classes.giaovien_id = users.id
                WHERE bang_tin.class_id = ? 
                ORDER BY bang_tin.ngay_tao DESC";
$stmt_bt = $conn->prepare($sql_bangtin);
$stmt_bt->bind_param("i", $id_lop);
$stmt_bt->execute();
$list_bangtin = $stmt_bt->get_result();

// ============================================================
// 3. Lấy danh sách bài tập
//    Bảng: bai_tap | Cột: class_id, tieu_de, han_nop
// ============================================================
$sql_baitap = "SELECT * FROM bai_tap WHERE class_id = ? ORDER BY han_nop ASC";
$stmt_btap = $conn->prepare($sql_baitap);
$stmt_btap->bind_param("i", $id_lop);
$stmt_btap->execute();
$list_baitap = $stmt_btap->get_result();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($lop['ten_lop']); ?> | Góc Học Tập</title>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Nunito', sans-serif; }
        body { background-color: #f4f7f6; }
        
        .main-content { margin-left: 260px; padding: 40px; padding-top: 100px; transition: margin-left 0.3s; }
        .main-content.mo-rong { margin-left: 0; }

        .class-header { background: #fff; padding: 25px; border-radius: 15px; border: 1px solid #eceff1; margin-bottom: 30px; border-left: 5px solid #0288d1; }
        .class-header h1 { color: #263238; font-size: 26px; font-weight: 800; }
        .class-header p { color: #78909c; font-weight: 600; margin-top: 5px; }

        /* Bố cục 2 cột */
        .room-container { display: flex; gap: 30px; align-items: start; }
        .left-column { flex: 7; display: flex; flex-direction: column; gap: 20px; }
        .right-column { flex: 3; display: flex; flex-direction: column; gap: 20px; position: sticky; top: 100px; }

        /* Khung bài đăng (Bảng tin) */
        .feed-card { background: #fff; border-radius: 15px; border: 1px solid #eceff1; padding: 25px; box-shadow: 0 4px 10px rgba(0,0,0,0.01); }
        .feed-author { display: flex; align-items: center; gap: 12px; margin-bottom: 15px; }
        .feed-author img { width: 42px; height: 42px; border-radius: 50%; object-fit: cover; background: #eee; }
        .author-info h4 { color: #263238; font-weight: 700; font-size: 16px; }
        .author-info span { color: #90a4ae; font-size: 12px; font-weight: 600; }
        .feed-content { color: #455a64; font-size: 15px; line-height: 1.6; font-weight: 600; white-space: pre-line; }

        /* Khung bài tập bên phải */
        .sidebar-box { background: #fff; border-radius: 15px; border: 1px solid #eceff1; padding: 20px; }
        .sidebar-box h3 { color: #263238; font-size: 18px; font-weight: 800; margin-bottom: 15px; }
        
        .task-card { background: #fafafa; border: 1px solid #f0f2f5; border-radius: 10px; padding: 15px; margin-bottom: 15px; }
        .task-card h4 { color: #263238; font-size: 15px; font-weight: 700; margin-bottom: 5px; }
        .task-card .deadline { color: #e53935; font-size: 12px; font-weight: 700; margin-bottom: 12px; }

        /* Form nộp bài */
        .upload-form { display: flex; flex-direction: column; gap: 8px; border-top: 1px dashed #cfd8dc; padding-top: 12px; }
        .upload-form label { font-size: 12px; color: #546e7a; font-weight: 700; }
        .file-select { font-size: 12px; font-weight: 600; color: #78909c; }
        .link-input { padding: 8px 12px; font-size: 13px; border: 1px solid #cfd8dc; border-radius: 6px; outline: none; font-weight: 600; }
        .btn-submit-task { background: #ff9800; color: #fff; border: none; padding: 8px; border-radius: 6px; font-weight: 700; cursor: pointer; font-size: 13px; margin-top: 5px; transition: 0.2s; }
        .btn-submit-task:hover { background: #f57c00; }
        
        .status-success { color: #4caf50 !important; font-weight: 700; font-size: 13px; }
    </style>
</head>
<body>

    <?php include 'thanh.php'; ?>

    <div class="main-content">
        <div class="class-header">
            <h1>🎨 Lớp: <?php echo htmlspecialchars($lop['ten_lop']); ?></h1>
            <p>
                Mã lớp: <strong><?php echo htmlspecialchars($lop['ma_lop']); ?></strong> &nbsp;|&nbsp;
                Giáo viên: <strong><?php echo htmlspecialchars($lop['ten_gv']); ?></strong>
            </p>
        </div>

        <div class="room-container">
            
            <!-- CỘT TRÁI: BẢNG TIN -->
            <div class="left-column">
                <h2 style="color: #263238; font-size: 18px; font-weight: 800;">📢 Bảng tin lớp học</h2>
                
                <?php if ($list_bangtin->num_rows > 0): ?>
                    <?php while ($bt = $list_bangtin->fetch_assoc()): ?>
                        <div class="feed-card">
                            <div class="feed-author">
                                <?php
                                // Xác định đường dẫn avatar giáo viên
                                $av_path = !empty($bt['avatar']) ? '../' . $bt['avatar'] : '../uploads/default.png';
                                ?>
                                <img src="<?php echo htmlspecialchars($av_path); ?>" alt="GV">
                                <div class="author-info">
                                    <!-- Dùng cột hoten (khớp với bảng users trong SQL) -->
                                    <h4><?php echo htmlspecialchars($bt['hoten']); ?> (Giáo viên)</h4>
                                    <!-- Dùng cột ngay_tao (khớp với bảng bang_tin trong SQL) -->
                                    <span>Đăng lúc: <?php echo date('H:i d/m/Y', strtotime($bt['ngay_tao'])); ?></span>
                                </div>
                            </div>
                            <div class="feed-content"><?php echo htmlspecialchars($bt['noi_dung']); ?></div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div style="background:#fff; padding:30px; border-radius:15px; text-align:center; color:#90a4ae; font-weight:600;">
                        Giáo viên chưa đăng thông báo nào lên bảng tin.
                    </div>
                <?php endif; ?>
            </div>

            <!-- CỘT PHẢI: BÀI TẬP -->
            <div class="right-column">
                <div class="sidebar-box">
                    <h3>📝 Bài tập sắp đến hạn</h3>
                    
                    <?php if ($list_baitap->num_rows > 0): ?>
                        <?php while ($btap = $list_baitap->fetch_assoc()): ?>
                            <div class="task-card">
                                <!-- Dùng cột tieu_de (khớp với bảng bai_tap trong SQL) -->
                                <h4><?php echo htmlspecialchars($btap['tieu_de']); ?></h4>
                                
                                <?php
                                // Kiểm tra học sinh đã nộp chưa
                                // Bảng: nop_bai | Cột: bai_tap_id, student_id
                                $sql_check = "SELECT id FROM nop_bai WHERE bai_tap_id = ? AND student_id = ?";
                                $stmt_ck = $conn->prepare($sql_check);
                                $stmt_ck->bind_param("ii", $btap['id'], $id_hocsinh);
                                $stmt_ck->execute();
                                $da_nop = $stmt_ck->get_result()->fetch_assoc();
                                ?>

                                <?php if ($da_nop): ?>
                                    <p class="status-success">✓ Đã hoàn thành nộp bài</p>
                                <?php else: ?>
                                    <!-- Dùng cột han_nop (khớp với bảng bai_tap trong SQL) -->
                                    <p class="deadline">Hạn chót: <?php echo date('H:i d/m/Y', strtotime($btap['han_nop'])); ?></p>
                                    
                                    <form action="xuly_nopbai.php" method="POST" enctype="multipart/form-data" class="upload-form">
                                        <input type="hidden" name="id_baitap" value="<?php echo $btap['id']; ?>">
                                        <input type="hidden" name="id_lop" value="<?php echo $id_lop; ?>">
                                        
                                        <label>Cách 1: Tải file lên từ máy</label>
                                        <input type="file" name="file_baitap" class="file-select">
                                        
                                        <label>Cách 2: Dán link bài tập</label>
                                        <input type="url" name="link_baitap" class="link-input" placeholder="https://drive.google.com/...">
                                        
                                        <button type="submit" class="btn-submit-task">Nộp bài ngay ➔</button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p style="color:#90a4ae; text-align:center; font-size:14px; font-weight:600; padding:10px 0;">
                            Lớp học hiện tại không có bài tập nào!
                        </p>
                    <?php endif; ?>
                </div>
            </div>

        </div>
    </div>

</body>
</html>