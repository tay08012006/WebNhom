<?php
session_start();
// Kiểm tra quyền truy cập
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'student') {
    header("Location: ../trangdangnhap.php");
    exit();
}

// Danh sách tất cả lớp học bạn đang đăng ký (Mặc định hiện ra)
$ds_lop_hoc = [
    ['ten' => 'Toán Đại Số 10A1', 'gv' => 'Thầy Trần Văn Test', 'status' => 'Đang học', 'color' => '#0288d1'],
    ['ten' => 'Tin học Cơ bản', 'gv' => 'Cô Lê Thị Code', 'status' => 'Đang học', 'color' => '#4caf50'],
    ['ten' => 'Tiếng Anh Giao Tiếp', 'gv' => 'Mr. Smith', 'status' => 'Sắp bắt đầu', 'color' => '#ff9800'],
    ['ten' => 'Ngữ Văn 12', 'gv' => 'Cô Phan Thị Thơ', 'status' => 'Đang học', 'color' => '#e91e63'],
    ['ten' => 'Vật Lý Nguyên Tử', 'gv' => 'Thầy Dương Gia Tốc', 'status' => 'Đã kết thúc', 'color' => '#9e9e9e']
];
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Lớp học của tôi | Góc Học Tập</title>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { 
            margin: 0; 
            font-family: 'Nunito', sans-serif; 
            display: flex; 
            background-color: #f4f7f6; 
            min-height: 100vh; 
        }

        .sidebar { 
            width: 280px; 
            background-color: white; 
            padding: 30px 20px; 
            box-shadow: 2px 0 15px rgba(0,0,0,0.05); 
        }

        .sidebar h2 { 
            color: #0288d1; 
            text-align: center; 
            margin-bottom: 40px; 
        }

        .menu-item { 
            display: flex; 
            align-items: center; 
            padding: 15px; 
            margin-bottom: 10px; 
            border-radius: 12px; 
            text-decoration: none; 
            color: #455a64; 
            font-weight: 600; 
            transition: 0.3s; 
        }

        .menu-item:hover { 
            background-color: #e1f5fe; 
            color: #0288d1; 
        }

        .active { 
            background-color: #e1f5fe; 
            color: #0288d1; 
        }

        .main-content { 
            flex: 1; 
            padding: 40px; 
        }

        .header-section {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .grid-container { 
            display: grid; 
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); 
            gap: 20px; 
        }

        .class-card { 
            background: white; 
            padding: 25px; 
            border-radius: 20px; 
            box-shadow: 0 4px 15px rgba(0,0,0,0.05); 
            transition: 0.3s;
        }

        .class-card:hover {
            transform: translateY(-5px);
        }

        .status-badge {
            font-size: 11px;
            font-weight: 800;
            padding: 4px 10px;
            border-radius: 20px;
            text-transform: uppercase;
            display: inline-block;
            margin-bottom: 10px;
        }

        .class-name {
            margin: 0;
            color: #263238;
            font-size: 18px;
        }

        .teacher-name {
            color: #78909c;
            font-size: 14px;
            margin: 10px 0 20px 0;
        }

        .btn-action {
            display: block;
            text-align: center;
            padding: 10px;
            background: #f0f7ff;
            color: #0288d1;
            text-decoration: none;
            border-radius: 10px;
            font-weight: 700;
            transition: 0.3s;
        }

        .btn-action:hover {
            background: #0288d1;
            color: white;
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <h2>Góc Học Tập</h2>
        <a href="index.php" class="menu-item">🏠 Bảng điều khiển</a>
        <a href="lophoc.php" class="menu-item active">📚 Lớp học của tôi</a>
        <a href="baitap.php" class="menu-item">📝 Bài tập cần làm</a>
        <a href="ketqua.php" class="menu-item">📊 Kết quả học tập</a>
        <a href="profile.php" class="menu-item">👤 Hồ sơ cá nhân</a>
    </div>

    <div class="main-content">
        <div class="header-section">
            <h1>📚 Tất cả lớp học của tôi</h1>
            <p style="color: #78909c;">Em có <?php echo count($ds_lop_hoc); ?> lớp học trong danh sách</p>
        </div>

        <div class="grid-container">
            <?php foreach ($ds_lop_hoc as $lop): ?>
                <div class="class-card" style="border-top: 5px solid <?php echo $lop['color']; ?>;">
                    <span class="status-badge" style="background: <?php echo $lop['color']; ?>22; color: <?php echo $lop['color']; ?>;">
                        <?php echo $lop['status']; ?>
                    </span>
                    <h3 class="class-name"><?php echo $lop['ten']; ?></h3>
                    <p class="teacher-name">Giáo viên: <?php echo $lop['gv']; ?></p>
                    <a href="#" class="btn-action">Vào lớp học ngay →</a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>