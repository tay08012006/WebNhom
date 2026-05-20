<?php
include '../config.php'; // Kết nối CSDL

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Kiểm tra quyền truy cập của Học sinh
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'student') {
    header("Location: ../trangdangnhap.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tham gia lớp học bằng mã | Góc Học Tập</title>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Nunito', sans-serif; }
        
        /* Cấu hình body để hỗ trợ căn giữa tuyệt đối */
        body { 
            background-color: #f4f7f6; 
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* NỘI DUNG CHÍNH: Biến thành một lòng Flexbox căn giữa */
        .main-content { 
            margin-left: 260px; /* Chừa khoảng trống cho Sidebar bên trái */
            padding: 20px; 
            flex: 1;
            display: flex;
            justify-content: center; /* Căn giữa theo chiều ngang */
            align-items: center;     /* Căn giữa theo chiều dọc */
            transition: margin-left 0.3s ease; 
            box-sizing: border-box;
        }
        
        /* Khi bấm nút ba gạch ẩn sidebar, vùng nội dung tự căn đều lại */
        .main-content.mo-rong { margin-left: 0px; }

        /* KHUNG HỘP NHẬP MÃ LỚP HỌC TRUNG TÂM */
        .join-box {
            background: #ffffff;
            width: 100%;
            max-width: 500px; /* Độ rộng tối đa của hộp */
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.06);
            border: 1px solid #eceff1;
            border-top: 5px solid #ff9800; /* Viền màu cam làm điểm nhấn */
        }
        
        .join-box p { 
            color: #78909c; 
            font-size: 14px; 
            margin-bottom: 25px; 
            font-weight: 600; 
            line-height: 1.6; 
        }
        
        .form-group { margin-bottom: 25px; }
        .form-group label { 
            display: block; 
            color: #263238; 
            font-weight: 700; 
            margin-bottom: 10px; 
            font-size: 15px; 
        }
        
        .form-control {
            width: 100%;
            padding: 14px 15px;
            font-size: 16px;
            border: 2px solid #cfd8dc;
            border-radius: 8px;
            outline: none;
            transition: 0.3s;
            text-transform: uppercase; /* Tự động viết hoa mã khi gõ */
            font-weight: 700;
            color: #0288d1;
            text-align: center; /* Căn giữa chữ đang gõ trong ô Input cho đẹp */
        }
        .form-control:focus { 
            border-color: #0288d1; 
            background: #fbfdfe; 
            box-shadow: 0 0 0 4px rgba(2, 136, 209, 0.1);
        }

        .btn-submit {
            width: 100%;
            padding: 14px;
            background: #0288d1;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            transition: 0.2s;
            box-shadow: 0 4px 12px rgba(2, 136, 209, 0.2);
        }
        .btn-submit:hover { 
            background: #01579b; 
            transform: translateY(-1px);
        }
        .btn-submit:active {
            transform: translateY(1px);
        }
    </style>
</head>
<body>

    <?php include 'thanh.php'; ?>

    <div class="main-content" id="mainContent">
        <div class="join-box">
            <label style="font-size: 22px; font-weight: 800; color: #263238; display: block; margin-bottom: 8px; text-align: center;">
                Tham gia lớp học
            </label>
            <p style="text-align: center;">Nhập mã lớp học do giáo viên cung cấp để tham gia vào phòng học trực tuyến, nhận tài liệu và làm bài tập nộp bài ngay.</p>
            
            <form action="xuly_thamgialop.php" method="POST">
                <div class="form-group">
                    <label for="ma_lop">Mã lớp học của em</label>
                    <input type="text" id="ma_lop" name="ma_lop" class="form-control" placeholder="VÍ DỤ: TOAN10" required autocomplete="off">
                </div>
                
                <button type="submit" class="btn-submit">Tham gia lớp học ngay ➔</button>
            </form>
            </div>
    </div>

</body>
</html>