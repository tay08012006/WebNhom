<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('session.name', 'GV_SESSION');
session_start();
require_once '../config.php';

// Kiểm tra đăng nhập giáo viên
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: ../trangdangnhap.php?error=Bạn cần đăng nhập!");
    exit;
}

$id_lop_hoc = isset($_GET['id_lop_hoc']) ? intval($_GET['id_lop_hoc']) : 0;

// Xác minh tính hợp pháp của lớp học (Lớp này có đúng của giáo viên đang đăng nhập quản lý không)
$stmt_class = $conn->prepare("SELECT ten_lop FROM classes WHERE id = ? AND giaovien_id = ?");
$stmt_class->bind_param("ii", $id_lop_hoc, $_SESSION['user_id']);
$stmt_class->execute();
$res_class = $stmt_class->get_result();

if ($res_class->num_rows === 0) {
    echo "<script>alert('Lớp học không hợp lệ hoặc bạn không có quyền truy cập báo cáo này!'); window.location.href='index.php';</script>";
    exit;
}
$lop = $res_class->fetch_assoc();

// Tính điểm số trung bình cộng bằng SQL AVG
$stmt_avg = $conn->prepare("SELECT 
                            AVG(sao_kien_thuc) as tb_kien_thuc, 
                            AVG(sao_su_pham) as tb_su_pham, 
                            AVG(sao_ho_tro) as tb_ho_tro,
                            COUNT(id) as tong_luot
                        FROM danh_gia 
                        WHERE id_lop_hoc = ?");
$stmt_avg->bind_param("i", $id_lop_hoc);
$stmt_avg->execute();
$thong_ke = $stmt_avg->get_result()->fetch_assoc();

$tong_luot    = $thong_ke['tong_luot'];
$tb_kien_thuc = round($thong_ke['tb_kien_thuc'] ?? 0, 1);
$tb_su_pham   = round($thong_ke['tb_su_pham'] ?? 0, 1);
$tb_ho_tro    = round($thong_ke['tb_ho_tro'] ?? 0, 1);

// Tính điểm tổng quan chung tổng hợp
$diem_tong_quan = round(($tb_kien_thuc + $tb_su_pham + $tb_ho_tro) / 3, 1);

// Lấy danh sách nhận xét văn bản của học sinh lớp này
$stmt_cmt = $conn->prepare("SELECT binh_luan, ngay_tao FROM danh_gia WHERE id_lop_hoc = ? AND binh_luan != '' ORDER BY ngay_tao DESC");
$stmt_cmt->bind_param("i", $id_lop_hoc);
$stmt_cmt->execute();
$ket_qua_nhan_xet = $stmt_cmt->get_result();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thống Kê Đánh Giá Chất Lượng | Góc Học Tập</title>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Nunito', sans-serif; }
        body { background: #f4f7f9; color: #1e293b; padding: 40px 20px; }
        .container { max-width: 950px; margin: 0 auto; }
        
        .header-area { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
        h1 { font-size: 26px; font-weight: 800; color: #1a237e; }
        .btn-back { background: #ffffff; color: #475569; border: 1px solid #cbd5e1; padding: 10px 18px; border-radius: 20px; font-weight: 700; font-size: 14px; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; box-shadow: 0 2px 4px rgba(0,0,0,0.02); }
        .btn-back:hover { background: #f8fafc; }

        .sub-desc { color: #666; font-size: 15px; margin-top: -20px; margin-bottom: 30px; }
        .sub-desc strong { color: #0288d1; }
        
        /* Layout Thẻ thống kê điểm */
        .khung-thong-ke { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 35px; }
        .the-thong-ke { background: white; padding: 22px; border-radius: 14px; box-shadow: 0 4px 12px rgba(0,0,0,0.03); border-top: 4px solid #0288d1; text-align: center; border-left: 1px solid #e2e8f0; border-right: 1px solid #e2e8f0; border-bottom: 1px solid #e2e8f0; }
        .the-thong-ke.tong-the { border-top-color: #ff9100; background: #fffcf8; }
        .the-thong-ke h3 { margin: 0 0 12px 0; font-size: 13px; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 700; }
        .the-thong-ke .diem-so { font-size: 26px; font-weight: 800; color: #0f172a; }

        /* Khung hiển thị bình luận văn bản */
        .vung-nhan-xet { margin-top: 45px; }
        .vung-nhan-xet h2 { font-size: 19px; font-weight: 800; color: #1a237e; margin-bottom: 20px; }
        .the-nhan-xet { background: white; padding: 18px 24px; border-radius: 12px; box-shadow: 0 3px 8px rgba(0,0,0,0.02); margin-bottom: 15px; border-left: 5px solid #10b981; border-top: 1px solid #f1f5f9; border-right: 1px solid #f1f5f9; border-bottom: 1px solid #f1f5f9; }
        .the-nhan-xet p { margin: 0 0 8px 0; font-size: 15px; line-height: 1.6; color: #334155; font-style: italic; }
        .the-nhan-xet .thoi-gian { font-size: 12px; color: #94a3b8; text-align: right; font-weight: 600; }
        .no-data { text-align: center; color: #94a3b8; font-style: italic; padding: 30px; background: white; border-radius: 12px; border: 1px solid #e2e8f0; }
    </style>
</head>
<body>

<div class="container">
    <div class="header-area">
        <h1>Thống Kê Phản Hồi Chất Lượng Giảng Dạy</h1>
        <a href="index.php" class="btn-back">← Quay lại danh sách lớp</a>
    </div>
    
    <div class="sub-desc">
        Đang xem báo cáo lớp: <strong><?php echo htmlspecialchars($lop['ten_lop']); ?></strong> | Tổng số lượt học sinh làm khảo sát: <strong><?php echo $tong_luot; ?></strong> lượt.
    </div>
    
    <div class="khung-thong-ke">
        <div class="the-thong-ke tong-the">
            <h3>Điểm Tổng Quan Chung</h3>
            <div class="diem-so" style="color: #ea580c;">⭐ <?php echo $tong_luot > 0 ? $diem_tong_quan : '0.0'; ?> <span style="font-size:15px; color:#94a3b8; font-weight:400;">/ 5</span></div>
        </div>
        <div class="the-thong-ke">
            <h3>1. Bài giảng & Kiến thức</h3>
            <div class="diem-so"><?php echo $tong_luot > 0 ? $tb_kien_thuc : '0.0'; ?></div>
        </div>
        <div class="the-thong-ke">
            <h3>2. Phương pháp sư phạm</h3>
            <div class="diem-so"><?php echo $tong_luot > 0 ? $tb_su_pham : '0.0'; ?></div>
        </div>
        <div class="the-thong-ke">
            <h3>3. Tương tác & Hỗ trợ</h3>
            <div class="diem-so"><?php echo $tong_luot > 0 ? $tb_ho_tro : '0.0'; ?></div>
        </div>
    </div>

    <div class="vung-nhan-xet">
        <h2>Ý kiến đóng góp, góp ý văn bản (Ẩn danh)</h2>
        <?php 
        if ($ket_qua_nhan_xet->num_rows > 0) {
            while ($dong = $ket_qua_nhan_xet->fetch_assoc()) {
                echo '<div class="the-nhan-xet">';
                echo '<p>"' . htmlspecialchars($dong['binh_luan']) . '"</p>';
                echo '<div class="thoi-gian">Đã gửi vào: ' . date('H:i - d/m/Y', strtotime($dong['ngay_tao'])) . '</div>';
                echo '</div>';
            }
        } else {
            echo '<div class="no-data">Hiện chưa có ý kiến đóng góp dạng nhận xét bằng chữ nào cho lớp học này.</div>';
        }
        ?>
    </div>
</div>

</body>
</html>