<?php
session_start();

require_once 'config.php';

$step    = isset($_SESSION['reset_step']) ? (int)$_SESSION['reset_step'] : 1;
$message = '';

// ──────────────────────────────────────────────
// Hàm tiện ích
// ──────────────────────────────────────────────
function maskEmail(string $email): string {
    $parts = explode('@', $email);
    if (count($parts) !== 2) return $email;
    $name    = $parts[0];
    $visible = mb_substr($name, 0, min(3, mb_strlen($name)), 'UTF-8');
    return $visible . str_repeat('*', max(2, mb_strlen($name, 'UTF-8') - 3)) . '@' . $parts[1];
}

// ──────────────────────────────────────────────
// Xử lý POST
// ──────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $action = $_POST['action'] ?? '';

    // ── BƯỚC 1: Xác minh email + môn học ──────
    if ($action === 'verify_identity') {
        $email  = trim($_POST['email'] ?? '');
        $monhoc = trim($_POST['monhoc_yeuthich'] ?? '');

        $stmt = $conn->prepare("SELECT id, email, role FROM users WHERE email = ? AND monhoc_yeuthich = ?");
        $stmt->bind_param("ss", $email, $monhoc);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            $message = ['type' => 'error', 'text' => '❌ Email hoặc Môn học yêu thích không đúng!'];
            $step = 1;
        } else {
            $user = $result->fetch_assoc();

            // Tạo OTP 6 số
            $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

            // Xoá OTP cũ rồi lưu mới
            $del = $conn->prepare("DELETE FROM otp_codes WHERE user_id = ?");
            $del->bind_param("i", $user['id']);
            $del->execute();

            $expires_db = date('Y-m-d H:i:s', time() + 300);
            $ins = $conn->prepare("INSERT INTO otp_codes (user_id, otp_code, expires_at) VALUES (?, ?, ?)");
            $ins->bind_param("iss", $user['id'], $otp, $expires_db);
            $ins->execute();

            // Lưu vào session
            $_SESSION['reset_step']     = 2;
            $_SESSION['reset_user_id']  = $user['id'];
            $_SESSION['reset_email']    = $user['email'];
            $_SESSION['reset_role']     = $user['role'];
            $_SESSION['reset_otp']      = $otp;
            $_SESSION['reset_expires']  = time() + 300;
            $_SESSION['reset_attempts'] = 0;

            // Gửi OTP qua email
            require_once 'lib/mailer.php';
            $mail_ok = smtp_send_otp($user['email'], $otp);
            $_SESSION['reset_mail_sent'] = $mail_ok;

            $step = 2;
            $message = ['type' => 'success', 'text' => '✅ Mã OTP đã được gửi đến email của bạn!'];
        }
    }

    // ── BƯỚC 2: Xác minh OTP ──────────────────
    elseif ($action === 'verify_otp') {
        if (empty($_SESSION['reset_user_id']) || $_SESSION['reset_step'] != 2) {
            session_unset(); header("Location: quenmatkhau.php"); exit;
        }

        // Gửi lại OTP
        if (isset($_POST['resend'])) {
            $user_id = (int)$_SESSION['reset_user_id'];
            $email   = $_SESSION['reset_email'];

            $new_otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

            $del = $conn->prepare("DELETE FROM otp_codes WHERE user_id = ?");
            $del->bind_param("i", $user_id);
            $del->execute();

            $expires_db = date('Y-m-d H:i:s', time() + 300);
            $ins = $conn->prepare("INSERT INTO otp_codes (user_id, otp_code, expires_at) VALUES (?, ?, ?)");
            $ins->bind_param("iss", $user_id, $new_otp, $expires_db);
            $ins->execute();

            $_SESSION['reset_otp']      = $new_otp;
            $_SESSION['reset_expires']  = time() + 300;
            $_SESSION['reset_attempts'] = 0;

            require_once 'lib/mailer.php';
            $_SESSION['reset_mail_sent'] = smtp_send_otp($email, $new_otp);

            $step = 2;
            $message = ['type' => 'success', 'text' => '✅ Mã OTP mới đã được gửi lại!'];

        } else {
            // Xác minh mã
            $digits  = $_POST['otp'] ?? [];
            $entered = is_array($digits) ? implode('', array_map('trim', $digits)) : trim($digits);

            $correct  = $_SESSION['reset_otp']     ?? '';
            $expires  = $_SESSION['reset_expires'] ?? 0;
            $attempts = (int)($_SESSION['reset_attempts'] ?? 0);

            if (strlen($entered) !== 6 || !ctype_digit($entered)) {
                $message = ['type' => 'error', 'text' => '❌ Mã OTP phải gồm đúng 6 chữ số!'];
                $step = 2;
            } elseif (time() > $expires) {
                $message = ['type' => 'error', 'text' => '❌ Mã OTP đã hết hạn! Vui lòng gửi lại.'];
                $step = 2;
            } elseif ($entered !== $correct) {
                $_SESSION['reset_attempts'] = $attempts + 1;
                $con = max(0, 3 - $_SESSION['reset_attempts']);
                $txt = "❌ Mã OTP không đúng!" . ($con > 0 ? " Còn $con lần thử." : " Vui lòng gửi lại mã mới.");
                $message = ['type' => 'error', 'text' => $txt];
                $step = 2;
            } else {
                // OTP đúng → chuyển sang bước đổi mật khẩu
                $user_id = (int)$_SESSION['reset_user_id'];
                $upd = $conn->prepare("UPDATE otp_codes SET used = 1 WHERE user_id = ? AND otp_code = ?");
                $upd->bind_param("is", $user_id, $entered);
                $upd->execute();

                $_SESSION['reset_step']     = 3;
                $_SESSION['reset_verified'] = true;
                unset($_SESSION['reset_otp'], $_SESSION['reset_expires'], $_SESSION['reset_attempts']);

                $step    = 3;
                $message = ['type' => 'success', 'text' => '✅ Xác minh thành công! Hãy đặt mật khẩu mới.'];
            }
        }
    }

    // ── BƯỚC 3: Đổi mật khẩu ──────────────────
    elseif ($action === 'reset_password') {
        if (empty($_SESSION['reset_verified']) || $_SESSION['reset_step'] != 3) {
            session_unset(); header("Location: quenmatkhau.php"); exit;
        }

        $new_pass     = $_POST['new_password']     ?? '';
        $confirm_pass = $_POST['confirm_password'] ?? '';

        if (strlen($new_pass) < 6) {
            $message = ['type' => 'error', 'text' => '❌ Mật khẩu phải có ít nhất 6 ký tự!'];
            $step = 3;
        } elseif ($new_pass !== $confirm_pass) {
            $message = ['type' => 'error', 'text' => '❌ Mật khẩu xác nhận không khớp!'];
            $step = 3;
        } else {
            $email_reset = $_SESSION['reset_email'];
            $hashed      = password_hash($new_pass, PASSWORD_DEFAULT);

            $upd = $conn->prepare("UPDATE users SET matkhau = ? WHERE email = ?");
            $upd->bind_param("ss", $hashed, $email_reset);

            if ($upd->execute()) {
                // Xoá toàn bộ session reset
                foreach (['reset_step','reset_user_id','reset_email','reset_role',
                          'reset_verified','reset_mail_sent'] as $k) {
                    unset($_SESSION[$k]);
                }
                $step    = 4;
                $message = ['type' => 'success', 'text' => '✅ Đổi mật khẩu thành công!'];
            } else {
                $message = ['type' => 'error', 'text' => '❌ Lỗi hệ thống, vui lòng thử lại!'];
                $step = 3;
            }
        }
    }
}

// Đồng bộ step từ session (trường hợp GET)
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $step = isset($_SESSION['reset_step']) ? (int)$_SESSION['reset_step'] : 1;
}

$email_hint = maskEmail($_SESSION['reset_email'] ?? '');
$role_hint  = $_SESSION['reset_role'] ?? '';
$role_label = $role_hint === 'teacher' ? 'Giáo viên' : 'Học sinh';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quên mật khẩu | Góc Học Tập</title>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            background: linear-gradient(135deg, #e0f7fa 0%, #bbdefb 100%);
            min-height: 100vh; display: flex; justify-content: center;
            align-items: center; font-family: 'Nunito', sans-serif; padding: 20px;
        }
        .card {
            background: #fff; border-radius: 24px; padding: 44px 40px;
            width: 100%; max-width: 440px;
            box-shadow: 0 20px 60px rgba(2,119,189,0.15);
        }

        /* Logo */
        .logo { text-align: center; margin-bottom: 6px; }
        .logo h2 { color: #0277bd; font-size: 26px; font-weight: 800; text-transform: uppercase; letter-spacing: 1px; }

        /* Icon shield */
        .shield-icon {
            display: flex; align-items: center; justify-content: center;
            width: 72px; height: 72px; background: linear-gradient(135deg, #0288d1, #40c4ff);
            border-radius: 50%; margin: 20px auto 16px;
            box-shadow: 0 8px 20px rgba(2,136,209,0.3);
        }

        /* Steps bar */
        .steps { display: flex; align-items: center; justify-content: center; margin-bottom: 28px; gap: 0; }
        .step-item { display: flex; flex-direction: column; align-items: center; position: relative; }
        .step-circle {
            width: 34px; height: 34px; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-weight: 800; font-size: 14px;
            border: 2.5px solid #e1f5fe; background: #f8fbff; color: #b0bec5;
            transition: 0.3s; position: relative; z-index: 1;
        }
        .step-circle.active  { border-color: #0288d1; background: #0288d1; color: #fff; box-shadow: 0 4px 12px rgba(2,136,209,0.35); }
        .step-circle.done    { border-color: #43a047; background: #43a047; color: #fff; }
        .step-label { font-size: 11px; font-weight: 700; margin-top: 5px; color: #b0bec5; white-space: nowrap; }
        .step-label.active   { color: #0288d1; }
        .step-label.done     { color: #43a047; }
        .step-line { width: 48px; height: 2.5px; background: #e1f5fe; margin-bottom: 18px; transition: 0.3s; }
        .step-line.done  { background: #43a047; }

        /* Input */
        .input-group { margin-bottom: 18px; }
        .input-group label { display: block; margin-bottom: 8px; font-weight: 700; color: #455a64; font-size: 14px; }
        .input-group input {
            width: 100%; padding: 13px 14px; border: 2px solid #e1f5fe;
            border-radius: 12px; outline: none; font-size: 15px;
            font-family: 'Nunito', sans-serif; color: #263238; transition: 0.3s;
        }
        .input-group input:focus { border-color: #03a9f4; background: #f1faff; }

        /* Password wrapper */
        .pw-wrap { position: relative; display: flex; align-items: center; }
        .pw-wrap input { padding-right: 48px; }
        .toggle-pw {
            position: absolute; right: 14px; cursor: pointer; color: #90a4ae;
            display: flex; align-items: center; transition: 0.3s; line-height: 0;
        }
        .toggle-pw:hover { color: #0288d1; }

        /* OTP boxes */
        .otp-boxes { display: flex; gap: 10px; justify-content: center; margin-bottom: 20px; }
        .otp-box {
            width: 52px; height: 60px; border: 2.5px solid #e1f5fe;
            border-radius: 14px; font-size: 26px; font-weight: 800;
            text-align: center; color: #0277bd; background: #f8fbff;
            outline: none; transition: 0.25s; caret-color: transparent;
        }
        .otp-box:focus { border-color: #0288d1; background: #e1f5fe; box-shadow: 0 0 0 3px rgba(2,136,209,0.15); }
        .otp-box.filled { border-color: #0288d1; background: #e1f5fe; }

        /* Countdown */
        .countdown-wrap { text-align: center; margin-bottom: 18px; }
        .countdown-text { font-size: 13px; color: #64748b; font-weight: 700; }
        .countdown-num  { color: #0288d1; font-size: 15px; font-weight: 800; }
        .cd-bar-bg { height: 4px; background: #e1f5fe; border-radius: 99px; margin-top: 8px; overflow: hidden; }
        .cd-bar    { height: 100%; width: 100%; background: linear-gradient(90deg,#0288d1,#40c4ff); border-radius: 99px; transition: width 1s linear; }

        /* Messages */
        .msg {
            padding: 12px 16px; border-radius: 10px; font-weight: 700;
            font-size: 14px; margin-bottom: 18px; text-align: center;
        }
        .msg.error   { background: #fff0f0; border-left: 4px solid #ef4444; color: #b91c1c; }
        .msg.success { background: #f0fdf4; border-left: 4px solid #22c55e; color: #166534; }

        /* Buttons */
        .btn-primary {
            width: 100%; padding: 16px; background: linear-gradient(135deg, #0288d1, #03a9f4);
            color: white; border: none; border-radius: 14px; font-weight: 800;
            cursor: pointer; font-size: 16px; font-family: 'Nunito', sans-serif;
            transition: 0.3s; box-shadow: 0 6px 16px rgba(2,136,209,0.3); margin-bottom: 12px;
        }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 10px 24px rgba(2,136,209,0.4); }
        .btn-primary:disabled { opacity: 0.6; cursor: not-allowed; transform: none; }
        .btn-secondary {
            width: 100%; padding: 12px; background: #f1f5f9; color: #475569;
            border: 2px solid #e2e8f0; border-radius: 14px; font-weight: 800;
            cursor: pointer; font-size: 14px; font-family: 'Nunito', sans-serif; transition: 0.3s;
        }
        .btn-secondary:hover { background: #e1f5fe; color: #0288d1; border-color: #b3e5fc; }

        /* Info box */
        .info-box {
            background: #f0f9ff; border: 1px solid #bae6fd; border-radius: 10px;
            padding: 12px 16px; font-size: 13px; color: #0277bd;
            font-weight: 600; text-align: center; margin-bottom: 20px;
        }

        /* Role badge */
        .role-badge {
            display: inline-flex; align-items: center; gap: 6px;
            background: #e1f5fe; color: #0277bd; font-size: 13px; font-weight: 700;
            border-radius: 20px; padding: 4px 14px; margin-bottom: 18px;
            border: 1px solid #b3e5fc;
        }

        /* Success final */
        .success-final { text-align: center; padding: 20px 0; }
        .success-final .big-check {
            width: 80px; height: 80px; background: linear-gradient(135deg,#43a047,#66bb6a);
            border-radius: 50%; display: flex; align-items: center; justify-content: center;
            margin: 0 auto 20px; box-shadow: 0 8px 20px rgba(67,160,71,0.3);
        }
        .success-final h3 { color: #2e7d32; font-size: 20px; font-weight: 800; margin-bottom: 8px; }
        .success-final p  { color: #546e7a; font-size: 14px; font-weight: 600; margin-bottom: 20px; }
        .btn-login {
            display: inline-block; padding: 14px 32px; background: #0288d1;
            color: white; text-decoration: none; border-radius: 14px;
            font-weight: 800; font-size: 15px; transition: 0.3s;
        }
        .btn-login:hover { background: #0277bd; transform: translateY(-2px); }

        .divider { height: 1px; background: #f1f5f9; margin: 12px 0; }
        .footer-link { text-align: center; margin-top: 16px; font-size: 14px; color: #64748b; font-weight: 600; }
        .footer-link a { color: #0288d1; text-decoration: none; font-weight: 800; }

        @keyframes shake { 0%,100%{transform:translateX(0)} 20%{transform:translateX(-6px)} 40%{transform:translateX(6px)} 60%{transform:translateX(-4px)} 80%{transform:translateX(4px)} }
        .shake { animation: shake 0.4s ease; }

        /* Countdown success redirect */
        .redirect-bar-wrap { height: 4px; background: #c8e6c9; border-radius: 99px; margin-top: 12px; overflow: hidden; }
        .redirect-bar { height: 100%; width: 100%; background: #43a047; border-radius: 99px; transition: width 1s linear; }
    </style>
</head>
<body>
<div class="card">

    <div class="logo"><h2>Góc Học Tập</h2></div>

    <!-- Shield icon -->
    <div class="shield-icon">
        <svg width="34" height="34" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
            <?php if ($step >= 3): ?>
            <polyline points="9 12 11 14 15 10"></polyline>
            <?php else: ?>
            <line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line>
            <?php endif; ?>
        </svg>
    </div>

    <!-- Step indicator -->
    <?php if ($step < 4): ?>
    <div class="steps">
        <div class="step-item">
            <div class="step-circle <?= $step == 1 ? 'active' : 'done' ?>">
                <?= $step > 1 ? '✓' : '1' ?>
            </div>
            <div class="step-label <?= $step == 1 ? 'active' : 'done' ?>">Xác minh</div>
        </div>
        <div class="step-line <?= $step > 1 ? 'done' : '' ?>"></div>
        <div class="step-item">
            <div class="step-circle <?= $step == 2 ? 'active' : ($step > 2 ? 'done' : '') ?>">
                <?= $step > 2 ? '✓' : '2' ?>
            </div>
            <div class="step-label <?= $step == 2 ? 'active' : ($step > 2 ? 'done' : '') ?>">Mã OTP</div>
        </div>
        <div class="step-line <?= $step > 2 ? 'done' : '' ?>"></div>
        <div class="step-item">
            <div class="step-circle <?= $step == 3 ? 'active' : '' ?>">3</div>
            <div class="step-label <?= $step == 3 ? 'active' : '' ?>">Mật khẩu</div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Message -->
    <?php if ($message): ?>
    <div class="msg <?= $message['type'] ?>" id="msgBox">
        <?= htmlspecialchars($message['text']) ?>
    </div>
    <?php endif; ?>

    <!-- ══════════════════════════════════════ -->
    <!-- BƯỚC 1: Nhập email + môn học          -->
    <!-- ══════════════════════════════════════ -->
    <?php if ($step == 1): ?>
    <div style="text-align:center; margin-bottom:20px;">
        <div style="font-size:18px; font-weight:800; color:#1e293b; margin-bottom:4px;">Khôi phục mật khẩu</div>
        <div style="font-size:13px; color:#64748b; font-weight:600;">Nhập email và môn học yêu thích để xác minh</div>
    </div>
    <form method="POST">
        <input type="hidden" name="action" value="verify_identity">
        <div class="input-group">
            <label>Địa chỉ Email</label>
            <input type="email" name="email" required placeholder="your@gmail.com" autofocus>
        </div>
        <div class="input-group">
            <label>Môn học yêu thích / Chuyên môn giảng dạy</label>
            <input type="text" name="monhoc_yeuthich" required placeholder="VD: Toán, Văn, Lập trình...">
        </div>
        <button type="submit" class="btn-primary">Tiếp tục →</button>
    </form>

    <!-- ══════════════════════════════════════ -->
    <!-- BƯỚC 2: Nhập OTP                      -->
    <!-- ══════════════════════════════════════ -->
    <?php elseif ($step == 2): ?>
    <div style="text-align:center; margin-bottom:16px;">
        <div style="font-size:18px; font-weight:800; color:#1e293b; margin-bottom:6px;">Nhập mã xác minh</div>
        <div style="font-size:13px; color:#64748b; font-weight:600; line-height:1.6;">
            Mã OTP 6 số đã gửi đến<br>
            <strong style="color:#0288d1;"><?= htmlspecialchars($email_hint) ?></strong>
        </div>
    </div>

    <?php if ($role_hint): ?>
    <div style="text-align:center; margin-bottom:16px;">
        <span class="role-badge">
            <?= $role_hint === 'teacher' ? '👨‍🏫' : '🎓' ?> <?= htmlspecialchars($role_label) ?>
        </span>
    </div>
    <?php endif; ?>

    <div class="info-box">📧 Kiểm tra hộp thư đến (và thư mục Spam) để lấy mã OTP</div>

    <form method="POST" id="otpForm">
        <input type="hidden" name="action" value="verify_otp">
        <div class="otp-boxes" id="otpBoxes">
            <?php for ($i = 1; $i <= 6; $i++): ?>
            <input type="text" inputmode="numeric" maxlength="1"
                   name="otp[<?= $i ?>]" id="otp<?= $i ?>"
                   class="otp-box" autocomplete="one-time-code">
            <?php endfor; ?>
        </div>

        <div class="countdown-wrap">
            <div class="countdown-text">Mã hết hạn sau <span class="countdown-num" id="cdNum">5:00</span></div>
            <div class="cd-bar-bg"><div class="cd-bar" id="cdBar"></div></div>
        </div>

        <button type="submit" class="btn-primary" id="btnSubmit">Xác nhận OTP</button>
    </form>

    <div class="divider"></div>

    <form method="POST">
        <input type="hidden" name="action" value="verify_otp">
        <input type="hidden" name="resend" value="1">
        <button type="submit" class="btn-secondary">🔄 Gửi lại mã OTP</button>
    </form>

    <!-- ══════════════════════════════════════ -->
    <!-- BƯỚC 3: Đổi mật khẩu                 -->
    <!-- ══════════════════════════════════════ -->
    <?php elseif ($step == 3): ?>
    <div style="text-align:center; margin-bottom:20px;">
        <div style="font-size:18px; font-weight:800; color:#1e293b; margin-bottom:4px;">Đặt mật khẩu mới</div>
        <div style="font-size:13px; color:#64748b; font-weight:600;">Mật khẩu phải có ít nhất 6 ký tự</div>
    </div>
    <form method="POST">
        <input type="hidden" name="action" value="reset_password">
        <div class="input-group">
            <label>Mật khẩu mới</label>
            <div class="pw-wrap">
                <input type="password" name="new_password" id="pw1" required placeholder="Nhập mật khẩu mới" autofocus>
                <span class="toggle-pw" onclick="togglePw('pw1',this)" title="Hiển thị">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                </span>
            </div>
        </div>
        <div class="input-group">
            <label>Xác nhận mật khẩu</label>
            <div class="pw-wrap">
                <input type="password" name="confirm_password" id="pw2" required placeholder="Nhập lại mật khẩu">
                <span class="toggle-pw" onclick="togglePw('pw2',this)" title="Hiển thị">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                </span>
            </div>
        </div>
        <button type="submit" class="btn-primary">💾 Lưu mật khẩu mới</button>
    </form>

    <!-- ══════════════════════════════════════ -->
    <!-- BƯỚC 4: Thành công                    -->
    <!-- ══════════════════════════════════════ -->
    <?php elseif ($step == 4): ?>
    <div class="success-final">
        <div class="big-check">
            <svg width="38" height="38" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="20 6 9 17 4 12"></polyline>
            </svg>
        </div>
        <h3>Đổi mật khẩu thành công!</h3>
        <p>Mật khẩu của bạn đã được cập nhật.<br>Tự động chuyển đến trang đăng nhập sau <span id="rCount" style="color:#0288d1;font-weight:800;">5</span> giây...</p>
        <div class="redirect-bar-wrap"><div class="redirect-bar" id="rBar"></div></div>
        <br>
        <a href="trangdangnhap.php" class="btn-login">Đăng nhập ngay →</a>
    </div>
    <?php endif; ?>

    <?php if ($step < 4): ?>
    <div class="footer-link" style="margin-top:18px;">
        <a href="trangdangnhap.php">← Quay lại đăng nhập</a>
    </div>
    <?php endif; ?>
</div>

<script>
// ── OTP Input logic (chỉ chạy ở bước 2) ──────────────────
(function() {
    var boxes = document.querySelectorAll('.otp-box');
    if (!boxes.length) return;

    boxes.forEach(function(box, idx) {
        box.addEventListener('keydown', function(e) {
            if (e.key === 'Backspace') {
                if (box.value === '' && idx > 0) {
                    boxes[idx-1].focus(); boxes[idx-1].value=''; boxes[idx-1].classList.remove('filled');
                } else { box.value=''; box.classList.remove('filled'); }
                e.preventDefault();
            }
        });
        box.addEventListener('input', function() {
            box.value = box.value.replace(/\D/g,'').slice(-1);
            if (box.value) {
                box.classList.add('filled');
                if (idx < boxes.length-1) boxes[idx+1].focus();
                else tryAutoSubmit();
            } else { box.classList.remove('filled'); }
        });
        box.addEventListener('paste', function(e) {
            e.preventDefault();
            var text = (e.clipboardData||window.clipboardData).getData('text').replace(/\D/g,'');
            for (var i=0; i<Math.min(text.length, boxes.length-idx); i++) {
                boxes[idx+i].value = text[i]; boxes[idx+i].classList.add('filled');
            }
            boxes[Math.min(idx+text.length, boxes.length-1)].focus();
            if (text.length >= boxes.length-idx) tryAutoSubmit();
        });
    });

    boxes[0].focus();

    function tryAutoSubmit() {
        if (Array.from(boxes).every(function(b){ return b.value!==''; }))
            setTimeout(function(){ document.getElementById('otpForm').submit(); }, 250);
    }

    // Shake on error
    var msgBox = document.getElementById('msgBox');
    if (msgBox && msgBox.classList.contains('error')) {
        msgBox.classList.add('shake');
        boxes[0].focus();
        // Clear all boxes on wrong OTP
        boxes.forEach(function(b){ b.value=''; b.classList.remove('filled'); });
        boxes[0].focus();
    }

    // Countdown 5 phút
    var total = 5*60, left = total;
    var barEl = document.getElementById('cdBar');
    var numEl = document.getElementById('cdNum');
    var btnEl = document.getElementById('btnSubmit');
    barEl.style.width='100%';
    setTimeout(function(){ barEl.style.transitionDuration=total+'s'; barEl.style.width='0%'; },50);
    var t = setInterval(function(){
        left--;
        var m=Math.floor(left/60), s=left%60;
        numEl.textContent = m+':'+(s<10?'0':'')+s;
        if (left<=30) numEl.style.color='#ef4444';
        if (left<=0) {
            clearInterval(t);
            numEl.textContent='Hết hạn';
            btnEl.disabled=true;
            btnEl.textContent='Mã OTP đã hết hạn — Gửi lại mã mới';
        }
    },1000);
})();

// ── Toggle password ───────────────────────────
function togglePw(id, span) {
    var inp = document.getElementById(id);
    var eyeOpen   = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>';
    var eyeClosed = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line></svg>';
    if (inp.type==='password') { inp.type='text'; span.innerHTML=eyeClosed; }
    else { inp.type='password'; span.innerHTML=eyeOpen; }
}

// ── Countdown redirect (bước 4) ──────────────
(function() {
    var rCount = document.getElementById('rCount');
    var rBar   = document.getElementById('rBar');
    if (!rCount) return;
    var sec = 5;
    rBar.style.width='100%';
    setTimeout(function(){ rBar.style.transitionDuration='5s'; rBar.style.width='0%'; },50);
    var t = setInterval(function(){
        sec--;
        rCount.textContent = sec;
        if (sec<=0) { clearInterval(t); window.location.href='trangdangnhap.php'; }
    },1000);
})();
</script>
</body>
</html>