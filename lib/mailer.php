<?php
/**
 * mailer.php — Gửi OTP qua Gmail SMTP (không cần Composer/PHPMailer)
 * Đặt file này vào: C:\xampp\htdocs\123\lib\mailer.php
 */

function smtp_send_otp(string $to_email, string $otp_code): bool
{
    $host    = SMTP_HOST;   // smtp.gmail.com
    $port    = SMTP_PORT;   // 587
    $user    = SMTP_USER;
    $pass    = SMTP_PASS;
    $from    = SMTP_FROM;
    $name    = SMTP_NAME;

    // Nội dung email
    $subject  = '=?UTF-8?B?' . base64_encode('[' . $name . '] Mã xác minh OTP của bạn') . '?=';
    $boundary = md5(uniqid());

    $html = smtp_otp_html($otp_code);
    $text = "Mã OTP của bạn là: $otp_code (hiệu lực 5 phút)";

    $body  = "--$boundary\r\n";
    $body .= "Content-Type: text/plain; charset=UTF-8\r\nContent-Transfer-Encoding: base64\r\n\r\n";
    $body .= chunk_split(base64_encode($text)) . "\r\n";
    $body .= "--$boundary\r\n";
    $body .= "Content-Type: text/html; charset=UTF-8\r\nContent-Transfer-Encoding: base64\r\n\r\n";
    $body .= chunk_split(base64_encode($html)) . "\r\n";
    $body .= "--$boundary--";

    $headers  = "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: multipart/alternative; boundary=\"$boundary\"\r\n";
    $headers .= "From: =?UTF-8?B?" . base64_encode($name) . "?= <$from>\r\n";
    $headers .= "To: $to_email\r\n";
    $headers .= "Subject: $subject\r\n";
    $headers .= "Date: " . date('r') . "\r\n";

    try {
        // Kết nối SMTP với STARTTLS (port 587)
        $ctx = stream_context_create(['ssl' => [
            'verify_peer'       => false,
            'verify_peer_name'  => false,
        ]]);

        $sock = stream_socket_client(
            "tcp://$host:$port", $errno, $errstr, 15,
            STREAM_CLIENT_CONNECT, $ctx
        );
        if (!$sock) throw new Exception("Không kết nối được SMTP: $errstr ($errno)");

        stream_set_timeout($sock, 15);

        $r = function() use ($sock) { return fgets($sock, 512); };
        $w = function($cmd) use ($sock) { fwrite($sock, $cmd . "\r\n"); };

        $r(); // 220 greeting

        $w("EHLO localhost");
        while ($line = $r()) { if ($line[3] === ' ') break; } // đọc hết EHLO multi-line

        $w("STARTTLS");
        $r(); // 220 ready

        // Nâng cấp lên TLS
        stream_socket_enable_crypto($sock, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);

        $w("EHLO localhost");
        while ($line = $r()) { if ($line[3] === ' ') break; }

        $w("AUTH LOGIN");
        $r(); // 334
        $w(base64_encode($user));
        $r(); // 334
        $w(base64_encode($pass));
        $resp = $r(); // 235 or error
        if (strpos($resp, '235') === false) throw new Exception("Xác thực SMTP thất bại: $resp");

        $w("MAIL FROM:<$from>");
        $r();
        $w("RCPT TO:<$to_email>");
        $r();
        $w("DATA");
        $r(); // 354
        $w($headers . "\r\n" . $body . "\r\n.");
        $resp = $r(); // 250
        if (strpos($resp, '250') === false) throw new Exception("Gửi DATA thất bại: $resp");

        $w("QUIT");
        fclose($sock);
        return true;

    } catch (Exception $e) {
        error_log('[Mailer] ' . $e->getMessage());
        if (isset($sock) && is_resource($sock)) fclose($sock);
        return false;
    }
}

function smtp_otp_html(string $otp): string
{
    $site = defined('SMTP_NAME') ? SMTP_NAME : 'Góc Học Tập';
    $digits = '';
    foreach (str_split($otp) as $d) {
        $digits .= "<span style='display:inline-block;width:44px;height:52px;line-height:52px;
                    text-align:center;font-size:28px;font-weight:900;color:#0277bd;
                    background:#e1f5fe;border-radius:10px;margin:0 4px;'>{$d}</span>";
    }
    return "<!DOCTYPE html><html lang='vi'><head><meta charset='UTF-8'></head>
<body style='margin:0;padding:0;background:#f0f7ff;font-family:Arial,sans-serif;'>
  <table width='100%' cellpadding='0' cellspacing='0' style='padding:40px 0;'>
    <tr><td align='center'>
      <table width='480' cellpadding='0' cellspacing='0'
             style='background:#fff;border-radius:20px;box-shadow:0 8px 30px rgba(2,119,189,0.12);overflow:hidden;'>
        <tr><td style='background:linear-gradient(135deg,#0288d1,#03a9f4);padding:32px 40px;text-align:center;'>
          <h1 style='margin:0;color:#fff;font-size:22px;letter-spacing:1px;'>🎓 {$site}</h1>
        </td></tr>
        <tr><td style='padding:36px 40px;text-align:center;'>
          <div style='font-size:40px;margin-bottom:16px;'>🔐</div>
          <h2 style='margin:0 0 10px;color:#1e293b;font-size:20px;'>Xác minh danh tính</h2>
          <p style='color:#64748b;font-size:15px;margin:0 0 28px;line-height:1.6;'>
            Mã OTP của bạn — hiệu lực <strong>5 phút</strong>:
          </p>
          <div style='margin-bottom:28px;'>{$digits}</div>
          <p style='color:#94a3b8;font-size:13px;margin:0;'>
            Nếu bạn không yêu cầu, hãy bỏ qua email này.
          </p>
        </td></tr>
        <tr><td style='background:#f8fafc;padding:18px 40px;text-align:center;border-top:1px solid #e2e8f0;'>
          <p style='margin:0;color:#94a3b8;font-size:12px;'>
            © " . date('Y') . " {$site} — Email tự động, vui lòng không reply.
          </p>
        </td></tr>
      </table>
    </td></tr>
  </table>
</body></html>";
}