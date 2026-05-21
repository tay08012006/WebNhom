<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require '../config.php';
date_default_timezone_set('Asia/Ho_Chi_Minh');

$target_dir = "../uploads/"; 
if (!file_exists($target_dir)) {
    mkdir($target_dir, 0777, true);
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$ma_lop = $_POST['ma_lop'] ?? $_GET['malop'] ?? '';

// Lấy class_id từ mã lớp
$class_id = 0;
if (!empty($ma_lop)) {
    $stmt = $conn->prepare("SELECT id FROM classes WHERE ma_lop = ?");
    $stmt->bind_param("s", $ma_lop);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $class_id = $row['id'];
    }
}

// ====================== HÀM UPLOAD FILE ======================
function uploadFiles($input_name, $target_dir) {
    $uploaded_files = [];
    if (isset($_FILES[$input_name]) && !empty($_FILES[$input_name]['name'][0])) {
        foreach ($_FILES[$input_name]['tmp_name'] as $key => $tmp_name) {
            if ($_FILES[$input_name]['error'][$key] === 0) {
                $safe_filename = preg_replace("/[^a-zA-Z0-9.\-_]/", "", basename($_FILES[$input_name]['name'][$key]));
                $fname = time() . "_" . $safe_filename;
                if (move_uploaded_file($tmp_name, $target_dir . $fname)) {
                    $uploaded_files[] = $fname;
                }
            }
        }
    }
    return $uploaded_files;
}

// ====================== XỬ LÝ ======================
if ($action == 'add_post') {
    $noi_dung = $_POST['noi_dung'] ?? '';
    
    if ($class_id <= 0) {
        die("Lỗi: Không tìm thấy lớp học!");
    }

    $uploaded_files = uploadFiles('post_files', $target_dir);
    $file_str = implode(',', $uploaded_files);

    $stmt = $conn->prepare("INSERT INTO bang_tin (class_id, noi_dung, file_dinh_kem, chinh_sua) 
                           VALUES (?, ?, ?, 0)");
    $stmt->bind_param("iss", $class_id, $noi_dung, $file_str);
    $stmt->execute();

    header("Location: phonghoc.php?malop=$ma_lop&tab=bang-tin");
    exit;

} elseif ($action == 'update_post') {
    $id_update = $_POST['id_update'] ?? 0;
    $noi_dung = $_POST['noi_dung'] ?? '';
    $kept_files = $_POST['old_files'] ?? [];

    // Lấy file cũ
    $stmt = $conn->prepare("SELECT file_dinh_kem FROM bang_tin WHERE id = ?");
    $stmt->bind_param("i", $id_update);
    $stmt->execute();
    $old_file_str = $stmt->get_result()->fetch_assoc()['file_dinh_kem'] ?? '';
    $old_files = !empty($old_file_str) ? explode(',', $old_file_str) : [];
    
    // Xóa file không giữ
    $deleted_files = array_diff($old_files, $kept_files);
    foreach ($deleted_files as $df) {
        if (file_exists($target_dir . $df)) {
            @unlink($target_dir . $df);
        }
    }

    $uploaded_files = uploadFiles('post_files', $target_dir);
    $final_files = array_merge($kept_files, $uploaded_files);
    $file_str = implode(',', $final_files);

    // CẬP NHẬT + ĐÁNH DẤU ĐÃ CHỈNH SỬA
    $stmt = $conn->prepare("UPDATE bang_tin 
                           SET noi_dung = ?, file_dinh_kem = ?, chinh_sua = 1 
                           WHERE id = ?");
    $stmt->bind_param("ssi", $noi_dung, $file_str, $id_update);
    $stmt->execute();
    
    header("Location: phonghoc.php?malop=$ma_lop&tab=bang-tin");
    exit;

} elseif ($action == 'delete_post') {
    $id_delete = $_GET['id'] ?? 0;

    $stmt = $conn->prepare("SELECT file_dinh_kem FROM bang_tin WHERE id = ?");
    $stmt->bind_param("i", $id_delete);
    $stmt->execute();
    $old_file_str = $stmt->get_result()->fetch_assoc()['file_dinh_kem'] ?? '';
    
    if (!empty($old_file_str)) {
        $old_files = explode(',', $old_file_str);
        foreach ($old_files as $f) {
            if (file_exists($target_dir . $f)) {
                @unlink($target_dir . $f);
            }
        }
    }

    $stmt = $conn->prepare("DELETE FROM bang_tin WHERE id = ?");
    $stmt->bind_param("i", $id_delete);
    $stmt->execute();
}

header("Location: phonghoc.php?malop=$ma_lop&tab=bang-tin");
exit;
?>