<?php
ini_set('session.name', 'HS_SESSION');
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'student') {
    header("Location: ../trangdangnhap.php");
    exit();
}

// Đọc avatar từ database để luôn hiển thị đúng
include '../config.php';
$avatar_file = '';
$stmt_av = $conn->prepare("SELECT avatar FROM users WHERE id = ?");
$stmt_av->bind_param("i", $_SESSION['user_id']);
$stmt_av->execute();
$row_av = $stmt_av->get_result()->fetch_assoc();
if (!empty($row_av['avatar'])) {
    $avatar_file = $row_av['avatar'];
    $_SESSION['avatar'] = $avatar_file; // đồng bộ session
}

$avatar_src = !empty($avatar_file)
    ? "../uploads/" . htmlspecialchars($avatar_file)
    : "https://ui-avatars.com/api/?name=" . urlencode($_SESSION['ho_ten'] ?? 'HS') . "&background=0288d1&color=fff&bold=true&size=120";
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Hồ sơ cá nhân | Góc Học Tập</title>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { 
            background-color: #f4f7f6; 
            font-family: 'Nunito', sans-serif; 
            margin: 0; 
            display: flex; 
            justify-content: center; 
            align-items: center; 
            min-height: 100vh; 
        }
        
        .profile-card { 
            background: white; 
            width: 450px; 
            padding: 30px; 
            border-radius: 20px; 
            box-shadow: 0 10px 25px rgba(0,0,0,0.05); 
            text-align: center; 
        }

        .avatar-wrapper {
            position: relative;
            display: inline-block;
            margin-bottom: 15px;
        }

        .avatar-img { 
            width: 120px; 
            height: 120px; 
            border-radius: 50%; 
            object-fit: cover; 
            border: 4px solid #e1f5fe; 
            display: block;
        }

        .avatar-overlay {
            position: absolute;
            bottom: 0; right: 0;
            background: #0288d1;
            border-radius: 50%;
            width: 34px; height: 34px;
            display: flex; align-items: center; justify-content: center;
            cursor: pointer;
            border: 2px solid white;
            transition: background 0.2s;
        }
        .avatar-overlay:hover { background: #01579b; }
        .avatar-overlay svg { color: white; }

        input[type="file"] { display: none; }
        
        .info-group { 
            text-align: left; 
            margin-top: 10px; 
        }

        .info-group label { 
            display: block; 
            color: #78909c; 
            font-weight: 700; 
            font-size: 13px; 
            margin-bottom: 5px; 
        }
        
        .input-style {
            width: 100%; 
            padding: 12px; 
            margin-bottom: 15px; 
            border-radius: 8px; 
            border: 1px solid #eceff1; 
            font-family: 'Nunito', sans-serif; 
            font-weight: 600; 
            font-size: 15px; 
            box-sizing: border-box;
        }

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
            transition: 0.3s;
        }

        .btn-save:hover { background: #e68a00; }

        .footer-link {
            margin-top: 25px;
            padding-top: 20px;
            border-top: 1px solid #f1f1f1;
        }

        .btn-back {
            display: inline-block;
            color: #0288d1;
            text-decoration: none;
            font-weight: 700;
            font-size: 14px;
            transition: all 0.3s;
        }

        .btn-back:hover {
            color: #01579b;
            transform: translateX(-5px);
        }

        .hint-text {
            font-size: 12px;
            color: #90a4ae;
            margin-top: -10px;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <div class="profile-card">
        <h2>Hồ sơ của em</h2>
        
        <!-- Avatar với nút bút chì bên góc -->
        <form action="xuly_upload.php" method="POST" enctype="multipart/form-data" id="avatarForm">
            <input type="hidden" name="type" value="change_avatar">
            <div class="avatar-wrapper">
                <img src="<?php echo $avatar_src; ?>" class="avatar-img" id="avatarPreview"
                     onerror="this.src='https://ui-avatars.com/api/?name=<?php echo urlencode($_SESSION['ho_ten'] ?? 'HS'); ?>&background=0288d1&color=fff&bold=true&size=120'">
                <label class="avatar-overlay" for="avatarInput" title="Đổi ảnh đại diện">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                    </svg>
                </label>
                <input type="file" id="avatarInput" name="avatar" accept="image/*" onchange="previewAndSubmit(this)">
            </div>
            <p class="hint-text">Nhấp vào biểu tượng bút để thay ảnh</p>
        </form>

        <form action="xuly_upload.php" method="POST">
            <input type="hidden" name="type" value="update_profile">
            
            <div class="info-group">
                <label>Họ và tên</label>
                <input type="text" name="ho_ten_moi" class="input-style" value="<?php echo htmlspecialchars($_SESSION['ho_ten'] ?? ''); ?>">
                
                <label>Vai trò</label>
                <input type="text" value="Học sinh" readonly class="input-style" style="background-color: #f8fafb; color: #b0bec5;">
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
            // Tự động submit
            document.getElementById('avatarForm').submit();
        }
    }
    </script>
</body>
</html>
