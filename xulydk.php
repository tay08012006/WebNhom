<?php
session_start();

// 1. CẤU HÌNH KẾT NỐI DATABASE
$host = "localhost";
$user = "root";       
$pass = "";           
$dbname = "quanly_hoctap"; // ĐIỀN TÊN DATABASE CỦA BẠN VÀO ĐÂY

$conn = mysqli_connect($host, $user, $pass, $dbname);
if (!$conn) {
    die("Lỗi kết nối CSDL: " . mysqli_connect_error());
}
mysqli_set_charset($conn, "utf8");

// 2. XỬ LÝ ĐĂNG KÝ
if (isset($_POST['email']) && isset($_POST['password'])) {
    
    $role = $_POST['role']; 
    $hoten = trim($_POST['hoten']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // A. Kiểm tra mật khẩu nhập lại
    if ($password !== $confirm_password) {
        header("Location: trangdangky.php?error=" . urlencode("Mật khẩu nhập lại không khớp!"));
        exit;
    }

    // B. Kiểm tra email đã tồn tại chưa
    $sql_check = "SELECT * FROM users WHERE email = '$email'";
    $result_check = mysqli_query($conn, $sql_check);

    if(mysqli_num_rows($result_check) > 0) {
        header("Location: trangdangky.php?error=" . urlencode("Email này đã được sử dụng!"));
        exit;
    }

    // C. Lưu thông tin vào database
    $sql_insert = "INSERT INTO users (hoten, email, matkhau, role) VALUES ('$hoten', '$email', '$password', '$role')";
    
    if (mysqli_query($conn, $sql_insert)) {
        // LƯU THÀNH CÔNG -> Đẩy về trang đăng ký để hiện khung thông báo xanh
        header("Location: trangdangky.php?success=" . urlencode("Đăng ký thành công!"));
        exit;
    } else {
        header("Location: trangdangky.php?error=" . urlencode("Lỗi hệ thống khi lưu dữ liệu!"));
        exit;
    }
} else {
    // Nếu truy cập trực tiếp file này -> Đuổi về trang đăng ký
    header("Location: trangdangky.php");
    exit;
}
?>