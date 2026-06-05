<?php
ini_set('session.name', 'GV_SESSION');
session_start();
require_once '../config.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: ../trangdangnhap.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// --- XỬ LÝ CẬP NHẬT TÊN ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['type']) && $_POST['type'] === 'update_name') {
    $ten_moi = trim($_POST['ho_ten_moi'] ?? '');
    if (!empty($ten_moi)) {
        $stmt_upd = $conn->prepare("UPDATE users SET hoten = ? WHERE id = ?");
        $stmt_upd->bind_param("si", $ten_moi, $user_id);
        $stmt_upd->execute();
        $_SESSION['ho_ten'] = $ten_moi;
        $_SESSION['hoten']  = $ten_moi;
        $success_msg = "Đã cập nhật tên thành: " . htmlspecialchars($ten_moi);
    } else {
        $error_msg = "Tên không được để trống!";
    }
}

// Đọc thông tin từ DB
$stmt_info = $conn->prepare("SELECT hoten, avatar FROM users WHERE id = ?");
$stmt_info->bind_param("i", $user_id);
$stmt_info->execute();
$row_info = $stmt_info->get_result()->fetch_assoc();

$ten_gv    = $row_info['hoten'] ?? $_SESSION['ho_ten'] ?? 'Giáo Viên';
$avatar_db = $row_info['avatar'] ?? '';

// Đọc thông báo từ session (sau redirect từ xuly_avatar.php)
if (!empty($_SESSION['success'])) {
    $success_msg = $_SESSION['success'];
    unset($_SESSION['success']);
}
if (!empty($_SESSION['error'])) {
    $error_msg = $_SESSION['error'];
    unset($_SESSION['error']);
}

// Xây dựng src ảnh
if (!empty($avatar_db)) {
    $avatar_src = "../uploads/" . htmlspecialchars($avatar_db);
} else {
    $avatar_src = "https://ui-avatars.com/api/?name=" . urlencode($ten_gv) . "&background=0284c7&color=fff&bold=true&size=120";
}

// Lấy chữ cái đầu để fallback
$words = explode(' ', trim($ten_gv));
$chu_cai = mb_strtoupper(mb_substr(end($words), 0, 1, 'UTF-8'), 'UTF-8');
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hồ sơ cá nhân | Góc Học Tập</title>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            background-color: #f4f7f6;
            font-family: 'Nunito', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .profile-card {
            background: white;
            width: 480px;
            padding: 35px 30px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
            text-align: center;
        }

        .profile-card h2 {
            font-size: 22px;
            font-weight: 800;
            color: #0f172a;
            margin-bottom: 25px;
        }

        /* Avatar */
        .avatar-wrapper {
            position: relative;
            display: inline-block;
            margin-bottom: 8px;
        }

        .avatar-img {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid #e0f2fe;
            display: block;
        }

        .avatar-edit-btn {
            position: absolute;
            bottom: 2px; right: 2px;
            background: #0284c7;
            border-radius: 50%;
            width: 34px; height: 34px;
            display: flex; align-items: center; justify-content: center;
            cursor: pointer;
            border: 2px solid white;
            transition: background 0.2s;
        }
        .avatar-edit-btn:hover { background: #01579b; }
        .avatar-edit-btn svg { color: white; }

        input[type="file"] { display: none; }

        .hint-text {
            font-size: 12px;
            color: #94a3b8;
            margin-bottom: 20px;
        }

        /* Thông báo */
        .alert {
            padding: 10px 14px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 18px;
            text-align: left;
        }
        .alert-success { background: #f0fdf4; color: #166534; border: 1px solid #bbf7d0; }
        .alert-error   { background: #fff1f2; color: #be123c; border: 1px solid #fecdd3; }

        /* Form */
        .info-group { text-align: left; }

        .info-group label {
            display: block;
            color: #78909c;
            font-weight: 700;
            font-size: 13px;
            margin-bottom: 6px;
        }

        .input-style {
            width: 100%;
            padding: 12px;
            margin-bottom: 16px;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
            font-family: 'Nunito', sans-serif;
            font-weight: 600;
            font-size: 15px;
            box-sizing: border-box;
            color: #1e293b;
            transition: border 0.2s;
        }
        .input-style:focus { outline: none; border-color: #0284c7; }
        .input-readonly { background: #f8fafc; color: #94a3b8; }

        .btn-save {
            background: #ff9800;
            color: white;
            padding: 12px;
            border: none;
            border-radius: 8px;
            font-weight: 700;
            cursor: pointer;
            width: 100%;
            font-size: 15px;
            font-family: 'Nunito', sans-serif;
            transition: background 0.2s;
        }
        .btn-save:hover { background: #e68a00; }

        .footer-link {
            margin-top: 22px;
            padding-top: 18px;
            border-top: 1px solid #f1f5f9;
        }

        .btn-back {
            display: inline-block;
            color: #0284c7;
            text-decoration: none;
            font-weight: 700;
            font-size: 14px;
            transition: all 0.2s;
        }
        .btn-back:hover { color: #01579b; transform: translateX(-4px); }
    </style>
</head>
<body>

<div class="profile-card">
    <h2>Hồ sơ cá nhân</h2>

    <?php if (!empty($success_msg)): ?>
        <div class="alert alert-success">✅ <?= $success_msg ?></div>
    <?php endif; ?>
    <?php if (!empty($error_msg)): ?>
        <div class="alert alert-error">❌ <?= $error_msg ?></div>
    <?php endif; ?>

    <!-- Avatar upload -->
    <form action="xuly_avatar.php" method="POST" enctype="multipart/form-data" id="avatarForm">
        <div class="avatar-wrapper">
            <img src="<?= $avatar_src ?>"
                 class="avatar-img"
                 id="avatarPreview"
                 onerror="this.src='https://ui-avatars.com/api/?name=<?= urlencode($ten_gv) ?>&background=0284c7&color=fff&bold=true&size=120'">
            <label class="avatar-edit-btn" for="avatarInput" title="Đổi ảnh đại diện">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                </svg>
            </label>
            <input type="file" id="avatarInput" name="file_avatar"
                   accept="image/jpeg,image/png,image/gif,image/webp"
                   onchange="previewAndSubmit(this)">
        </div>
    </form>
    <p class="hint-text">Nhấp vào biểu tượng bút để thay ảnh</p>

    <!-- Cập nhật tên -->
    <form action="profile.php" method="POST">
        <input type="hidden" name="type" value="update_name">
        <div class="info-group">
            <label>Họ và tên</label>
            <input type="text" name="ho_ten_moi" class="input-style"
                   value="<?= htmlspecialchars($ten_gv) ?>" required>

            <label>Vai trò</label>
            <input type="text" value="Giáo viên" readonly class="input-style input-readonly">
        </div>
        <button type="submit" class="btn-save">Lưu thay đổi tên</button>
    </form>

    <div class="footer-link">
        <a href="index.php" class="btn-back">← Quay lại Bảng điều khiển</a>
    </div>
</div>

<script>
function previewAndSubmit(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('avatarPreview').src = e.target.result;
        };
        reader.readAsDataURL(input.files[0]);
        document.getElementById('avatarForm').submit();
    }
}
</script>
</body>
</html>
