<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

require_once '../config.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: ../trangdangnhap.php?error=Bạn cần đăng nhập!");
    exit;
}

// Lấy danh sách lớp học của giáo viên từ DATABASE
$stmt = $conn->prepare("SELECT * FROM classes WHERE giaovien_id = ? ORDER BY ngay_tao DESC");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$classes = $stmt->get_result();

// Xử lý đăng xuất
if (isset($_GET['action']) && $_GET['action'] == 'logout') {
    session_destroy();
    header("Location: ../trangdangnhap.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bảng Điều Khiển Giáo Viên | Góc Học Tập</title>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Nunito', sans-serif; }
        body { background: #f4f7f9; min-height: 100vh; color: #455a64; }
        .navbar { background: #ffffff; padding: 15px 40px; box-shadow: 0 4px 15px rgba(0,0,0,0.03); display: flex; justify-content: space-between; align-items: center; position: sticky; top: 0; z-index: 100; }
        .nav-brand { font-size: 24px; font-weight: 800; color: #0277bd; text-decoration: none; text-transform: uppercase; letter-spacing: 1px; }
        .nav-actions { display: flex; align-items: center; gap: 20px; }
        .btn-create { background: #0288d1; color: white; border: none; padding: 10px 20px; border-radius: 12px; font-weight: 700; font-size: 15px; cursor: pointer; transition: 0.3s; box-shadow: 0 4px 10px rgba(2, 136, 209, 0.2); }
        .btn-create:hover { background: #0277bd; transform: translateY(-2px); }
        .user-menu { display: flex; align-items: center; gap: 10px; }
        .welcome-text { font-weight: 700; color: #37474f; font-size: 15px; }
        .btn-logout { background: #ffebee; color: #d32f2f; text-decoration: none; padding: 8px 15px; border-radius: 10px; font-weight: 700; font-size: 14px; transition: 0.3s; border: 1px solid #ffcdd2; }
        .btn-logout:hover { background: #ef9a9a; color: white; }
        .container { max-width: 1200px; margin: 40px auto; padding: 0 20px; }
        .page-header { margin-bottom: 30px; font-size: 22px; font-weight: 800; color: #37474f; }
        .class-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 30px; }
        .class-card { background: #ffffff; border-radius: 20px; overflow: hidden; box-shadow: 0 10px 25px rgba(0,0,0,0.04); transition: 0.3s; display: flex; flex-direction: column; border: 1px solid #edf2f5; }
        .class-card:hover { transform: translateY(-5px); box-shadow: 0 15px 35px rgba(0,0,0,0.08); }
        .card-cover { background: linear-gradient(135deg, #e0f7fa 0%, #bbdefb 100%); padding: 25px; height: 130px; }
        .card-cover h3 { color: #0277bd; font-size: 22px; font-weight: 800; margin-bottom: 5px; }
        .card-cover p { color: #546e7a; font-size: 14px; font-weight: 600; }
        .card-body { padding: 25px; flex-grow: 1; }
        .code-box-container { display: flex; align-items: center; gap: 10px; margin-top: 5px; }
        .code-box { display: inline-block; background: #f1f8e9; color: #2e7d32; padding: 8px 15px; border-radius: 8px; font-weight: 800; font-size: 16px; letter-spacing: 2px; }
        
        /* Cập nhật CSS cho nút sao chép */
        .btn-copy { 
            background: #ffffff; 
            border: 1px solid #cfd8dc; 
            cursor: pointer; 
            color: #546e7a; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            padding: 8px; 
            border-radius: 8px; 
            transition: all 0.2s ease;
            box-shadow: 0 2px 5px rgba(0,0,0,0.02);
        }
        .btn-copy:hover { 
            background: #0288d1; 
            color: white; 
            border-color: #0288d1;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(2, 136, 209, 0.2);
        }
        .btn-copy:active {
            transform: translateY(0);
        }

        .card-footer { padding: 15px 25px; border-top: 1px solid #f4f7f9; display: flex; justify-content: space-between; align-items: center; background: #fafcfd; }
        .btn-enter { color: #0288d1; text-decoration: none; font-weight: 800; font-size: 15px; transition: 0.3s;}
        .btn-enter:hover { color: #01579b; text-decoration: underline; }
        .btn-delete { color: #d32f2f; text-decoration: none; font-size: 14px; font-weight: 700; transition: 0.3s; }
        .btn-delete:hover { text-decoration: underline; }

        .modal-overlay { display: none; position: fixed; z-index: 999; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5); align-items: center; justify-content: center; }
        .modal-box { background-color: #fff; width: 420px; padding: 30px; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.2); position: relative; }
        .close-modal { position: absolute; top: 15px; right: 20px; font-size: 28px; font-weight: bold; color: #90a4ae; cursor: pointer; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; font-weight: 700; font-size: 14px; margin-bottom: 5px; color: #546e7a; }
        .form-group input, .form-group textarea { width: 100%; padding: 12px; border: 1px solid #cfd8dc; border-radius: 8px; font-size: 15px; outline: none; }
        .form-group input:focus, .form-group textarea:focus { border-color: #0288d1; }
        .btn-submit-modal { width: 100%; background: #0288d1; color: white; border: none; padding: 14px; border-radius: 8px; font-weight: 800; font-size: 16px; cursor: pointer; transition: 0.2s; }
        .btn-submit-modal:hover { background: #0277bd; }
    </style>
</head>
<body>

    <nav class="navbar">
        <a href="#" class="nav-brand">Góc Học Tập</a>
        <div class="nav-actions">
            <button class="btn-create" onclick="openModal()">+ Tạo lớp học</button>
            <div class="user-menu">
                <span class="welcome-text">Chào, <?= htmlspecialchars($_SESSION['ho_ten'] ?? 'Giáo Viên') ?></span>
                <a href="?action=logout" class="btn-logout">Đăng xuất</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <h2 class="page-header">Lớp học bạn đang giảng dạy</h2>
        
        <div class="class-grid">
            <?php if ($classes->num_rows > 0): ?>
                <?php while($class = $classes->fetch_assoc()): ?>
                <div class="class-card">
                    <div class="card-cover">
                        <h3><?= htmlspecialchars($class['ten_lop']) ?></h3>
                        <p><?= htmlspecialchars($class['hoc_ky'] ?? '') ?></p>
                    </div>
                    <div class="card-body">
                        <p style="font-size: 13px; color: #78909c; font-weight: 700;">MÃ THAM GIA LỚP</p>
                        <div class="code-box-container">
                            <div class="code-box" id="code-<?= htmlspecialchars($class['ma_lop']) ?>"><?= htmlspecialchars($class['ma_lop']) ?></div>
                            <button class="btn-copy" onclick="copyCode('code-<?= htmlspecialchars($class['ma_lop']) ?>')" title="Sao chép mã lớp">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect>
                                    <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                    <div class="card-footer">
                        <a href="xoalophoc.php?id=<?= $class['id'] ?>" class="btn-delete" onclick="return confirm('Bạn có chắc chắn muốn xóa lớp này không?');">Xóa</a>
                        <a href="phonghoc.php?malop=<?= htmlspecialchars($class['ma_lop']) ?>" class="btn-enter">Vào phòng học ➔</a>
                    </div>
                </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p style="grid-column: 1 / -1; text-align: center; padding: 80px 20px; color: #90a4ae; font-size: 17px;">
                    Bạn chưa có lớp học nào.<br>
                    Hãy nhấn nút "<strong>+ Tạo lớp học</strong>" để bắt đầu.
                </p>
            <?php endif; ?>
        </div>
    </div>

<div id="createClassModal" class="modal-overlay">
    <div class="modal-box">
        <span class="close-modal" onclick="closeModal()">×</span>
        <h2>Tạo lớp học mới</h2>
        
        <form action="taolophoc.php" method="POST">
            <div class="form-group">
                <label>Tên lớp học</label>
                <input type="text" name="ten_lop" placeholder="Nhập tên lớp học" required>
            </div>
            <div class="form-group">
                <label>Học kỳ / Năm học</label>
                <input type="text" name="hoc_ky" placeholder="Nhập học kỳ và năm học" required>
            </div>
            <div class="form-group">
                <label>Mô tả lớp (không bắt buộc)</label>
                <textarea name="mo_ta" rows="3" placeholder="Mô tả ngắn gọn về lớp học..."></textarea>
            </div>
            <button type="submit" class="btn-submit-modal">Tạo lớp học</button>
        </form>
    </div>
</div>

    <div id="toast" style="display:none; position:fixed; bottom:30px; left:50%; transform:translateX(-50%); background:#333; color:white; padding:12px 20px; border-radius:8px; z-index:10000; box-shadow: 0 4px 12px rgba(0,0,0,0.15);">
        Đã sao chép mã lớp!
    </div>

    <script>
        function openModal() { document.getElementById('createClassModal').style.display = 'flex'; }
        function closeModal() { document.getElementById('createClassModal').style.display = 'none'; }

        function copyCode(id) {
            const text = document.getElementById(id).innerText;
            navigator.clipboard.writeText(text).then(() => {
                const toast = document.getElementById('toast');
                toast.style.display = 'block';
                setTimeout(() => toast.style.display = 'none', 2500);
            });
        }

        window.onclick = function(e) {
            if (e.target.id === 'createClassModal') closeModal();
        }
    </script>
</body>
</html>