<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: ../trangdangnhap.php");
    exit;
}

$ma_lop = $_GET['malop'] ?? '';
$tab = $_GET['tab'] ?? 'bang-tin'; // Mặc định là tab bảng tin

// Tìm thông tin lớp
$current_class = null;
if (isset($_SESSION['classes'])) {
    foreach ($_SESSION['classes'] as $class) {
        if ($class['ma_lop'] === $ma_lop) {
            $current_class = $class;
            break;
        }
    }
}

if (!$current_class) { echo "Lớp học không tồn tại"; exit; }
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($current_class['ten_mon']) ?> | Góc Học Tập</title>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style> 
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Nunito', sans-serif; }
        body { background: #f4f7f9; color: #455a64; }
        .navbar { background: #fff; padding: 15px 40px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); display: flex; justify-content: space-between; align-items: center; position: sticky; top: 0; z-index: 100; }
        .btn-back { text-decoration: none; color: #546e7a; font-weight: 700; background: #eceff1; padding: 8px 15px; border-radius: 8px; }
        .container { max-width: 1000px; margin: 30px auto; padding: 0 20px; }
        .class-banner { background: linear-gradient(135deg, #0288d1 0%, #0277bd 100%); border-radius: 20px; padding: 40px; color: white; margin-bottom: 30px; }
        .class-nav { display: flex; gap: 30px; border-bottom: 2px solid #cfd8dc; margin-bottom: 30px; }
        .class-nav a { text-decoration: none; color: #78909c; font-weight: 800; padding-bottom: 10px; position: relative; }
        .class-nav a.active { color: #0288d1; border-bottom: 4px solid #0288d1; }
        
        /* Style chung cho các file con */
        .content-card { background: white; padding: 30px; border-radius: 15px; border: 1px solid #edf2f5; box-shadow: 0 4px 10px rgba(0,0,0,0.02); }
        .btn-action { background: #0288d1; color: white; border: none; padding: 10px 20px; border-radius: 8px; font-weight: 700; cursor: pointer; }
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
            <h1><?= htmlspecialchars($current_class['ten_mon']) ?></h1>
            <p><?= htmlspecialchars($current_class['hoc_ky']) ?> | Mã lớp: <b><?= $ma_lop ?></b></p>
        </div>

        <div class="class-nav">
            <a href="?malop=<?= $ma_lop ?>&tab=bang-tin" class="<?= $tab == 'bang-tin' ? 'active' : '' ?>">Bảng tin</a>
            <a href="?malop=<?= $ma_lop ?>&tab=bai-tap" class="<?= $tab == 'bai-tap' ? 'active' : '' ?>">Bài tập trên lớp</a>
            <a href="?malop=<?= $ma_lop ?>&tab=moi-nguoi" class="<?= $tab == 'moi-nguoi' ? 'active' : '' ?>">Mọi người</a>
        </div>

        <div class="main-content">
            <?php 
                if ($tab == 'bang-tin') include 'taobangtin.php';
                elseif ($tab == 'bai-tap') include 'taobaitap.php';
                elseif ($tab == 'moi-nguoi') include 'taomoinguoi.php';
            ?>
        </div>
    </div>

</body>
</html>