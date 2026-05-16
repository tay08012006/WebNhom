<?php
// Bật session để lưu trữ dữ liệu
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Đảm bảo mảng bảng tin đã tồn tại
if (!isset($_SESSION['bang_tin_list'])) {
    $_SESSION['bang_tin_list'] = [];
}

// Thiết lập thư mục lưu file upload (Đảm bảo đường dẫn này đúng với cấu trúc thư mục của bạn)
// Ở đây đang cấu hình là thư mục "uploads" nằm ở thư mục gốc (lùi lại 1 cấp)
$target_dir = "../uploads/"; 
if (!file_exists($target_dir)) {
    mkdir($target_dir, 0777, true); // Tự động tạo thư mục nếu chưa có
}

// Lấy action và mã lớp từ POST hoặc GET
$action = $_POST['action'] ?? $_GET['action'] ?? '';
$ma_lop = $_POST['ma_lop'] ?? $_GET['malop'] ?? '';

// --- HÀM HỖ TRỢ UPLOAD FILE ---
function uploadFiles($input_name, $target_dir) {
    $uploaded_files = [];
    if (isset($_FILES[$input_name])) {
        $total_files = count($_FILES[$input_name]['name']);
        for ($i = 0; $i < $total_files; $i++) {
            if ($_FILES[$input_name]['error'][$i] == 0) {
                // Thêm timestamp để tên file không bị trùng lặp
                $filename = time() . '_' . basename($_FILES[$input_name]['name'][$i]);
                $target_file = $target_dir . $filename;
                
                if (move_uploaded_file($_FILES[$input_name]['tmp_name'][$i], $target_file)) {
                    $uploaded_files[] = $filename;
                }
            }
        }
    }
    return $uploaded_files;
}

// --- 1. XỬ LÝ ĐĂNG THÔNG BÁO MỚI ---
if ($action == 'add_post') {
    $noi_dung = $_POST['noi_dung'] ?? '';
    $uploaded_files = uploadFiles('post_files', $target_dir);

    $new_post = [
        'id' => uniqid('bt_'), // Tạo ID ngẫu nhiên cho bảng tin
        'ma_lop' => $ma_lop,
        'noi_dung' => trim($noi_dung),
        'ngay_dang' => date('H:i d/m/Y'),
        'files' => $uploaded_files,
        'is_edited' => false
    ];

    $_SESSION['bang_tin_list'][] = $new_post;
    
    // Đăng xong thì quay về trang lớp học (tab bảng tin)
    header("Location: phonghoc.php?malop=$ma_lop&tab=bang-tin");
    exit;
}

// --- 2. XỬ LÝ CẬP NHẬT (SỬA) THÔNG BÁO ---
elseif ($action == 'update_post') {
    $id_update = $_POST['id_update'] ?? '';
    $noi_dung = $_POST['noi_dung'] ?? '';
    $kept_files = $_POST['old_files'] ?? []; // Các file cũ mà người dùng KHÔNG bấm xóa
    
    // Upload các file mới thêm vào
    $uploaded_files = uploadFiles('post_files', $target_dir);

    foreach ($_SESSION['bang_tin_list'] as &$post) {
        if ($post['id'] == $id_update) {
            // Xác định các file cũ đã bị người dùng bấm xóa để xóa khỏi thư mục
            $old_files = $post['files'] ?? [];
            $deleted_files = array_diff($old_files, $kept_files);
            foreach ($deleted_files as $df) {
                if (file_exists($target_dir . $df)) {
                    @unlink($target_dir . $df); // Xóa file vật lý
                }
            }

            // Cập nhật lại data
            $post['noi_dung'] = trim($noi_dung);
            $post['files'] = array_merge($kept_files, $uploaded_files); // Gộp file giữ lại + file mới
            $post['is_edited'] = true; // Đánh dấu là đã chỉnh sửa
            break;
        }
    }
    
    header("Location: phonghoc.php?malop=$ma_lop&tab=bang-tin");
    exit;
}

// --- 3. XỬ LÝ XÓA THÔNG BÁO ---
elseif ($action == 'delete_post') {
    $id_delete = $_GET['id'] ?? '';
    
    foreach ($_SESSION['bang_tin_list'] as $key => $post) {
        if ($post['id'] == $id_delete) {
            // Xóa hết các file vật lý đính kèm của thông báo này
            if (!empty($post['files'])) {
                foreach ($post['files'] as $f) {
                    if (file_exists($target_dir . $f)) {
                        @unlink($target_dir . $f);
                    }
                }
            }
            // Xóa thông báo khỏi session
            unset($_SESSION['bang_tin_list'][$key]);
            break;
        }
    }
    
    // Reset lại index của mảng để tránh lỗi vòng lặp sau này
    $_SESSION['bang_tin_list'] = array_values($_SESSION['bang_tin_list']);
    
    header("Location: phonghoc.php?malop=$ma_lop&tab=bang-tin");
    exit;
}

// Nếu không khớp action nào, mặc định đẩy về trang lớp học
header("Location: phonghoc.php?malop=$ma_lop&tab=bang-tin");
exit;