<?php
session_start();

if (isset($_FILES['file_avatar']) && $_FILES['file_avatar']['error'] === UPLOAD_ERR_OK) {
    // Thư mục lưu ảnh (sẽ tự tạo thư mục uploads/avatars nếu chưa có)
    $upload_dir = 'uploads/avatars/'; 
    
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    $file_tmp = $_FILES['file_avatar']['tmp_name'];
    // Đổi tên file để không bị trùng (thêm thời gian vào trước tên file)
    $file_name = time() . '_' . basename($_FILES['file_avatar']['name']);
    $target_file = $upload_dir . $file_name;

    // Di chuyển file vào thư mục và lưu đường dẫn vào Session
    if (move_uploaded_file($file_tmp, $target_file)) {
        $_SESSION['gv_avatar'] = $target_file;
    }
}

// Chuyển hướng người dùng quay lại trang phonghoc.php ngay lập tức
header('Location: ' . $_SERVER['HTTP_REFERER']);
exit;
?>