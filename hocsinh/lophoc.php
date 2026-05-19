<?php
// 1. KHỞI CHẠY SESSION VÀ KẾT NỐI DATABASE
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include '../dp.php'; // Đường dẫn kết nối CSDL của bạn

// 2. KIỂM TRA QUYỀN TRUY CẬP HỌC SINH
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'student') {
    header("Location: ../trangdangnhap.php");
    exit();
}

if (!isset($_SESSION['user_id'])) {
    die("Lỗi: Vui lòng đăng nhập lại để hệ thống nhận diện ID!");
}
$id_hocsinh = $_SESSION['user_id'];

// Tự động kiểm tra nạp lại họ tên từ Database nếu Session bị trống
if (!isset($_SESSION['ho_ten']) || empty($_SESSION['ho_ten'])) {
    $sql_user = "SELECT ho_ten FROM users WHERE id = ?";
    $stmt_user = $conn->prepare($sql_user);
    $stmt_user->bind_param("i", $id_hocsinh);
    $stmt_user->execute();
    $res_user = $stmt_user->get_result();
    if($row_user = $res_user->fetch_assoc()){
        $_SESSION['ho_ten'] = $row_user['ho_ten'];
    }
}

// 3. TRUY VẤN LẤY DANH SÁCH LỚP HỌC THỰC TẾ
$sql_get_class = "SELECT lop_hoc.*, users.ho_ten AS ten_gv 
                FROM hocsinh_lop 
                JOIN lop_hoc ON hocsinh_lop.id_lop = lop_hoc.id
                JOIN users ON lop_hoc.id_giaovien = users.id
                WHERE hocsinh_lop.id_hocsinh = ?";
$stmt = $conn->prepare($sql_get_class);
$stmt->bind_param("i", $id_hocsinh);
$stmt->execute();
$result_class = $stmt->get_result();

$class_count = $result_class->num_rows;
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lớp học của tôi | Góc Học Tập</title>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        /* Áp dụng font Nunito cho toàn bộ trang */
        * { 
            margin: 0; 
            padding: 0; 
            box-sizing: border-box; 
            font-family: 'Nunito', sans-serif; 
        }
        
        body {
            background-color: #f4f7f6;
        }
        
        /* KHU VỰC NỘI DUNG CHÍNH */
        .main-content {
            margin-left: 260px; /* Nhường chỗ cho sidebar */
            padding: 40px;
            padding-top: 100px; /* Đẩy nội dung xuống dưới thanh Header ngang an toàn */
            transition: margin-left 0.3s ease;
        }
        
        /* Khi sidebar thu gọn */
        .main-content.mo-rong {
            margin-left: 0px;
        }

        .page-title {
            color: #263238;
            font-size: 28px;
            font-weight: 800;
            margin-bottom: 5px;
        }

        .page-subtitle {
            color: #78909c;
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 30px;
        }

        /* ĐỊNH DẠNG LƯỚI CHỨA CÁC Ô LỚP HỌC */
        .grid-lop-hoc {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 25px;
        }

        /* CÁC THẺ Ô LỚP HỌC ĐỀU NHAU TĂM TẮP */
        .the-lop-hoc {
            background: white;
            border: 1px solid #eceff1;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.02);
            transition: transform 0.2s, box-shadow 0.2s;
            display: flex;
            flex-direction: column;
            justify-content: space-between; /* Giúp các thành phần giãn đều, chiều cao bằng nhau */
            min-height: 160px; /* Đảm bảo các ô có độ cao tối thiểu bằng nhau */
        }
        
        .the-lop-hoc:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.06);
        }

        .the-lop-hoc h3 {
            color: #0288d1;
            font-size: 20px;
            font-weight: 800;
            margin-top: 0;
            margin-bottom: 12px;
            line-height: 1.3;
        }

        .info-line {
            color: #546e7a;
            font-size: 14px;
            font-weight: 600;
            margin: 4px 0;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        
        .info-line strong {
            color: #263238;
            font-weight: 700;
        }

        /* Hộp thông báo trống nếu chưa có lớp học */
        .thong-bao-trong {
            background: white;
            padding: 40px;
            border-radius: 15px;
            border: 1px solid #eceff1;
            color: #78909c;
            font-weight: 600;
            text-align: center;
            max-width: 600px;
            line-height: 1.6;
            box-shadow: 0 5px 15px rgba(0,0,0,0.02);
        }
    </style>
</head>
<body>

    <?php include 'thanh.php'; ?>

    <div class="main-content">
        <h1 class="page-title">Tất cả lớp học của tôi</h1>
        <p class="page-subtitle">Em có <?php echo $class_count; ?> lớp học trong danh sách</p>

        <?php if ($class_count > 0): ?>
            <div class="grid-lop-hoc">
                <?php while ($row = $result_class->fetch_assoc()): ?>
                    <div class="the-lop-hoc">
                        <div>
                            <h3><?php echo htmlspecialchars($row['ten_lop']); ?></h3>
                            <div class="info-line">📌 <strong>Mã lớp:</strong> <?php echo htmlspecialchars($row['ma_lop']); ?></div>
                            <div class="info-line">👨‍🏫 <strong>Giáo viên:</strong> <?php echo htmlspecialchars($row['ten_gv']); ?></div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="thong-bao-trong">
                Em chưa tham gia lớp học nào cả.<br>Hãy nhấn vào nút <strong>"+ Tham gia lớp học bằng mã"</strong> ở thanh trên cùng nhé!
            </div>
        <?php endif; ?>
    </div>

</body>
</html>