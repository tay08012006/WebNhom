<?php
session_start();

// 1. Khai báo tài khoản "mồi" để test
$email_giaovien_test = "giaovien@gmail.com";
$pass_giaovien_test = "123456";

// 2. Nhận dữ liệu từ form gửi sang
if (isset($_POST['email']) && isset($_POST['password'])) {
    
    $email = $_POST['email'];
    $password = $_POST['password'];
    $role = $_POST['role']; 

    // 3. Kiểm tra logic đăng nhập cho Giáo viên
    if ($role === 'teacher') {
        if ($email === $email_giaovien_test && $password === $pass_giaovien_test) {
            // Đăng nhập đúng -> Tạo Session
            $_SESSION['user_id'] = 999; 
            $_SESSION['role'] = 'teacher';
            $_SESSION['ho_ten'] = "Giáo Viên Demo";

            // Chuyển hướng sang thư mục giaovien của bạn
            header("Location: giaovien/index.php");
            exit;
        } else {
            // Sai pass
            header("Location: trangdangnhap.php?error=Sai Email hoặc Mật khẩu test!");
            exit;
        }
    } 
    
} else {
    // Truy cập bậy bạ -> đuổi về
    header("Location: trangdangnhap.php");
    exit;
}
?>