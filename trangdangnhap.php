<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Đăng nhập | Góc Học Tập</title>

<link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

<style>

*,
*::before,
*::after{
    margin:0;
    padding:0;
    box-sizing:border-box;
}
:root{
    --blue:#1877f2;
    --blue-dark:#1565d8;
    --blue-soft:#e8f0fe;

    --orange:#ff7043;

    --text:#17172b;
    --muted:#718096;

    --border:#dde3ea;

    --bg-left:#f4f8ff;

    --white:#ffffff;
}
body{
    font-family:'Be Vietnam Pro',sans-serif;
    min-height:100vh;
    display:flex;
    overflow:hidden;
    background:#fff;
}
   LEFT PANEL

.left-panel{
    flex:1;

    background:var(--bg-left);

    position:relative;

    display:flex;
    flex-direction:column;

    justify-content:center;
    align-items:flex-start;

    padding:55px 80px;

    overflow:hidden;
}
.left-panel::before{
    content:'';

    position:absolute;

    width:550px;
    height:550px;

    border-radius:50%;

    background:
    radial-gradient(
        circle,
        rgba(24,119,242,0.08) 0%,
        transparent 70%
    );
    top:50%;
    right:-120px;

    transform:translateY(-50%);
}
.dots-bg{
    position:absolute;
    inset:0;

    opacity:0.18;

    background-image:
    radial-gradient(circle,#1877f2 1px,transparent 1px);

    background-size:28px 28px;

    mask-image:
    radial-gradient(
        ellipse 60% 60% at 78% 50%,
        black 0%,
        transparent 80%
    );
}
   BRAND

.brand{
    display:flex;
    align-items:center;
    gap:16px;

    margin-bottom:55px;

    position:relative;
    z-index:2;
}
.brand-icon{
    width:66px;
    height:66px;

    border-radius:20px;

    background:var(--blue);

    display:flex;
    align-items:center;
    justify-content:center;

    box-shadow:0 12px 25px rgba(24,119,242,0.35);
}
.brand-name{
    font-size:25px;
    font-weight:900;
    letter-spacing:-0.7px;

    color:var(--text);
}
.brand-tagline{
    margin-top:2px;

    font-size:13px;
    font-weight:600;

    color:var(--muted);
}
   HERO

.headline{
    font-size:clamp(45px,5vw,72px);

    line-height:1.06;

    font-weight:900;

    letter-spacing:-2.5px;

    color:var(--text);

    margin-bottom:24px;

    max-width:600px;

    position:relative;
    z-index:2;
}
.hl-blue{
    color:var(--blue);
}
.hl-orange{
    color:var(--orange);
}
.sub-headline{
    max-width:430px;

    font-size:15px;
    line-height:1.8;

    color:var(--muted);

    font-weight:500;

    margin-bottom:0;

    position:relative;
    z-index:2;
}
   MIDDLE SUPPORT PANEL

.middle-panel{
    width:340px;

    flex-shrink:0;

    background:var(--bg-left);

    border-left:1px solid rgba(24,119,242,0.12);

    display:flex;
    flex-direction:column;

    justify-content:flex-start;

    padding:95px 26px 30px;
}
.mp-title{
    display:flex;
    align-items:center;
    gap:10px;

    margin-bottom:24px;

    font-size:13px;
    font-weight:800;

    text-transform:uppercase;
    letter-spacing:1px;

    color:var(--blue);
}
.mp-title::before{
    content:'';

    width:22px;
    height:3px;

    border-radius:10px;

    background:var(--blue);
}
.mp-item{
    display:flex;
    align-items:center;
    gap:16px;

    width:100%;

    text-decoration:none;

    padding:16px;

    border-radius:18px;

    transition:0.25s;
}
.mp-item:hover{
    background:rgba(24,119,242,0.08);
    transform:translateX(3px);
}
.mp-divider{
    height:1px;

    background:rgba(24,119,242,0.08);

    margin:4px 10px;
}
.mp-ico{
    width:56px;
    height:56px;

    border-radius:16px;

    display:flex;
    align-items:center;
    justify-content:center;

    flex-shrink:0;

    font-size:24px;
}
.mp-phone{
    background:rgba(255,112,67,0.14);
}

.mp-zalo{
    background:rgba(24,119,242,0.12);
}

.mp-fb{
    background:rgba(103,80,164,0.14);
}

.mp-email{
    background:rgba(255,193,7,0.15);
}

.mp-info{
    display:flex;
    flex-direction:column;

    flex:1;
    min-width:0;
}

.mp-lbl{
    font-size:12px;
    font-weight:600;

    color:var(--muted);

    margin-bottom:4px;
}

.mp-val{
    font-size:16px;
    font-weight:800;

    color:var(--text);

    white-space:normal;
    overflow:visible;
    text-overflow:unset;

    word-break:break-word;

    line-height:1.5;
}
   RIGHT PANEL

.right-panel{
    width:520px;

    flex-shrink:0;

    background:#fff;

    border-left:1px solid var(--border);

    display:flex;
    align-items:center;
    justify-content:center;

    padding:40px 48px;
}
.form-wrap{
    width:100%;
    max-width:390px;
}
.form-title{
    font-size:45px;

    line-height:1.1;

    font-weight:900;

    letter-spacing:-1.5px;

    color:var(--text);

    margin-bottom:10px;
}
.form-sub{
    font-size:15px;
    font-weight:500;

    color:var(--muted);

    margin-bottom:28px;
}
   ROLE TABS

.role-tabs{
    display:flex;

    background:#f4f6f8;

    border-radius:16px;

    padding:5px;

    margin-bottom:24px;
}
.role-tab{
    flex:1;

    text-align:center;

    padding:13px 10px;

    border-radius:12px;

    font-size:15px;
    font-weight:700;

    color:var(--muted);

    cursor:pointer;

    transition:0.2s;
}
.role-tab.active{
    background:#fff;

    color:var(--blue);

    box-shadow:0 4px 12px rgba(0,0,0,0.08);
}
   INPUTS

.input-field{
    width:100%;

    padding:15px 16px;

    border:1.5px solid var(--border);

    border-radius:14px;

    font-size:15px;

    font-family:'Be Vietnam Pro',sans-serif;

    outline:none;

    transition:0.2s;

    margin-bottom:16px;
}
.input-field:focus{
    border-color:var(--blue);

    box-shadow:0 0 0 4px rgba(24,119,242,0.1);
}
.input-field::placeholder{
    color:#b0bec5;
}
.pw-wrap{
    position:relative;

    margin-bottom:16px;
}
.pw-wrap .input-field{
    margin-bottom:0;
    padding-right:50px;
}
.toggle-pw{
    position:absolute;

    right:16px;
    top:50%;

    transform:translateY(-50%);

    color:#b0bec5;

    cursor:pointer;

    display:flex;
    align-items:center;

    transition:0.2s;
}
.toggle-pw:hover{
    color:var(--blue);
}
   BUTTONS

.btn-login{
    width:100%;

    border:none;

    border-radius:14px;

    padding:15px;

    background:var(--blue);
    color:#fff;

    font-size:16px;
    font-weight:800;

    font-family:'Be Vietnam Pro',sans-serif;

    cursor:pointer;

    transition:0.2s;

    box-shadow:0 10px 24px rgba(24,119,242,0.3);
}
.btn-login:hover{
    background:var(--blue-dark);

    transform:translateY(-1px);
}
.forgot-link{
    display:block;

    text-align:center;

    margin:16px 0 6px;

    text-decoration:none;

    color:var(--blue);

    font-size:14px;
    font-weight:700;
}
.forgot-link:hover{
    text-decoration:underline;
}
.divider{
    display:flex;
    align-items:center;
    gap:10px;

    margin:16px 0;
}
.divider::before,
.divider::after{
    content:'';

    flex:1;
    height:1px;

    background:var(--border);
}
.divider span{
    font-size:12px;
    font-weight:600;

    color:var(--muted);
}
.btn-register{
    width:100%;

    padding:15px;

    border-radius:14px;

    border:1.5px solid var(--blue);

    background:#fff;
    color:var(--blue);

    font-size:15px;
    font-weight:800;

    font-family:'Be Vietnam Pro',sans-serif;

    cursor:pointer;

    transition:0.2s;
}
.btn-register:hover{
    background:var(--blue-soft);
}
.footer-note{
    text-align:center;

    margin-top:24px;

    font-size:12px;
    line-height:1.7;

    color:#a0aec0;
}
.footer-note a{
    text-decoration:none;
    color:#718096;
}

.footer-note a:hover{
    text-decoration:underline;
}
   ALERT

.alert{
    padding:12px 14px;

    border-radius:12px;

    margin-bottom:18px;

    text-align:center;

    font-size:13px;
    font-weight:600;
}
.alert-error{
    background:#fff1f1;
    color:#c0392b;
    border:1px solid #ffd0d0;
}
.alert-success{
    background:#f0fff4;
    color:#2f855a;
    border:1px solid #bbf7d0;
}
   RESPONSIVE

@media(max-width:1200px){

    .middle-panel{
        width:300px;
    }
}
@media(max-width:1050px){

    .middle-panel{
        display:none;
    }
}
@media(max-width:860px){

    .left-panel{
        display:none;
    }

    .right-panel{
        width:100%;
        border:none;
    }

    body{
        background:#f4f8ff;
    }
}
</style>
</head>

<body>

     LEFT PANEL

<div class="left-panel">

    <div class="dots-bg"></div>

    <!-- BRAND -->
    <div class="brand">

        <div class="brand-icon">

            <svg width="30" height="30" viewBox="0 0 28 28" fill="none">

                <rect x="5" y="4"
                      width="15"
                      height="19"
                      rx="3"
                      fill="white"
                      opacity="0.95"/>

                <rect x="5" y="4"
                      width="3"
                      height="19"
                      rx="1.5"
                      fill="white"/>

                <line x1="10" y1="9"
                      x2="18" y2="9"
                      stroke="#1877f2"
                      stroke-width="1.5"
                      stroke-linecap="round"/>

                <line x1="10" y1="13"
                      x2="18" y2="13"
                      stroke="#1877f2"
                      stroke-width="1.5"
                      stroke-linecap="round"/>

                <line x1="10" y1="17"
                      x2="15" y2="17"
                      stroke="#1877f2"
                      stroke-width="1.5"
                      stroke-linecap="round"/>

            </svg>

        </div>

        <div>
            <div class="brand-name">
                Góc Học Tập
            </div>

            <div class="brand-tagline">
                Nền tảng học tập thông minh
            </div>

        </div>

    </div>

    <!-- HERO -->
    <div class="headline">

        Khám phá<br>

        <span class="hl-blue">
            những bài học
        </span><br>

        <span class="hl-orange">
            mới mỗi ngày.
        </span>

    </div>

    <div class="sub-headline">

        Học cùng thầy cô, kết nối với bạn bè,
        chinh phục mọi môn học —
        tất cả trong một nơi duy nhất.

    </div>

</div>

     SUPPORT PANEL

<div class="middle-panel">

    <div class="mp-title">
        Liên hệ hỗ trợ
    </div>

    <!-- PHONE -->
    <a class="mp-item"
       href="tel:0766700574">

        <div class="mp-ico mp-phone">
            📞
        </div>

        <div class="mp-info">

            <div class="mp-lbl">
                Điện thoại
            </div>

            <div class="mp-val">
                0766700574
            </div>

        </div>

    </a>

    <div class="mp-divider"></div>

    <!-- ZALO -->
    <a class="mp-item"
       href="https://zalo.me/0766700574"
       target="_blank">

        <div class="mp-ico mp-zalo">
            💬
        </div>

        <div class="mp-info">

            <div class="mp-lbl">
                Zalo hỗ trợ
            </div>

            <div class="mp-val">
                0766 700 574
            </div>

        </div>

    </a>

    <div class="mp-divider"></div>

    <!-- FACEBOOK -->
    <a class="mp-item"
       href="https://facebook.com/gochoctap"
       target="_blank">

        <div class="mp-ico mp-fb">
            👤
        </div>

        <div class="mp-info">

            <div class="mp-lbl">
                Facebook
            </div>

            <div class="mp-val">
                Góc Học Tập Official
            </div>

        </div>

    </a>

    <div class="mp-divider"></div>

    <!-- EMAIL -->
    <a class="mp-item"
       href="mailto:support@gochoctap.vn">

        <div class="mp-ico mp-email">
            ✉️
        </div>

        <div class="mp-info">

            <div class="mp-lbl">
                Email hỗ trợ
            </div>

            <div class="mp-val">
                support@gochoctap.vn
            </div>

        </div>

    </a>

</div>

     RIGHT PANEL

<div class="right-panel">

    <div class="form-wrap">

        <div class="form-title">
            Đăng nhập vào Góc Học Tập
        </div>

        <div class="form-sub">
            Chào mừng bạn quay trở lại! 
        </div>

        <!-- ROLE -->
        <div class="role-tabs">

            <div class="role-tab active"
                 id="tab-student"
                 onclick="switchRole('student')">

                Học sinh

            </div>

            <div class="role-tab"
                 id="tab-teacher"
                 onclick="switchRole('teacher')">

                Giáo viên

            </div>

        </div>

        <!-- ALERT -->
        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-error">
                <?= htmlspecialchars($_GET['error']) ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success">
                ✅ <?= htmlspecialchars($_GET['success']) ?>
            </div>
        <?php endif; ?>

        <!-- FORM -->
        <form action="xulidn.php" method="POST">

            <input type="hidden"
                   name="role"
                   id="role-input"
                   value="student">

            <input class="input-field"
                   type="email"
                   name="email"
                   required
                   autofocus
                   placeholder="Email của bạn">

            <div class="pw-wrap">

                <input class="input-field"
                       type="password"
                       name="password"
                       id="login-pwd"
                       required
                       placeholder="Mật khẩu">

                <span class="toggle-pw"
                      onclick="togglePw('login-pwd',this)">

                    <svg width="19"
                         height="19"
                         viewBox="0 0 24 24"
                         fill="none"
                         stroke="currentColor"
                         stroke-width="2"
                         stroke-linecap="round"
                         stroke-linejoin="round">

                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                        <circle cx="12" cy="12" r="3"/>

                    </svg>

                </span>

            </div>

            <button type="submit"
                    class="btn-login"
                    id="btn-login">

                Đăng nhập

            </button>

        </form>

        <a href="quenmatkhau.php"
           class="forgot-link">

           Quên mật khẩu?

        </a>

        <div class="divider">
            <span>hoặc</span>
        </div>

        <button class="btn-register"
                onclick="window.location.href='trangdangky.php'">

            Tạo tài khoản mới

        </button>

        <div class="footer-note">

            Bằng cách đăng nhập,
            bạn đồng ý với<br>

            <a href="#">
                Điều khoản sử dụng
            </a>

            &

            <a href="#">
                Chính sách bảo mật
            </a>

        </div>

    </div>

</div>

<script>

function switchRole(role){

    document
    .getElementById('tab-student')
    .classList.toggle(
        'active',
        role === 'student'
    );
    document
    .getElementById('tab-teacher')
    .classList.toggle(
        'active',
        role === 'teacher'
    );
    document
    .getElementById('role-input')
    .value = role;

    document
    .getElementById('btn-login')
    .textContent =
        role === 'teacher'
        ? 'Đăng nhập '
        : 'Đăng nhập';
}
function togglePw(id,span){

    const inp =
    document.getElementById(id);

    const eyeOpen = `
    <svg width="19" height="19"
         viewBox="0 0 24 24"
         fill="none"
         stroke="currentColor"
         stroke-width="2"
         stroke-linecap="round"
         stroke-linejoin="round">

        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
        <circle cx="12" cy="12" r="3"/>

    </svg>`;

    const eyeClose = `
    <svg width="19" height="19"
         viewBox="0 0 24 24"
         fill="none"
         stroke="currentColor"
         stroke-width="2"
         stroke-linecap="round"
         stroke-linejoin="round">

        <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20
                 c-7 0-11-8-11-8
                 a18.45 18.45 0 0 1 5.06-5.94"/>

        <path d="M9.9 4.24A9.12 9.12 0 0 1 12 4
                 c7 0 11 8 11 8
                 a18.5 18.5 0 0 1-2.16 3.19"/>

        <path d="M14.12 14.12a3 3 0 1 1-4.24-4.24"/>

        <line x1="1" y1="1" x2="23" y2="23"/>

    </svg>`;

    if(inp.type === 'password'){

        inp.type = 'text';

        span.innerHTML = eyeClose;

    }else{

        inp.type = 'password';

        span.innerHTML = eyeOpen;
    }
}
</script>

</body>
</html>