<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('session.name', 'GV_SESSION');
session_start();
require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: ../trangdangnhap.php?error=Bạn cần đăng nhập!");
    exit;
}

$stmt = $conn->prepare("SELECT * FROM classes WHERE giaovien_id = ? ORDER BY ngay_tao DESC");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$classes = $stmt->get_result();

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
        body { background: #f4f7f9; color: #333; min-height: 100vh; overflow-x: hidden !important; overflow-y: auto !important; }
        .main-content { margin-left: 260px; padding-top: 60px; transition: margin-left 0.3s; }
        .main-content.mo-rong { margin-left: 0 !important; }
        .btn-create { background: #0288d1; color: white; border: none; padding: 10px 18px; border-radius: 8px; font-weight: 700; font-size: 14px; cursor: pointer; display: flex; align-items: center; gap: 8px; transition: all 0.2s ease; box-shadow: 0 2px 5px rgba(2,136,209,0.2); }
        .btn-create:hover { background: #0277bd; transform: translateY(-1px); box-shadow: 0 4px 8px rgba(2,136,209,0.3); }
        .container { max-width: 1200px; margin: 40px auto; padding: 0 20px; }
        .welcome-section { margin-bottom: 30px; }
        .welcome-section h1 { font-size: 26px; font-weight: 800; color: #1a237e; }
        .welcome-section p { color: #666; font-size: 15px; margin-top: 5px; }
        .class-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 25px; margin-top: 20px; }
        .class-card { background: white; border-radius: 14px; border: 1px solid #e1e8ed; overflow: hidden; display: flex; flex-direction: column; box-shadow: 0 4px 12px rgba(0,0,0,0.03); transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1); position: relative; }
        .class-card:hover { transform: translateY(-4px); box-shadow: 0 12px 20px rgba(0,0,0,0.08); border-color: #0288d1; }
        .btn-delete-class { position: absolute; top: 12px; right: 12px; background: #ef3a3a; color: white; width: 28px; height: 28px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 18px; font-weight: bold; box-shadow: 0 2px 6px rgba(255,82,82,0.3); z-index: 10; text-decoration: none; transition: all 0.2s ease; }
        .btn-delete-class:hover { background: #d32f2f; transform: scale(1.15); }
        .class-header { background: linear-gradient(135deg, #0277bd 0%, #40c4ff 100%); color: white; padding: 22px 20px; position: relative; box-shadow: 0 6px 20px rgba(2, 119, 189, 0.4); }
        .class-header h3 { font-size: 20px; font-weight: 700; margin: 0; text-shadow: 0 2px 5px rgba(0,0,0,0.25); }
        .class-header p { font-size: 13.5px; opacity: 0.95; margin-top: 4px; font-weight: 500; }
        .class-body { padding: 20px; flex-grow: 1; display: flex; flex-direction: column; justify-content: space-between; background: white; }
        .class-desc { font-size: 14px; color: #555; margin-bottom: 15px; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; height: 42px; line-height: 1.5; }
        .class-footer { display: flex; justify-content: space-between; align-items: center; padding-top: 15px; border-top: 1px solid #f0f4f8; width: 100%; }
        .code-box { background: #e1f5fe; color: #0288d1; padding: 4px 10px; border-radius: 6px; font-weight: 700; font-family: monospace; cursor: pointer; display: inline-flex; align-items: center; gap: 4px; font-size: 13px; }
        .code-box:hover { background: #b3e5fc; }
        .group-buttons { display: flex; align-items: center; gap: 12px; }
        .btn-danh-gia { color: #ea580c; text-decoration: none; font-weight: 700; font-size: 14px; display: inline-flex; align-items: center; gap: 3px; transition: transform 0.2s; }
        .btn-danh-gia:hover { transform: scale(1.05); color: #c2410c; }
        .btn-enter { color: #0288d1; text-decoration: none; font-weight: 700; font-size: 14px; display: inline-flex; align-items: center; gap: 4px; }
        .btn-enter:hover { color: #01579b; }
        .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.4); justify-content: center; align-items: center; z-index: 1000; backdrop-filter: blur(4px); }
        .modal-content { background: white; padding: 30px; border-radius: 16px; width: 100%; max-width: 450px; box-shadow: 0 10px 25px rgba(0,0,0,0.15); animation: slideUp 0.3s ease; }
        @keyframes slideUp { from { transform: translateY(20px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
        .form-group { margin-bottom: 18px; }
        .form-group label { display: block; font-size: 14px; font-weight: 700; margin-bottom: 6px; color: #444; }
        .form-group input, .form-group textarea { width: 100%; padding: 12px; border: 1px solid #cfd8dc; border-radius: 8px; font-size: 14px; outline: none; font-family: inherit; }
        .btn-submit-modal { background: #0288d1; color: white; border: none; width: 100%; padding: 12px; border-radius: 8px; font-weight: 700; font-size: 15px; cursor: pointer; margin-top: 10px; }
        .empty-state { text-align: center; padding: 60px 20px; background: white; border-radius: 14px; border: 1px solid #e1e8ed; max-width: 500px; margin: 40px auto; }
    </style>
</head>
<body>

    <?php include 'thanh.php'; ?>

    <div class="main-content">
        <div class="container">
            <div class="welcome-section">
                <h1>Xin chào, Giáo Viên <?= htmlspecialchars($avatar_name ?? 'Giáo Viên') ?>!</h1>
                <p>Dưới đây là danh sách các lớp học trực tuyến do Giáo Viên quản lý.</p>
            </div>

            <?php if ($classes->num_rows > 0): ?>
                <div class="class-grid">
                    <?php while ($row = $classes->fetch_assoc()): ?>
                        <div class="class-card">
                            <a href="xoalophoc.php?id=<?= $row['id'] ?>" 
                               class="btn-delete-class"
                               onclick="return confirm('Bạn có chắc chắn muốn xóa lớp học này không?')"
                               title="Xóa lớp học">
                                ×
                            </a>

                            <div class="class-header">
                                <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 6px;">
                                    <img src="<?= htmlspecialchars($gv_avatar_src ?? '') ?>" 
                                         style="width: 36px; height: 36px; border-radius: 50%; object-fit: cover; flex-shrink: 0; border: 2px solid rgba(255,255,255,0.75);" 
                                         alt="Avatar"
                                         onerror="this.src='https://ui-avatars.com/api/?name=Lop&background=0284c7&color=fff'">
                                    <h3><?= htmlspecialchars($row['ten_lop']) ?></h3>
                                </div>
                                <p><?= htmlspecialchars($row['hoc_ky'] ?? '') ?></p>
                            </div>
                            
                            <div class="class-body">
                                <p class="class-desc"><?= htmlspecialchars($row['mo_ta'] ?: 'Chưa có mô tả ngắn gọn nào cho lớp học này...') ?></p>
                                
                                <div class="class-footer" style="display: flex; align-items: center; justify-content: space-between; padding-top: 15px; border-top: 1px solid #f0f4f8; width: 100%;">
                                    
                                    <div style="flex: 1; text-align: left;">
                                        <span class="code-box" title="Bấm để sao chép mã lớp" onclick="event.preventDefault(); copyCode('code_<?= $row['ma_lop'] ?>')">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path></svg>
                                            <span id="code_<?= $row['ma_lop'] ?>"><?= htmlspecialchars($row['ma_lop']) ?></span>
                                        </span>
                                    </div>
                                    
                                    <div style="flex: 1; text-align: center;">
                                        <a href="thongke.php?id_lop_hoc=<?= $row['id'] ?>" class="btn-danh-gia" title="Xem kết quả khảo sát" style="color: #ea580c; text-decoration: none; font-weight: 700; font-size: 14px;">
                                            Xem Đánh giá
                                        </a>
                                    </div>

                                    <div style="flex: 1; text-align: right;">
                                        <a href="phonghoc.php?malop=<?= $row['ma_lop'] ?>" class="btn-enter" style="color: #0288d1; text-decoration: none; font-weight: 700; font-size: 14px;">
                                            Vào lớp →
                                        </a>
                                    </div>

                                </div>
                            </div>
                        </div> <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"></path></svg>
                    <h3>Chưa có lớp học nào</h3>
                    <p>Giáo viên chưa tạo lớp học nào. Hãy nhấn nút phía trên để bắt đầu tạo lớp học đầu tiên.</p>
                    <button class="btn-create" style="margin: 0 auto;" onclick="openModal()">Tạo lớp ngay</button>
                </div>
            <?php endif; ?>
        </div>
    </div> 

    <div id="createClassModal" class="modal">
        <div class="modal-content">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
                <h2>Tạo Lớp Học Mới</h2>
                <button style="background:none; border:none; font-size:24px; cursor:pointer;" onclick="closeModal()">&times;</button>
            </div>
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
                    <label>Mô tả lớp</label>
                    <textarea name="mo_ta" rows="3" placeholder="Mô tả ngắn gọn về lớp học..."></textarea>
                </div>
                <button type="submit" class="btn-submit-modal">Tạo lớp học</button>
            </form>
        </div>
    </div>

    <div id="toast" style="display:none; position:fixed; bottom:30px; left:50%; transform:translateX(-50%); background:#333; color:white; padding:12px 20px; border-radius:8px; z-index:10000;">
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
                setTimeout(() => toast.style.display = 'none', 2000);
            });
        }
    </script>
</body>
</html>