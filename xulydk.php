<?php
session_start();

$host = "localhost";
$user = "root";       
$pass = "";           
$dbname = "quanly_hoctap";

$conn = mysqli_connect($host, $user, $pass, $dbname);
if (!$conn) {
    die("Lỗi kết nối: " . mysqli_connect_error());
}
mysqli_set_charset($conn, "utf8");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $role = $_POST['role']; 
    $hoten = trim($_POST['hoten']);
    $gioitinh = $_POST['gioitinh'] ?? null;
    $email = trim($_POST['email']);
    $monhoc = trim($_POST['monhoc_yeuthich'] ?? '');
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if ($password !== $confirm_password) {
        header("Location: trangdangky.php?error=" . urlencode("Mật khẩu nhập lại không khớp!"));
        exit;
    }

    $sql_check = "SELECT * FROM users WHERE email = '$email'";
    if(mysqli_num_rows(mysqli_query($conn, $sql_check)) > 0) {
        header("Location: trangdangky.php?error=" . urlencode("Email này đã được sử dụng!"));
        exit;
    }

    $sql_insert = "INSERT INTO users (hoten, gioitinh, email, monhoc_yeuthich, matkhau, role) 
                   VALUES ('$hoten', '$gioitinh', '$email', '$monhoc', '$password', '$role')";
    
    if (mysqli_query($conn, $sql_insert)) {
        header("Location: trangdangky.php?success=" . urlencode("Đăng ký thành công!"));
        exit;
    } else {
        header("Location: trangdangky.php?error=" . urlencode("Lỗi hệ thống!"));
        exit;
    }
}
header("Location: trangdangky.php");
?>