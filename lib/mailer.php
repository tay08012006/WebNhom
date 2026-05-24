<?php
/**
 * Gửi email qua Gmail SMTP (STARTTLS port 587)
 * Không cần thư viện ngoài — dùng PHP socket thuần
 */
function smtp_send_otp(string $to_email, string $otp_code): bool {
    $host     = defined('SMTP_HOST') ? SMTP_HOST : '';
    $port     = defined('SMTP_PORT') ? SMTP_PORT : 587;
    $user     = defined('SMTP_USER') ? SMTP_USER : '';
    $pass     = str_replace(' ', '', defined('SMTP_PASS') ? SMTP_PASS : ''); // bỏ dấu cách App Password
    $from     = defined('SMTP_FROM') ? SMTP_FROM : $user;
    $fromName = defined('SMTP_NAME') ? SMTP_NAME : 'Góc Học Tập';

    if (empty($host) || empty($user) || empty($pass)) {
        return false; // Chưa cấu hình SMTP
    }

    $subject  = 'Mã OTP đăng nhập — Góc Học Tập';
    $bodyText = "Xin chào!\n\nMã OTP đăng nhập của bạn là:\n\n   $otp_code\n\nMã có hiệu lực trong 5 phút.\nNếu bạn không thực hiện yêu cầu này, hãy bỏ qua email này.\n\n— Đội ngũ Góc Học Tập";

    // Body HTML đẹp hơn
    $bodyHtml = "
<div style='font-family:Nunito,sans-serif;max-width:480px;margin:0 auto;background:#fff;border-radius:16px;overflow:hidden;border:1px solid #e1f5fe'>
  <div style='background:linear-gradient(135deg,#0277bd,#03a9f4);padding:28px 32px;text-align:center'>
    <h1 style='color:white;margin:0;font-size:24px;font-weight:800;letter-spacing:1px'>GÓC HỌC TẬP</h1>
    <p style='color:rgba(255,255,255,0.85);margin:6px 0 0;font-size:14px'>Xác minh đăng nhập</p>
  </div>
  <div style='padding:36px 32px;text-align:center'>
    <p style='color:#455a64;font-size:15px;margin-bottom:24px'>Mã OTP của bạn là:</p>
    <div style='background:#e1f5fe;border-radius:14px;padding:20px;display:inline-block;margin-bottom:24px'>
      <span style='font-size:42px;font-weight:900;letter-spacing:12px;color:#0277bd'>$otp_code</span>
    </div>
    <p style='color:#78909c;font-size:13px;margin:0'>Mã có hiệu lực trong <b>5 phút</b>.</p>
    <p style='color:#b0bec5;font-size:12px;margin-top:8px'>Nếu bạn không thực hiện yêu cầu này, hãy bỏ qua email này.</p>
  </div>
  <div style='background:#f9fbfb;padding:16px 32px;text-align:center;border-top:1px solid #e1f5fe'>
    <p style='color:#90a4ae;font-size:12px;margin:0'>© Góc Học Tập — Hệ thống học tập trực tuyến</p>
  </div>
</div>";

    $boundary = md5(uniqid(rand(), true));

    // Build raw email
    $rawSubject = '=?UTF-8?B?' . base64_encode($subject) . '?=';
    $rawFrom    = '=?UTF-8?B?' . base64_encode($fromName) . '?= <' . $from . '>';

    $email  = "From: $rawFrom\r\n";
    $email .= "To: $to_email\r\n";
    $email .= "Subject: $rawSubject\r\n";
    $email .= "MIME-Version: 1.0\r\n";
    $email .= "Content-Type: multipart/alternative; boundary=\"$boundary\"\r\n";
    $email .= "\r\n";
    $email .= "--$boundary\r\n";
    $email .= "Content-Type: text/plain; charset=UTF-8\r\n";
    $email .= "Content-Transfer-Encoding: base64\r\n\r\n";
    $email .= chunk_split(base64_encode($bodyText)) . "\r\n";
    $email .= "--$boundary\r\n";
    $email .= "Content-Type: text/html; charset=UTF-8\r\n";
    $email .= "Content-Transfer-Encoding: base64\r\n\r\n";
    $email .= chunk_split(base64_encode($bodyHtml)) . "\r\n";
    $email .= "--$boundary--\r\n";

    // SMTP handshake
    $sock = @fsockopen("tcp://$host", $port, $errno, $errstr, 15);
    if (!$sock) return false;

    $read = function() use ($sock) {
        $line = '';
        while (!feof($sock)) {
            $chunk = fgets($sock, 512);
            $line .= $chunk;
            // Đọc đến khi gặp dòng không có dấu '-' ở vị trí thứ 4 (response cuối)
            if (isset($chunk[3]) && $chunk[3] !== '-') break;
        }
        return $line;
    };

    $write = function(string $cmd) use ($sock) {
        fwrite($sock, $cmd . "\r\n");
    };

    $resp = $read(); // 220 greeting
    if (!str_starts_with($resp, '2')) { fclose($sock); return false; }

    $write("EHLO localhost");
    $read(); // 250 capabilities

    $write("STARTTLS");
    $resp = $read(); // 220 Go ahead
    if (!str_starts_with($resp, '2')) { fclose($sock); return false; }

    // Upgrade to TLS
    if (!stream_socket_enable_crypto($sock, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
        fclose($sock); return false;
    }

    $write("EHLO localhost");
    $read();

    $write("AUTH LOGIN");
    $read(); // 334 Username:
    $write(base64_encode($user));
    $read(); // 334 Password:
    $write(base64_encode($pass));
    $resp = $read(); // 235 Authenticated
    if (!str_starts_with($resp, '2')) { fclose($sock); return false; }

    $write("MAIL FROM:<$from>");
    $read();
    $write("RCPT TO:<$to_email>");
    $resp = $read();
    if (!str_starts_with($resp, '2')) { fclose($sock); return false; }

    $write("DATA");
    $read(); // 354
    fwrite($sock, $email . ".\r\n");
    $resp = $read(); // 250

    $write("QUIT");
    fclose($sock);

    return str_starts_with($resp, '2');
}
