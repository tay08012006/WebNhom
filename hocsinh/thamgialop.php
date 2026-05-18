<?php
session_start();
// Kiểm tra quyền truy cập của Học sinh
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'student') {
    header("Location: ../trangdangnhap.php");
    exit();
}
// Lấy tên file hiện tại để phục vụ menu active tự động
$current_page = basename($_SERVER['PHP_SELF']);
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
        body { background-color: #f4f7f6; display: flex; min-height: 100vh; overflow-x: hidden; position: relative; }

        /* NÚT 3 GẠCH ĐIỀU KHIỂN SIDEBAR */
        .toggle-btn {
            position: fixed;
            top: 20px;
            left: 20px;
            font-size: 26px;
            background: #ffffff;
            color: #0288d1;
            border: 1px solid #eceff1;
            padding: 8px 15px;
            border-radius: 8px;
            cursor: pointer;
            z-index: 1000;
            box-shadow: 0 4px 10px rgba(0,0,0,0.05);
            transition: 0.3s;
        }
        .toggle-btn:hover { background: #e1f5fe; }

        /* THANH SIDEBAR THUẦN CHỮ */
        .sidebar { 
            width: 260px; 
            background: #ffffff; 
            padding: 80px 25px 25px 25px; 
            box-shadow: 2px 0 10px rgba(0,0,0,0.05); 
            transition: transform 0.3s ease;
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            z-index: 999;
        }
        .sidebar.hidden { transform: translateX(-260px); }
        .sidebar h2 { color: #0288d1; font-weight: 800; margin-bottom: 30px; text-align: center; font-size: 24px; }
        .menu-item { display: block; padding: 12px 15px; text-decoration: none; color: #546e7a; font-weight: 600; border-radius: 8px; margin-bottom: 10px; transition: 0.3s; }
        .menu-item.active, .menu-item:hover { background: #e1f5fe; color: #0288d1; }
        .logout { color: #e53935; margin-top: 50px; border: 1px solid transparent; }
        .logout:hover { background: #ffebee; color: #c62828; border-color: #ffcdd2; }

        /* KHU VỰC NỘI DUNG CHÍNH */
        .main-content { flex: 1; padding: 40px 40px 40px 300px; transition: padding 0.3s ease; }
        .main-content.expanded { padding-left: 80px; }

        .header-section { margin-bottom: 30px; margin-top: 20px; }
        .header-section h1 { color: #263238; font-size: 28px; }

        /* GIAO DIỆN HỘP NHẬP MÃ LỚP HỌC */
        .join-box {
            background: #ffffff;
            max-width: 500px;
            padding: 35px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.04);
            border: 1px solid #eceff1;
            border-top: 4px solid #ff9800; /* Viền màu cam làm điểm nhấn */
        }
        .join-box p { color: #78909c; font-size: 14px; margin-bottom: 20px; font-weight: 600; line-height: 1.6; }
        
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; color: #263238; font-weight: 700; margin-bottom: 8px; font-size: 15px; }
        
        .form-control {
            width: 100%;
            padding: 12px 15px;
            font-size: 16px;
            border: 2px solid #cfd8dc;
            border-radius: 8px;
            outline: none;
            transition: 0.3s;
            text-transform: uppercase; /* Tự động viết hoa mã khi gõ */
            font-weight: 700;
            color: #0288d1;
        }
        .form-control:focus { border-color: #0288d1; background: #fbfdfe; }

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
        }
        .btn-submit:hover { background: #01579b; }
        
        .btn-back {
            display: block;
            text-align: center;
            margin-top: 15px;
            color: #546e7a;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
        }
        .btn-back:hover { color: #0288d1; }
    </style>
</head>
<body>

    <button class="toggle-btn" onclick="toggleSidebar()">☰</button>

    <div class="sidebar" id="mySidebar">
        <h2>Góc Học Tập</h2>
        <a href="index.php" class="menu-item <?php echo ($current_page == 'index.php') ? 'active' : ''; ?>">Bảng điều khiển</a>
        <a href="lophoc.php" class="menu-item <?php echo ($current_page == 'lophoc.php') ? 'active' : ''; ?>">Lớp học của tôi</a>
        <a href="profile.php" class="menu-item <?php echo ($current_page == 'profile.php') ? 'active' : ''; ?>">Hồ sơ cá nhân</a>

        <a href="../logout.php" class="menu-item logout">Đăng xuất</a>
    </div>

    <div class="main-content" id="mainContent">
        <div class="header-section">
            <h1>Thành viên mới</h1>
        </div>

        <div class="join-box">
            <label style="font-size: 20px; font-weight: 800; color: #263238; display: block; margin-bottom: 5px;">Tham gia lớp học</label>
            <p>Nhập mã lớp học do giáo viên cung cấp để tham gia vào phòng học trực tuyến, nhận tài liệu và làm bài tập nộp bài ngay.</p>
            
            <form action="xuly_thamgialop.php" method="POST">
                <div class="form-group">
                    <label for="ma_lop">Mã lớp học</label>
                    <input type="text" id="ma_lop" name="ma_lop" class="form-control" placeholder="VÍ DỤ: TOAN10" required autocomplete="off">
                </div>
                
                <button type="submit" class="btn-submit">Tham gia lớp học ngay ➔</button>
            </form>
            
            <a href="index.php" class="btn-back">Quay lại Bảng điều khiển</a>
        </div>
    </div>

    <script>
        function toggleSidebar() {
            var sidebar = document.getElementById("mySidebar");
            var mainContent = document.getElementById("mainContent");
            
            sidebar.classList.toggle("hidden");
            mainContent.classList.toggle("expanded");
        }
    </script>

</body>
</html>