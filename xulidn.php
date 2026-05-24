<?php
$role_input = trim($_POST['role'] ?? '');
if ($role_input === 'teacher') {
    ini_set('session.name', 'GV_SESSION');
} else {
    ini_set('session.name', 'HS_SESSION');
}
session_start();

require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['email']) || empty($_POST['password'])) {
    header("Location: trangdangnhap.php");
    exit;
}

$email    = trim($_POST['email']);
$password = $_POST['password'];
$role     = $role_input;

if (!in_array($role, ['student', 'teacher'])) {
    header("Location: trangdangnhap.php?error=" . urlencode("Vai trò không hợp lệ!"));
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header("Location: trangdangnhap.php?error=" . urlencode("Địa chỉ email không đúng định dạng!"));
    exit;
}

$stmt = $conn->prepare("SELECT id, hoten, email, matkhau, role FROM users WHERE email = ? AND role = ?");
$stmt->bind_param("ss", $email, $role);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: trangdangnhap.php?error=" . urlencode("Email hoặc mật khẩu không chính xác!"));
    exit;
}

$user = $result->fetch_assoc();

// ============================================================
// KIỂM TRA MẬT KHẨU
// - Nếu trong DB đã là bcrypt ($2y$...) → dùng password_verify
// - Nếu còn plain text (dữ liệu cũ) → so sánh trực tiếp rồi
//   tự động hash lại để lần sau dùng bcrypt
// ============================================================
$mat_khau_dung = false;
$is_bcrypt = (
    !empty($user['matkhau']) &&
    strlen($user['matkhau']) >= 60 &&
    str_starts_with($user['matkhau'], '$2')
);

if ($is_bcrypt) {
    // Mật khẩu đã hash đúng chuẩn → chỉ verify, KHÔNG hash lại
    $mat_khau_dung = password_verify($password, $user['matkhau']);
} else {
    // Mật khẩu plain text cũ → so sánh rồi hash lại một lần duy nhất
    $mat_khau_dung = ($password === $user['matkhau']);
    if ($mat_khau_dung) {
        $hash_moi = password_hash($password, PASSWORD_DEFAULT);
        $upd = $conn->prepare("UPDATE users SET matkhau = ? WHERE id = ?");
        $upd->bind_param("si", $hash_moi, $user['id']);
        $upd->execute();
    }
}

if (!$mat_khau_dung) {
    header("Location: trangdangnhap.php?error=" . urlencode("Email hoặc mật khẩu không chính xác!"));
    exit;
}

// ============================================================
// KIỂM TRA THIẾT BỊ TIN CẬY
// Chỉ bỏ qua OTP nếu cookie hợp lệ VÀ bảng trusted_devices tồn tại
// ============================================================
$cookie_name  = 'trusted_device_' . $user['id'];
$device_token = $_COOKIE[$cookie_name] ?? '';
$is_trusted   = false;

if (!empty($device_token)) {
    // Kiểm tra bảng trusted_devices có tồn tại không trước khi query
    $tbl_check = $conn->query("SHOW TABLES LIKE 'trusted_devices'");
    if ($tbl_check && $tbl_check->num_rows > 0) {
        $stmt_dev = $conn->prepare(
            "SELECT id FROM trusted_devices WHERE user_id = ? AND token = ? AND expires_at > NOW()"
        );
        $stmt_dev->bind_param("is", $user['id'], $device_token);
        $stmt_dev->execute();
        if ($stmt_dev->get_result()->num_rows > 0) {
            $is_trusted = true;
        }
    }
}

if ($is_trusted) {
    // Thiết bị đã tin cậy → đăng nhập thẳng, không cần OTP
    session_regenerate_id(true);
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['role']    = $user['role'];
    $_SESSION['ho_ten']  = $user['hoten'];
    $_SESSION['hoten']   = $user['hoten'];
    $_SESSION['email']   = $user['email'];

    header("Location: " . ($user['role'] === 'teacher' ? 'giaovien/index.php' : 'hocsinh/index.php'));
    exit;
}

// Thiết bị lạ → yêu cầu OTP
$otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

session_regenerate_id(true);
$_SESSION['otp_pending']  = true;
$_SESSION['otp_user_id']  = $user['id'];
$_SESSION['otp_role']     = $user['role'];
$_SESSION['otp_ho_ten']   = $user['hoten'];
$_SESSION['otp_email']    = $user['email'];
$_SESSION['otp_code']     = $otp;
$_SESSION['otp_expires']  = time() + 300;
$_SESSION['otp_type']     = 'login';

$del = $conn->prepare("DELETE FROM otp_codes WHERE user_id = ?");
$del->bind_param("i", $user['id']);
$del->execute();
$expires_db = date('Y-m-d H:i:s', time() + 300);
$stmt_otp = $conn->prepare("INSERT INTO otp_codes (user_id, otp_code, expires_at) VALUES (?, ?, ?)");
$stmt_otp->bind_param("iss", $user['id'], $otp, $expires_db);
$stmt_otp->execute();

require_once 'lib/mailer.php';
$_SESSION['otp_mail_sent'] = smtp_send_otp($user['email'], $otp);

header("Location: xacnhan_otp.php?role=" . urlencode($role));
exit;
?>
