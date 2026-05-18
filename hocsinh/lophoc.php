<?php
session_start();
include '../dp.php'; // 1. Gọi file kết nối CSDL ở thư mục gốc của bạn

// Kiểm tra quyền truy cập của Học sinh
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'student') {
    header("Location: ../trangdangnhap.php");
    exit();
}

// Lấy ID học sinh đang đăng nhập từ Session
if (!isset($_SESSION['user_id'])) {
    die("Lỗi: Vui lòng đăng nhập lại để hệ thống nhận diện ID!");
}
$id_hocsinh = $_SESSION['user_id'];

// 2. Truy vấn lấy danh sách các lớp học mà học sinh này thực tế đã nhập mã tham gia
$sql_get_class = "SELECT lop_hoc.*, users.ho_ten AS ten_gv 
                  FROM hocsinh_lop 
                  JOIN lop_hoc ON hocsinh_lop.id_lop = lop_hoc.id
                  JOIN users ON lop_hoc.id_giaovien = users.id
                  WHERE hocsinh_lop.id_hocsinh = ?";
$stmt = $conn->prepare($sql_get_class);
$stmt->bind_param("i", $id_hocsinh);
$stmt->execute();
$result_class = $stmt->get_result();

// Tự động đếm tổng số lớp học thực tế trong database của học sinh này
$class_count = $result_class->num_rows;

// Lấy tên file hiện tại để làm menu active tự động
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tất cả lớp học của tôi | Góc Học Tập</title>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Nunito', sans-serif; }
        body { background-color: #f4f7f6; display: flex; min-height: 100vh; overflow-x: hidden; position: relative; }

        /* NÚT 3 GẠCH ĐIỀU KHIỂN ĐỒNG BỘ */
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

        /* THANH SIDEBAR MỚI - CHỈ CHỮ, KHÔNG ICON */
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
        
        /* Trạng thái ẩn của Sidebar */
        .sidebar.hidden {
            transform: translateX(-260px);
        }

        .sidebar h2 { color: #0288d1; font-weight: 800; margin-bottom: 30px; text-align: center; font-size: 24px; }
        
        /* Menu item thô thuần chữ theo đúng ý bạn */
        .menu-item { display: block; padding: 12px 15px; text-decoration: none; color: #546e7a; font-weight: 600; border-radius: 8px; margin-bottom: 10px; transition: 0.3s; }
        .menu-item.active, .menu-item:hover { background: #e1f5fe; color: #0288d1; }
        
        .logout { color: #e53935; margin-top: 50px; border: 1px solid transparent; }
        .logout:hover { background: #ffebee; color: #c62828; border-color: #ffcdd2; }

        /* KHU VỰC NỘI DUNG CHÍNH (Tự động co giãn mượt mà) */
        .main-content { 
            flex: 1; 
            padding: 40px 40px 40px 300px; 
            transition: padding 0.3s ease;
        }
        
        /* Khi sidebar ẩn, nội dung tự động mở rộng ra sát lề */
        .main-content.expanded {
            padding-left: 80px;
        }

        .header-section { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; margin-top: 20px; }
        .header-section h1 { color: #263238; font-size: 28px; }
        .class-count { color: #78909c; font-size: 15px; font-weight: 600; }

        /* LƯỚI THẺ LỚP HỌC */
        .class-grid { 
            display: grid; 
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); 
            gap: 25px; 
        }
        
        /* Style các thẻ lớp học của bạn */
        .class-card { 
            background: white; 
            border-radius: 15px; 
            padding: 25px; 
            box-shadow: 0 5px 15px rgba(0,0,0,0.04); 
            border: 1px solid #eceff1;
            position: relative;
        }
        
        /* Các đường viền màu trên đầu thẻ */
        .card-toan { border-top: 4px solid #0288d1; }
        .card-tin { border-top: 4px solid #4caf50; }
        .card-anh { border-top: 4px solid #ff9800; }
        .card-van { border-top: 4px solid #e91e63; }
        .card-ly { border-top: 4px solid #9e9e9e; }

        .class-card h3 { color: #263238; margin-bottom: 8px; font-size: 20px; font-weight: 700; }
        .teacher-name { color: #78909c; font-size: 14px; margin-bottom: 20px; font-weight: 600; }
        
        /* Trạng thái lớp học */
        .status-badge { display: inline-block; padding: 4px 10px; border-radius: 6px; font-weight: 700; font-size: 11px; text-transform: uppercase; margin-bottom: 15px; }
        .status-active { background: #e8f5e9; color: #2e7d32; }
        .status-pending { background: #fff3e0; color: #ef6c00; }
        .status-ended { background: #eceff1; color: #607d8b; }
        
        .btn-enter-class { 
            display: block; 
            text-align: center; 
            padding: 12px; 
            background: #e1f5fe; 
            color: #0288d1; 
            border-radius: 8px; 
            text-decoration: none; 
            font-size: 14px; 
            font-weight: 700; 
            transition: 0.2s; 
        }
        .btn-enter-class:hover { background: #b3e5fc; }

        /* Style hộp thông báo kết quả nhập mã */
        .alert-box { padding: 12px 20px; border-radius: 8px; font-weight: 700; font-size: 15px; margin-bottom: 25px; }
        .alert-success { background: #e8f5e9; color: #2e7d32; border: 1px solid #c8e6c9; }
        .alert-error { background: #ffebee; color: #c62828; border: 1px solid #ffcdd2; }
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
            <h1>📚 Tất cả lớp học của tôi</h1>
            <div class="class-count">Em có <?php echo $class_count; ?> lớp học trong danh sách</div>
        </div>

        <?php if (isset($_GET['status'])): ?>
            <?php if ($_GET['status'] == 'success'): ?>
                <div class="alert-box alert-success">🎉 Chúc mừng! Em đã tham gia vào lớp học thành công.</div>
            <?php elseif ($_GET['status'] == 'already_joined'): ?>
                <div class="alert-box alert-error">⚠️ Chú ý: Em đã ở trong lớp học này từ trước rồi nhé!</div>
            <?php elseif ($_GET['status'] == 'invalid_code'): ?>
                <div class="alert-box alert-error">❌ Lỗi: Mã lớp học không tồn tại. Vui lòng kiểm tra lại!</div>
            <?php endif; ?>
        <?php endif; ?>

        <div class="class-grid">
            <?php 
            // Mảng chứa danh sách các class màu viền cũ của bạn
            $color_styles = ['card-toan', 'card-tin', 'card-anh', 'card-van', 'card-ly'];
            $i = 0;

            if ($class_count > 0): 
                // Sử dụng vòng lặp để nhân bản các ô lớp học thật ra ngoài màn hình
                while($row = $result_class->fetch_assoc()): 
                    // Luân phiên lấy màu viền trong mảng để các card không bị trùng màu sát nhau
                    $current_color = $color_styles[$i % count($color_styles)];
                    $i++;
            ?>
                <div class="class-card <?php echo $current_color; ?>">
                    <span class="status-badge status-active">Đang học</span>
                    <h3><?php echo htmlspecialchars($row['ten_lop']); ?></h3>
                    <div class="teacher-name">Giáo viên: <?php echo htmlspecialchars($row['ten_gv']); ?></div>
                    
                    <a href="phonghoc.php?id=<?php echo $row['id']; ?>" class="btn-enter-class">Vào lớp học ngay ➔</a>
                </div>
            <?php 
                endwhile; 
            else: 
            ?>
                <p style="color:#78909c; font-weight:600; padding: 30px 10px; text-align: center; grid-column: 1 / -1;">
                    Em chưa tham gia lớp học nào cả. Hãy nhấn vào nút "Tham gia lớp học bằng mã" ở Bảng điều khiển nhé!
                </p>
            <?php endif; ?>
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