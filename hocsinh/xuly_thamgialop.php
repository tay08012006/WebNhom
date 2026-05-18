<?php
session_start();
// Kết nối CSDL bằng file dp.php ở thư mục gốc của bạn
include '../dp.php'; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Nhận mã lớp học sinh nhập từ form (ví dụ ô input đặt name="ma_lop")
    $ma_lop = strtoupper(trim($_POST['ma_lop']));
    
    // Giả sử lúc học sinh đăng nhập, bạn đã lưu ID học sinh vào $_SESSION['user_id']
    if (!isset($_SESSION['user_id'])) {
        die("Vui lòng đăng nhập trước khi tham gia lớp học!");
    }
    $id_hocsinh = $_SESSION['user_id']; 

    // 1. Truy vấn kiểm tra mã lớp trong bảng 'lop_hoc' (do giáo viên tạo)
    $sql_check = "SELECT id FROM lop_hoc WHERE ma_lop = ?";
    $stmt = $conn->prepare($sql_check);
    $stmt->bind_param("s", $ma_lop);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $lop = $result->fetch_assoc();
        $id_lop = $lop['id'];
        
        // 2. Kiểm tra xem học sinh đã tham gia lớp này chưa
        $sql_joined = "SELECT id FROM hocsinh_lop WHERE id_hocsinh = ? AND id_lop = ?";
        $stmt_joined = $conn->prepare($sql_joined);
        $stmt_joined->bind_param("ii", $id_hocsinh, $id_lop);
        $stmt_joined->execute();
        
        if ($stmt_joined->get_result()->num_rows > 0) {
            // Đã tham gia rồi -> Chuyển hướng về trang danh sách kèm thông báo báo lỗi
            header("Location: lophoc.php?status=already_joined");
            exit();
        } else {
            // 3. Tiến hành thêm một dòng vào bảng 'hocsinh_lop' để ghi nhận tham gia thành công
            $sql_insert = "INSERT INTO hocsinh_lop (id_hocsinh, id_lop) VALUES (?, ?)";
            $stmt_insert = $conn->prepare($sql_insert);
            $stmt_insert->bind_param("ii", $id_hocsinh, $id_lop);
            
            if ($stmt_insert->execute()) {
                // Thành công -> Chuyển hướng về trang danh sách lớp học
                header("Location: lophoc.php?status=success");
                exit();
            }
        }
    } else {
        // Mã không tồn tại -> Chuyển hướng về và báo lỗi sai mã
        header("Location: lophoc.php?status=invalid_code");
        exit();
    }
}
?>