<?php
session_start();
include '../dp.php'; // Kết nối cơ sở dữ liệu chung

// Kiểm tra quyền truy cập của Học sinh
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'student') {
    header("Location: ../trangdangnhap.php");
    exit();
}

$id_hocsinh = $_SESSION['user_id'];
$ho_ten_hs = $_SESSION['ho_ten'];

// 1. TRUY VẤN DANH SÁCH LỚP HỌC MÀ HỌC SINH NÀY ĐÃ THAM GIA
$sql_get_class = "SELECT l.*, u.ho_ten AS ten_gv 
                FROM hocsinh_lop hl
                JOIN lop_hoc l ON hl.id_lop = l.id
                JOIN users u ON l.id_giaovien = u.id
                WHERE hl.id_hocsinh = ?";
$stmt = $conn->prepare($sql_get_class);
$stmt->bind_param("i", $id_hocsinh);
$stmt->execute();
$result_class = $stmt->get_result();

// 2. TRUY VẤN DANH SÁCH BÀI TẬP CHƯA NỘP HOẶC SẮP ĐẾN HẠN CỦA CÁC LỚP ĐANG HỌC
$sql_baitap = "SELECT b.*, l.ten_lop 
            FROM baitap b
            JOIN lop_hoc l ON b.id_lop = l.id
            JOIN hocsinh_lop hl ON l.id = hl.id_lop
            WHERE hl.id_hocsinh = ? 
            AND b.id NOT IN (SELECT id_baitap FROM nop_bai_tap WHERE id_hocsinh = ?)
            ORDER BY b.han_nop ASC";
$stmt_bt = $conn->prepare($sql_baitap);
$stmt_bt->bind_param("ii", $id_hocsinh, $id_hocsinh);
$stmt_bt->execute();
$result_baitap = $stmt_bt->get_result();
$tong_bai_tap = $result_baitap->num_rows;
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bảng điều khiển Học sinh</title>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Nunito', sans-serif; }
        body { background-color: #f8fafc; display: flex; min-height: 100vh; color: #1e293b; }
        
        /* SIDEBAR (THANH MENU TRÁI) */
        .sidebar { width: 260px; background: white; padding: 30px 20px; border-right: 1px solid #e2e8f0; display: flex; flex-direction: column; }
        .sidebar h2 { color: #0284c7; font-weight: 800; font-size: 22px; margin-bottom: 35px; text-align: center; }
        .menu-links { flex: 1; }
        .menu-item { display: block; padding: 12px 18px; text-decoration: none; color: #64748b; font-weight: 600; border-radius: 10px; margin-bottom: 8px; font-size: 15px; }
        .menu-item.active, .menu-item:hover { background: #f0f9ff; color: #0284c7; font-weight: 700; }
        .logout { color: #ef4444; margin-top: auto; font-weight: 700; }
        .logout:hover { background: #fef2f2; }

        /* MAIN CONTENT AREA */
        .main-content { flex: 1; padding: 40px; }
        .welcome-header { display: flex; justify-content: space-between; align-items: start; margin-bottom: 35px; }
        .welcome-text h1 { font-size: 28px; font-weight: 800; color: #0f172a; margin-bottom: 6px; }
        .welcome-text p { color: #64748b; font-weight: 600; }
        
        /* NÚT THAM GIA LỚP HỌC */
        .btn-join { padding: 12px 24px; background: #0284c7; color: white; text-decoration: none; border-radius: 10px; font-weight: 700; font-size: 15px; box-shadow: 0 4px 12px rgba(2, 132, 199, 0.15); }
        .btn-join:hover { background: #0369a1; }

        /* KHỐI LỚP HỌC (GRID CARDS) */
        .section-title { font-size: 18px; font-weight: 800; color: #0f172a; margin-bottom: 20px; display: flex; align-items: center; gap: 8px; }
        .class-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 24px; margin-bottom: 40px; }
        .class-card { background: white; border-radius: 16px; border: 1px solid #e2e8f0; padding: 24px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02); display: flex; flex-direction: column; justify-content: space-between; }
        .class-card h3 { font-size: 19px; font-weight: 800; color: #0369a1; margin-bottom: 8px; }
        .class-card p { color: #64748b; font-size: 14px; margin-bottom: 15px; font-weight: 600; }
        .badge-status { display: inline-block; padding: 4px 10px; background: #f0fdf4; color: #166534; font-size: 12px; font-weight: 700; border-radius: 6px; margin-bottom: 20px; }
        
        .btn-enter { display: block; text-align: center; padding: 10px; background: #e0f2fe; color: #0369a1; text-decoration: none; border-radius: 8px; font-weight: 700; font-size: 14px; }
        .btn-enter:hover { background: #bae6fd; }

        /* DANH SÁCH BÀI TẬP */
        .homework-box { background: white; border-radius: 16px; border: 1px solid #e2e8f0; padding: 10px 24px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02); }
        .homework-item { display: flex; justify-content: space-between; align-items: center; padding: 20px 0; border-bottom: 1px solid #f1f5f9; }
        .homework-item:last-child { border-bottom: none; }
        .hw-info h4 { font-size: 16px; font-weight: 700; color: #1e293b; margin-bottom: 6px; }
        .hw-info p { font-size: 13px; color: #ef4444; font-weight: 600; display: flex; align-items: center; gap: 4px; }
        
        .btn-submit-hw { padding: 8px 16px; background: #f0fdf4; color: #166534; text-decoration: none; border-radius: 8px; font-size: 13px; font-weight: 700; border: 1px solid #bbf7d0; }
        .btn-submit-hw:hover { background: #dcfce7; }
    </style>
</head>
<body>

    <div class="sidebar">
        <h2>Góc Học Tập</h2>
        <div class="menu-links">
            <a href="index.php" class="menu-item active">Bảng điều khiển</a>
            <a href="lophoc.php" class="menu-item">Lớp học của tôi</a>
            <a href="profile.php" class="menu-item">Hồ sơ cá nhân</a>
        </div>
        <a href="../logout.php" class="menu-item logout">Đăng xuất</a>
    </div>

    <div class="main-content">
        <div class="welcome-header">
            <div class="welcome-text">
                <h1>Chào em, <?php echo htmlspecialchars($ho_ten_hs); ?>!</h1>
                <p>Hôm nay em có <?php echo $tong_bai_tap; ?> bài tập sắp đến hạn. Cố lên nhé!</p>
            </div>
            <a href="thamgialop.php" class="btn-join">+ Tham gia lớp học bằng mã</a>
        </div>

        <div class="section-title">Lớp học đang tham gia</div>
        <div class="class-grid">
            <?php if ($result_class->num_rows > 0): ?>
                <?php while ($row = $result_class->fetch_assoc()): ?>
                    <div class="class-card">
                        <div>
                            <h3><?php echo htmlspecialchars($row['ten_lop']); ?></h3>
                            <p>Giáo viên: <?php echo htmlspecialchars($row['ten_gv']); ?></p>
                            <span class="badge-status">Đang học</span>
                        </div>
                        <a href="phonghoc.php?id_lop=<?php echo $row['id']; ?>" class="btn-enter">Vào lớp học ➔</a>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p style="grid-column: 1/-1; color: #64748b; font-weight: 600;">Em chưa tham gia lớp học nào cả. Hãy nhập mã để vào lớp nhé!</p>
            <?php endif; ?>
        </div>

        <div class="section-title">Bài tập sắp đến hạn</div>
        <div class="homework-box">
            <?php if ($result_baitap->num_rows > 0): ?>
                <?php while ($row_bt = $result_baitap->fetch_assoc()): ?>
                    <div class="homework-item">
                        <div class="hw-info">
                            <h4><?php echo htmlspecialchars($row_bt['ten_bai_tap']); ?> (<?php echo htmlspecialchars($row_bt['ten_lop']); ?>)</h4>
                            <p>⏰ Hạn nộp: <?php echo date("H:i d/m/Y", strtotime($row_bt['han_nop'])); ?></p>
                        </div>
                        <a href="lambaitap.php?id_baitap=<?php echo $row_bt['id']; ?>" class="btn-submit-hw">Nộp bài ngay</a>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p style="padding: 15px 0; color: #64748b; font-weight: 600;">Tuyệt vời! Hiện tại em đã hoàn thành toàn bộ bài tập.</p>
            <?php endif; ?>
        </div>
    </div>

</body>
</html>