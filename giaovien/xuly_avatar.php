<?php
session_start();
require_once '../config.php'; // Kết nối database

// Kiểm tra đăng nhập và có file gửi lên
if (!isset($_SESSION['user_id'])) {
    header("Location: ../trangdangnhap.php");
    exit;
}

if (isset($_FILES['file_avatar']) && $_FILES['file_avatar']['error'] === UPLOAD_ERR_OK) {

    $upload_dir = 'uploads/';
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    $max_size     = 2 * 1024 * 1024; // 2MB
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $file_mime    = mime_content_type($_FILES['file_avatar']['tmp_name']);

    if ($_FILES['file_avatar']['size'] <= $max_size && in_array($file_mime, $allowed_types)) {

        $ext       = pathinfo($_FILES['file_avatar']['name'], PATHINFO_EXTENSION);
        $file_name = "USER_" . $_SESSION['user_id'] . "_" . time() . "." . $ext;
        $target    = $upload_dir . $file_name;

        if (move_uploaded_file($_FILES['file_avatar']['tmp_name'], $target)) {

            // Xóa ảnh cũ khỏi server (nếu có và không phải avatar mặc định)
            $stmt_old = $conn->prepare("SELECT avatar FROM users WHERE id = ?");
            $stmt_old->bind_param("i", $_SESSION['user_id']);
            $stmt_old->execute();
            $old = $stmt_old->get_result()->fetch_assoc();
            if (!empty($old['avatar']) && file_exists($old['avatar'])) {
                @unlink($old['avatar']);
            }

            // Cập nhật đường dẫn ảnh mới vào DATABASE
            $stmt = $conn->prepare("UPDATE users SET avatar = ? WHERE id = ?");
            $stmt->bind_param("si", $target, $_SESSION['user_id']);
            $stmt->execute();

            // Đồng bộ vào Session để hiển thị ngay mà không cần reload thêm
            $_SESSION['gv_avatar'] = $target;
        }
    }
}

// Quay về trang trước
if (isset($_SERVER['HTTP_REFERER'])) {
    header('Location: ' . $_SERVER['HTTP_REFERER']);
} else {
    header('Location: index.php');
}
exit;