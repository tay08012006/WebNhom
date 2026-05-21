<?php
session_start();
$host = "localhost";
$user = "root";       
$pass = "";           
$dbname = "quanly_hoctap";

$conn = mysqli_connect($host, $user, $pass, $dbname);
if (!$conn) die("Lỗi kết nối: " . mysqli_connect_error());
mysqli_set_charset($conn, "utf8");

$step = 1;
$message = "";
$email = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = mysqli_real_escape_string($conn, trim($_POST['email'] ?? ''));

    // Bước 1: Kiểm tra email và môn học yêu thích
    if (isset($_POST['monhoc_yeuthich'])) {
        $monhoc = mysqli_real_escape_string($conn, trim($_POST['monhoc_yeuthich']));
        
        $sql = "SELECT * FROM users WHERE email = '$email' AND monhoc_yeuthich = '$monhoc'";
        $result = mysqli_query($conn, $sql);

        if (mysqli_num_rows($result) > 0) {
            $_SESSION['reset_email'] = $email;
            $step = 2; // Chuyển sang bước đổi mật khẩu
        } else {
            $message = "❌ Email hoặc Môn học yêu thích không đúng!";
        }
    }
    // Bước 2: Đổi mật khẩu
    elseif (isset($_POST['new_password'])) {
        if (!isset($_SESSION['reset_email'])) {
            header("Location: quenmatkhau.php");
            exit;
        }

        $new_pass = trim($_POST['new_password']);
        $confirm_pass = trim($_POST['confirm_password']);

        if ($new_pass !== $confirm_pass) {
            $message = "❌ Mật khẩu xác nhận không khớp!";
            $step = 2;
        } else {
            $email_reset = $_SESSION['reset_email'];
            $sql_update = "UPDATE users SET matkhau = '$new_pass' WHERE email = '$email_reset'";
            
            if (mysqli_query($conn, $sql_update)) {
                unset($_SESSION['reset_email']);
                $message = "✅ Đổi mật khẩu thành công! <a href='trangdangnhap.php'>Đăng nhập ngay</a>";
                $step = 3;
            } else {
                $message = "❌ Lỗi khi cập nhật mật khẩu!";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quên mật khẩu | Góc Học Tập</title>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #e0f7fa 0%, #bbdefb 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            font-family: 'Nunito', sans-serif;
            margin: 0;
        }
        .wrapper {
            background: #fff;
            max-width: 420px;
            width: 100%;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }
        .logo { text-align: center; margin-bottom: 25px; }
        .logo h2 { color: #0277bd; margin: 0; font-size: 28px; font-weight: 800; }
        .input-group { margin-bottom: 20px; }
        .input-group label { display: block; margin-bottom: 8px; font-weight: 700; color: #455a64; }
        .input-group input {
            width: 100%; padding: 14px; border: 2px solid #e1f5fe;
            border-radius: 12px; font-size: 15px; outline: none;
        }
        .input-group input:focus { border-color: #03a9f4; background: #f1faff; }
        .btn { width: 100%; padding: 16px; background: #0288d1; color: white; border: none; 
               border-radius: 12px; font-size: 16px; font-weight: 800; cursor: pointer; }
        .btn:hover { background: #0277bd; }
        .alert { padding: 15px; border-radius: 12px; margin-bottom: 20px; text-align: center; }
        .success { background: #e8f5e9; color: #2e7d32; }
        .error { background: #ffebee; color: #d32f2f; }
    </style>
</head>
<body>

<div class="wrapper">
    <div class="logo">
        <h2>Góc Học Tập</h2>
        <p>Khôi phục mật khẩu</p>
    </div>

    <?php if ($message): ?>
        <div class="alert <?= strpos($message, '✅') !== false ? 'success' : 'error' ?>">
            <?= $message ?>
        </div>
    <?php endif; ?>

    <?php if ($step == 1): ?>
    <form method="POST">
        <div class="input-group">
            <label>Địa chỉ Email</label>
            <input type="email" name="email" value="<?= htmlspecialchars($email) ?>" required placeholder="your@email.com">
        </div>
        <div class="input-group">
            <label>Môn học yêu thích / Chuyên môn</label>
            <input type="text" name="monhoc_yeuthich" required placeholder>
        </div>
        <button type="submit" class="btn">Tiếp tục</button>
    </form>

    <?php elseif ($step == 2): ?>
    <form method="POST">
        <input type="hidden" name="email" value="<?= htmlspecialchars($email) ?>">
        <div class="input-group">
            <label>Mật khẩu mới</label>
            <input type="password" name="new_password" required placeholder="Nhập mật khẩu mới">
        </div>
        <div class="input-group">
            <label>Xác nhận mật khẩu mới</label>
            <input type="password" name="confirm_password" required placeholder="Nhập lại mật khẩu">
        </div>
        <button type="submit" class="btn">Đổi mật khẩu</button>
    </form>

    <?php endif; ?>

    <p style="text-align:center; margin-top:20px;">
        <a href="trangdangnhap.php" style="color:#0288d1; text-decoration:none;">← Quay lại Đăng nhập</a>
    </p>
</div>

</body>
</html>