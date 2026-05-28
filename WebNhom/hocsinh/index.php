<?php
ini_set('session.name', 'HS_SESSION');
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
$sql_get_class = "SELECT c.*, u.hoten AS ten_gv, u.avatar AS avatar_gv 
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
        
        /* CSS CHO THẺ CLASS-CARD */
        .class-card { 
            background: white; 
            border-radius: 14px; 
            border: 1px solid #e1e8ed; 
            overflow: hidden; 
            box-shadow: 0 4px 12px rgba(0,0,0,0.03); 
            display: flex; 
            flex-direction: column; 
            transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1); 
        }
        .class-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 20px rgba(0,0,0,0.08);
            border-color: #0288d1;
        }
        
        /* HEADER MÀU XANH */
        .class-header { 
            background: linear-gradient(135deg, #0277bd 0%, #40c4ff 100%); 
            padding: 22px 20px; 
            position: relative; 
            color: white;
            box-shadow: 0 6px 20px rgba(2, 119, 189, 0.4); 
        }
        
        .class-header h3 { 
            font-size: 19px; 
            font-weight: 800; 
            margin-bottom: 4px; 
            color: white;
            text-shadow: 0 2px 5px rgba(0,0,0,0.25);
        }
        
        .class-header p { 
            font-size: 14px; 
            margin-bottom: 0; 
            font-weight: 600; 
            color: rgba(255, 255, 255, 0.9);
        }

        /* PHẦN BODY MÀU TRẮNG BÊN DƯỚI */
        .class-body {
            padding: 20px;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            background: white;
        }
        
        .badge-status { 
            display: inline-block; 
            padding: 5px 12px; 
            background: #f0fdf4; 
            color: #166534; 
            font-size: 13px; 
            font-weight: 700; 
            border-radius: 8px; 
            margin-bottom: 20px; 
            border: 1px solid #bbf7d0;
        }

        /* --- CSS CHO CỤM NÚT BẤM MỚI --- */
        .action-buttons {
            display: flex;
            gap: 10px; /* Khoảng cách giữa 2 nút */
            width: 100%;
        }
        
        .btn-evaluate {
            flex: 1; /* Nút chiếm nửa không gian */
            text-align: center;
            padding: 10px;
            background: #fff7ed; /* Màu nền cam nhạt */
            color: #ea580c; /* Chữ màu cam đậm */
            text-decoration: none;
            border-radius: 8px;
            font-weight: 700;
            font-size: 14px;
            border: 1px solid #ffedd5;
            transition: background 0.2s;
        }
        .btn-evaluate:hover { background: #ffedd5; }
        
        .btn-enter { 
            flex: 1; /* Nút chiếm nửa không gian */
            text-align: center; 
            padding: 10px; 
            background: #e0f2fe; 
            color: #0369a1; 
            text-decoration: none; 
            border-radius: 8px; 
            font-weight: 700; 
            font-size: 14px; 
            transition: background 0.2s;
        }
        .btn-enter:hover { background: #bae6fd; }
        /* ---------------------------------- */

        /* BÀI TẬP */
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
            transition: background 0.2s;
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
                <?php while ($row = $result_class->fetch_assoc()): 
                    
                    // --- XỬ LÝ LẤY ĐƯỜNG DẪN AVATAR CỦA GIÁO VIÊN ---
                    $gv_avatar = $row['avatar_gv'] ?? '';
                    $gv_name = $row['ten_gv'] ?? 'Giáo viên';
                    
                    if (!empty($gv_avatar) && !str_starts_with($gv_avatar, 'http')) {
                        $gv_avatar_src = '../uploads/' . $gv_avatar;
                    } elseif (!empty($gv_avatar)) {
                        $gv_avatar_src = $gv_avatar;
                    } else {
                        // Nếu GV chưa có ảnh, tự tạo ảnh chữ cái tên
                        $gv_avatar_src = 'https://ui-avatars.com/api/?name=' . urlencode($gv_name) . '&background=0284c7&color=fff&bold=true';
                    }
                    $fallback_av = 'https://ui-avatars.com/api/?name=' . urlencode($gv_name) . '&background=0284c7&color=fff&bold=true';
                    // ------------------------------------------------
                ?>
                    <div class="class-card">
                        
                        <div class="class-header">
                            <div style="display: flex; align-items: center; gap: 15px;">
                                <img src="<?php echo htmlspecialchars($gv_avatar_src); ?>" 
                                     alt="Avatar Giáo viên" 
                                     style="width: 48px; height: 48px; border-radius: 50%; object-fit: cover; border: 2px solid rgba(255,255,255,0.8); box-shadow: 0 2px 5px rgba(0,0,0,0.15); flex-shrink: 0;"
                                     onerror="this.src='<?php echo $fallback_av; ?>'">
                                
                                <div>
                                    <h3><?php echo htmlspecialchars($row['ten_lop']); ?></h3>
                                    <p>Giáo viên: <?php echo htmlspecialchars($gv_name); ?></p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="class-body">
                            <div>
                                <span class="badge-status">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: text-bottom; margin-right: 4px;"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                                    Đang học
                                </span>
                            </div>
                            
                            <div class="action-buttons">
                                <a href="danhgia.php?id=<?php echo $row['id']; ?>" class="btn-evaluate">Đánh giá</a>
                                <a href="phonghoc.php?id=<?php echo $row['id']; ?>" class="btn-enter">Vào lớp ➔</a>
                            </div>

                        </div>
                        
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div style="grid-column: 1/-1; background: white; padding: 40px; border-radius: 14px; text-align: center; border: 1px dashed #cbd5e1;">
                    <p style="color: #64748b; font-weight: 600; font-size: 15px; margin: 0;">
                        Em chưa tham gia lớp học nào cả. Hãy nhập mã do giáo viên cung cấp để vào lớp nhé!
                    </p>
                </div>
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
                <p style="padding: 15px 0; color: #64748b; font-weight: 600; text-align: center;">Tuyệt vời! Hiện tại em đã hoàn thành toàn bộ bài tập.</p>
            <?php endif; ?>
        </div>

    </div>
</body>
</html>