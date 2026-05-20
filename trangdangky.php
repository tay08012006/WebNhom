<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng ký | Góc Học Tập</title>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        body { background: linear-gradient(135deg, #e0f7fa 0%, #bbdefb 100%); min-height: 100vh; display: flex; justify-content: center; align-items: center; margin: 0; font-family: 'Nunito', sans-serif; padding: 20px 0; }
        .login-wrapper { background: #ffffff; width: 100%; max-width: 450px; padding: 40px; border-radius: 20px; box-shadow: 0 10px 25px rgba(0,0,0,0.1); }
        .logo-area { text-align: center; margin-bottom: 30px; }
        .logo-area h2 { color: #0277bd; margin: 0; font-size: 28px; font-weight: 800; text-transform: uppercase; letter-spacing: 1px; }
        .logo-area p { color: #78909c; margin-top: 5px; font-weight: 600; }
        
        .role-tabs { display: flex; background: #f1f8e9; border-radius: 12px; padding: 5px; margin-bottom: 25px; }
        .role-tab { flex: 1; text-align: center; padding: 12px; cursor: pointer; border-radius: 10px; font-weight: 700; color: #78909c; transition: 0.3s; }
        .role-tab.active { background: #0288d1; color: #ffffff; box-shadow: 0 4px 10px rgba(2, 136, 209, 0.2); }
        
        .input-group { margin-bottom: 15px; }
        .input-group label { display: block; margin-bottom: 8px; font-weight: 700; color: #455a64; font-size: 14px; }
        .input-group input { width: 100%; padding: 12px 14px; border: 2px solid #e1f5fe; border-radius: 12px; outline: none; box-sizing: border-box; transition: 0.3s; font-size: 15px; }
        .input-group input:focus { border-color: #03a9f4; background-color: #f1faff; }
        
        .btn-submit { width: 100%; padding: 16px; background: #0288d1; color: white; border: none; border-radius: 12px; font-weight: 800; cursor: pointer; font-size: 16px; transition: 0.3s; margin-top: 10px; }
        .btn-submit:hover { background: #0277bd; transform: translateY(-2px); }
        
        .footer-links { text-align: center; margin-top: 20px; font-size: 14px; color: #546e7a; }
        .footer-links a { color: #0288d1; text-decoration: none; font-weight: 700; }
        
        .btn-login-back { display: inline-block; margin-top: 12px; padding: 10px 20px; background-color: #2e7d32; color: white; text-decoration: none; border-radius: 8px; font-weight: 700; transition: 0.3s; }
        .btn-login-back:hover { background-color: #1b5e20; }

        /* CSS cho tính năng Ẩn/Hiện mật khẩu */
        .password-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }
        .password-wrapper input {
            padding-right: 45px; /* Chừa chỗ cho icon con mắt */
        }
        .toggle-password {
            position: absolute;
            right: 15px;
            cursor: pointer;
            color: #78909c;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: 0.3s;
        }
        .toggle-password:hover {
            color: #0288d1;
        }
    </style>
</head>
<body>
<div class="login-wrapper">
    <div class="logo-area">
        <h2>Góc Học Tập</h2>
        <p id="welcome-text">Tạo tài khoản Học sinh mới</p>
    </div>
    
    <div class="role-tabs">
        <div class="role-tab active" id="tab-student" onclick="switchRole('student')">Học sinh</div>
        <div class="role-tab" id="tab-teacher" onclick="switchRole('teacher')">Giáo viên</div>
    </div>
    
    <form action="xulydk.php" method="POST">
        <div class="input-group">
            <label>Họ và tên</label>
            <input type="text" name="hoten" required placeholder="Nhập họ và tên của bạn">
        </div>
        <div class="input-group">
            <label>Địa chỉ Email</label>
            <input type="email" name="email" required placeholder="vidu@gmail.com">
        </div>
        
        <div class="input-group">
            <label>Mật khẩu</label>
            <div class="password-wrapper">
                <input type="password" name="password" id="reg-pwd" required placeholder="********">
                <span class="toggle-password" onclick="togglePassword('reg-pwd', this)" title="Hiển thị mật khẩu">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                </span>
            </div>
        </div>
        <div class="input-group">
            <label>Nhập lại mật khẩu</label>
            <div class="password-wrapper">
                <input type="password" name="confirm_password" id="reg-confirm-pwd" required placeholder="********">
                <span class="toggle-password" onclick="togglePassword('reg-confirm-pwd', this)" title="Hiển thị mật khẩu">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                </span>
            </div>
        </div>

        <?php if (isset($_GET['error'])): ?>
            <div style="color: #d32f2f; background-color: #ffebee; padding: 12px; border-radius: 8px; text-align: center; margin-bottom: 15px; font-weight: 700; font-size: 14px;">
                <?= htmlspecialchars($_GET['error']) ?>
            </div>
        <?php endif; ?>

        <input type="hidden" name="role" id="role-input" value="student">
        <button type="submit" class="btn-submit" id="btn-register">Đăng ký Học sinh</button>
        <?php if (isset($_GET['success'])): ?>
            <div style="text-align:center; margin-top:20px; padding:18px; background:#f0fdf4; border:1px solid #bbf7d0; border-radius:12px;">
                <div style="color:#16a34a; font-weight:800; font-size:17px; margin-bottom:8px;">
                     <?= htmlspecialchars($_GET['success']) ?>
                </div>
                <p style="color:#64748b; font-size:14px; font-weight:600; margin:0 0 12px 0;">
                    Tự động chuyển sang đăng nhập sau
                    <span id="dem-nguoc" style="color:#0284c7; font-weight:800; font-size:16px;"> 5 </span>giây...
                </p>
            </div>
            <script>
                // Ẩn nút submit khi đã đăng ký thành công
                document.getElementById('btn-register').style.display = 'none';

                let soGiay = 5;
                const span = document.getElementById('dem-nguoc');
                const demNguoc = setInterval(function() {
                    soGiay--;
                    span.textContent = ' ' + soGiay + ' ';
                    if (soGiay <= 0) {
                        clearInterval(demNguoc);
                        window.location.href = 'trangdangnhap.php';
                    }
                }, 1000);
            </script>
        <?php endif; ?>
    </form>
    
    <div class="footer-links">
        Đã có tài khoản? <a href="trangdangnhap.php">Đăng nhập ngay</a>
    </div>
</div>

<script>
    function switchRole(role) {
        const tabStudent = document.getElementById('tab-student');
        const tabTeacher = document.getElementById('tab-teacher');
        const btnRegister = document.getElementById('btn-register');
        const roleInput = document.getElementById('role-input');
        const welcomeText = document.getElementById('welcome-text');
        
        if (role === 'student') {
            tabStudent.classList.add('active');
            tabTeacher.classList.remove('active');
            btnRegister.innerText = "Đăng ký Học sinh";
            roleInput.value = "student";
            welcomeText.innerText = "Tạo tài khoản Học sinh mới";
        } else {
            tabTeacher.classList.add('active');
            tabStudent.classList.remove('active');
            btnRegister.innerText = "Đăng ký Giáo viên";
            roleInput.value = "teacher";
            welcomeText.innerText = "Tạo tài khoản Giáo viên mới";
        }
    }
    function togglePassword(inputId, iconSpan) {
        const input = document.getElementById(inputId);
        
        // Icon mở mắt (Hiển thị mật khẩu)
        const eyeOpen = `<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>`;
        
        // Icon nhắm mắt (Ẩn mật khẩu)
        const eyeClosed = `<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line></svg>`;

        if (input.type === "password") {
            input.type = "text";
            iconSpan.innerHTML = eyeClosed;
            iconSpan.title = "Ẩn mật khẩu";
        } else {
            input.type = "password";
            iconSpan.innerHTML = eyeOpen;
            iconSpan.title = "Hiển thị mật khẩu";
        }
    }
</script>
</body>
</html>