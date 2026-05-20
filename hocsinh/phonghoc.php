<?php
session_start();
include '../config.php'; // Kết nối CSDL từ file gốc

// 1. Kiểm tra quyền truy cập của Học sinh
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'student') {
    header("Location: ../trangdangnhap.php");
    exit();
}

// 2. Kiểm tra ID lớp học trên URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Lỗi: Không tìm thấy ID lớp học hợp pháp!");
}
$id_lop = intval($_GET['id']);
$id_hocsinh = $_SESSION['user_id'];

// 3. TỐI ƯU BẢO MẬT: Kiểm tra xem học sinh này có thực sự đã tham gia lớp này chưa
$sql_check = "SELECT id FROM class_enrollments WHERE user_id = ? AND class_id = ?";
$stmt_check = $conn->prepare($sql_check);
$stmt_check->bind_param("ii", $id_hocsinh, $id_lop);
$stmt_check->execute();
if ($stmt_check->get_result()->num_rows == 0) {
    die("Lỗi bảo mật: Em không có quyền truy cập vào phòng học này!");
}

// 4. LẤY THÔNG TIN LỚP HỌC & GIÁO VIÊN
$sql_lop = "SELECT c.*, u.hoten AS ten_gv 
            FROM classes c
            JOIN users u ON c.giaovien_id = u.id 
            WHERE c.id = ?";
$stmt_lop = $conn->prepare($sql_lop);
$stmt_lop->bind_param("i", $id_lop);
$stmt_lop->execute();
$info_lop = $stmt_lop->get_result()->fetch_assoc();

if (!$info_lop) {
    die("Lỗi: Không tìm thấy thông tin lớp học!");
}

// 5. LẤY DANH SÁCH BÀI TẬP VÀ TRẠNG THÁI NỘP BÀI CỦA HỌC SINH
$sql_baitap = "SELECT b.*, n.ngay_nop 
               FROM bai_tap b 
               LEFT JOIN nop_bai n ON b.id = n.bai_tap_id AND n.student_id = ?
               WHERE b.class_id = ? 
               ORDER BY b.han_nop ASC";
$stmt_baitap = $conn->prepare($sql_baitap);
$stmt_baitap->bind_param("ii", $id_hocsinh, $id_lop);
$stmt_baitap->execute();
$result_baitap = $stmt_baitap->get_result();

$current_page = 'lophoc.php';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Phòng học: <?php echo htmlspecialchars($info_lop['ten_lop']); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Nunito', sans-serif; }
        body { background-color: #f4f7f6; display: flex; min-height: 100vh; overflow-x: hidden; }

        .toggle-btn { position: fixed; top: 20px; left: 20px; font-size: 26px; background: #ffffff; color: #0288d1; border: 1px solid #eceff1; padding: 8px 15px; border-radius: 8px; cursor: pointer; z-index: 1000; box-shadow: 0 4px 10px rgba(0,0,0,0.05); }
        .sidebar { width: 260px; background: #ffffff; padding: 80px 25px 25px 25px; box-shadow: 2px 0 10px rgba(0,0,0,0.05); position: fixed; height: 100vh; z-index: 999; transition: 0.3s; }
        .sidebar.hidden { transform: translateX(-260px); }
        .sidebar h2 { color: #0288d1; font-weight: 800; margin-bottom: 30px; text-align: center; font-size: 24px; }
        .menu-item { display: block; padding: 12px 15px; text-decoration: none; color: #546e7a; font-weight: 600; border-radius: 8px; margin-bottom: 10px; }
        .menu-item.active, .menu-item:hover { background: #e1f5fe; color: #0288d1; }
        .logout { color: #e53935; margin-top: 50px; border: 1px solid transparent; }
        .logout:hover { background: #ffebee; color: #c62828; }

        .main-content { flex: 1; padding: 40px 40px 40px 300px; transition: 0.3s; }
        .main-content.expanded { padding-left: 80px; }

        .room-header { background: white; padding: 30px; border-radius: 15px; border: 1px solid #eceff1; margin-bottom: 30px; margin-top: 20px; }
        .room-header h1 { color: #0288d1; font-size: 28px; font-weight: 800; margin-bottom: 5px; }
        .room-header p { color: #78909c; font-weight: 600; font-size: 15px; }

        .section-title { color: #263238; font-size: 20px; font-weight: 700; margin-bottom: 15px; }

        .assignment-container { background: white; border-radius: 15px; border: 1px solid #eceff1; overflow: hidden; box-shadow: 0 5px 15px rgba(0,0,0,0.02); }
        .assignment-row { display: flex; justify-content: space-between; align-items: center; padding: 20px 30px; border-bottom: 1px solid #f1f5f7; transition: 0.2s; }
        .assignment-row:last-child { border-bottom: none; }
        .assignment-row:hover { background-color: #fafbfc; }

        .assignment-info h3 { color: #263238; font-size: 16px; font-weight: 700; margin-bottom: 6px; }
        .assignment-deadline { color: #78909c; font-size: 13px; font-weight: 600; }
        .deadline-highlight { color: #e53935; }

        .btn-status { display: inline-block; padding: 10px 20px; border-radius: 8px; font-size: 14px; font-weight: 700; text-decoration: none; text-align: center; min-width: 130px; }
        .status-yet { background: #fff3e0; color: #ef6c00; }
        .status-yet:hover { background: #ffe0b2; }
        .status-done { background: #e8f5e9; color: #2e7d32; pointer-events: none; }
        .status-expired { background: #ffebee; color: #c62828; pointer-events: none; }
    </style>
</head>
<body>

    <button class="toggle-btn" onclick="toggleSidebar()">☰</button>

    <div class="sidebar" id="mySidebar">
        <h2>Góc Học Tập</h2>
        <a href="index.php" class="menu-item">Bảng điều khiển</a>
        <a href="lophoc.php" class="menu-item active">Lớp học của tôi</a>
        <a href="profile.php" class="menu-item">Hồ sơ cá nhân</a>
        <a href="../logout.php" class="menu-item logout">Đăng xuất</a>
    </div>

    <div class="main-content" id="mainContent">
        <div class="room-header">
            <h1><?php echo htmlspecialchars($info_lop['ten_lop']); ?></h1>
            <p>Giáo viên phụ trách: <?php echo htmlspecialchars($info_lop['ten_gv']); ?></p>
        </div>

        <h2 class="section-title">Bài tập sắp đến hạn và cần làm</h2>

        <div class="assignment-container">
            <?php 
            if ($result_baitap->num_rows > 0):
                while($row = $result_baitap->fetch_assoc()):
                    $han_nop_timestamp = strtotime($row['han_nop']);
                    $current_timestamp = time();
                    $formatted_deadline = date("H:i d/m/Y", $han_nop_timestamp);
            ?>
                <div class="assignment-row">
                    <div class="assignment-info">
                        <h3><?php echo htmlspecialchars($row['tieu_de']); ?></h3>
                        <div class="assignment-deadline">
                            Hạn nộp: <span class="deadline-highlight"><?php echo $formatted_deadline; ?></span>
                        </div>
                    </div>
                    
                    <div class="assignment-action">
                        <?php 
                        if (!empty($row['ngay_nop'])) {
                            echo '<span class="btn-status status-done">Đã nộp bài</span>';
                        } elseif ($current_timestamp > $han_nop_timestamp) {
                            echo '<span class="btn-status status-expired">Quá hạn nộp</span>';
                        } else {
                            echo '<a href="nopbai.php?id_baitap='.$row['id'].'" class="btn-status status-yet">Nộp bài ngay</a>';
                        }
                        ?>
                    </div>
                </div>
            <?php 
                endwhile;
            else:
            ?>
                <p style="color:#78909c; font-weight:600; padding: 40px; text-align: center;">
                    Hiện tại lớp học này chưa có bài tập nào cần làm.
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