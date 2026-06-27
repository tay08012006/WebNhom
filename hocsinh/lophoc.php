<?php
// Khởi động session và cấu hình tên session cho học sinh
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

// Dừng chương trình nếu không tìm thấy ID người dùng
if (!isset($_SESSION['user_id'])) {
    die("Lỗi: Vui lòng đăng nhập lại để hệ thống nhận diện ID!");
}

$id_hocsinh = $_SESSION['user_id'];

// Tự động lấy lại họ tên từ Database nếu session bị mất
if (!isset($_SESSION['hoten']) || empty($_SESSION['hoten'])) {
    $sql_user = "SELECT hoten FROM users WHERE id = ?";
    $stmt_user = $conn->prepare($sql_user);
    $stmt_user->bind_param("i", $id_hocsinh);
    $stmt_user->execute();
    $res_user = $stmt_user->get_result();
    if($row_user = $res_user->fetch_assoc()){
        $_SESSION['hoten'] = $row_user['hoten'];
    }
}

// Hàm lấy chữ cái đầu của các từ để làm avatar (VD: "Lập trình web" -> "LT")
function tao_avatar_chu($ten_lop) {
    $tu = explode(' ', $ten_lop);
    $avatar = '';
    foreach ($tu as $t) {
        $avatar .= mb_substr($t, 0, 1, 'UTF-8');
    }
    return mb_strtoupper(mb_substr($avatar, 0, 2, 'UTF-8'), 'UTF-8');
}

// Truy vấn lấy danh sách các lớp học mà học sinh này đang tham gia
$sql_get_class = "SELECT c.*, u.hoten AS ten_gv 
                FROM class_enrollments ce
                JOIN classes c ON ce.class_id = c.id
                JOIN users u ON c.giaovien_id = u.id
                WHERE ce.user_id = ?";
$stmt = $conn->prepare($sql_get_class);
$stmt->bind_param("i", $id_hocsinh);
$stmt->execute();
$result_class = $stmt->get_result();

// Đếm tổng số lớp học
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
        /* Reset CSS mặc định và dùng font Nunito */
        * { 
            margin: 0; 
            padding: 0; 
            box-sizing: border-box; 
            font-family: 'Nunito', sans-serif; 
        }
        
        /* Màu nền tổng thể trang web */
        body {
            background-color: #f4f7f6;
        }
        
        /* Phần nội dung chính (chừa chỗ cho thanh menu trái) */
        .main-content {
            margin-left: 260px;
            padding: 40px;
            padding-top: 100px;
            transition: margin-left 0.3s ease;
        }
        
        /* Giao diện khi menu trái thu gọn */
        .main-content.mo-rong {
            margin-left: 0px;
        }

        /* Tiêu đề chính của trang */
        .page-title {
            color: #263238;
            font-size: 28px;
            font-weight: 800;
            margin-bottom: 5px;
        }

        /* Chữ phụ trợ dưới tiêu đề */
        .page-subtitle {
            color: #78909c;
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 30px;
        }

        /* Lưới hiển thị các thẻ lớp học */
        .grid-lop-hoc {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(310px, 1fr));
            gap: 25px;
        }

        /* Thiết kế khung bên ngoài của một thẻ lớp học */
        .the-lop-hoc {
            background: white;
            border: 1px solid #eceff1;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.02);
            transition: transform 0.2s, box-shadow 0.2s;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            min-height: 200px;
            overflow: hidden;
        }
        
        /* Hiệu ứng nổi lên khi di chuột vào thẻ */
        .the-lop-hoc:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.06);
        }

        /* Khung màu xanh gradient ở đầu thẻ */
        .card-header-blue {
            background: linear-gradient(135deg, #38bdf8, #0288d1);
            padding: 20px;
            display: flex;
            align-items: center;
            gap: 15px;
            color: white;
        }

        /* Vòng tròn ảnh đại diện hiển thị bằng chữ */
        .avatar-lop {
            width: 46px;
            height: 46px;
            background: rgba(255, 255, 255, 0.2);
            border: 2px solid rgba(255, 255, 255, 0.6);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: 15px;
            text-transform: uppercase;
            flex-shrink: 0;
        }

        /* Khung chứa tên lớp và thông tin năm học */
        .info-header {
            flex: 1;
            min-width: 0;
        }

        /* Tên lớp học rút gọn nếu quá dài */
        .the-lop-hoc h3 {
            color: white;
            font-size: 18px;
            font-weight: 800;
            margin: 0;
            line-height: 1.3;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* Hiển thị năm học phụ trợ */
        .hoc-ky-phu {
            font-size: 12px;
            color: rgba(255, 255, 255, 0.85);
            font-weight: 600;
            margin-top: 2px;
        }

        /* Thân thẻ chứa tên giáo viên và các nút chức năng */
        .card-body-content {
            padding: 20px;
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        /* Dòng hiển thị tên giáo viên */
        .info-line {
            color: #546e7a;
            font-size: 14px;
            font-weight: 600;
            margin: 4px 0;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        
        /* Bôi đậm phần tiêu đề "Giáo viên:" */
        .info-line strong {
            color: #263238;
            font-weight: 700;
        }

        /* Dưới cùng thẻ chứa mã lớp và nút đánh giá */
        .card-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-top: 15px;
            padding-top: 14px;
            border-top: 1px solid #eceff1;
            gap: 10px;
        }

        /* Viên thuốc chứa mã lớp học */
        .ma-lop-badge {
            background: #e0f2fe;
            color: #0369a1;
            padding: 5px 12px;
            border-radius: 20px;
            font-weight: 800;
            font-size: 12px;
            letter-spacing: 0.3px;
        }

        /* Nút đánh giá màu cam nhạt */
        .btn-danh-gia {
            background: #fff8f1;
            color: #ea580c;
            border: 1px solid #fcd9bc;
            padding: 7px 14px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 700;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            transition: all 0.2s;
        }
        
        /* Đổi màu khi đưa chuột vào nút đánh giá */
        .btn-danh-gia:hover {
            background: #ea580c;
            color: white;
            border-color: #ea580c;
        }

        /* Thông báo khi chưa tham gia lớp học nào */
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
                <?php while ($row = $result_class->fetch_assoc()): 
                    // Tự động cắt chữ cái để làm avatar
                    $avatar_chu = tao_avatar_chu($row['ten_lop']);
                ?>
                    <div class="the-lop-hoc">
                        
                        <div class="card-header-blue">
                            <div class="avatar-lop"><?php echo $avatar_chu; ?></div>
                            <div class="info-header">
                                <h3><?php echo htmlspecialchars($row['ten_lop']); ?></h3>
                                <div class="hoc-ky-phu">Học kỳ 2-2026</div>
                            </div>
                        </div>
                        
                        <div class="card-body-content">
                            <div>
                                <div class="info-line"> <strong>Giáo viên:</strong> <?php echo htmlspecialchars($row['ten_gv']); ?></div>
                            </div>
                            
                            <div class="card-footer">
                                <span class="ma-lop-badge"><?php echo htmlspecialchars($row['ma_lop']); ?></span>
                                
                                <a href="danhgia.php?id_lop_hoc=<?php echo $row['id']; ?>" class="btn-danh-gia">
                                    Đánh giá giáo viên
                                </a>
                            </div>
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