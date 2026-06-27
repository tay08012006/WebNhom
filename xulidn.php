<?php

//  XULIDN.PHP — Xử lý đăng nhập
//  Luồng: Kiểm tra role → Validate → Xác thực mật khẩu → Tạo session

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

//  Kiểm tra mật khẩu
//  Hỗ trợ cả bcrypt (mới) và plain text (cũ — tự động hash lại)

$mat_khau_dung = false;
$is_bcrypt = (
    !empty($user['matkhau']) &&
    strlen($user['matkhau']) >= 60 &&
    str_starts_with($user['matkhau'], '$2')
);
if ($is_bcrypt) {
    $mat_khau_dung = password_verify($password, $user['matkhau']);
} else {
    // Plain text cũ → so sánh rồi tự động nâng cấp lên bcrypt
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
//  Đăng nhập thành công — Tạo session

session_regenerate_id(true);
$_SESSION['user_id'] = $user['id'];
$_SESSION['role']    = $user['role'];
$_SESSION['ho_ten']  = $user['hoten'];
$_SESSION['hoten']   = $user['hoten'];
$_SESSION['email']   = $user['email'];

header("Location: " . ($user['role'] === 'teacher' ? 'giaovien/index.php' : 'hocsinh/index.php'));
exit;
?>