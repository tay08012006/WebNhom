<?php
ini_set('session.name', 'GV_SESSION');
session_start();
require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: ../trangdangnhap.php");
    exit;
}

$ma_lop = $_GET['malop'] ?? '';

$stmt = $conn->prepare("SELECT * FROM classes WHERE ma_lop = ? AND giaovien_id = ?");
$stmt->bind_param("si", $ma_lop, $_SESSION['user_id']);
$stmt->execute();
$class = $stmt->get_result()->fetch_assoc();

if (!$class) {
    die("Lớp học không tồn tại hoặc bạn không có quyền!");
}

$thong_bao = '';
$loi = '';

// Xóa video
if (isset($_GET['xoa']) && is_numeric($_GET['xoa'])) {
    $id_xoa = intval($_GET['xoa']);
    $stmt_del = $conn->prepare("DELETE FROM videos WHERE id = ? AND giaovien_id = ?");
    $stmt_del->bind_param("ii", $id_xoa, $_SESSION['user_id']);
    $stmt_del->execute();
    header("Location: dangvideo.php?malop=$ma_lop&xong=1");
    exit;
}

if (isset($_GET['xong'])) {
    $thong_bao = "Đã xóa video thành công!";
}

// Thêm video mới
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tieu_de    = trim($_POST['tieu_de'] ?? '');
    $mo_ta      = trim($_POST['mo_ta'] ?? '');
    $youtube_url = trim($_POST['youtube_url'] ?? '');

    if (empty($tieu_de) || empty($youtube_url)) {
        $loi = "Vui lòng nhập tiêu đề và link YouTube!";
    } else {
        $regex = '/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/';
        if (!preg_match($regex, $youtube_url)) {
            $loi = "Đường dẫn YouTube không hợp lệ!";
        } else {
            $stmt_ins = $conn->prepare("INSERT INTO videos (class_id, giaovien_id, tieu_de, mo_ta, youtube_url) VALUES (?, ?, ?, ?, ?)");
            $stmt_ins->bind_param("iisss", $class['id'], $_SESSION['user_id'], $tieu_de, $mo_ta, $youtube_url);
            if ($stmt_ins->execute()) {
                $thong_bao = "Đăng video thành công!";
            } else {
                $loi = "Có lỗi xảy ra, vui lòng thử lại!";
            }
        }
    }
}

// Lấy danh sách video của lớp
$stmt_vids = $conn->prepare("SELECT * FROM videos WHERE class_id = ? ORDER BY ngay_tao DESC");
$stmt_vids->bind_param("i", $class['id']);
$stmt_vids->execute();
$videos = $stmt_vids->get_result();

function extractYoutubeId($url) {
    $regex = '/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/';
    return preg_match($regex, $url, $m) ? $m[1] : null;
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Video | <?= htmlspecialchars($class['ten_lop']) ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Nunito', sans-serif; }
        body { background: #f4f7f9; color: #333; min-height: 100vh; }
        .navbar { display: flex; justify-content: space-between; align-items: center; background: white; padding: 12px 30px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); position: sticky; top: 0; z-index: 100; }
        .btn-back { display: inline-flex; align-items: center; gap: 6px; background: #f0f4f8; color: #555; text-decoration: none; padding: 8px 16px; border-radius: 20px; font-weight: 700; font-size: 14px; transition: all 0.2s; }
        .btn-back:hover { background: #e1e8ed; color: #333; }
        .logo-text { font-weight: 800; color: #0288d1; font-size: 20px; }
        .container { max-width: 900px; margin: 30px auto; padding: 0 20px; }
        .page-title { font-size: 24px; font-weight: 800; color: #1a237e; margin-bottom: 6px; }
        .page-sub { color: #666; font-size: 14px; margin-bottom: 25px; }

        /* Form đăng video */
        .form-card { background: white; border-radius: 16px; padding: 28px; box-shadow: 0 4px 12px rgba(0,0,0,0.04); margin-bottom: 30px; border: 1px solid #e1e8ed; }
        .form-card h3 { font-size: 18px; font-weight: 800; color: #0277bd; margin-bottom: 20px; display: flex; align-items: center; gap: 8px; }
        .form-group { margin-bottom: 16px; }
        .form-group label { display: block; font-size: 14px; font-weight: 700; color: #444; margin-bottom: 6px; }
        .form-group input, .form-group textarea { width: 100%; padding: 12px 14px; border: 1.5px solid #cfd8dc; border-radius: 10px; font-size: 14px; font-family: inherit; outline: none; transition: border-color 0.2s; }
        .form-group input:focus, .form-group textarea:focus { border-color: #0288d1; box-shadow: 0 0 0 3px rgba(2,136,209,0.1); }
        .btn-submit { background: #0288d1; color: white; border: none; padding: 13px 28px; border-radius: 10px; font-weight: 800; font-size: 15px; cursor: pointer; transition: 0.2s; }
        .btn-submit:hover { background: #0277bd; transform: translateY(-1px); }

        /* Thông báo */
        .alert-success { background: #e8f5e9; color: #2e7d32; border: 1px solid #c8e6c9; padding: 14px 18px; border-radius: 10px; margin-bottom: 20px; font-weight: 700; }
        .alert-error { background: #ffebee; color: #c62828; border: 1px solid #ffcdd2; padding: 14px 18px; border-radius: 10px; margin-bottom: 20px; font-weight: 700; }

        /* Danh sách video */
        .section-title { font-size: 18px; font-weight: 800; color: #1a237e; margin-bottom: 16px; display: flex; align-items: center; gap: 8px; }
        .video-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 20px; }
        .video-card { background: white; border-radius: 14px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.05); border: 1px solid #e1e8ed; transition: all 0.25s; }
        .video-card:hover { transform: translateY(-3px); box-shadow: 0 10px 20px rgba(0,0,0,0.09); }
        .video-thumb { position: relative; background: #000; aspect-ratio: 16/9; overflow: hidden; }
        .video-thumb img { width: 100%; height: 100%; object-fit: cover; display: block; }
        .video-thumb .play-icon { position: absolute; inset: 0; display: flex; align-items: center; justify-content: center; background: rgba(0,0,0,0.3); }
        .video-thumb .play-icon svg { width: 48px; height: 48px; fill: white; filter: drop-shadow(0 2px 6px rgba(0,0,0,0.5)); }
        .video-info { padding: 14px; }
        .video-info h4 { font-size: 15px; font-weight: 700; color: #1a237e; margin-bottom: 6px; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
        .video-info p { font-size: 13px; color: #64748b; margin-bottom: 10px; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
        .video-date { font-size: 12px; color: #94a3b8; }
        .video-actions { display: flex; gap: 8px; margin-top: 12px; }
        .btn-watch { flex: 1; background: #e3f2fd; color: #0277bd; border: none; padding: 8px; border-radius: 8px; font-weight: 700; font-size: 13px; cursor: pointer; text-decoration: none; text-align: center; transition: 0.2s; }
        .btn-watch:hover { background: #bbdefb; }
        .btn-delete { background: #ffebee; color: #c62828; border: none; padding: 8px 12px; border-radius: 8px; font-weight: 700; font-size: 13px; cursor: pointer; text-decoration: none; transition: 0.2s; }
        .btn-delete:hover { background: #ffcdd2; }
        .empty-state { text-align: center; padding: 50px 20px; color: #94a3b8; background: white; border-radius: 14px; border: 1px solid #e1e8ed; }
        .empty-state p { margin-top: 12px; font-size: 15px; font-weight: 600; }

        /* Modal xem video */
        .modal-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.75); z-index: 1000; align-items: center; justify-content: center; }
        .modal-overlay.open { display: flex; }
        .modal-box { background: #111; border-radius: 16px; width: 100%; max-width: 820px; overflow: hidden; position: relative; }
        .modal-close { position: absolute; top: 12px; right: 16px; font-size: 28px; color: white; cursor: pointer; z-index: 10; background: none; border: none; line-height: 1; }
        .modal-close:hover { color: #ef4444; }
        .modal-iframe { width: 100%; aspect-ratio: 16/9; border: none; display: block; }
    </style>
</head>
<body>
<nav class="navbar">
    <div style="display:flex; align-items:center; gap:16px;">
        <a href="phonghoc.php?malop=<?= htmlspecialchars($ma_lop) ?>" class="btn-back">← Quay lại lớp học</a>
        <span class="logo-text">Góc Học Tập</span>
    </div>
    <?php include 'anhdaidien.php'; ?>
</nav>

<div class="container">
    <div class="page-title">📹 Quản lý Video Bài Giảng</div>
    <div class="page-sub">Lớp: <b><?= htmlspecialchars($class['ten_lop']) ?></b> | Mã lớp: <b><?= htmlspecialchars($class['ma_lop']) ?></b></div>

    <?php if ($thong_bao): ?>
        <div class="alert-success">✅ <?= htmlspecialchars($thong_bao) ?></div>
    <?php endif; ?>
    <?php if ($loi): ?>
        <div class="alert-error">❌ <?= htmlspecialchars($loi) ?></div>
    <?php endif; ?>

    <div class="form-card">
        <h3>
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Đăng video mới cho lớp học
        </h3>
        <form method="POST">
            <div class="form-group">
                <label>Tiêu đề video <span style="color:#ef4444">*</span></label>
                <input type="text" name="tieu_de" placeholder="Nhập tiêu đề bài giảng..." required>
            </div>
            <div class="form-group">
                <label>Mô tả (không bắt buộc)</label>
                <textarea name="mo_ta" rows="2" placeholder="Mô tả ngắn về nội dung video..."></textarea>
            </div>
            <div class="form-group">
                <label>Link YouTube <span style="color:#ef4444">*</span></label>
                <input type="url" name="youtube_url" placeholder="https://www.youtube.com/watch?v=... hoặc https://youtu.be/..." required>
            </div>
            <button type="submit" class="btn-submit">📤 Đăng Video</button>
        </form>
    </div>

    <div class="section-title">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="23 7 16 12 23 17 23 7"/><rect x="1" y="5" width="15" height="14" rx="2" ry="2"/></svg>
        Danh sách video đã đăng (<?= $videos->num_rows ?>)
    </div>

    <?php if ($videos->num_rows > 0): ?>
        <div class="video-grid">
            <?php while ($vid = $videos->fetch_assoc()):
                $ytId = extractYoutubeId($vid['youtube_url']);
                $thumb = $ytId ? "https://img.youtube.com/vi/$ytId/mqdefault.jpg" : '';
            ?>
            <div class="video-card">
                <div class="video-thumb" style="cursor:pointer;" onclick="xemVideo('<?= htmlspecialchars($ytId ?? '') ?>')">
                    <?php if ($thumb): ?>
                        <img src="<?= $thumb ?>" alt="<?= htmlspecialchars($vid['tieu_de']) ?>">
                    <?php endif; ?>
                    <div class="play-icon">
                        <svg viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                    </div>
                </div>
                <div class="video-info">
                    <h4><?= htmlspecialchars($vid['tieu_de']) ?></h4>
                    <?php if ($vid['mo_ta']): ?>
                        <p><?= htmlspecialchars($vid['mo_ta']) ?></p>
                    <?php endif; ?>
                    <div class="video-date">📅 <?= date("H:i d/m/Y", strtotime($vid['ngay_tao'])) ?></div>
                    <div class="video-actions">
                        <a href="javascript:void(0)" class="btn-watch" onclick="xemVideo('<?= htmlspecialchars($ytId ?? '') ?>')">▶ Xem</a>
                        <a href="dangvideo.php?malop=<?= urlencode($ma_lop) ?>&xoa=<?= $vid['id'] ?>"
                           class="btn-delete"
                           onclick="return confirm('Xóa video này?')">🗑</a>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <div class="empty-state">
            <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#cbd5e1" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><polygon points="23 7 16 12 23 17 23 7"/><rect x="1" y="5" width="15" height="14" rx="2" ry="2"/></svg>
            <p>Chưa có video nào. Hãy đăng video bài giảng đầu tiên!</p>
        </div>
    <?php endif; ?>
</div>

<!-- Modal xem video -->
<div class="modal-overlay" id="videoModal" onclick="if(event.target===this)dongVideo()">
    <div class="modal-box">
        <button class="modal-close" onclick="dongVideo()">&times;</button>
        <iframe id="modalIframe" class="modal-iframe" src="" allowfullscreen allow="autoplay; encrypted-media"></iframe>
    </div>
</div>

<script>
function xemVideo(ytId) {
    if (!ytId) return;
    document.getElementById('modalIframe').src = 'https://www.youtube.com/embed/' + ytId + '?autoplay=1';
    document.getElementById('videoModal').classList.add('open');
}
function dongVideo() {
    document.getElementById('modalIframe').src = '';
    document.getElementById('videoModal').classList.remove('open');
}
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') dongVideo();
});
</script>
</body>
</html>
