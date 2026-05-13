<?php
session_start();
// Kiểm tra: Nếu chưa đăng nhập hoặc không phải Học sinh (role = student) thì đẩy ra trang đăng nhập
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
    <title>Bảng điều khiển Học sinh | Góc Học Tập</title>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Nunito', sans-serif; }
        body { background-color: #f4f7f6; display: flex; min-height: 100vh; }

        /* Thanh menu bên trái */
        .sidebar { width: 260px; background: #ffffff; padding: 25px; box-shadow: 2px 0 10px rgba(0,0,0,0.05); }
        .sidebar h2 { color: #0288d1; font-weight: 800; margin-bottom: 30px; text-align: center; font-size: 24px; }
        .menu-item { display: block; padding: 12px 15px; text-decoration: none; color: #546e7a; font-weight: 600; border-radius: 8px; margin-bottom: 10px; transition: 0.3s; }
        .menu-item.active, .menu-item:hover { background: #e1f5fe; color: #0288d1; }
        .logout { color: #e53935; margin-top: 50px; }
        .logout:hover { background: #ffebee; color: #c62828; }

        /* Khu vực nội dung chính */
        .main-content { flex: 1; padding: 40px; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 40px; }
        .header h1 { color: #263238; font-size: 28px; }
        
        .btn-join { background: #ff9800; color: white; padding: 12px 24px; text-decoration: none; border-radius: 8px; font-weight: 700; transition: 0.3s; box-shadow: 0 4px 10px rgba(255, 152, 0, 0.3); }
        .btn-join:hover { background: #f57c00; transform: translateY(-2px); }

        /* Lưới hiển thị các lớp học */
        .class-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 25px; margin-bottom: 40px; }
        
        /* Thẻ hiển thị từng lớp */
        .class-card { background: white; border-radius: 15px; padding: 25px; box-shadow: 0 5px 15px rgba(0,0,0,0.05); border: 1px solid #eceff1; }
        .class-card h3 { color: #0277bd; margin-bottom: 5px; font-size: 20px; }
        .teacher-name { color: #78909c; font-size: 14px; margin-bottom: 15px; font-weight: 600; }
        .status-badge { display: inline-block; background: #e8f5e9; color: #2e7d32; padding: 5px 12px; border-radius: 6px; font-weight: 700; font-size: 13px; margin-bottom: 20px; }
        
        /* Các nút hành động trong thẻ lớp */
        .card-actions { display: flex; gap: 10px; }
        .btn-action { flex: 1; text-align: center; padding: 10px; border-radius: 6px; text-decoration: none; font-size: 14px; font-weight: 700; transition: 0.2s; }
        .btn-enter { background: #e1f5fe; color: #0288d1; }
        .btn-enter:hover { background: #b3e5fc; }

        /* Khu vực bài tập sắp hạn */
        .homework-section { background: white; padding: 25px; border-radius: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.05); border: 1px solid #eceff1; }
        .homework-item { display: flex; justify-content: space-between; align-items: center; padding: 15px 0; border-bottom: 1px solid #eeeeee; }
        .homework-item:last-child { border-bottom: none; padding-bottom: 0; }
        .hw-title { font-weight: 700; color: #37474f; }
        .hw-deadline { color: #e53935; font-size: 14px; font-weight: 600; }
        .btn-submit-hw { background: #e8f5e9; color: #2e7d32; padding: 6px 15px; border-radius: 6px; text-decoration: none; font-size: 13px; font-weight: 700; transition: 0.2s; }
        .btn-submit-hw:hover { background: #c8e6c9; }
    </style>
</head>
<body>

    <div class="sidebar">
        <h2>Góc Học Tập</h2>
        <a href="index.php" class="menu-item active">🏠 Bảng điều khiển</a>
        <a href="lophoc.php" class="menu-item">📚 Lớp học của tôi</a>
        <a href="#" class="menu-item">📝 Bài tập cần làm</a>
        <a href="#" class="menu-item">📊 Kết quả học tập</a>
        
        <a href="profile.php" class="menu-item">👤 Hồ sơ cá nhân</a>

        <a href="../trangdangnhap.php" class="menu-item logout">🚪 Đăng xuất</a>
    </div>

    <div class="main-content">
        <div class="header">
            <div>
                <h1>Chào em, <?php echo $_SESSION['ho_ten']; ?>!</h1>
                <p style="color: #78909c; margin-top: 5px;">Hôm nay em có 2 bài tập sắp đến hạn. Cố lên nhé!</p>
            </div>
            <a href="#" class="btn-join">+ Tham gia lớp học bằng mã</a>
        </div>

        <h2 style="color: #37474f; margin-bottom: 20px; font-size: 20px;">Lớp học đang tham gia</h2>

        <div class="class-grid">
            <div class="class-card">
                <h3>Toán Đại Số 10A1</h3>
                <div class="teacher-name">Giáo viên: Thầy Trần Văn Test</div>
                <div class="status-badge">Đang học</div>
                <div class="card-actions">
                    <a href="#" class="btn-action btn-enter">Vào lớp học ➔</a>
                </div>
            </div>

            <div class="class-card">
                <h3>Tin học Cơ bản</h3>
                <div class="teacher-name">Giáo viên: Cô Lê Thị Code</div>
                <div class="status-badge">Đang học</div>
                <div class="card-actions">
                    <a href="#" class="btn-action btn-enter">Vào lớp học ➔</a>
                </div>
            </div>
        </div>

        <h2 style="color: #37474f; margin-bottom: 20px; font-size: 20px;">Bài tập sắp đến hạn</h2>
        <div class="homework-section">
            <div class="homework-item">
                <div>
                    <div class="hw-title">Giải phương trình bậc 2 (Toán 10A1)</div>
                    <div class="hw-deadline">⏰ Hạn nộp: 23:59 Hôm nay</div>
                </div>
                <a href="nopbai.php" class="btn-submit-hw">Nộp bài ngay</a>
            </div>
            <div class="homework-item">
                <div>
                    <div class="hw-title">Tạo trang HTML đơn giản (Tin học)</div>
                    <div class="hw-deadline">⏰ Hạn nộp: Ngày mai</div>
                </div>
                <a href="nopbai.php" class="btn-submit-hw">Nộp bài ngay</a>
            </div>
        </div>
    </div>

</body>
</html>