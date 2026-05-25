<?php
ini_set('session.name', 'GV_SESSION');
session_start();
require_once '../config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../trangdangnhap.php");
    exit;
}

if (isset($_FILES['file_avatar']) && $_FILES['file_avatar']['error'] === UPLOAD_ERR_OK) {

    $upload_dir = '../uploads/'; 
    
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    $max_size = 5 * 1024 * 1024; // 5MB
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    
    $file_mime = mime_content_type($_FILES['file_avatar']['tmp_name']);

    if ($_FILES['file_avatar']['size'] > $max_size) {
        $_SESSION['error'] = "Ảnh quá lớn! Vui lòng chọn ảnh dưới 5MB.";
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit;
    }

    if (!in_array($file_mime, $allowed_types)) {
        $_SESSION['error'] = "Chỉ chấp nhận file ảnh (jpg, png, gif, webp).";
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit;
    }

    $ext = pathinfo($_FILES['file_avatar']['name'], PATHINFO_EXTENSION);
    $file_name = "AVATAR_" . $_SESSION['user_id'] . "_" . time() . "." . strtolower($ext);
    $target_path = $upload_dir . $file_name;

    if (move_uploaded_file($_FILES['file_avatar']['tmp_name'], $target_path)) {

        // Xóa ảnh cũ nếu có
        $stmt_old = $conn->prepare("SELECT avatar FROM users WHERE id = ?");
        $stmt_old->bind_param("i", $_SESSION['user_id']);
        $stmt_old->execute();
        $old = $stmt_old->get_result()->fetch_assoc();

        if (!empty($old['avatar'])) {
            $old_file = '../uploads/' . $old['avatar'];
            if (file_exists($old_file)) {
                @unlink($old_file);
            }
        }

        // Cập nhật vào database (chỉ lưu tên file)
        $stmt = $conn->prepare("UPDATE users SET avatar = ? WHERE id = ?");
        $stmt->bind_param("si", $file_name, $_SESSION['user_id']);
        $stmt->execute();

        $_SESSION['success'] = "✅ Đổi ảnh đại diện thành công!";
    } else {
        $_SESSION['error'] = "❌ Không thể tải ảnh lên server. Vui lòng thử lại.";
    }
} else {
    $_SESSION['error'] = "❌ Không có file ảnh nào được chọn.";
}

header('Location: ' . $_SERVER['HTTP_REFERER']);
exit;
?>