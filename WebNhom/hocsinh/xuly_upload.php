<?php
ini_set('session.name', 'HS_SESSION');
session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'student') {
    die("Bạn không có quyền truy cập!");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    if (empty($_POST) && empty($_FILES)) {
        echo "<script>alert('Lỗi: File tải lên quá nặng (vượt quá 2MB)! Vui lòng chọn ảnh/file nhỏ hơn.'); window.location.href='profile.php';</script>";
        exit();
    }

    $type = isset($_POST['type']) ? $_POST['type'] : '';
    $target_dir = "../uploads/"; 
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    // --- XỬ LÝ ĐỔI ẢNH ĐẠI DIỆN ---
    if ($type == 'change_avatar') {
        if (isset($_FILES["avatar"]) && $_FILES["avatar"]["error"] == 0) {

            // Kiểm tra loại file
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            $file_mime = mime_content_type($_FILES["avatar"]["tmp_name"]);
            if (!in_array($file_mime, $allowed_types)) {
                echo "<script>alert('Lỗi: Chỉ chấp nhận file ảnh (jpg, png, gif, webp)!'); window.location.href='profile.php';</script>";
                exit();
            }

            $ext = strtolower(pathinfo($_FILES["avatar"]["name"], PATHINFO_EXTENSION));
            $file_name = "HS_" . $_SESSION['user_id'] . "_" . time() . "." . $ext;
            $target_file = $target_dir . $file_name;

            if (move_uploaded_file($_FILES["avatar"]["tmp_name"], $target_file)) {

                // Kết nối DB để cập nhật avatar
                include '../config.php';

                // Xóa ảnh cũ nếu có
                $stmt_old = $conn->prepare("SELECT avatar FROM users WHERE id = ?");
                $stmt_old->bind_param("i", $_SESSION['user_id']);
                $stmt_old->execute();
                $old = $stmt_old->get_result()->fetch_assoc();
                if (!empty($old['avatar'])) {
                    $old_path = $target_dir . $old['avatar'];
                    if (file_exists($old_path)) {
                        @unlink($old_path);
                    }
                }

                // Lưu tên file vào database
                $stmt_update = $conn->prepare("UPDATE users SET avatar = ? WHERE id = ?");
                $stmt_update->bind_param("si", $file_name, $_SESSION['user_id']);
                $stmt_update->execute();

                // Cập nhật session (lưu tên file, không phải đường dẫn đầy đủ)
                $_SESSION['avatar'] = $file_name;

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
            $_SESSION['ho_ten'] = $ten_moi;
            echo "<script>alert('Đã cập nhật tên thành: $ten_moi'); window.location.href='profile.php';</script>";
        } else {
            echo "<script>alert('Tên không được để trống!'); window.location.href='profile.php';</script>";
        }
    }

    // --- XỬ LÝ NỘP BÀI TẬP ---
    if ($type == 'submit_assignment') {
        include '../config.php';

        $id_baitap = isset($_POST['id_baitap']) ? intval($_POST['id_baitap']) : 0;
        $id_lop = isset($_POST['id_lop']) ? intval($_POST['id_lop']) : 0;
        $id_hocsinh = $_SESSION['user_id'];
        $link_baitap = isset($_POST['link_baitap']) ? trim($_POST['link_baitap']) : '';
        $file_name = null;

        if (isset($_FILES["file_baitap"]) && $_FILES["file_baitap"]["error"] == 0) {
            $file_name = "BAITAP_" . $id_hocsinh . "_" . time() . "_" . basename($_FILES["file_baitap"]["name"]);
            $target_file = $target_dir . $file_name;
            
            if (!move_uploaded_file($_FILES["file_baitap"]["tmp_name"], $target_file)) {
                echo "<script>alert('Lỗi không thể lưu file bài tập vào thư mục!'); window.history.back();</script>";
                exit();
            }
        }

        if (empty($file_name) && empty($link_baitap)) {
            echo "<script>alert('Lỗi: Em chưa chọn file hoặc chưa dán link bài tập!'); window.history.back();</script>";
            exit();
        }

        $sql_nop = "INSERT INTO nop_bai (bai_tap_id, student_id, file_nop, link_nop, ngay_nop) VALUES (?, ?, ?, ?, NOW())";
        $stmt_nop = $conn->prepare($sql_nop);
        
        if (!$stmt_nop) {
            $sql_nop = "INSERT INTO nop_bai_tap (bai_tap_id, student_id, file_nop, link_nop, ngay_nop) VALUES (?, ?, ?, ?, NOW())";
            $stmt_nop = $conn->prepare($sql_nop);
        }

        if ($stmt_nop) {
            $stmt_nop->bind_param("iiss", $id_baitap, $id_hocsinh, $file_name, $link_baitap);
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
    echo "<h2 style='text-align:center; margin-top:50px; font-family:sans-serif;'>Trang này dùng để xử lý ngầm. Vui lòng quay lại!</h2>";
}
?>
