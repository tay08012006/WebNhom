<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: ../trangdangnhap.php");
    exit; 
}

$ma_lop = $_GET['malop'] ?? '';

// Lấy thông tin lớp từ DATABASE
$stmt = $conn->prepare("
    SELECT * FROM classes 
    WHERE ma_lop = ? AND giaovien_id = ?
");
$stmt->bind_param("si", $ma_lop, $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Lớp học không tồn tại hoặc bạn không có quyền truy cập!");
}

$current_class = $result->fetch_assoc();
$tab = $_GET['tab'] ?? 'bang-tin';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($current_class['ten_lop']) ?> | Góc Học Tập</title>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Nunito', sans-serif; }
        body { background: #f4f7f9; color: #333; }
        .navbar { display: flex; justify-content: space-between; align-items: center; background: white; padding: 12px 30px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); position: sticky; top: 0; z-index: 100; }
        .btn-back { display: inline-flex; align-items: center; background: #f0f4f8; color: #555; text-decoration: none; padding: 8px 16px; border-radius: 20px; font-weight: 700; font-size: 14px; transition: all 0.2s; }
        .btn-back:hover { background: #e1e8ed; color: #333; }
        .container { max-width: 1000px; margin: 30px auto; padding: 0 20px; }
        
        /* === KHUNG TÊN LỚP - PHIÊN BẢN NỔI BẬT HƠN === */
        .class-banner { 
            background: linear-gradient(135deg, #0277bd 0%, #03a9f4 100%); 
            color: white; 
            padding: 28px 30px; 
            border-radius: 16px; 
            margin-bottom: 25px; 
            box-shadow: 0 8px 25px rgba(2, 119, 189, 0.35);
            position: relative;
            overflow: hidden;
        }
        .class-banner::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(255,255,255,0.15), transparent);
            pointer-events: none;
        }
        
        .class-banner h1 { 
            font-size: 28px; 
            font-weight: 800; 
            margin: 0; 
            text-shadow: 0 3px 6px rgba(0,0,0,0.2);
        }
        .class-banner p { 
            color: rgba(255,255,255,0.92); 
            font-size: 15.5px; 
            margin-top: 8px; 
            font-weight: 500;
        }
        
        /* Thanh điều hướng */
        .class-nav { 
            display: flex; 
            background: linear-gradient(135deg, #0277bd, #03a9f4); 
            border-radius: 12px; 
            padding: 0 8px; 
            margin-bottom: 25px; 
            box-shadow: 0 6px 16px rgba(2, 119, 189, 0.25); 
        }
        .class-nav a { 
            padding: 16px 28px; 
            color: rgba(255, 255, 255, 0.9); 
            text-decoration: none; 
            font-weight: 700; 
            font-size: 15px; 
            border-bottom: 4px solid transparent; 
            transition: all 0.2s; 
        }
        .class-nav a:hover { color: #ffffff; }
        .class-nav a.active { 
            color: #ffffff; 
            border-bottom: 4px solid #ffffff; 
        }
        
        .content { padding: 20px; }
    </style>
</head>
<body>
    <nav class="navbar">
        <div style="display: flex; align-items: center; gap: 20px;">
            <a href="index.php" class="btn-back">← Quay lại</a>
            <span style="font-weight: 800; color: #0277bd; font-size: 20px;">Góc Học Tập</span>
        </div>
        <?php include 'anhdaidien.php'; ?>
    </nav>
    
    <div class="container">
        <div class="class-banner">
            <div style="display: flex; align-items: center; gap: 14px;">
                <img src="<?= htmlspecialchars($gv_avatar ?? '') ?>" 
                     style="width: 52px; height: 52px; border-radius: 50%; object-fit: cover; 
                            border: 3px solid rgba(255,255,255,0.85); flex-shrink: 0; box-shadow: 0 4px 8px rgba(0,0,0,0.15);" 
                     alt="Avatar Giáo Viên">
                <div>
                    <h1><?= htmlspecialchars($current_class['ten_lop']) ?></h1>
                    <p>Học kỳ 1 - 2024 | Mã lớp: <b><?= htmlspecialchars($current_class['ma_lop']) ?></b></p>
                </div>
            </div>
        </div>
        
        <div class="class-nav">
            <a href="?malop=<?= $ma_lop ?>&tab=bang-tin" class="<?= $tab == 'bang-tin' ? 'active' : '' ?>">Bảng tin</a>
            <a href="?malop=<?= $ma_lop ?>&tab=bai-tap" class="<?= $tab == 'bai-tap' ? 'active' : '' ?>">Bài tập trên lớp</a>
            <a href="?malop=<?= $ma_lop ?>&tab=moi-nguoi" class="<?= $tab == 'moi-nguoi' ? 'active' : '' ?>">Mọi người</a>
        </div>
        
        <div class="content">
            <?php 
            if ($tab === 'bang-tin') {
                include 'taobangtin.php';
            } elseif ($tab === 'bai-tap') {
                include 'taobaitap.php';
            } elseif ($tab === 'moi-nguoi') {
                include 'taomoinguoi.php';
            }
            ?>
        </div>
    </div>
</body>
</html>