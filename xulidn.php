<?php
session_start();

// 1. CẤU HÌNH KẾT NỐI DATABASE
$host = "localhost";
$user = "root";       
$pass = "";           
$dbname = "quanly_hoctap"; // Tên Database của bạn

$conn = mysqli_connect($host, $user, $pass, $dbname);
if (!$conn) {
    die("Lỗi kết nối CSDL: " . mysqli_connect_error());
}
mysqli_set_charset($conn, "utf8");

// 2. NHẬN DỮ LIỆU TỪ FORM GỬI SANG
if (isset($_POST['email']) && isset($_POST['password'])) {
    
    // Ngăn chặn SQL Injection cơ bản
    $email = mysqli_real_escape_string($conn, trim($_POST['email']));
    $password = trim($_POST['password']);
    $role = $_POST['role']; // Biến này sẽ nhận 'student' hoặc 'teacher' từ tab bạn chọn

    // 3. KIỂM TRA THÔNG TIN TRONG DATABASE
    // Tìm kiếm tài khoản có email VÀ role trùng khớp
    $sql = "SELECT * FROM users WHERE email = '$email' AND role = '$role'";
    $result = mysqli_query($conn, $sql);

    // Nếu tìm thấy tài khoản
    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        
        // So sánh mật khẩu (Kiểm tra đúng pass)
        if ($password === $row['matkhau']) {
            
            // Đăng nhập đúng -> Tạo Session lưu thông tin người dùng
            $_SESSION['user_id'] = $row['id']; 
            $_SESSION['role'] = $row['role'];
            $_SESSION['ho_ten'] = $row['hoten'];
            $_SESSION['email'] = $row['email'];

            // 4. CHUYỂN HƯỚNG DỰA TRÊN VAI TRÒ (ROLE) - ĐÃ SỬA LẠI ĐƯỜNG DẪN Ở ĐÂY
          // 4. CHUYỂN HƯỚNG DỰA TRÊN VAI TRÒ (ROLE)
            if ($role === 'teacher') {
                // Đã sửa lại đúng đường dẫn vào thư mục giaovien
                header("Location: giaovien/index.php");
                exit;
            } else {
                // Chuyển hướng sang trang của Học sinh
                header("Location: hocsinh/index.php"); 
                exit;
            }
        } else {
            // Sai mật khẩu
            $error_message = "Địa chỉ email hoặc mật khẩu không chính xác!";
            header("Location: trangdangnhap.php?error=" . urlencode($error_message));
            exit;
        }
    } else {
        // Sai email hoặc sai Tab (Ví dụ: Tài khoản giáo viên nhưng lại chọn tab Học sinh để đăng nhập)
        $error_message = "Địa chỉ email hoặc mật khẩu không chính xác!";
        header("Location: trangdangnhap.php?error=" . urlencode($error_message));
        exit;
    }
    
} else {
    // Truy cập thẳng file bằng đường dẫn -> Đuổi về trang đăng nhập
    header("Location: trangdangnhap.php");
    exit;
}
?>