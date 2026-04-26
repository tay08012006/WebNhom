<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập | Góc Học Tập</title>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        /* Bộ mã màu bạn yêu thích */
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
            margin-bottom: 30px;
        }

        .logo-area h2 {
            color: #0277bd; /* Màu xanh đậm thương hiệu */
            margin: 0;
            font-size: 28px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .logo-area p {
            color: #78909c;
            margin-top: 5px;
            font-weight: 600;
        }

        /* Tab chuyển đổi Học sinh / Giáo viên */
        .role-tabs {
            display: flex;
            background: #f1f8e9;
            border-radius: 12px;
            padding: 5px;
            margin-bottom: 25px;
        }

        .role-tab {
            flex: 1;
            text-align: center;
            padding: 12px;
            cursor: pointer;
            border-radius: 10px;
            font-weight: 700;
            color: #78909c;
            transition: 0.3s;
        }

        .role-tab.active {
            background: #0288d1;
            color: #ffffff;
            box-shadow: 0 4px 10px rgba(2, 136, 209, 0.2);
        }

        /* Form styling */
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
        }

        .btn-submit:hover {
            background: #0277bd;
            transform: translateY(-2px);
        }

        /* Link hỗ trợ */
        .footer-links {
            text-align: center;
            margin-top: 20px;
            font-size: 13px;
            color: #90a4ae;
        }

        .footer-links a {
            color: #0288d1;
            text-decoration: none;
            font-weight: 700;
        }
    </style>
</head>
<body>

<div class="login-wrapper">
    <div class="logo-area">
        <h2>Góc Học Tập</h2>
        <p id="welcome-text">Chào mừng bạn đã quay trở lại!</p>
    </div>

    <div class="role-tabs">
        <div class="role-tab active" id="tab-student" onclick="switchRole('student')">Học sinh</div>
        <div class="role-tab" id="tab-teacher" onclick="switchRole('teacher')">Giáo viên</div>
    </div>

    <form action="xuly_dangnhap.php" method="POST">
        <div class="input-group">
            <label>Địa chỉ Email</label>
            <input type="email" name="email" required placeholder="nhapemail@sv.edu.vn">
        </div>

        <div class="input-group">
            <label>Mật khẩu</label>
            <input type="password" name="password" required placeholder="********">
        </div>

        <input type="hidden" name="role" id="role-input" value="student">

        <button type="submit" class="btn-submit" id="btn-login">Đăng nhập Học sinh</button>
    </form>

    <div class="footer-links">
        Quên mật khẩu? <a href="#">Nhấn vào đây</a>
    </div>
</div>

<script>
    function switchRole(role) {
        const tabStudent = document.getElementById('tab-student');
        const tabTeacher = document.getElementById('tab-teacher');
        const btnLogin = document.getElementById('btn-login');
        const roleInput = document.getElementById('role-input');
        const welcomeText = document.getElementById('welcome-text');

        if (role === 'student') {
            tabStudent.classList.add('active');
            tabTeacher.classList.remove('active');
            btnLogin.innerText = "Đăng nhập Học sinh";
            roleInput.value = "student";
            welcomeText.innerText = "Chào mừng Học sinh quay lại!";
            btnLogin.style.background = "#0288d1";
        } else {
            tabTeacher.classList.add('active');
            tabStudent.classList.remove('active');
            btnLogin.innerText = "Đăng nhập Giáo viên";
            roleInput.value = "teacher";
            welcomeText.innerText = "Kính chào Thầy/Cô giáo!";
            // Đổi màu nhẹ sang xanh lá cho giáo viên nếu bạn muốn, hoặc giữ nguyên xanh dương
            // btnLogin.style.background = "#2e7d32"; 
        }
    }
</script>

</body>
</html>