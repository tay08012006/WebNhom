<?php
session_start();
include '../config.php'; // Kết nối cơ sở dữ liệu chung

// Kiểm tra quyền truy cập của Học sinh
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'student') {
    header("Location: ../trangdangnhap.php");
    exit();
}

$id_hocsinh = $_SESSION['user_id'];
$ho_ten_hs = $_SESSION['hoten'] ?? 'Học sinh';

// 1. TRUY VẤN DANH SÁCH LỚP HỌC
$sql_get_class = "SELECT c.*, u.hoten AS ten_gv 
                FROM class_enrollments ce
                JOIN classes c ON ce.class_id = c.id
                JOIN users u ON c.giaovien_id = u.id
                WHERE ce.user_id = ?";
$stmt = $conn->prepare($sql_get_class);
$stmt->bind_param("i", $id_hocsinh);
$stmt->execute();
$result_class = $stmt->get_result();

// 2. TRUY VẤN DANH SÁCH BÀI TẬP CHƯA NỘP
$sql_baitap = "SELECT b.*, c.ten_lop 
            FROM bai_tap b
            JOIN classes c ON b.class_id = c.id
            JOIN class_enrollments ce ON c.id = ce.class_id
            WHERE ce.user_id = ? 
            AND b.id NOT IN (SELECT bai_tap_id FROM nop_bai WHERE student_id = ?)
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
        
        .main-content {
            flex: 1;
            margin-left: 280px;
            padding: 40px;
            padding-top: 100px;
            box-sizing: border-box;
            transition: margin-left 0.3s ease; 
        }
        
        .main-content.mo-rong {
            margin-left: 80px;
        }

        .section-title { 
            font-size: 18px; 
            font-weight: 800; 
            color: #0f172a; 
            margin-top: 30px; 
            margin-bottom: 20px; 
        }
        
        .class-grid { 
            display: grid; 
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); 
            gap: 24px; 
            margin-bottom: 40px; 
        }
        
        .class-card { 
            background: white; 
            border-radius: 16px; 
            border: 1px solid #e2e8f0; 
            padding: 24px; 
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02); 
            display: flex; 
            flex-direction: column; 
            justify-content: space-between; 
        }
        
        .class-card h3 { 
            font-size: 19px; 
            font-weight: 800; 
            color: #0369a1; 
            margin-bottom: 8px; 
        }
        
        .class-card p { 
            color: #64748b; 
            font-size: 14px; 
            margin-bottom: 15px; 
            font-weight: 600; 
        }
        
        .badge-status { 
            display: inline-block; 
            padding: 4px 10px; 
            background: #f0fdf4; 
            color: #166534; 
            font-size: 12px; 
            font-weight: 700; 
            border-radius: 6px; 
            margin-bottom: 20px; 
        }
        
        .btn-enter { 
            display: block; 
            text-align: center; 
            padding: 10px; 
            background: #e0f2fe; 
            color: #0369a1; 
            text-decoration: none; 
            border-radius: 8px; 
            font-weight: 700; 
            font-size: 14px; 
        }
        .btn-enter:hover { background: #bae6fd; }

        .homework-box { 
            background: white; 
            border-radius: 16px; 
            border: 1px solid #e2e8f0; 
            padding: 10px 24px; 
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02); 
        }
        
        .homework-item { 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            padding: 20px 0; 
            border-bottom: 1px solid #f1f5f9; 
        }
        .homework-item:last-child { border-bottom: none; }
        
        .hw-info h4 { 
            font-size: 16px; 
            font-weight: 700; 
            color: #1e293b; 
            margin-bottom: 6px; 
        }
        
        .hw-info p { 
            font-size: 13px; 
            color: #ef4444; 
            font-weight: 600; 
        }
        
        .btn-submit-hw { 
            padding: 8px 16px; 
            background: #f0fdf4; 
            color: #166534; 
            text-decoration: none; 
            border-radius: 8px; 
            font-size: 13px; 
            font-weight: 700; 
            border: 1px solid #bbf7d0; 
        }
        .btn-submit-hw:hover { background: #dcfce7; }
    </style>
</head>
<body>

    <?php include 'thanh.php'; ?>
    
    <div class="main-content">
        
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
            <h1 style="font-weight: 800; font-size: 28px;">Chào em, <?php echo htmlspecialchars($ho_ten_hs); ?>!</h1>
        </div>
        
        <p style="color: #64748b; font-weight: 600; margin-bottom: 30px;">Hôm nay em có <?php echo $tong_bai_tap; ?> bài tập sắp đến hạn. Cố lên nhé!</p>

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
                        <a href="phonghoc.php?id=<?php echo $row['id']; ?>" class="btn-enter">Vào lớp học ➔</a>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p style="grid-column: 1/-1; color: #64748b; font-weight: 600; padding: 10px 0;">
                    Em chưa tham gia lớp học nào cả. Hãy nhập mã để vào lớp nhé!
                </p>
            <?php endif; ?>
        </div>

        <div class="section-title">Bài tập sắp đến hạn</div>
        <div class="homework-box">
            <?php if ($result_baitap->num_rows > 0): ?>
                <?php while ($row_bt = $result_baitap->fetch_assoc()): ?>
                    <div class="homework-item">
                        <div class="hw-info">
                            <h4><?php echo htmlspecialchars($row_bt['tieu_de']); ?> (<?php echo htmlspecialchars($row_bt['ten_lop']); ?>)</h4>
                            <p>Hạn nộp: <?php echo date("H:i d/m/Y", strtotime($row_bt['han_nop'])); ?></p>
                        </div>
                        <a href="nopbai.php?id_baitap=<?php echo $row_bt['id']; ?>" class="btn-submit-hw">Nộp bài ngay</a>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p style="padding: 15px 0; color: #64748b; font-weight: 600;">Tuyệt vời! Hiện tại em đã hoàn thành toàn bộ bài tập.</p>
            <?php endif; ?>
        </div>

    </div>
</body>
</html>