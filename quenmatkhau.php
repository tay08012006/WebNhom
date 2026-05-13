<?php
// Biến lưu thông báo khi người dùng bấm gửi
$success_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['email'])) {
    $email = htmlspecialchars($_POST['email']);
    // Sau này bạn có thể ghép code gửi email thật vào đây
    // Hiện tại chúng ta sẽ hiển thị một thông báo thành công giả lập
    $success_message = "Chúng tôi đã gửi hướng dẫn khôi phục mật khẩu đến email <strong>" . $email . "</strong>. Vui lòng kiểm tra hộp thư của bạn!";
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
        /* CSS giữ nguyên đồng bộ với trang Đăng nhập */
        body {
            background: linear-gradient(135deg, #e0f7fa 0%, #bbdefb 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 0;
            font-family: 'Nunito', sans-serif;
        }

        .login-wrapper {
            background: #ffffff;
            width: 100%;
            max-width: 400px;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }

        .logo-area {
            text-align: center;
            margin-bottom: 25px;
        }

        .logo-area h2 {
            color: #0277bd;
            margin: 0;
            font-size: 28px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .logo-area p {
            color: #78909c;
            margin-top: 10px;
            font-size: 15px;
            font-weight: 600;
            line-height: 1.5;
        }

        .input-group {
            margin-bottom: 20px;
        }

        .input-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 700;
            color: #455a64;
            font-size: 14px;
        }

        .input-group input {
            width: 100%;
            padding: 14px;
            border: 2px solid #e1f5fe;
            border-radius: 12px;
            outline: none;
            box-sizing: border-box;
            transition: 0.3s;
            font-size: 15px;
        }

        .input-group input:focus {
            border-color: #03a9f4;
            background-color: #f1faff;
        }

        .btn-submit {
            width: 100%;
            padding: 16px;
            background: #0288d1;
            color: white;
            border: none;
            border-radius: 12px;
            font-weight: 800;
            cursor: pointer;
            font-size: 16px;
            transition: 0.3s;
            box-shadow: 0 5px 15px rgba(2, 136, 209, 0.3);
            margin-top: 10px;
        }

        .btn-submit:hover {
            background: #0277bd;
            transform: translateY(-2px);
        }

        .footer-links {
            text-align: center;
            margin-top: 25px;
            font-size: 14px;
            color: #90a4ae;
        }

        .footer-links a {
            color: #0288d1;
            text-decoration: none;
            font-weight: 700;
            transition: 0.2s;
        }

        .footer-links a:hover {
            text-decoration: underline;
        }

        /* Khung thông báo thành công */
        .success-box {
            background: #e8f5e9;
            color: #2e7d32;
            padding: 15px;
            border-radius: 12px;
            margin-bottom: 20px;
            text-align: center;
            border: 1px solid #c8e6c9;
            font-size: 14.5px;
            line-height: 1.5;
        }
    </style>
</head>
<body>

<div class="login-wrapper">
    <div class="logo-area">
        <h2>Góc Học Tập</h2>
        <p>Đừng lo lắng! Hãy nhập email của bạn, chúng tôi sẽ giúp bạn lấy lại quyền truy cập.</p>
    </div>

    <?php if (!empty($success_message)): ?>
        <div class="success-box">
            <?php echo $success_message; ?>
        </div>
    <?php endif; ?>
    
    <form action="quenmatkhau.php" method="POST">
        <div class="input-group">
            <label>Địa chỉ Email đã đăng ký</label>
            <input type="email" name="email" required placeholder="nhapemail@sv.edu.vn">
        </div>

        <button type="submit" class="btn-submit">Gửi yêu cầu khôi phục</button>
    </form>

    <div class="footer-links">
        Nhớ ra mật khẩu rồi? <a href="trangdangnhap.php">Quay lại Đăng nhập</a>
    </div>
</div>

</body>
</html>