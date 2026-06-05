<?php
// Đặt session name theo role trước khi session_start
$role_post = trim($_POST['role'] ?? '');
if ($role_post === 'teacher') {
    ini_set('session.name', 'GV_SESSION');
} else {
    ini_set('session.name', 'HS_SESSION');
}
session_start();

require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: trangdangky.php");
    exit;
}

$role     = trim($_POST['role'] ?? '');
$hoten    = trim($_POST['hoten'] ?? '');
$gioitinh = $_POST['gioitinh'] ?? null;
$email    = trim($_POST['email'] ?? '');
$monhoc   = trim($_POST['monhoc_yeuthich'] ?? '');
$password = $_POST['password'] ?? '';
$confirm  = $_POST['confirm_password'] ?? '';

function redirect_err($msg) {
    header("Location: trangdangky.php?error=" . urlencode($msg));
    exit;
}

if (!in_array($role, ['student', 'teacher'])) {
    redirect_err("Vai trò không hợp lệ!");
}

if (empty($hoten) || empty($email) || empty($password)) {
    redirect_err("Vui lòng điền đầy đủ thông tin bắt buộc!");
}

if (mb_strlen($hoten) < 2 || mb_strlen($hoten) > 100) {
    redirect_err("Họ tên phải từ 2 đến 100 ký tự!");
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    redirect_err("Địa chỉ email không đúng định dạng!");
}

if (strlen($password) < 6) {
    redirect_err("Mật khẩu phải có ít nhất 6 ký tự!");
}

if ($password !== $confirm) {
    redirect_err("Mật khẩu nhập lại không khớp!");
}

// Kiểm tra email đã tồn tại
$stmt_check = $conn->prepare("SELECT id FROM users WHERE email = ?");
$stmt_check->bind_param("s", $email);
$stmt_check->execute();
if ($stmt_check->get_result()->num_rows > 0) {
    redirect_err("Email này đã được sử dụng!");
}

// Mã hoá mật khẩu và lưu vào DB
$matkhau_hash = password_hash($password, PASSWORD_DEFAULT);

$stmt_ins = $conn->prepare(
    "INSERT INTO users (hoten, gioitinh, email, monhoc_yeuthich, matkhau, role) VALUES (?, ?, ?, ?, ?, ?)"
);
$stmt_ins->bind_param("ssssss", $hoten, $gioitinh, $email, $monhoc, $matkhau_hash, $role);

if (!$stmt_ins->execute()) {
    redirect_err("Lỗi hệ thống, vui lòng thử lại!");
}

$new_user_id = $conn->insert_id;

// Tạo OTP xác minh đăng ký
$otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

// Xoá OTP cũ (nếu có) rồi lưu mới vào bảng otp_codes
$del = $conn->prepare("DELETE FROM otp_codes WHERE user_id = ?");
$del->bind_param("i", $new_user_id);
$del->execute();

$expires_db = date('Y-m-d H:i:s', time() + 300);
$stmt_otp = $conn->prepare("INSERT INTO otp_codes (user_id, otp_code, expires_at) VALUES (?, ?, ?)");
$stmt_otp->bind_param("iss", $new_user_id, $otp, $expires_db);
$stmt_otp->execute();

// Lưu thông tin vào session để xacnhan_otp.php xử lý
session_regenerate_id(true);
$_SESSION['otp_pending']  = true;
$_SESSION['otp_user_id']  = $new_user_id;
$_SESSION['otp_role']     = $role;
$_SESSION['otp_ho_ten']   = $hoten;
$_SESSION['otp_email']    = $email;
$_SESSION['otp_code']     = $otp;
$_SESSION['otp_expires']  = time() + 300;
$_SESSION['otp_type']     = 'register';

// Gửi OTP qua email
require_once 'lib/mailer.php';
$_SESSION['otp_mail_sent'] = smtp_send_otp($email, $otp);

header("Location: xacnhan_otp.php?role=" . urlencode($role));
exit;
?>