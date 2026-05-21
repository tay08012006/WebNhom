<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng ký | Góc Học Tập</title>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        * { box-sizing: border-box; }

        body {
            background: linear-gradient(135deg, #e0f7fa 0%, #bbdefb 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 0;
            font-family: 'Nunito', sans-serif;
            padding: 20px 0;
        }

        .login-wrapper {
            background: #ffffff;
            width: 100%;
            max-width: 450px;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }

        .logo-area { text-align: center; margin-bottom: 30px; }
        .logo-area h2 {
            color: #0277bd; margin: 0; font-size: 28px;
            font-weight: 800; text-transform: uppercase; letter-spacing: 1px;
        }
        .logo-area p { color: #78909c; margin-top: 5px; font-weight: 600; }

        /* Role Tabs */
        .role-tabs {
            display: flex; background: #f1f8e9;
            border-radius: 12px; padding: 5px; margin-bottom: 25px;
        }
        .role-tab {
            flex: 1; text-align: center; padding: 12px; cursor: pointer;
            border-radius: 10px; font-weight: 700; color: #78909c;
            transition: 0.3s; user-select: none;
        }
        .role-tab.active {
            background: #0288d1; color: #ffffff;
            box-shadow: 0 4px 10px rgba(2,136,209,0.25);
        }

        /* Input Groups */
        .input-group { margin-bottom: 16px; }
        .input-group label {
            display: block; margin-bottom: 8px;
            font-weight: 700; color: #455a64; font-size: 14px;
        }
        .input-group input {
            width: 100%; padding: 12px 14px; border: 2px solid #e1f5fe;
            border-radius: 12px; outline: none; transition: 0.3s;
            font-size: 15px; font-family: 'Nunito', sans-serif; color: #263238;
        }
        .input-group input:focus { border-color: #03a9f4; background-color: #f1faff; }
        .input-group input::placeholder { color: #b0bec5; }

        /* Gender Select */
        .gender-group { display: flex; gap: 10px; }
        .gender-option { flex: 1; position: relative; }
        .gender-option input[type="radio"] { display: none; }
        .gender-option label {
            display: flex; align-items: center; justify-content: center;
            gap: 8px; width: 100%; padding: 11px 14px;
            border: 2px solid #e1f5fe; border-radius: 12px; cursor: pointer;
            font-weight: 700; color: #78909c; font-size: 14px;
            transition: 0.25s; user-select: none; background: #fff; margin-bottom: 0;
        }
        .gender-option label:hover { border-color: #03a9f4; color: #0288d1; background: #f1faff; }
        .gender-option input[type="radio"]:checked + label {
            border-color: #0288d1; background: #e1f5fe; color: #0277bd;
            box-shadow: 0 2px 8px rgba(2,136,209,0.15);
        }
        .gender-icon { font-size: 18px; line-height: 1; }

        /* Password wrapper */
        .password-wrapper { position: relative; display: flex; align-items: center; }
        .password-wrapper input { padding-right: 48px; }
        .toggle-password {
            position: absolute; right: 14px; cursor: pointer; color: #90a4ae;
            display: flex; align-items: center; justify-content: center;
            transition: 0.3s; line-height: 0;
        }
        .toggle-password:hover { color: #0288d1; }

        /* Submit button */
        .btn-submit {
            width: 100%; padding: 16px; background: #0288d1; color: white;
            border: none; border-radius: 12px; font-weight: 800; cursor: pointer;
            font-size: 16px; font-family: 'Nunito', sans-serif; transition: 0.3s;
            margin-top: 6px; letter-spacing: 0.5px;
        }
        .btn-submit:hover {
            background: #0277bd; transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(2,119,189,0.3);
        }
        .btn-submit:active { transform: translateY(0); }

        /* Messages */
        .msg-error {
            color: #d32f2f; background-color: #ffebee; padding: 12px;
            border-radius: 8px; text-align: center; margin-bottom: 15px;
            font-weight: 700; font-size: 14px; border-left: 4px solid #d32f2f;
        }
        .msg-success { text-align: center; margin-top: 20px; }
        .msg-success p { color: #2e7d32; font-weight: 800; font-size: 16px; margin-bottom: 10px; }

        /* Footer */
        .footer-links { text-align: center; margin-top: 22px; font-size: 14px; color: #546e7a; }
        .footer-links a { color: #0288d1; text-decoration: none; font-weight: 700; }
        .footer-links a:hover { text-decoration: underline; }

        .btn-login-back {
            display: inline-block; margin-top: 10px; padding: 10px 24px;
            background-color: #2e7d32; color: white; text-decoration: none;
            border-radius: 8px; font-weight: 700; font-family: 'Nunito', sans-serif; transition: 0.3s;
        }
        .btn-login-back:hover { background-color: #1b5e20; }

        /* Countdown */
        .countdown-text { color: #546e7a; font-size: 13px; font-weight: 600; margin-top: 10px; }
        .countdown-text span { color: #0288d1; font-weight: 800; font-size: 15px; }
        .countdown-bar-wrap {
            width: 100%; height: 5px; background: #e1f5fe;
            border-radius: 99px; margin-top: 8px; overflow: hidden;
        }
        .countdown-bar {
            height: 100%; width: 100%; background: #0288d1;
            border-radius: 99px; transition: width 1s linear;
        }

        .divider { height: 1px; background: #e1f5fe; margin: 4px 0 16px; }
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

        <!-- Họ và tên -->
        <div class="input-group">
            <label>Họ và tên</label>
            <input type="text" name="hoten" required placeholder="Nhập họ và tên của bạn">
        </div>

        <!-- Giới tính -->
        <div class="input-group">
            <label>Giới tính</label>
            <div class="gender-group">
                <div class="gender-option">
                    <input type="radio" name="gioitinh" id="gender-male" value="nam" required>
                    <label for="gender-male"><span class="gender-icon">♂</span> Nam</label>
                </div>
                <div class="gender-option">
                    <input type="radio" name="gioitinh" id="gender-female" value="nu">
                    <label for="gender-female"><span class="gender-icon">♀</span> Nữ</label>
                </div>
                <div class="gender-option">
                    <input type="radio" name="gioitinh" id="gender-other" value="khac">
                    <label for="gender-other"><span class="gender-icon">⚬</span> Khác</label>
                </div>
            </div>
        </div>

        <!-- Email -->
        <div class="input-group">
            <label>Địa chỉ Email</label>
            <input type="email" name="email" required placeholder="your@gmail.com">
        </div>

        <!-- Mật khẩu -->
        <div class="input-group">
            <label>Mật khẩu</label>
            <div class="password-wrapper">
                <input type="password" name="password" id="reg-pwd" required placeholder="Tối thiểu 6 ký tự">
                <span class="toggle-password" onclick="togglePassword('reg-pwd', this)" title="Hiển thị mật khẩu">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                        <circle cx="12" cy="12" r="3"></circle>
                    </svg>
                </span>
            </div>
        </div>

        <!-- Nhập lại mật khẩu -->
        <div class="input-group">
            <label>Nhập lại mật khẩu</label>
            <div class="password-wrapper">
                <input type="password" name="confirm_password" id="reg-confirm-pwd" required placeholder="Nhập lại mật khẩu">
                <span class="toggle-password" onclick="togglePassword('reg-confirm-pwd', this)" title="Hiển thị mật khẩu">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                        <circle cx="12" cy="12" r="3"></circle>
                    </svg>
                </span>
            </div>
        </div>

        <!-- Môn học yêu thích / Chuyên môn giảng dạy -->
        <div class="input-group" id="subject-group">
            <label id="subject-label">Môn học yêu thích</label>
            <input type="text" name="monhoc_yeuthich" id="subject-input"
                   placeholder="Nhập môn học yêu thích của bạn">
        </div>

        <!-- Error message (PHP) -->
        <?php if (isset($_GET['error'])): ?>
        <div class="msg-error">
            <?= htmlspecialchars($_GET['error']) ?>
        </div>
        <?php endif; ?>

        <!-- Hidden role field -->
        <input type="hidden" name="role" id="role-input" value="student">

        <!-- Submit -->
        <button type="submit" class="btn-submit" id="btn-register">Đăng ký Học sinh</button>

        <!-- Success message (PHP) -->
        <?php if (isset($_GET['success'])): ?>
        <div class="msg-success" id="success-box">
            <p>✅ <?= htmlspecialchars($_GET['success']) ?></p>
            <div class="countdown-text">Tự động chuyển sau <span id="countdown-num">5</span> giây...</div>
            <div class="countdown-bar-wrap">
                <div class="countdown-bar" id="countdown-bar"></div>
            </div>
        </div>
        <?php endif; ?>

    </form>

    <div class="footer-links">
        Đã có tài khoản? <a href="trangdangnhap.php">Đăng nhập ngay</a>
    </div>
</div>

<script>
    // Auto redirect countdown after success
    (function() {
        const numEl = document.getElementById('countdown-num');
        const barEl = document.getElementById('countdown-bar');
        if (!numEl) return;

        let seconds = 5;
        barEl.style.width = '100%';
        setTimeout(() => { barEl.style.width = '0%'; }, 50);

        const timer = setInterval(() => {
            seconds--;
            numEl.textContent = seconds;
            if (seconds <= 0) {
                clearInterval(timer);
                window.location.href = 'trangdangnhap.php';
            }
        }, 1000);
    })();

    function switchRole(role) {
        const tabStudent   = document.getElementById('tab-student');
        const tabTeacher   = document.getElementById('tab-teacher');
        const btnRegister  = document.getElementById('btn-register');
        const roleInput    = document.getElementById('role-input');
        const welcomeText  = document.getElementById('welcome-text');
        const subjectLabel = document.getElementById('subject-label');
        const subjectInput = document.getElementById('subject-input');

        if (role === 'student') {
            tabStudent.classList.add('active');
            tabTeacher.classList.remove('active');
            btnRegister.innerText    = 'Đăng ký Học sinh';
            roleInput.value          = 'student';
            welcomeText.innerText    = 'Tạo tài khoản Học sinh mới';
            subjectLabel.innerText   = 'Môn học yêu thích';
            subjectInput.placeholder = 'Ví dụ: Toán, Ngữ Văn, Tiếng Anh...';
        } else {
            tabTeacher.classList.add('active');
            tabStudent.classList.remove('active');
            btnRegister.innerText    = 'Đăng ký Giáo viên';
            roleInput.value          = 'teacher';
            welcomeText.innerText    = 'Tạo tài khoản Giáo viên mới';
            subjectLabel.innerText   = 'Chuyên môn giảng dạy';
            subjectInput.placeholder = 'Ví dụ: Vật Lý, Hóa Học, Sinh Học...';
        }
    }

    function togglePassword(inputId, iconSpan) {
        const input = document.getElementById(inputId);
        const eyeOpen   = `<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>`;
        const eyeClosed = `<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line></svg>`;

        if (input.type === 'password') {
            input.type         = 'text';
            iconSpan.innerHTML = eyeClosed;
            iconSpan.title     = 'Ẩn mật khẩu';
        } else {
            input.type         = 'password';
            iconSpan.innerHTML = eyeOpen;
            iconSpan.title     = 'Hiển thị mật khẩu';
        }
    }
</script>
</body>
</html>