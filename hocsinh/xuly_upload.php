<?php
session_start();

// 1. BẬT HIỂN THỊ LỖI (Để nếu có lỗi nó sẽ hiện chữ ra thay vì màn hình trắng)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Kiểm tra quyền
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'student') {
    die("Bạn không có quyền truy cập!");
}

// Nếu có dữ liệu gửi lên (Bấm nút gửi)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // 2. KIỂM TRA DUNG LƯỢNG FILE
    // Mặc định XAMPP chỉ cho tải file tối đa 2MB. Nếu ảnh nặng hơn, nó sẽ ngắt ngầm.
    if (empty($_POST) && empty($_FILES)) {
        echo "<script>alert('Lỗi: File tải lên quá nặng (vượt quá 2MB)! Vui lòng chọn ảnh/file nhỏ hơn.'); window.location.href='profile.php';</script>";
        exit();
    }

    $type = isset($_POST['type']) ? $_POST['type'] : '';
    $target_dir = "../uploads/"; 
    //Tự động tạo thư mục nếu chưa có!
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    // --- XỬ LÝ ĐỔI ẢNH ĐẠI DIỆN ---
    if ($type == 'change_avatar') {
        // Kiểm tra xem có nhận được file ảnh không
        if (isset($_FILES["avatar"]) && $_FILES["avatar"]["error"] == 0) {
            $file_name = "HS_" . time() . "_" . basename($_FILES["avatar"]["name"]);
            $target_file = $target_dir . $file_name;

            if (move_uploaded_file($_FILES["avatar"]["tmp_name"], $target_file)) {
                $_SESSION['avatar'] = $target_file; 
                echo "<script>alert('Tuyệt vời! Đã cập nhật ảnh đại diện thành công!'); window.location.href='profile.php';</script>";
            } else {
                echo "<script>alert('Lỗi: Không thể đưa file vào thư mục uploads. Hãy chắc chắn thư mục uploads đã được tạo!'); window.location.href='profile.php';</script>";
            }
        } else {
            echo "<script>alert('Lỗi: File ảnh bị hỏng hoặc chưa được chọn!'); window.location.href='profile.php';</script>";
        }
    }
    // --- XỬ LÝ CẬP NHẬT TÊN MỚI ---
    if ($type == 'update_profile') {
        if (isset($_POST['ho_ten_moi']) && !empty($_POST['ho_ten_moi'])) {
            $ten_moi = $_POST['ho_ten_moi'];
            
            // 1. Cập nhật tên mới vào Session để hiển thị ngay lập tức trên web
            $_SESSION['ho_ten'] = $ten_moi;

            echo "<script>alert('Đã cập nhật tên thành: $ten_moi'); window.location.href='profile.php';</script>";
        } else {
            echo "<script>alert('Tên không được để trống!'); window.location.href='profile.php';</script>";
        }
    }

    // --- XỬ LÝ NỘP BÀI TẬP ---
// --- XỬ LÝ NỘP BÀI TẬP ---
    if ($type == 'submit_assignment') {
        include '../config.php'; // Đảm bảo kết nối database ở đây nếu chưa có

        $id_baitap = isset($_POST['id_baitap']) ? intval($_POST['id_baitap']) : 0;
        $id_lop = isset($_POST['id_lop']) ? intval($_POST['id_lop']) : 0;
        $id_hocsinh = $_SESSION['user_id'];
        $link_baitap = isset($_POST['link_baitap']) ? trim($_POST['link_baitap']) : '';
        $file_name = null;

        // Xử lý nộp file nếu có tải file lên
        if (isset($_FILES["file_baitap"]) && $_FILES["file_baitap"]["error"] == 0) {
            $file_name = "BAITAP_" . $id_hocsinh . "_" . time() . "_" . basename($_FILES["file_baitap"]["name"]);
            $target_file = $target_dir . $file_name;
            
            if (!move_uploaded_file($_FILES["file_baitap"]["tmp_name"], $target_file)) {
                echo "<script>alert('Lỗi không thể lưu file bài tập vào thư mục!'); window.history.back();</script>";
                exit();
            }
        }

        // Kiểm tra xem học sinh có nộp gì không (phải có file hoặc có link)
        if (empty($file_name) && empty($link_baitap)) {
            echo "<script>alert('Lỗi: Em chưa chọn file hoặc chưa dán link bài tập!'); window.history.back();</script>";
            exit();
        }

        $sql_nop = "INSERT INTO nop_bai (bai_tap_id, student_id, file_nop, link_nop, ngay_nop) VALUES (?, ?, ?, ?, NOW())";
        $stmt_nop = $conn->prepare($sql_nop);
        
        if (!$stmt_nop) {
            // Dự phòng nếu tên bảng là nop_bai_tap
            $sql_nop = "INSERT INTO nop_bai_tap (bai_tap_id, student_id, file_nop, link_nop, ngay_nop) VALUES (?, ?, ?, ?, NOW())";
            $stmt_nop = $conn->prepare($sql_nop);
        }

        if ($stmt_nop) {
            $stmt_nop->bind_param("iiss", $id_baitap, $id_hocsinh, $file_name, $link_nop);
            if ($stmt_nop->execute()) {
                echo "<script>alert('Nộp bài thành công!'); window.location.href='phonghoc.php?id=" . $id_lop . "';</script>";
            } else {
                echo "<script>alert('Lỗi kết nối database khi nộp bài: " . $stmt_nop->error . "'); window.history.back();</script>";
            }
        } else {
            echo "<script>alert('Lỗi: Hệ thống không tìm thấy bảng nop_bai hoặc nop_bai_tap trong DB!'); window.history.back();</script>";
        }
        exit();
    }
} else {
    // Nếu vô tình gõ trực tiếp link xuly_upload.php vào trình duyệt thì báo lỗi này
    echo "<h2 style='text-align:center; margin-top:50px; font-family:sans-serif;'>Trang này dùng để xử lý ngầm. Vui lòng quay lại!</h2>";
}
?>