<?php
session_start();
require_once '../config.php'; // Nạp kết nối database (Đường dẫn tùy thuộc vào cấu trúc thư mục của bạn)

// Kiểm tra xem có nhận được file và user đã đăng nhập chưa
if (isset($_FILES['file_avatar']) && $_FILES['file_avatar']['error'] === UPLOAD_ERR_OK && isset($_SESSION['user_id'])) {
    
    $upload_dir = 'uploads/'; 
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    $max_size = 2 * 1024 * 1024; // 2MB
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $file_mime_type = mime_content_type($_FILES['file_avatar']['tmp_name']);

    if ($_FILES['file_avatar']['size'] <= $max_size && in_array($file_mime_type, $allowed_types)) {
        
        $file_extension = pathinfo($_FILES['file_avatar']['name'], PATHINFO_EXTENSION);
        $file_name = "USER_" . $_SESSION['user_id'] . "_" . time() . "." . $file_extension;
        $target_file = $upload_dir . $file_name;

        // Di chuyển file vào thư mục
        if (move_uploaded_file($_FILES['file_avatar']['tmp_name'], $target_file)) {
            
            // 1. Cập nhật vào DATABASE
            $stmt = $conn->prepare("UPDATE users SET avatar = ? WHERE id = ?");
            $stmt->bind_param("si", $target_file, $_SESSION['user_id']);
            $stmt->execute();

            // 2. Cập nhật vào SESSION
            $_SESSION['gv_avatar'] = $target_file;
        }
    }
}

// Quay lại trang trước đó
if (isset($_SERVER['HTTP_REFERER'])) {
    header('Location: ' . $_SERVER['HTTP_REFERER']);
} else {
    header('Location: index.php'); 
}
exit;
?>