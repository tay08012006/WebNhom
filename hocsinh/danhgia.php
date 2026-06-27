<?php
// Bật báo lỗi để dễ sửa code
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Đặt tên session riêng cho học sinh và khởi động session
ini_set('session.name', 'HS_SESSION');
session_start();

// Kết nối cơ sở dữ liệu
require_once '../config.php';

// Đẩy về trang đăng nhập nếu chưa đăng nhập hoặc không phải học sinh
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'student') {
    header("Location: ../trangdangnhap.php");
    exit();
}

// Nhận ID lớp học từ URL (hỗ trợ cả biến 'id_lop_hoc' và 'id')
$id_lop_hoc = 0;
if (isset($_GET['id_lop_hoc'])) {
    $id_lop_hoc = intval($_GET['id_lop_hoc']);
} elseif (isset($_GET['id'])) {
    $id_lop_hoc = intval($_GET['id']);
}

// Lấy tên lớp học từ database để kiểm tra xem lớp có tồn tại không
$check_class = $conn->prepare("SELECT ten_lop FROM classes WHERE id = ?");
$check_class->bind_param("i", $id_lop_hoc);
$check_class->execute();
$res_class = $check_class->get_result();

// Báo lỗi và quay lại trang chủ nếu lớp không tồn tại
if ($res_class->num_rows === 0) {
    echo "<script>alert('Lớp học không hợp lệ hoặc đã bị xóa!'); window.location.href='index.php';</script>";
    exit;
}
$lop = $res_class->fetch_assoc();

// Xử lý lưu dữ liệu khi học sinh bấm "Gửi phản hồi"
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // Lấy số sao đánh giá và bình luận, mặc định là 0 nếu chưa chọn
    $sao_kien_thuc = isset($_POST['sao_kien_thuc']) ? intval($_POST['sao_kien_thuc']) : 0;
    $sao_su_pham   = isset($_POST['sao_su_pham']) ? intval($_POST['sao_su_pham']) : 0;
    $sao_ho_tro    = isset($_POST['sao_ho_tro']) ? intval($_POST['sao_ho_tro']) : 0;
    $binh_luan     = trim($_POST['binh_luan'] ?? '');

    // Kiểm tra xem đã chọn đủ 3 tiêu chí từ 1-5 sao chưa
    if ($sao_kien_thuc >= 1 && $sao_kien_thuc <= 5 && 
        $sao_su_pham >= 1 && $sao_su_pham <= 5 && 
        $sao_ho_tro >= 1 && $sao_ho_tro <= 5) {
        
        // Lưu đánh giá vào database hoàn toàn ẩn danh (không lưu ID học sinh)
        $stmt = $conn->prepare("INSERT INTO danh_gia (id_lop_hoc, sao_kien_thuc, sao_su_pham, sao_ho_tro, binh_luan) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("iiiis", $id_lop_hoc, $sao_kien_thuc, $sao_su_pham, $sao_ho_tro, $binh_luan);
        
        // Hiển thị thông báo thành công và chuyển hướng
        if ($stmt->execute()) {
            echo "<script>alert('Cảm ơn phản hồi của em! Đánh giá đã được gửi ẩn danh thành công.'); window.location.href='index.php';</script>";
            exit;
        } else {
            $loi = "Lỗi hệ thống khi lưu đánh giá.";
        }
    } else {
        $loi = "Vui lòng chọn đầy đủ số sao từ 1 đến 5 cho cả 3 tiêu chí!";
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đánh Giá Lớp Học | Góc Học Tập</title>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        /* Cài đặt chung để trang web nhất quán */
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Nunito', sans-serif; }
        body { background: #f8fafc; color: #1e293b; display: flex; justify-content: center; align-items: center; min-height: 100vh; padding: 20px; }
        
        /* Khung trắng chứa toàn bộ form ở giữa màn hình */
        .wrapper { 
            background: white; 
            max-width: 550px; 
            width: 100%; 
            padding: 35px; 
            border-radius: 20px; 
            box-shadow: 0 10px 25px rgba(0,0,0,0.05); 
            border: 1px solid #e2e8f0; 
        }
        
        /* Tiêu đề chính và phụ */
        h2 { text-align: center; color: #0f172a; font-weight: 800; margin-bottom: 8px; font-size: 24px; }
        .sub-title { text-align: center; color: #64748b; font-size: 14px; margin-bottom: 30px; font-weight: 600; }
        
        /* Hộp thông báo lỗi màu đỏ */
        .alert-error { 
            background: #fef2f2; 
            color: #b91c1c; 
            padding: 12px; 
            border-radius: 8px; 
            font-weight: 700; 
            font-size: 14px; 
            margin-bottom: 20px; 
            border: 1px solid #fca5a5; 
        }
        
        /* Khoảng cách của mỗi nhóm tiêu chí đánh giá */
        .tieu-chi { margin-bottom: 25px; }
        .tieu-chi label { font-weight: 700; display: block; margin-bottom: 8px; color: #334155; font-size: 15px; }
        
        /* Vùng chọn sao - đảo ngược mảng để hover sáng dần từ trái qua phải */
        .cham-sao { display: flex; flex-direction: row-reverse; justify-content: flex-end; }
        
        /* Ẩn các ô check input mặc định đi */
        .cham-sao input { display: none; } 
        
        /* Chèn icon ngôi sao màu xám làm mặc định */
        .cham-sao label { 
            cursor: pointer; 
            width: 36px; 
            height: 36px; 
            margin-right: 6px; 
            background-image: url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%23cbd5e1'><path d='M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z'/></svg>"); 
            background-repeat: no-repeat; 
            background-position: center; 
            background-size: contain; 
            transition: transform 0.1s ease; 
        }
        
        /* Phóng to sao một chút khi di chuột vào */
        .cham-sao label:hover { transform: scale(1.15); }
        
        /* Đổi màu ngôi sao thành cam khi được chọn hoặc khi hover chuột */
        .cham-sao input:checked ~ label, 
        .cham-sao label:hover, 
        .cham-sao label:hover ~ label { 
            background-image: url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%23ea580c'><path d='M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z'/></svg>"); 
        }
        
        /* Giao diện ô text để nhập bình luận */
        textarea { 
            width: 100%; 
            height: 110px; 
            border-radius: 10px; 
            border: 1px solid #cbd5e1; 
            padding: 12px; 
            resize: none; 
            box-sizing: border-box; 
            outline: none; 
            font-size: 14px; 
            transition: border-color 0.2s; 
        }
        textarea:focus { border-color: #ea580c; box-shadow: 0 0 0 3px rgba(234,88,12,0.1); }
        
        /* Vùng chứa 2 nút bấm ở cuối form */
        .actions { display: flex; gap: 15px; margin-top: 25px; }
        
        /* Nút "Quay lại" màu xám */
        .btn-back { 
            display: block; 
            text-align: center; 
            width: 35%; 
            padding: 12px; 
            background: #f1f5f9; 
            color: #475569; 
            text-decoration: none; 
            border-radius: 10px; 
            font-weight: 700; 
            font-size: 15px; 
        }
        .btn-back:hover { background: #e2e8f0; }
        
        /* Nút "Gửi phản hồi" màu cam */
        button { 
            width: 65%; 
            padding: 12px; 
            background: #ea580c; 
            color: white; 
            border: none; 
            border-radius: 10px; 
            font-size: 15px; 
            cursor: pointer; 
            font-weight: 700; 
        }
        button:hover { background: #c2410c; }
    </style>
</head>
<body>

<div class="wrapper">
    <h2>Đánh Giá Lớp Học</h2>
    <div class="sub-title">Lớp: <?php echo htmlspecialchars($lop['ten_lop']); ?> (Phản hồi hoàn toàn ẩn danh)</div>

    <?php if (isset($loi)): ?>
        <div class="alert-error"><?= $loi ?></div>
    <?php endif; ?>

    <form action="" method="POST">
        
        <div class="tieu-chi">
            <label>1. Chất lượng bài giảng & Kiến thức truyền tải:</label>
            <div class="cham-sao">
                <input type="radio" id="tc1-s5" name="sao_kien_thuc" value="5" required/><label for="tc1-s5" title="Xuất sắc"></label>
                <input type="radio" id="tc1-s4" name="sao_kien_thuc" value="4" /><label for="tc1-s4" title="Tốt"></label>
                <input type="radio" id="tc1-s3" name="sao_kien_thuc" value="3" /><label for="tc1-s3" title="Bình thường"></label>
                <input type="radio" id="tc1-s2" name="sao_kien_thuc" value="2" /><label for="tc1-s2" title="Yếu"></label>
                <input type="radio" id="tc1-s1" name="sao_kien_thuc" value="1" /><label for="tc1-s1" title="Kém"></label>
            </div>
        </div>

        <div class="tieu-chi">
            <label>2. Phương pháp sư phạm & Tốc độ giảng dạy:</label>
            <div class="cham-sao">
                <input type="radio" id="tc2-s5" name="sao_su_pham" value="5" required/><label for="tc2-s5" title="Xuất sắc"></label>
                <input type="radio" id="tc2-s4" name="sao_su_pham" value="4" /><label for="tc2-s4" title="Tốt"></label>
                <input type="radio" id="tc2-s3" name="sao_su_pham" value="3" /><label for="tc2-s3" title="Bình thường"></label>
                <input type="radio" id="tc2-s2" name="sao_su_pham" value="2" /><label for="tc2-s2" title="Yếu"></label>
                <input type="radio" id="tc2-s1" name="sao_su_pham" value="1" /><label for="tc2-s1" title="Kém"></label>
            </div>
        </div>

        <div class="tieu-chi">
            <label>3. Tương tác, hỗ trợ và giải đáp thắc mắc:</label>
            <div class="cham-sao">
                <input type="radio" id="tc3-s5" name="sao_ho_tro" value="5" required/><label for="tc3-s5" title="Xuất sắc"></label>
                <input type="radio" id="tc3-s4" name="sao_ho_tro" value="4" /><label for="tc3-s4" title="Tốt"></label>
                <input type="radio" id="tc3-s3" name="sao_ho_tro" value="3" /><label for="tc3-s3" title="Bình thường"></label>
                <input type="radio" id="tc3-s2" name="sao_ho_tro" value="2" /><label for="tc3-s2" title="Yếu"></label>
                <input type="radio" id="tc3-s1" name="sao_ho_tro" value="1" /><label for="tc3-s1" title="Kém"></label>
            </div>
        </div>

        <div class="tieu-chi">
            <label>Ý kiến đóng góp khác giúp cải thiện lớp học:</label>
            <textarea name="binh_luan" placeholder="Em hãy nhập ý kiến, mong muốn hoặc nhận xét chi tiết tại đây (Hệ thống bảo mật danh tính tuyệt đối)..."></textarea>
        </div>

        <div class="actions">
            <a href="index.php" class="btn-back">Quay lại</a>
            <button type="submit">Gửi phản hồi</button>
        </div>
    </form>
</div>

</body>
</html>