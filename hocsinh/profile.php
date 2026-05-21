<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'student') {
    header("Location: ../trangdangnhap.php");
    exit();
}
$avatar = isset($_SESSION['avatar']) ? $_SESSION['avatar'] : "../uploads/default-avatar.png";
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

        .avatar-img { 
            width: 120px; 
            height: 120px; 
            border-radius: 50%; 
            object-fit: cover; 
            border: 4px solid #e1f5fe; 
            margin-bottom: 15px; 
        }
        
        .btn-upload { 
            background: #0288d1; 
            color: white; 
            padding: 8px 18px; 
            border-radius: 8px; 
            cursor: pointer; 
            display: inline-block; 
            font-weight: 700; 
            font-size: 14px; 
            margin-bottom: 20px; 
            transition: 0.3s; 
        }

        .btn-upload:hover { 
            background: #01579b; 
        }
        
        input[type="file"] { 
            display: none; 
        }
        
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

        .btn-save:hover { 
            background: #e68a00; 
        }

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
    </style>
</head>
<body>
    <div class="profile-card">
        <h2>Hồ sơ của em</h2>
        
        <img src="<?php echo $avatar; ?>" class="avatar-img">
        <form action="xuly_upload.php" method="POST" enctype="multipart/form-data">
            <label class="btn-upload">
                Đổi ảnh đại diện
                <input type="file" name="avatar" accept="image/*" onchange="this.form.submit()">
            </label>
            <input type="hidden" name="type" value="change_avatar">
        </form>

        <form action="xuly_upload.php" method="POST">
            <input type="hidden" name="type" value="update_profile">
            
            <div class="info-group">
                <label>Họ và tên</label>
                <input type="text" name="ho_ten_moi" class="input-style" value="<?php echo $_SESSION['ho_ten']; ?>">
                
                <label>Vai trò</label>
                <input type="text" value="Học sinh" readonly class="input-style" style="background-color: #f8fafb; color: #b0bec5;">
            </div>

            <button type="submit" class="btn-save">Lưu thay đổi tên</button>
        </form>

        <div class="footer-link">
            <a href="index.php" class="btn-back">← Quay lại Bảng điều khiển</a>
        </div>
    </div>
</body>
</html>