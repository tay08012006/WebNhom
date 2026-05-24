<?php
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

// Kiểm tra role hợp lệ
if (!in_array($role, ['student', 'teacher'])) {
    redirect_err("Vai trò không hợp lệ!");
}

// Kiểm tra đầu vào
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

// Kiểm tra email đã tồn tại chưa
$stmt_check = $conn->prepare("SELECT id FROM users WHERE email = ?");
$stmt_check->bind_param("s", $email);
$stmt_check->execute();
if ($stmt_check->get_result()->num_rows > 0) {
    redirect_err("Email này đã được sử dụng!");
}

// Mã hoá mật khẩu
$matkhau_hash = password_hash($password, PASSWORD_DEFAULT);

// Lưu vào database
$stmt_ins = $conn->prepare(
    "INSERT INTO users (hoten, gioitinh, email, monhoc_yeuthich, matkhau, role) VALUES (?, ?, ?, ?, ?, ?)"
);
$stmt_ins->bind_param("ssssss", $hoten, $gioitinh, $email, $monhoc, $matkhau_hash, $role);

if ($stmt_ins->execute()) {
    header("Location: trangdangky.php?success=" . urlencode("Đăng ký thành công! Hãy đăng nhập."));
} else {
    header("Location: trangdangky.php?error=" . urlencode("Lỗi hệ thống, vui lòng thử lại!"));
}
exit;
?>
