<?php
$role_get = trim($_GET['role'] ?? '');
if ($role_get === 'teacher') {
    ini_set('session.name', 'GV_SESSION');
} else {
    ini_set('session.name', 'HS_SESSION');
}
session_start();
require_once 'config.php';

// Kiểm tra đang ở trạng thái chờ OTP

if (empty($_SESSION['otp_pending']) || empty($_SESSION['otp_user_id'])) {
    header("Location: trangdangnhap.php");
    exit;
}
$error      = '';
$resent     = false;
$email_hint = $_SESSION['otp_email'] ?? '';
$role_label = ($role_get === 'teacher') ? 'Giáo viên' : 'Học sinh';
$attempts   = $_SESSION['otp_attempts'] ?? 0;
$otp_type   = $_SESSION['otp_type'] ?? 'login'; // 'login' hoặc 'register'

// Hiện OTP trực tiếp nếu email gửi thất bại (máy không có SMTP)

$mail_sent  = $_SESSION['otp_mail_sent'] ?? false;
$otp_hien   = (!$mail_sent) ? ($_SESSION['otp_code'] ?? '') : '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'verify';

    // Xử lý gửi lại OTP

    if ($action === 'resend') {
        $new_otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $_SESSION['otp_code']     = $new_otp;
        $_SESSION['otp_expires']  = time() + 300;
        $_SESSION['otp_attempts'] = 0;

        $user_id  = (int)$_SESSION['otp_user_id'];
        $d = $conn->prepare("DELETE FROM otp_codes WHERE user_id = ?");
        $d->bind_param("i", $user_id);
        $d->execute();
        $exp_db = date('Y-m-d H:i:s', time() + 300);
        $s = $conn->prepare("INSERT INTO otp_codes (user_id, otp_code, expires_at) VALUES (?, ?, ?)");
        $s->bind_param("iss", $user_id, $new_otp, $exp_db);
        $s->execute();

        require_once 'lib/mailer.php';
        $_SESSION['otp_mail_sent'] = smtp_send_otp($email_hint, $new_otp);
        $mail_sent = $_SESSION['otp_mail_sent'];
        $otp_hien  = $mail_sent ? '' : $new_otp;
        $resent = true;

    } else {
        // Xác minh OTP
        $digits  = $_POST['otp'] ?? [];
        $entered = is_array($digits) ? implode('', array_map('trim', $digits)) : trim($digits);

        if (strlen($entered) !== 6 || !ctype_digit($entered)) {
            $error = "Mã OTP phải gồm đúng 6 chữ số!";
        } else {
            $correct_otp  = $_SESSION['otp_code']    ?? '';
            $expires_at   = $_SESSION['otp_expires']  ?? 0;

            if (time() > $expires_at) {
                $error = "Mã OTP đã hết hạn! Vui lòng nhấn Gửi lại.";
            } elseif ($entered !== $correct_otp) {
                $_SESSION['otp_attempts'] = ($attempts + 1);
                $con = max(0, 3 - $_SESSION['otp_attempts']);
                $error = "Mã OTP không đúng!" . ($con > 0 ? " Còn $con lần thử." : " Vui lòng gửi lại mã mới.");
            } else {

                // OTP đúng

                $user_id = (int)$_SESSION['otp_user_id'];
                $role    = $_SESSION['otp_role'];

                // Đánh dấu OTP đã dùng trong DB
                $upd = $conn->prepare("UPDATE otp_codes SET used = 1 WHERE user_id = ? AND otp_code = ?");
                $upd->bind_param("is", $user_id, $entered);
                $upd->execute();

                // Xoá hết session OTP
                unset(
                    $_SESSION['otp_pending'],  $_SESSION['otp_user_id'],
                    $_SESSION['otp_role'],     $_SESSION['otp_ho_ten'],
                    $_SESSION['otp_email'],    $_SESSION['otp_code'],
                    $_SESSION['otp_expires'],  $_SESSION['otp_attempts'],
                    $_SESSION['otp_type'],     $_SESSION['otp_mail_sent']
                );
                // OTP đăng ký → chuyển về trang đăng nhập

                session_regenerate_id(true);
                header("Location: trangdangnhap.php?success=" . urlencode("Xác minh thành công! Hãy đăng nhập."));
                exit;
            }
        }
    }
}
function maskEmail(string $email): string {
    $parts = explode('@', $email);
    if (count($parts) !== 2) return $email;
    $name    = $parts[0];
    $visible = mb_substr($name, 0, min(3, mb_strlen($name)), 'UTF-8');
    return $visible . str_repeat('*', max(2, mb_strlen($name, 'UTF-8') - 3)) . '@' . $parts[1];
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Xác nhận OTP | Góc Học Tập</title>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            background: linear-gradient(135deg, #e0f7fa 0%, #bbdefb 100%);
            min-height: 100vh; display: flex; justify-content: center;
            align-items: center; font-family: 'Nunito', sans-serif; padding: 20px;
        }
        .card {
            background: white; border-radius: 24px; padding: 44px 40px;
            width: 100%; max-width: 440px;
            box-shadow: 0 20px 60px rgba(2,119,189,0.15);
        }
        .logo-area { text-align: center; margin-bottom: 8px; }
        .logo-area h2 {
            color: #0277bd; font-size: 26px; font-weight: 800;
            text-transform: uppercase; letter-spacing: 1px;
        }
        .shield-icon {
            display: flex; align-items: center; justify-content: center;
            width: 72px; height: 72px; background: linear-gradient(135deg, #0288d1, #40c4ff);
            border-radius: 50%; margin: 20px auto 16px; box-shadow: 0 8px 20px rgba(2,136,209,0.3);
        }
        .otp-title { text-align: center; font-size: 20px; font-weight: 800; color: #1e293b; margin-bottom: 6px; }
        .otp-subtitle { text-align: center; color: #64748b; font-size: 14px; font-weight: 600; line-height: 1.5; margin-bottom: 20px; }
        .otp-subtitle b { color: #0288d1; }
        .role-badge {
            display: inline-flex; align-items: center; gap: 6px;
            background: #e1f5fe; color: #0277bd; font-size: 13px; font-weight: 700;
            border-radius: 20px; padding: 4px 14px; margin-bottom: 22px;
            border: 1px solid #b3e5fc;
        }
        .otp-boxes { display: flex; gap: 10px; justify-content: center; margin-bottom: 22px; }
        .otp-box {
            width: 52px; height: 60px; border: 2.5px solid #e1f5fe;
            border-radius: 14px; font-size: 26px; font-weight: 800;
            text-align: center; color: #0277bd; background: #f8fbff;
            outline: none; transition: 0.25s; caret-color: transparent;
        }
        .otp-box:focus { border-color: #0288d1; background: #e1f5fe; box-shadow: 0 0 0 3px rgba(2,136,209,0.15); }
        .otp-box.filled { border-color: #0288d1; background: #e1f5fe; }
        .countdown-wrap { text-align: center; margin-bottom: 20px; }
        .countdown-text { font-size: 13px; color: #64748b; font-weight: 700; }
        .countdown-num { color: #0288d1; font-size: 16px; font-weight: 800; }
        .countdown-bar-bg { height: 4px; background: #e1f5fe; border-radius: 99px; margin-top: 8px; overflow: hidden; }
        .countdown-bar { height: 100%; width: 100%; background: linear-gradient(90deg, #0288d1, #40c4ff); border-radius: 99px; transition: width 1s linear; }
        .msg-error {
            background: #fff0f0; border-left: 4px solid #ef4444; color: #b91c1c;
            padding: 12px 16px; border-radius: 10px; font-weight: 700; font-size: 14px;
            margin-bottom: 16px; text-align: center;
        }
        .msg-success {
            background: #f0fdf4; border-left: 4px solid #22c55e; color: #166534;
            padding: 12px 16px; border-radius: 10px; font-weight: 700; font-size: 14px;
            margin-bottom: 16px; text-align: center;
        }
        .btn-submit {
            width: 100%; padding: 16px; background: linear-gradient(135deg, #0288d1, #03a9f4);
            color: white; border: none; border-radius: 14px; font-weight: 800;
            cursor: pointer; font-size: 16px; font-family: 'Nunito', sans-serif;
            transition: 0.3s; letter-spacing: 0.5px; box-shadow: 0 6px 16px rgba(2,136,209,0.3);
            margin-bottom: 12px;
        }
        .btn-submit:hover { transform: translateY(-2px); box-shadow: 0 10px 24px rgba(2,136,209,0.4); }
        .btn-submit:disabled { opacity: 0.6; cursor: not-allowed; transform: none; }
        .btn-resend {
            width: 100%; padding: 12px; background: #f1f5f9; color: #475569;
            border: 2px solid #e2e8f0; border-radius: 14px; font-weight: 800;
            cursor: pointer; font-size: 14px; font-family: 'Nunito', sans-serif;
            transition: 0.3s;
        }
        .btn-resend:hover { background: #e1f5fe; color: #0288d1; border-color: #b3e5fc; }
        .footer-link { text-align: center; margin-top: 18px; font-size: 14px; color: #64748b; font-weight: 600; }
        .footer-link a { color: #0288d1; text-decoration: none; font-weight: 800; }
        @keyframes shake {
            0%,100% { transform: translateX(0); }
            20%     { transform: translateX(-6px); }
            40%     { transform: translateX(6px); }
            60%     { transform: translateX(-4px); }
            80%     { transform: translateX(4px); }
        }
        .shake { animation: shake 0.4s ease; }
        .divider { height: 1px; background: #f1f5f9; margin: 14px 0; }
        .email-note {
            background: #f0f9ff; border-radius: 10px; padding: 12px 16px;
            font-size: 13px; color: #0277bd; font-weight: 600;
            text-align: center; margin-bottom: 22px; border: 1px solid #bae6fd;
        }
        /* Checkbox nhớ thiết bị */
        .remember-device {
            display: flex; align-items: center; gap: 10px;
            background: #f0f9ff; border: 1px solid #bae6fd;
            border-radius: 12px; padding: 12px 16px; margin-bottom: 16px;
            cursor: pointer;
        }
        .remember-device input[type="checkbox"] {
            width: 18px; height: 18px; accent-color: #0288d1; cursor: pointer; flex-shrink: 0;
        }
        .remember-device span {
            font-size: 13px; font-weight: 700; color: #0277bd; line-height: 1.4;
        }
        .remember-device span small {
            display: block; color: #64748b; font-weight: 600; font-size: 11px; margin-top: 2px;
        }
    </style>
</head>
<body>
<div class="card">
    <div class="logo-area"><h2>Góc Học Tập</h2></div>

    <div class="shield-icon">
        <svg width="34" height="34" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
            <polyline points="9 12 11 14 15 10"></polyline>
        </svg>
    </div>

    <div class="otp-title">Xác minh danh tính</div>
    <div class="otp-subtitle">
        <?php if ($otp_type === 'register'): ?>
            Mã xác minh đã được gửi đến email<br>
            <b><?= htmlspecialchars(maskEmail($email_hint)) ?></b><br>
            <span style="font-size:12px;color:#94a3b8;">Xác minh để hoàn tất đăng ký</span>
        <?php else: ?>
            Phát hiện đăng nhập từ thiết bị mới.<br>
            Mã OTP đã gửi đến <b><?= htmlspecialchars(maskEmail($email_hint)) ?></b>
        <?php endif; ?>
    </div>

    <div style="text-align:center; margin-bottom: 20px;">
        <span class="role-badge">
            <?= $role_get === 'teacher' ? '👨‍🏫' : '🎓' ?>
            <?= htmlspecialchars($role_label) ?>
        </span>
    </div>

    <?php if ($otp_hien): ?>
    <div style="background:#fffbeb;border:2px dashed #f59e0b;border-radius:14px;padding:18px;text-align:center;margin-bottom:20px;">
        <p style="color:#92400e;font-size:12px;font-weight:700;margin-bottom:8px;">
            ⚠️ Không gửi được email — Mã OTP của bạn:
        </p>
        <div style="font-size:36px;font-weight:900;letter-spacing:10px;color:#d97706;">
            <?= htmlspecialchars($otp_hien) ?>
        </div>
        <p style="color:#b45309;font-size:11px;margin-top:8px;font-weight:600;">
            Cấu hình SMTP trong config.php để gửi email thật
        </p>
    </div>
    <?php else: ?>
    <div class="email-note">
        📧 Kiểm tra hộp thư đến (và thư mục Spam) để lấy mã OTP 6 chữ số
    </div>
    <?php endif; ?>

    <?php if ($resent): ?>
    <div class="msg-success">✅ Mã OTP mới đã được gửi lại!</div>
    <?php endif; ?>

    <?php if ($error): ?>
    <div class="msg-error" id="errBox"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" id="otpForm">
        <input type="hidden" name="action" value="verify">
        <div class="otp-boxes" id="otpBoxes">
            <?php for ($i = 1; $i <= 6; $i++): ?>
            <input type="text" inputmode="numeric" maxlength="1"
                   name="otp[<?= $i ?>]" id="otp<?= $i ?>"
                   class="otp-box" autocomplete="one-time-code">
            <?php endfor; ?>
        </div>

        <div class="countdown-wrap">
            <div class="countdown-text">Mã hết hạn sau <span class="countdown-num" id="cdNum">5:00</span></div>
            <div class="countdown-bar-bg"><div class="countdown-bar" id="cdBar"></div></div>
        </div>


        <button type="submit" class="btn-submit" id="btnSubmit">Xác nhận OTP</button>
    </form>

    <div class="divider"></div>

    <form method="POST">
        <input type="hidden" name="action" value="resend">
        <button type="submit" class="btn-resend" id="btnResend">🔄 Gửi lại mã OTP</button>
    </form>

    <div class="footer-link">
        <a href="trangdangnhap.php">← Quay lại đăng nhập</a>
    </div>
</div>

<script>
(function() {
    var boxes = document.querySelectorAll('.otp-box');

    boxes.forEach(function(box, idx) {
        box.addEventListener('keydown', function(e) {
            if (e.key === 'Backspace') {
                if (box.value === '' && idx > 0) {
                    boxes[idx - 1].focus();
                    boxes[idx - 1].value = '';
                    boxes[idx - 1].classList.remove('filled');
                } else {
                    box.value = '';
                    box.classList.remove('filled');
                }
                e.preventDefault();
            }
        });
        box.addEventListener('input', function() {
            box.value = box.value.replace(/\D/g, '').slice(-1);
            if (box.value) {
                box.classList.add('filled');
                if (idx < boxes.length - 1) boxes[idx + 1].focus();
                else tryAutoSubmit();
            } else {
                box.classList.remove('filled');
            }
        });
        box.addEventListener('paste', function(e) {
            e.preventDefault();
            var text = (e.clipboardData || window.clipboardData).getData('text').replace(/\D/g, '');
            var start = idx;
            for (var i = 0; i < Math.min(text.length, boxes.length - start); i++) {
                boxes[start + i].value = text[i];
                boxes[start + i].classList.add('filled');
            }
            var next = Math.min(start + text.length, boxes.length - 1);
            boxes[next].focus();
            if (text.length >= boxes.length - start) tryAutoSubmit();
        });
    });
    if (boxes.length > 0) boxes[0].focus();

    function tryAutoSubmit() {
        var all = Array.from(boxes).every(function(b) { return b.value !== ''; });
        if (all) setTimeout(function() { document.getElementById('otpForm').submit(); }, 200);
    }
    var errBox = document.getElementById('errBox');
    if (errBox) { errBox.classList.add('shake'); boxes[0].focus(); }

    // Countdown 5 phút

    var total = 5 * 60;
    var left  = total;
    var barEl = document.getElementById('cdBar');
    var numEl = document.getElementById('cdNum');
    var btnEl = document.getElementById('btnSubmit');

    barEl.style.width = '100%';
    setTimeout(function() {
        barEl.style.transitionDuration = total + 's';
        barEl.style.width = '0%';
    }, 50);
    var timer = setInterval(function() {
        left--;
        var m = Math.floor(left / 60);
        var s = left % 60;
        numEl.textContent = m + ':' + (s < 10 ? '0' : '') + s;
        if (left <= 30) numEl.style.color = '#ef4444';
        if (left <= 0) {
            clearInterval(timer);
            numEl.textContent = 'Hết hạn';
            btnEl.disabled    = true;
            btnEl.textContent = 'Mã OTP đã hết hạn — Gửi lại mã mới';
        }
    }, 1000);
})();
</script>
</body>
</html>