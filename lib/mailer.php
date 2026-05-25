<?php
/**
 * Gửi OTP qua PHP mail() mặc định của server hosting
 */
function smtp_send_otp(string $to_email, string $otp_code): bool {
    $fromName = defined('SMTP_NAME') ? SMTP_NAME : 'Góc Học Tập';
    $from     = defined('SMTP_FROM') ? SMTP_FROM : 'no-reply@gochoctap.local';

    $subject = '=?UTF-8?B?' . base64_encode('Mã OTP xác minh — Góc Học Tập') . '?=';

    $bodyHtml = "
<div style='font-family:Nunito,sans-serif;max-width:480px;margin:0 auto;background:#fff;border-radius:16px;overflow:hidden;border:1px solid #e1f5fe'>
  <div style='background:linear-gradient(135deg,#0277bd,#03a9f4);padding:28px 32px;text-align:center'>
    <h1 style='color:white;margin:0;font-size:24px;font-weight:800;letter-spacing:1px'>GÓC HỌC TẬP</h1>
    <p style='color:rgba(255,255,255,0.85);margin:6px 0 0;font-size:14px'>Xác minh đăng ký</p>
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

    $headers  = "From: =?UTF-8?B?" . base64_encode($fromName) . "?= <$from>\r\n";
    $headers .= "Reply-To: $from\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: multipart/alternative; boundary=\"$boundary\"\r\n";
    $headers .= "X-Mailer: PHP/" . phpversion();

    $bodyText = "Mã OTP của bạn là: $otp_code\n\nMã có hiệu lực trong 5 phút.\nNếu bạn không thực hiện yêu cầu này, hãy bỏ qua email này.\n\n— Đội ngũ Góc Học Tập";

    $body  = "--$boundary\r\n";
    $body .= "Content-Type: text/plain; charset=UTF-8\r\n";
    $body .= "Content-Transfer-Encoding: base64\r\n\r\n";
    $body .= chunk_split(base64_encode($bodyText)) . "\r\n";
    $body .= "--$boundary\r\n";
    $body .= "Content-Type: text/html; charset=UTF-8\r\n";
    $body .= "Content-Transfer-Encoding: base64\r\n\r\n";
    $body .= chunk_split(base64_encode($bodyHtml)) . "\r\n";
    $body .= "--$boundary--\r\n";

    return mail($to_email, $subject, $body, $headers);
}
