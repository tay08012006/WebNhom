<?php
session_start();
include '../config.php';   // ← Sửa thành config.php (không dùng dp.php)

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Nhận mã lớp học sinh nhập
    $ma_lop = strtoupper(trim($_POST['ma_lop']));
    
    if (!isset($_SESSION['user_id'])) {
        die("Vui lòng đăng nhập trước khi tham gia lớp học!");
    }
    
    $id_hocsinh = $_SESSION['user_id']; 

    // 1. Kiểm tra mã lớp trong bảng classes
    $sql_check = "SELECT id FROM classes WHERE ma_lop = ?";
    $stmt = $conn->prepare($sql_check);
    $stmt->bind_param("s", $ma_lop);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $lop = $result->fetch_assoc();
        $id_lop = $lop['id'];
        
        // 2. Kiểm tra xem học sinh đã tham gia lớp này chưa
        $sql_joined = "SELECT id FROM class_enrollments WHERE user_id = ? AND class_id = ?";
        $stmt_joined = $conn->prepare($sql_joined);
        $stmt_joined->bind_param("ii", $id_hocsinh, $id_lop);
        $stmt_joined->execute();
        
        if ($stmt_joined->get_result()->num_rows > 0) {
            header("Location: lophoc.php?status=already_joined");
            exit();
        } else {
            // 3. Thêm vào bảng class_enrollments
            $sql_insert = "INSERT INTO class_enrollments (user_id, class_id) VALUES (?, ?)";
            $stmt_insert = $conn->prepare($sql_insert);
            $stmt_insert->bind_param("ii", $id_hocsinh, $id_lop);
            
            if ($stmt_insert->execute()) {
                header("Location: lophoc.php?status=success");
                exit();
            } else {
                die("Lỗi khi tham gia lớp học!");
            }
        }
    } else {
        // Mã lớp không tồn tại
        header("Location: lophoc.php?status=invalid_code");
        exit();
    }
} else {
    header("Location: thamgialop.php");
    exit();
}
?>