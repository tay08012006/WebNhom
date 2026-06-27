<?php
// Khởi động session nếu chưa có
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.name', 'HS_SESSION');
    session_start();
}

// Gọi file kết nối CSDL chung
include '../config.php';

// Đẩy về trang đăng nhập nếu không phải học sinh
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'student') {
    header("Location: ../trangdangnhap.php");
    exit();
}

// Lấy ID lớp từ URL và ID học sinh từ session
$id_lop = intval($_GET['id_lop'] ?? 0);
$id_hocsinh = $_SESSION['user_id'];

// Dừng chương trình nếu không có ID lớp
if (!$id_lop) {
    die("Không tìm thấy lớp học!");
}

// Lấy thông tin lớp và tên giáo viên
$stmt_lop = $conn->prepare("
    SELECT classes.*, users.hoten AS ten_gv
    FROM classes
    JOIN users ON classes.giaovien_id = users.id
    WHERE classes.id = ?
");
$stmt_lop->bind_param("i", $id_lop);
$stmt_lop->execute();
$lop = $stmt_lop->get_result()->fetch_assoc();

// Báo lỗi nếu lớp không tồn tại
if (!$lop) die("Lớp học không tồn tại!");

// Kiểm tra xem học sinh này có thuộc lớp đang truy cập không
$stmt_check = $conn->prepare("SELECT id FROM class_enrollments WHERE user_id = ? AND class_id = ?");
$stmt_check->bind_param("ii", $id_hocsinh, $id_lop);
$stmt_check->execute();

// Chặn truy cập nếu chưa tham gia lớp
if ($stmt_check->get_result()->num_rows === 0) {
    die("Bạn không có quyền xem lớp học này!");
}

// Truy vấn lấy danh sách tất cả video của lớp này
$stmt_vids = $conn->prepare("SELECT * FROM videos WHERE class_id = ? ORDER BY ngay_tao DESC");
$stmt_vids->bind_param("i", $id_lop);
$stmt_vids->execute();
$videos = $stmt_vids->get_result();

// Hàm dùng Regex để trích xuất nhanh ID video từ mọi định dạng link YouTube
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
    <title>Bài giảng Video | <?= htmlspecialchars($lop['ten_lop']) ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    
    <style>
        /* Reset CSS mặc định */
        * { 
            margin: 0; 
            padding: 0; 
            box-sizing: border-box; 
            font-family: 'Nunito', sans-serif; 
        }
        
        /* Cài đặt nền toàn trang */
        body { 
            background: #f4f7f6; 
            min-height: 100vh; 
        }
        
        /* Khung nội dung chính bên phải thanh menu */
        .main-content { 
            margin-left: 260px; 
            padding: 90px 30px 30px 30px; 
        }

        /* Khung chứa tiêu đề trang và nút quay lại */
        .page-header { 
            display: flex; 
            align-items: center; 
            gap: 16px; 
            margin-bottom: 25px; 
            flex-wrap: wrap; 
        }
        
        /* Nút quay lại lớp học */
        .btn-back { 
            display: inline-flex; 
            align-items: center; 
            gap: 6px; 
            background: #e2e8f0; 
            color: #475569; 
            text-decoration: none; 
            padding: 9px 18px; 
            border-radius: 20px; 
            font-weight: 700; 
            font-size: 14px; 
            transition: all 0.2s; 
            white-space: nowrap; 
        }
        .btn-back:hover { 
            background: #cbd5e1; 
            color: #1e293b; 
        }
        
        /* Tên tiêu đề chính */
        .page-title { 
            font-size: 22px; 
            font-weight: 800; 
            color: #1e3a8a; 
        }
        
        /* Thông tin phụ (Tên lớp, Tên GV) */
        .page-sub { 
            font-size: 14px; 
            color: #64748b; 
            margin-top: 2px; 
        }

        /* Lưới hiển thị danh sách các thẻ video */
        .video-grid { 
            display: grid; 
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); 
            gap: 22px; 
        }
        
        /* Khung thẻ chứa 1 video riêng lẻ */
        .video-card { 
            background: white; 
            border-radius: 16px; 
            overflow: hidden; 
            box-shadow: 0 4px 14px rgba(0,0,0,0.05); 
            border: 1px solid #e2e8f0; 
            cursor: pointer; 
            transition: all 0.25s; 
        }
        
        /* Hiệu ứng nổi thẻ lên khi đưa chuột vào */
        .video-card:hover { 
            transform: translateY(-4px); 
            box-shadow: 0 12px 24px rgba(0,0,0,0.10); 
            border-color: #1e3a8a; 
        }
        
        /* Khung chứa ảnh bìa (thumbnail) tỉ lệ 16:9 */
        .video-thumb { 
            position: relative; 
            background: #0f172a; 
            aspect-ratio: 16/9; 
            overflow: hidden; 
        }
        
        /* Ảnh bìa video */
        .video-thumb img { 
            width: 100%; 
            height: 100%; 
            object-fit: cover; 
            display: block; 
            transition: transform 0.3s; 
        }
        
        /* Hiệu ứng zoom nhẹ ảnh bìa khi hover */
        .video-card:hover .video-thumb img { 
            transform: scale(1.04); 
        }
        
        /* Lớp nền mờ màu đen phủ lên ảnh bìa */
        .play-overlay { 
            position: absolute; 
            inset: 0; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            background: rgba(0,0,0,0.28); 
        }
        
        /* Nút Play hình tròn nổi bật ở giữa ảnh */
        .play-btn { 
            width: 52px; 
            height: 52px; 
            background: rgba(255,255,255,0.9); 
            border-radius: 50%; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            box-shadow: 0 4px 12px rgba(0,0,0,0.3); 
            transition: transform 0.2s; 
        }
        
        /* Phóng to nút Play khi hover thẻ */
        .video-card:hover .play-btn { 
            transform: scale(1.12); 
        }
        
        /* Icon hình tam giác Play */
        .play-btn svg { 
            fill: #1e3a8a; 
            width: 22px; 
            height: 22px; 
            margin-left: 3px; 
        }
        
        /* Khung chữ thông tin bên dưới ảnh bìa */
        .video-info { 
            padding: 16px; 
        }
        
        /* Tiêu đề video (Tự động cắt dấu ... nếu quá 2 dòng) */
        .video-title { 
            font-size: 15px; 
            font-weight: 700; 
            color: #1e293b; 
            margin-bottom: 6px; 
            display: -webkit-box; 
            -webkit-line-clamp: 2; 
            -webkit-box-orient: vertical; 
            overflow: hidden; 
            line-height: 1.4; 
        }
        
        /* Mô tả video (Tự động cắt dấu ... nếu quá 2 dòng) */
        .video-desc { 
            font-size: 13px; 
            color: #64748b; 
            margin-bottom: 10px; 
            display: -webkit-box; 
            -webkit-line-clamp: 2; 
            -webkit-box-orient: vertical; 
            overflow: hidden; 
        }
        
        /* Dòng thông tin phụ (Tên GV, Ngày tải lên) */
        .video-meta { 
            display: flex; 
            align-items: center; 
            gap: 8px; 
            font-size: 12px; 
            color: #94a3b8; 
        }
        
        /* Thẻ tên giáo viên màu xanh nhạt */
        .tag-gv { 
            background: #eff6ff; 
            color: #1d4ed8; 
            padding: 2px 8px; 
            border-radius: 20px; 
            font-weight: 700; 
        }

        /* Giao diện hiển thị khi danh sách trống */
        .empty-state { 
            text-align: center; 
            padding: 60px 20px; 
            color: #94a3b8; 
            background: white; 
            border-radius: 16px; 
            border: 1px solid #e2e8f0; 
        }
        .empty-state i { 
            font-size: 48px; 
            margin-bottom: 12px; 
            display: block; 
        }
        .empty-state p { 
            font-size: 15px; 
            font-weight: 600; 
        }

        /* Lớp nền mờ tối che màn hình khi mở Modal xem video */
        .modal-overlay { 
            display: none; 
            position: fixed; 
            inset: 0; 
            background: rgba(0,0,0,0.8); 
            z-index: 2000; 
            align-items: center; 
            justify-content: center; 
            padding: 20px; 
        }
        .modal-overlay.open { 
            display: flex; 
        }
        
        /* Khung hộp Modal chứa iframe video */
        .modal-box { 
            background: #0f172a; 
            border-radius: 16px; 
            width: 100%; 
            max-width: 860px; 
            overflow: hidden; 
            position: relative; 
        }
        
        /* Phần Header đen của Modal */
        .modal-header { 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            padding: 14px 18px; 
            background: #1e293b; 
        }
        
        /* Tiêu đề video trong cửa sổ Modal */
        .modal-vid-title { 
            color: white; 
            font-weight: 700; 
            font-size: 15px; 
            overflow: hidden; 
            text-overflow: ellipsis; 
            white-space: nowrap; 
            flex: 1; 
            margin-right: 12px; 
        }
        
        /* Nút dấu X để đóng Modal */
        .modal-close { 
            color: #94a3b8; 
            font-size: 26px; 
            cursor: pointer; 
            background: none; 
            border: none; 
            line-height: 1; 
            transition: color 0.2s; 
        }
        .modal-close:hover { 
            color: white; 
        }
        
        /* Iframe nhúng video từ YouTube */
        .modal-iframe { 
            width: 100%; 
            aspect-ratio: 16/9; 
            border: none; 
            display: block; 
        }
    </style>
</head>
<body>
    
    <?php include 'thanh.php'; ?>

    <div class="main-content">
        <div class="page-header">
            <a href="chitiet_lop.php?id=<?= $id_lop ?>&tab=xem-video" class="btn-back">
                <i class="fa-solid fa-arrow-left"></i> Quay lại lớp học
            </a>
            <div>
                <div class="page-title"><i class="fa-solid fa-play-circle" style="color:#1e3a8a;"></i> Bài giảng Video</div>
                <div class="page-sub">Lớp: <b><?= htmlspecialchars($lop['ten_lop']) ?></b> &nbsp;|&nbsp; Giáo viên: <b><?= htmlspecialchars($lop['ten_gv']) ?></b></div>
            </div>
        </div>

        <?php if ($videos->num_rows > 0): ?>
            <div class="video-grid">
                <?php while ($vid = $videos->fetch_assoc()):
                    // Lấy ID Youtube và tạo link ảnh bìa (thumbnail)
                    $ytId = extractYoutubeId($vid['youtube_url']);
                    $thumb = $ytId ? "https://img.youtube.com/vi/$ytId/mqdefault.jpg" : '';
                ?>
                <div class="video-card" onclick="xemVideo('<?= htmlspecialchars($ytId ?? '') ?>', '<?= htmlspecialchars(addslashes($vid['tieu_de'])) ?>')">
                    <div class="video-thumb">
                        <?php if ($thumb): ?>
                            <img src="<?= $thumb ?>" alt="<?= htmlspecialchars($vid['tieu_de']) ?>">
                        <?php else: ?>
                            <div style="width:100%;height:100%;background:#1e293b;display:flex;align-items:center;justify-content:center;">
                                <i class="fa-solid fa-video" style="font-size:36px;color:#475569;"></i>
                            </div>
                        <?php endif; ?>
                        
                        <div class="play-overlay">
                            <div class="play-btn">
                                <svg viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                            </div>
                        </div>
                    </div>
                    
                    <div class="video-info">
                        <div class="video-title"><?= htmlspecialchars($vid['tieu_de']) ?></div>
                        
                        <?php if ($vid['mo_ta']): ?>
                            <div class="video-desc"><?= htmlspecialchars($vid['mo_ta']) ?></div>
                        <?php endif; ?>
                        
                        <div class="video-meta">
                            <span class="tag-gv"><i class="fa-solid fa-chalkboard-user"></i> <?= htmlspecialchars($lop['ten_gv']) ?></span>
                            <span> <?= date("d/m/Y", strtotime($vid['ngay_tao'])) ?></span>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
            
        <?php else: ?>
            <div class="empty-state">
                <i class="fa-solid fa-video-slash"></i>
                <p>Chưa có bài giảng video nào trong lớp học này.</p>
            </div>
        <?php endif; ?>
    </div>

    <div class="modal-overlay" id="videoModal" onclick="if(event.target===this)dongVideo()">
        <div class="modal-box">
            <div class="modal-header">
                <span class="modal-vid-title" id="modalTitle"></span>
                <button class="modal-close" onclick="dongVideo()">&times;</button>
            </div>
            <iframe id="modalIframe" class="modal-iframe" src="" allowfullscreen allow="autoplay; encrypted-media"></iframe>
        </div>
    </div>

    <script>
    // Hàm nhận tham số là ID Youtube và Tiêu đề để truyền vào Modal
    function xemVideo(ytId, title) {
        if (!ytId) return; // Không làm gì nếu URL lỗi
        document.getElementById('modalTitle').textContent = title;
        // Bật tham số autoplay=1 để video tự chạy khi mở Modal
        document.getElementById('modalIframe').src = 'https://www.youtube.com/embed/' + ytId + '?autoplay=1';
        document.getElementById('videoModal').classList.add('open');
    }
    
    // Hàm tắt Modal và xóa link Youtube để dừng phát nhạc
    function dongVideo() {
        document.getElementById('modalIframe').src = '';
        document.getElementById('videoModal').classList.remove('open');
    }
    
    // Nhấn phím Escape (Esc) trên bàn phím để thoát video nhanh
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') dongVideo();
    });
    </script>
</body>
</html>